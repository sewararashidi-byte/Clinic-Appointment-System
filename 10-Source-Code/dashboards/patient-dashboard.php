<?php
require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

$accessError = $_SESSION['access_error'] ?? '';
unset($_SESSION['access_error']);

$fullName = $_SESSION['full_name'] ?? 'Patient';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Patient Dashboard - Clinic Appointment System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5">

    <?php if ($accessError !== ''): ?>
        <div class="alert alert-warning">
            <?php
            echo htmlspecialchars(
                $accessError,
                ENT_QUOTES,
                'UTF-8'
            );
            ?>
        </div>
    <?php endif; ?>

    <div class="card shadow p-4">

        <h2>Patient Dashboard</h2>

        <p>
            Welcome,
            <strong>
                <?php
                echo htmlspecialchars(
                    $fullName,
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>
            </strong>
        </p>

        <p class="text-muted">
            You are logged in as a patient.
        </p>

        <hr>

        <h4>Find a Doctor</h4>

        <p class="text-muted">
            Search for doctors by name or medical specialty,
            and view their professional information
            and current availability.
        </p>

        <div class="d-flex flex-wrap gap-2 mt-3">

            <a
                href="../patient/doctor-search.php"
                class="btn btn-primary"
            >
                Find a Doctor
            </a>
<a
    href="../patient/profile.php"
    class="btn btn-outline-primary"
>
    My Profile
</a>
<a
    href="../patient/appointments.php"
    class="btn btn-outline-primary"
>
    My Appointments
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