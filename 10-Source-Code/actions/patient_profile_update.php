
<?php

/**
 * Sprint 3 - US-04: Patient Profile Management
 * Secure patient profile update action.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

// Return a message to the patient profile page.
function patientProfileResponse(
    string $type,
    string $message
): void {
    $_SESSION[$type] = $message;

    header('Location: ../patient/profile.php');
    exit;
}

// Only form submissions are accepted.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verify the CSRF token.
$submittedToken = $_POST['csrf_token'] ?? null;
$sessionToken = $_SESSION['csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    $sessionToken === '' ||
    !hash_equals($sessionToken, $submittedToken)
) {
    patientProfileResponse(
        'profile_error',
        'Invalid security token. Please try again.'
    );
}

// The patient ID must come from the login session.
$patientId = (int) ($_SESSION['user_id'] ?? 0);

if ($patientId <= 0) {
    patientProfileResponse(
        'profile_error',
        'Invalid patient session.'
    );
}

// Read submitted form values.
$fullName = $_POST['full_name'] ?? '';
$phone = $_POST['phone'] ?? '';
$ageInput = $_POST['age'] ?? '';
$bloodType = $_POST['blood_type'] ?? '';
$diseaseType = $_POST['disease_type'] ?? '';
$relativesInput = $_POST['close_relatives_count'] ?? '';

// Reject invalid input types.
foreach ([
    $fullName,
    $phone,
    $ageInput,
    $bloodType,
    $diseaseType,
    $relativesInput
] as $value) {
    if (!is_string($value)) {
        patientProfileResponse(
            'profile_error',
            'Invalid profile information.'
        );
    }
}

// Remove unnecessary whitespace.
$fullName = trim($fullName);
$phone = trim($phone);
$ageInput = trim($ageInput);
$bloodType = trim($bloodType);
$diseaseType = trim($diseaseType);
$relativesInput = trim($relativesInput);

// Full name and phone are required.
if ($fullName === '' || $phone === '') {
    patientProfileResponse(
        'profile_error',
        'Full name and phone number are required.'
    );
}

// Check database field lengths.
if (
    strlen($fullName) > 100 ||
    strlen($phone) > 20 ||
    strlen($bloodType) > 5 ||
    strlen($diseaseType) > 255
) {
    patientProfileResponse(
        'profile_error',
        'One or more profile fields are too long.'
    );
}

// Age is optional.
$age = null;

if ($ageInput !== '') {
    $age = filter_var(
        $ageInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0, 'max_range' => 255]]
    );

    if ($age === false) {
        patientProfileResponse(
            'profile_error',
            'Please enter a valid age.'
        );
    }
}

// Number of close relatives is optional.
$closeRelativesCount = null;

if ($relativesInput !== '') {
    $closeRelativesCount = filter_var(
        $relativesInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 0, 'max_range' => 65535]]
    );

    if ($closeRelativesCount === false) {
        patientProfileResponse(
            'profile_error',
            'Please enter a valid number of close relatives.'
        );
    }
}

// Store empty optional fields as NULL.
$bloodType = $bloodType === '' ? null : $bloodType;
$diseaseType = $diseaseType === '' ? null : $diseaseType;

try {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../includes/patient_profile_repository.php';

    $updated = updatePatientProfile(
        $conn,
        $patientId,
        $fullName,
        $phone,
        $age,
        $bloodType,
        $diseaseType,
        $closeRelativesCount
    );

    $conn->close();

    if (!$updated) {
        patientProfileResponse(
            'profile_error',
            'Unable to update your profile.'
        );
    }

    patientProfileResponse(
        'profile_success',
        'Your profile has been updated successfully.'
    );

} catch (Throwable $e) {
    patientProfileResponse(
        'profile_error',
        'Unable to update your profile. Please try again.'
    );
}
