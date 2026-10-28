
<?php

/**
 * Sprint 4 - US-14: Doctor Profile Data Loader
 *
 * Load the authenticated doctor's professional profile.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['doctor']);

// Generate a CSRF token for the profile update form.
if (
    !isset($_SESSION['doctor_profile_csrf_token']) ||
    !is_string($_SESSION['doctor_profile_csrf_token'])
) {
    $_SESSION['doctor_profile_csrf_token'] =
        bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/doctor_profile_repository.php';

$doctorId = (int) ($_SESSION['user_id'] ?? 0);

$doctorProfile = null;
$profileError = '';

if ($doctorId <= 0) {

    $profileError = 'Invalid doctor session.';

} else {

    try {

        $doctorProfile = getDoctorProfessionalProfile(
            $conn,
            $doctorId
        );

        if ($doctorProfile === null) {
            $profileError = 'Doctor profile not found.';
        }

    } catch (Throwable $e) {

        error_log(
            'Doctor profile retrieval failed: ' .
            $e->getMessage()
        );

        $profileError =
            'Unable to load doctor profile. Please try again.';
    }
}

$conn->close();
