
<?php

/**
 * Sprint 5 — US-19
 * Staff Appointment Cancellation Action
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

/**
 * Return to the staff appointment list
 * with a one-time message.
 */
function staffCancellationResponse(
    string $messageType,
    string $message
): void {

    $_SESSION[$messageType] = $message;

    header('Location: ../staff/appointments.php');
    exit;
}

// Accept POST requests only.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    staffCancellationResponse(
        'staff_appointment_error',
        'Invalid cancellation request.'
    );
}

// Identify the logged-in staff member.
$staffUserId = (int) ($_SESSION['user_id'] ?? 0);

// Validate appointment ID.
$appointmentId = filter_var(
    $_POST['appointment_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (
    $staffUserId <= 0 ||
    !is_int($appointmentId)
) {
    staffCancellationResponse(
        'staff_appointment_error',
        'Please select a valid appointment.'
    );
}

// Verify CSRF token.
$submittedToken = $_POST['csrf_token'] ?? null;

$sessionToken =
    $_SESSION['staff_appointment_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    staffCancellationResponse(
        'staff_appointment_error',
        'Invalid or expired request. Please reload the page.'
    );
}

// Read cancellation reason.
$reason = $_POST['reason'] ?? '';

if (!is_string($reason)) {
    staffCancellationResponse(
        'staff_appointment_error',
        'Invalid cancellation reason.'
    );
}

$reason = trim($reason);

if (strlen($reason) > 500) {
    staffCancellationResponse(
        'staff_appointment_error',
        'Cancellation reason must not exceed 500 characters.'
    );
}

try {

    require_once __DIR__ . '/../config/database.php';

    require_once __DIR__ . '/../config/timezone.php';

    require_once __DIR__ .
        '/../includes/staff_appointment_repository.php';

    // The repository checks:
    // - Active staff authorization
    // - Appointment status
    // - Future appointment date/time
    // - 24-hour cancellation deadline
    // - Mandatory reason for late cancellation
    // - Database transaction and audit logging

    cancelStaffAppointment(
        $conn,
        $appointmentId,
        $staffUserId,
        $reason
    );

    $conn->close();

    staffCancellationResponse(
        'staff_appointment_success',
        'Appointment cancelled successfully.'
    );

} catch (DomainException $e) {

    staffCancellationResponse(
        'staff_appointment_error',
        $e->getMessage()
    );

} catch (Throwable $e) {

    error_log(
        'Staff appointment cancellation failed: ' .
        $e->getMessage()
    );

    staffCancellationResponse(
        'staff_appointment_error',
        'Unable to cancel the appointment. Please try again.'
    );
}
