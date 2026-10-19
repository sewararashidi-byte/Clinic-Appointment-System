
<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['doctor']);

/**
 * Redirect to the Doctor Dashboard with a message.
 */
function availabilityUpdateResponse(
    string $messageType,
    string $message
): void {
    $_SESSION[$messageType] = $message;

    header('Location: ../dashboards/doctor-dashboard.php');
    exit;
}

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Get the logged-in doctor's ID from the session.
$doctorId = (int) $_SESSION['user_id'];

// Read availability record ID.
$availabilityId = filter_var(
    $_POST['availability_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

// Read submitted values safely.
$availabilityDate = $_POST['availability_date'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endTime = $_POST['end_time'] ?? '';

// Validate input types.
if (
    !is_int($availabilityId) ||
    !is_string($availabilityDate) ||
    !is_string($startTime) ||
    !is_string($endTime)
) {
    availabilityUpdateResponse(
        'availability_error',
        'Invalid availability information.'
    );
}

$availabilityDate = trim($availabilityDate);
$startTime = trim($startTime);
$endTime = trim($endTime);

// Validate required fields.
if (
    $availabilityDate === '' ||
    $startTime === '' ||
    $endTime === ''
) {
    availabilityUpdateResponse(
        'availability_error',
        'Please enter the availability date, start time, and end time.'
    );
}

// Validate the date.
$dateObject = DateTimeImmutable::createFromFormat(
    '!Y-m-d',
    $availabilityDate
);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $availabilityDate
) {
    availabilityUpdateResponse(
        'availability_error',
        'Please enter a valid availability date.'
    );
}

// Validate time format.
$timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';

if (
    !preg_match($timePattern, $startTime) ||
    !preg_match($timePattern, $endTime)
) {
    availabilityUpdateResponse(
        'availability_error',
        'Please enter valid start and end times.'
    );
}

// Normalize times to HH:MM:SS.
if (strlen($startTime) === 5) {
    $startTime .= ':00';
}

if (strlen($endTime) === 5) {
    $endTime .= ':00';
}

// Start time must be earlier than end time.
if ($startTime >= $endTime) {
    availabilityUpdateResponse(
        'availability_error',
        'Start time must be earlier than end time.'
    );
}

try {

    // Begin a transaction to protect related records.
    $conn->begin_transaction();

    /*
     * Lock the doctor record.
     * The appointment-booking backend uses the
     * same doctor-row locking approach.
     */
    $doctorStmt = $conn->prepare(
        "SELECT id
         FROM users
         WHERE id = ?
           AND role = 'doctor'
         FOR UPDATE"
    );

    $doctorStmt->bind_param('i', $doctorId);
    $doctorStmt->execute();

    $doctorRecord = $doctorStmt->get_result()->fetch_assoc();

    $doctorStmt->close();

    if (!$doctorRecord) {
        throw new DomainException(
            'Doctor account not found.'
        );
    }

    /*
     * Verify ownership of the availability record.
     */
    $checkStmt = $conn->prepare(
        "SELECT id
         FROM doctor_availability
         WHERE id = ?
           AND doctor_id = ?
         FOR UPDATE"
    );

    $checkStmt->bind_param(
        'ii',
        $availabilityId,
        $doctorId
    );

    $checkStmt->execute();

    $availabilityRecord = $checkStmt
        ->get_result()
        ->fetch_assoc();

    $checkStmt->close();

    if (!$availabilityRecord) {
        throw new DomainException(
            'Availability record not found or access denied.'
        );
    }

    /*
     * Check whether confirmed appointments
     * are associated with this availability.
     */
    $bookingStmt = $conn->prepare(
        "SELECT id
         FROM appointments
         WHERE availability_id = ?
           AND doctor_id = ?
           AND status = 'confirmed'
         LIMIT 1"
    );

    $bookingStmt->bind_param(
        'ii',
        $availabilityId,
        $doctorId
    );

    $bookingStmt->execute();

    $existingBooking = $bookingStmt
        ->get_result()
        ->fetch_assoc();

    $bookingStmt->close();

    /*
     * Do not change availability containing
     * confirmed patient appointments.
     */
    if ($existingBooking) {
        throw new DomainException(
            'This availability contains confirmed appointments and cannot be updated until those bookings are resolved.'
        );
    }

    /*
     * No confirmed bookings exist.
     * The doctor can safely update the record.
     */
    $updateStmt = $conn->prepare(
        "UPDATE doctor_availability
         SET availability_date = ?,
             start_time = ?,
             end_time = ?
         WHERE id = ?
           AND doctor_id = ?"
    );

    $updateStmt->bind_param(
        'sssii',
        $availabilityDate,
        $startTime,
        $endTime,
        $availabilityId,
        $doctorId
    );

    $updateStmt->execute();
    $updateStmt->close();

    // Save the successful update.
    $conn->commit();

    $conn->close();

    availabilityUpdateResponse(
        'availability_success',
        'Availability updated successfully.'
    );

} catch (DomainException $e) {

    $conn->rollback();
    $conn->close();

    availabilityUpdateResponse(
        'availability_error',
        $e->getMessage()
    );

} catch (Throwable $e) {

    $conn->rollback();
    $conn->close();

    availabilityUpdateResponse(
        'availability_error',
        'Unable to update availability. Please try again.'
    );
}
