<?php


/**
 * Staff Appointment Booking
 * Secure frontend connected to the existing booking backend.
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


// Validate the selected doctor.
$doctorInput = $_GET['doctor_id'] ?? null;


$doctorId = is_string($doctorInput)
    ? filter_var(
        $doctorInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;


$doctorId = is_int($doctorId) ? $doctorId : 0;


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


// Retrieve actual future appointment slots.
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


    $availableSlots = array_slice(
        $availableSlots,
        0,
        100
    );
}


$conn->close();


// Preserve booking CSRF protection.
if (empty($_SESSION['staff_booking_csrf_token'])) {
    $_SESSION['staff_booking_csrf_token'] =
        bin2hex(random_bytes(32));
}


// Read and clear the previous error.
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


    <title>Book Appointment | Clinic Appointment System</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        href="../assets/css/style.css"
        rel="stylesheet"
    >


    <style>
        .staff-navbar {
            background: #13385b;
            padding: 18px 0;
        }


        .staff-brand {
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
        }


        .staff-brand:hover {
            color: #a3eee1;
            text-decoration: none;
        }


        .booking-container {
            max-width: 900px;
        }


        .page-title {
            color: #173e61;
            font-weight: 700;
        }


        .back-link {
            color: #087d85;
            font-weight: 700;
        }


        .booking-card {
            background: #ffffff;
            border: 1px solid #e1ebf0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(25, 71, 103, 0.07);
        }


        .booking-card-header {
            background: #f4f9fb;
            padding: 24px 28px;
            border-bottom: 1px solid #e5edf2;
        }


        .booking-card-header h2 {
            color: #173e61;
            font-size: 1.15rem;
            font-weight: 700;
        }


        .booking-card-body {
            padding: 28px;
        }


        .step-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e4f5f3;
            color: #087d85;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            font-weight: 700;
            flex-shrink: 0;
        }


        .form-label {
            color: #344e63;
            font-size: 0.92rem;
            font-weight: 600;
        }


        .form-select {
            min-height: 49px;
            border: 1px solid #d5e0e8;
            border-radius: 10px;
            padding: 10px 14px;
        }


        .form-select:focus {
            border-color: #16888e;
            box-shadow: 0 0 0 0.2rem rgba(22, 136, 142, 0.13);
        }


        .btn-teal {
            background: #087d85;
            border: 1px solid #087d85;
            color: #ffffff;
            border-radius: 10px;
            padding: 12px 22px;
            font-weight: 700;
        }


        .btn-teal:hover {
            background: #08656b;
            border-color: #08656b;
            color: #ffffff;
        }


        .btn-back {
            background: #eef3f6;
            color: #38536a;
            border-radius: 10px;
            padding: 12px 22px;
            font-weight: 700;
        }


        .btn-back:hover {
            background: #dfe9ef;
            color: #173e61;
        }


        .booking-note {
            background: #edf8f7;
            border: 1px solid #d5eeea;
            color: #376a66;
            border-radius: 12px;
            padding: 16px 18px;
            font-size: 0.9rem;
        }


        .empty-state {
            padding: 18px;
            background: #fff8e5;
            border: 1px solid #f2e5b4;
            border-radius: 12px;
            color: #80651f;
        }


        @media (max-width: 576px) {
            .booking-card-header,
            .booking-card-body {
                padding: 20px;
            }


            .page-title {
                font-size: 1.6rem;
            }


            .btn-teal,
            .btn-back {
                width: 100%;
            }
        }
    </style>


</head>


<body>


<nav class="staff-navbar">
    <div class="container">


        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">


            <a
                href="../dashboards/admin-dashboard.php"
                class="staff-brand"
            >
                🏥 Clinic Appointment System
            </a>


            <a
                href="../logout.php"
                class="btn btn-outline-light btn-sm"
            >
                Logout
            </a>


        </div>


    </div>
</nav>


<main class="container booking-container py-5">


    <a
        href="appointments.php"
        class="back-link"
    >
        &larr; Back to Appointments
    </a>


    <div class="mt-4 mb-4">


        <h1 class="page-title">
            Book an Appointment
        </h1>


        <p class="text-muted">
            Schedule an appointment for a
            registered patient.
        </p>


    </div>


    <?php if ($error !== ''): ?>


        <div class="alert alert-danger" role="alert">
            <?= escapeStaffBooking($error) ?>
        </div>


    <?php endif; ?>


    <section class="booking-card mb-4">


        <div class="booking-card-header">


            <div class="d-flex align-items-center gap-3">


                <div class="step-icon" aria-hidden="true">
                    1
                </div>


                <div>
                    <h2 class="mb-1">
                        Select a Doctor
                    </h2>


                    <p class="text-muted small mb-0">
                        Choose the doctor to view available times.
                    </p>
                </div>


            </div>


        </div>


        <div class="booking-card-body">


            <?php if (empty($doctors)): ?>


                <div class="empty-state">
                    No active doctors are currently available.
                </div>


            <?php else: ?>


                <form method="GET" action="appointment-book.php">


                    <div class="mb-3">


                        <label
                            for="doctorSelect"
                            class="form-label"
                        >
                            Doctor
                        </label>


                        <select
                            id="doctorSelect"
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
                        class="btn btn-teal"
                    >
                        Show Available Times
                    </button>


                </form>


            <?php endif; ?>


        </div>


    </section>


    <?php if ($doctorId > 0): ?>


        <section class="booking-card mb-4">


            <div class="booking-card-header">


                <div class="d-flex align-items-center gap-3">


                    <div class="step-icon" aria-hidden="true">
                        2
                    </div>


                    <div>
                        <h2 class="mb-1">
                            Select Patient and Time
                        </h2>


                        <p class="text-muted small mb-0">
                            Choose a patient and an available appointment.
                        </p>
                    </div>


                </div>


            </div>


            <div class="booking-card-body">


                <?php if (empty($patients)): ?>


                    <div class="empty-state">
                        No active patient accounts were found.
                    </div>


                <?php elseif (empty($availableSlots)): ?>


                    <div class="empty-state">
                        No future appointment times are available
                        for this doctor. Please choose another doctor
                        or add availability through the doctor account.
                    </div>


                <?php else: ?>


                    <div class="booking-note mb-4">


                        <strong>
                            <?= count($availableSlots) ?>
                            available time option(s)
                        </strong>


                        <div class="small mt-1">
                            Only unbooked future slots are shown.
                        </div>


                    </div>


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
                            value="<?= (int) $doctorId ?>"
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


                        <div class="row g-3">


                            <div class="col-12">


                                <label
                                    for="patientSelect"
                                    class="form-label"
                                >
                                    Patient
                                </label>


                                <select
                                    id="patientSelect"
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


                            <div class="col-12">


                                <label
                                    for="slotChoice"
                                    class="form-label"
                                >
                                    Available Appointment Time
                                </label>


                                <select
                                    id="slotChoice"
                                    class="form-select"
                                    required
                                >


                                    <option value="">
                                        Choose an available date and time
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
                                                substr($slot['start_time'], 0, 5)
                                            ) ?>


                                            –


                                            <?= escapeStaffBooking(
                                                substr($slot['end_time'], 0, 5)
                                            ) ?>
                                        </option>


                                    <?php endforeach; ?>


                                </select>


                            </div>


                        </div>


                        <div class="booking-note mt-4">


                            <strong>Booking confirmation</strong>


                            <p class="small mb-0 mt-1">
                                Check the patient, doctor and
                                appointment time before confirming.
                                The appointment will be saved
                                using the clinic's booking rules.
                            </p>


                        </div>


                        <div class="d-flex flex-wrap gap-3 mt-4">


                            <button
                                type="submit"
                                class="btn btn-teal"
                            >
                                Confirm Appointment Booking
                            </button>


                            <a
                                href="appointments.php"
                                class="btn btn-back"
                            >
                                Cancel / Back
                            </a>


                        </div>


                    </form>


                <?php endif; ?>


            </div>


        </section>


    <?php endif; ?>


</main>


<script>
const bookingForm = document.getElementById('staffBookingForm');


if (bookingForm) {


    const slotSelect = document.getElementById('slotChoice');


    bookingForm.addEventListener('submit', function (event) {


        const selected =
            slotSelect.options[slotSelect.selectedIndex];


        if (!selected || selected.value === '') {
            event.preventDefault();
            slotSelect.focus();
            return;
        }


        document.getElementById('availabilityId').value =
            selected.dataset.availabilityId || '';


        document.getElementById('appointmentDate').value =
            selected.dataset.date || '';


        document.getElementById('appointmentStartTime').value =
            selected.dataset.start || '';


    });
}
</script>


</body>
</html>

