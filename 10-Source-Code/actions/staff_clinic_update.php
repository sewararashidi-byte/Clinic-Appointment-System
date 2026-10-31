
<?php

/**
 * Sprint 5 — US-20
 * Secure Clinic Information Update Action
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

/**
 * Return to the clinic management page
 * with a one-time success or error message.
 */
function staffClinicResponse(
    string $messageType,
    string $message
): void {

    $_SESSION[$messageType] = $message;

    header('Location: ../staff/clinic.php');
    exit;
}

// Only POST requests are accepted.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    staffClinicResponse(
        'staff_clinic_error',
        'Invalid clinic information request.'
    );
}

// Verify the CSRF token.
$submittedToken = $_POST['csrf_token'] ?? null;

$sessionToken =
    $_SESSION['staff_clinic_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    staffClinicResponse(
        'staff_clinic_error',
        'Invalid or expired request. Please reload the page.'
    );
}

// Identify the authenticated staff member.
$staffUserId = (int) ($_SESSION['user_id'] ?? 0);

// Read the four required fields.
$clinicName = $_POST['clinic_name'] ?? null;
$clinicAddress = $_POST['clinic_address'] ?? null;
$contactPhone = $_POST['contact_phone'] ?? null;
$openingHours = $_POST['opening_hours'] ?? null;

// Reject missing or incorrectly structured input.
if (
    $staffUserId <= 0 ||
    !is_string($clinicName) ||
    !is_string($clinicAddress) ||
    !is_string($contactPhone) ||
    !is_string($openingHours)
) {
    staffClinicResponse(
        'staff_clinic_error',
        'Please provide valid clinic information.'
    );
}

try {

    require_once __DIR__ . '/../config/database.php';

    require_once __DIR__ .
        '/../includes/staff_clinic_repository.php';

    // The repository verifies active staff authorization,
    // validates all four fields, and saves the clinic record.
    saveClinicInformation(
        $conn,
        $staffUserId,
        $clinicName,
        $clinicAddress,
        $contactPhone,
        $openingHours
    );

    $conn->close();

    staffClinicResponse(
        'staff_clinic_success',
        'Clinic information saved successfully.'
    );

} catch (DomainException $e) {

    staffClinicResponse(
        'staff_clinic_error',
        $e->getMessage()
    );

} catch (Throwable $e) {

    error_log(
        'Clinic information update failed: ' .
        $e->getMessage()
    );

    staffClinicResponse(
        'staff_clinic_error',
        'Unable to save clinic information. Please try again.'
    );
}
