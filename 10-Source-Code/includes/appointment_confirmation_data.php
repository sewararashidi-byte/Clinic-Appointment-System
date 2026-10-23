
<?php

/**
 * US-11: Load appointment confirmation data.
 *
 * This file will be included by the patient
 * appointment confirmation page.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/appointment_repository.php';

// Identify the logged-in patient.
$patientId = (int) ($_SESSION['user_id'] ?? 0);

// Read the requested appointment ID, or use the
// ID saved after the patient's latest booking.
$appointmentId = filter_var(
    $_GET['appointment_id']
        ?? ($_SESSION['latest_booking_appointment_id'] ?? null),
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$confirmation = null;
$confirmationError = '';

if (is_int($appointmentId) && $patientId > 0) {

    // The repository checks that the appointment
    // belongs to this authenticated patient.
    $confirmation = getPatientAppointmentConfirmation(
        $conn,
        $appointmentId,
        $patientId
    );
}

// Do not reveal whether another patient's
// appointment ID exists.
if ($confirmation === null) {
    $confirmationError =
        'Appointment confirmation not found or you do not have permission to view it.';
}

$conn->close();
