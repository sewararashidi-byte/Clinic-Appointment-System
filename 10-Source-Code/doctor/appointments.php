<?php
require_once __DIR__ . '/../includes/doctor_appointments_data.php';

function escapeAppointment($value): string
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
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Appointments - Clinic Appointment System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5 mb-5">

    <div class="card shadow p-4">

        <h2>My Appointments</h2>

        <p class="text-muted">
            View your confirmed patient appointments.
        </p>

        <hr>

        <?php if ($appointmentsError !== ''): ?>

            <div class="alert alert-danger">
                <?= escapeAppointment($appointmentsError) ?>
            </div>

        <?php elseif (empty($doctorAppointments)): ?>

            <div class="alert alert-info">
                You have no confirmed appointments.
            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table table-bordered table-striped">

                    <thead class="table-light">
                        <tr>
                            <th>Reference</th>
                            <th>Patient Name</th>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($doctorAppointments as $appointment): ?>

                            <tr>

                                <td>
                                    <?= escapeAppointment(
                                        $appointment['appointment_id']
                                    ) ?>
                                </td>

                                <td>
                                    <?= escapeAppointment(
                                        $appointment['patient_name']
                                    ) ?>
                                </td>

                                <td>
                                    <?= escapeAppointment(
                                        $appointment['appointment_date']
                                    ) ?>
                                </td>

                                <td>
                                    <?= escapeAppointment(
                                        $appointment['start_time']
                                    ) ?>
                                </td>

                                <td>
                                    <?= escapeAppointment(
                                        $appointment['end_time']
                                    ) ?>
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        <?= escapeAppointment(
                                            $appointment['status']
                                        ) ?>
                                    </span>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

        <div class="mt-3">

            <a
                href="../dashboards/doctor-dashboard.php"
                class="btn btn-secondary"
            >
                Back to Dashboard
            </a>

        </div>

    </div>

</div>

</body>

</html>