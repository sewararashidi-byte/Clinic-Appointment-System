<?php


/**
 * Doctor Information Management
 * Authorized staff can view and edit permitted
 * doctor information.
 */


require_once __DIR__ . '/../config/auth.php';


requireRole(['receptionist', 'administrator']);


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_doctor_repository.php';


$doctors = getStaffDoctors($conn);


$conn->close();


function escapeStaffDoctors($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}


function doctorDisplayValue($value): string
{
    if ($value === null || trim((string) $value) === '') {
        return '—';
    }


    return escapeStaffDoctors($value);
}


$totalDoctors = count($doctors);


?>


<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>Doctor Management | Clinic Appointment System</title>


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
            color: #ffffff;
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


        .back-link {
            color: #087d85;
            font-weight: 700;
        }


        .back-link:hover {
            color: #075a60;
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


        .doctor-table {
            margin-bottom: 0;
            vertical-align: middle;
        }


        .doctor-table thead th {
            background: #eef5f8;
            color: #294b67;
            font-size: 0.85rem;
            font-weight: 700;
            border-bottom: none;
            padding: 16px;
            white-space: nowrap;
        }


        .doctor-table tbody td {
            padding: 16px;
            border-color: #eef2f5;
        }


        .doctor-table tbody tr:hover td {
            background: #f6fbfc;
        }


        .doctor-name {
            color: #173e61;
            font-weight: 700;
            white-space: nowrap;
        }


        .specialty-badge {
            display: inline-block;
            background: #e8f5f6;
            color: #087d85;
            padding: 7px 12px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 600;
        }


        .status-badge {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 20px;
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


        @media (max-width: 576px) {
            .management-card-header {
                padding: 18px;
            }


            .doctor-table thead th,
            .doctor-table tbody td {
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
            Doctor Management
        </h1>


        <p class="text-muted">
            View registered doctors, professional
            information, contact details, and
            consultation room assignments.
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
                        Registered Doctors
                    </h2>


                    <p class="text-muted small mb-0">
                        View doctor profiles and update
                        permitted information.
                    </p>


                </div>


                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <?= $totalDoctors ?> Doctors
                </span>


            </div>


        </div>


        <div class="table-responsive">


            <table class="table doctor-table">


                <thead>


                    <tr>
                        <th scope="col">Doctor ID</th>
                        <th scope="col">Doctor</th>
                        <th scope="col">Specialization</th>
                        <th scope="col">Qualifications</th>
                        <th scope="col">Experience</th>
                        <th scope="col">Consultation Room</th>
                        <th scope="col">Email</th>
                        <th scope="col">Phone</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>


                </thead>


                <tbody>


                    <?php foreach ($doctors as $doctor): ?>


                        <?php
                        $status = strtolower(
                            (string) ($doctor['account_status'] ?? '')
                        );


                        $statusClass = match ($status) {
                            'active' => 'status-active',
                            'pending' => 'status-pending',
                            'rejected' => 'status-rejected',
                            default => 'status-pending'
                        };


                        $experience = $doctor['years_experience'] ?? null;
                        ?>


                        <tr>


                            <td>
                                #<?= (int) $doctor['id'] ?>
                            </td>


                            <td class="doctor-name">
                                <?= escapeStaffDoctors(
                                    $doctor['full_name']
                                ) ?>
                            </td>


                            <td>
                                <span class="specialty-badge">
                                    <?= doctorDisplayValue(
                                        $doctor['specialty'] ?? null
                                    ) ?>
                                </span>
                            </td>


                            <td>
                                <?= doctorDisplayValue(
                                    $doctor['qualifications'] ?? null
                                ) ?>
                            </td>


                            <td>
                                <?php if ($experience !== null): ?>
                                    <?= (int) $experience ?> years
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>


                            <td>
                                <?= doctorDisplayValue(
                                    $doctor['consultation_room'] ?? null
                                ) ?>
                            </td>


                            <td>
                                <?= escapeStaffDoctors(
                                    $doctor['email']
                                ) ?>
                            </td>


                            <td>
                                <?= escapeStaffDoctors(
                                    $doctor['phone']
                                ) ?>
                            </td>


                            <td>
                                <span class="status-badge <?= $statusClass ?>">
                                    <?= escapeStaffDoctors(
                                        ucfirst($status)
                                    ) ?>
                                </span>
                            </td>


                            <td>
                                <a
                                    href="doctor-edit.php?id=<?= (int) $doctor['id'] ?>"
                                    class="edit-button"
                                    aria-label="Edit information for <?= escapeStaffDoctors($doctor['full_name']) ?>"
                                >
                                    Edit Details
                                </a>
                            </td>


                        </tr>


                    <?php endforeach; ?>


                    <?php if (empty($doctors)): ?>


                        <tr>
                            <td
                                colspan="10"
                                class="text-center text-muted py-5"
                            >
                                No doctor records were found.
                            </td>
                        </tr>


                    <?php endif; ?>


                </tbody>


            </table>


        </div>


    </section>


</main>


</body>
</html>