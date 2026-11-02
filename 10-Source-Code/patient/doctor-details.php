<?php

/**
 * Doctor Profile & Appointment Booking
 * Clinic Appointment System
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/doctor_repository.php';
require_once __DIR__ . '/../includes/appointment_repository.php';

// Retrieve one-time booking messages.
$bookingSuccess = $_SESSION['booking_success'] ?? '';
$bookingError = $_SESSION['booking_error'] ?? '';

unset(
    $_SESSION['booking_success'],
    $_SESSION['booking_error']
);

// Preserve booking CSRF protection.
if (
    empty($_SESSION['booking_csrf_token']) ||
    !is_string($_SESSION['booking_csrf_token'])
) {
    $_SESSION['booking_csrf_token'] =
        bin2hex(random_bytes(32));
}

$bookingToken = $_SESSION['booking_csrf_token'];

// Validate doctor ID.
$doctorInput = $_GET['doctor_id'] ?? null;

$doctorId = (
    is_string($doctorInput) ||
    is_int($doctorInput)
)
    ? filter_var(
        $doctorInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;

// Optional appointment date filter.
$selectedDate = $_GET['appointment_date'] ?? '';

if (
    !is_string($selectedDate) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)
) {
    $selectedDate = '';
}

$doctor = null;
$availability = [];
$appointmentSlots = [];

// Retrieve genuine doctor data and availability.
if (is_int($doctorId)) {

    $doctor = getDoctorById(
        $conn,
        $doctorId
    );

    if ($doctor !== null) {

        $availability = getDoctorAvailability(
            $conn,
            $doctorId
        );

        $appointmentSlots = getAvailableAppointmentSlots(
            $conn,
            $doctorId,
            $availability
        );

        // Preserve the existing date filter.
        if ($selectedDate !== '') {

            $appointmentSlots = array_values(
                array_filter(
                    $appointmentSlots,
                    function (array $slot) use ($selectedDate): bool {
                        return $slot['appointment_date'] === $selectedDate;
                    }
                )
            );

        }
    }
}

$conn->close();

function profileEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function profileTime($value): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date('g:i A', $timestamp);
}

function profileDate($value): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date('D, M j, Y', $timestamp);
}

function profileInitials($name): string
{
    $parts = preg_split('/\s+/', trim((string) $name));

    if (!$parts || $parts[0] === '') {
        return 'DR';
    }

    $first = strtoupper(substr($parts[0], 0, 1));

    if (count($parts) === 1) {
        return $first;
    }

    return $first . strtoupper(
        substr($parts[count($parts) - 1], 0, 1)
    );
}

// Group available appointment slots by date.
$slotsByDate = [];

foreach ($appointmentSlots as $slot) {

    $date = $slot['appointment_date'];

    if (!isset($slotsByDate[$date])) {
        $slotsByDate[$date] = [];
    }

    $slotsByDate[$date][] = $slot;
}

ksort($slotsByDate);

foreach ($slotsByDate as &$slots) {

    usort(
        $slots,
        function ($a, $b) {
            return strcmp(
                $a['start_time'],
                $b['start_time']
            );
        }
    );

}

unset($slots);

$totalSlots = count($appointmentSlots);

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
        Doctor Profile | Clinic Appointment System
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
            --teal: #087d85;
            --teal-dark: #08666d;
            --ink: #17374d;
            --muted: #718697;
            --border: #e1eaee;
            --background: #f4f8fa;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--background);
            color: var(--ink);
            font-family: "DM Sans", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        button,
        input {
            font: inherit;
        }

        button {
            cursor: pointer;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible {
            outline: 3px solid rgba(8,125,133,.35);
            outline-offset: 3px;
        }

        /* Patient portal layout */

        .app-layout {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 30px 18px 22px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            color: white;

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

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 3px 12px 34px;
            font-family: "Manrope", sans-serif;
            font-weight: 800;
            font-size: .89rem;
            letter-spacing: -.03em;
            line-height: 1.45;
        }

        .brand-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            flex-shrink: 0;
            color: #98e9db;
            background: rgba(117,220,204,.12);
            border: 1px solid rgba(138,228,213,.2);
            font-size: 1.5rem;
        }

        .nav-caption {
            padding: 0 15px 13px;
            font-size: .67rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .16em;
            color: #91adbb;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            min-height: 46px;
            padding: 11px 15px;
            border-radius: 11px;
            color: #c0d1da;
            font-size: .89rem;
            font-weight: 600;
            transition: background .2s, color .2s;
        }

        .nav-link:hover {
            background: rgba(255,255,255,.08);
            color: white;
        }

        .nav-link.active {
            background: rgba(113,209,195,.14);
            color: white;
            border: 1px solid rgba(152,226,215,.13);
        }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid rgba(255,255,255,.11);
        }

        .main-area {
            min-width: 0;
        }

        .topbar {
            min-height: 88px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            padding: 18px 42px;
            background: white;
            border-bottom: 1px solid var(--border);
        }

        .topbar-caption {
            display: block;
            margin-bottom: 5px;
            color: var(--teal);
            font-size: .69rem;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .topbar-title {
            font-family: "Manrope", sans-serif;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .directory-link {
            padding: 11px 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 700;
        }

        .directory-link:hover {
            background: #edf8f6;
            color: var(--teal);
        }

        .page-content {
            max-width: 1450px;
            margin: auto;
            padding: 35px 42px 50px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 25px;
            color: var(--teal);
            font-size: .86rem;
            font-weight: 800;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Doctor profile header */

        .doctor-hero {
            position: relative;
            overflow: hidden;
            padding: 36px 40px;
            border-radius: 22px;
            color: white;

            background:
                radial-gradient(
                    circle at 90% 15%,
                    rgba(80,197,181,.22),
                    transparent 42%
                ),
                linear-gradient(
                    115deg,
                    #102d46,
                    #173f56 68%,
                    #0b5660
                );
        }

        .hero-eyebrow {
            display: block;
            margin-bottom: 19px;
            color: #9fe9df;
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }

        .doctor-identity {
            display: flex;
            align-items: center;
            gap: 23px;
        }

        .doctor-avatar {
            display: grid;
            place-items: center;
            width: 100px;
            height: 100px;
            flex-shrink: 0;
            border-radius: 23px;
            border: 1px solid rgba(218,249,243,.23);
            background: rgba(223,255,250,.13);
            color: #c5f6ec;
            font-family: "Manrope", sans-serif;
            font-size: 1.9rem;
            font-weight: 800;
        }

        .doctor-hero h1 {
            margin: 0 0 12px;
            font-family: "Manrope", sans-serif;
            font-size: clamp(1.85rem,3vw,2.6rem);
            line-height: 1.25;
            letter-spacing: -.055em;
            overflow-wrap: anywhere;
        }

        .specialty-badge {
            display: inline-block;
            padding: 8px 13px;
            border-radius: 9px;
            background: rgba(178,236,227,.13);
            border: 1px solid rgba(178,236,227,.22);
            color: #cbf2e9;
            font-size: .84rem;
            font-weight: 700;
        }

        /* General cards */

        .card-grid {
            display: grid;
            grid-template-columns: minmax(0,1.25fr) minmax(250px,.75fr);
            gap: 22px;
            margin-top: 24px;
        }

        .surface-card {
            min-width: 0;
            overflow: hidden;
            background: white;
            border: 1px solid var(--border);
            border-radius: 19px;
            box-shadow: 0 9px 28px rgba(20,64,86,.045);
        }

        .card-header {
            padding: 24px 28px;
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
            font-size: 1.11rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .card-description {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: .86rem;
            line-height: 1.7;
        }

        .card-body {
            padding: 26px 28px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 24px;
        }

        .detail-label {
            display: block;
            margin-bottom: 8px;
            color: #8295a3;
            font-size: .73rem;
            font-weight: 800;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .detail-value {
            color: #2e5065;
            font-size: .93rem;
            line-height: 1.75;
            overflow-wrap: anywhere;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .bio-block {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid var(--border);
        }

        .bio-block h3 {
            margin: 0 0 10px;
            font-family: "Manrope", sans-serif;
            font-size: .98rem;
        }

        .bio-block p {
            margin: 0;
            color: #607a8b;
            font-size: .89rem;
            line-height: 1.85;
        }

        /* Booking guide */

        .guide-step {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 23px;
        }

        .guide-step:last-child {
            margin-bottom: 0;
        }

        .step-number {
            display: grid;
            place-items: center;
            width: 35px;
            height: 35px;
            flex-shrink: 0;
            border-radius: 10px;
            background: #e6f5f1;
            color: var(--teal);
            font-size: .8rem;
            font-weight: 800;
        }

        .guide-step h3 {
            margin: 2px 0 5px;
            font-family: "Manrope", sans-serif;
            font-size: .87rem;
            font-weight: 800;
        }

        .guide-step p {
            margin: 0;
            color: var(--muted);
            font-size: .83rem;
            line-height: 1.65;
        }

        /* Section headings */

        .section-header {
            margin: 36px 0 20px;
        }

        .section-eyebrow {
            display: block;
            margin-bottom: 8px;
            color: var(--teal);
            font-size: .7rem;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .section-header h2 {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: 1.38rem;
            font-weight: 800;
            letter-spacing: -.045em;
        }

        .section-header p {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: .89rem;
            line-height: 1.7;
        }

        /* Availability table */

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .schedule-table {
            width: 100%;
            border-collapse: collapse;
        }

        .schedule-table th {
            padding: 16px 19px;
            text-align: left;
            background: #f5f9fa;
            color: #718797;
            font-size: .73rem;
            font-weight: 800;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .schedule-table td {
            padding: 17px 19px;
            border-top: 1px solid #edf2f4;
            color: #355469;
            font-size: .87rem;
            white-space: nowrap;
        }

        .duration {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 8px;
            background: #e8f6f3;
            color: #087780;
            font-size: .78rem;
            font-weight: 700;
        }

        /* Booking filter */

        .booking-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
        }

        .slot-count {
            padding: 9px 13px;
            border-radius: 30px;
            background: #e8f6f3;
            color: #08676e;
            font-size: .8rem;
            font-weight: 800;
        }

        .date-filter {
            display: flex;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 13px;
            padding: 21px;
            margin-bottom: 25px;
            border-radius: 14px;
            border: 1px solid #e2ecee;
            background: #f7fafb;
        }

        .date-field {
            flex: 1 1 235px;
            min-width: 0;
        }

        .field-label {
            display: block;
            margin-bottom: 9px;
            color: #31536a;
            font-size: .86rem;
            font-weight: 700;
        }

        .date-input {
            display: block;
            width: 100%;
            min-height: 48px;
            padding: 10px 13px;
            border-radius: 10px;
            border: 1px solid #d8e5e9;
            color: var(--ink);
            background: white;
        }

        .date-input:focus {
            border-color: var(--teal);
            outline: none;
            box-shadow: 0 0 0 4px rgba(8,125,133,.1);
        }

        .button-primary,
        .button-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 11px 17px;
            border-radius: 10px;
            font-size: .84rem;
            font-weight: 800;
        }

        .button-primary {
            border: 1px solid var(--teal);
            background: var(--teal);
            color: white;
        }

        .button-primary:hover {
            background: var(--teal-dark);
            color: white;
        }

        .button-secondary {
            border: 1px solid #d7e4e9;
            background: white;
            color: #34566b;
        }

        .button-secondary:hover {
            background: #edf8f5;
        }

        /* Bookable time cards */

        .date-group {
            margin-bottom: 29px;
        }

        .date-group:last-child {
            margin-bottom: 0;
        }

        .date-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 15px;
        }

        .date-heading h3 {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: .98rem;
            font-weight: 800;
        }

        .date-heading span {
            color: var(--muted);
            font-size: .8rem;
        }

        .slot-grid {
            display: grid;
            grid-template-columns: repeat(3,minmax(0,1fr));
            gap: 14px;
        }

        .slot-card {
            min-width: 0;
            padding: 20px;
            border: 1px solid #dce9ec;
            border-radius: 14px;
            background: white;
            transition: transform .2s, border-color .2s;
        }

        .slot-card:hover {
            transform: translateY(-2px);
            border-color: #a6d8d1;
        }

        .slot-time {
            display: block;
            margin-bottom: 7px;
            font-family: "Manrope", sans-serif;
            color: #173f54;
            font-size: .97rem;
            font-weight: 800;
        }

        .slot-caption {
            display: block;
            margin-bottom: 20px;
            color: #78909d;
            font-size: .8rem;
        }

        .book-button {
            width: 100%;
            min-height: 43px;
            padding: 10px 13px;
            border: 1px solid var(--teal);
            border-radius: 10px;
            background: var(--teal);
            color: white;
            font-size: .83rem;
            font-weight: 800;
        }

        .book-button:hover {
            background: var(--teal-dark);
        }

        /* Notices and empty states */

        .notice {
            padding: 17px 20px;
            margin-bottom: 23px;
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
            background: #eaf8f0;
            border-color: #c8ead5;
            color: #276e50;
        }

        .notice.error {
            background: #fff2f1;
            border-color: #efd1d0;
            color: #a13b42;
        }

        .notice.warning {
            background: #fff8e7;
            border-color: #f1dfb4;
            color: #80601d;
        }

        .empty-state {
            padding: 24px;
            background: #f7fafb;
            border: 1px solid #e1ecf0;
            border-radius: 14px;
        }

        .empty-state h3 {
            margin: 0 0 8px;
            font-family: "Manrope", sans-serif;
            font-size: .97rem;
        }

        .empty-state p {
            margin: 0;
            color: var(--muted);
            font-size: .87rem;
            line-height: 1.8;
        }

        .booking-note {
            margin: 24px 0 0;
            padding-top: 19px;
            border-top: 1px solid var(--border);
            color: #8294a2;
            font-size: .83rem;
            line-height: 1.7;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding-top: 35px;
            color: #8b9eaa;
            font-size: .76rem;
        }

        /* Responsive design */

        @media (max-width: 1150px) {
            .card-grid {
                grid-template-columns: 1fr;
            }

            .slot-grid {
                grid-template-columns: repeat(2,minmax(0,1fr));
            }
        }

        @media (max-width: 950px) {
            .app-layout {
                grid-template-columns: 220px minmax(0,1fr);
            }

            .topbar {
                padding: 18px 28px;
            }

            .page-content {
                padding: 28px;
            }
        }

        @media (max-width: 800px) {
            .app-layout {
                display: block;
            }

            .sidebar {
                position: static;
                height: auto;
                padding: 16px 18px;
            }

            .brand {
                padding-bottom: 14px;
            }

            .nav-caption {
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

            .topbar {
                padding: 16px 22px;
            }

            .page-content {
                padding: 24px 20px;
            }
        }

        @media (max-width: 600px) {
            .sidebar {
                padding: 15px 12px;
            }

            .nav-link {
                font-size: .8rem;
                padding: 9px 10px;
            }

            .page-content {
                padding: 19px 13px 30px;
            }

            .doctor-hero {
                padding: 26px 22px;
            }

            .doctor-identity {
                gap: 14px;
            }

            .doctor-avatar {
                width: 68px;
                height: 68px;
                border-radius: 15px;
                font-size: 1.3rem;
            }

            .doctor-hero h1 {
                font-size: 1.6rem;
            }

            .detail-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }

            .card-header,
            .card-body {
                padding: 22px 20px;
            }

            .slot-grid {
                grid-template-columns: 1fr;
            }

            .date-field {
                flex-basis: 100%;
            }

            .button-primary,
            .button-secondary {
                flex: 1;
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

    <!-- Patient navigation -->

    <aside class="sidebar">

        <a
            href="../dashboards/patient-dashboard.php"
            class="brand"
        >
            <span
                class="brand-icon"
                aria-hidden="true"
            >+</span>

            <span>
                Clinic Appointment<br>
                System
            </span>
        </a>

        <div class="nav-caption">
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
                Dashboard
            </a>

            <a
                href="doctor-search.php"
                class="nav-link active"
            >
                Find a Doctor
            </a>

            <a
                href="appointments.php"
                class="nav-link"
            >
                My Appointments
            </a>

            <a
                href="profile.php"
                class="nav-link"
            >
                My Profile
            </a>

        </nav>

        <div class="sidebar-bottom">

            <a
                href="../logout.php"
                class="nav-link"
            >
                Logout
            </a>

        </div>

    </aside>

    <!-- Main content -->

    <div class="main-area">

        <header class="topbar">

            <div>

                <span class="topbar-caption">
                    Patient Portal
                </span>

                <div class="topbar-title">
                    Doctor Profile & Booking
                </div>

            </div>

            <a
                href="doctor-search.php"
                class="directory-link"
            >
                Back to Directory
            </a>

        </header>

        <main class="page-content">

            <a
                href="doctor-search.php"
                class="back-link"
            >
                &larr; Back to Doctor Search
            </a>

            <!-- Booking feedback -->

            <?php if ($bookingSuccess !== ''): ?>

                <div
                    class="notice success"
                    role="status"
                >
                    <strong>
                        Appointment Update
                    </strong>

                    <?= profileEscape($bookingSuccess) ?>
                </div>

            <?php endif; ?>

            <?php if ($bookingError !== ''): ?>

                <div
                    class="notice error"
                    role="alert"
                >
                    <strong>
                        Booking Unsuccessful
                    </strong>

                    <?= profileEscape($bookingError) ?>
                </div>

            <?php endif; ?>

            <?php if ($doctor === null): ?>

                <div
                    class="notice warning"
                    role="alert"
                >
                    <strong>
                        Doctor Not Found
                    </strong>

                    The requested doctor could not be found.
                    Return to Doctor Search to select
                    an available doctor.
                </div>

            <?php else: ?>

                <!-- Doctor identity -->

                <section class="doctor-hero">

                    <span class="hero-eyebrow">
                        Doctor Profile
                    </span>

                    <div class="doctor-identity">

                        <div
                            class="doctor-avatar"
                            aria-hidden="true"
                        >
                            <?= profileEscape(
                                profileInitials(
                                    $doctor['full_name']
                                )
                            ) ?>
                        </div>

                        <div>

                            <h1>
                                <?= profileEscape(
                                    $doctor['full_name']
                                ) ?>
                            </h1>

                            <?php if (
                                !empty($doctor['specialty'])
                            ): ?>

                                <span class="specialty-badge">
                                    <?= profileEscape(
                                        $doctor['specialty']
                                    ) ?>
                                </span>

                            <?php endif; ?>

                        </div>

                    </div>

                </section>

                <!-- Professional details -->

                <div class="card-grid">

                    <section class="surface-card">

                        <div class="card-header">

                            <span class="card-eyebrow">
                                About the Doctor
                            </span>

                            <h2 class="card-title">
                                Professional Information
                            </h2>

                            <p class="card-description">
                                Qualifications, experience,
                                and professional background.
                            </p>

                        </div>

                        <div class="card-body">

                            <div class="detail-grid">

                                <div>

                                    <span class="detail-label">
                                        Medical Specialty
                                    </span>

                                    <div class="detail-value">
                                        <?= profileEscape(
                                            $doctor['specialty']
                                            ?? 'Not listed'
                                        ) ?>
                                    </div>

                                </div>

                                <div>

                                    <span class="detail-label">
                                        Years of Experience
                                    </span>

                                    <div class="detail-value">

                                        <?php if (
                                            isset(
                                                $doctor['years_experience']
                                            ) &&
                                            $doctor['years_experience'] !== ''
                                        ): ?>

                                            <?= profileEscape(
                                                $doctor['years_experience']
                                            ) ?>
                                            years

                                        <?php else: ?>

                                            Not listed

                                        <?php endif; ?>

                                    </div>

                                </div>

                                <div class="full-width">

                                    <span class="detail-label">
                                        Professional Qualifications
                                    </span>

                                    <div class="detail-value">
                                        <?= profileEscape(
                                            !empty(
                                                $doctor['qualifications']
                                            )
                                                ? $doctor['qualifications']
                                                : 'Not listed'
                                        ) ?>
                                    </div>

                                </div>

                            </div>

                            <div class="bio-block">

                                <h3>
                                    Professional Biography
                                </h3>

                                <p>

                                    <?php if (
                                        !empty($doctor['biography'])
                                    ): ?>

                                        <?= nl2br(
                                            profileEscape(
                                                $doctor['biography']
                                            )
                                        ) ?>

                                    <?php else: ?>

                                        No professional biography
                                        is available.

                                    <?php endif; ?>

                                </p>

                            </div>

                        </div>

                    </section>

                    <!-- Booking steps -->

                    <section class="surface-card">

                        <div class="card-header">

                            <span class="card-eyebrow">
                                Appointment Guide
                            </span>

                            <h2 class="card-title">
                                Schedule Your Visit
                            </h2>

                            <p class="card-description">
                                A simple way to choose
                                your appointment time.
                            </p>

                        </div>

                        <div class="card-body">

                            <div class="guide-step">

                                <span class="step-number">
                                    01
                                </span>

                                <div>

                                    <h3>
                                        Review the Doctor
                                    </h3>

                                    <p>
                                        Check professional information
                                        and the published schedule.
                                    </p>

                                </div>

                            </div>

                            <div class="guide-step">

                                <span class="step-number">
                                    02
                                </span>

                                <div>

                                    <h3>
                                        Choose a Time
                                    </h3>

                                    <p>
                                        Select one of the genuinely
                                        available appointment slots.
                                    </p>

                                </div>

                            </div>

                            <div class="guide-step">

                                <span class="step-number">
                                    03
                                </span>

                                <div>

                                    <h3>
                                        Confirm Your Booking
                                    </h3>

                                    <p>
                                        Submit your selection
                                        and review the result.
                                    </p>

                                </div>

                            </div>

                        </div>

                    </section>

                </div>

                <!-- Doctor availability -->

                <div class="section-header">

                    <span class="section-eyebrow">
                        Doctor Schedule
                    </span>

                    <h2>
                        Published Availability
                    </h2>

                    <p>
                        Review the availability periods
                        provided by this doctor.
                    </p>

                </div>

                <section class="surface-card">

                    <?php if (empty($availability)): ?>

                        <div class="card-body">

                            <div class="empty-state">

                                <h3>
                                    No Availability Listed
                                </h3>

                                <p>
                                    This doctor currently has
                                    no published availability
                                    periods.
                                </p>

                            </div>

                        </div>

                    <?php else: ?>

                        <div class="table-wrap">

                            <table class="schedule-table">

                                <thead>

                                    <tr>
                                        <th>Date</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Slot Duration</th>
                                    </tr>

                                </thead>

                                <tbody>

                                    <?php foreach (
                                        $availability as $entry
                                    ): ?>

                                        <tr>

                                            <td>
                                                <?= profileEscape(
                                                    profileDate(
                                                        $entry[
                                                            'availability_date'
                                                        ]
                                                    )
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= profileEscape(
                                                    profileTime(
                                                        $entry['start_time']
                                                    )
                                                ) ?>
                                            </td>

                                            <td>
                                                <?= profileEscape(
                                                    profileTime(
                                                        $entry['end_time']
                                                    )
                                                ) ?>
                                            </td>

                                            <td>

                                                <span class="duration">
                                                    <?= (int) (
                                                        $entry[
                                                            'slot_duration_minutes'
                                                        ]
                                                    ) ?>
                                                    minutes
                                                </span>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php endif; ?>

                </section>

                <!-- Booking section -->

                <div class="section-header">

                    <span class="section-eyebrow">
                        Appointment Scheduling
                    </span>

                    <h2>
                        Book an Appointment
                    </h2>

                    <p>
                        Choose from the doctor's
                        currently available
                        appointment times.
                    </p>

                </div>

                <section class="surface-card">

                    <div class="card-header booking-header">

                        <div>

                            <span class="card-eyebrow">
                                Available Appointments
                            </span>

                            <h2 class="card-title">
                                Select Your Appointment Time
                            </h2>

                        </div>

                        <span class="slot-count">

                            <?= $totalSlots ?>

                            <?= $totalSlots === 1
                                ? 'available slot'
                                : 'available slots' ?>

                        </span>

                    </div>

                    <div class="card-body">

                        <!-- Date filter -->

                        <form
                            method="GET"
                            action="doctor-details.php"
                            class="date-filter"
                        >

                            <input
                                type="hidden"
                                name="doctor_id"
                                value="<?= (int) $doctorId ?>"
                            >

                            <div class="date-field">

                                <label
                                    for="appointment_date"
                                    class="field-label"
                                >
                                    Select Appointment Date
                                </label>

                                <input
                                    type="date"
                                    id="appointment_date"
                                    name="appointment_date"
                                    class="date-input"
                                    min="<?= profileEscape(
                                        date('Y-m-d')
                                    ) ?>"
                                    value="<?= profileEscape(
                                        $selectedDate
                                    ) ?>"
                                >

                            </div>

                            <button
                                type="submit"
                                class="button-primary"
                            >
                                Show Available Slots
                            </button>

                            <a
                                href="doctor-details.php?doctor_id=<?= (int) $doctorId ?>"
                                class="button-secondary"
                            >
                                Show All Dates
                            </a>

                        </form>

                        <?php if (empty($appointmentSlots)): ?>

                            <div class="empty-state">

                                <?php if (
                                    $selectedDate !== ''
                                ): ?>

                                    <h3>
                                        No Slots on This Date
                                    </h3>

                                    <p>
                                        No appointment times
                                        are available for
                                        <?= profileEscape(
                                            profileDate(
                                                $selectedDate
                                            )
                                        ) ?>.
                                        Try another date or
                                        select Show All Dates.
                                    </p>

                                <?php else: ?>

                                    <h3>
                                        No Appointments Available
                                    </h3>

                                    <p>
                                        No future bookable
                                        appointment times
                                        are currently available
                                        for this doctor.
                                    </p>

                                <?php endif; ?>

                            </div>

                        <?php else: ?>

                            <?php foreach (
                                $slotsByDate as $date => $slots
                            ): ?>

                                <div class="date-group">

                                    <div class="date-heading">

                                        <h3>
                                            <?= profileEscape(
                                                profileDate($date)
                                            ) ?>
                                        </h3>

                                        <span>
                                            <?= count($slots) ?>
                                            <?= count($slots) === 1
                                                ? 'time'
                                                : 'times' ?>
                                            available
                                        </span>

                                    </div>

                                    <div class="slot-grid">

                                        <?php foreach (
                                            $slots as $slot
                                        ): ?>

                                            <article class="slot-card">

                                                <span class="slot-time">

                                                    <?= profileEscape(
                                                        profileTime(
                                                            $slot[
                                                                'start_time'
                                                            ]
                                                        )
                                                    ) ?>

                                                    –

                                                    <?= profileEscape(
                                                        profileTime(
                                                            $slot[
                                                                'end_time'
                                                            ]
                                                        )
                                                    ) ?>

                                                </span>

                                                <span class="slot-caption">
                                                    Available appointment time
                                                </span>

                                                <!-- Preserve booking action -->

                                                <form
                                                    method="POST"
                                                    action="../actions/appointment_book.php"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="doctor_id"
                                                        value="<?= (int) $doctorId ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="availability_id"
                                                        value="<?= (int) $slot['availability_id'] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="appointment_date"
                                                        value="<?= profileEscape(
                                                            $slot[
                                                                'appointment_date'
                                                            ]
                                                        ) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="start_time"
                                                        value="<?= profileEscape(
                                                            $slot[
                                                                'start_time'
                                                            ]
                                                        ) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= profileEscape(
                                                            $bookingToken
                                                        ) ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="book-button"
                                                    >
                                                        Book This Time
                                                    </button>

                                                </form>

                                            </article>

                                        <?php endforeach; ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                            <p class="booking-note">
                                Only currently available slots
                                are displayed. The server checks
                                availability again when you
                                submit a booking.
                            </p>

                        <?php endif; ?>

                    </div>

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