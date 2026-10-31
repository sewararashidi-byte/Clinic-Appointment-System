
<?php

/**
 * Sprint 5 — US-18: Doctor Information Management
 * Authorized staff can view doctor information.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_doctor_repository.php';

$doctors = getStaffDoctors($conn);
$conn->close();

function e($value): string
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

    <title>Doctor Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <h2>Doctor Management</h2>

    <p class="text-muted">
        View registered doctors and their
        professional information.
    </p>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Doctor ID</th>
                    <th>Full Name</th>
                    <th>Specialty</th>
                    <th>Qualifications</th>
                    <th>Experience</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($doctors as $doctor): ?>
                    <tr>
                        <td><?= e($doctor['id']) ?></td>
                        <td><?= e($doctor['full_name']) ?></td>
                        <td><?= e($doctor['specialty']) ?></td>
                        <td><?= e($doctor['qualifications']) ?></td>
                        <td><?= e($doctor['years_experience']) ?> years</td>
                        <td><?= e($doctor['email']) ?></td>
                        <td><?= e($doctor['phone']) ?></td>
                        <td><?= e($doctor['account_status']) ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($doctors)): ?>
                    <tr>
                        <td colspan="8" class="text-center">
                            No doctors found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <a
        href="../dashboards/admin-dashboard.php"
        class="btn btn-secondary"
    >
        Back to Dashboard
    </a>

</div>

</body>
</html>
