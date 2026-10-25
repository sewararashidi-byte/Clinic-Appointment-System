
<?php

/**
 * Sprint 3 - US-16: Doctor Appointment Management
 *
 * Load confirmed appointments belonging to the
 * currently authenticated doctor.
 */

require_once __DIR__ . '/../config/auth.php';

// Only authenticated doctors can access this data.
requireRole(['doctor']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/appointment_repository.php';

// Use the authenticated doctor's ID, not a URL parameter.
$doctorId = (int) ($_SESSION['user_id'] ?? 0);

$doctorAppointments = [];
$appointmentsError = '';

if ($doctorId > 0) {
    $doctorAppointments = getDoctorAppointments(
        $conn,
        $doctorId
    );
} else {
    $appointmentsError = 'Invalid doctor session.';
}

$conn->close();
