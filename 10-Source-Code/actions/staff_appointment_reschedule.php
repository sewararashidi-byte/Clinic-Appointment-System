
<?php

/**
 * Sprint 5 — US-19
 * Staff Appointment Rescheduling Action
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

function staffRescheduleResponse(
    string $messageType,
    string $message,
    int $appointmentId = 0,
    bool $success = false
): void {

    $_SESSION[$messageType] = $message;

    if ($success || $appointmentId <= 0) {
        $location = '../staff/appointments.php';
    } else {
        $location = '../staff/appointment-reschedule.php?id='
            . $appointmentId;
    }

    header('Location: ' . $location);
    exit;
}

// Only POST requests are allowed.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    staffRescheduleResponse(
        'staff_appointment_error',
        'Invalid rescheduling request.'
    );
}

// Identify the authenticated staff member.
$staffUserId = (int) ($_SESSION['user_id'] ?? 0);

// Validate appointment and availability IDs.
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

if (!is_int($appointmentId)) {
    staffRescheduleResponse(
        'staff_appointment_error',
        'Invalid appointment selected.'
    );
}

// Verify the CSRF token.
$submittedToken = $_POST['csrf_token'] ?? null;

$sessionToken =
    $_SESSION['staff_reschedule_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    staffRescheduleResponse(
        'staff_reschedule_error',
        'Invalid or expired request. Please reload the page.',
        $appointmentId
    );
}

// Retrieve the selected date and time.
$newDate = $_POST['appointment_date'] ?? null;
$newStartTime = $_POST['start_time'] ?? null;

if (
    $staffUserId <= 0 ||
    !is_int($availabilityId) ||
    !is_string($newDate) ||
    !is_string($newStartTime)
) {
    staffRescheduleResponse(
        'staff_reschedule_error',
        'Please select a valid appointment time.',
        $appointmentId
    );
}

try {

    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/doctor_repository.php';
    require_once __DIR__ . '/../includes/appointment_repository.php';

    // Find the patient associated with this appointment.
    // Do not trust a patient ID supplied by a form.
    $sql = "
        SELECT patient_id
        FROM appointments
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $appointmentId);
    $stmt->execute();

    $appointment = $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();

    if (!$appointment) {
        throw new DomainException(
            'Appointment not found.'
        );
    }

    $patientId = (int) $appointment['patient_id'];

    // Reuse existing rescheduling logic.
    // The final argument identifies the staff member.
    reschedulePatientAppointment(
        $conn,
        $appointmentId,
        $patientId,
        $availabilityId,
        $newDate,
        $newStartTime,
        $staffUserId
    );

    $conn->close();

    staffRescheduleResponse(
        'staff_appointment_success',
        'Appointment #' . $appointmentId .
            ' rescheduled successfully.',
        $appointmentId,
        true
    );

} catch (DomainException $e) {

    staffRescheduleResponse(
        'staff_reschedule_error',
        $e->getMessage(),
        $appointmentId
    );

} catch (Throwable $e) {

    error_log(
        'Staff appointment rescheduling failed: ' .
        $e->getMessage()
    );

    staffRescheduleResponse(
        'staff_reschedule_error',
        'Unable to reschedule the appointment.',
        $appointmentId
    );
}
