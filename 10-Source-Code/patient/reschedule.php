<?php

/**
 * Sprint 4 - US-10: Appointment Rescheduling Frontend
 * Developer: Sonbol Almas
 *
 * Displays available alternative appointment slots
 * for the patient's existing doctor.
 */

require_once __DIR__ . '/../config/auth.php';
requireRole(['patient']);

require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ .
    '/../includes/doctor_repository.php';

require_once __DIR__ .
    '/../includes/appointment_repository.php';

/**
 * Escape values before displaying them in HTML.
 */
function escapeRescheduleValue($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

// Prepare the CSRF token expected by the backend action.
if (
    !isset($_SESSION['appointment_action_csrf_token']) ||
    !is_string($_SESSION['appointment_action_csrf_token'])
) {
    $_SESSION['appointment_action_csrf_token'] =
        bin2hex(random_bytes(32));
}

$clinicTimezone = new DateTimeZone('Asia/Kabul');

$now = new DateTimeImmutable(
    'now',
    $clinicTimezone
);

$patientId = (int) ($_SESSION['user_id'] ?? 0);

$appointmentId = filter_var(
    $_GET['appointment_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$selectedAppointment = null;
$availableSlots = [];
$pageError = '';

try {

    if (
        $patientId <= 0 ||
        !is_int($appointmentId)
    ) {
        throw new DomainException(
            'Invalid appointment request.'
        );
    }

    // Retrieve only this patient's appointments.
    $patientAppointments = getPatientAppointments(
        $conn,
        $patientId
    );

    // Find the requested appointment.
    foreach ($patientAppointments as $appointment) {

        if (
            (int) $appointment['appointment_id'] ===
            $appointmentId
        ) {
            $selectedAppointment = $appointment;
            break;
        }
    }

    if ($selectedAppointment === null) {
        throw new DomainException(
            'Appointment not found.'
        );
    }

    // Only confirmed appointments can be rescheduled.
    if ($selectedAppointment['status'] !== 'confirmed') {
        throw new DomainException(
            'Only confirmed appointments can be rescheduled.'
        );
    }

    $originalStart = new DateTimeImmutable(
        $selectedAppointment['appointment_date'] . ' ' .
        $selectedAppointment['start_time'],
        $clinicTimezone
    );

    // Product Owner rule: at least two hours before
    // the original appointment.
    $rescheduleDeadline = $originalStart
        ->modify('-2 hours');

    if ($now > $rescheduleDeadline) {
        throw new DomainException(
            'Online rescheduling is no longer available. ' .
            'Please contact the clinic.'
        );
    }

    // Keep the same doctor.
    $doctorId = (int) $selectedAppointment['doctor_id'];

    // Get the doctor's current availability.
    $availabilityRecords = getDoctorAvailability(
        $conn,
        $doctorId
    );

    // Reuse the existing booking conflict checks.
    $availableSlots = getAvailableAppointmentSlots(
        $conn,
        $doctorId,
        $availabilityRecords
    );

    // Do not offer the original appointment slot.
    $availableSlots = array_values(
        array_filter(
            $availableSlots,
            function ($slot) use ($selectedAppointment) {

                return !(
                    $slot['appointment_date'] ===
                        $selectedAppointment['appointment_date'] &&
                    $slot['start_time'] ===
                        $selectedAppointment['start_time']
                );
            }
        )
    );

    // Display a manageable selection of slots.
    $availableSlots = array_slice(
        $availableSlots,
        0,
        100
    );

} catch (DomainException $e) {

    $pageError = $e->getMessage();

} catch (Throwable $e) {

    error_log(
        'Reschedule page error: ' . $e->getMessage()
    );

    $pageError =
        'Unable to load appointment options. Please try again.';

} finally {

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
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
        Reschedule Appointment - Clinic Appointment System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5 mb-5">

    <h2 class="text-center mb-4">
        Reschedule Appointment
    </h2>

    <p class="text-center text-muted">
        Choose a different available appointment time
        with the same doctor.
    </p>

    <?php if ($pageError !== ''): ?>

        <div class="alert alert-warning" role="alert">

            <?php
            echo escapeRescheduleValue($pageError);
            ?>

        </div>

    <?php elseif ($selectedAppointment !== null): ?>

        <!-- Current Appointment -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-primary text-white">

                <h4 class="mb-0">
                    Current Appointment
                </h4>

            </div>

            <div class="card-body">

                <table class="table table-bordered">

                    <tbody>

                        <tr>
                            <th>Reference</th>
                            <td>
                                #<?php
                                echo (int) $selectedAppointment['appointment_id'];
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Doctor</th>
                            <td>
                                <?php
                                echo escapeRescheduleValue(
                                    $selectedAppointment['doctor_name']
                                );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Current Date</th>
                            <td>
                                <?php
                                echo escapeRescheduleValue(
                                    $selectedAppointment['appointment_date']
                                );
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <th>Current Time</th>
                            <td>
                                <?php
                                echo escapeRescheduleValue(
                                    date(
                                        'g:i A',
                                        strtotime(
                                            $selectedAppointment['start_time']
                                        )
                                    )
                                );
                                ?>
                            </td>
                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

        <!-- Alternative Appointment Slots -->

        <div class="card shadow-sm">

            <div class="card-header bg-light">

                <h4 class="mb-0">
                    Available Alternative Slots
                </h4>

            </div>

            <div class="card-body">

                <?php if (empty($availableSlots)): ?>

                    <div class="alert alert-info">

                        No alternative appointment slots
                        are currently available.

                    </div>

                <?php else: ?>

                    <p class="text-muted">

                        Select one of the available times below.
                        All times are shown in Afghanistan time.

                    </p>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-light">

                                <tr>
                                    <th>Date</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Action</th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php foreach ($availableSlots as $slot): ?>

                                <tr>

                                    <td>
                                        <?php
                                        echo escapeRescheduleValue(
                                            date(
                                                'F j, Y',
                                                strtotime(
                                                    $slot['appointment_date']
                                                )
                                            )
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo escapeRescheduleValue(
                                            date(
                                                'g:i A',
                                                strtotime(
                                                    $slot['start_time']
                                                )
                                            )
                                        );
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo escapeRescheduleValue(
                                            date(
                                                'g:i A',
                                                strtotime(
                                                    $slot['end_time']
                                                )
                                            )
                                        );
                                        ?>
                                    </td>

                                    <td>

                                        <form
                                            action="../actions/appointment_reschedule.php"
                                            method="POST"
                                            onsubmit="return confirm('Reschedule your appointment to this time?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="appointment_id"
                                                value="<?php
                                                    echo (int) $selectedAppointment['appointment_id'];
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="availability_id"
                                                value="<?php
                                                    echo (int) $slot['availability_id'];
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="appointment_date"
                                                value="<?php
                                                    echo escapeRescheduleValue(
                                                        $slot['appointment_date']
                                                    );
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="start_time"
                                                value="<?php
                                                    echo escapeRescheduleValue(
                                                        $slot['start_time']
                                                    );
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?php
                                                    echo escapeRescheduleValue(
                                                        $_SESSION['appointment_action_csrf_token']
                                                    );
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-success"
                                            >
                                                Select New Time
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>

    <div class="text-center mt-4">

        <a
            href="appointments.php"
            class="btn btn-secondary"
        >
            Back to My Appointments
        </a>

    </div>

</div>

</body>

</html>