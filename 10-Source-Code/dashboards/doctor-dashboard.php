<?php

require_once __DIR__ . '/../config/auth.php';

requireRole(['doctor']);

$accessError = $_SESSION['access_error'] ?? '';
unset($_SESSION['access_error']);

$availabilitySuccess = $_SESSION['availability_success'] ?? '';
unset($_SESSION['availability_success']);

$availabilityError = $_SESSION['availability_error'] ?? '';
unset($_SESSION['availability_error']);

$fullName = $_SESSION['full_name'] ?? 'Doctor';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Doctor Dashboard - Clinic Appointment System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5">

    <?php if ($accessError !== ''): ?>
        <div class="alert alert-warning">
            <?php echo htmlspecialchars($accessError); ?>
        </div>
    <?php endif; ?>

    <?php if ($availabilitySuccess !== ''): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($availabilitySuccess); ?>
        </div>
    <?php endif; ?>

    <?php if ($availabilityError !== ''): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($availabilityError); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow p-4">

        <h2 class="mb-3">
            Doctor Dashboard
        </h2>

        <p>
            Welcome,
            <strong>
                <?php echo htmlspecialchars($fullName); ?>
            </strong>
        </p>

        <p class="text-muted">
            You are logged in as a doctor.
        </p>

        <hr>

        <h5 class="mb-3">
            Doctor Functions
        </h5>

        <div class="d-flex gap-2 flex-wrap">

            <a
                href="../doctor/availability.php"
                class="btn btn-primary"
            >
                Manage Availability
            </a>

            <a
                href="../logout.php"
                class="btn btn-danger"
            >
                Logout
            </a>

        </div>

    </div>

</div>

</body>

</html>