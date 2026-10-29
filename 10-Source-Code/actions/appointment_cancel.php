
<?php

/**
 * Sprint 4 - US-09: Appointment Cancellation
 *
 * Handles authenticated patient cancellation requests.
 * Uses the existing cancellation repository function.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

/**
 * Redirect back to the patient's appointment list.
 */
function cancellationResponse(
    string $messageType,
    string $message
): void {

    $_SESSION[$messageType] = $message;

    header(
        'Location: ../patient/appointments.php'
    );

    exit;
}

// Only POST requests are accepted.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    cancellationResponse(
        'appointment_action_error',
        'Invalid cancellation request.'
    );
}

// Patient identity must come from the login session.
$patientId = (int) ($_SESSION['user_id'] ?? 0);

// Validate the requested appointment ID.
$appointmentId = filter_var(
    $_POST['appointment_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (
    $patientId <= 0 ||
    !is_int($appointmentId)
) {

    cancellationResponse(
        'appointment_action_error',
        'Please select a valid appointment.'
    );
}

// Verify CSRF protection.
$submittedToken = $_POST['csrf_token'] ?? null;

$sessionToken =
    $_SESSION['appointment_action_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {

    cancellationResponse(
        'appointment_action_error',
        'Invalid or expired request. Please reload the page.'
    );
}

try {

    require_once __DIR__ . '/../config/database.php';

    require_once __DIR__ .
        '/../includes/appointment_repository.php';

    // The repository validates ownership,
    // status and the Product Owner's 2-hour rule.
    cancelPatientAppointment(
        $conn,
        $appointmentId,
        $patientId
    );

    $conn->close();

    cancellationResponse(
        'appointment_action_success',
        'Your appointment has been cancelled successfully.'
    );

} catch (DomainException $e) {

    cancellationResponse(
        'appointment_action_error',
        $e->getMessage()
    );

} catch (Throwable $e) {

    error_log(
        'Appointment cancellation failed: ' .
        $e->getMessage()
    );

    cancellationResponse(
        'appointment_action_error',
        'Unable to cancel the appointment. Please try again.'
    );
}
