<?php

/**
 * Patient Profile
 * Clinic Appointment System
 *
 * Premium patient profile interface.
 * Existing data loading and update functionality preserved.
 */

require_once __DIR__ . '/../includes/patient_profile_data.php';

$successMessage = $_SESSION['profile_success'] ?? '';
$errorMessage = $_SESSION['profile_error'] ?? '';

unset(
    $_SESSION['profile_success'],
    $_SESSION['profile_error']
);

$profileError = (string) ($profileError ?? '');

function escapeProfileValue($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$hasProfile = isset($patientProfile)
    && is_array($patientProfile);

$displayName = $hasProfile
    ? trim((string) ($patientProfile['full_name'] ?? ''))
    : '';

if ($displayName === '') {
    $displayName = trim(
        (string) ($_SESSION['full_name'] ?? 'Patient')
    );
}

if ($displayName === '') {
    $displayName = 'Patient';
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
        My Profile | Clinic Appointment System
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
            --navy-light: #173e61;
            --teal: #087d85;
            --teal-dark: #08666e;
            --teal-pale: #e8f6f3;

            --ink: #17374d;
            --muted: #718697;
            --border: #e2ebef;
            --background: #f4f8fa;
            --surface: #ffffff;

            --shadow: 0 10px 32px rgba(20,64,86,.045);
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
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

        button {
            cursor: pointer;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible {
            outline: 3px solid rgba(8,125,133,.32);
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
            overflow-y: auto;

            display: flex;
            flex-direction: column;

            padding: 30px 18px 22px;
            color: #ffffff;

            background:
                radial-gradient(
                    circle at 10% 95%,
                    rgba(54,180,170,.14),
                    transparent 44%
                ),
                linear-gradient(
                    160deg,
                    #102d46,
                    #102d46 65%,
                    #0e3848
                );
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;

            padding: 3px 12px 34px;
        }

        .brand-symbol {
            display: grid;
            place-items: center;

            width: 42px;
            height: 42px;
            flex-shrink: 0;

            border: 1px solid rgba(138,228,213,.2);
            border-radius: 12px;

            background: rgba(117,220,204,.12);
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
            letter-spacing: -.035em;
            line-height: 1.4;
        }

        .nav-label {
            padding: 0 15px 12px;

            color: #91adbb;
            font-size: .66rem;
            font-weight: 800;

            letter-spacing: .16em;
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

            min-height: 46px;
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
            color: #ffffff;
        }

        .nav-link.active {
            background: rgba(113,209,195,.14);
            border-color: rgba(152,226,215,.12);
            color: #ffffff;
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
            margin-bottom: 13px;
            background: rgba(255,255,255,.12);
        }

        /* Top bar */

        .main-area {
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;

            min-height: 88px;
            padding: 18px 42px;

            background: white;
            border-bottom: 1px solid var(--border);
        }

        .topbar-eyebrow {
            display: block;
            margin-bottom: 6px;

            color: var(--teal);
            font-size: .68rem;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .topbar-title {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: 1.07rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .account-chip {
            display: inline-flex;
            align-items: center;
            gap: 10px;

            max-width: 245px;
            padding: 7px 13px 7px 7px;

            border: 1px solid var(--border);
            border-radius: 30px;
            background: #ffffff;

            font-size: .83rem;
            font-weight: 700;
        }

        .account-avatar {
            display: grid;
            place-items: center;

            width: 32px;
            height: 32px;
            flex-shrink: 0;

            border-radius: 50%;
            background: var(--teal-pale);
            color: var(--teal);
        }

        .account-avatar svg {
            width: 18px;
            height: 18px;
        }

        .account-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Main content */

        .page-content {
            max-width: 1450px;
            margin: 0 auto;
            padding: 37px 42px 55px;
        }

        /* Profile introduction */

        .profile-hero {
            position: relative;
            isolation: isolate;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;

            min-height: 225px;
            padding: 40px 43px;

            overflow: hidden;
            border-radius: 23px;

            color: #ffffff;

            background:
                radial-gradient(
                    circle at 92% 15%,
                    rgba(80,197,181,.24),
                    transparent 40%
                ),
                linear-gradient(
                    115deg,
                    #102d46,
                    #17455a 65%,
                    #0e5860
                );
        }

        .profile-hero::after {
            content: "";
            position: absolute;
            z-index: -1;

            width: 360px;
            height: 360px;

            right: -180px;
            bottom: -275px;

            border: 1px solid rgba(178,236,227,.2);
            border-radius: 50%;

            box-shadow:
                0 0 0 45px rgba(178,236,227,.035);

            pointer-events: none;
        }

        .hero-eyebrow {
            display: block;
            margin-bottom: 15px;

            color: #a6e9de;
            font-size: .71rem;
            font-weight: 800;

            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .profile-hero h1 {
            margin: 0 0 15px;

            font-family: "Manrope", sans-serif;
            font-size: clamp(1.9rem,3vw,2.75rem);
            font-weight: 800;
            letter-spacing: -.06em;
            line-height: 1.2;
        }

        .profile-hero p {
            max-width: 610px;
            margin: 0;

            color: #cce1e7;
            font-size: .94rem;
            line-height: 1.85;
        }

        .hero-profile-mark {
            display: grid;
            place-items: center;

            width: 92px;
            height: 92px;
            flex-shrink: 0;

            border: 1px solid rgba(223,250,245,.2);
            border-radius: 23px;

            background: rgba(255,255,255,.09);
            color: #b1eddf;
        }

        .hero-profile-mark svg {
            width: 44px;
            height: 44px;
        }

        /* Page information */

        .section-heading {
            display: flex;
            align-items: end;
            justify-content: space-between;
            gap: 18px;

            margin: 37px 0 20px;
        }

        .section-eyebrow {
            display: block;
            margin-bottom: 8px;

            color: var(--teal);
            font-size: .69rem;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
        }

        .section-title {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -.05em;
        }

        .section-description {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: .88rem;
            line-height: 1.7;
        }

        .required-note {
            color: #8496a3;
            font-size: .8rem;
            white-space: nowrap;
        }

        /* Feedback */

        .notice {
            margin-top: 23px;
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

        .notice.success {
            color: #246c4d;
            background: #e9f8ef;
            border-color: #c8ead5;
        }

        .notice.error {
            color: #a03d43;
            background: #fff1f1;
            border-color: #efd0d0;
        }

        /* Content columns */

        .profile-layout {
            display: grid;
            grid-template-columns:
                minmax(0,1.65fr)
                minmax(260px,.75fr);

            align-items: start;
            gap: 22px;
        }

        /* Profile form */

        .profile-form {
            min-width: 0;
            overflow: hidden;

            background: white;
            border: 1px solid var(--border);
            border-radius: 19px;

            box-shadow: var(--shadow);
        }

        .form-section {
            padding: 29px 31px;
        }

        .form-section + .form-section {
            border-top: 1px solid var(--border);
        }

        .form-section-heading {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 27px;
        }

        .form-section-icon {
            display: grid;
            place-items: center;

            width: 43px;
            height: 43px;
            flex-shrink: 0;

            border-radius: 12px;
            background: #e8f6f3;
            color: var(--teal);
        }

        .form-section-icon.blue {
            background: #eaf2fa;
            color: #326994;
        }

        .form-section-icon svg {
            width: 22px;
            height: 22px;
        }

        .form-section-title {
            margin: 1px 0 7px;

            font-family: "Manrope", sans-serif;
            font-size: 1.02rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .form-section-description {
            margin: 0;

            color: var(--muted);
            font-size: .84rem;
            line-height: 1.7;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 22px;
        }

        .field {
            min-width: 0;
        }

        .field.full-width {
            grid-column: 1 / -1;
        }

        .field-label {
            display: block;
            margin-bottom: 10px;

            color: #2b4c62;
            font-size: .87rem;
            font-weight: 700;
        }

        .required-mark {
            color: var(--teal);
        }

        .field-input {
            display: block;

            width: 100%;
            min-height: 52px;

            padding: 13px 15px;

            border: 1px solid #dce7eb;
            border-radius: 11px;

            color: #173c52;
            background: #f8fbfc;

            font-size: .93rem;

            outline: none;

            transition:
                background .2s,
                border-color .2s,
                box-shadow .2s;
        }

        .field-input:hover {
            background: white;
            border-color: #bdd6dc;
        }

        .field-input:focus {
            background: white;
            border-color: var(--teal);
            box-shadow: 0 0 0 4px rgba(8,125,133,.10);
        }

        .field-hint {
            display: block;
            margin-top: 9px;

            color: #899ba7;
            font-size: .77rem;
            line-height: 1.65;
        }

        .optional-label {
            color: #92a2ad;
            font-size: .77rem;
            font-weight: 500;
        }

        /* Save controls */

        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;

            padding: 25px 31px;

            border-top: 1px solid var(--border);
            background: #fbfdfd;
        }

        .save-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 11px;

            min-height: 49px;
            padding: 12px 21px;

            border: 1px solid var(--teal);
            border-radius: 11px;

            background:
                linear-gradient(115deg,#087d85,#0b6d78);

            color: white;

            font-family: "Manrope", sans-serif;
            font-size: .87rem;
            font-weight: 800;

            box-shadow:
                0 9px 22px rgba(8,125,133,.14);

            transition:
                transform .2s,
                box-shadow .2s,
                background .2s;
        }

        .save-button:hover {
            transform: translateY(-1px);
            background: var(--teal-dark);
            box-shadow:
                0 13px 26px rgba(8,125,133,.19);
        }

        .save-button svg {
            width: 18px;
            height: 18px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-height: 49px;
            padding: 12px 19px;

            border: 1px solid #d9e5ea;
            border-radius: 11px;

            background: #ffffff;
            color: #496577;

            font-size: .85rem;
            font-weight: 800;
        }

        .back-button:hover {
            background: #eef7f6;
            border-color: #badbd6;
            color: #1d5960;
        }

        /* Supporting information */

        .information-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-card {
            padding: 27px;

            background: white;
            border: 1px solid var(--border);
            border-radius: 19px;

            box-shadow: var(--shadow);
        }

        .info-card h3 {
            margin: 0 0 12px;

            font-family: "Manrope", sans-serif;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .info-card p {
            margin: 0;

            color: var(--muted);
            font-size: .85rem;
            line-height: 1.85;
        }

        .info-card a {
            display: inline-flex;
            align-items: center;
            gap: 9px;

            margin-top: 22px;

            color: var(--teal);
            font-size: .85rem;
            font-weight: 800;
        }

        .info-card a:hover {
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        .info-card a svg {
            width: 17px;
            height: 17px;
        }

        .info-card.highlight {
            background:
                linear-gradient(
                    155deg,
                    #eaf7f4,
                    #f7fbfa
                );

            border-color: #d7eae6;
        }

        .information-list {
            margin: 17px 0 0;
            padding-left: 19px;

            color: #5c7887;
            font-size: .85rem;
            line-height: 1.95;
        }

        /* Error state */

        .profile-unavailable {
            padding: 33px;

            background: white;
            border: 1px solid var(--border);
            border-radius: 18px;
        }

        .profile-unavailable h2 {
            margin: 0 0 11px;
            font-family: "Manrope", sans-serif;
            font-size: 1.1rem;
        }

        .profile-unavailable p {
            color: var(--muted);
            line-height: 1.8;
        }

        /* Footer */

        .footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;

            padding-top: 37px;

            color: #8a9ba7;
            font-size: .76rem;
        }

        /* Responsive layouts */

        @media (max-width: 1150px) {
            .app-layout {
                grid-template-columns: 220px minmax(0,1fr);
            }

            .topbar {
                padding: 18px 28px;
            }

            .page-content {
                padding: 28px;
            }

            .profile-layout {
                grid-template-columns: minmax(0,1fr);
            }

            .information-column {
                display: grid;
                grid-template-columns: repeat(2,minmax(0,1fr));
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
                margin-top: 12px;
                padding-top: 10px;
            }

            .sidebar-bottom::before {
                display: none;
            }

            .topbar {
                padding: 17px 24px;
            }

            .page-content {
                padding: 25px 24px;
            }
        }

        @media (max-width: 650px) {
            .sidebar {
                padding: 16px 13px;
            }

            .nav-link {
                min-height: 39px;
                padding: 9px 10px;
                font-size: .79rem;
            }

            .nav-link svg {
                width: 16px;
                height: 16px;
            }

            .topbar {
                padding: 16px;
            }

            .account-name {
                max-width: 120px;
            }

            .page-content {
                padding: 19px 13px 35px;
            }

            .profile-hero {
                padding: 30px 24px;
            }

            .profile-hero h1 {
                font-size: 1.95rem;
                overflow-wrap: anywhere;
            }

            .hero-profile-mark {
                display: none;
            }

            .section-heading {
                align-items: flex-start;
                flex-direction: column;
            }

            .field-grid {
                grid-template-columns: 1fr;
                gap: 19px;
            }

            .field.full-width {
                grid-column: auto;
            }

            .form-section {
                padding: 25px 20px;
            }

            .form-actions {
                padding: 22px 20px;
            }

            .save-button,
            .back-button {
                width: 100%;
            }

            .information-column {
                display: flex;
            }

            .info-card {
                padding: 23px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            html {
                scroll-behavior: auto;
            }

            *,
            *::before,
            *::after {
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body>

<div class="app-layout">

    <!-- Patient portal navigation -->

    <aside class="sidebar">

        <a
            href="../dashboards/patient-dashboard.php"
            class="sidebar-brand"
        >
            <span
                class="brand-symbol"
                aria-hidden="true"
            >
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
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21a8 8 0 0 1 16 0"/>
                </svg>

                My Profile
            </a>

        </nav>

        <div class="sidebar-bottom">

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

                <h2 class="topbar-title">
                    Profile Settings
                </h2>
            </div>

            <div class="account-chip">

                <span
                    class="account-avatar"
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

                <span class="account-name">
                    <?= escapeProfileValue($displayName) ?>
                </span>

            </div>

        </header>

        <main class="page-content">

            <!-- Introduction -->

            <section class="profile-hero">

                <div>

                    <span class="hero-eyebrow">
                        Your Personal Information
                    </span>

                    <h1>
                        Your profile, in one place.
                    </h1>

                    <p>
                        Review your details and keep
                        your contact and personal
                        information up to date.
                    </p>

                </div>

                <span
                    class="hero-profile-mark"
                    aria-hidden="true"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.4"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="7.5" r="3.8"/>
                        <path d="M4.7 20a7.3 7.3 0 0 1 14.6 0"/>
                        <path d="m18 3 2 2 3-3"/>
                    </svg>
                </span>

            </section>

            <!-- Existing feedback -->

            <?php if ($successMessage !== ''): ?>

                <div
                    class="notice success"
                    role="status"
                >
                    <strong>Profile Updated</strong>

                    <?= escapeProfileValue(
                        $successMessage
                    ) ?>
                </div>

            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>

                <div
                    class="notice error"
                    role="alert"
                >
                    <strong>Update Unsuccessful</strong>

                    <?= escapeProfileValue(
                        $errorMessage
                    ) ?>
                </div>

            <?php endif; ?>

            <?php if ($profileError !== ''): ?>

                <div
                    class="notice error"
                    role="alert"
                >
                    <strong>Unable to Load Profile</strong>

                    <?= escapeProfileValue(
                        $profileError
                    ) ?>
                </div>

            <?php endif; ?>

            <!-- Profile form heading -->

            <div class="section-heading">

                <div>

                    <span class="section-eyebrow">
                        Account Details
                    </span>

                    <h2 class="section-title">
                        Manage Your Information
                    </h2>

                    <p class="section-description">
                        Edit the details below and save
                        your changes when finished.
                    </p>

                </div>

                <span class="required-note">
                    * Required field
                </span>

            </div>

            <?php if ($hasProfile): ?>

                <div class="profile-layout">

                    <!-- Existing profile update form -->

                    <form
                        class="profile-form"
                        method="POST"
                        action="../actions/patient_profile_update.php"
                    >

                        <!-- Preserve original CSRF field -->

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= escapeProfileValue(
                                $_SESSION['csrf_token'] ?? ''
                            ) ?>"
                        >

                        <!-- Personal details -->

                        <section class="form-section">

                            <div class="form-section-heading">

                                <span
                                    class="form-section-icon"
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

                                <div>

                                    <h3 class="form-section-title">
                                        Personal Details
                                    </h3>

                                    <p class="form-section-description">
                                        Your basic information
                                        and contact number.
                                    </p>

                                </div>

                            </div>

                            <div class="field-grid">

                                <div class="field full-width">

                                    <label
                                        for="full_name"
                                        class="field-label"
                                    >
                                        Full Name
                                        <span class="required-mark">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        class="field-input"
                                        id="full_name"
                                        name="full_name"
                                        value="<?= escapeProfileValue(
                                            $patientProfile['full_name'] ?? ''
                                        ) ?>"
                                        maxlength="100"
                                        autocomplete="name"
                                        required
                                    >

                                </div>

                                <div class="field">

                                    <label
                                        for="phone"
                                        class="field-label"
                                    >
                                        Phone Number
                                        <span class="required-mark">*</span>
                                    </label>

                                    <input
                                        type="tel"
                                        class="field-input"
                                        id="phone"
                                        name="phone"
                                        value="<?= escapeProfileValue(
                                            $patientProfile['phone'] ?? ''
                                        ) ?>"
                                        maxlength="20"
                                        autocomplete="tel"
                                        required
                                    >

                                </div>

                                <div class="field">

                                    <label
                                        for="age"
                                        class="field-label"
                                    >
                                        Age
                                        <span class="optional-label">
                                            (Optional)
                                        </span>
                                    </label>

                                    <input
                                        type="number"
                                        class="field-input"
                                        id="age"
                                        name="age"
                                        value="<?= escapeProfileValue(
                                            $patientProfile['age'] ?? ''
                                        ) ?>"
                                        min="0"
                                        max="255"
                                        step="1"
                                    >

                                </div>

                            </div>

                        </section>

                        <!-- Health information -->

                        <section class="form-section">

                            <div class="form-section-heading">

                                <span
                                    class="form-section-icon blue"
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
                                        <path
                                            d="M3 12h4l3-6 4 12 3-6h4"
                                        />
                                    </svg>
                                </span>

                                <div>

                                    <h3 class="form-section-title">
                                        Health Information
                                    </h3>

                                    <p class="form-section-description">
                                        Optional information included
                                        in your patient profile.
                                    </p>

                                </div>

                            </div>

                            <div class="field-grid">

                                <div class="field">

                                    <label
                                        for="blood_type"
                                        class="field-label"
                                    >
                                        Blood Type
                                        <span class="optional-label">
                                            (Optional)
                                        </span>
                                    </label>

                                    <input
                                        type="text"
                                        class="field-input"
                                        id="blood_type"
                                        name="blood_type"
                                        value="<?= escapeProfileValue(
                                            $patientProfile['blood_type'] ?? ''
                                        ) ?>"
                                        maxlength="5"
                                        placeholder="e.g. O+"
                                    >

                                    <span class="field-hint">
                                        Enter your known blood type,
                                        or leave this field blank.
                                    </span>

                                </div>

                                <div class="field">

                                    <label
                                        for="disease_type"
                                        class="field-label"
                                    >
                                        Type of Disease
                                        <span class="optional-label">
                                            (Optional)
                                        </span>
                                    </label>

                                    <input
                                        type="text"
                                        class="field-input"
                                        id="disease_type"
                                        name="disease_type"
                                        value="<?= escapeProfileValue(
                                            $patientProfile['disease_type'] ?? ''
                                        ) ?>"
                                        maxlength="255"
                                        placeholder="Enter if applicable"
                                    >

                                    <span class="field-hint">
                                        Provide this information only
                                        if applicable.
                                    </span>

                                </div>

                            </div>

                        </section>

                        <!-- Family information -->

                        <section class="form-section">

                            <div class="form-section-heading">

                                <span
                                    class="form-section-icon"
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
                                        <circle cx="9" cy="8" r="3"/>
                                        <path d="M2 20a7 7 0 0 1 14 0"/>
                                        <path d="M17 8a3 3 0 0 1 0 6"/>
                                        <path d="M18 16a5 5 0 0 1 4 4"/>
                                    </svg>
                                </span>

                                <div>

                                    <h3 class="form-section-title">
                                        Family Information
                                    </h3>

                                    <p class="form-section-description">
                                        Additional optional details
                                        stored in your profile.
                                    </p>

                                </div>

                            </div>

                            <div class="field-grid">

                                <div class="field">

                                    <label
                                        for="close_relatives_count"
                                        class="field-label"
                                    >
                                        Number of Close Relatives
                                        <span class="optional-label">
                                            (Optional)
                                        </span>
                                    </label>

                                    <input
                                        type="number"
                                        class="field-input"
                                        id="close_relatives_count"
                                        name="close_relatives_count"
                                        value="<?= escapeProfileValue(
                                            $patientProfile[
                                                'close_relatives_count'
                                            ] ?? ''
                                        ) ?>"
                                        min="0"
                                        max="65535"
                                        step="1"
                                    >

                                </div>

                            </div>

                        </section>

                        <!-- Actions -->

                        <div class="form-actions">

                            <button
                                type="submit"
                                class="save-button"
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
                                    <path
                                        d="M4 3h13l3 3v15H4z"
                                    />
                                    <path d="M8 3v7h8V3"/>
                                    <path d="M8 21v-7h8v7"/>
                                </svg>

                                Save Changes

                            </button>

                            <a
                                href="../dashboards/patient-dashboard.php"
                                class="back-button"
                            >
                                Back to Dashboard
                            </a>

                        </div>

                    </form>

                    <!-- Supporting information -->

                    <aside class="information-column">

                        <div class="info-card highlight">

                            <h3>
                                Keep Your Details Current
                            </h3>

                            <p>
                                Accurate contact and personal
                                information helps keep your
                                patient profile up to date.
                            </p>

                            <ul class="information-list">
                                <li>Review your full name.</li>
                                <li>Check your phone number.</li>
                                <li>Update optional details when needed.</li>
                                <li>Save changes before leaving.</li>
                            </ul>

                        </div>

                        <div class="info-card">

                            <h3>
                                Review Your Appointments
                            </h3>

                            <p>
                                Your appointment history and
                                upcoming visits are available
                                in the patient portal.
                            </p>

                            <a href="appointments.php">

                                My Appointments

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

                        <div class="info-card">

                            <h3>
                                About Health Details
                            </h3>

                            <p>
                                Blood type and disease
                                information are optional.
                                Review sensitive information
                                carefully before submitting
                                changes.
                            </p>

                        </div>

                    </aside>

                </div>

            <?php else: ?>

                <!-- Error state -->

                <section class="profile-unavailable">

                    <h2>
                        Profile Unavailable
                    </h2>

                    <p>
                        Your profile could not be loaded.
                        Please return to the dashboard
                        and try again.
                    </p>

                    <a
                        href="../dashboards/patient-dashboard.php"
                        class="back-button"
                    >
                        Back to Dashboard
                    </a>

                </section>

            <?php endif; ?>

            <footer class="footer">

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