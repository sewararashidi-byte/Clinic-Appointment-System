
<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['doctor']);

/**
 * Redirect to the Doctor Dashboard with a message.
 */
function availabilityDeleteResponse(
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

// Identify the logged-in doctor.
$doctorId = (int) $_SESSION['user_id'];

// Validate the submitted availability ID.
$availabilityId = filter_var(
    $_POST['availability_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!is_int($availabilityId)) {
    availabilityDeleteResponse(
        'availability_error',
        'Invalid availability record.'
    );
}

try {

    // Start a database transaction.
    $conn->begin_transaction();

    /*
     * Lock the doctor record so this action
     * coordinates with the appointment-booking
     * backend, which locks the same doctor row.
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

    $doctorRecord = $doctorStmt
        ->get_result()
        ->fetch_assoc();

    $doctorStmt->close();

    if (!$doctorRecord) {
        throw new DomainException(
            'Doctor account not found.'
        );
    }

    /*
     * Check ownership and lock the
     * selected availability record.
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
     * Check for any appointment linked
     * to this availability record.
     *
     * We check all appointment statuses because
     * the database foreign key also protects
     * historical appointment records.
     */
    $bookingStmt = $conn->prepare(
        "SELECT id
         FROM appointments
         WHERE availability_id = ?
         LIMIT 1"
    );

    $bookingStmt->bind_param(
        'i',
        $availabilityId
    );

    $bookingStmt->execute();

    $existingBooking = $bookingStmt
        ->get_result()
        ->fetch_assoc();

    $bookingStmt->close();

    /*
     * Prevent deletion if any appointment
     * references this availability.
     */
    if ($existingBooking) {
        throw new DomainException(
            'This availability is linked to an appointment and cannot be deleted.'
        );
    }

    /*
     * No appointments are linked.
     * The doctor may delete this availability.
     */
    $deleteStmt = $conn->prepare(
        "DELETE FROM doctor_availability
         WHERE id = ?
           AND doctor_id = ?"
    );

    $deleteStmt->bind_param(
        'ii',
        $availabilityId,
        $doctorId
    );

    $deleteStmt->execute();
    $deleteStmt->close();

    // Save the deletion.
    $conn->commit();
    $conn->close();

    availabilityDeleteResponse(
        'availability_success',
        'Availability deleted successfully.'
    );

} catch (DomainException $e) {

    $conn->rollback();
    $conn->close();

    availabilityDeleteResponse(
        'availability_error',
        $e->getMessage()
    );

} catch (Throwable $e) {

    $conn->rollback();
    $conn->close();

    availabilityDeleteResponse(
        'availability_error',
        'Unable to delete availability. Please try again.'
    );
}
