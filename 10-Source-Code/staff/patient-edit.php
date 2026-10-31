
<?php

/**
 * Sprint 5 — US-17
 * Staff Patient Information Editing Form
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_patient_repository.php';

// Validate patient ID from the URL.
$patientId = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if (!is_int($patientId)) {
    http_response_code(400);
    exit('Invalid patient ID.');
}

// Get the selected patient.
$patient = getStaffPatientById($conn, $patientId);

$conn->close();

if ($patient === null) {
    http_response_code(404);
    exit('Patient not found.');
}

// Create CSRF token for the update form.
if (empty($_SESSION['staff_patient_csrf_token'])) {
    $_SESSION['staff_patient_csrf_token'] =
        bin2hex(random_bytes(32));
}

// Read and clear one-time messages.
$success = $_SESSION['staff_patient_success'] ?? '';
$error = $_SESSION['staff_patient_error'] ?? '';

unset(
    $_SESSION['staff_patient_success'],
    $_SESSION['staff_patient_error']
);

// Escape information before displaying it.
function escapePatientEdit($value): string
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

    <title>Edit Patient Information</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body>

<div class="container mt-5" style="max-width: 750px;">

    <h2>Edit Patient Information</h2>

    <p class="text-muted">
        Update approved general patient information.
    </p>

    <?php if ($success !== ''): ?>
        <div class="alert alert-success">
            <?= escapePatientEdit($success) ?>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger">
            <?= escapePatientEdit($error) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">

            <form
                method="POST"
                action="../actions/staff_patient_update.php"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapePatientEdit(
                        $_SESSION['staff_patient_csrf_token']
                    ) ?>"
                >

                <input
                    type="hidden"
                    name="patient_id"
                    value="<?= escapePatientEdit($patient['id']) ?>"
                >

                <div class="mb-3">
                    <label class="form-label">
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        class="form-control"
                        maxlength="100"
                        required
                        value="<?= escapePatientEdit(
                            $patient['full_name']
                        ) ?>"
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">
                        Age
                    </label>

                    <input
                        type="number"
                        name="age"
                        class="form-control"
                        min="1"
                        max="120"
                        value="<?= escapePatientEdit(
                            $patient['age']
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
                        value="<?= escapePatientEdit(
                            $patient['phone']
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
                        value="<?= escapePatientEdit(
                            $patient['email']
                        ) ?>"
                    >
                </div>
                
<div class="mb-3">
    <label class="form-label">
        Home Address (Optional)
    </label>

    <textarea
        name="home_address"
        class="form-control"
        rows="3"
        maxlength="255"
        placeholder="Enter patient's home address"
    ><?= escapePatientEdit(
        $patient['home_address'] ?? ''
    ) ?></textarea>
</div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Patient Changes
                </button>

                <a
                    href="patients.php"
                    class="btn btn-secondary"
                >
                    Back to Patient Management
                </a>

            </form>

        </div>
    </div>

</div>

</body>
</html>
