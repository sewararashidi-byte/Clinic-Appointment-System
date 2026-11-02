<?php

/**
 * Doctor Dashboard
 * Clinic Appointment System
 *
 * Doctor-only workspace with appointment,
 * availability and profile navigation.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['doctor']);

// Preserve existing one-time notifications.
$accessError = (string) (
    $_SESSION['access_error'] ?? ''
);

$availabilitySuccess = (string) (
    $_SESSION['availability_success'] ?? ''
);

$availabilityError = (string) (
    $_SESSION['availability_error'] ?? ''
);

unset(
    $_SESSION['access_error'],
    $_SESSION['availability_success'],
    $_SESSION['availability_error']
);

$fullName = trim(
    (string) ($_SESSION['full_name'] ?? 'Doctor')
);

if ($fullName === '') {
    $fullName = 'Doctor';
}

function doctorDashboardEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Display genuine local date and greeting.
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
        Doctor Dashboard | Clinic Appointment System
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

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
            --navy-deep: #0b2639;
            --navy-soft: #17455a;

            --teal: #087d85;
            --teal-deep: #08656d;
            --teal-light: #e8f6f3;

            --ink: #17384d;
            --muted: #6f8595;

            --background: #f3f8fa;
            --surface: #ffffff;
            --border: #e1ebef;

            --shadow: 0 12px 34px rgba(20, 64, 86, .05);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;

            background:
                radial-gradient(
                    ellipse at 95% 0%,
                    rgba(95, 186, 182, .065),
                    transparent 38%
                ),
                var(--background);

            color: var(--ink);
            font-family: "DM Sans", "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button {
            font: inherit;
            cursor: pointer;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid rgba(8, 125, 133, .35);
            outline-offset: 3px;
        }

        svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* Application shell */

        .app-shell {
            display: grid;
            grid-template-columns: 268px minmax(0, 1fr);
            min-height: 100vh;
        }

        /* Sidebar */

        .sidebar {
            position: sticky;
            top: 0;

            display: flex;
            flex-direction: column;

            height: 100vh;
            padding: 29px 18px 22px;
            overflow-y: auto;

            color: white;

            background:
                radial-gradient(
                    circle at 8% 92%,
                    rgba(65, 185, 173, .16),
                    transparent 43%
                ),
                linear-gradient(
                    160deg,
                    #102d46 0%,
                    #102d46 65%,
                    #103949 100%
                );
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;

            padding: 4px 12px 37px;
        }

        .brand-icon {
            display: grid;
            place-items: center;

            width: 43px;
            height: 43px;
            flex-shrink: 0;

            border: 1px solid rgba(156, 233, 218, .23);
            border-radius: 13px;

            background: rgba(156, 233, 218, .11);
            color: #9be9dc;
        }

        .brand-icon svg {
            width: 26px;
            height: 26px;
        }

        .brand-name {
            font-family: "Manrope", sans-serif;
            font-size: .88rem;
            font-weight: 800;
            letter-spacing: -.035em;
            line-height: 1.45;
        }

        .sidebar-label {
            padding: 0 15px 14px;

            color: #8eafbb;
            font-size: .67rem;
            font-weight: 800;
            letter-spacing: .17em;
            text-transform: uppercase;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 13px;

            min-height: 47px;
            padding: 12px 15px;

            border: 1px solid transparent;
            border-radius: 12px;

            color: #bdd0da;
            font-size: .88rem;
            font-weight: 600;

            transition: background .2s, color .2s;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, .075);
            color: white;
        }

        .nav-link.active {
            background: rgba(112, 210, 196, .14);
            border-color: rgba(152, 227, 215, .13);
            color: white;
        }

        .nav-link.active svg {
            color: #92e6d7;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 25px;
        }

        .sidebar-bottom::before {
            content: "";
            display: block;
            height: 1px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, .11);
        }

        .sidebar-role {
            display: flex;
            align-items: center;
            gap: 10px;

            margin: 0 5px 17px;
            padding: 12px;

            border: 1px solid rgba(255, 255, 255, .09);
            border-radius: 12px;

            background: rgba(255, 255, 255, .045);
        }

        .role-indicator {
            width: 8px;
            height: 8px;
            flex-shrink: 0;
            border-radius: 50%;
            background: #92e6d7;
        }

        .role-text {
            color: #d6e7e9;
            font-size: .78rem;
            font-weight: 600;
        }

        /* Main area */

        .main-area {
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 18px;

            min-height: 89px;
            padding: 18px 43px;

            background: #ffffff;
            border-bottom: 1px solid var(--border);
        }

        .topbar-eyebrow {
            display: block;
            margin-bottom: 5px;

            color: var(--teal);
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .topbar-title {
            margin: 0;

            font-family: "Manrope", sans-serif;
            font-size: 1.07rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 17px;
        }

        .topbar-date {
            color: #8397a3;
            font-size: .83rem;
            white-space: nowrap;
        }

        .account-chip {
            display: flex;
            align-items: center;
            gap: 10px;

            padding: 7px 14px 7px 7px;

            border: 1px solid var(--border);
            border-radius: 30px;
            background: white;

            font-size: .83rem;
            font-weight: 700;
        }

        .account-chip:hover {
            background: #f4faf9;
        }

        .account-avatar {
            display: grid;
            place-items: center;

            width: 34px;
            height: 34px;
            flex-shrink: 0;

            border-radius: 50%;
            background: var(--teal-light);
            color: var(--teal);
        }

        .account-avatar svg {
            width: 19px;
            height: 19px;
        }

        .content {
            width: 100%;
            max-width: 1520px;

            margin: 0 auto;
            padding: 39px 43px 52px;
        }

        /* Messages */

        .notice {
            margin-bottom: 20px;
            padding: 17px 20px;

            border: 1px solid;
            border-radius: 13px;

            font-size: .89rem;
            line-height: 1.7;
        }

        .notice strong {
            display: block;
            margin-bottom: 5px;
        }

        .notice.warning {
            background: #fff8e8;
            border-color: #f0dfb7;
            color: #816122;
        }

        .notice.success {
            background: #eaf8ef;
            border-color: #c8e9d3;
            color: #226d4c;
        }

        .notice.error {
            background: #fff2f1;
            border-color: #efd0cf;
            color: #9e3e44;
        }

        /* Doctor welcome hero */

        .hero {
            position: relative;
            isolation: isolate;

            display: grid;
            grid-template-columns: minmax(0, 1.4fr)
                                   minmax(240px, .6fr);
            align-items: center;
            gap: 36px;

            min-height: 325px;
            padding: 43px 45px;

            overflow: hidden;
            border-radius: 24px;
            color: white;

            background:
                radial-gradient(
                    circle at 92% 12%,
                    rgba(83, 203, 185, .22),
                    transparent 42%
                ),
                linear-gradient(
                    117deg,
                    #102d46 0%,
                    #173e56 59%,
                    #0b5860 100%
                );
        }

        .hero::before {
            content: "";
            position: absolute;
            z-index: -1;

            width: 420px;
            height: 420px;
            right: -230px;
            bottom: -315px;

            border: 1px solid rgba(179, 242, 230, .22);
            border-radius: 50%;

            box-shadow:
                0 0 0 50px rgba(179, 242, 230, .025),
                0 0 0 105px rgba(179, 242, 230, .02);
        }

        .hero-eyebrow {
            display: block;
            margin-bottom: 18px;

            color: #a7ebdf;
            font-size: .71rem;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .hero h1 {
            max-width: 760px;
            margin: 0 0 17px;

            font-family: "Manrope", sans-serif;
            font-size: clamp(2rem, 3.2vw, 3.15rem);
            font-weight: 800;
            letter-spacing: -.065em;
            line-height: 1.2;

            overflow-wrap: anywhere;
        }

        .hero-description {
            max-width: 620px;
            margin: 0;

            color: #cce1e6;
            font-size: .96rem;
            line-height: 1.85;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .hero-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            min-height: 47px;
            padding: 12px 18px;

            border: 1px solid transparent;
            border-radius: 11px;

            font-size: .86rem;
            font-weight: 800;
            transition: background .2s, transform .2s;
        }

        .hero-button svg {
            width: 18px;
            height: 18px;
        }

        .hero-button.primary {
            background: #b2eee1;
            color: #0d444c;
        }

        .hero-button.primary:hover {
            background: #ccf8ed;
            transform: translateY(-2px);
        }

        .hero-button.secondary {
            color: white;
            background: rgba(255, 255, 255, .06);
            border-color: rgba(228, 251, 246, .29);
        }

        .hero-button.secondary:hover {
            background: rgba(255, 255, 255, .14);
        }

        /* Genuine date information */

        .date-panel {
            position: relative;
            z-index: 1;

            padding: 24px;

            border: 1px solid rgba(228, 249, 246, .21);
            border-radius: 19px;

            background: rgba(255, 255, 255, .09);
            backdrop-filter: blur(12px);
        }

        .date-panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            color: #d7efeb;
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .date-panel-heading svg {
            color: #a5e8dd;
        }

        .date-number {
            margin-top: 23px;

            font-family: "Manrope", sans-serif;
            font-size: 4.6rem;
            line-height: 1;
            font-weight: 800;
            letter-spacing: -.09em;
        }

        .date-month {
            margin-top: 12px;
            color: white;
            font-family: "Manrope", sans-serif;
            font-size: 1rem;
            font-weight: 800;
        }

        .date-weekday {
            margin-top: 6px;
            color: #c3e0e2;
            font-size: .85rem;
        }

        .date-footer {
            margin-top: 24px;
            padding-top: 17px;

            border-top: 1px solid rgba(255, 255, 255, .17);

            color: #c7e5e3;
            font-size: .78rem;
        }

        /* Section headings */

        .section-heading {
            margin: 38px 0 20px;
        }

        .section-eyebrow {
            display: block;
            margin-bottom: 8px;

            color: var(--teal);
            font-size: .69rem;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .section-title {
            margin: 0;

            font-family: "Manrope", sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -.05em;
        }

        .section-description {
            margin: 9px 0 0;

            color: var(--muted);
            font-size: .88rem;
            line-height: 1.75;
        }

        /* Service cards */

        .service-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        .service-card {
            display: flex;
            flex-direction: column;
            min-width: 0;
            min-height: 265px;

            padding: 27px;

            border: 1px solid var(--border);
            border-radius: 19px;

            background: white;
            box-shadow: var(--shadow);

            transition:
                transform .2s,
                border-color .2s,
                box-shadow .2s;
        }

        .service-card:hover {
            transform: translateY(-4px);
            border-color: #bdded8;
            box-shadow: 0 16px 37px rgba(20, 64, 86, .09);
        }

        .service-icon {
            display: grid;
            place-items: center;

            width: 49px;
            height: 49px;

            margin-bottom: 25px;

            border-radius: 14px;
            background: #e8f6f3;
            color: var(--teal);
        }

        .service-icon.blue {
            color: #336b9c;
            background: #e9f2fa;
        }

        .service-icon.purple {
            color: #7461a4;
            background: #f0edfa;
        }

        .service-icon svg {
            width: 24px;
            height: 24px;
        }

        .service-title {
            margin: 0 0 10px;

            font-family: "Manrope", sans-serif;
            font-size: 1.03rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .service-description {
            margin: 0 0 25px;

            color: var(--muted);
            font-size: .86rem;
            line-height: 1.8;
        }

        .service-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;

            margin-top: auto;

            color: var(--teal);
            font-size: .86rem;
            font-weight: 800;
        }

        .service-link svg {
            width: 17px;
            height: 17px;
            transition: transform .2s;
        }

        .service-card:hover .service-link svg {
            transform: translateX(4px);
        }

        /* Lower workspace panels */

        .lower-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr)
                                   minmax(0, .9fr);
            gap: 20px;
            margin-top: 24px;
        }

        .workspace-panel {
            min-width: 0;
            padding: 29px;

            background: white;
            border: 1px solid var(--border);
            border-radius: 19px;
            box-shadow: var(--shadow);
        }

        .workspace-panel.featured {
            background: linear-gradient(
                155deg,
                #eaf7f4,
                #ffffff 90%
            );
            border-color: #d7ebe6;
        }

        .panel-eyebrow {
            display: block;
            margin-bottom: 9px;

            color: var(--teal);
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .workspace-panel h3 {
            margin: 0 0 13px;

            font-family: "Manrope", sans-serif;
            font-size: 1.08rem;
            font-weight: 800;
            letter-spacing: -.045em;
        }

        .workspace-panel > p {
            margin: 0 0 23px;

            color: #627b8b;
            font-size: .87rem;
            line-height: 1.85;
        }

        .workflow-step {
            display: flex;
            gap: 13px;
            align-items: flex-start;
            margin-bottom: 19px;
        }

        .workflow-step:last-child {
            margin-bottom: 0;
        }

        .step-number {
            display: grid;
            place-items: center;

            width: 33px;
            height: 33px;
            flex-shrink: 0;

            border-radius: 10px;

            background: #dcefe9;
            color: var(--teal);

            font-size: .77rem;
            font-weight: 800;
        }

        .workflow-step h4 {
            margin: 2px 0 5px;

            font-family: "Manrope", sans-serif;
            font-size: .85rem;
            font-weight: 800;
        }

        .workflow-step p {
            margin: 0;

            color: #68818e;
            font-size: .81rem;
            line-height: 1.7;
        }

        .shortcut-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;

            padding: 17px 0;

            border-bottom: 1px solid var(--border);

            color: #244a5e;
            font-size: .86rem;
            font-weight: 700;
        }

        .shortcut-link:first-of-type {
            margin-top: 5px;
        }

        .shortcut-link:last-of-type {
            border-bottom: none;
        }

        .shortcut-link:hover {
            color: var(--teal);
        }

        .shortcut-link svg {
            width: 17px;
            height: 17px;
            color: var(--teal);
        }

        .footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;

            margin-top: 36px;

            color: #889ba8;
            font-size: .77rem;
        }

        /* Responsive layout */

        @media (max-width: 1190px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .date-panel {
                max-width: 330px;
            }

            .service-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 990px) {
            .app-shell {
                grid-template-columns: 220px minmax(0, 1fr);
            }

            .topbar {
                padding: 18px 27px;
            }

            .content {
                padding: 30px 27px 45px;
            }

            .lower-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 850px) {
            .app-shell {
                display: block;
            }

            .sidebar {
                position: static;
                height: auto;
                padding: 16px 20px;
            }

            .brand {
                padding: 0 0 14px;
            }

            .sidebar-label,
            .sidebar-role {
                display: none;
            }

            .sidebar-nav {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .sidebar-bottom {
                margin-top: 10px;
                padding-top: 9px;
            }

            .sidebar-bottom::before {
                display: none;
            }

            .topbar {
                padding: 17px 23px;
            }

            .content {
                padding: 25px 23px 40px;
            }

            .hero {
                padding: 35px;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                padding: 15px 13px;
            }

            .sidebar-nav {
                gap: 5px;
            }

            .nav-link {
                min-height: 39px;
                padding: 9px 10px;
                font-size: .79rem;
            }

            .nav-link svg {
                width: 17px;
                height: 17px;
            }

            .topbar {
                align-items: flex-start;
                padding: 17px;
            }

            .topbar-right {
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
            }

            .topbar-date {
                font-size: .77rem;
            }

            .content {
                padding: 19px 13px 32px;
            }

            .hero {
                padding: 29px 24px;
                border-radius: 19px;
            }

            .hero h1 {
                font-size: 2rem;
            }

            .hero-description {
                font-size: .88rem;
            }

            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-button {
                width: 100%;
            }

            .date-panel {
                max-width: none;
            }

            .date-number {
                font-size: 3.7rem;
            }

            .service-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .service-card {
                min-height: 225px;
                padding: 24px;
            }

            .workspace-panel {
                padding: 24px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                transition-duration: .01ms !important;
            }
        }
    </style>

</head>

<body>

<div class="app-shell">

    <!-- Doctor navigation -->

    <aside class="sidebar">

        <a
            href="doctor-dashboard.php"
            class="brand"
        >

            <span class="brand-icon" aria-hidden="true">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                    stroke-linecap="round"
                >
                    <path d="M12 3v18M3 12h18"/>
                </svg>

            </span>

            <span class="brand-name">
                Clinic Appointment<br>
                System
            </span>

        </a>

        <div class="sidebar-label">
            Doctor Portal
        </div>

        <nav
            class="sidebar-nav"
            aria-label="Doctor navigation"
        >

            <a
                href="doctor-dashboard.php"
                class="nav-link active"
                aria-current="page"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>

                Dashboard

            </a>

            <a
                href="../doctor/appointments.php"
                class="nav-link"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M7 3v4M17 3v4M3 10h18"/>
                    <path d="m9 15 2 2 4-4"/>
                </svg>

                My Appointments

            </a>

            <a
                href="../doctor/availability.php"
                class="nav-link"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M7 3v4M17 3v4M3 10h18"/>
                    <path d="M8 15h3M13 15h3M8 18h3"/>
                </svg>

                My Availability

            </a>

            <a
                href="../doctor/profile.php"
                class="nav-link"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21a8 8 0 0 1 16 0"/>
                </svg>

                My Profile

            </a>

        </nav>

        <div class="sidebar-bottom">

            <div class="sidebar-role">

                <span
                    class="role-indicator"
                    aria-hidden="true"
                ></span>

                <span class="role-text">
                    Doctor Account
                </span>

            </div>

            <a
                href="../logout.php"
                class="nav-link"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
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

                <span class="topbar-eyebrow">
                    Doctor Portal
                </span>

                <h2 class="topbar-title">
                    My Dashboard
                </h2>

            </div>

            <div class="topbar-right">

                <span class="topbar-date">
                    <?= doctorDashboardEscape(
                        $today->format('l, F j, Y')
                    ) ?>
                </span>

                <a
                    href="../doctor/profile.php"
                    class="account-chip"
                >

                    <span class="account-avatar">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21a8 8 0 0 1 16 0"/>
                        </svg>

                    </span>

                    My Account

                </a>

            </div>

        </header>

        <main class="content">

            <!-- Existing session notifications -->

            <?php if ($accessError !== ''): ?>

                <div class="notice warning" role="alert">

                    <strong>
                        Access Notice
                    </strong>

                    <?= doctorDashboardEscape($accessError) ?>

                </div>

            <?php endif; ?>

            <?php if ($availabilitySuccess !== ''): ?>

                <div class="notice success" role="status">

                    <strong>
                        Availability Updated
                    </strong>

                    <?= doctorDashboardEscape(
                        $availabilitySuccess
                    ) ?>

                </div>

            <?php endif; ?>

            <?php if ($availabilityError !== ''): ?>

                <div class="notice error" role="alert">

                    <strong>
                        Availability Update Unsuccessful
                    </strong>

                    <?= doctorDashboardEscape(
                        $availabilityError
                    ) ?>

                </div>

            <?php endif; ?>

            <!-- Doctor welcome -->

            <section class="hero">

                <div>

                    <span class="hero-eyebrow">
                        <?= doctorDashboardEscape($greeting) ?>
                    </span>

                    <h1>
                        Welcome back,<br>
                        <?= doctorDashboardEscape($fullName) ?>.
                    </h1>

                    <p class="hero-description">
                        A dedicated workspace for managing
                        your clinic day. Review appointments,
                        maintain your availability, and keep
                        your professional information current.
                    </p>

                    <div class="hero-actions">

                        <a
                            href="../doctor/appointments.php"
                            class="hero-button primary"
                        >

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <rect
                                    x="3" y="5"
                                    width="18" height="16"
                                    rx="2"
                                />
                                <path d="M7 3v4M17 3v4M3 10h18"/>
                            </svg>

                            View Appointments

                        </a>

                        <a
                            href="../doctor/availability.php"
                            class="hero-button secondary"
                        >

                            Manage Availability

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M5 12h14"/>
                                <path d="m13 6 6 6-6 6"/>
                            </svg>

                        </a>

                    </div>

                </div>

                <!-- Real current date, not fictional statistics -->

                <div class="date-panel">

                    <div class="date-panel-heading">

                        <span>
                            Today's Date
                        </span>

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <rect
                                x="3" y="5"
                                width="18" height="16"
                                rx="2"
                            />
                            <path d="M7 3v4M17 3v4M3 10h18"/>
                        </svg>

                    </div>

                    <div class="date-number">
                        <?= doctorDashboardEscape(
                            $today->format('j')
                        ) ?>
                    </div>

                    <div class="date-month">
                        <?= doctorDashboardEscape(
                            $today->format('F Y')
                        ) ?>
                    </div>

                    <div class="date-weekday">
                        <?= doctorDashboardEscape(
                            $today->format('l')
                        ) ?>
                    </div>

                    <div class="date-footer">
                        Afghanistan local time
                    </div>

                </div>

            </section>

            <!-- Main doctor functions -->

            <div class="section-heading">

                <span class="section-eyebrow">
                    Your Workspace
                </span>

                <h2 class="section-title">
                    Manage Your Clinical Day
                </h2>

                <p class="section-description">
                    Access your scheduling and
                    professional information tools.
                </p>

            </div>

            <section
                class="service-grid"
                aria-label="Doctor functions"
            >

                <!-- Appointments -->

                <a
                    href="../doctor/appointments.php"
                    class="service-card"
                >

                    <span
                        class="service-icon"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect
                                x="3" y="5"
                                width="18" height="16"
                                rx="2"
                            />
                            <path d="M7 3v4M17 3v4M3 10h18"/>
                            <path d="m9 15 2 2 4-4"/>
                        </svg>
                    </span>

                    <h3 class="service-title">
                        My Appointments
                    </h3>

                    <p class="service-description">
                        Review booked visits,
                        appointment information,
                        and your patient schedule.
                    </p>

                    <span class="service-link">

                        Open Appointments

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>

                    </span>

                </a>

                <!-- Availability -->

                <a
                    href="../doctor/availability.php"
                    class="service-card"
                >

                    <span
                        class="service-icon blue"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <rect
                                x="3" y="5"
                                width="18" height="16"
                                rx="2"
                            />
                            <path d="M7 3v4M17 3v4M3 10h18"/>
                            <path d="M8 14h3M13 14h3M8 18h3"/>
                        </svg>
                    </span>

                    <h3 class="service-title">
                        My Availability
                    </h3>

                    <p class="service-description">
                        Review and manage the
                        appointment times you
                        make available to patients.
                    </p>

                    <span class="service-link">

                        Manage Schedule

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>

                    </span>

                </a>

                <!-- Doctor profile -->

                <a
                    href="../doctor/profile.php"
                    class="service-card"
                >

                    <span
                        class="service-icon purple"
                        aria-hidden="true"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M4 21a8 8 0 0 1 16 0"/>
                        </svg>
                    </span>

                    <h3 class="service-title">
                        My Professional Profile
                    </h3>

                    <p class="service-description">
                        Review your professional
                        information and keep your
                        profile details up to date.
                    </p>

                    <span class="service-link">

                        View My Profile

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>

                    </span>

                </a>

            </section>

            <!-- Supporting workspace information -->

            <section class="lower-grid">

                <div class="workspace-panel featured">

                    <span class="panel-eyebrow">
                        Schedule Management
                    </span>

                    <h3>
                        Keep Your Availability Current
                    </h3>

                    <p>
                        Your published working times
                        help patients find appointment
                        slots that fit your schedule.
                    </p>

                    <div class="workflow-step">

                        <span class="step-number">
                            01
                        </span>

                        <div>

                            <h4>
                                Review Available Times
                            </h4>

                            <p>
                                Check the availability
                                you've already provided.
                            </p>

                        </div>

                    </div>

                    <div class="workflow-step">

                        <span class="step-number">
                            02
                        </span>

                        <div>

                            <h4>
                                Update Your Schedule
                            </h4>

                            <p>
                                Use the availability
                                management page to make
                                permitted changes.
                            </p>

                        </div>

                    </div>

                    <div class="workflow-step">

                        <span class="step-number">
                            03
                        </span>

                        <div>

                            <h4>
                                Review Appointments
                            </h4>

                            <p>
                                Check your appointment
                                list for scheduled visits.
                            </p>

                        </div>

                    </div>

                </div>

                <div class="workspace-panel">

                    <span class="panel-eyebrow">
                        Quick Access
                    </span>

                    <h3>
                        Your Essential Tools
                    </h3>

                    <p>
                        Move directly between the
                        main parts of your doctor workspace.
                    </p>

                    <a
                        href="../doctor/appointments.php"
                        class="shortcut-link"
                    >
                        Review My Appointments

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>
                    </a>

                    <a
                        href="../doctor/availability.php"
                        class="shortcut-link"
                    >
                        Open Availability Management

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>
                    </a>

                    <a
                        href="../doctor/profile.php"
                        class="shortcut-link"
                    >
                        Review Professional Profile

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>
                    </a>

                    <a
                        href="../logout.php"
                        class="shortcut-link"
                    >
                        Sign Out

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M5 12h14"/>
                            <path d="m13 6 6 6-6 6"/>
                        </svg>
                    </a>

                </div>

            </section>

            <footer class="footer">

                <span>
                    Clinic Appointment System
                </span>

                <span>
                    Doctor Portal
                </span>

            </footer>

        </main>

    </div>

</div>

</body>
</html>