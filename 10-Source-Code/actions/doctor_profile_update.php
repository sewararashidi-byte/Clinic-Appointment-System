
<?php

/**
 * Sprint 4 - US-14: Doctor Profile Update Action
 *
 * Process profile updates for the authenticated doctor.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['doctor']);

/**
 * Redirect back to the Doctor Profile page.
 */
function doctorProfileResponse(
    string $messageType,
    string $message
): void {

    $_SESSION[$messageType] = $message;

    header('Location: ../doctor/profile.php');
    exit;
}

// Only POST requests are allowed.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    doctorProfileResponse(
        'doctor_profile_error',
        'Invalid profile update request.'
    );
}

$doctorId = (int) ($_SESSION['user_id'] ?? 0);

if ($doctorId <= 0) {

    doctorProfileResponse(
        'doctor_profile_error',
        'Invalid doctor session.'
    );
}

// Check the CSRF security token.
$submittedToken = $_POST['csrf_token'] ?? null;

$sessionToken =
    $_SESSION['doctor_profile_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {

    doctorProfileResponse(
        'doctor_profile_error',
        'Invalid or expired form. Please reload the page.'
    );
}

// Read form fields.
$fullName = $_POST['full_name'] ?? null;
$phone = $_POST['phone'] ?? null;
$specialty = $_POST['specialty'] ?? null;
$qualifications = $_POST['qualifications'] ?? null;
$experienceInput = $_POST['years_experience'] ?? null;
$biography = $_POST['biography'] ?? null;

// Reject arrays and invalid input types.
if (
    !is_string($fullName) ||
    !is_string($phone) ||
    !is_string($specialty) ||
    !is_string($qualifications) ||
    !is_string($experienceInput) ||
    !is_string($biography)
) {

    doctorProfileResponse(
        'doctor_profile_error',
        'Invalid profile information.'
    );
}

// Remove unnecessary whitespace.
$fullName = trim($fullName);
$phone = trim($phone);
$specialty = trim($specialty);
$qualifications = trim($qualifications);
$experienceInput = trim($experienceInput);
$biography = trim($biography);

// Required fields.
if (
    $fullName === '' ||
    $phone === '' ||
    $specialty === '' ||
    $qualifications === ''
) {

    doctorProfileResponse(
        'doctor_profile_error',
        'Please complete all required profile fields.'
    );
}

// Validate permitted field lengths.
if (
    strlen($fullName) > 100 ||
    strlen($phone) > 20 ||
    strlen($specialty) > 100 ||
    strlen($qualifications) > 255 ||
    strlen($biography) > 65535
) {

    doctorProfileResponse(
        'doctor_profile_error',
        'One or more profile fields exceed the allowed length.'
    );
}

// Validate years of experience (0-255).
$yearsExperience = filter_var(
    $experienceInput,
    FILTER_VALIDATE_INT,
    [
        'options' => [
            'min_range' => 0,
            'max_range' => 255
        ]
    ]
);

if (!is_int($yearsExperience)) {

    doctorProfileResponse(
        'doctor_profile_error',
        'Please enter a valid number of years of experience.'
    );
}

// Biography is optional.
$biographyValue = $biography === ''
    ? null
    : $biography;

try {

    require_once __DIR__ . '/../config/database.php';

    require_once __DIR__ .
        '/../includes/doctor_profile_repository.php';

    saveDoctorProfessionalProfile(
        $conn,
        $doctorId,
        $fullName,
        $phone,
        $specialty,
        $qualifications,
        $yearsExperience,
        $biographyValue
    );

    $conn->close();

    // Keep the displayed dashboard name synchronized.
    $_SESSION['full_name'] = $fullName;

    doctorProfileResponse(
        'doctor_profile_success',
        'Your professional profile was updated successfully.'
    );

} catch (DomainException $e) {

    doctorProfileResponse(
        'doctor_profile_error',
        $e->getMessage()
    );

} catch (Throwable $e) {

    error_log(
        'Doctor profile update failed: ' .
        $e->getMessage()
    );

    doctorProfileResponse(
        'doctor_profile_error',
        'Unable to update your profile. Please try again.'
    );
}
