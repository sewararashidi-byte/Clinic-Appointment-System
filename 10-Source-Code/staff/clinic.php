
<?php

/**
 * Sprint 5 — US-20
 * Clinic Information Management Page
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_clinic_repository.php';

// Retrieve existing clinic information, if available.
$clinic = getClinicInformation($conn);

$conn->close();

// Generate CSRF token for the update form.
if (empty($_SESSION['staff_clinic_csrf_token'])) {
    $_SESSION['staff_clinic_csrf_token'] =
        bin2hex(random_bytes(32));
}

// Read and clear one-time messages.
$success = $_SESSION['staff_clinic_success'] ?? '';
$error = $_SESSION['staff_clinic_error'] ?? '';

unset(
    $_SESSION['staff_clinic_success'],
    $_SESSION['staff_clinic_error']
);

// Escape values for safe HTML output.
function escapeClinic($value): string
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

    <title>Clinic Information Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5" style="max-width: 850px;">

    <h2>Clinic Information Management</h2>

    <p class="text-muted">
        Sprint 5 — US-20: Manage clinic information.
    </p>

    <?php if ($success !== ''): ?>

        <div class="alert alert-success">
            <?= escapeClinic($success) ?>
        </div>

    <?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="alert alert-danger">
            <?= escapeClinic($error) ?>
        </div>

    <?php endif; ?>

    <?php if ($clinic === null): ?>

        <div class="alert alert-info">
            No clinic information has been saved yet.
            Enter the approved clinic details below.
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <h5 class="mb-4">Clinic Details</h5>

            <form
                method="POST"
                action="../actions/staff_clinic_update.php"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapeClinic(
                        $_SESSION['staff_clinic_csrf_token']
                    ) ?>"
                >

                <div class="mb-3">

                    <label class="form-label">
                        Clinic Name *
                    </label>

                    <input
                        type="text"
                        name="clinic_name"
                        class="form-control"
                        maxlength="150"
                        required
                        value="<?= escapeClinic(
                            $clinic['clinic_name'] ?? ''
                        ) ?>"
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Clinic Address *
                    </label>

                    <textarea
                        name="clinic_address"
                        class="form-control"
                        rows="2"
                        maxlength="255"
                        required
                    ><?= escapeClinic(
                        $clinic['clinic_address'] ?? ''
                    ) ?></textarea>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Contact Phone Number *
                    </label>

                    <input
                        type="text"
                        name="contact_phone"
                        class="form-control"
                        maxlength="30"
                        required
                        value="<?= escapeClinic(
                            $clinic['contact_phone'] ?? ''
                        ) ?>"
                    >

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Opening Hours *
                    </label>

                    <input
                        type="text"
                        name="opening_hours"
                        class="form-control"
                        maxlength="255"
                        placeholder="Enter approved opening hours"
                        required
                        value="<?= escapeClinic(
                            $clinic['opening_hours'] ?? ''
                        ) ?>"
                    >

                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Clinic Information
                </button>

                <a
                    href="../dashboards/admin-dashboard.php"
                    class="btn btn-secondary"
                >
                    Back to Dashboard
                </a>

            </form>

        </div>

    </div>

    <?php if ($clinic !== null): ?>

        <p class="text-muted mt-3">
            Last updated:
            <?= escapeClinic($clinic['updated_at']) ?>
        </p>

    <?php endif; ?>

</div>

</body>
</html>
