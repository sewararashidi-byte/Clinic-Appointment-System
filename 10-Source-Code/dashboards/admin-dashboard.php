<?php

/**
 * Sprint 5 — Staff Dashboard
 * Sonbol: Frontend and navigation integration.
 *
 * Available to receptionists and administrators.
 */

require_once __DIR__. '/../config/auth.php';

requireRole(['administrator', 'receptionist']);

$accessError = $_SESSION['access_error'] ?? '';
unset($_SESSION['access_error']);

$fullName = $_SESSION['full_name'] ?? 'Staff';
$role = $_SESSION['role'] ?? 'staff';

$roleLabel = $role === 'administrator'
    ? 'Administrator'
    : 'Receptionist';

function escapeDashboard($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$features = [
    [
        'title' => 'Patient Management',
        'story' => 'US-17',
        'icon' => '👥',
        'description' =>
            'View patient records and update approved general information.',
        'link' => '../staff/patients.php'
    ],
    [
        'title' => 'Doctor Management',
        'story' => 'US-18',
        'icon' => '🩺',
        'description' =>
            'View doctors and manage contact information and consultation rooms.',
        'link' => '../staff/doctors.php'
    ],
    [
        'title' => 'Appointment Management',
        'story' => 'US-19',
        'icon' => '📅',
        'description' =>
            'View appointments, book for patients, cancel and reschedule.',
        'link' => '../staff/appointments.php'
    ],
    [
        'title' => 'Clinic Information',
        'story' => 'US-20',
        'icon' => '🏥',
        'description' =>
            'View and update approved clinic contact details and opening hours.',
        'link' => '../staff/clinic.php'
    ]
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Staff Dashboard | Clinic Appointment System</title>

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
            font-size: 1.15rem;
            font-weight: 700;
        }

        .staff-brand:hover {
            color: #a3eee1;
            text-decoration: none;
        }

        .staff-hero {
            background: linear-gradient(
                120deg,
                #174c75,
                #167f86
            );
            color: white;
            border-radius: 20px;
            padding: 38px;
            margin-bottom: 30px;
        }

        .staff-hero h1 {
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }

        .staff-hero p {
            color: #e3f3f7;
        }

        .staff-feature-card {
            background: white;
            border: 1px solid #e1ebf0;
            border-radius: 18px;
            height: 100%;
            padding: 28px;
            position: relative;
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease;
        }

        .staff-feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px
                rgba(25, 71, 103, 0.12);
        }

        .staff-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 62px;
            height: 62px;
            background: #e7f5f5;
            border-radius: 15px;
            font-size: 1.8rem;
            margin-bottom: 18px;
        }

        .staff-feature-card h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #173e61;
        }

        .staff-feature-card p {
            color: #617488;
            font-size: 0.95rem;
            min-height: 65px;
        }

        .staff-feature-link {
            color: #087d85;
            font-weight: 700;
        }

        .staff-feature-link:hover {
            color: #075a60;
        }

        .staff-footer {
            padding: 28px 0;
            color: #738599;
            font-size: 0.85rem;
        }

        @media (max-width: 576px) {
            .staff-hero {
                padding: 24px;
            }

            .staff-hero h1 {
                font-size: 1.55rem;
            }

            .staff-feature-card {
                padding: 22px;
            }

            .staff-feature-card p {
                min-height: auto;
            }
        }
    </style>
</head>

<body>

<nav class="staff-navbar">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

            <a
                href="admin-dashboard.php"
                class="staff-brand"
            >
                🏥 Clinic Appointment System
            </a>

            <div class="d-flex align-items-center gap-3">

                <span class="text-white">
                    <?= escapeDashboard($roleLabel) ?>
                </span>

                <a
                    href="../logout.php"
                    class="btn btn-outline-light btn-sm"
                >
                    Logout
                </a>

            </div>

        </div>
    </div>
</nav>

<main class="container py-5">

    <?php if ($accessError !== ''): ?>
        <div class="alert alert-warning">
            <?= escapeDashboard($accessError) ?>
        </div>
    <?php endif; ?>

    <section class="staff-hero">

        <span class="badge bg-light text-dark mb-3">
            <?= escapeDashboard($roleLabel) ?> Portal
        </span>

        <h1>
            Welcome,
            <?= escapeDashboard($fullName) ?>!
        </h1>

        <p class="mb-0">
            Access the clinic's management tools and
            keep daily operations organized.
        </p>

    </section>

    <section aria-labelledby="management-heading">

        <div class="mb-4">

            <h2
                id="management-heading"
                class="fw-bold"
                style="color: #173e61;"
            >
                Management Services
            </h2>

            <p class="text-muted">
                Select a service to get started.
            </p>

        </div>

        <div class="row g-4">

            <?php foreach ($features as $feature): ?>

                <div class="col-12 col-md-6">

                    <div class="staff-feature-card">

                        <div class="staff-icon" aria-hidden="true">
                            <?= escapeDashboard($feature['icon']) ?>
                        </div>

                        

                        <h3>
                            <?= escapeDashboard($feature['title']) ?>
                        </h3>

                        <p>
                            <?= escapeDashboard($feature['description']) ?>
                        </p>

                        <a
                            href="<?= escapeDashboard($feature['link']) ?>"
                            class="staff-feature-link stretched-link"
                        >
                            Open Management
                            &rarr;
                        </a>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </section>

</main>

<footer class="staff-footer text-center">
    <div class="container">
        Clinic Appointment System
        &middot;
        Staff Management Portal
    </div>
</footer>

</body>
</html>