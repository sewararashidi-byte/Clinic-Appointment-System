<?php

/**
 * Sprint 4 - US-12: Patient Appointment History
 * Sprint 4 - US-09: Appointment Cancellation Frontend
 *
 * Frontend Developer: Sonbol Almas
 */

// Load authenticated patient's appointments.
// This also prepares the appointment action CSRF token.
require_once __DIR__ .
    '/../includes/patient_appointments_data.php';

require_once __DIR__ . '/../config/timezone.php';

/**
 * Escape displayed values to prevent HTML injection.
 */
function escapeAppointmentValue($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Use the clinic's timezone.
$clinicTimezone = new DateTimeZone('Asia/Kabul');

$now = new DateTimeImmutable(
    'now',
    $clinicTimezone
);

$currentDateTime = $now->format('Y-m-d H:i:s');

// Retrieve success and error messages from
// cancellation or rescheduling actions.
$actionSuccess =
    $_SESSION['appointment_action_success'] ?? '';

$actionError =
    $_SESSION['appointment_action_error'] ?? '';

// Display each message only once.
unset(
    $_SESSION['appointment_action_success'],
    $_SESSION['appointment_action_error']
);

// Organize appointments into three groups.
$appointmentGroups = [
    'Upcoming' => [],
    'Previous' => [],
    'Cancelled' => []
];

foreach ($patientAppointments as $appointment) {

    $status = strtolower(
        (string) $appointment['status']
    );

    $appointmentStart =
        $appointment['appointment_date'] . ' ' .
        $appointment['start_time'];

    if ($status === 'cancelled') {

        // Cancelled appointments remain in history.
        $appointmentGroups['Cancelled'][] =
            $appointment;

    } elseif ($appointmentStart >= $currentDateTime) {

        $appointmentGroups['Upcoming'][] =
            $appointment;

    } else {

        $appointmentGroups['Previous'][] =
            $appointment;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        My Appointments - Clinic Appointment System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container mt-5 mb-5">

    <!-- Page Heading -->

    <h2 class="text-center mb-4">
        My Appointments
    </h2>

    <p class="text-center text-muted mb-4">
        View your upcoming, previous and cancelled appointments.
    </p>

    <!-- Success Message -->

    <?php if (
        is_string($actionSuccess) &&
        $actionSuccess !== ''
    ): ?>

        <div
            class="alert alert-success"
            role="alert"
        >
            <?php
            echo escapeAppointmentValue(
                $actionSuccess
            );
            ?>
        </div>

    <?php endif; ?>

    <!-- Error Message -->

    <?php if (
        is_string($actionError) &&
        $actionError !== ''
    ): ?>

        <div
            class="alert alert-danger"
            role="alert"
        >
            <?php
            echo escapeAppointmentValue(
                $actionError
            );
            ?>
        </div>

    <?php endif; ?>

    <!-- Appointment Loading Error -->

    <?php if ($appointmentsError !== ''): ?>

        <div
            class="alert alert-danger"
            role="alert"
        >

            <?php
            echo escapeAppointmentValue(
                $appointmentsError
            );
            ?>

        </div>

    <!-- No Appointments -->

    <?php elseif (empty($patientAppointments)): ?>

        <div class="alert alert-info text-center">

            You do not have any appointments yet.

        </div>

    <!-- Appointment Groups -->

    <?php else: ?>

        <?php foreach (
            $appointmentGroups as $groupName => $appointments
        ): ?>

            <div class="card shadow-sm mb-4">

                <!-- Group Heading -->

                <div class="card-header bg-light">

                    <h4 class="mb-0">

                        <?php
                        echo escapeAppointmentValue(
                            $groupName
                        );
                        ?>

                        Appointments

                    </h4>

                </div>

                <div class="card-body">

                    <!-- Empty Group -->

                    <?php if (empty($appointments)): ?>

                        <p class="text-muted mb-0">

                            No appointments in this category.

                        </p>

                    <?php else: ?>

                        <div class="table-responsive">

                            <table
                                class="table table-bordered table-hover align-middle"
                            >

                                <thead class="table-light">

                                    <tr>

                                        <th>Reference</th>

                                        <th>Doctor</th>

                                        <th>Date</th>

                                        <th>Start Time</th>

                                        <th>End Time</th>

                                        <th>Status</th>

                                        <th>Actions</th>

                                    </tr>

                                </thead>

                                <tbody>

                                <?php foreach (
                                    $appointments as $appointment
                                ): ?>

                                    <tr>

                                        <!-- Appointment Reference -->

                                        <td>

                                            #<?php
                                            echo (int) $appointment['appointment_id'];
                                            ?>

                                        </td>

                                        <!-- Doctor Name -->

                                        <td>

                                            <?php
                                            echo escapeAppointmentValue(
                                                $appointment['doctor_name']
                                            );
                                            ?>

                                        </td>

                                        <!-- Appointment Date -->

                                        <td>

                                            <?php
                                            echo escapeAppointmentValue(
                                                date(
                                                    'F j, Y',
                                                    strtotime(
                                                        $appointment['appointment_date']
                                                    )
                                                )
                                            );
                                            ?>

                                        </td>

                                        <!-- Start Time -->

                                        <td>

                                            <?php
                                            echo escapeAppointmentValue(
                                                date(
                                                    'g:i A',
                                                    strtotime(
                                                        $appointment['start_time']
                                                    )
                                                )
                                            );
                                            ?>

                                        </td>

                                        <!-- End Time -->

                                        <td>

                                            <?php
                                            echo escapeAppointmentValue(
                                                date(
                                                    'g:i A',
                                                    strtotime(
                                                        $appointment['end_time']
                                                    )
                                                )
                                            );
                                            ?>

                                        </td>

                                        <!-- Appointment Status -->

                                        <td>

                                            <?php if (
                                                $appointment['status'] === 'cancelled'
                                            ): ?>

                                                <span class="badge bg-danger">

                                                    Cancelled

                                                </span>

                                            <?php elseif (
                                                $groupName === 'Upcoming'
                                            ): ?>

                                                <span class="badge bg-success">

                                                    Upcoming

                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-secondary">

                                                    Previous

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <!-- Appointment Actions -->

                                        <td>

                                            <?php if (
                                                $groupName === 'Upcoming' &&
                                                $appointment['status'] === 'confirmed'
                                            ): ?>

                                                <?php

                                                // Calculate appointment start time
                                                // using Afghanistan local time.

                                                $appointmentStartDateTime =
                                                    new DateTimeImmutable(
                                                        $appointment['appointment_date'] . ' ' .
                                                        $appointment['start_time'],
                                                        $clinicTimezone
                                                    );

                                                // Product Owner rule:
                                                // Cancellation must happen at least
                                                // two hours before the appointment.

                                                $cancellationDeadline =
                                                    $appointmentStartDateTime
                                                        ->modify('-2 hours');

                                                $canCancel =
                                                    $now <= $cancellationDeadline;

                                                ?>

                                                <?php if ($canCancel): ?>

                                                    <!-- Cancellation Form -->

                                                    <form
                                                        action="../actions/appointment_cancel.php"
                                                        method="POST"
                                                        onsubmit="return confirm('Are you sure you want to cancel this appointment?');"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="appointment_id"
                                                            value="<?php
                                                                echo (int) $appointment['appointment_id'];
                                                            ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="csrf_token"
                                                            value="<?php
                                                                echo escapeAppointmentValue(
                                                                    $_SESSION['appointment_action_csrf_token'] ?? ''
                                                                );
                                                            ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                        >

                                                            Cancel Appointment

                                                        </button>

     
                                                   </form>
                                                   <!-- US-10: Reschedule Appointment -->

<a
    href="reschedule.php?appointment_id=<?php
        echo (int) $appointment['appointment_id'];
    ?>"
    class="btn btn-sm btn-outline-primary mt-2"
>
    Reschedule Appointment
</a>

                                                <?php else: ?>

                                                    <!-- Cancellation Deadline Passed -->

                                                    <span class="text-muted small">

                                                        Contact clinic to cancel

                                                    </span>

                                                <?php endif; ?>

                                            <?php else: ?>

                                                <!-- No Action Available -->

                                                <span class="text-muted">

                                                    &mdash;

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

    <!-- Navigation Button -->

    <div class="text-center mt-4">

        <a
            href="doctor-search.php"
            class="btn btn-primary"
        >

            Back to Doctor Search

        </a>

    </div>

</div>

</body>

</html>