
<?php

/**
 * Sprint 5 — US-19
 * Staff Appointment Rescheduling Form
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/doctor_repository.php';
require_once __DIR__ . '/../includes/appointment_repository.php';

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

$appointment = $stmt
    ->get_result()
    ->fetch_assoc();

$stmt->close();

if (!$appointment) {
    $conn->close();
    http_response_code(404);
    exit('Appointment not found.');
}

// Check whether the appointment is eligible.
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

// Retrieve available slots for the SAME doctor.
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

// Create CSRF token.
if (empty($_SESSION['staff_reschedule_csrf_token'])) {
    $_SESSION['staff_reschedule_csrf_token'] =
        bin2hex(random_bytes(32));
}

// Read and clear error messages.
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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Staff Appointment Rescheduling</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5" style="max-width: 800px;">

    <h2>Reschedule Patient Appointment</h2>

    <p class="text-muted">
        Staff Appointment Management — US-19
    </p>

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= escapeStaffReschedule($error) ?>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <h5>Current Appointment Details</h5>

            <p>
                <strong>Appointment ID:</strong>
                <?= escapeStaffReschedule(
                    $appointment['id']
                ) ?>
            </p>

            <p>
                <strong>Patient:</strong>
                <?= escapeStaffReschedule(
                    $appointment['patient_name']
                ) ?>
            </p>

            <p>
                <strong>Doctor:</strong>
                <?= escapeStaffReschedule(
                    $appointment['doctor_name']
                ) ?>
            </p>

            <p>
                <strong>Current Date:</strong>
                <?= escapeStaffReschedule(
                    $appointment['appointment_date']
                ) ?>
            </p>

            <p>
                <strong>Current Time:</strong>
                <?= escapeStaffReschedule(
                    $appointment['start_time']
                ) ?>
            </p>

            <p>
                <strong>Status:</strong>
                <?= escapeStaffReschedule(
                    $appointment['status']
                ) ?>
            </p>

            <hr>

            <?php if (!$canReschedule): ?>

                <div class="alert alert-warning">
                    This appointment cannot be rescheduled.
                    It may be cancelled, already started,
                    or past the rescheduling deadline.
                </div>

            <?php elseif (empty($availableSlots)): ?>

                <div class="alert alert-warning">
                    No alternative appointment slots
                    are currently available.
                </div>

            <?php else: ?>

                <h5>Select a New Appointment Time</h5>

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

                    <div class="mb-3">

                        <label class="form-label">
                            New Available Time
                        </label>

                        <select
                            id="slotChoice"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Choose a new appointment time
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
                                        $slot['start_time']
                                    ) ?>

                                    -

                                    <?= escapeStaffReschedule(
                                        $slot['end_time']
                                    ) ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Confirm Rescheduling
                    </button>

                </form>

            <?php endif; ?>

            <a
                href="appointments.php"
                class="btn btn-secondary mt-3"
            >
                Back to Appointment Management
            </a>

        </div>

    </div>

</div>

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

            if (!selected || !selected.value) {
                event.preventDefault();
                return;
            }

            document.getElementById(
                'availabilityId'
            ).value = selected.dataset.availabilityId;

            document.getElementById(
                'appointmentDate'
            ).value = selected.dataset.date;

            document.getElementById(
                'appointmentStartTime'
            ).value = selected.dataset.start;

        }
    );
}

</script>

</body>
</html>
