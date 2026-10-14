<?php
require_once __DIR__ . '/../config/auth.php';

requireRole(['administrator', 'receptionist']);

$accessError = $_SESSION['access_error'] ?? '';
unset($_SESSION['access_error']);

$fullName = $_SESSION['full_name'] ?? 'Staff';
$role = $_SESSION['role'] ?? 'staff';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Clinic Appointment System</title>

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

    <div class="card shadow p-4">

        <h2>Admin Dashboard</h2>

        <p>
            Welcome,
            <strong><?php echo htmlspecialchars($fullName); ?></strong>
        </p>

        <p class="text-muted">
            You are logged in as
            <strong><?php echo htmlspecialchars($role); ?></strong>.
        </p>

        <div>
            <a href="../logout.php" class="btn btn-danger">
                Logout
            </a>
        </div>

    </div>

</div>

</body>
</html>