<?php

/**
 * Patient Dashboard
 * Clinic Appointment System
 *
 * Personalized patient portal.
 * Existing authentication and navigation are preserved.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

$accessError = $_SESSION['access_error'] ?? '';

unset($_SESSION['access_error']);

$fullName = trim(
    (string) ($_SESSION['full_name'] ?? 'Patient')
);

if ($fullName === '') {
    $fullName = 'Patient';
}

function patientDashboardEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$today = new DateTimeImmutable(
    'now',
    new DateTimeZone('Asia/Kabul')
);

$hour = (int) $today->format('G');

if ($hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour < 17) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
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

    <title>
        Patient Dashboard | Clinic Appointment System
    </title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {
            --navy: #102d46;
            --navy-dark: #0c2336;
            --navy-soft: #183d56;

            --teal: #087d85;
            --teal-dark: #08656d;
            --teal-light: #e7f5f3;

            --ink: #17364b;
            --muted: #728798;

            --background: #f4f8fa;
            --white: #ffffff;
            --border: #e3ebef;

            --shadow:
                0 10px 32px rgba(23, 64, 89, 0.045);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;

            background: var(--background);

            font-family:
                "DM Sans",
                "Segoe UI",
                sans-serif;

            color: var(--ink);

            -webkit-font-smoothing: antialiased;
        }

        button,
        input {
            font: inherit;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        a:focus-visible {
            outline: 3px solid #39b7b0;
            outline-offset: 4px;
        }

        svg {
            width: 21px;
            height: 21px;
            flex-shrink: 0;
        }

        /* Application layout */

        .dashboard-layout {
            display: grid;
            grid-template-columns: 270px minmax(0, 1fr);

            min-height: 100vh;
        }

        /* Sidebar */

        .sidebar {
            position: sticky;
            top: 0;

            height: 100vh;

            display: flex;
            flex-direction: column;

            padding: 32px 19px 22px;

            color: #ffffff;

            background:
                radial-gradient(
                    circle at 5% 96%,
                    rgba(54, 175, 169, 0.15),
                    transparent 42%
                ),
                linear-gradient(
                    170deg,
                    #102d46 0%,
                    #102d46 65%,
                    #0c3444 100%
                );

            overflow-y: auto;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 13px;

            padding: 3px 12px 32px;
        }

        .brand-mark {
            width: 43px;
            height: 43px;

            display: grid;
            place-items: center;

            border-radius: 13px;

            background: rgba(117, 220, 204, 0.12);
            border: 1px solid rgba(138, 228, 213, 0.2);

            color: #9de7dc;
        }

        .brand-mark svg {
            width: 25px;
            height: 25px;
        }

        .brand-text {
            color: #ffffff;

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: 0.89rem;
            font-weight: 800;
            line-height: 1.45;

            letter-spacing: -0.035em;
        }

        .sidebar-section-label {
            padding: 14px 15px 12px;

            color: #91aebb;

            font-size: 0.66rem;
            font-weight: 700;
            letter-spacing: 0.17em;
            text-transform: uppercase;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 13px;

            min-height: 47px;

            padding: 12px 15px;

            border: 1px solid transparent;
            border-radius: 12px;

            color: #bcd0da;

            font-size: 0.9rem;
            font-weight: 600;

            transition:
                background 0.2s ease,
                color 0.2s ease,
                border-color 0.2s ease;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.075);
            color: #ffffff;
        }

        .nav-item.active {
            color: #ffffff;
            background: rgba(113, 209, 195, 0.13);
            border-color: rgba(152, 226, 215, 0.12);
        }

        .nav-item.active svg {
            color: #8ce5d6;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 30px;
        }

        .sidebar-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin-bottom: 16px;
        }

        .logout-link {
            color: #d1dee3;
        }

        .logout-link:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        /* Main content */

        .main-area {
            min-width: 0;
        }

        .topbar {
            min-height: 91px;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;

            padding: 18px 44px;

            background: #ffffff;
            border-bottom: 1px solid var(--border);
        }

        .topbar-label {
            margin: 0 0 6px;

            color: var(--teal);

            font-size: 0.68rem;
            font-weight: 800;
            letter-spacing: 0.15em;
            text-transform: uppercase;
        }

        .topbar-title {
            margin: 0;

            color: var(--ink);

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: 1.08rem;
            font-weight: 800;

            letter-spacing: -0.04em;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .today-text {
            color: var(--muted);
            font-size: 0.86rem;
            white-space: nowrap;
        }

        .profile-chip {
            display: inline-flex;
            align-items: center;
            gap: 10px;

            padding: 7px 12px 7px 7px;

            border: 1px solid var(--border);
            border-radius: 30px;

            color: var(--ink);
            background: #ffffff;

            font-size: 0.86rem;
            font-weight: 700;
        }

        .profile-avatar {
            display: grid;
            place-items: center;

            width: 32px;
            height: 32px;

            border-radius: 50%;

            color: #087d85;
            background: #e7f5f3;
        }

        .profile-avatar svg {
            width: 17px;
            height: 17px;
        }

        /* Content wrapper */

        .dashboard-content {
            max-width: 1500px;

            margin: 0 auto;
            padding: 39px 44px 50px;
        }

        .access-alert {
            margin-bottom: 26px;

            padding: 16px 19px;

            background: #fff8e6;
            border: 1px solid #f0dfb0;
            border-radius: 13px;

            color: #785b21;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        /* Hero */

        .hero {
            position: relative;
            isolation: isolate;

            overflow: hidden;

            display: grid;
            grid-template-columns:
                minmax(0, 1.25fr)
                minmax(250px, 0.75fr);

            gap: 30px;
            align-items: center;

            min-height: 300px;

            padding: 42px 45px;

            border-radius: 24px;

            color: #ffffff;

            background:
                radial-gradient(
                    circle at 95% 15%,
                    rgba(66, 193, 178, 0.22),
                    transparent 38%
                ),
                linear-gradient(
                    120deg,
                    #102d46 0%,
                    #17435a 58%,
                    #0c5860 100%
                );
        }

        .hero::before {
            content: "";

            position: absolute;
            z-index: -1;

            width: 430px;
            height: 430px;

            right: -200px;
            bottom: -285px;

            border: 1px solid rgba(174, 240, 225, 0.18);
            border-radius: 50%;

            box-shadow:
                0 0 0 46px rgba(174, 240, 225, 0.025),
                0 0 0 90px rgba(174, 240, 225, 0.025);
        }

        .hero-kicker {
            display: inline-block;

            margin-bottom: 18px;

            color: #a8e7dc;

            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.13em;
            text-transform: uppercase;
        }

        .hero h1 {
            max-width: 660px;
            margin: 0 0 17px;

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: clamp(1.9rem, 3vw, 2.8rem);
            font-weight: 800;
            line-height: 1.25;
            letter-spacing: -0.065em;
        }

        .hero p {
            max-width: 560px;

            margin: 0;

            color: #c8e0e6;

            font-size: 0.96rem;
            line-height: 1.8;
        }

        .hero-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;

            margin-top: 29px;
        }

        .hero-primary,
        .hero-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 11px;

            min-height: 46px;

            padding: 12px 18px;

            border-radius: 11px;

            font-size: 0.88rem;
            font-weight: 700;

            transition:
                transform 0.2s ease,
                background 0.2s ease;
        }

        .hero-primary {
            color: #0e3a45;
            background: #a7e7dc;
        }

        .hero-primary:hover {
            color: #0a3039;
            background: #c2f2e9;
            transform: translateY(-2px);
        }

        .hero-secondary {
            color: #ffffff;

            border: 1px solid rgba(219, 245, 243, 0.35);
            background: rgba(255, 255, 255, 0.065);
        }

        .hero-secondary:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.13);
        }

        .hero-buttons svg {
            width: 18px;
            height: 18px;
        }

        /* Hero booking guide */

        .hero-guide {
            padding: 23px 24px;

            border: 1px solid rgba(226, 250, 245, 0.2);
            border-radius: 17px;

            background: rgba(255, 255, 255, 0.09);

            backdrop-filter: blur(12px);
        }

        .guide-title {
            margin: 0 0 23px;

            color: #ffffff;

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: 0.88rem;
            font-weight: 800;
        }

        .guide-step {
            display: flex;
            align-items: center;
            gap: 13px;

            margin-bottom: 17px;

            color: #e0edf0;
            font-size: 0.83rem;
            font-weight: 500;
        }

        .guide-step:last-child {
            margin-bottom: 0;
        }

        .guide-number {
            display: grid;
            place-items: center;

            width: 30px;
            height: 30px;
            flex-shrink: 0;

            border: 1px solid rgba(191, 242, 233, 0.3);
            border-radius: 9px;

            background: rgba(183, 241, 231, 0.12);
            color: #b9f1e8;

            font-size: 0.78rem;
            font-weight: 800;
        }

        /* Section headings */

        .section-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;

            margin: 37px 0 20px;
        }

        .section-eyebrow {
            display: block;

            margin-bottom: 8px;

            color: var(--teal);

            font-size: 0.7rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .section-title {
            margin: 0;

            color: var(--ink);

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -0.05em;
        }

        .section-description {
            margin: 7px 0 0;

            color: var(--muted);
            font-size: 0.89rem;
            line-height: 1.6;
        }

        /* Action cards */

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .action-card {
            position: relative;

            display: flex;
            flex-direction: column;

            min-height: 228px;

            padding: 26px;

            border: 1px solid var(--border);
            border-radius: 19px;

            background: #ffffff;
            box-shadow: var(--shadow);

            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                border-color 0.2s ease;
        }

        .action-card:hover {
            transform: translateY(-4px);

            border-color: #c7e4df;

            box-shadow:
                0 16px 40px rgba(17, 76, 88, 0.09);
        }

        .action-icon {
            display: grid;
            place-items: center;

            width: 48px;
            height: 48px;

            margin-bottom: 24px;

            border-radius: 14px;

            color: #087d85;
            background: #e9f6f3;
        }

        .action-icon.blue {
            color: #245f93;
            background: #e9f2fa;
        }

        .action-icon.purple {
            color: #725ca3;
            background: #f0edfa;
        }

        .action-title {
            margin: 0 0 9px;

            color: var(--ink);

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: 1.04rem;
            font-weight: 800;
            letter-spacing: -0.035em;
        }

        .action-description {
            margin: 0 0 24px;

            color: var(--muted);

            font-size: 0.86rem;
            line-height: 1.75;
        }

        .action-link {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;

            margin-top: auto;

            color: #087d85;

            font-size: 0.86rem;
            font-weight: 800;
        }

        .action-link svg {
            width: 18px;
            height: 18px;

            transition: transform 0.2s ease;
        }

        .action-card:hover .action-link svg {
            transform: translateX(4px);
        }

        /* Lower content */

        .lower-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 20px;

            margin-top: 24px;
        }

        .information-card {
            padding: 28px;

            border: 1px solid var(--border);
            border-radius: 19px;

            background: #ffffff;

            box-shadow: var(--shadow);
        }

        .information-card h3 {
            margin: 0 0 11px;

            color: var(--ink);

            font-family:
                "Manrope",
                "Segoe UI",
                sans-serif;

            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.035em;
        }

        .information-card p {
            margin: 0;

            color: var(--muted);

            font-size: 0.87rem;
            line-height: 1.75;
        }

        .information-card .card-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;

            margin-top: 24px;

            color: var(--teal);

            font-size: 0.86rem;
            font-weight: 800;
        }

        .information-card .card-link:hover {
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        .card-link svg {
            width: 17px;
            height: 17px;
        }

        .support-note {
            display: flex;
            gap: 15px;
            align-items: flex-start;
        }

        .support-icon {
            display: grid;
            place-items: center;

            width: 44px;
            height: 44px;
            flex-shrink: 0;

            border-radius: 13px;

            background: #eaf5f3;
            color: var(--teal);
        }

        /* Footer */

        .dashboard-footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;

            margin-top: 36px;

            color: #8799a7;
            font-size: 0.76rem;
        }

        /* Responsive design */

        @media (max-width: 1190px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .hero-guide {
                max-width: 420px;
            }
        }

        @media (max-width: 1050px) {
            .dashboard-layout {
                grid-template-columns: 220px minmax(0, 1fr);
            }

            .topbar {
                padding-left: 28px;
                padding-right: 28px;
            }

            .dashboard-content {
                padding-left: 28px;
                padding-right: 28px;
            }

            .hero {
                padding: 35px;
            }
        }

        @media (max-width: 850px) {
            .dashboard-layout {
                display: block;
            }

            .sidebar {
                position: static;
                height: auto;

                padding: 16px 20px;
            }

            .sidebar-brand {
                padding: 0 0 12px;
            }

            .sidebar-section-label,
            .sidebar-bottom {
                display: none;
            }

            .sidebar-nav {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .nav-item {
                padding: 10px 12px;
                min-height: 40px;
            }

            .topbar {
                padding: 18px 24px;
            }

            .dashboard-content {
                padding: 28px 24px 40px;
            }

            .actions-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .lower-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .topbar {
                align-items: flex-start;
                flex-direction: column;
                gap: 12px;
            }

            .today-text {
                font-size: 0.8rem;
            }

            .dashboard-content {
                padding: 19px 14px 30px;
            }

            .hero {
                padding: 27px 24px;
                border-radius: 18px;
            }

            .hero h1 {
                font-size: 1.9rem;
            }

            .hero-guide {
                padding: 20px;
            }

            .hero-buttons {
                flex-direction: column;
            }

            .hero-primary,
            .hero-secondary {
                width: 100%;
            }

            .actions-grid {
                grid-template-columns: 1fr;
            }

            .action-card {
                min-height: 200px;
            }

            .information-card {
                padding: 23px;
            }

            .section-heading {
                margin-top: 29px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                transition-duration: 0.01ms !important;
            }
        }

    </style>

</head>

<body>

<div class="dashboard-layout">

    <!-- Sidebar -->

    <aside class="sidebar">

        <div class="sidebar-brand">

            <div class="brand-mark" aria-hidden="true">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M12 3v18M3 12h18"/>
                </svg>

            </div>

            <div class="brand-text">
                Clinic Appointment<br>System
            </div>

        </div>

        <div class="sidebar-section-label">
            Patient Portal
        </div>

        <nav
            class="sidebar-nav"
            aria-label="Patient navigation"
        >

            <a
                href="patient-dashboard.php"
                class="nav-item active"
                aria-current="page"
            >

                <svg viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>

                Dashboard
            </a>

            <a
                href="../patient/doctor-search.php"
                class="nav-item"
            >

                <svg viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-4-4"/>
                </svg>

                Find a Doctor
            </a>

            <a
                href="../patient/appointments.php"
                class="nav-item"
            >

                <svg viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M7 3v4M17 3v4M3 10h18"/>
                    <path d="m9 15 2 2 4-4"/>
                </svg>

                My Appointments
            </a>

            <a
                href="../patient/profile.php"
                class="nav-item"
            >

                <svg viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21a8 8 0 0 1 16 0"/>
                </svg>

                My Profile
            </a>

        </nav>

        <div class="sidebar-bottom">

            <div class="sidebar-divider"></div>

            <a
                href="../logout.php"
                class="nav-item logout-link"
            >

                <svg viewBox="0 0 24 24"
                     fill="none" stroke="currentColor"
                     stroke-width="1.8"
                     stroke-linecap="round"
                     stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <path d="m16 17 5-5-5-5M21 12H9"/>
                </svg>

                Logout
            </a>

        </div>

    </aside>

    <!-- Main dashboard -->

    <div class="main-area">

        <header class="topbar">

            <div>

                <p class="topbar-label">
                    Patient Portal
                </p>

                <h2 class="topbar-title">
                    My Dashboard
                </h2>

            </div>

            <div class="topbar-right">

                <span class="today-text">
                    <?= patientDashboardEscape(
                        $today->format('l, F j, Y')
                    ) ?>
                </span>

                <a
                    href="../patient/profile.php"
                    class="profile-chip"
                >

                    <span class="profile-avatar">

                        <svg viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.9"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21a8 8 0 0 1 16 0"/>
                        </svg>

                    </span>

                    My Account

                </a>

            </div>

        </header>

        <main class="dashboard-content">

            <?php if ($accessError !== ''): ?>

                <div class="access-alert" role="alert">
                    <?= patientDashboardEscape($accessError) ?>
                </div>

            <?php endif; ?>

            <!-- Welcome hero -->

            <section class="hero">

                <div class="hero-main">

                    <span class="hero-kicker">
                        <?= patientDashboardEscape($greeting) ?>
                    </span>

                    <h1>
                        Welcome back,<br>
                        <?= patientDashboardEscape($fullName) ?>.
                    </h1>

                    <p>
                        Everything you need to find care,
                        manage appointments, and keep your
                        clinic experience organized.
                    </p>

                    <div class="hero-buttons">

                        <a
                            href="../patient/doctor-search.php"
                            class="hero-primary"
                        >

                            <svg viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="1.9"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">
                                <circle cx="11" cy="11" r="7"/>
                                <path d="m20 20-4-4"/>
                            </svg>

                            Find a Doctor

                        </a>

                        <a
                            href="../patient/appointments.php"
                            class="hero-secondary"
                        >

                            My Appointments

                            <svg viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="1.9"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">
                                <path d="M5 12h14m-6-6 6 6-6 6"/>
                            </svg>

                        </a>

                    </div>

                </div>

                <div class="hero-guide">

                    <h2 class="guide-title">
                        Booking an appointment
                    </h2>

                    <div class="guide-step">
                        <span class="guide-number">01</span>
                        Find a doctor
                    </div>

                    <div class="guide-step">
                        <span class="guide-number">02</span>
                        Select an available time
                    </div>

                    <div class="guide-step">
                        <span class="guide-number">03</span>
                        Confirm your booking
                    </div>

                </div>

            </section>

            <!-- Main actions -->

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">
                        Your Workspace
                    </span>

                    <h2 class="section-title">
                        What would you like to do?
                    </h2>

                    <p class="section-description">
                        Access the services you need from one place.
                    </p>
                </div>

            </div>

            <section
                class="actions-grid"
                aria-label="Patient services"
            >

                <a
                    href="../patient/doctor-search.php"
                    class="action-card"
                >

                    <span class="action-icon">

                        <svg viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m20 20-4-4"/>
                        </svg>

                    </span>

                    <h3 class="action-title">
                        Find a Doctor
                    </h3>

                    <p class="action-description">
                        Explore doctors by name or
                        specialty and view their
                        available appointment times.
                    </p>

                    <span class="action-link">
                        Explore Doctors

                        <svg viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M5 12h14m-6-6 6 6-6 6"/>
                        </svg>
                    </span>

                </a>

                <a
                    href="../patient/appointments.php"
                    class="action-card"
                >

                    <span class="action-icon blue">

                        <svg viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <rect x="3" y="5"
                                  width="18" height="16" rx="2"/>
                            <path d="M7 3v4M17 3v4M3 10h18"/>
                            <path d="m9 15 2 2 4-4"/>
                        </svg>

                    </span>

                    <h3 class="action-title">
                        My Appointments
                    </h3>

                    <p class="action-description">
                        Review your appointment
                        schedule, history, and
                        available management options.
                    </p>

                    <span class="action-link">
                        View Appointments

                        <svg viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M5 12h14m-6-6 6 6-6 6"/>
                        </svg>
                    </span>

                </a>

                <a
                    href="../patient/profile.php"
                    class="action-card"
                >

                    <span class="action-icon purple">

                        <svg viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="1.8"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21a8 8 0 0 1 16 0"/>
                        </svg>

                    </span>

                    <h3 class="action-title">
                        My Profile
                    </h3>

                    <p class="action-description">
                        Review your personal details
                        and keep your contact
                        information up to date.
                    </p>

                    <span class="action-link">
                        Manage Profile

                        <svg viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M5 12h14m-6-6 6 6-6 6"/>
                        </svg>
                    </span>

                </a>

            </section>

            <!-- Supporting information -->

            <section class="lower-grid">

                <div class="information-card">

                    <h3>
                        Keep track of your visits
                    </h3>

                    <p>
                        Your appointment page brings your
                        scheduled visits, past appointments,
                        and available cancellation or
                        rescheduling options together.
                    </p>

                    <a
                        href="../patient/appointments.php"
                        class="card-link"
                    >
                        Open My Appointments

                        <svg viewBox="0 0 24 24"
                             fill="none" stroke="currentColor"
                             stroke-width="2"
                             stroke-linecap="round"
                             stroke-linejoin="round">
                            <path d="M5 12h14m-6-6 6 6-6 6"/>
                        </svg>
                    </a>

                </div>

                <div class="information-card">

                    <div class="support-note">

                        <span class="support-icon">

                            <svg viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor"
                                 stroke-width="1.8"
                                 stroke-linecap="round"
                                 stroke-linejoin="round">
                                <path d="M12 3 4 7v5c0 5 3.5 8 8 9 4.5-1 8-4 8-9V7l-8-4Z"/>
                                <path d="m9 12 2 2 4-4"/>
                            </svg>

                        </span>

                        <div>

                            <h3>
                                Your information matters
                            </h3>

                            <p>
                                Keep your profile details
                                accurate so your clinic
                                information stays up to date.
                            </p>

                            <a
                                href="../patient/profile.php"
                                class="card-link"
                            >
                                Review My Profile

                                <svg viewBox="0 0 24 24"
                                     fill="none" stroke="currentColor"
                                     stroke-width="2"
                                     stroke-linecap="round"
                                     stroke-linejoin="round">
                                    <path d="M5 12h14m-6-6 6 6-6 6"/>
                                </svg>
                            </a>

                        </div>

                    </div>

                </div>

            </section>

            <footer class="dashboard-footer">

                <span>
                    Clinic Appointment System
                </span>

                <span>
                    Patient Portal
                </span>

            </footer>

        </main>

    </div>

</div>

</body>

</html>