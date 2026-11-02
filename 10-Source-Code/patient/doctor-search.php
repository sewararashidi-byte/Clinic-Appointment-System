<?php

/**
 * Patient Doctor Search
 * Clinic Appointment System
 *
 * Premium patient-facing doctor discovery.
 * Existing search functionality is preserved.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/doctor_repository.php';

$fullName = trim(
    (string) ($_SESSION['full_name'] ?? 'Patient')
);

if ($fullName === '') {
    $fullName = 'Patient';
}

$doctorName = isset($_GET['doctor_name'])
    && is_string($_GET['doctor_name'])
        ? trim($_GET['doctor_name'])
        : '';

$specialty = isset($_GET['specialty'])
    && is_string($_GET['specialty'])
        ? trim($_GET['specialty'])
        : '';

// Preserve the existing search behavior:
// results appear after the form is submitted.
$hasSearched = isset($_GET['doctor_name'])
    || isset($_GET['specialty']);

$doctors = [];

if ($hasSearched) {
    $doctors = searchDoctors(
        $conn,
        $doctorName,
        $specialty
    );
}

$conn->close();

function doctorSearchEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function doctorSearchInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));

    if (empty($parts) || $parts[0] === '') {
        return 'DR';
    }

    $first = strtoupper(substr($parts[0], 0, 1));

    if (count($parts) === 1) {
        return $first;
    }

    $last = strtoupper(
        substr($parts[count($parts) - 1], 0, 1)
    );

    return $first . $last;
}

$resultCount = count($doctors);

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
        Find a Doctor | Clinic Appointment System
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
            --navy-deep: #0c263b;
            --teal: #087d85;
            --teal-dark: #08666e;
            --teal-soft: #e8f6f3;
            --ink: #17374d;
            --muted: #708697;
            --border: #e3ebef;
            --background: #f4f8fa;
            --white: #ffffff;
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
            color: var(--ink);

            font-family: "DM Sans", "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        button,
        input {
            font: inherit;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible {
            outline: 3px solid rgba(8,125,133,.4);
            outline-offset: 3px;
        }

        svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* Application layout */

        .app-layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            min-height: 100vh;
        }

        /* Sidebar */

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            padding: 30px 18px 22px;
            overflow-y: auto;

            background:
                radial-gradient(
                    circle at 10% 95%,
                    rgba(54,180,170,.14),
                    transparent 44%
                ),
                linear-gradient(160deg,#102d46,#102d46 65%,#0e3848);

            color: white;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 3px 12px 34px;
        }

        .brand-symbol {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            background: rgba(117,220,204,.12);
            border: 1px solid rgba(138,228,213,.2);
            color: #9de7dc;
        }

        .brand-symbol svg {
            width: 25px;
            height: 25px;
        }

        .brand-name {
            font-family: "Manrope", sans-serif;
            font-size: .88rem;
            font-weight: 800;
            line-height: 1.4;
            letter-spacing: -.035em;
        }

        .nav-label {
            margin: 0 15px 12px;
            color: #91adbb;
            font-size: .65rem;
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
            min-height: 46px;
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 11px 15px;
            border: 1px solid transparent;
            border-radius: 11px;
            color: #c0d1da;
            font-size: .89rem;
            font-weight: 600;
            transition: background .2s, color .2s;
        }

        .nav-link:hover {
            background: rgba(255,255,255,.075);
            color: white;
        }

        .nav-link.active {
            color: white;
            background: rgba(113,209,195,.14);
            border-color: rgba(152,226,215,.12);
        }

        .nav-link.active svg {
            color: #8ce5d6;
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 24px;
        }

        .sidebar-bottom::before {
            content: "";
            display: block;
            height: 1px;
            background: rgba(255,255,255,.12);
            margin-bottom: 13px;
        }

        /* Top navigation */

        .main-area {
            min-width: 0;
        }

        .topbar {
            min-height: 88px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 18px 42px;
            background: white;
            border-bottom: 1px solid var(--border);
        }

        .topbar-eyebrow {
            display: block;
            margin-bottom: 5px;
            color: var(--teal);
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .topbar-title {
            font-family: "Manrope", sans-serif;
            font-size: 1.06rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .account-link {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            padding: 7px 13px 7px 7px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 28px;
            font-size: .83rem;
            font-weight: 700;
        }

        .account-link:hover {
            background: #f7fbfb;
            border-color: #c7dfdf;
        }

        .account-avatar {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: var(--teal-soft);
            color: var(--teal);
        }

        .account-avatar svg {
            width: 18px;
            height: 18px;
        }

        /* Main content */

        .page-content {
            max-width: 1450px;
            margin: 0 auto;
            padding: 38px 42px 55px;
        }

        /* Intro panel */

        .page-intro {
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 30px;
            min-height: 210px;
            padding: 36px 42px;
            background:
                radial-gradient(
                    circle at 91% 12%,
                    rgba(77,200,184,.23),
                    transparent 38%
                ),
                linear-gradient(115deg,#102d46 0%,#17465b 68%,#0b5b60);
            border-radius: 22px;
            color: white;
        }

        .page-intro::after {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            right: -140px;
            bottom: -220px;
            border: 1px solid rgba(178,236,227,.2);
            border-radius: 50%;
            box-shadow: 0 0 0 38px rgba(178,236,227,.035);
            pointer-events: none;
        }

        .intro-content {
            position: relative;
            z-index: 1;
        }

        .intro-eyebrow {
            display: block;
            margin-bottom: 13px;
            color: #a5eadf;
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .15em;
        }

        .page-intro h1 {
            margin: 0 0 13px;
            max-width: 720px;
            font-family: "Manrope", sans-serif;
            font-size: clamp(1.85rem,3vw,2.75rem);
            font-weight: 800;
            letter-spacing: -.06em;
            line-height: 1.2;
        }

        .page-intro p {
            max-width: 640px;
            margin: 0;
            color: #cce0e8;
            font-size: .95rem;
            line-height: 1.8;
        }

        .intro-icon {
            position: relative;
            z-index: 1;
            display: grid;
            place-items: center;
            width: 96px;
            height: 96px;
            flex-shrink: 0;
            border-radius: 26px;
            background: rgba(255,255,255,.09);
            border: 1px solid rgba(255,255,255,.17);
            color: #a4e9dc;
        }

        .intro-icon svg {
            width: 50px;
            height: 50px;
        }

        /* Search panel */

        .search-panel {
            position: relative;
            margin-top: 26px;
            padding: 30px 34px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 19px;
            box-shadow: 0 12px 35px rgba(20,64,86,.045);
        }

        .panel-eyebrow {
            color: var(--teal);
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .panel-title {
            margin: 8px 0 8px;
            color: var(--ink);
            font-family: "Manrope", sans-serif;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -.045em;
        }

        .panel-description {
            margin: 0 0 25px;
            color: var(--muted);
            font-size: .88rem;
            line-height: 1.7;
        }

        .search-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            align-items: end;
            gap: 16px;
        }

        .field-group {
            min-width: 0;
        }

        .field-label {
            display: block;
            margin-bottom: 9px;
            color: #294b61;
            font-size: .86rem;
            font-weight: 700;
        }

        .field-input {
            display: block;
            width: 100%;
            min-height: 51px;
            padding: 12px 16px;
            color: var(--ink);
            background: #f8fbfc;
            border: 1px solid #dce7eb;
            border-radius: 11px;
            outline: none;
            transition: background .2s, border-color .2s, box-shadow .2s;
        }

        .field-input::placeholder {
            color: #9aabb6;
        }

        .field-input:hover {
            background: white;
            border-color: #bdd5db;
        }

        .field-input:focus {
            background: white;
            border-color: var(--teal);
            box-shadow: 0 0 0 4px rgba(8,125,133,.1);
        }

        .search-button {
            min-height: 51px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 22px;
            border: 1px solid var(--teal);
            border-radius: 11px;
            background: var(--teal);
            color: white;
            font-size: .89rem;
            font-weight: 800;
            white-space: nowrap;
            cursor: pointer;
            transition: background .2s, transform .2s;
        }

        .search-button:hover {
            background: var(--teal-dark);
            transform: translateY(-1px);
        }

        .search-button svg {
            width: 18px;
            height: 18px;
        }

        .search-help {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 7px;
            margin: 18px 0 0;
            color: #8194a2;
            font-size: .8rem;
            line-height: 1.6;
        }

        .search-help svg {
            width: 15px;
            height: 15px;
            color: var(--teal);
        }

        /* Results */

        .results-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin: 34px 0 19px;
        }

        .results-eyebrow {
            display: block;
            margin-bottom: 7px;
            color: var(--teal);
            font-size: .69rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .results-title {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -.05em;
        }

        .results-count {
            padding: 9px 14px;
            border-radius: 30px;
            background: var(--teal-soft);
            color: var(--teal-dark);
            font-size: .81rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .results-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .results-description {
            margin: 0;
            color: var(--muted);
            font-size: .88rem;
        }

        .clear-link {
            color: var(--teal);
            font-size: .85rem;
            font-weight: 800;
        }

        .clear-link:hover {
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        /* Doctor cards */

        .doctor-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 20px;
        }

        .doctor-card {
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: 26px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 19px;
            box-shadow: 0 8px 28px rgba(20,64,86,.04);
            transition: transform .2s, border-color .2s, box-shadow .2s;
        }

        .doctor-card:hover {
            transform: translateY(-3px);
            border-color: #c0dfd9;
            box-shadow: 0 16px 38px rgba(20,64,86,.085);
        }

        .doctor-top {
            display: flex;
            gap: 16px;
            align-items: center;
            margin-bottom: 23px;
        }

        .doctor-avatar {
            display: grid;
            place-items: center;
            width: 65px;
            height: 65px;
            flex-shrink: 0;
            border-radius: 18px;
            background:
                linear-gradient(145deg,#e1f5f0,#d9ebf3);
            color: #136a73;
            font-family: "Manrope", sans-serif;
            font-size: 1.2rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .doctor-identity {
            min-width: 0;
        }

        .doctor-name {
            margin: 0 0 7px;
            color: #17374d;
            font-family: "Manrope", sans-serif;
            font-size: 1.07rem;
            font-weight: 800;
            line-height: 1.45;
            letter-spacing: -.04em;
            overflow-wrap: anywhere;
        }

        .doctor-specialty {
            display: inline-block;
            padding: 6px 11px;
            background: #e8f6f3;
            color: #087780;
            border-radius: 8px;
            font-size: .77rem;
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .doctor-details {
            flex: 1;
            padding: 19px 0;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        .detail-label {
            display: block;
            margin-bottom: 7px;
            color: #8497a4;
            font-size: .73rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .detail-value {
            color: #3e596d;
            font-size: .9rem;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .doctor-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            padding-top: 21px;
        }

        .doctor-action-note {
            color: #8b9da9;
            font-size: .78rem;
        }

        .view-doctor {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 43px;
            padding: 10px 16px;
            border-radius: 10px;
            background: var(--teal);
            color: white;
            font-size: .85rem;
            font-weight: 800;
            transition: background .2s, transform .2s;
        }

        .view-doctor:hover {
            color: white;
            background: var(--teal-dark);
            transform: translateY(-1px);
        }

        .view-doctor svg {
            width: 16px;
            height: 16px;
        }

        /* Empty and initial states */

        .state-panel {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-top: 29px;
            padding: 30px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 19px;
        }

        .state-icon {
            display: grid;
            place-items: center;
            width: 53px;
            height: 53px;
            flex-shrink: 0;
            background: var(--teal-soft);
            color: var(--teal);
            border-radius: 15px;
        }

        .state-icon svg {
            width: 27px;
            height: 27px;
        }

        .state-title {
            margin: 0 0 8px;
            font-family: "Manrope", sans-serif;
            font-size: 1.03rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .state-description {
            margin: 0;
            color: var(--muted);
            font-size: .9rem;
            line-height: 1.8;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding: 35px 0 0;
            color: #8b9eaa;
            font-size: .77rem;
        }

        /* Responsive */

        @media (max-width: 1100px) {
            .app-layout {
                grid-template-columns: 220px minmax(0,1fr);
            }

            .topbar {
                padding: 18px 28px;
            }

            .page-content {
                padding: 28px;
            }

            .search-grid {
                grid-template-columns: 1fr 1fr;
            }

            .search-button {
                grid-column: 1 / -1;
            }

            .doctor-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 850px) {
            .app-layout {
                display: block;
            }

            .sidebar {
                position: static;
                height: auto;
                padding: 16px 20px;
            }

            .sidebar-brand {
                padding: 0 0 14px;
            }

            .nav-label {
                display: none;
            }

            .sidebar-nav {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .sidebar-bottom {
                padding-top: 10px;
            }

            .sidebar-bottom::before {
                display: none;
            }

            .sidebar-bottom .nav-link {
                display: inline-flex;
            }

            .topbar {
                padding: 16px 24px;
            }

            .page-content {
                padding: 25px 24px;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                padding: 16px 13px;
            }

            .sidebar-nav {
                gap: 5px;
            }

            .nav-link {
                font-size: .8rem;
                padding: 9px 10px;
                min-height: 38px;
            }

            .nav-link svg {
                width: 16px;
                height: 16px;
            }

            .topbar {
                padding: 15px 17px;
            }

            .account-link {
                font-size: 0;
                padding: 5px;
            }

            .page-content {
                padding: 18px 13px 30px;
            }

            .page-intro {
                padding: 30px 24px;
                min-height: 190px;
            }

            .page-intro h1 {
                font-size: 1.9rem;
            }

            .page-intro p {
                font-size: .87rem;
            }

            .intro-icon {
                display: none;
            }

            .search-panel {
                padding: 24px 20px;
            }

            .search-grid {
                grid-template-columns: 1fr;
                gap: 17px;
            }

            .search-button {
                grid-column: auto;
                width: 100%;
            }

            .doctor-card {
                padding: 20px;
            }

            .doctor-top {
                align-items: flex-start;
            }

            .doctor-avatar {
                width: 54px;
                height: 54px;
            }

            .doctor-actions {
                align-items: stretch;
            }

            .view-doctor {
                width: 100%;
                justify-content: center;
            }

            .state-panel {
                padding: 23px;
            }

            .footer {
                padding-top: 24px;
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

<div class="app-layout">

    <!-- Patient navigation -->

    <aside class="sidebar">

        <a
            href="../dashboards/patient-dashboard.php"
            class="sidebar-brand"
        >
            <span class="brand-symbol" aria-hidden="true">
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

        <div class="nav-label">
            Patient Portal
        </div>

        <nav
            class="sidebar-nav"
            aria-label="Patient navigation"
        >

            <a
                href="../dashboards/patient-dashboard.php"
                class="nav-link"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                </svg>

                Dashboard
            </a>

            <a
                href="doctor-search.php"
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
                >
                    <circle cx="11" cy="11" r="7"/>
                    <path d="m20 20-4-4"/>
                </svg>

                Find a Doctor
            </a>

            <a
                href="appointments.php"
                class="nav-link"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M7 3v4M17 3v4M3 10h18"/>
                </svg>

                My Appointments
            </a>

            <a
                href="profile.php"
                class="nav-link"
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

                My Profile
            </a>

        </nav>

        <div class="sidebar-bottom">
            <a href="../logout.php" class="nav-link">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <path d="m16 17 5-5-5-5M21 12H9"/>
                </svg>

                Logout
            </a>
        </div>

    </aside>

    <!-- Main area -->

    <div class="main-area">

        <header class="topbar">

            <div>
                <span class="topbar-eyebrow">
                    Patient Portal
                </span>

                <div class="topbar-title">
                    Doctor Discovery
                </div>
            </div>

            <a
                href="profile.php"
                class="account-link"
                title="<?= doctorSearchEscape($fullName) ?>"
            >
                <span class="account-avatar">
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

                My Account
            </a>

        </header>

        <main class="page-content">

            <!-- Page introduction -->

            <section class="page-intro">

                <div class="intro-content">

                    <span class="intro-eyebrow">
                        Doctor Directory
                    </span>

                    <h1>
                        Find the right care for you.
                    </h1>

                    <p>
                        Search for doctors by name or
                        medical specialty. Explore their
                        qualifications and view appointment
                        availability before booking.
                    </p>

                </div>

                <div class="intro-icon" aria-hidden="true">

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.4"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M6 3v5a4 4 0 0 0 8 0V3"/>
                        <path d="M6 3H4m10 0h2"/>
                        <path d="M10 12v3a5 5 0 0 0 10 0v-1"/>
                        <circle cx="20" cy="11" r="2"/>
                    </svg>

                </div>

            </section>

            <!-- Search form -->

            <section
                class="search-panel"
                aria-labelledby="searchHeading"
            >

                <span class="panel-eyebrow">
                    Search Directory
                </span>

                <h2
                    id="searchHeading"
                    class="panel-title"
                >
                    Search for a Doctor
                </h2>

                <p class="panel-description">
                    Enter a doctor's name, specialty,
                    or both to find matching records.
                </p>

                <form
                    method="GET"
                    action="doctor-search.php"
                    role="search"
                >

                    <div class="search-grid">

                        <div class="field-group">

                            <label
                                class="field-label"
                                for="doctor_name"
                            >
                                Doctor Name
                            </label>

                            <input
                                type="search"
                                class="field-input"
                                id="doctor_name"
                                name="doctor_name"
                                placeholder="Search by name"
                                autocomplete="off"
                                value="<?= doctorSearchEscape(
                                    $doctorName
                                ) ?>"
                            >

                        </div>

                        <div class="field-group">

                            <label
                                class="field-label"
                                for="specialty"
                            >
                                Medical Specialty
                            </label>

                            <input
                                type="search"
                                class="field-input"
                                id="specialty"
                                name="specialty"
                                placeholder="e.g. Cardiology"
                                autocomplete="off"
                                value="<?= doctorSearchEscape(
                                    $specialty
                                ) ?>"
                            >

                        </div>

                        <button
                            type="submit"
                            class="search-button"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <circle cx="11" cy="11" r="7"/>
                                <path d="m20 20-4-4"/>
                            </svg>

                            Search Doctors
                        </button>

                    </div>

                    <p class="search-help">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 11v5M12 7h.01"/>
                        </svg>

                        Leave both fields blank and click
                        Search Doctors to view the
                        available directory results.
                    </p>

                </form>

            </section>

            <!-- Search results -->

            <?php if ($hasSearched): ?>

                <div class="results-heading">

                    <div>

                        <span class="results-eyebrow">
                            Directory Results
                        </span>

                        <h2 class="results-title">
                            Matching Doctors
                        </h2>

                    </div>

                    <span
                        class="results-count"
                        role="status"
                    >
                        <?= $resultCount ?>
                        <?= $resultCount === 1
                            ? 'doctor'
                            : 'doctors' ?> found
                    </span>

                </div>

                <div class="results-toolbar">

                    <p class="results-description">
                        Results matching your search criteria.
                    </p>

                    <a
                        href="doctor-search.php"
                        class="clear-link"
                    >
                        Clear Search
                    </a>

                </div>

                <?php if (empty($doctors)): ?>

                    <section class="state-panel">

                        <div class="state-icon" aria-hidden="true">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <circle cx="11" cy="11" r="7"/>
                                <path d="m20 20-4-4"/>
                            </svg>
                        </div>

                        <div>
                            <h3 class="state-title">
                                No matching doctors found
                            </h3>

                            <p class="state-description">
                                Try a different doctor name
                                or medical specialty.
                                You can also clear the
                                filters and search again.
                            </p>
                        </div>

                    </section>

                <?php else: ?>

                    <section
                        class="doctor-grid"
                        aria-label="Doctor search results"
                    >

                        <?php foreach ($doctors as $doctor): ?>

                            <?php
                            $name = (string) (
                                $doctor['full_name'] ?? ''
                            );

                            $doctorSpecialty = (string) (
                                $doctor['specialty'] ?? ''
                            );

                            $qualifications = (string) (
                                $doctor['qualifications'] ?? ''
                            );

                            $doctorId = (int) (
                                $doctor['doctor_id'] ?? 0
                            );
                            ?>

                            <article class="doctor-card">

                                <div class="doctor-top">

                                    <div
                                        class="doctor-avatar"
                                        aria-hidden="true"
                                    >
                                        <?= doctorSearchEscape(
                                            doctorSearchInitials($name)
                                        ) ?>
                                    </div>

                                    <div class="doctor-identity">

                                        <h3 class="doctor-name">
                                            <?= doctorSearchEscape($name) ?>
                                        </h3>

                                        <?php if ($doctorSpecialty !== ''): ?>

                                            <span class="doctor-specialty">
                                                <?= doctorSearchEscape(
                                                    $doctorSpecialty
                                                ) ?>
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </div>

                                <div class="doctor-details">

                                    <span class="detail-label">
                                        Professional Qualifications
                                    </span>

                                    <div class="detail-value">

                                        <?php if (
                                            trim($qualifications) !== ''
                                        ): ?>

                                            <?= doctorSearchEscape(
                                                $qualifications
                                            ) ?>

                                        <?php else: ?>

                                            <span style="color:#8295a3;">
                                                No qualifications listed.
                                            </span>

                                        <?php endif; ?>

                                    </div>

                                </div>

                                <div class="doctor-actions">

                                    <span class="doctor-action-note">
                                        View details and availability
                                    </span>

                                    <a
                                        href="doctor-details.php?doctor_id=<?= $doctorId ?>"
                                        class="view-doctor"
                                        aria-label="View profile of <?= doctorSearchEscape($name) ?>"
                                    >
                                        View Profile

                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true"
                                        >
                                            <path d="M5 12h14m-6-6 6 6-6 6"/>
                                        </svg>

                                    </a>

                                </div>

                            </article>

                        <?php endforeach; ?>

                    </section>

                <?php endif; ?>

            <?php else: ?>

                <section class="state-panel">

                    <div class="state-icon" aria-hidden="true">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m20 20-4-4"/>
                        </svg>

                    </div>

                    <div>

                        <h2 class="state-title">
                            Explore the Doctor Directory
                        </h2>

                        <p class="state-description">
                            Start by entering a name or
                            specialty in the search form.
                            Matching doctors will appear here,
                            with links to their profile
                            and appointment availability.
                        </p>

                    </div>

                </section>

            <?php endif; ?>

            <footer class="footer">
                <span>Clinic Appointment System</span>
                <span>Patient Portal</span>
            </footer>

        </main>

    </div>

</div>

</body>
</html>