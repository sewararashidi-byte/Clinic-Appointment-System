
<?php

/**
 * Sprint 5 — US-17
 * Secure Staff Patient Information Update
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

function staffPatientResponse(
    string $type,
    string $message,
    int $patientId = 0
): void {
    $_SESSION[$type] = $message;

    $location = $patientId > 0
        ? '../staff/patient-edit.php?id=' . $patientId
        : '../staff/patients.php';

    header('Location: ' . $location);
    exit;
}

// Only POST requests are allowed.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    staffPatientResponse(
        'staff_patient_error',
        'Invalid request.'
    );
}

// Validate patient ID.
$patientId = filter_var(
    $_POST['patient_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!is_int($patientId)) {
    staffPatientResponse(
        'staff_patient_error',
        'Invalid patient selected.'
    );
}

// Check CSRF token.
$submittedToken = $_POST['csrf_token'] ?? null;
$sessionToken = $_SESSION['staff_patient_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    staffPatientResponse(
        'staff_patient_error',
        'Invalid or expired request. Please reload the page.',
        $patientId
    );
}

// Check input types.
$fullName = $_POST['full_name'] ?? null;
$phone = $_POST['phone'] ?? null;
$email = $_POST['email'] ?? null;
$ageInput = $_POST['age'] ?? '';
$homeAddress = $_POST['home_address'] ?? null;

if (
    !is_string($fullName) ||
    !is_string($phone) ||
    !is_string($email) ||
    !is_string($ageInput) ||
    ($homeAddress !== null && !is_string($homeAddress))
) {
    staffPatientResponse(
        'staff_patient_error',
        'Invalid patient information.',
        $patientId
    );
}

// Age is optional in the existing database.
$ageInput = trim($ageInput);
$age = null;

if ($ageInput !== '') {
    $age = filter_var(
        $ageInput,
        FILTER_VALIDATE_INT,
        ['options' => [
            'min_range' => 1,
            'max_range' => 120
        ]]
    );

    if ($age === false) {
        staffPatientResponse(
            'staff_patient_error',
            'Please enter a valid age.',
            $patientId
        );
    }
}

try {
    require_once __DIR__ . '/../config/database.php';

    require_once __DIR__ .
        '/../includes/staff_patient_repository.php';

    // Confirm that the patient exists.
    $patient = getStaffPatientById(
        $conn,
        $patientId
    );

    if ($patient === null) {
        throw new DomainException(
            'Patient not found.'
        );
    }

    // Only approved general information is updated.
    $changed = updateStaffPatient(
    $conn,
    $patientId,
    $fullName,
    $age,
    $phone,
    $email,
    $homeAddress
);

    $conn->close();

    staffPatientResponse(
        'staff_patient_success',
        $changed
            ? 'Patient information updated successfully.'
            : 'No changes were needed.',
        $patientId
    );

} catch (DomainException $e) {

    staffPatientResponse(
        'staff_patient_error',
        $e->getMessage(),
        $patientId
    );

} catch (mysqli_sql_exception $e) {

    error_log('Staff patient update: ' . $e->getMessage());

    $message = (int) $e->getCode() === 1062
        ? 'This email address is already in use.'
        : 'Unable to save patient information.';

    staffPatientResponse(
        'staff_patient_error',
        $message,
        $patientId
    );

} catch (Throwable $e) {

    error_log('Staff patient update: ' . $e->getMessage());

    staffPatientResponse(
        'staff_patient_error',
        'Unable to save patient information.',
        $patientId
    );
}
