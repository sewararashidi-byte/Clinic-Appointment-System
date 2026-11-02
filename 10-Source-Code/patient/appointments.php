<?php

/**
 * Patient Appointment Management
 * Clinic Appointment System
 *
 * Displays real patient appointments and preserves
 * existing cancellation and rescheduling actions.
 */

require_once __DIR__ .
    '/../includes/patient_appointments_data.php';

require_once __DIR__ . '/../config/timezone.php';

// Escape displayed database and session values.
function escapeAppointmentValue($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Format appointment dates.
function formatPatientAppointmentDate($value): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date('l, F j, Y', $timestamp);
}

// Format appointment times.
function formatPatientAppointmentTime($value): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date('g:i A', $timestamp);
}

// Application timezone.
$clinicTimezone = new DateTimeZone('Asia/Kabul');

$now = new DateTimeImmutable(
    'now',
    $clinicTimezone
);

// One-time action messages.
$actionSuccess =
    $_SESSION['appointment_action_success'] ?? '';

$actionError =
    $_SESSION['appointment_action_error'] ?? '';

unset(
    $_SESSION['appointment_action_success'],
    $_SESSION['appointment_action_error']
);

// The existing data include prepares this token.
$appointmentToken =
    $_SESSION['appointment_action_csrf_token'] ?? '';

$fullName = trim(
    (string) ($_SESSION['full_name'] ?? 'Patient')
);

if ($fullName === '') {
    $fullName = 'Patient';
}

// Normalize appointment data.
$patientAppointments = is_array(
    $patientAppointments ?? null
)
    ? $patientAppointments
    : [];

$appointmentsError = (string) (
    $appointmentsError ?? ''
);

// Organize real appointments into categories.
$appointmentGroups = [
    'Upcoming' => [],
    'Previous' => [],
    'Cancelled' => []
];

foreach ($patientAppointments as $appointment) {

    $status = strtolower(
        trim((string) ($appointment['status'] ?? ''))
    );

    $appointmentStart = new DateTimeImmutable(
        $appointment['appointment_date'] . ' ' .
        $appointment['start_time'],
        $clinicTimezone
    );

    if ($status === 'cancelled') {

        $group = 'Cancelled';

    } elseif ($appointmentStart > $now) {

        $group = 'Upcoming';

    } else {

        $group = 'Previous';

    }

    // Final Product Owner rule:
    // Patients may cancel at least 24 hours
    // before their appointment.
    $cancellationDeadline =
        $appointmentStart->modify('-24 hours');

    // Existing rescheduling rule:
    // Rescheduling is permitted at least
    // two hours before the appointment.
    $reschedulingDeadline =
        $appointmentStart->modify('-2 hours');

    $appointment['_can_cancel'] = (
        $group === 'Upcoming' &&
        $status === 'confirmed' &&
        $now <= $cancellationDeadline
    );

    $appointment['_can_reschedule'] = (
        $group === 'Upcoming' &&
        $status === 'confirmed' &&
        $now <= $reschedulingDeadline
    );

    $appointmentGroups[$group][] = $appointment;
}

// Real summary counts.
$upcomingCount =
    count($appointmentGroups['Upcoming']);

$previousCount =
    count($appointmentGroups['Previous']);

$cancelledCount =
    count($appointmentGroups['Cancelled']);

$totalCount =
    $upcomingCount +
    $previousCount +
    $cancelledCount;

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
        My Appointments | Clinic Appointment System
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
            --navy-deep: #0c293d;
            --navy-soft: #173e61;

            --teal: #087d85;
            --teal-dark: #08656d;
            --teal-pale: #e8f6f3;

            --ink: #17374d;
            --muted: #718697;

            --background: #f4f8fa;
            --white: #ffffff;
            --border: #e2ebef;

            --shadow:
                0 10px 32px rgba(20, 64, 86, 0.045);
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

            font-family:
                "DM Sans",
                "Segoe UI",
                sans-serif;

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
        button:focus-visible {
            outline: 3px solid rgba(8, 125, 133, .4);
            outline-offset: 3px;
        }

        svg {
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* Main application layout */

        .app-layout {
            display: grid;

            grid-template-columns:
                260px minmax(0, 1fr);

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

            color: white;

            background:
                radial-gradient(
                    circle at 10% 95%,
                    rgba(54, 180, 170, .14),
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

            min-height: 46px;
            padding: 11px 15px;

            border: 1px solid transparent;
            border-radius: 11px;

            color: #c0d1da;

            font-size: .89rem;
            font-weight: 600;

            transition:
                background .2s,
                color .2s;
        }

        .nav-link:hover {
            background: rgba(255,255,255,.075);
            color: white;
        }

        .nav-link.active {
            background: rgba(113,209,195,.14);

            border-color:
                rgba(152,226,215,.12);

            color: white;
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

            min-height: 88px;
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

            font-size: 1.07rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .account-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;

            max-width: 250px;

            padding: 7px 14px;

            border: 1px solid var(--border);
            border-radius: 30px;

            background: #ffffff;

            font-size: .83rem;
            font-weight: 700;
        }

        .account-link:hover {
            background: #f5faf9;
        }

        .account-link svg {
            width: 18px;
            height: 18px;

            color: var(--teal);
        }

        .account-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Content */

        .page-content {
            max-width: 1450px;

            margin: 0 auto;
            padding: 38px 42px 55px;
        }

        /* Page hero */

        .page-hero {
            position: relative;
            isolation: isolate;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 28px;

            padding: 39px 42px;
            min-height: 218px;

            overflow: hidden;

            border-radius: 23px;

            color: white;

            background:
                radial-gradient(
                    circle at 92% 12%,
                    rgba(79,200,184,.23),
                    transparent 40%
                ),
                linear-gradient(
                    115deg,
                    #102d46,
                    #17455a 68%,
                    #0d5960
                );
        }

        .page-hero::after {
            content: "";
            position: absolute;
            z-index: -1;

            width: 350px;
            height: 350px;

            right: -180px;
            bottom: -270px;

            border: 1px solid rgba(178,236,227,.23);
            border-radius: 50%;

            box-shadow:
                0 0 0 47px rgba(178,236,227,.035);

            pointer-events: none;
        }

        .hero-eyebrow {
            display: block;

            margin-bottom: 15px;

            color: #a4e8dc;

            font-size: .71rem;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .page-hero h1 {
            margin: 0 0 15px;

            font-family: "Manrope", sans-serif;

            font-size: clamp(1.9rem, 3vw, 2.8rem);
            font-weight: 800;
            letter-spacing: -.06em;
            line-height: 1.2;
        }

        .page-hero p {
            max-width: 550px;

            margin: 0;

            color: #cde2e8;

            font-size: .95rem;
            line-height: 1.8;
        }

        .hero-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            min-height: 48px;
            padding: 12px 19px;

            border-radius: 11px;

            background: #b3eee1;
            color: #0b4450;

            font-size: .87rem;
            font-weight: 800;
            white-space: nowrap;

            transition: transform .2s, background .2s;
        }

        .hero-action:hover {
            transform: translateY(-2px);

            background: #ccf7ed;
            color: #093940;
        }

        .hero-action svg {
            width: 18px;
            height: 18px;
        }

        /* Summary cards */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0,1fr));
            gap: 19px;

            margin-top: 24px;
        }

        .summary-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;

            padding: 23px 25px;

            background: white;

            border: 1px solid var(--border);
            border-radius: 17px;

            box-shadow: var(--shadow);
        }

        .summary-label {
            display: block;

            margin-bottom: 8px;

            color: var(--muted);

            font-size: .85rem;
            font-weight: 600;
        }

        .summary-value {
            display: block;

            color: var(--ink);

            font-family: "Manrope", sans-serif;
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -.055em;
            line-height: 1.15;
        }

        .summary-icon {
            display: grid;
            place-items: center;

            width: 47px;
            height: 47px;
            flex-shrink: 0;

            border-radius: 14px;

            color: var(--teal);
            background: #e6f5f1;
        }

        .summary-icon.blue {
            color: #35689a;
            background: #e9f1fa;
        }

        .summary-icon.red {
            color: #ad5e66;
            background: #fbebed;
        }

        /* Feedback */

        .notice {
            margin-top: 24px;
            padding: 17px 20px;

            border: 1px solid;
            border-radius: 13px;

            font-size: .9rem;
            line-height: 1.7;
        }

        .notice strong {
            display: block;
            margin-bottom: 5px;
        }

        .notice.success {
            background: #e9f8ef;
            border-color: #c9ead6;
            color: #256e50;
        }

        .notice.error {
            background: #fff2f1;
            border-color: #efd0cf;
            color: #a13a42;
        }

        /* Section heading */

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-wrap: wrap;
            gap: 15px;

            margin: 36px 0 20px;
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

            font-size: .87rem;
            line-height: 1.7;
        }

        /* Interactive filters */

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 9px;

            margin-bottom: 22px;
            padding: 8px;

            border: 1px solid var(--border);
            border-radius: 14px;

            background: white;

            width: fit-content;
            max-width: 100%;
        }

        .filter-button {
            display: inline-flex;
            align-items: center;
            gap: 9px;

            min-height: 41px;

            padding: 10px 15px;

            border: none;
            border-radius: 10px;

            background: transparent;
            color: #718697;

            font-size: .85rem;
            font-weight: 700;

            transition: background .2s, color .2s;
        }

        .filter-button:hover {
            background: #f2f7f8;
            color: var(--ink);
        }

        .filter-button[aria-pressed="true"] {
            background: var(--navy);
            color: white;
        }

        .filter-number {
            display: inline-grid;
            place-items: center;

            min-width: 22px;
            height: 22px;

            padding: 0 5px;

            border-radius: 7px;

            background: #edf3f5;

            color: #456377;

            font-size: .72rem;
            font-weight: 800;
        }

        .filter-button[aria-pressed="true"]
        .filter-number {
            background: rgba(255,255,255,.14);
            color: white;
        }

        /* Appointment categories */

        .appointment-group {
            margin-bottom: 31px;
        }

        .appointment-group[hidden] {
            display: none !important;
        }

        .group-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;

            margin-bottom: 17px;
        }

        .group-title {
            display: flex;
            align-items: center;
            gap: 11px;

            margin: 0;

            color: var(--ink);

            font-family: "Manrope", sans-serif;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .group-indicator {
            width: 10px;
            height: 10px;

            border-radius: 50%;

            background: #128f80;
        }

        .group-indicator.previous {
            background: #708da9;
        }

        .group-indicator.cancelled {
            background: #c2737a;
        }

        .group-count {
            color: var(--muted);
            font-size: .82rem;
            font-weight: 600;
        }

        /* Appointment cards */

        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 19px;
        }

        .appointment-card {
            min-width: 0;
            overflow: hidden;

            background: white;

            border: 1px solid var(--border);
            border-radius: 19px;

            box-shadow: var(--shadow);

            transition:
                border-color .2s,
                transform .2s,
                box-shadow .2s;
        }

        .appointment-card:hover {
            transform: translateY(-2px);

            border-color: #c5e0dc;

            box-shadow:
                0 16px 38px rgba(20,64,86,.075);
        }

        .appointment-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 14px;

            padding: 23px 24px 19px;

            border-bottom: 1px solid var(--border);
        }

        .reference-label {
            display: block;

            margin-bottom: 7px;

            color: #8296a3;

            font-size: .69rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .reference-value {
            display: block;

            color: var(--ink);

            font-family: "Manrope", sans-serif;
            font-size: 1rem;
            font-weight: 800;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;

            padding: 8px 11px;

            border-radius: 9px;

            font-size: .75rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .status-badge.upcoming {
            background: #e6f7ee;
            color: #28744f;
        }

        .status-badge.previous {
            background: #edf3f8;
            color: #58728b;
        }

        .status-badge.cancelled {
            background: #fbecee;
            color: #a85a62;
        }

        .appointment-card-body {
            padding: 24px;
        }

        .doctor-label {
            display: block;

            margin-bottom: 6px;

            color: #8396a4;

            font-size: .71rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .doctor-name {
            margin: 0 0 22px;

            color: var(--ink);

            font-family: "Manrope", sans-serif;
            font-size: 1.13rem;
            font-weight: 800;
            letter-spacing: -.04em;

            overflow-wrap: anywhere;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 15px;

            padding-top: 20px;

            border-top: 1px solid var(--border);
        }

        .detail-label {
            display: block;

            margin-bottom: 7px;

            color: #8b9da9;

            font-size: .72rem;
            font-weight: 700;
        }

        .detail-value {
            display: block;

            color: #35556a;

            font-size: .86rem;
            font-weight: 700;
            line-height: 1.6;
        }

        .detail-value.time {
            font-family: "Manrope", sans-serif;
            font-size: .85rem;
        }

        .appointment-card-footer {
            padding: 19px 24px 23px;

            background: #fcfdfd;
            border-top: 1px solid var(--border);
        }

        .action-row {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .action-row form {
            margin: 0;
        }

        .action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            min-height: 42px;

            padding: 11px 14px;

            border: 1px solid transparent;
            border-radius: 10px;

            font-size: .82rem;
            font-weight: 800;
            line-height: 1.2;

            transition:
                background .2s,
                border-color .2s;
        }

        .action-button.primary {
            background: var(--teal);
            color: white;
        }

        .action-button.primary:hover {
            background: var(--teal-dark);
        }

        .action-button.danger {
            background: white;
            border-color: #e9c9cc;
            color: #a24e59;
        }

        .action-button.danger:hover {
            background: #fff1f2;
            border-color: #dbadb2;
        }

        .action-button svg {
            width: 16px;
            height: 16px;
        }

        .action-note {
            margin: 13px 0 0;

            color: #8598a6;

            font-size: .79rem;
            line-height: 1.7;
        }

        .no-action {
            color: #8497a3;

            font-size: .82rem;
            line-height: 1.6;
        }

        /* Empty states */

        .empty-state {
            display: flex;
            align-items: flex-start;
            gap: 17px;

            padding: 25px;

            border: 1px solid var(--border);
            border-radius: 17px;

            background: white;
        }

        .empty-icon {
            display: grid;
            place-items: center;

            width: 48px;
            height: 48px;
            flex-shrink: 0;

            border-radius: 13px;

            background: #e8f6f3;
            color: var(--teal);
        }

        .empty-state h3 {
            margin: 0 0 8px;

            font-family: "Manrope", sans-serif;
            font-size: .96rem;
            font-weight: 800;
        }

        .empty-state p {
            margin: 0;

            color: var(--muted);
            font-size: .86rem;
            line-height: 1.8;
        }

        .empty-state a {
            display: inline-block;
            margin-top: 14px;
            color: var(--teal);
            font-size: .85rem;
            font-weight: 800;
        }

        .empty-state a:hover {
            text-decoration: underline;
        }

        /* Booking rules */

        .rules-panel {
            margin-top: 33px;
            padding: 26px 29px;

            background: #eef6f7;

            border: 1px solid #dcecef;
            border-radius: 18px;
        }

        .rules-panel h2 {
            margin: 0 0 12px;

            color: #21485b;

            font-family: "Manrope", sans-serif;
            font-size: .99rem;
            font-weight: 800;
        }

        .rules-panel p {
            margin: 0;

            color: #567384;
            font-size: .86rem;
            line-height: 1.9;
        }

        /* Footer */

        .footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;

            padding-top: 36px;

            color: #8b9eaa;
            font-size: .77rem;
        }

        /* Responsive layout */

        @media (max-width: 1150px) {
            .app-layout {
                grid-template-columns:
                    220px minmax(0,1fr);
            }

            .page-content {
                padding: 28px;
            }

            .topbar {
                padding: 18px 28px;
            }

            .appointments-grid {
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

            .summary-grid {
                grid-template-columns:
                    repeat(3,minmax(0,1fr));
            }

            .page-hero {
                padding: 32px;
            }
        }

        @media (max-width: 650px) {
            .sidebar {
                padding: 15px 13px;
            }

            .nav-link {
                padding: 9px 10px;
                min-height: 39px;
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
                max-width: 110px;
            }

            .page-content {
                padding: 19px 13px 35px;
            }

            .page-hero {
                display: block;
                padding: 28px 23px;
            }

            .page-hero h1 {
                font-size: 2rem;
            }

            .hero-action {
                width: 100%;
                margin-top: 23px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .summary-card {
                padding: 18px 21px;
            }

            .summary-value {
                font-size: 1.5rem;
            }

            .filter-bar {
                width: 100%;
            }

            .filter-button {
                flex: 1 1 auto;
                justify-content: center;
            }

            .appointment-card-top,
            .appointment-card-body,
            .appointment-card-footer {
                padding-left: 20px;
                padding-right: 20px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            .action-row {
                flex-direction: column;
            }

            .action-row form,
            .action-button {
                width: 100%;
            }

            .rules-panel {
                padding: 23px 21px;
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
                    <rect
                        x="3" y="5"
                        width="18" height="16" rx="2"
                    />
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

                <div class="topbar-title">
                    Appointment Management
                </div>

            </div>

            <a
                href="profile.php"
                class="account-link"
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

                <span class="account-name">
                    <?= escapeAppointmentValue($fullName) ?>
                </span>
            </a>

        </header>

        <main class="page-content">

            <!-- Welcome section -->

            <section class="page-hero">

                <div>

                    <span class="hero-eyebrow">
                        Your Appointments
                    </span>

                    <h1>
                        Your schedule, organized.
                    </h1>

                    <p>
                        Review upcoming visits, manage
                        eligible appointments, and access
                        your previous appointment history
                        from one place.
                    </p>

                </div>

                <a
                    href="doctor-search.php"
                    class="hero-action"
                >

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <circle cx="11" cy="11" r="7"/>
                        <path d="m20 20-4-4"/>
                    </svg>

                    Book an Appointment

                </a>

            </section>

            <!-- Real appointment summaries -->

            <section
                class="summary-grid"
                aria-label="Appointment summary"
            >

                <div class="summary-card">

                    <div>

                        <span class="summary-label">
                            Upcoming
                        </span>

                        <strong class="summary-value">
                            <?= $upcomingCount ?>
                        </strong>

                    </div>

                    <span class="summary-icon">

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

                    </span>

                </div>

                <div class="summary-card">

                    <div>

                        <span class="summary-label">
                            Previous
                        </span>

                        <strong class="summary-value">
                            <?= $previousCount ?>
                        </strong>

                    </div>

                    <span class="summary-icon blue">

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
                            <path d="M12 7v5l3 2"/>
                        </svg>

                    </span>

                </div>

                <div class="summary-card">

                    <div>

                        <span class="summary-label">
                            Cancelled
                        </span>

                        <strong class="summary-value">
                            <?= $cancelledCount ?>
                        </strong>

                    </div>

                    <span class="summary-icon red">

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
                            <path d="m9 9 6 6M15 9l-6 6"/>
                        </svg>

                    </span>

                </div>

            </section>

            <!-- One-time action feedback -->

            <?php if (
                is_string($actionSuccess) &&
                $actionSuccess !== ''
            ): ?>

                <div
                    class="notice success"
                    role="status"
                >
                    <strong>
                        Appointment Updated
                    </strong>

                    <?= escapeAppointmentValue(
                        $actionSuccess
                    ) ?>
                </div>

            <?php endif; ?>

            <?php if (
                is_string($actionError) &&
                $actionError !== ''
            ): ?>

                <div
                    class="notice error"
                    role="alert"
                >
                    <strong>
                        Appointment Action Unsuccessful
                    </strong>

                    <?= escapeAppointmentValue(
                        $actionError
                    ) ?>
                </div>

            <?php endif; ?>

            <!-- Appointment history -->

            <div class="section-heading">

                <div>

                    <span class="section-eyebrow">
                        Appointment Records
                    </span>

                    <h2 class="section-title">
                        My Appointment History
                    </h2>

                    <p class="section-description">
                        Browse your appointments by category.
                    </p>

                </div>

            </div>

            <?php if ($appointmentsError !== ''): ?>

                <div
                    class="notice error"
                    role="alert"
                >
                    <strong>
                        Unable to Load Appointments
                    </strong>

                    <?= escapeAppointmentValue(
                        $appointmentsError
                    ) ?>
                </div>

            <?php elseif (empty($patientAppointments)): ?>

                <section class="empty-state">

                    <span
                        class="empty-icon"
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
                        </svg>
                    </span>

                    <div>

                        <h3>
                            No Appointments Yet
                        </h3>

                        <p>
                            You have not booked any
                            appointments. Find a doctor
                            to view available times
                            and schedule your first visit.
                        </p>

                        <a href="doctor-search.php">
                            Find a Doctor &rarr;
                        </a>

                    </div>

                </section>

            <?php else: ?>

                <!-- Interactive category filters -->

                <div
                    class="filter-bar"
                    role="group"
                    aria-label="Filter appointments"
                >

                    <button
                        type="button"
                        class="filter-button"
                        data-ap-filter="All"
                        aria-pressed="true"
                    >
                        All

                        <span class="filter-number">
                            <?= $totalCount ?>
                        </span>
                    </button>

                    <button
                        type="button"
                        class="filter-button"
                        data-ap-filter="Upcoming"
                        aria-pressed="false"
                    >
                        Upcoming

                        <span class="filter-number">
                            <?= $upcomingCount ?>
                        </span>
                    </button>

                    <button
                        type="button"
                        class="filter-button"
                        data-ap-filter="Previous"
                        aria-pressed="false"
                    >
                        Previous

                        <span class="filter-number">
                            <?= $previousCount ?>
                        </span>
                    </button>

                    <button
                        type="button"
                        class="filter-button"
                        data-ap-filter="Cancelled"
                        aria-pressed="false"
                    >
                        Cancelled

                        <span class="filter-number">
                            <?= $cancelledCount ?>
                        </span>
                    </button>

                </div>

                <?php foreach (
                    $appointmentGroups as
                    $groupName => $appointments
                ): ?>

                    <section
                        class="appointment-group"
                        data-ap-group="<?= escapeAppointmentValue(
                            $groupName
                        ) ?>"
                    >

                        <div class="group-heading">

                            <h3 class="group-title">

                                <span
                                    class="group-indicator <?= strtolower(
                                        escapeAppointmentValue($groupName)
                                    ) ?>"
                                ></span>

                                <?= escapeAppointmentValue(
                                    $groupName
                                ) ?>

                                Appointments

                            </h3>

                            <span class="group-count">
                                <?= count($appointments) ?>
                                <?= count($appointments) === 1
                                    ? 'appointment'
                                    : 'appointments' ?>
                            </span>

                        </div>

                        <?php if (empty($appointments)): ?>

                            <div class="empty-state">

                                <span
                                    class="empty-icon"
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
                                    </svg>
                                </span>

                                <div>

                                    <h3>
                                        No <?= escapeAppointmentValue(
                                            strtolower($groupName)
                                        ) ?> appointments
                                    </h3>

                                    <p>
                                        There are currently no
                                        appointments in this category.
                                    </p>

                                </div>

                            </div>

                        <?php else: ?>

                            <div class="appointments-grid">

                                <?php foreach (
                                    $appointments as $appointment
                                ): ?>

                                    <?php

                                    $appointmentId = (int) (
                                        $appointment[
                                            'appointment_id'
                                        ] ?? 0
                                    );

                                    $doctorName = (string) (
                                        $appointment[
                                            'doctor_name'
                                        ] ?? 'Doctor'
                                    );

                                    $status = strtolower(
                                        (string) (
                                            $appointment['status'] ?? ''
                                        )
                                    );

                                    $canCancel = (
                                        $appointment[
                                            '_can_cancel'
                                        ] ?? false
                                    );

                                    $canReschedule = (
                                        $appointment[
                                            '_can_reschedule'
                                        ] ?? false
                                    );

                                    ?>

                                    <article class="appointment-card">

                                        <div class="appointment-card-top">

                                            <div>

                                                <span class="reference-label">
                                                    Appointment Reference
                                                </span>

                                                <span class="reference-value">
                                                    #<?= $appointmentId ?>
                                                </span>

                                            </div>

                                            <span
                                                class="status-badge <?= strtolower(
                                                    escapeAppointmentValue(
                                                        $groupName
                                                    )
                                                ) ?>"
                                            >

                                                <?php if (
                                                    $groupName === 'Upcoming'
                                                ): ?>

                                                    <?= escapeAppointmentValue(
                                                        ucfirst($status)
                                                    ) ?>

                                                <?php else: ?>

                                                    <?= escapeAppointmentValue(
                                                        $groupName
                                                    ) ?>

                                                <?php endif; ?>

                                            </span>

                                        </div>

                                        <div class="appointment-card-body">

                                            <span class="doctor-label">
                                                Doctor
                                            </span>

                                            <h4 class="doctor-name">
                                                <?= escapeAppointmentValue(
                                                    $doctorName
                                                ) ?>
                                            </h4>

                                            <div class="detail-grid">

                                                <div>

                                                    <span class="detail-label">
                                                        Appointment Date
                                                    </span>

                                                    <span class="detail-value">
                                                        <?= escapeAppointmentValue(
                                                            formatPatientAppointmentDate(
                                                                $appointment[
                                                                    'appointment_date'
                                                                ]
                                                            )
                                                        ) ?>
                                                    </span>

                                                </div>

                                                <div>

                                                    <span class="detail-label">
                                                        Appointment Time
                                                    </span>

                                                    <span class="detail-value time">

                                                        <?= escapeAppointmentValue(
                                                            formatPatientAppointmentTime(
                                                                $appointment[
                                                                    'start_time'
                                                                ]
                                                            )
                                                        ) ?>

                                                        –

                                                        <?= escapeAppointmentValue(
                                                            formatPatientAppointmentTime(
                                                                $appointment[
                                                                    'end_time'
                                                                ]
                                                            )
                                                        ) ?>

                                                    </span>

                                                </div>

                                            </div>

                                        </div>

                                        <div class="appointment-card-footer">

                                            <?php if (
                                                $groupName === 'Upcoming' &&
                                                ($canCancel || $canReschedule)
                                            ): ?>

                                                <div class="action-row">

                                                    <?php if ($canReschedule): ?>

                                                        <a
                                                            href="reschedule.php?appointment_id=<?= $appointmentId ?>"
                                                            class="action-button primary"
                                                        >
                                                            Reschedule
                                                        </a>

                                                    <?php endif; ?>

                                                    <?php if ($canCancel): ?>

                                                        <form
                                                            method="POST"
                                                            action="../actions/appointment_cancel.php"
                                                            onsubmit="return confirm('Are you sure you want to cancel this appointment?');"
                                                        >

                                                            <input
                                                                type="hidden"
                                                                name="appointment_id"
                                                                value="<?= $appointmentId ?>"
                                                            >

                                                            <input
                                                                type="hidden"
                                                                name="csrf_token"
                                                                value="<?= escapeAppointmentValue(
                                                                    $appointmentToken
                                                                ) ?>"
                                                            >

                                                            <button
                                                                type="submit"
                                                                class="action-button danger"
                                                            >
                                                                Cancel Appointment
                                                            </button>

                                                        </form>

                                                    <?php endif; ?>

                                                </div>

                                                <?php if (
                                                    !$canCancel &&
                                                    $canReschedule
                                                ): ?>

                                                    <p class="action-note">
                                                        Online cancellation
                                                        is no longer available
                                                        within 24 hours
                                                        of the appointment.
                                                        Contact the clinic
                                                        if cancellation
                                                        is necessary.
                                                    </p>

                                                <?php endif; ?>

                                            <?php elseif (
                                                $groupName === 'Upcoming'
                                            ): ?>

                                                <span class="no-action">
                                                    No online changes are
                                                    available for this appointment.
                                                    Contact the clinic if
                                                    assistance is needed.
                                                </span>

                                            <?php else: ?>

                                                <span class="no-action">
                                                    This appointment is
                                                    part of your history.
                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    </article>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

                    </section>

                <?php endforeach; ?>

            <?php endif; ?>

            <!-- Appointment policy information -->

            <section class="rules-panel">

                <h2>
                    Appointment Changes
                </h2>

                <p>
                    Patients can cancel a confirmed
                    appointment at least 24 hours
                    before its scheduled start.

                    Eligible appointments may be
                    rescheduled to another available
                    time with the same doctor at
                    least two hours before the
                    original appointment.

                    For later cancellation requests,
                    contact the clinic for assistance.
                </p>

            </section>

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
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const filterButtons =
            document.querySelectorAll(
                '[data-ap-filter]'
            );

        const groups =
            document.querySelectorAll(
                '[data-ap-group]'
            );

        filterButtons.forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    const selected =
                        button.dataset.apFilter;

                    filterButtons.forEach(
                        function (otherButton) {

                            otherButton.setAttribute(
                                'aria-pressed',
                                otherButton === button
                                    ? 'true'
                                    : 'false'
                            );

                        }
                    );

                    groups.forEach(
                        function (group) {

                            const groupName =
                                group.dataset.apGroup;

                            group.hidden = (
                                selected !== 'All' &&
                                selected !== groupName
                            );

                        }
                    );

                }
            );

        });

    }
);
</script>

</body>
</html>