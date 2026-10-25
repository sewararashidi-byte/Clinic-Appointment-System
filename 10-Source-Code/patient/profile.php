<?php
require_once __DIR__ . '/../includes/patient_profile_data.php';

$successMessage = $_SESSION['profile_success'] ?? '';
$errorMessage = $_SESSION['profile_error'] ?? '';

unset($_SESSION['profile_success'], $_SESSION['profile_error']);

function escapeProfileValue($value): string
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Profile - Clinic Appointment System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5 mb-5">

    <div class="card shadow p-4">

        <h2>My Profile</h2>

        <p class="text-muted">
            View and update your personal information.
        </p>

        <hr>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success">
                <?= escapeProfileValue($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger">
                <?= escapeProfileValue($errorMessage) ?>
            </div>
        <?php endif; ?>

        <?php if ($profileError !== ''): ?>
            <div class="alert alert-danger">
                <?= escapeProfileValue($profileError) ?>
            </div>
        <?php endif; ?>

        <?php if ($patientProfile !== null): ?>

            <form
                action="../actions/patient_profile_update.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapeProfileValue($_SESSION['csrf_token'] ?? '') ?>"
                >

                <div class="mb-3">

                    <label for="full_name" class="form-label">
                        Full Name *
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="full_name"
                        name="full_name"
                        value="<?= escapeProfileValue($patientProfile['full_name']) ?>"
                        maxlength="100"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label for="phone" class="form-label">
                        Phone Number *
                    </label>

                    <input
                        type="tel"
                        class="form-control"
                        id="phone"
                        name="phone"
                        value="<?= escapeProfileValue($patientProfile['phone']) ?>"
                        maxlength="20"
                        required
                    >

                </div>

                <div class="mb-3">

                    <label for="age" class="form-label">
                        Age (Optional)
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="age"
                        name="age"
                        value="<?= escapeProfileValue($patientProfile['age']) ?>"
                        min="0"
                        max="255"
                        step="1"
                    >

                </div>

                <div class="mb-3">

                    <label for="blood_type" class="form-label">
                        Blood Type (Optional)
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="blood_type"
                        name="blood_type"
                        value="<?= escapeProfileValue($patientProfile['blood_type']) ?>"
                        maxlength="5"
                        placeholder="Example: O+"
                    >

                </div>

                <div class="mb-3">

                    <label for="disease_type" class="form-label">
                        Type of Disease (Optional)
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        id="disease_type"
                        name="disease_type"
                        value="<?= escapeProfileValue($patientProfile['disease_type']) ?>"
                        maxlength="255"
                    >

                </div>

                <div class="mb-3">

                    <label for="close_relatives_count" class="form-label">
                        Number of Close Relatives (Optional)
                    </label>

                    <input
                        type="number"
                        class="form-control"
                        id="close_relatives_count"
                        name="close_relatives_count"
                        value="<?= escapeProfileValue($patientProfile['close_relatives_count']) ?>"
                        min="0"
                        max="65535"
                        step="1"
                    >

                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Changes
                    </button>

                    <a
                        href="../dashboards/patient-dashboard.php"
                        class="btn btn-secondary"
                    >
                        Back to Dashboard
                    </a>

                </div>

            </form>

        <?php else: ?>

            <p class="text-muted">
                Your profile could not be loaded.
            </p>

            <a
                href="../dashboards/patient-dashboard.php"
                class="btn btn-secondary"
            >
                Back to Dashboard
            </a>

        <?php endif; ?>

    </div>

</div>

</body>

</html>