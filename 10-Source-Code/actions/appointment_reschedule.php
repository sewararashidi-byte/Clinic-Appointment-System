
<?php

/**
 * Sprint 4 - US-10: Appointment Rescheduling
 *
 * Process an authenticated patient's request
 * to move an appointment to another available
 * slot with the same doctor.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

/**
 * Redirect the patient back to My Appointments
 * with an appropriate result message.
 */
function reschedulingResponse(
    string $messageType,
    string $message
): void {

    $_SESSION[$messageType] = $message;

    header(
        'Location: ../patient/appointments.php'
    );

    exit;
}

// Rescheduling requests must use POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    reschedulingResponse(
        'appointment_action_error',
        'Invalid rescheduling request.'
    );
}

// Always obtain patient identity from the session.
$patientId = (int) ($_SESSION['user_id'] ?? 0);

// Read the requested appointment and availability IDs.
$appointmentId = filter_var(
    $_POST['appointment_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$availabilityId = filter_var(
    $_POST['availability_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

// Read the requested new appointment date and time.
$newDate = $_POST['appointment_date'] ?? null;
$newStartTime = $_POST['start_time'] ?? null;

// Reject invalid or missing request values.
if (
    $patientId <= 0 ||
    !is_int($appointmentId) ||
    !is_int($availabilityId) ||
    !is_string($newDate) ||
    !is_string($newStartTime) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate) ||
    !preg_match('/^\d{2}:\d{2}:\d{2}$/', $newStartTime)
) {

    reschedulingResponse(
        'appointment_action_error',
        'Please select a valid appointment and new time slot.'
    );
}

// Validate the appointment action CSRF token.
$submittedToken = $_POST['csrf_token'] ?? null;

$sessionToken =
    $_SESSION['appointment_action_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {

    reschedulingResponse(
        'appointment_action_error',
        'Invalid or expired request. Please reload the page.'
    );
}

try {

    require_once __DIR__ . '/../config/database.php';

    // Provides getDoctorAvailability().
    require_once __DIR__ .
        '/../includes/doctor_repository.php';

    // Provides reschedulePatientAppointment().
    require_once __DIR__ .
        '/../includes/appointment_repository.php';

    // The repository performs the complete
    // transaction-safe rescheduling operation.
    reschedulePatientAppointment(
        $conn,
        $appointmentId,
        $patientId,
        $availabilityId,
        $newDate,
        $newStartTime
    );

    $conn->close();

    reschedulingResponse(
        'appointment_action_success',
        'Your appointment has been rescheduled successfully.'
    );

} catch (DomainException $e) {

    reschedulingResponse(
        'appointment_action_error',
        $e->getMessage()
    );

} catch (mysqli_sql_exception $e) {

    error_log(
        'Appointment rescheduling database error: ' .
        $e->getMessage()
    );

    $message = (int) $e->getCode() === 1062
        ? 'This appointment slot has already been booked.'
        : 'Unable to reschedule the appointment. Please try again.';

    reschedulingResponse(
        'appointment_action_error',
        $message
    );

} catch (Throwable $e) {

    error_log(
        'Appointment rescheduling failed: ' .
        $e->getMessage()
    );

    reschedulingResponse(
        'appointment_action_error',
        'Unable to reschedule the appointment. Please try again.'
    );
}
