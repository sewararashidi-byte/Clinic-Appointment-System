<?php

/**
 * Doctor Professional Profile
 * Clinic Appointment System
 *
 * Premium Doctor Portal interface.
 * Existing profile backend and update action preserved.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['doctor']);

require_once __DIR__ . '/../includes/doctor_profile_data.php';

function escapeDoctorProfileValue($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Preserve one-time backend messages.
$profileSuccess = (string) (
    $_SESSION['doctor_profile_success'] ?? ''
);

$profileActionError = (string) (
    $_SESSION['doctor_profile_error'] ?? ''
);

unset(
    $_SESSION['doctor_profile_success'],
    $_SESSION['doctor_profile_error']
);

$profileError = (string) ($profileError ?? '');

$hasProfile = isset($doctorProfile)
    && is_array($doctorProfile);

$doctorName = $hasProfile
    ? trim((string) ($doctorProfile['full_name'] ?? ''))
    : '';

if ($doctorName === '') {
    $doctorName = trim(
        (string) ($_SESSION['full_name'] ?? 'Doctor')
    );
}

if ($doctorName === '') {
    $doctorName = 'Doctor';
}

$specialty = $hasProfile
    ? trim((string) ($doctorProfile['specialty'] ?? ''))
    : '';

$qualifications = $hasProfile
    ? trim((string) ($doctorProfile['qualifications'] ?? ''))
    : '';

$experience = $hasProfile
    ? (string) ($doctorProfile['years_experience'] ?? '0')
    : '0';

$biography = $hasProfile
    ? (string) ($doctorProfile['biography'] ?? '')
    : '';

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
        My Professional Profile | Clinic Appointment System
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
            --teal: #087d85;
            --teal-dark: #08666e;
            --teal-pale: #e8f6f3;
            --ink: #17384d;
            --muted: #718697;
            --border: #e1ebef;
            --background: #f3f8fa;
            --white: #ffffff;
            --shadow: 0 12px 34px rgba(20,64,86,.05);
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

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input,
        textarea {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        textarea:focus-visible {
            outline: 3px solid rgba(8,125,133,.32);
            outline-offset: 3px;
        }

        svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* Application layout */

        .app-shell {
            display: grid;
            grid-template-columns: 268px minmax(0,1fr);
            min-height: 100vh;
        }

        /* Doctor sidebar */

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            padding: 29px 18px 22px;
            color: #fff;
            background:
                radial-gradient(
                    circle at 8% 92%,
                    rgba(65,185,173,.16),
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
            border: 1px solid rgba(156,233,218,.23);
            border-radius: 13px;
            background: rgba(156,233,218,.11);
            color: #9be9dc;
        }

        .brand-icon svg {
            width: 26px;
            height: 26px;
        }

        .brand-name {
            font-family: "Manrope",sans-serif;
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
            background: rgba(255,255,255,.075);
            color: white;
        }

        .nav-link.active {
            background: rgba(112,210,196,.14);
            border-color: rgba(152,227,215,.13);
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
            background: rgba(255,255,255,.11);
        }

        .sidebar-role {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0 5px 17px;
            padding: 12px;
            border: 1px solid rgba(255,255,255,.09);
            border-radius: 12px;
            background: rgba(255,255,255,.045);
            color: #d6e7e9;
            font-size: .78rem;
            font-weight: 600;
        }

        .role-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #92e6d7;
        }

        /* Topbar */

        .main-area {
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            min-height: 89px;
            padding: 18px 43px;
            background: white;
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
            font-family: "Manrope",sans-serif;
            font-size: 1.07rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-button {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 42px;
            padding: 10px 15px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: white;
            color: #426174;
            font-size: .83rem;
            font-weight: 800;
        }

        .topbar-button:hover {
            background: #eff8f6;
            color: var(--teal);
        }

        .topbar-button svg {
            width: 17px;
            height: 17px;
        }

        /* Main page */

        .content {
            max-width: 1510px;
            margin: 0 auto;
            padding: 37px 43px 55px;
        }

        /* Profile hero */

        .hero {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 30px;
            min-height: 250px;
            padding: 42px 45px;
            border-radius: 24px;
            color: white;
            background:
                radial-gradient(
                    circle at 91% 12%,
                    rgba(83,203,185,.23),
                    transparent 42%
                ),
                linear-gradient(
                    117deg,
                    #102d46,
                    #173e56 59%,
                    #0b5860
                );
        }

        .hero::after {
            content: "";
            position: absolute;
            z-index: -1;
            width: 385px;
            height: 385px;
            right: -200px;
            bottom: -294px;
            border: 1px solid rgba(179,242,230,.22);
            border-radius: 50%;
            box-shadow:
                0 0 0 50px rgba(179,242,230,.03);
        }

        .hero-eyebrow {
            display: block;
            margin-bottom: 15px;
            color: #a7ebdf;
            font-size: .71rem;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 0 0 15px;
            font-family: "Manrope",sans-serif;
            font-size: clamp(2rem,3.2vw,2.85rem);
            font-weight: 800;
            letter-spacing: -.06em;
            line-height: 1.23;
        }

        .hero p {
            max-width: 630px;
            margin: 0;
            color: #cce1e7;
            font-size: .94rem;
            line-height: 1.85;
        }

        .hero-icon {
            display: grid;
            place-items: center;
            width: 100px;
            height: 100px;
            flex-shrink: 0;
            border: 1px solid rgba(223,250,245,.22);
            border-radius: 25px;
            background: rgba(255,255,255,.09);
            color: #b3eee1;
        }

        .hero-icon svg {
            width: 49px;
            height: 49px;
        }

        /* Notifications */

        .notice {
            margin-top: 23px;
            padding: 18px 21px;
            border: 1px solid;
            border-radius: 13px;
            font-size: .89rem;
            line-height: 1.75;
        }

        .notice strong {
            display: block;
            margin-bottom: 5px;
        }

        .notice.success {
            background: #eaf8ef;
            border-color: #c8ead5;
            color: #246c4d;
        }

        .notice.error {
            background: #fff2f1;
            border-color: #efd0cf;
            color: #a13d44;
        }

        .notice.warning {
            background: #fff8e8;
            border-color: #f0dfb7;
            color: #816122;
        }

        /* Section headings */

        .section-heading {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            margin: 38px 0 20px;
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
            font-family: "Manrope",sans-serif;
            font-size: 1.36rem;
            font-weight: 800;
            letter-spacing: -.05em;
        }

        .section-description {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: .88rem;
            line-height: 1.75;
        }

        .required-note {
            color: #8799a5;
            font-size: .8rem;
        }

        /* Profile workspace */

        .profile-layout {
            display: grid;
            grid-template-columns: minmax(0,1.55fr)
                                   minmax(270px,.8fr);
            align-items: start;
            gap: 22px;
        }

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

        .form-heading {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 26px;
        }

        .form-icon {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            flex-shrink: 0;
            border-radius: 13px;
            background: #e8f6f3;
            color: var(--teal);
        }

        .form-icon.blue {
            background: #eaf2fa;
            color: #346b99;
        }

        .form-icon.purple {
            background: #f0edfa;
            color: #7461a4;
        }

        .form-icon svg {
            width: 23px;
            height: 23px;
        }

        .form-title {
            margin: 2px 0 7px;
            font-family: "Manrope",sans-serif;
            font-size: 1.02rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .form-description {
            margin: 0;
            color: var(--muted);
            font-size: .84rem;
            line-height: 1.75;
        }

        /* Fields */

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 21px;
        }

        .field {
            min-width: 0;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field-label {
            display: block;
            margin-bottom: 10px;
            color: #294b61;
            font-size: .86rem;
            font-weight: 700;
        }

        .required {
            color: var(--teal);
        }

        .optional {
            color: #93a2ac;
            font-size: .78rem;
            font-weight: 500;
        }

        .field-input {
            display: block;
            width: 100%;
            min-height: 52px;
            padding: 13px 15px;
            border: 1px solid #dce7eb;
            border-radius: 11px;
            outline: none;
            background: #f8fbfc;
            color: #173c52;
            font-size: .92rem;
            transition:
                background .2s,
                border-color .2s,
                box-shadow .2s;
        }

        .field-input:hover {
            background: white;
            border-color: #bed5da;
        }

        .field-input:focus {
            background: white;
            border-color: var(--teal);
            box-shadow: 0 0 0 4px rgba(8,125,133,.1);
        }

        textarea.field-input {
            min-height: 170px;
            resize: vertical;
            line-height: 1.8;
        }

        .field-hint {
            display: block;
            margin-top: 9px;
            color: #879ba7;
            font-size: .77rem;
            line-height: 1.7;
        }

        .field-meta {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .character-count {
            color: #8a9ca8;
            font-size: .76rem;
            font-weight: 600;
        }

        /* Form actions */

        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;
            padding: 25px 31px;
            border-top: 1px solid var(--border);
            background: #fbfdfd;
        }

        .save-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 49px;
            padding: 12px 21px;
            border: 1px solid var(--teal);
            border-radius: 11px;
            background: linear-gradient(
                115deg,
                #087d85,
                #0b6d78
            );
            color: white;
            font-family: "Manrope",sans-serif;
            font-size: .87rem;
            font-weight: 800;
            box-shadow: 0 9px 22px rgba(8,125,133,.14);
            transition:
                background .2s,
                transform .2s,
                box-shadow .2s;
        }

        .save-button:hover {
            background: var(--teal-dark);
            transform: translateY(-1px);
            box-shadow: 0 13px 26px rgba(8,125,133,.19);
        }

        .save-button svg {
            width: 18px;
            height: 18px;
        }

        .secondary-button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-height: 49px;
            padding: 12px 18px;
            border: 1px solid #d9e5ea;
            border-radius: 11px;
            background: white;
            color: #496577;
            font-size: .85rem;
            font-weight: 800;
        }

        .secondary-button:hover {
            background: #edf7f5;
            color: var(--teal);
        }

        /* Profile preview and support */

        .side-column {
            display: flex;
            flex-direction: column;
            gap: 21px;
        }

        .preview-card {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 19px;
            background: white;
            box-shadow: var(--shadow);
        }

        .preview-banner {
            height: 114px;
            background:
                radial-gradient(
                    circle at 90% 10%,
                    rgba(112,216,200,.22),
                    transparent 55%
                ),
                linear-gradient(
                    120deg,
                    #102d46,
                    #0e5960
                );
        }

        .preview-body {
            position: relative;
            padding: 0 25px 25px;
        }

        .preview-avatar {
            display: grid;
            place-items: center;
            width: 76px;
            height: 76px;
            margin-top: -38px;
            margin-bottom: 18px;
            border: 5px solid white;
            border-radius: 20px;
            background: #e5f4f0;
            color: var(--teal);
            box-shadow: 0 7px 17px rgba(16,45,70,.06);
        }

        .preview-avatar svg {
            width: 34px;
            height: 34px;
        }

        .preview-eyebrow {
            display: block;
            margin-bottom: 8px;
            color: var(--teal);
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .preview-name {
            margin: 0 0 12px;
            font-family: "Manrope",sans-serif;
            font-size: 1.21rem;
            font-weight: 800;
            letter-spacing: -.05em;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }

        .specialty-chip {
            display: inline-block;
            max-width: 100%;
            padding: 8px 12px;
            border-radius: 9px;
            background: #e7f5f1;
            color: var(--teal);
            font-size: .81rem;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .preview-divider {
            height: 1px;
            margin: 23px 0;
            background: var(--border);
        }

        .preview-label {
            display: block;
            margin-bottom: 8px;
            color: #8296a3;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .preview-value {
            margin: 0 0 19px;
            color: #426174;
            font-size: .86rem;
            line-height: 1.8;
            overflow-wrap: anywhere;
        }

        .preview-value:last-child {
            margin-bottom: 0;
        }

        .preview-footer {
            padding: 16px 25px;
            border-top: 1px solid var(--border);
            background: #f9fcfc;
            color: #768e9a;
            font-size: .79rem;
            line-height: 1.65;
        }

        .info-card {
            padding: 26px;
            border: 1px solid var(--border);
            border-radius: 19px;
            background: white;
            box-shadow: var(--shadow);
        }

        .info-card.highlight {
            background: linear-gradient(
                155deg,
                #eaf7f4,
                #ffffff
            );
            border-color: #d7ebe6;
        }

        .info-card h3 {
            margin: 0 0 12px;
            font-family: "Manrope",sans-serif;
            font-size: .99rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .info-card p {
            margin: 0;
            color: #68808e;
            font-size: .85rem;
            line-height: 1.85;
        }

        .info-link {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-top: 19px;
            color: var(--teal);
            font-size: .84rem;
            font-weight: 800;
        }

        .info-link:hover {
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        .info-link svg {
            width: 17px;
            height: 17px;
        }

        /* Error state */

        .unavailable-card {
            padding: 30px;
            border: 1px solid var(--border);
            border-radius: 19px;
            background: white;
        }

        .unavailable-card h2 {
            margin: 0 0 12px;
            font-family: "Manrope",sans-serif;
            font-size: 1.08rem;
        }

        .unavailable-card p {
            color: var(--muted);
            font-size: .89rem;
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

        /* Responsive */

        @media (max-width: 1190px) {
            .profile-layout {
                grid-template-columns: minmax(0,1fr);
            }

            .side-column {
                display: grid;
                grid-template-columns: repeat(2,minmax(0,1fr));
            }

            .preview-card {
                grid-row: span 2;
            }
        }

        @media (max-width: 990px) {
            .app-shell {
                grid-template-columns: 220px minmax(0,1fr);
            }

            .topbar {
                padding: 18px 27px;
            }

            .content {
                padding: 30px 27px 45px;
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
        }

        @media (max-width: 650px) {
            .sidebar {
                padding: 15px 13px;
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
                padding: 16px;
            }

            .content {
                padding: 19px 13px 32px;
            }

            .hero {
                padding: 30px 24px;
                min-height: 205px;
            }

            .hero h1 {
                font-size: 1.96rem;
            }

            .hero-icon {
                display: none;
            }

            .section-heading {
                align-items: flex-start;
            }

            .form-section {
                padding: 25px 20px;
            }

            .field-grid {
                grid-template-columns: 1fr;
            }

            .field.full {
                grid-column: auto;
            }

            .form-actions {
                padding: 22px 20px;
            }

            .save-button,
            .secondary-button {
                width: 100%;
            }

            .side-column {
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

<div class="app-shell">

    <!-- Doctor portal navigation -->

    <aside class="sidebar">

        <a
            href="../dashboards/doctor-dashboard.php"
            class="brand"
        >
            <span
                class="brand-icon"
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

        <div class="sidebar-label">
            Doctor Portal
        </div>

        <nav
            class="sidebar-nav"
            aria-label="Doctor navigation"
        >
            <a
                href="../dashboards/doctor-dashboard.php"
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
                href="availability.php"
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
                    <path d="M8 14h3M13 14h3M8 18h3"/>
                </svg>
                My Availability
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

            <div class="sidebar-role">
                <span
                    class="role-dot"
                    aria-hidden="true"
                ></span>
                Doctor Account
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
                >
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <path d="m16 17 5-5-5-5M21 12H9"/>
                </svg>
                Logout
            </a>

        </div>

    </aside>

    <!-- Main workspace -->

    <div class="main-area">

        <header class="topbar">

            <div>
                <span class="topbar-eyebrow">
                    Doctor Portal
                </span>

                <h2 class="topbar-title">
                    Professional Profile
                </h2>
            </div>

            <div class="topbar-actions">

                <a
                    href="../dashboards/doctor-dashboard.php"
                    class="topbar-button"
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
                        <path d="m15 18-6-6 6-6"/>
                    </svg>

                    Dashboard
                </a>

            </div>

        </header>

        <main class="content">

            <!-- Introduction -->

            <section class="hero">

                <div>
                    <span class="hero-eyebrow">
                        Your Professional Identity
                    </span>

                    <h1>
                        Your expertise, clearly presented.
                    </h1>

                    <p>
                        Keep your professional details
                        accurate and your biography
                        up to date. Your information helps
                        patients understand your medical
                        specialty and qualifications.
                    </p>
                </div>

                <span
                    class="hero-icon"
                    aria-hidden="true"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="12" cy="7" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                        <path d="m17 5 2 2 3-3"/>
                    </svg>
                </span>

            </section>

            <!-- Backend messages -->

            <?php if ($profileSuccess !== ''): ?>

                <div
                    class="notice success"
                    role="status"
                >
                    <strong>
                        Profile Updated
                    </strong>

                    <?= escapeDoctorProfileValue(
                        $profileSuccess
                    ) ?>
                </div>

            <?php endif; ?>

            <?php if ($profileActionError !== ''): ?>

                <div
                    class="notice error"
                    role="alert"
                >
                    <strong>
                        Update Unsuccessful
                    </strong>

                    <?= escapeDoctorProfileValue(
                        $profileActionError
                    ) ?>
                </div>

            <?php endif; ?>

            <?php if ($profileError !== ''): ?>

                <div
                    class="notice warning"
                    role="alert"
                >
                    <strong>
                        Profile Information Unavailable
                    </strong>

                    <?= escapeDoctorProfileValue(
                        $profileError
                    ) ?>
                </div>

            <?php endif; ?>

            <div class="section-heading">

                <div>
                    <span class="section-eyebrow">
                        Profile Management
                    </span>

                    <h2 class="section-title">
                        Edit Your Professional Details
                    </h2>

                    <p class="section-description">
                        Review your information and save
                        your changes when you're finished.
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
                        id="doctorProfileForm"
                        class="profile-form"
                        action="../actions/doctor_profile_update.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= escapeDoctorProfileValue(
                                $_SESSION[
                                    'doctor_profile_csrf_token'
                                ] ?? ''
                            ) ?>"
                        >

                        <!-- Personal and contact information -->

                        <section class="form-section">

                            <div class="form-heading">

                                <span
                                    class="form-icon"
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
                                    <h3 class="form-title">
                                        Personal & Contact Information
                                    </h3>

                                    <p class="form-description">
                                        Your professional name and
                                        contact phone number.
                                    </p>
                                </div>

                            </div>

                            <div class="field-grid">

                                <div class="field">

                                    <label
                                        for="full_name"
                                        class="field-label"
                                    >
                                        Full Name
                                        <span class="required">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        id="full_name"
                                        name="full_name"
                                        class="field-input"
                                        value="<?= escapeDoctorProfileValue(
                                            $doctorProfile[
                                                'full_name'
                                            ] ?? ''
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
                                        Professional Phone Number
                                        <span class="required">*</span>
                                    </label>

                                    <input
                                        type="tel"
                                        id="phone"
                                        name="phone"
                                        class="field-input"
                                        value="<?= escapeDoctorProfileValue(
                                            $doctorProfile[
                                                'phone'
                                            ] ?? ''
                                        ) ?>"
                                        maxlength="20"
                                        autocomplete="tel"
                                        required
                                    >

                                </div>

                            </div>

                        </section>

                        <!-- Professional credentials -->

                        <section class="form-section">

                            <div class="form-heading">

                                <span
                                    class="form-icon blue"
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
                                        <path d="M4 4h16v15H4z"/>
                                        <path d="M8 9h8M8 13h5"/>
                                        <path d="m15 18 2 3 2-3"/>
                                    </svg>
                                </span>

                                <div>
                                    <h3 class="form-title">
                                        Professional Credentials
                                    </h3>

                                    <p class="form-description">
                                        Medical specialty,
                                        qualifications and
                                        professional experience.
                                    </p>
                                </div>

                            </div>

                            <div class="field-grid">

                                <div class="field">

                                    <label
                                        for="specialty"
                                        class="field-label"
                                    >
                                        Medical Specialty
                                        <span class="required">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        id="specialty"
                                        name="specialty"
                                        class="field-input"
                                        value="<?= escapeDoctorProfileValue(
                                            $doctorProfile[
                                                'specialty'
                                            ] ?? ''
                                        ) ?>"
                                        maxlength="100"
                                        required
                                    >

                                </div>

                                <div class="field">

                                    <label
                                        for="years_experience"
                                        class="field-label"
                                    >
                                        Years of Experience
                                        <span class="required">*</span>
                                    </label>

                                    <input
                                        type="number"
                                        id="years_experience"
                                        name="years_experience"
                                        class="field-input"
                                        value="<?= escapeDoctorProfileValue(
                                            $doctorProfile[
                                                'years_experience'
                                            ] ?? 0
                                        ) ?>"
                                        min="0"
                                        max="255"
                                        step="1"
                                        required
                                    >

                                </div>

                                <div class="field full">

                                    <label
                                        for="qualifications"
                                        class="field-label"
                                    >
                                        Qualifications
                                        <span class="required">*</span>
                                    </label>

                                    <input
                                        type="text"
                                        id="qualifications"
                                        name="qualifications"
                                        class="field-input"
                                        value="<?= escapeDoctorProfileValue(
                                            $doctorProfile[
                                                'qualifications'
                                            ] ?? ''
                                        ) ?>"
                                        maxlength="255"
                                        required
                                    >

                                    <span class="field-hint">
                                        Enter your verified
                                        professional qualifications.
                                    </span>

                                </div>

                            </div>

                        </section>

                        <!-- Biography -->

                        <section class="form-section">

                            <div class="form-heading">

                                <span
                                    class="form-icon purple"
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
                                        <path d="M5 4h11l3 3v13H5z"/>
                                        <path d="M15 4v4h4"/>
                                        <path d="M8 12h8M8 16h8"/>
                                    </svg>
                                </span>

                                <div>
                                    <h3 class="form-title">
                                        Professional Biography
                                    </h3>

                                    <p class="form-description">
                                        Describe your professional
                                        background and experience.
                                    </p>
                                </div>

                            </div>

                            <div class="field">

                                <label
                                    for="biography"
                                    class="field-label"
                                >
                                    Biography
                                    <span class="optional">
                                        (Optional)
                                    </span>
                                </label>

                                <textarea
                                    id="biography"
                                    name="biography"
                                    class="field-input"
                                    rows="6"
                                    maxlength="65535"
                                    placeholder="Describe your professional background and experience."
                                ><?= escapeDoctorProfileValue(
                                    $doctorProfile[
                                        'biography'
                                    ] ?? ''
                                ) ?></textarea>

                                <div class="field-meta">

                                    <span class="field-hint">
                                        Use clear, accurate
                                        professional information.
                                    </span>

                                    <span
                                        id="biographyCount"
                                        class="character-count"
                                    ></span>

                                </div>

                            </div>

                        </section>

                        <!-- Submit -->

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
                                    <path d="M4 3h13l3 3v15H4z"/>
                                    <path d="M8 3v7h8V3"/>
                                    <path d="M8 21v-7h8v7"/>
                                </svg>

                                Save Profile Changes
                            </button>

                            <a
                                href="../dashboards/doctor-dashboard.php"
                                class="secondary-button"
                            >
                                Back to Dashboard
                            </a>

                        </div>

                    </form>

                    <!-- Live profile preview -->

                    <aside class="side-column">

                        <section class="preview-card">

                            <div class="preview-banner"></div>

                            <div class="preview-body">

                                <div
                                    class="preview-avatar"
                                    aria-hidden="true"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <circle cx="12" cy="8" r="4"/>
                                        <path d="M4 21a8 8 0 0 1 16 0"/>
                                    </svg>
                                </div>

                                <span class="preview-eyebrow">
                                    Profile Preview
                                </span>

                                <h3
                                    id="previewName"
                                    class="preview-name"
                                >
                                    <?= escapeDoctorProfileValue(
                                        $doctorName
                                    ) ?>
                                </h3>

                                <span
                                    id="previewSpecialty"
                                    class="specialty-chip"
                                >
                                    <?= escapeDoctorProfileValue(
                                        $specialty !== ''
                                            ? $specialty
                                            : 'Specialty not entered'
                                    ) ?>
                                </span>

                                <div class="preview-divider"></div>

                                <span class="preview-label">
                                    Qualifications
                                </span>

                                <p
                                    id="previewQualifications"
                                    class="preview-value"
                                >
                                    <?= escapeDoctorProfileValue(
                                        $qualifications !== ''
                                            ? $qualifications
                                            : 'Not provided'
                                    ) ?>
                                </p>

                                <span class="preview-label">
                                    Experience
                                </span>

                                <p
                                    id="previewExperience"
                                    class="preview-value"
                                >
                                    <?= escapeDoctorProfileValue(
                                        $experience
                                    ) ?>
                                    years
                                </p>

                                <span class="preview-label">
                                    Biography
                                </span>

                                <p
                                    id="previewBiography"
                                    class="preview-value"
                                    style="white-space:pre-line;"
                                ><?= escapeDoctorProfileValue(
                                    trim($biography) !== ''
                                        ? $biography
                                        : 'No biography provided.'
                                ) ?></p>

                            </div>

                            <div class="preview-footer">

                                <span id="previewNotice">
                                    This preview reflects
                                    your current form values.
                                    Changes are saved only
                                    when you submit the form.
                                </span>

                            </div>

                        </section>

                        <!-- Profile guidance -->

                        <section class="info-card highlight">

                            <h3>
                                Present Your Expertise Clearly
                            </h3>

                            <p>
                                Your specialty, qualifications,
                                experience and biography help
                                patients review your professional
                                information when searching
                                for a doctor.
                            </p>

                        </section>

                        <section class="info-card">

                            <h3>
                                Manage Your Schedule
                            </h3>

                            <p>
                                Keep your working hours
                                and availability periods
                                up to date alongside
                                your professional profile.
                            </p>

                            <a
                                href="availability.php"
                                class="info-link"
                            >
                                My Availability

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

                        </section>

                        <section class="info-card">

                            <h3>
                                Review Appointments
                            </h3>

                            <p>
                                Check the visits scheduled
                                with your doctor account
                                in the Doctor Portal.
                            </p>

                            <a
                                href="appointments.php"
                                class="info-link"
                            >
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

                        </section>

                    </aside>

                </div>

            <?php else: ?>

                <section class="unavailable-card">

                    <h2>
                        Professional Profile Unavailable
                    </h2>

                    <p>
                        Your professional profile
                        could not be loaded.
                        Please return to your
                        dashboard and try again.
                    </p>

                    <a
                        href="../dashboards/doctor-dashboard.php"
                        class="secondary-button"
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
                    Doctor Portal
                </span>

            </footer>

        </main>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('doctorProfileForm');

    if (!form) {
        return;
    }

    const fields = {
        fullName: document.getElementById('full_name'),
        specialty: document.getElementById('specialty'),
        qualifications: document.getElementById('qualifications'),
        experience: document.getElementById('years_experience'),
        biography: document.getElementById('biography')
    };

    const previews = {
        fullName: document.getElementById('previewName'),
        specialty: document.getElementById('previewSpecialty'),
        qualifications: document.getElementById('previewQualifications'),
        experience: document.getElementById('previewExperience'),
        biography: document.getElementById('previewBiography')
    };

    const biographyCount =
        document.getElementById('biographyCount');

    const previewNotice =
        document.getElementById('previewNotice');

    let hasChanges = false;

    function updatePreview() {

        const name = fields.fullName.value.trim();
        const specialty = fields.specialty.value.trim();
        const qualifications =
            fields.qualifications.value.trim();

        const years = fields.experience.value.trim();
        const biography = fields.biography.value.trim();

        // Use textContent to avoid HTML injection.
        previews.fullName.textContent =
            name || 'Doctor name not entered';

        previews.specialty.textContent =
            specialty || 'Specialty not entered';

        previews.qualifications.textContent =
            qualifications || 'Not provided';

        previews.experience.textContent =
            years === ''
                ? 'Not provided'
                : years + (
                    Number(years) === 1
                        ? ' year'
                        : ' years'
                );

        previews.biography.textContent =
            biography || 'No biography provided.';

        if (biographyCount) {
            biographyCount.textContent =
                fields.biography.value.length +
                ' / 65535 characters';
        }

        if (hasChanges && previewNotice) {
            previewNotice.textContent =
                'Previewing unsaved changes. ' +
                'Click Save Profile Changes to update your profile.';
        }
    }

    Object.values(fields).forEach(function (field) {

        field.addEventListener('input', function () {
            hasChanges = true;
            updatePreview();
        });

    });

    // Populate the preview and character count on load.
    updatePreview();

});
</script>

</body>
</html>