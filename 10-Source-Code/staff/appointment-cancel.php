
<?php

/**
 * Sprint 5 — US-19
 * Staff Appointment Cancellation Form
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';

// Validate appointment ID.
$idInput = $_GET['id'] ?? null;

$appointmentId = is_string($idInput)
    ? filter_var(
        $idInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;

if ($appointmentId === false) {
    http_response_code(400);
    exit('Invalid appointment ID.');
}

// Retrieve appointment details.
$sql = "
    SELECT
        a.id,
        a.appointment_date,
        a.start_time,
        a.status,
        p.full_name AS patient_name,
        d.full_name AS doctor_name
    FROM appointments AS a
    INNER JOIN users AS p ON p.id = a.patient_id
    INNER JOIN users AS d ON d.id = a.doctor_id
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

// Create CSRF token.
if (empty($_SESSION['staff_appointment_csrf_token'])) {
    $_SESSION['staff_appointment_csrf_token'] =
        bin2hex(random_bytes(32));
}

// Check whether cancellation is currently possible.
$appointmentStart = new DateTimeImmutable(
    $appointment['appointment_date'] . ' ' .
    $appointment['start_time']
);

$now = new DateTimeImmutable('now');

$canCancel = (
    $appointment['status'] === 'confirmed' &&
    $appointmentStart > $now
);

$isLate = (
    $canCancel &&
    $now > $appointmentStart->modify('-24 hours')
);

// Escape displayed values.
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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Staff Appointment Cancellation</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body>

<div class="container mt-5" style="max-width: 750px;">

    <h2>Cancel Patient Appointment</h2>

    <p class="text-muted">
        Staff Appointment Management — US-19
    </p>

    <div class="card shadow-sm">
        <div class="card-body">

            <p>
                <strong>Appointment ID:</strong>
                <?= staffCancelEscape($appointment['id']) ?>
            </p>

            <p>
                <strong>Patient:</strong>
                <?= staffCancelEscape($appointment['patient_name']) ?>
            </p>

            <p>
                <strong>Doctor:</strong>
                <?= staffCancelEscape($appointment['doctor_name']) ?>
            </p>

            <p>
                <strong>Date:</strong>
                <?= staffCancelEscape($appointment['appointment_date']) ?>
            </p>

            <p>
                <strong>Time:</strong>
                <?= staffCancelEscape($appointment['start_time']) ?>
            </p>

            <p>
                <strong>Status:</strong>
                <?= staffCancelEscape($appointment['status']) ?>
            </p>

            <?php if (!$canCancel): ?>

                <div class="alert alert-warning">
                    This appointment cannot be cancelled
                    because it is not confirmed or has
                    already started.
                </div>

            <?php else: ?>

                <?php if ($isLate): ?>
                    <div class="alert alert-warning">
                        This appointment is within 24 hours.
                        A cancellation reason is required.
                    </div>
                <?php endif; ?>

                <form
                    method="POST"
                    action="../actions/staff_appointment_cancel.php"
                >

                    <input
                        type="hidden"
                        name="appointment_id"
                        value="<?= staffCancelEscape(
                            $appointment['id']
                        ) ?>"
                    >

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= staffCancelEscape(
                            $_SESSION['staff_appointment_csrf_token']
                        ) ?>"
                    >

                    <div class="mb-3">
                        <label class="form-label">
                            Cancellation Reason
                        </label>

                        <textarea
                            name="reason"
                            class="form-control"
                            rows="3"
                            maxlength="500"
                            <?= $isLate ? 'required' : '' ?>
                        ></textarea>

                        <div class="form-text">
                            Required for cancellations
                            within 24 hours.
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        Confirm Cancellation
                    </button>

                </form>

            <?php endif; ?>

            <a
                href="appointments.php"
                class="btn btn-secondary mt-3"
            >
                Back to Appointments
            </a>

        </div>
    </div>

</div>

</body>
</html>
