
<?php

/**
 * Sprint 4 - US-12: Patient Appointment History
 *
 * Securely load appointments belonging to
 * the authenticated patient.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

if (
    !isset($_SESSION['appointment_action_csrf_token']) ||
    !is_string($_SESSION['appointment_action_csrf_token'])
) {
    $_SESSION['appointment_action_csrf_token'] =
        bin2hex(random_bytes(32));
}


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/appointment_repository.php';

$patientId = (int) ($_SESSION['user_id'] ?? 0);

$patientAppointments = [];
$appointmentsError = '';

if ($patientId <= 0) {

    $appointmentsError = 'Invalid patient session.';

} else {

    try {

        $patientAppointments = getPatientAppointments(
            $conn,
            $patientId
        );

    } catch (Throwable $e) {

        error_log(
            'Patient appointments retrieval failed: ' .
            $e->getMessage()
        );

        $appointmentsError =
            'Unable to load appointments. Please try again.';
    }
}

$conn->close();
