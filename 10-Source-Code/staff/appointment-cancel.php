

<?php


/**
 * Staff Appointment Cancellation
 * Frontend interface for authorized clinic staff.
 */


require_once __DIR__ . '/../config/auth.php';


requireRole(['receptionist', 'administrator']);


require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';


// Validate the appointment ID.
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
        a.appointment_date,
        a.start_time,
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
$conn->close();


if (!$appointment) {
    http_response_code(404);
    exit('Appointment not found.');
}


// Preserve the existing CSRF protection.
if (empty($_SESSION['staff_appointment_csrf_token'])) {
    $_SESSION['staff_appointment_csrf_token'] =
        bin2hex(random_bytes(32));
}


// Calculate cancellation eligibility.
$appointmentStart = new DateTimeImmutable(
    $appointment['appointment_date'] . ' ' .
    $appointment['start_time']
);


$now = new DateTimeImmutable('now');


$canCancel = (
    $appointment['status'] === 'confirmed' &&
    $appointmentStart > $now
);


// A reason is required for cancellations
// within the final 24 hours.
$isLate = (
    $canCancel &&
    $now > $appointmentStart->modify('-24 hours')
);


function staffCancelEscape($value): string
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


    <title>Cancel Appointment | Clinic Appointment System</title>


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
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 700;
        }


        .staff-brand:hover {
            color: #a3eee1;
            text-decoration: none;
        }


        .cancel-container {
            max-width: 850px;
        }


        .page-title {
            color: #173e61;
            font-weight: 700;
        }


        .back-link {
            color: #087d85;
            font-weight: 700;
        }


        .back-link:hover {
            color: #075a60;
        }


        .cancel-card {
            background: #ffffff;
            border: 1px solid #e1ebf0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px
                rgba(25, 71, 103, 0.07);
        }


        .cancel-card-header {
            background: #f4f9fb;
            padding: 24px 28px;
            border-bottom: 1px solid #e5edf2;
        }


        .cancel-card-header h2 {
            color: #173e61;
            font-size: 1.15rem;
            font-weight: 700;
        }


        .cancel-card-body {
            padding: 28px;
        }


        .detail-label {
            display: block;
            color: #718498;
            font-size: 0.84rem;
            margin-bottom: 5px;
        }


        .detail-value {
            color: #173e61;
            font-size: 0.97rem;
            font-weight: 600;
            overflow-wrap: anywhere;
        }


        .details-grid {
            background: #f8fbfc;
            border: 1px solid #e7eef2;
            border-radius: 14px;
            padding: 24px;
        }


        .section-heading {
            color: #173e61;
            font-size: 1rem;
            font-weight: 700;
        }


        .status-badge {
            display: inline-block;
            border-radius: 20px;
            padding: 7px 13px;
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


        .warning-panel {
            background: #fff7e6;
            border: 1px solid #f3db9e;
            color: #79571a;
            border-radius: 12px;
            padding: 18px;
        }


        .notice-panel {
            background: #edf8f7;
            border: 1px solid #d5eeea;
            color: #376a66;
            border-radius: 12px;
            padding: 18px;
        }


        .form-label {
            color: #344e63;
            font-weight: 600;
        }


        .form-control {
            border: 1px solid #d5e0e8;
            border-radius: 10px;
            padding: 12px 14px;
        }


        .form-control:focus {
            border-color: #16888e;
            box-shadow: 0 0 0 0.2rem
                rgba(22, 136, 142, 0.13);
        }


        .reason-input {
            min-height: 115px;
            resize: vertical;
        }


        .btn-cancel-appointment {
            background: #bd3945;
            border: 1px solid #bd3945;
            color: #ffffff;
            border-radius: 10px;
            padding: 12px 22px;
            font-weight: 700;
        }


        .btn-cancel-appointment:hover {
            background: #a52d38;
            border-color: #a52d38;
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


        @media (max-width: 576px) {
            .page-title {
                font-size: 1.65rem;
            }


            .cancel-card-header,
            .cancel-card-body {
                padding: 20px;
            }


            .details-grid {
                padding: 18px;
            }


            .btn-cancel-appointment,
            .btn-back {
                width: 100%;
                text-align: center;
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


<main class="container cancel-container py-5">


    <a
        href="appointments.php"
        class="back-link"
    >
        &larr; Back to Appointments
    </a>


    <div class="mt-4 mb-4">


        <h1 class="page-title">
            Cancel Appointment
        </h1>


        <p class="text-muted">
            Review the appointment details before
            submitting a cancellation request.
        </p>


    </div>


    <section class="cancel-card">


        <div class="cancel-card-header">


            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">


                <div>
                    <h2 class="mb-1">
                        Appointment Details
                    </h2>


                    <p class="text-muted small mb-0">
                        Confirm that you have selected
                        the correct appointment.
                    </p>
                </div>


                <span class="badge bg-primary rounded-pill px-3 py-2">
                    Appointment #<?= (int) $appointment['id'] ?>
                </span>


            </div>


        </div>


        <div class="cancel-card-body">


            <?php
            $status = strtolower(
                (string) $appointment['status']
            );


            $statusClass = match ($status) {
                'confirmed' => 'status-confirmed',
                'cancelled' => 'status-cancelled',
                default => 'status-other'
            };
            ?>


            <div class="details-grid mb-4">


                <div class="row g-4">


                    <div class="col-12 col-md-6">


                        <span class="detail-label">
                            Patient
                        </span>


                        <div class="detail-value">
                            <?= staffCancelEscape(
                                $appointment['patient_name']
                            ) ?>
                        </div>


                    </div>


                    <div class="col-12 col-md-6">


                        <span class="detail-label">
                            Doctor
                        </span>


                        <div class="detail-value">
                            <?= staffCancelEscape(
                                $appointment['doctor_name']
                            ) ?>
                        </div>


                    </div>


                    <div class="col-12 col-md-6">


                        <span class="detail-label">
                            Appointment Date
                        </span>


                        <div class="detail-value">
                            <?= staffCancelEscape(
                                $appointment['appointment_date']
                            ) ?>
                        </div>


                    </div>


                    <div class="col-12 col-md-6">


                        <span class="detail-label">
                            Appointment Time
                        </span>


                        <div class="detail-value">
                            <?= staffCancelEscape(
                                substr($appointment['start_time'], 0, 5)
                            ) ?>
                        </div>


                    </div>


                    <div class="col-12">


                        <span class="detail-label">
                            Appointment Status
                        </span>


                        <span class="status-badge <?= $statusClass ?>">
                            <?= staffCancelEscape($status) ?>
                        </span>


                    </div>


                </div>


            </div>


            <?php if (!$canCancel): ?>


                <div class="warning-panel" role="alert">


                    <strong>Cancellation unavailable</strong>


                    <p class="mb-0 mt-2">
                        This appointment cannot be cancelled
                        online because it is not confirmed
                        or the scheduled time has passed.
                    </p>


                </div>


            <?php else: ?>


                <h3 class="section-heading mb-3">
                    Cancellation Request
                </h3>


                <?php if ($isLate): ?>


                    <div
                        class="warning-panel mb-4"
                        role="alert"
                    >


                        <strong>
                            Cancellation within 24 hours
                        </strong>


                        <p class="mb-0 mt-2">
                            This appointment is scheduled
                            within the next 24 hours.
                            A cancellation reason is required
                            before the request can proceed.
                        </p>


                    </div>


                <?php else: ?>


                    <div class="notice-panel mb-4">


                        <strong>
                            Please review before continuing
                        </strong>


                        <p class="mb-0 mt-2">
                            Cancelling this appointment
                            will release the reserved time.
                            The appointment will remain
                            in the patient's history.
                        </p>


                    </div>


                <?php endif; ?>


                <form
                    method="POST"
                    action="../actions/staff_appointment_cancel.php"
                    onsubmit="return confirm('Are you sure you want to cancel this appointment?');"
                >


                    <input
                        type="hidden"
                        name="appointment_id"
                        value="<?= (int) $appointment['id'] ?>"
                    >


                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= staffCancelEscape(
                            $_SESSION['staff_appointment_csrf_token']
                        ) ?>"
                    >


                    <div class="mb-4">


                        <label
                            for="cancellationReason"
                            class="form-label"
                        >
                            Cancellation Reason


                            <?php if ($isLate): ?>
                                <span class="text-danger">*</span>
                            <?php else: ?>
                                <span class="text-muted fw-normal">
                                    (Optional)
                                </span>
                            <?php endif; ?>


                        </label>


                        <textarea
                            id="cancellationReason"
                            name="reason"
                            class="form-control reason-input"
                            maxlength="500"
                            rows="4"
                            placeholder="Enter the reason for cancellation"
                            <?= $isLate ? 'required' : '' ?>
                        ></textarea>


                        <div class="form-text">
                            <?= $isLate
                                ? 'A reason is mandatory for cancellations within 24 hours.'
                                : 'You may provide a reason for the clinic records.' ?>
                        </div>


                    </div>


                    <div class="d-flex flex-wrap gap-3">


                        <button
                            type="submit"
                            class="btn btn-cancel-appointment"
                        >
                            Confirm Cancellation
                        </button>


                        <a
                            href="appointments.php"
                            class="btn btn-back"
                        >
                            Keep Appointment
                        </a>


                    </div>


                </form>


            <?php endif; ?>


        </div>


    </section>


    <p class="text-muted small text-center mt-4">
        Clinic Appointment System
        &middot;
        Appointment Management
    </p>


</main>


</body>
</html>