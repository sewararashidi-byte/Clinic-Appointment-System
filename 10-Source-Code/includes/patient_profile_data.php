
<?php

/**
 * Sprint 3 - US-04: Patient Profile Management
 *
 * Securely load the logged-in patient's profile
 * for display on the Patient Profile page.
 */

require_once __DIR__ . '/../config/auth.php';

// Only authenticated patients can access this data.
requireRole(['patient']);

/*
 * Sprint 3 - US-04:
 * Generate a CSRF token for the patient profile form
 * if the session does not already have one.
 */
if (
    !isset($_SESSION['csrf_token']) ||
    !is_string($_SESSION['csrf_token']) ||
    $_SESSION['csrf_token'] === ''
) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/patient_profile_repository.php';

// Get the patient's ID from the login session.
$patientId = (int) ($_SESSION['user_id'] ?? 0);

$patientProfile = null;
$profileError = '';

if ($patientId > 0) {
    $patientProfile = getPatientProfile(
        $conn,
        $patientId
    );
}

if ($patientProfile === null) {
    $profileError =
        'Patient profile not found or access denied.';
}

$conn->close();
