<?php


/**
 * Staff Appointment Management
 * View appointments and access booking,
 * cancellation, and rescheduling.
 */


require_once __DIR__ . '/../config/auth.php';


requireRole(['receptionist', 'administrator']);


require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_appointment_repository.php';


$appointments = getStaffAppointments($conn);


$conn->close();


// Read one-time feedback from appointment actions.
$success = $_SESSION['staff_appointment_success'] ?? '';
$error = $_SESSION['staff_appointment_error'] ?? '';


unset(
    $_SESSION['staff_appointment_success'],
    $_SESSION['staff_appointment_error']
);


function escapeStaffAppointment($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}


$now = new DateTimeImmutable('now');
$totalAppointments = count($appointments);


?>


<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>
        Appointment Management | Clinic Appointment System
    </title>


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


        .appointment-card {
            background: white;
            border: 1px solid #e1ebf0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px
                rgba(25, 71, 103, 0.06);
        }


        .card-heading {
            padding: 24px;
            border-bottom: 1px solid #e7eef3;
        }


        .appointment-table {
            margin-bottom: 0;
            vertical-align: middle;
        }


        .appointment-table thead th {
            background: #eef5f8;
            color: #294b67;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 16px;
            white-space: nowrap;
            border-bottom: none;
        }


        .appointment-table tbody td {
            padding: 16px;
            border-color: #eef2f5;
        }


        .appointment-table tbody tr:hover td {
            background: #f6fbfc;
        }


        .patient-name {
            font-weight: 700;
            color: #173e61;
        }


        .status-badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: capitalize;
        }


        .status-confirmed {
            color: #167347;
            background: #e0f5ea;
        }


        .status-cancelled {
            color: #a52d35;
            background: #fde8e8;
        }


        .status-other {
            color: #765b21;
            background: #fff3d6;
        }


        .book-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #087d85;
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 700;
            min-height: 46px;
        }


        .book-button:hover {
            background: #08656b;
            color: white;
            text-decoration: none;
        }


        .action-button {
            display: inline-block;
            border-radius: 8px;
            padding: 8px 13px;
            font-size: 0.82rem;
            font-weight: 700;
            white-space: nowrap;
            text-decoration: none;
        }


        .cancel-button {
            color: #a52d35;
            background: #fde8e8;
            border: 1px solid #f7cccc;
        }


        .cancel-button:hover {
            background: #ba3440;
            color: white;
            text-decoration: none;
        }


        .reschedule-button {
            color: #087d85;
            background: #e6f4f4;
            border: 1px solid #c6e5e5;
        }


        .reschedule-button:hover {
            background: #087d85;
            color: white;
            text-decoration: none;
        }


        .action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }


        .empty-state {
            padding: 55px 20px;
            text-align: center;
            color: #71869a;
        }


        @media (max-width: 576px) {
            .page-title {
                font-size: 1.65rem;
            }


            .card-heading {
                padding: 18px;
            }


            .book-button {
                width: 100%;
            }


            .appointment-table thead th,
            .appointment-table tbody td {
                padding: 12px;
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


<main class="container py-5">


    <a
        href="../dashboards/admin-dashboard.php"
        class="back-link"
    >
        &larr; Back to Dashboard
    </a>


    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mt-4 mb-4">


        <div>
            <h1 class="page-title mb-2">
                Appointment Management
            </h1>


            <p class="text-muted mb-0">
                Manage patient appointments, bookings,
                cancellations and rescheduling.
            </p>
        </div>


        <a
            href="appointment-book.php"
            class="book-button"
        >
            + Book Appointment
        </a>


    </div>


    <?php if ($success !== ''): ?>


        <div
            class="alert alert-success"
            role="status"
        >
            <?= escapeStaffAppointment($success) ?>
        </div>


    <?php endif; ?>


    <?php if ($error !== ''): ?>


        <div
            class="alert alert-danger"
            role="alert"
        >
            <?= escapeStaffAppointment($error) ?>
        </div>


    <?php endif; ?>


    <section class="appointment-card">


        <div class="card-heading">


            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">


                <div>
                    <h2
                        class="h5 fw-bold mb-1"
                        style="color: #173e61;"
                    >
                        Appointment Records
                    </h2>


                    <p class="text-muted small mb-0">
                        View appointment details and
                        available management actions.
                    </p>
                </div>


                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <?= $totalAppointments ?> Records
                </span>


            </div>


        </div>


        <div class="table-responsive">


            <table class="table appointment-table">


                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Patient</th>
                        <th scope="col">Doctor</th>
                        <th scope="col">Date</th>
                        <th scope="col">Start Time</th>
                        <th scope="col">End Time</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>


                <tbody>


                    <?php foreach ($appointments as $appointment): ?>


                        <?php


                        $appointmentStart = new DateTimeImmutable(
                            $appointment['appointment_date'] . ' ' .
                            $appointment['start_time']
                        );


                        $status = strtolower(
                            (string) $appointment['status']
                        );


                        // Staff may open cancellation for a
                        // future confirmed appointment.
                        // Late cancellations require a reason.
                        $canCancel = (
                            $status === 'confirmed' &&
                            $appointmentStart > $now
                        );


                        // Preserve the existing two-hour
                        // rescheduling deadline.
                        $canReschedule = (
                            $status === 'confirmed' &&
                            $now <= $appointmentStart->modify('-2 hours')
                        );


                        $statusClass = match ($status) {
                            'confirmed' => 'status-confirmed',
                            'cancelled' => 'status-cancelled',
                            default => 'status-other'
                        };


                        ?>


                        <tr>


                            <td>
                                #<?= (int) $appointment['id'] ?>
                            </td>


                            <td class="patient-name">
                                <?= escapeStaffAppointment(
                                    $appointment['patient_name']
                                ) ?>
                            </td>


                            <td>
                                <?= escapeStaffAppointment(
                                    $appointment['doctor_name']
                                ) ?>
                            </td>


                            <td>
                                <?= escapeStaffAppointment(
                                    $appointment['appointment_date']
                                ) ?>
                            </td>


                            <td>
                                <?= escapeStaffAppointment(
                                    substr(
                                        (string) $appointment['start_time'],
                                        0,
                                        5
                                    )
                                ) ?>
                            </td>


                            <td>
                                <?= escapeStaffAppointment(
                                    substr(
                                        (string) $appointment['end_time'],
                                        0,
                                        5
                                    )
                                ) ?>
                            </td>


                            <td>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= escapeStaffAppointment($status) ?>
                                </span>
                            </td>


                            <td>


                                <div class="action-group">


                                    <?php if ($canCancel): ?>


                                        <a
                                            href="appointment-cancel.php?id=<?= (int) $appointment['id'] ?>"
                                            class="action-button cancel-button"
                                        >
                                            Cancel
                                        </a>


                                    <?php endif; ?>


                                    <?php if ($canReschedule): ?>


                                        <a
                                            href="appointment-reschedule.php?id=<?= (int) $appointment['id'] ?>"
                                            class="action-button reschedule-button"
                                        >
                                            Reschedule
                                        </a>


                                    <?php endif; ?>


                                    <?php if (!$canCancel && !$canReschedule): ?>


                                        <span class="text-muted small">
                                            No actions available
                                        </span>


                                    <?php endif; ?>


                                </div>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                    <?php if (empty($appointments)): ?>


                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <div class="fs-2 mb-2">📅</div>


                                    <strong>
                                        No appointments found
                                    </strong>


                                    <p class="small mt-2 mb-0">
                                        New appointments will
                                        appear here after booking.
                                    </p>
                                </div>
                            </td>
                        </tr>


                    <?php endif; ?>


                </tbody>


            </table>


        </div>


    </section>


    <p class="text-muted small mt-3">
        Actions are available according to
        appointment status and scheduling rules.
    </p>


</main>


</body>
</html>