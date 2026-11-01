<?php

/**
 * Sprint 5 — US-17: Patient Information Management
 * Frontend integration: Sonbol
 *
 * Staff can view patient records and open
 * the existing secure patient editing page.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['receptionist', 'administrator']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_patient_repository.php';

$patients = getStaffPatients($conn);

$conn->close();

function escapeStaffPatients($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$totalPatients = count($patients);

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
        Patient Management | Clinic Appointment System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="../assets/css/style.css"
        rel="stylesheet"
    >

    <style>
        .staff-navbar {
            background: #13385b;
            padding: 18px 0;
        }

        .staff-brand {
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .staff-brand:hover {
            color: #a3eee1;
            text-decoration: none;
        }

        .page-heading {
            color: #173e61;
            font-weight: 700;
        }

        .management-card {
            background: white;
            border: 1px solid #e1ebf0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px
                rgba(25, 71, 103, 0.06);
        }

        .management-card-header {
            padding: 24px;
            border-bottom: 1px solid #e7eef3;
        }

        .patient-table {
            margin-bottom: 0;
            vertical-align: middle;
        }

        .patient-table thead th {
            background: #eef5f8;
            color: #294b67;
            font-size: 0.85rem;
            font-weight: 700;
            border-bottom: none;
            padding: 16px;
            white-space: nowrap;
        }

        .patient-table tbody td {
            padding: 16px;
            border-color: #eef2f5;
        }

        .patient-table tbody tr:hover td {
            background: #f6fbfc;
        }

        .patient-name {
            color: #173e61;
            font-weight: 700;
        }

        .status-badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 30px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .status-active {
            background: #e0f5ea;
            color: #167347;
        }

        .status-pending {
            background: #fff3d6;
            color: #986600;
        }

        .status-rejected {
            background: #fde8e8;
            color: #a52d35;
        }

        .edit-button {
            display: inline-block;
            background: #e6f4f4;
            color: #087d85;
            border: 1px solid #c6e5e5;
            border-radius: 9px;
            padding: 8px 14px;
            font-size: 0.85rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .edit-button:hover {
            background: #087d85;
            color: white;
            text-decoration: none;
        }

        .back-link {
            color: #087d85;
            font-weight: 700;
        }

        .back-link:hover {
            color: #075a60;
        }

        @media (max-width: 576px) {
            .management-card-header {
                padding: 18px;
            }

            .patient-table thead th,
            .patient-table tbody td {
                padding: 12px;
            }
        }
    </style>

</head>

<body>

<nav class="staff-navbar">
    <div class="container">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <a
                href="../dashboards/admin-dashboard.php"
                class="staff-brand"
            >
                🏥 Clinic Appointment System
            </a>

            <a
                href="../logout.php"
                class="btn btn-outline-light btn-sm"
            >
                Logout
            </a>

        </div>

    </div>
</nav>

<main class="container py-5">

    <a
        href="../dashboards/admin-dashboard.php"
        class="back-link"
    >
        &larr; Back to Dashboard
    </a>

    <div class="mt-4 mb-4">


        <h1 class="page-heading">
            Patient Management
        </h1>

        <p class="text-muted">
            View registered patients and update
            approved general information.
        </p>

    </div>

    <section class="management-card">

        <div class="management-card-header">

            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

                <div>
                    <h2
                        class="h5 fw-bold mb-1"
                        style="color: #173e61;"
                    >
                        Registered Patients
                    </h2>

                    <p class="text-muted mb-0 small">
                        Patient records available to clinic staff.
                    </p>
                </div>

                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <?= (int) $totalPatients ?> Patients
                </span>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table patient-table">

                <thead>
                    <tr>
                        <th scope="col">Patient ID</th>
                        <th scope="col">Full Name</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Account Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($patients as $patient): ?>

                        <?php

                        $status = strtolower(
                            (string) $patient['account_status']
                        );

                        $statusClass = match ($status) {
                            'active' => 'status-active',
                            'pending' => 'status-pending',
                            'rejected' => 'status-rejected',
                            default => 'status-pending'
                        };

                        ?>

                        <tr>

                            <td>
                                #<?= (int) $patient['id'] ?>
                            </td>

                            <td class="patient-name">
                                <?= escapeStaffPatients(
                                    $patient['full_name']
                                ) ?>
                            </td>

                            <td>
                                <?= escapeStaffPatients(
                                    $patient['email']
                                ) ?>
                            </td>

                            <td>
                                <?= escapeStaffPatients(
                                    $patient['phone']
                                ) ?>
                            </td>

                            <td>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= escapeStaffPatients(
                                        ucfirst($status)
                                    ) ?>
                                </span>
                            </td>

                            <td>

                                <a
                                    href="patient-edit.php?id=<?= (int) $patient['id'] ?>"
                                    class="edit-button"
                                    aria-label="Edit information for <?= escapeStaffPatients($patient['full_name']) ?>"
                                >
                                    Edit Details
                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    <?php if (empty($patients)): ?>

                        <tr>
                            <td
                                colspan="6"
                                class="text-center text-muted py-5"
                            >
                                No patient records were found.
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </section>

    <p class="text-muted small mt-3">
        Only approved general patient information
        is displayed. Sensitive medical information
        is not included in this list.
    </p>

</main>

</body>
</html>