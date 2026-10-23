
<?php

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/doctor_repository.php';
require_once __DIR__ . '/../includes/appointment_repository.php';

/**
 * Redirect the patient back to Doctor Details
 * with a success or error message.
 */
function bookingResponse(
    int $doctorId,
    string $messageType,
    string $message
): void {
    $_SESSION[$messageType] = $message;

    $url = '../patient/doctor-details.php';

    if ($doctorId > 0) {
        $url .= '?doctor_id=' . $doctorId;
    }

    header('Location: ' . $url);
    exit;
}

// Booking requests must use POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    bookingResponse(
        0,
        'booking_error',
        'Invalid booking request.'
    );
}

// Patient identity comes from the authenticated session.
$patientId = (int) $_SESSION['user_id'];

// Read the selected doctor and availability IDs.
$doctorId = filter_var(
    $_POST['doctor_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$availabilityId = filter_var(
    $_POST['availability_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$doctorId = is_int($doctorId) ? $doctorId : 0;
$availabilityId = is_int($availabilityId) ? $availabilityId : 0;

// Read the requested appointment date and start time.
$appointmentDate = $_POST['appointment_date'] ?? '';
$startTime = $_POST['start_time'] ?? '';

// Reject missing or incorrectly structured data.
if (
    $doctorId <= 0 ||
    $availabilityId <= 0 ||
    !is_string($appointmentDate) ||
    !is_string($startTime) ||
    $appointmentDate === '' ||
    $startTime === ''
) {
    bookingResponse(
        $doctorId,
        'booking_error',
        'Please select a valid appointment slot.'
    );
}

// Verify the booking form's CSRF token.
$submittedToken = $_POST['csrf_token'] ?? null;
$sessionToken = $_SESSION['booking_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    bookingResponse(
        $doctorId,
        'booking_error',
        'Invalid or expired booking form. Please try again.'
    );
}

try {

    // Begin a transaction so booking checks and insertion
    // occur together.
    $conn->begin_transaction();

    // Lock the doctor's user record while processing the booking.
    // Concurrent requests using this handler must wait.
    $doctorSql = "
        SELECT id
        FROM users
        WHERE id = ?
          AND role = 'doctor'
          AND account_status = 'active'
        FOR UPDATE
    ";

    $doctorStmt = $conn->prepare($doctorSql);
    $doctorStmt->bind_param('i', $doctorId);
    $doctorStmt->execute();

    $doctorRecord = $doctorStmt->get_result()->fetch_assoc();
    $doctorStmt->close();

    if (!$doctorRecord) {
        throw new DomainException(
            'The selected doctor is not available.'
        );
    }

    // Retrieve current availability from the database.
    $availabilityRecords = getDoctorAvailability(
        $conn,
        $doctorId
    );

    // Generate valid future slots and exclude booked times.
    $availableSlots = getAvailableAppointmentSlots(
        $conn,
        $doctorId,
        $availabilityRecords
    );

    $selectedSlot = null;

    // Confirm that the submitted slot is actually available.
    foreach ($availableSlots as $slot) {

        if (
            (int) $slot['availability_id'] === $availabilityId &&
            $slot['appointment_date'] === $appointmentDate &&
            $slot['start_time'] === $startTime
        ) {
            $selectedSlot = $slot;
            break;
        }
    }

    if ($selectedSlot === null) {
        throw new DomainException(
            'This appointment slot is unavailable. Please select another slot.'
        );
    }

    // The end time is taken from the validated server-side slot.
    // It is not trusted from the submitted form.
    $endTime = $selectedSlot['end_time'];

    // Save an automatically confirmed appointment.
    $insertSql = "
        INSERT INTO appointments (
            patient_id,
            doctor_id,
            availability_id,
            appointment_date,
            start_time,
            end_time,
            status
        )
        VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
    ";

    $insertStmt = $conn->prepare($insertSql);

    $insertStmt->bind_param(
        'iiisss',
        $patientId,
        $doctorId,
        $availabilityId,
        $appointmentDate,
        $startTime,
        $endTime
    );

    
$insertStmt->execute();

// Get the ID of the newly booked appointment.
$appointmentId = (int) $conn->insert_id;

$insertStmt->close();

// Save the appointment permanently.
$conn->commit();

// Remember the latest successful booking for confirmation.
$_SESSION['latest_booking_appointment_id'] = $appointmentId;

// Keep the existing success message working.
bookingResponse(
    $doctorId,
    'booking_success',
    'Appointment booked successfully.'
);


} catch (DomainException $e) {

    $conn->rollback();

    bookingResponse(
        $doctorId,
        'booking_error',
        $e->getMessage()
    );

} catch (mysqli_sql_exception $e) {

    $conn->rollback();

    // Handle an appointment-slot uniqueness conflict.
    $message = (int) $e->getCode() === 1062
        ? 'This appointment slot has already been booked.'
        : 'Unable to complete the booking. Please try again.';

    bookingResponse(
        $doctorId,
        'booking_error',
        $message
    );

} catch (Throwable $e) {

    $conn->rollback();

    bookingResponse(
        $doctorId,
        'booking_error',
        'Unable to complete the booking. Please try again.'
    );
}
