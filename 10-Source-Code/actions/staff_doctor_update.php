
<?php

/**
 * Sprint 5 — US-18
 * Secure Doctor Contact Information Update
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

/**
 * Redirect to the doctor editing page
 * with a success or error message.
 */
function staffDoctorResponse(
    string $type,
    string $message,
    int $doctorId = 0
): void {

    $_SESSION[$type] = $message;

    $location = $doctorId > 0
        ? '../staff/doctor-edit.php?id=' . $doctorId
        : '../staff/doctors.php';

    header('Location: ' . $location);
    exit;
}

// Only POST requests are allowed.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    staffDoctorResponse(
        'staff_doctor_error',
        'Invalid request.'
    );
}

// Validate doctor ID.
$doctorId = filter_var(
    $_POST['doctor_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!is_int($doctorId)) {
    staffDoctorResponse(
        'staff_doctor_error',
        'Invalid doctor selected.'
    );
}

// Verify CSRF token.
$submittedToken = $_POST['csrf_token'] ?? null;

$sessionToken =
    $_SESSION['staff_doctor_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    staffDoctorResponse(
        'staff_doctor_error',
        'Invalid or expired request. Please reload the page.',
        $doctorId
    );
}

// Read and validate input types.
$phone = $_POST['phone'] ?? null;
$email = $_POST['email'] ?? null;
$consultationRoom = $_POST['consultation_room'] ?? null;

if (
    !is_string($phone) ||
    !is_string($email) ||
    ($consultationRoom !== null && !is_string($consultationRoom))
) {
    staffDoctorResponse(
        'staff_doctor_error',
        'Invalid doctor information.',
        $doctorId
    );
}

try {

    require_once __DIR__ . '/../config/database.php';

    require_once __DIR__ .
        '/../includes/staff_doctor_repository.php';

    // Confirm that this doctor exists.
    $doctor = getStaffDoctorById(
        $conn,
        $doctorId
    );

    if ($doctor === null) {
        throw new DomainException(
            'Doctor not found.'
        );
    }

    // Update only approved contact fields.
    
    // Save contact details and consultation room together.
    $conn->begin_transaction();

    try {

        $contactChanged = updateStaffDoctorContact(
            $conn,
            $doctorId,
            $phone,
            $email
        );

        // Preserve the room if an older form did not submit it.
        $roomChanged = false;

        if ($consultationRoom !== null) {
            $roomChanged = updateStaffDoctorConsultationRoom(
                $conn,
                $doctorId,
                $consultationRoom
            );
        }

        $conn->commit();

    } catch (Throwable $e) {

        $conn->rollback();
        throw $e;
    }

    $changed = $contactChanged || $roomChanged;


    $conn->close();

    staffDoctorResponse(
        'staff_doctor_success',
        $changed
            ? 'Doctor contact information updated successfully.'
            : 'No changes were needed.',
        $doctorId
    );

} catch (DomainException $e) {

    staffDoctorResponse(
        'staff_doctor_error',
        $e->getMessage(),
        $doctorId
    );

} catch (mysqli_sql_exception $e) {

    error_log(
        'Staff doctor update: ' . $e->getMessage()
    );

    $message = (int) $e->getCode() === 1062
        ? 'This email address is already in use.'
        : 'Unable to save doctor information.';

    staffDoctorResponse(
        'staff_doctor_error',
        $message,
        $doctorId
    );

} catch (Throwable $e) {

    error_log(
        'Staff doctor update: ' . $e->getMessage()
    );

    staffDoctorResponse(
        'staff_doctor_error',
        'Unable to save doctor information.',
        $doctorId
    );
}
