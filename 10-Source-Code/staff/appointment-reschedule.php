

<?php


/**
 * Staff Appointment Rescheduling
 * Secure clinic staff frontend.
 */


require_once __DIR__ . '/../config/auth.php';


requireRole(['receptionist', 'administrator']);


require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/doctor_repository.php';
require_once __DIR__ . '/../includes/appointment_repository.php';


// Validate appointment ID.
$idInput = $_GET['id'] ?? null;


$appointmentId = is_string($idInput)
    ? filter_var(
        $idInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;


if (!is_int($appointmentId)) {
    http_response_code(400);
    exit('Invalid appointment ID.');
}


// Retrieve the selected appointment.
$sql = "
    SELECT
        a.id,
        a.patient_id,
        a.doctor_id,
        a.appointment_date,
        a.start_time,
        a.end_time,
        a.status,
        p.full_name AS patient_name,
        d.full_name AS doctor_name
    FROM appointments AS a
    INNER JOIN users AS p
        ON p.id = a.patient_id
    INNER JOIN users AS d
        ON d.id = a.doctor_id
    WHERE a.id = ?
    LIMIT 1
";


$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $appointmentId);
$stmt->execute();


$appointment = $stmt->get_result()->fetch_assoc();
$stmt->close();


if (!$appointment) {
    $conn->close();
    http_response_code(404);
    exit('Appointment not found.');
}


// Follow the existing rescheduling rule.
$originalStart = new DateTimeImmutable(
    $appointment['appointment_date'] . ' ' .
    $appointment['start_time']
);


$now = new DateTimeImmutable('now');


$deadline = $originalStart->modify('-2 hours');


$canReschedule = (
    $appointment['status'] === 'confirmed' &&
    $now <= $deadline
);


// Retrieve real available slots for the SAME doctor.
$availableSlots = [];


if ($canReschedule) {


    $doctorId = (int) $appointment['doctor_id'];


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


// Preserve CSRF protection.
if (empty($_SESSION['staff_reschedule_csrf_token'])) {
    $_SESSION['staff_reschedule_csrf_token'] =
        bin2hex(random_bytes(32));
}


// Read and clear previous error messages.
$error = $_SESSION['staff_reschedule_error'] ?? '';


unset($_SESSION['staff_reschedule_error']);


function escapeStaffReschedule($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}


$status = strtolower((string) $appointment['status']);


?>


<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>Reschedule Appointment | Clinic Appointment System</title>


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


        .reschedule-container {
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


        .reschedule-card {
            background: white;
            border: 1px solid #e1ebf0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px
                rgba(25, 71, 103, 0.07);
        }


        .card-heading {
            background: #f4f9fb;
            padding: 24px 28px;
            border-bottom: 1px solid #e5edf2;
        }


        .card-heading h2 {
            color: #173e61;
            font-size: 1.15rem;
            font-weight: 700;
        }


        .card-content {
            padding: 28px;
        }


        .details-grid {
            background: #f8fbfc;
            border: 1px solid #e7eef2;
            border-radius: 14px;
            padding: 24px;
        }


        .detail-label {
            color: #718498;
            font-size: 0.84rem;
            margin-bottom: 5px;
        }


        .detail-value {
            color: #173e61;
            font-weight: 600;
            overflow-wrap: anywhere;
        }


        .section-title {
            color: #173e61;
            font-size: 1.05rem;
            font-weight: 700;
        }


        .status-badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: capitalize;
        }


        .status-confirmed {
            background: #e0f5ea;
            color: #167347;
        }


        .status-cancelled {
            background: #fde8e8;
            color: #a52d35;
        }


        .status-other {
            background: #fff3d6;
            color: #986600;
        }


        .form-label {
            color: #344e63;
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
            box-shadow: 0 0 0 0.2rem
                rgba(22, 136, 142, 0.13);
        }


        .info-panel {
            background: #edf8f7;
            border: 1px solid #d5eeea;
            border-radius: 12px;
            padding: 17px;
            color: #376a66;
        }


        .warning-panel {
            background: #fff7e6;
            border: 1px solid #f3db9e;
            border-radius: 12px;
            padding: 18px;
            color: #79571a;
        }


        .btn-save {
            background: #087d85;
            color: white;
            border: none;
            border-radius: 10px;
            padding: 12px 22px;
            font-weight: 700;
        }


        .btn-save:hover {
            background: #08656b;
            color: white;
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


        @media (max-width: 576px) {
            .page-title {
                font-size: 1.6rem;
            }


            .card-heading,
            .card-content {
                padding: 20px;
            }


            .details-grid {
                padding: 18px;
            }


            .btn-save,
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


<main class="container reschedule-container py-5">


    <a href="appointments.php" class="back-link">
        &larr; Back to Appointments
    </a>


    <div class="mt-4 mb-4">


        <h1 class="page-title">
            Reschedule Appointment
        </h1>


        <p class="text-muted">
            Review the current appointment and
            choose an available new time.
        </p>


    </div>


    <?php if ($error !== ''): ?>


        <div class="alert alert-danger" role="alert">
            <?= escapeStaffReschedule($error) ?>
        </div>


    <?php endif; ?>


    <section class="reschedule-card">


        <div class="card-heading">


            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">


                <div>
                    <h2 class="mb-1">
                        Current Appointment
                    </h2>


                    <p class="small text-muted mb-0">
                        Verify the appointment before rescheduling.
                    </p>
                </div>


                <span class="badge bg-primary rounded-pill px-3 py-2">
                    Appointment #<?= (int) $appointment['id'] ?>
                </span>


            </div>


        </div>


        <div class="card-content">


            <div class="details-grid mb-4">


                <div class="row g-4">


                    <div class="col-12 col-md-6">
                        <div class="detail-label">Patient</div>
                        <div class="detail-value">
                            <?= escapeStaffReschedule(
                                $appointment['patient_name']
                            ) ?>
                        </div>
                    </div>


                    <div class="col-12 col-md-6">
                        <div class="detail-label">Doctor</div>
                        <div class="detail-value">
                            <?= escapeStaffReschedule(
                                $appointment['doctor_name']
                            ) ?>
                        </div>
                    </div>


                    <div class="col-12 col-md-6">
                        <div class="detail-label">Current Date</div>
                        <div class="detail-value">
                            <?= escapeStaffReschedule(
                                $appointment['appointment_date']
                            ) ?>
                        </div>
                    </div>


                    <div class="col-12 col-md-6">
                        <div class="detail-label">Current Time</div>
                        <div class="detail-value">
                            <?= escapeStaffReschedule(
                                substr($appointment['start_time'], 0, 5)
                            ) ?>
                            –
                            <?= escapeStaffReschedule(
                                substr($appointment['end_time'], 0, 5)
                            ) ?>
                        </div>
                    </div>


                    <div class="col-12">


                        <div class="detail-label">
                            Appointment Status
                        </div>


                        <span class="status-badge <?= $status === 'confirmed'
                            ? 'status-confirmed'
                            : ($status === 'cancelled'
                                ? 'status-cancelled'
                                : 'status-other') ?>">
                            <?= escapeStaffReschedule($status) ?>
                        </span>


                    </div>


                </div>


            </div>


            <?php if (!$canReschedule): ?>


                <div class="warning-panel" role="alert">


                    <strong>
                        Rescheduling unavailable
                    </strong>


                    <p class="mb-0 mt-2">
                        This appointment cannot be rescheduled.
                        It may already be cancelled or past
                        the permitted rescheduling deadline.
                        Please contact the clinic if assistance
                        is needed.
                    </p>


                </div>


            <?php elseif (empty($availableSlots)): ?>


                <div class="warning-panel" role="alert">


                    <strong>
                        No alternative times available
                    </strong>


                    <p class="mb-0 mt-2">
                        There are currently no available
                        alternative appointment slots
                        for this doctor.
                        Please check again later.
                    </p>


                </div>


            <?php else: ?>


                <h3 class="section-title mb-3">
                    Choose a New Appointment Time
                </h3>


                <div class="info-panel mb-4">


                    <strong>
                        <?= count($availableSlots) ?>
                        available time option(s)
                    </strong>


                    <p class="small mb-0 mt-1">
                        The appointment will remain with
                        the same doctor. Only available
                        future time slots are shown.
                    </p>


                </div>


                <form
                    id="staffRescheduleForm"
                    method="POST"
                    action="../actions/staff_appointment_reschedule.php"
                >


                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escapeStaffReschedule(
                            $_SESSION['staff_reschedule_csrf_token']
                        ) ?>"
                    >


                    <input
                        type="hidden"
                        name="appointment_id"
                        value="<?= (int) $appointment['id'] ?>"
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


                    <div class="mb-4">


                        <label
                            for="slotChoice"
                            class="form-label"
                        >
                            New Available Time
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
                                    data-date="<?= escapeStaffReschedule(
                                        $slot['appointment_date']
                                    ) ?>"
                                    data-start="<?= escapeStaffReschedule(
                                        $slot['start_time']
                                    ) ?>"
                                >
                                    <?= escapeStaffReschedule(
                                        $slot['appointment_date']
                                    ) ?>


                                    |


                                    <?= escapeStaffReschedule(
                                        substr($slot['start_time'], 0, 5)
                                    ) ?>


                                    –


                                    <?= escapeStaffReschedule(
                                        substr($slot['end_time'], 0, 5)
                                    ) ?>
                                </option>


                            <?php endforeach; ?>


                        </select>


                        <div class="form-text">
                            Select a different time for this appointment.
                        </div>


                    </div>


                    <div class="info-panel mb-4">


                        <strong>
                            Please review your selection
                        </strong>


                        <p class="small mb-0 mt-1">
                            The system will check availability
                            again before saving the new
                            appointment time.
                        </p>


                    </div>


                    <div class="d-flex flex-wrap gap-3">


                        <button
                            type="submit"
                            class="btn btn-save"
                        >
                            Confirm Rescheduling
                        </button>


                        <a
                            href="appointments.php"
                            class="btn btn-back"
                        >
                            Keep Current Appointment
                        </a>


                    </div>


                </form>


            <?php endif; ?>


        </div>


    </section>


</main>


<script>
const rescheduleForm =
    document.getElementById('staffRescheduleForm');


if (rescheduleForm) {


    rescheduleForm.addEventListener(
        'submit',
        function (event) {


            const slotSelect =
                document.getElementById('slotChoice');


            const selected =
                slotSelect.options[slotSelect.selectedIndex];


            if (!selected || selected.value === '') {
                event.preventDefault();
                slotSelect.focus();
                return;
            }


            document.getElementById(
                'availabilityId'
            ).value = selected.dataset.availabilityId || '';


            document.getElementById(
                'appointmentDate'
            ).value = selected.dataset.date || '';


            document.getElementById(
                'appointmentStartTime'
            ).value = selected.dataset.start || '';


        }
    );
}
</script>


</body>
</html>