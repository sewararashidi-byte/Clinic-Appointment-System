
<?php

/**
 * Sprint 5 — US-19
 * Staff Appointment Booking Form
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/doctor_repository.php';
require_once __DIR__ . '/../includes/appointment_repository.php';

// Retrieve active patients.
$patientResult = $conn->query("
    SELECT id, full_name, email
    FROM users
    WHERE role = 'patient'
      AND account_status = 'active'
    ORDER BY full_name, id
");

$patients = $patientResult->fetch_all(MYSQLI_ASSOC);

// Retrieve active doctors.
$doctorResult = $conn->query("
    SELECT id, full_name
    FROM users
    WHERE role = 'doctor'
      AND account_status = 'active'
    ORDER BY full_name, id
");

$doctors = $doctorResult->fetch_all(MYSQLI_ASSOC);

// Identify the doctor selected using GET.
$doctorInput = $_GET['doctor_id'] ?? null;

$doctorId = is_string($doctorInput)
    ? filter_var(
        $doctorInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;

$doctorId = is_int($doctorId) ? $doctorId : 0;

// Confirm the doctor is active.
$validDoctor = false;

foreach ($doctors as $doctor) {
    if ((int) $doctor['id'] === $doctorId) {
        $validDoctor = true;
        break;
    }
}

if (!$validDoctor) {
    $doctorId = 0;
}

// Retrieve genuine available appointment slots.
$availableSlots = [];

if ($doctorId > 0) {

    $availabilityRecords = getDoctorAvailability(
        $conn,
        $doctorId
    );

    $availableSlots = getAvailableAppointmentSlots(
        $conn,
        $doctorId,
        $availabilityRecords
    );

    usort(
        $availableSlots,
        function ($a, $b) {
            return strcmp(
                $a['appointment_date'] . $a['start_time'],
                $b['appointment_date'] . $b['start_time']
            );
        }
    );

    // Keep the form manageable.
    $availableSlots = array_slice(
        $availableSlots,
        0,
        100
    );
}

$conn->close();

// Create the booking CSRF token.
if (empty($_SESSION['staff_booking_csrf_token'])) {
    $_SESSION['staff_booking_csrf_token'] =
        bin2hex(random_bytes(32));
}

// Read and clear one-time errors.
$error = $_SESSION['staff_booking_error'] ?? '';

unset($_SESSION['staff_booking_error']);

function escapeStaffBooking($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Staff Appointment Booking</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5" style="max-width: 850px;">

    <h2>Book Appointment for Patient</h2>

    <p class="text-muted">
        Staff Appointment Management — US-19
    </p>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= escapeStaffBooking($error) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-body">

            <h5>Step 1 — Select Doctor</h5>

            <form method="GET">

                <div class="mb-3">

                    <label class="form-label">
                        Doctor
                    </label>

                    <select
                        name="doctor_id"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Choose a doctor
                        </option>

                        <?php foreach ($doctors as $doctor): ?>

                            <option
                                value="<?= (int) $doctor['id'] ?>"
                                <?= (int) $doctor['id'] === $doctorId
                                    ? 'selected'
                                    : '' ?>
                            >
                                <?= escapeStaffBooking(
                                    $doctor['full_name']
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Show Available Times
                </button>

            </form>

        </div>
    </div>

    <?php if ($doctorId > 0): ?>

        <div class="card shadow-sm">
            <div class="card-body">

                <h5>Step 2 — Select Patient and Time</h5>

                <?php if (empty($availableSlots)): ?>

                    <div class="alert alert-warning">
                        No available future appointment slots
                        were found for this doctor.
                    </div>

                <?php elseif (empty($patients)): ?>

                    <div class="alert alert-warning">
                        No active patient accounts were found.
                    </div>

                <?php else: ?>

                    <form
                        id="staffBookingForm"
                        method="POST"
                        action="../actions/staff_appointment_book.php"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= escapeStaffBooking(
                                $_SESSION['staff_booking_csrf_token']
                            ) ?>"
                        >

                        <input
                            type="hidden"
                            name="doctor_id"
                            value="<?= $doctorId ?>"
                        >

                        <input
                            type="hidden"
                            name="availability_id"
                            id="availabilityId"
                        >

                        <input
                            type="hidden"
                            name="appointment_date"
                            id="appointmentDate"
                        >

                        <input
                            type="hidden"
                            name="start_time"
                            id="appointmentStartTime"
                        >

                        <div class="mb-3">

                            <label class="form-label">
                                Patient
                            </label>

                            <select
                                name="patient_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Choose a patient
                                </option>

                                <?php foreach ($patients as $patient): ?>

                                    <option
                                        value="<?= (int) $patient['id'] ?>"
                                    >
                                        <?= escapeStaffBooking(
                                            $patient['full_name']
                                        ) ?>
                                        —
                                        <?= escapeStaffBooking(
                                            $patient['email']
                                        ) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Available Appointment Time
                            </label>

                            <select
                                id="slotChoice"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Choose an available time
                                </option>

                                <?php foreach ($availableSlots as $index => $slot): ?>

                                    <option
                                        value="<?= (int) $index ?>"
                                        data-availability-id="<?= (int) $slot['availability_id'] ?>"
                                        data-date="<?= escapeStaffBooking(
                                            $slot['appointment_date']
                                        ) ?>"
                                        data-start="<?= escapeStaffBooking(
                                            $slot['start_time']
                                        ) ?>"
                                    >
                                        <?= escapeStaffBooking(
                                            $slot['appointment_date']
                                        ) ?>
                                        |
                                        <?= escapeStaffBooking(
                                            $slot['start_time']
                                        ) ?>
                                        -
                                        <?= escapeStaffBooking(
                                            $slot['end_time']
                                        ) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Confirm Appointment Booking
                        </button>

                    </form>

                <?php endif; ?>

            </div>
        </div>

    <?php endif; ?>

    <a
        href="appointments.php"
        class="btn btn-secondary mt-4"
    >
        Back to Appointment Management
    </a>

</div>

<script>
const bookingForm = document.getElementById('staffBookingForm');

if (bookingForm) {

    bookingForm.addEventListener('submit', function (event) {

        const slotSelect =
            document.getElementById('slotChoice');

        const selected =
            slotSelect.options[slotSelect.selectedIndex];

        if (!selected.value) {
            event.preventDefault();
            return;
        }

        document.getElementById('availabilityId').value =
            selected.dataset.availabilityId;

        document.getElementById('appointmentDate').value =
            selected.dataset.date;

        document.getElementById('appointmentStartTime').value =
            selected.dataset.start;

    });
}
</script>

</body>
</html>
