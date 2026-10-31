
<?php
/**
 * Sprint 5 — US-17: Patient Information Management
 * Secure patient list for receptionists and administrators.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_patient_repository.php';

$patients = getStaffPatients($conn);
$conn->close();

function e($value): string
{
    return htmlspecialchars(
        (string) $value,
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
    <title>Patient Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body>

<div class="container mt-5">

    <h2>Patient Management</h2>
    <p class="text-muted">
        View registered patient information.
    </p>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Patient ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Account Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($patients as $patient): ?>
                <tr>
                    <td><?= e($patient['id']) ?></td>
                    <td><?= e($patient['full_name']) ?></td>
                    <td><?= e($patient['email']) ?></td>
                    <td><?= e($patient['phone']) ?></td>
                    <td><?= e($patient['account_status']) ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($patients)): ?>
                <tr>
                    <td colspan="5" class="text-center">
                        No patients found.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="../dashboards/admin-dashboard.php"
       class="btn btn-secondary">
        Back to Dashboard
    </a>

</div>

</body>
</html>
