<?php

/**
 * Patient Appointment Confirmation
 * Clinic Appointment System
 *
 * Premium confirmation interface.
 * Existing backend data loading and authorization preserved.
 */

require_once __DIR__ .
    '/../includes/appointment_confirmation_data.php';

// Escape all displayed data.
function escapeConfirmationValue($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Format stored appointment dates.
function confirmationDate($value, string $format): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date($format, $timestamp);
}

function confirmationTime($value): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date('g:i A', $timestamp);
}

// Only use information returned by the existing backend.
$hasConfirmation = isset($confirmation)
    && is_array($confirmation);

$confirmationError = (string) (
    $confirmationError ?? ''
);

$status = $hasConfirmation
    ? strtolower(
        trim((string) ($confirmation['status'] ?? ''))
    )
    : '';

$isConfirmed = $status === 'confirmed';
$isCancelled = $status === 'cancelled';

$statusClass = $isConfirmed
    ? 'confirmed'
    : ($isCancelled ? 'cancelled' : 'other');

$statusLabel = $status !== ''
    ? ucfirst($status)
    : 'Unavailable';

$appointmentReference = $hasConfirmation
    ? (int) ($confirmation['appointment_id'] ?? 0)
    : 0;

$doctorName = $hasConfirmation
    ? (string) ($confirmation['doctor_name'] ?? '')
    : '';

$appointmentDate = $hasConfirmation
    ? (string) ($confirmation['appointment_date'] ?? '')
    : '';

$startTime = $hasConfirmation
    ? (string) ($confirmation['start_time'] ?? '')
    : '';

$endTime = $hasConfirmation
    ? (string) ($confirmation['end_time'] ?? '')
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
        Appointment Confirmation | Clinic Appointment System
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
            --navy-light: #173e61;
            --teal: #087d85;
            --teal-dark: #08676e;
            --teal-pale: #e8f6f3;
            --ink: #17374d;
            --muted: #718697;
            --border: #e2ebef;
            --background: #f4f8fa;
            --surface: #ffffff;
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

        button {
            font: inherit;
            cursor: pointer;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        a:focus-visible,
        button:focus-visible {
            outline: 3px solid rgba(8,125,133,.35);
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
            grid-template-columns: 260px minmax(0,1fr);
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
            margin-bottom: 13px;
            background: rgba(255,255,255,.12);
        }

        /* Top navigation */

        .main-area {
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;

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

        .topbar-link {
            display: inline-flex;
            align-items: center;
            gap: 9px;

            min-height: 41px;
            padding: 10px 15px;

            border: 1px solid var(--border);
            border-radius: 10px;
            background: white;

            font-size: .84rem;
            font-weight: 700;
        }

        .topbar-link:hover {
            background: #eff8f6;
            border-color: #c8dfdb;
        }

        .topbar-link svg {
            width: 17px;
            height: 17px;
        }

        /* Main content */

        .page-content {
            max-width: 1250px;
            margin: 0 auto;
            padding: 37px 42px 55px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 9px;

            margin-bottom: 26px;
            color: var(--teal);
            font-size: .85rem;
            font-weight: 800;
        }

        .back-link:hover {
            text-decoration: underline;
            text-underline-offset: 4px;
        }

        .back-link svg {
            width: 16px;
            height: 16px;
        }

        /* Confirmation hero */

        .confirmation-hero {
            position: relative;
            isolation: isolate;
            overflow: hidden;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 28px;

            min-height: 230px;
            padding: 39px 43px;

            border-radius: 23px;
            color: white;

            background:
                radial-gradient(
                    circle at 92% 15%,
                    rgba(80,197,181,.23),
                    transparent 40%
                ),
                linear-gradient(
                    115deg,
                    #102d46,
                    #17455a 65%,
                    #0e5860
                );
        }

        .confirmation-hero::after {
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
                0 0 0 46px rgba(178,236,227,.035);

            pointer-events: none;
        }

        .hero-eyebrow {
            display: block;
            margin-bottom: 16px;
            color: #a8eadf;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .confirmation-hero h1 {
            margin: 0 0 15px;

            font-family: "Manrope", sans-serif;
            font-size: clamp(1.95rem,3vw,2.75rem);
            font-weight: 800;
            letter-spacing: -.06em;
            line-height: 1.2;
        }

        .confirmation-hero p {
            max-width: 590px;
            margin: 0;
            color: #cce1e7;
            font-size: .94rem;
            line-height: 1.85;
        }

        .hero-mark {
            display: grid;
            place-items: center;
            width: 94px;
            height: 94px;
            flex-shrink: 0;

            border: 1px solid rgba(223,250,245,.2);
            border-radius: 24px;

            background: rgba(255,255,255,.09);
            color: #b3eee1;
        }

        .hero-mark svg {
            width: 48px;
            height: 48px;
        }

        /* Confirmation content */

        .confirmation-layout {
            display: grid;
            grid-template-columns: minmax(0,1.35fr)
                                   minmax(250px,.75fr);
            gap: 22px;
            margin-top: 25px;
        }

        .surface-card {
            min-width: 0;
            overflow: hidden;

            border: 1px solid var(--border);
            border-radius: 19px;
            background: white;

            box-shadow:
                0 10px 32px rgba(20,64,86,.045);
        }

        .card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;

            padding: 25px 29px;
            border-bottom: 1px solid var(--border);
        }

        .card-eyebrow {
            display: block;
            margin-bottom: 8px;
            color: var(--teal);
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .card-title {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: 1.1rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .card-description {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: .86rem;
            line-height: 1.7;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            padding: 9px 13px;
            border-radius: 10px;

            font-size: .8rem;
            font-weight: 800;
        }

        .status-badge.confirmed {
            background: #e7f7ee;
            color: #23754e;
        }

        .status-badge.cancelled {
            background: #fbecee;
            color: #a64b59;
        }

        .status-badge.other {
            background: #edf3f7;
            color: #506d82;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: currentColor;
            border-radius: 50%;
        }

        .card-body {
            padding: 28px 29px;
        }

        /* Appointment identity */

        .appointment-reference {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;

            margin-bottom: 25px;
            padding: 19px 20px;

            background: #f1f8f8;
            border: 1px solid #dfedeb;
            border-radius: 13px;
        }

        .reference-label {
            display: block;
            margin-bottom: 6px;
            color: #6e8993;
            font-size: .76rem;
            font-weight: 700;
        }

        .reference-number {
            color: #155063;
            font-family: "Manrope", sans-serif;
            font-size: 1.4rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .reference-note {
            color: #718c94;
            font-size: .8rem;
        }

        /* Doctor and schedule */

        .doctor-block {
            display: flex;
            align-items: center;
            gap: 16px;

            margin-bottom: 27px;
            padding-bottom: 26px;
            border-bottom: 1px solid var(--border);
        }

        .doctor-avatar {
            display: grid;
            place-items: center;
            width: 53px;
            height: 53px;
            flex-shrink: 0;

            border-radius: 15px;
            background: #e7f5f2;
            color: var(--teal);
        }

        .doctor-avatar svg {
            width: 27px;
            height: 27px;
        }

        .detail-label {
            display: block;
            margin-bottom: 7px;
            color: #8699a6;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .doctor-name {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: 1.12rem;
            font-weight: 800;
            letter-spacing: -.04em;
            overflow-wrap: anywhere;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 25px;
        }

        .detail-wide {
            grid-column: 1 / -1;
        }

        .detail-value {
            color: #2f5165;
            font-size: .92rem;
            font-weight: 700;
            line-height: 1.65;
            overflow-wrap: anywhere;
        }

        .timezone-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-top: 26px;
            padding-top: 21px;

            border-top: 1px solid var(--border);

            color: #8195a2;
            font-size: .82rem;
            line-height: 1.7;
        }

        .timezone-note svg {
            width: 17px;
            height: 17px;
            color: var(--teal);
        }

        /* Side information panel */

        .side-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .visit-card {
            padding: 27px;
            background:
                linear-gradient(160deg,#e9f7f4,#ffffff);
            border-color: #d7eae6;
        }

        .visit-card h2,
        .next-card h2 {
            margin: 0 0 13px;
            color: var(--ink);
            font-family: "Manrope", sans-serif;
            font-size: 1rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .calendar-date {
            margin: 23px 0 16px;
            padding: 23px 15px;
            border: 1px solid #d4e9e3;
            border-radius: 15px;
            background: white;
            text-align: center;
        }

        .calendar-month {
            display: block;
            margin-bottom: 5px;
            color: var(--teal);
            font-size: .77rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .13em;
        }

        .calendar-day {
            display: block;
            color: #153c50;
            font-family: "Manrope", sans-serif;
            font-size: 3.4rem;
            line-height: 1.15;
            letter-spacing: -.07em;
            font-weight: 800;
        }

        .calendar-weekday {
            display: block;
            margin-top: 7px;
            color: #658292;
            font-size: .84rem;
            font-weight: 700;
        }

        .visit-time {
            text-align: center;
            color: #315467;
            font-size: .9rem;
            font-weight: 700;
            line-height: 1.7;
        }

        .next-card {
            padding: 27px;
        }

        .next-card p {
            margin: 0;
            color: var(--muted);
            font-size: .86rem;
            line-height: 1.8;
        }

        .next-card a {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            margin-top: 19px;
            color: var(--teal);
            font-size: .86rem;
            font-weight: 800;
        }

        .next-card a:hover {
            text-decoration: underline;
        }

        /* Page actions */

        .actions-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            margin-top: 26px;
        }

        .primary-button,
        .secondary-button,
        .print-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 11px;

            min-height: 48px;
            padding: 12px 19px;

            border-radius: 11px;
            font-size: .86rem;
            font-weight: 800;

            transition: background .2s, transform .2s;
        }

        .primary-button {
            border: 1px solid var(--teal);
            background: var(--teal);
            color: white;
        }

        .primary-button:hover {
            color: white;
            background: var(--teal-dark);
            transform: translateY(-1px);
        }

        .secondary-button,
        .print-button {
            border: 1px solid #d5e3e7;
            background: white;
            color: #3c5d70;
        }

        .secondary-button:hover,
        .print-button:hover {
            background: #edf7f5;
            border-color: #badbd5;
        }

        .actions-bar svg {
            width: 18px;
            height: 18px;
        }

        /* Invalid appointment */

        .error-card {
            margin-top: 25px;
            padding: 31px;

            border: 1px solid #f0dec0;
            border-radius: 18px;
            background: #fffaf1;
        }

        .error-card h2 {
            margin: 0 0 12px;
            color: #805c1f;
            font-family: "Manrope", sans-serif;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .error-card p {
            margin: 0;
            color: #876f48;
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
            color: #8b9da9;
            font-size: .77rem;
        }

        /* Responsive design */

        @media (max-width: 1120px) {
            .app-layout {
                grid-template-columns: 220px minmax(0,1fr);
            }

            .topbar {
                padding: 18px 28px;
            }

            .page-content {
                padding: 28px;
            }

            .confirmation-layout {
                grid-template-columns: 1fr;
            }

            .side-column {
                display: grid;
                grid-template-columns: repeat(2,minmax(0,1fr));
                align-items: start;
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
                padding-bottom: 14px;
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

        @media (max-width: 600px) {
            .sidebar {
                padding: 15px 13px;
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

            .page-content {
                padding: 19px 13px 34px;
            }

            .confirmation-hero {
                min-height: 190px;
                padding: 30px 24px;
            }

            .confirmation-hero h1 {
                font-size: 1.95rem;
            }

            .hero-mark {
                display: none;
            }

            .card-header,
            .card-body {
                padding: 23px 20px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
                gap: 21px;
            }

            .detail-wide {
                grid-column: auto;
            }

            .appointment-reference {
                padding: 17px;
            }

            .side-column {
                display: flex;
            }

            .visit-card,
            .next-card {
                padding: 23px;
            }

            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .primary-button,
            .secondary-button,
            .print-button {
                width: 100%;
            }
        }

        /* Print-friendly confirmation */

        @media print {
            body {
                background: white;
                color: #172f41;
            }

            .app-layout {
                display: block;
            }

            .sidebar,
            .topbar,
            .back-link,
            .actions-bar,
            .footer {
                display: none !important;
            }

            .page-content {
                max-width: none;
                padding: 0;
            }

            .confirmation-hero {
                min-height: 0;
                padding: 22px;
                border: 1px solid #d5e2e6;
                border-radius: 8px;
                background: white !important;
                color: #17374d !important;
            }

            .confirmation-hero h1,
            .confirmation-hero p,
            .hero-eyebrow {
                color: #17374d !important;
            }

            .hero-mark {
                display: none;
            }

            .confirmation-layout {
                display: block;
                margin-top: 18px;
            }

            .surface-card {
                margin-bottom: 15px;
                border-radius: 8px;
                box-shadow: none;
                break-inside: avoid;
            }

            .side-column {
                display: none;
            }

            .card-header,
            .card-body {
                padding: 16px 20px;
            }

            .appointment-reference,
            .status-badge {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
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

<div class="app-layout">

    <!-- Patient Portal sidebar -->

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

    <!-- Main content -->

    <div class="main-area">

        <header class="topbar">

            <div>

                <span class="topbar-eyebrow">
                    Patient Portal
                </span>

                <h2 class="topbar-title">
                    Appointment Confirmation
                </h2>

            </div>

            <a
                href="appointments.php"
                class="topbar-link"
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

                My Appointments
            </a>

        </header>

        <main class="page-content">

            <a
                href="appointments.php"
                class="back-link"
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

                Back to My Appointments
            </a>

            <!-- Page introduction -->

            <section class="confirmation-hero">

                <div>

                    <span class="hero-eyebrow">
                        Your Appointment
                    </span>

                    <h1>

                        <?php if (!$hasConfirmation): ?>

                            Appointment details unavailable.

                        <?php elseif ($isConfirmed): ?>

                            Your appointment is confirmed.

                        <?php elseif ($isCancelled): ?>

                            Appointment cancelled.

                        <?php else: ?>

                            Your appointment details.

                        <?php endif; ?>

                    </h1>

                    <p>

                        <?php if (!$hasConfirmation): ?>

                            We couldn't display the requested
                            appointment. Return to your
                            appointment history to review
                            your bookings.

                        <?php elseif ($isConfirmed): ?>

                            Your booking has been recorded
                            in the clinic's scheduling system.
                            You can review the confirmed
                            details below.

                        <?php else: ?>

                            Review the current information
                            and status associated with
                            this appointment.

                        <?php endif; ?>

                    </p>

                </div>

                <span
                    class="hero-mark"
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
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M7 3v4M17 3v4M3 10h18"/>
                        <path d="m8.5 15 2.5 2.5 5-5"/>
                    </svg>
                </span>

            </section>

            <?php if (!$hasConfirmation): ?>

                <!-- Missing or unauthorized appointment -->

                <section
                    class="error-card"
                    role="alert"
                >
                    <h2>
                        Unable to Display Appointment
                    </h2>

                    <p>
                        <?= escapeConfirmationValue(
                            $confirmationError !== ''
                                ? $confirmationError
                                : 'Appointment information could not be loaded.'
                        ) ?>
                    </p>
                </section>

            <?php else: ?>

                <!-- Real appointment information -->

                <div class="confirmation-layout">

                    <section class="surface-card">

                        <div class="card-header">

                            <div>

                                <span class="card-eyebrow">
                                    Booking Record
                                </span>

                                <h2 class="card-title">
                                    Appointment Details
                                </h2>

                                <p class="card-description">
                                    Information retrieved from
                                    your appointment record.
                                </p>

                            </div>

                            <span
                                class="status-badge <?= $statusClass ?>"
                            >
                                <span
                                    class="status-dot"
                                    aria-hidden="true"
                                ></span>

                                <?= escapeConfirmationValue(
                                    $statusLabel
                                ) ?>
                            </span>

                        </div>

                        <div class="card-body">

                            <!-- Reference number -->

                            <div class="appointment-reference">

                                <div>

                                    <span class="reference-label">
                                        Appointment Reference
                                    </span>

                                    <span class="reference-number">
                                        #<?= $appointmentReference ?>
                                    </span>

                                </div>

                                <span class="reference-note">
                                    Clinic Appointment System
                                </span>

                            </div>

                            <!-- Doctor -->

                            <div class="doctor-block">

                                <span
                                    class="doctor-avatar"
                                    aria-hidden="true"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path d="M6 3v5a4 4 0 0 0 8 0V3"/>
                                        <path d="M6 3H4m10 0h2"/>
                                        <path d="M10 12v3a5 5 0 0 0 10 0v-1"/>
                                        <circle cx="20" cy="11" r="2"/>
                                    </svg>
                                </span>

                                <div>

                                    <span class="detail-label">
                                        Doctor
                                    </span>

                                    <h3 class="doctor-name">
                                        <?= escapeConfirmationValue(
                                            $doctorName
                                        ) ?>
                                    </h3>

                                </div>

                            </div>

                            <!-- Date and time -->

                            <div class="detail-grid">

                                <div class="detail-wide">

                                    <span class="detail-label">
                                        Appointment Date
                                    </span>

                                    <span class="detail-value">
                                        <?= escapeConfirmationValue(
                                            confirmationDate(
                                                $appointmentDate,
                                                'l, F j, Y'
                                            )
                                        ) ?>
                                    </span>

                                </div>

                                <div>

                                    <span class="detail-label">
                                        Start Time
                                    </span>

                                    <span class="detail-value">
                                        <?= escapeConfirmationValue(
                                            confirmationTime(
                                                $startTime
                                            )
                                        ) ?>
                                    </span>

                                </div>

                                <div>

                                    <span class="detail-label">
                                        End Time
                                    </span>

                                    <span class="detail-value">
                                        <?= escapeConfirmationValue(
                                            confirmationTime(
                                                $endTime
                                            )
                                        ) ?>
                                    </span>

                                </div>

                            </div>

                            <div class="timezone-note">

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="12" r="9"/>
                                    <path d="M12 7v5l3 2"/>
                                </svg>

                                Appointment times are shown
                                in Afghanistan local time
                                (Asia/Kabul).

                            </div>

                        </div>

                    </section>

                    <!-- Appointment summary -->

                    <aside class="side-column">

                        <div class="surface-card visit-card">

                            <h2>
                                Appointment Date
                            </h2>

                            <div class="calendar-date">

                                <span class="calendar-month">
                                    <?= escapeConfirmationValue(
                                        confirmationDate(
                                            $appointmentDate,
                                            'F Y'
                                        )
                                    ) ?>
                                </span>

                                <span class="calendar-day">
                                    <?= escapeConfirmationValue(
                                        confirmationDate(
                                            $appointmentDate,
                                            'j'
                                        )
                                    ) ?>
                                </span>

                                <span class="calendar-weekday">
                                    <?= escapeConfirmationValue(
                                        confirmationDate(
                                            $appointmentDate,
                                            'l'
                                        )
                                    ) ?>
                                </span>

                            </div>

                            <div class="visit-time">

                                <?= escapeConfirmationValue(
                                    confirmationTime($startTime)
                                ) ?>

                                –

                                <?= escapeConfirmationValue(
                                    confirmationTime($endTime)
                                ) ?>

                            </div>

                        </div>

                        <div class="surface-card next-card">

                            <h2>
                                Manage Your Appointment
                            </h2>

                            <p>
                                Review your appointment
                                history and any available
                                cancellation or rescheduling
                                options in My Appointments.
                            </p>

                            <a href="appointments.php">
                                View My Appointments
                                <span aria-hidden="true">&rarr;</span>
                            </a>

                        </div>

                    </aside>

                </div>

            <?php endif; ?>

            <!-- Working actions -->

            <div class="actions-bar">

                <a
                    href="appointments.php"
                    class="primary-button"
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
                    </svg>

                    My Appointments
                </a>

                <a
                    href="doctor-search.php"
                    class="secondary-button"
                >
                    Find a Doctor
                </a>

                <?php if ($hasConfirmation): ?>

                    <button
                        type="button"
                        id="printConfirmation"
                        class="print-button"
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
                            <path d="M6 9V3h12v6"/>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                            <path d="M6 14h12v7H6z"/>
                        </svg>

                        Print Confirmation
                    </button>

                <?php endif; ?>

            </div>

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

<script>
document.addEventListener('DOMContentLoaded', function () {

    const printButton = document.getElementById(
        'printConfirmation'
    );

    if (printButton) {
        printButton.addEventListener('click', function () {
            window.print();
        });
    }

});
</script>

</body>
</html>