
<?php

/**
 * Sprint 5 — US-19: Appointment Management
 * Staff appointment list with cancellation links.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_appointment_repository.php';

$appointments = getStaffAppointments($conn);

$conn->close();

// Display messages returned by staff appointment actions.
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

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Appointment Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body>

<div class="container mt-5">

    <h2>Appointment Management</h2>

    <p class="text-muted">
        View and manage clinic appointments.
    </p>
    
<div class="mb-3">
    <a
        href="appointment-book.php"
        class="btn btn-success"
    >
        + Book Appointment for Patient
    </a>
</div>


    <?php if ($success !== ''): ?>
        <div class="alert alert-success">
            <?= escapeStaffAppointment($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= escapeStaffAppointment($error) ?>
        </div>
    <?php endif; ?>

    <div class="table-responsive">

        <table class="table table-bordered table-striped">

            <thead>
                <tr>
                    <th>Appointment ID</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($appointments as $appointment): ?>

                    <?php
                    $appointmentStart = new DateTimeImmutable(
                        $appointment['appointment_date'] . ' ' .
                        $appointment['start_time']
                    );

                    $canCancel = (
                        $appointment['status'] === 'confirmed' &&
                        $appointmentStart > $now
                    );
                    $canReschedule = (
    $appointment['status'] === 'confirmed' &&
    $now <= $appointmentStart->modify('-2 hours')
);
                    ?>

                    <tr>

                        <td>
                            <?= escapeStaffAppointment(
                                $appointment['id']
                            ) ?>
                        </td>

                        <td>
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
                                $appointment['start_time']
                            ) ?>
                        </td>

                        <td>
                            <?= escapeStaffAppointment(
                                $appointment['end_time']
                            ) ?>
                        </td>

                        <td>
                            <?= escapeStaffAppointment(
                                $appointment['status']
                            ) ?>
                        </td>

                        
<td>
    <?php if ($canCancel): ?>

        <a
            href="appointment-cancel.php?id=<?= (int) $appointment['id'] ?>"
            class="btn btn-sm btn-danger"
        >
            Cancel
        </a>

    <?php endif; ?>

    <?php if ($canReschedule): ?>

        <a
            href="appointment-reschedule.php?id=<?= (int) $appointment['id'] ?>"
            class="btn btn-sm btn-primary"
        >
            Reschedule
        </a>

    <?php endif; ?>

    <?php if (!$canCancel && !$canReschedule): ?>

        <span class="text-muted">
            No actions available
        </span>

    <?php endif; ?>
</td>


                    </tr>

                <?php endforeach; ?>

                <?php if (empty($appointments)): ?>

                    <tr>
                        <td colspan="8" class="text-center">
                            No appointments found.
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <a
        href="../dashboards/admin-dashboard.php"
        class="btn btn-secondary"
    >
        Back to Dashboard
    </a>

</div>

</body>
</html>
