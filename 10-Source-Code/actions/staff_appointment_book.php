
<?php

/**
 * Sprint 5 — US-19
 * Staff Appointment Booking Action
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

function staffBookingResponse(
    string $messageType,
    string $message,
    bool $success = false
): void {

    $_SESSION[$messageType] = $message;

    $location = $success
        ? '../staff/appointments.php'
        : '../staff/appointment-book.php';

    header('Location: ' . $location);
    exit;
}

// Booking must use POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    staffBookingResponse(
        'staff_booking_error',
        'Invalid booking request.'
    );
}

// Verify CSRF protection.
$submittedToken = $_POST['csrf_token'] ?? null;
$sessionToken = $_SESSION['staff_booking_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    staffBookingResponse(
        'staff_booking_error',
        'Invalid or expired booking request.'
    );
}

// Identify the logged-in staff member.
$staffUserId = (int) ($_SESSION['user_id'] ?? 0);

// Validate patient, doctor, and availability IDs.
$patientId = filter_var(
    $_POST['patient_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$doctorId = filter_var(
    $_POST['doctor_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$availabilityId = filter_var(
    $_POST['availability_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

// Read the selected appointment date and time.
$appointmentDate = $_POST['appointment_date'] ?? null;
$startTime = $_POST['start_time'] ?? null;

if (
    $staffUserId <= 0 ||
    !is_int($patientId) ||
    !is_int($doctorId) ||
    !is_int($availabilityId) ||
    !is_string($appointmentDate) ||
    !is_string($startTime)
) {
    staffBookingResponse(
        'staff_booking_error',
        'Please select a valid patient, doctor, and appointment slot.'
    );
}

try {

    require_once __DIR__ . '/../config/database.php';

    require_once __DIR__ .
        '/../includes/staff_appointment_booking.php';

    // This function validates:
    // - Active staff and patient accounts
    // - Doctor availability
    // - Selected appointment slot
    // - Double-booking prevention
    // - Atomic booking and audit logging

    $appointmentId = bookStaffAppointmentForPatient(
        $conn,
        $staffUserId,
        $patientId,
        $doctorId,
        $availabilityId,
        $appointmentDate,
        $startTime
    );

    $conn->close();

    $_SESSION['staff_appointment_success'] =
        'Appointment #' . $appointmentId .
        ' booked successfully.';

    header('Location: ../staff/appointments.php');
    exit;

} catch (DomainException $e) {

    staffBookingResponse(
        'staff_booking_error',
        $e->getMessage()
    );

} catch (mysqli_sql_exception $e) {

    error_log(
        'Staff booking database error: ' . $e->getMessage()
    );

    $message = (int) $e->getCode() === 1062
        ? 'This appointment slot has already been booked.'
        : 'Unable to complete the appointment booking.';

    staffBookingResponse(
        'staff_booking_error',
        $message
    );

} catch (Throwable $e) {

    error_log(
        'Staff appointment booking failed: ' . $e->getMessage()
    );

    staffBookingResponse(
        'staff_booking_error',
        'Unable to complete the appointment booking.'
    );
}
