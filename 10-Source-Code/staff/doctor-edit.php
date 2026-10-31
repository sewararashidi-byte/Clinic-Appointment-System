
<?php

/**
 * Sprint 5 — US-18
 * Staff Doctor Contact Information Editing Form
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_doctor_repository.php';

// Validate doctor ID.
$doctorId = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!is_int($doctorId)) {
    http_response_code(400);
    exit('Invalid doctor ID.');
}

// Retrieve doctor information.
$doctor = getStaffDoctorById($conn, $doctorId);

$conn->close();

if ($doctor === null) {
    http_response_code(404);
    exit('Doctor not found.');
}

// Create CSRF token.
if (empty($_SESSION['staff_doctor_csrf_token'])) {
    $_SESSION['staff_doctor_csrf_token'] =
        bin2hex(random_bytes(32));
}

// Read one-time messages.
$success = $_SESSION['staff_doctor_success'] ?? '';
$error = $_SESSION['staff_doctor_error'] ?? '';

unset(
    $_SESSION['staff_doctor_success'],
    $_SESSION['staff_doctor_error']
);

// Escape output.
function escapeDoctorEdit($value): string
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

    <title>Edit Doctor Contact Information</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5" style="max-width: 750px;">

    <h2>Edit Doctor Contact Information</h2>

    <p class="text-muted">
        Update approved administrative contact details.
    </p>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success">
            <?= escapeDoctorEdit($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= escapeDoctorEdit($error) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <form
                method="POST"
                action="../actions/staff_doctor_update.php"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapeDoctorEdit(
                        $_SESSION['staff_doctor_csrf_token']
                    ) ?>"
                >

                <input
                    type="hidden"
                    name="doctor_id"
                    value="<?= escapeDoctorEdit($doctor['id']) ?>"
                >

                <div class="mb-3">
                    <label class="form-label">
                        Doctor Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= escapeDoctorEdit(
                            $doctor['full_name']
                        ) ?>"
                        readonly
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Specialization
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= escapeDoctorEdit(
                            $doctor['specialty']
                        ) ?>"
                        readonly
                    >
                </div>
                
<div class="mb-3">
    <label class="form-label">
        Consultation Room (Optional)
    </label>

    <input
        type="text"
        name="consultation_room"
        class="form-control"
        maxlength="100"
        placeholder="Enter consultation room"
        value="<?= escapeDoctorEdit(
            $doctor['consultation_room'] ?? ''
        ) ?>"
    >
</div>


                <div class="mb-3">
                    <label class="form-label">
                        Phone Number
                    </label>

                    <input
                        type="text"
                        name="phone"
                        class="form-control"
                        maxlength="20"
                        required
                        value="<?= escapeDoctorEdit(
                            $doctor['phone']
                        ) ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        maxlength="150"
                        required
                        value="<?= escapeDoctorEdit(
                            $doctor['email']
                        ) ?>"
                    >
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Doctor Changes
                </button>

                <a
                    href="doctors.php"
                    class="btn btn-secondary"
                >
                    Back to Doctor Management
                </a>

            </form>

        </div>
    </div>

</div>

</body>
</html>
