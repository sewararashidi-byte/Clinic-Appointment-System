<?php

/**
 * Doctor Appointment Schedule
 * Clinic Appointment System
 *
 * Doctor-facing appointment list.
 * Existing data source and authorization preserved.
 */

require_once __DIR__ . '/../includes/doctor_appointments_data.php';
require_once __DIR__ . '/../config/timezone.php';

function doctorAppointmentEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function doctorAppointmentDate($value, string $format = 'l, F j, Y'): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date($format, $timestamp);
}

function doctorAppointmentTime($value): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date('g:i A', $timestamp);
}

// Preserve the results supplied by the existing data loader.
$doctorAppointments = is_array($doctorAppointments ?? null)
    ? $doctorAppointments
    : [];

$appointmentsError = (string) ($appointmentsError ?? '');

$doctorName = trim(
    (string) ($_SESSION['full_name'] ?? 'Doctor')
);

if ($doctorName === '') {
    $doctorName = 'Doctor';
}

// Compare appointment times using the clinic's timezone.
$clinicTimezone = new DateTimeZone('Asia/Kabul');

$now = new DateTimeImmutable(
    'now',
    $clinicTimezone
);

$currentDateTime = $now->format('Y-m-d H:i:s');

// Group appointments without changing the database.
$appointmentGroups = [
    'Upcoming' => [],
    'Previous' => []
];

foreach ($doctorAppointments as $appointment) {

    if (!is_array($appointment)) {
        continue;
    }

    $startDateTime =
        (string) ($appointment['appointment_date'] ?? '') .
        ' ' .
        (string) ($appointment['start_time'] ?? '');

    if ($startDateTime > $currentDateTime) {
        $appointmentGroups['Upcoming'][] = $appointment;
    } else {
        $appointmentGroups['Previous'][] = $appointment;
    }
}

// Nearest future visit first.
usort(
    $appointmentGroups['Upcoming'],
    function ($a, $b): int {
        return strcmp(
            $a['appointment_date'] . ' ' . $a['start_time'],
            $b['appointment_date'] . ' ' . $b['start_time']
        );
    }
);

// Most recent previous visit first.
usort(
    $appointmentGroups['Previous'],
    function ($a, $b): int {
        return strcmp(
            $b['appointment_date'] . ' ' . $b['start_time'],
            $a['appointment_date'] . ' ' . $a['start_time']
        );
    }
);

$upcomingCount = count($appointmentGroups['Upcoming']);
$previousCount = count($appointmentGroups['Previous']);
$totalCount = $upcomingCount + $previousCount;

$nextAppointment = $appointmentGroups['Upcoming'][0] ?? null;

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
            --teal-light: #e8f6f3;
            --ink: #17384d;
            --muted: #708595;
            --border: #e0eaee;
            --background: #f3f8fa;
            --surface: #ffffff;
            --shadow: 0 12px 34px rgba(20,64,86,.05);
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

        a {
            text-decoration: none;
            color: inherit;
        }

        button {
            font: inherit;
            cursor: pointer;
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

        /* Overall layout */

        .app-shell {
            display: grid;
            grid-template-columns: 268px minmax(0,1fr);
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

            padding: 29px 18px 22px;
            color: white;

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
            flex-shrink: 0;
            border-radius: 50%;
            background: #92e6d7;
        }

        /* Top navigation */

        .main-area {
            min-width: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .date-label {
            color: #8397a3;
            font-size: .83rem;
            white-space: nowrap;
        }

        .account-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;

            border: 1px solid var(--border);
            border-radius: 30px;
            background: white;

            font-size: .83rem;
            font-weight: 700;
        }

        .account-link:hover {
            background: #f4faf9;
        }

        .account-link svg {
            width: 17px;
            height: 17px;
            color: var(--teal);
        }

        /* Content */

        .content {
            max-width: 1500px;
            margin: 0 auto;
            padding: 37px 43px 55px;
        }

        /* Welcome hero */

        .hero {
            position: relative;
            isolation: isolate;
            overflow: hidden;

            display: grid;
            grid-template-columns: minmax(0,1.3fr)
                                   minmax(265px,.7fr);
            gap: 30px;
            align-items: center;

            padding: 41px 43px;
            min-height: 288px;

            border-radius: 23px;
            color: white;

            background:
                radial-gradient(
                    circle at 90% 12%,
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

        .hero::before {
            content: "";
            position: absolute;
            z-index: -1;
            width: 390px;
            height: 390px;
            right: -205px;
            bottom: -295px;

            border: 1px solid rgba(179,242,230,.2);
            border-radius: 50%;
            box-shadow:
                0 0 0 48px rgba(179,242,230,.03);
        }

        .hero-eyebrow {
            display: block;
            margin-bottom: 17px;
            color: #a7ebdf;
            font-size: .71rem;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 0 0 16px;
            font-family: "Manrope", sans-serif;
            font-size: clamp(2rem,3vw,2.8rem);
            font-weight: 800;
            letter-spacing: -.06em;
            line-height: 1.22;
        }

        .hero-description {
            max-width: 610px;
            margin: 0;
            color: #cde2e7;
            font-size: .95rem;
            line-height: 1.85;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 25px;
        }

        .hero-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 46px;
            padding: 12px 17px;
            border-radius: 10px;
            font-size: .85rem;
            font-weight: 800;
            transition: transform .2s, background .2s;
        }

        .hero-button.primary {
            background: #b2eee1;
            color: #0d444c;
        }

        .hero-button.primary:hover {
            background: #cbf8ed;
            transform: translateY(-2px);
        }

        .hero-button.secondary {
            color: white;
            border: 1px solid rgba(228,251,246,.28);
            background: rgba(255,255,255,.055);
        }

        .hero-button.secondary:hover {
            background: rgba(255,255,255,.14);
        }

        .hero-button svg {
            width: 17px;
            height: 17px;
        }

        /* Next scheduled visit panel */

        .next-visit {
            position: relative;
            z-index: 1;
            padding: 23px;

            border: 1px solid rgba(228,249,246,.22);
            border-radius: 18px;
            background: rgba(255,255,255,.09);
            backdrop-filter: blur(10px);
        }

        .next-label {
            display: block;
            margin-bottom: 17px;
            color: #b7e8e1;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .next-date {
            font-family: "Manrope", sans-serif;
            font-size: 1.3rem;
            font-weight: 800;
            letter-spacing: -.045em;
            line-height: 1.4;
        }

        .next-time {
            margin-top: 8px;
            color: #d7f0ec;
            font-size: .9rem;
            font-weight: 700;
        }

        .next-divider {
            height: 1px;
            margin: 17px 0;
            background: rgba(255,255,255,.19);
        }

        .next-patient-label {
            display: block;
            margin-bottom: 6px;
            color: #b9d9df;
            font-size: .74rem;
        }

        .next-patient {
            font-family: "Manrope", sans-serif;
            font-size: .94rem;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .next-empty {
            margin: 0;
            color: #d5e9ea;
            font-size: .87rem;
            line-height: 1.75;
        }

        /* Summary cards */

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3,minmax(0,1fr));
            gap: 19px;
            margin-top: 24px;
        }

        .summary-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;

            padding: 23px 25px;
            border: 1px solid var(--border);
            border-radius: 17px;

            background: white;
            box-shadow: var(--shadow);
        }

        .summary-label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: .84rem;
            font-weight: 600;
        }

        .summary-value {
            font-family: "Manrope", sans-serif;
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -.055em;
            line-height: 1.1;
        }

        .summary-icon {
            display: grid;
            place-items: center;
            width: 47px;
            height: 47px;
            flex-shrink: 0;
            border-radius: 14px;

            background: var(--teal-light);
            color: var(--teal);
        }

        .summary-icon.blue {
            background: #eaf2fa;
            color: #356b96;
        }

        .summary-icon.slate {
            background: #edf2f6;
            color: #648094;
        }

        /* Section titles */

        .section-heading {
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
            font-family: "Manrope", sans-serif;
            font-size: 1.38rem;
            font-weight: 800;
            letter-spacing: -.05em;
        }

        .section-description {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: .87rem;
            line-height: 1.75;
        }

        /* Interactive category filters */

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;

            width: fit-content;
            max-width: 100%;
            margin-bottom: 25px;
            padding: 8px;

            border: 1px solid var(--border);
            border-radius: 14px;
            background: white;
        }

        .filter-button {
            display: inline-flex;
            align-items: center;
            gap: 9px;

            min-height: 42px;
            padding: 10px 16px;

            border: 0;
            border-radius: 10px;

            color: #718697;
            background: transparent;
            font-size: .84rem;
            font-weight: 700;
        }

        .filter-button:hover {
            color: var(--ink);
            background: #f2f7f8;
        }

        .filter-button[aria-pressed="true"] {
            background: var(--navy);
            color: white;
        }

        .filter-count {
            display: grid;
            place-items: center;
            min-width: 23px;
            height: 23px;
            padding: 0 5px;
            border-radius: 7px;

            background: #edf3f5;
            color: #456377;
            font-size: .73rem;
            font-weight: 800;
        }

        .filter-button[aria-pressed="true"] .filter-count {
            background: rgba(255,255,255,.15);
            color: white;
        }

        /* Appointment sections */

        .appointment-group {
            margin-bottom: 32px;
        }

        .appointment-group[hidden] {
            display: none !important;
        }

        .group-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 17px;
        }

        .group-name {
            display: flex;
            align-items: center;
            gap: 11px;
            margin: 0;

            font-family: "Manrope", sans-serif;
            font-size: 1.04rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .group-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #169486;
        }

        .group-dot.past {
            background: #8ca3b2;
        }

        .group-total {
            color: var(--muted);
            font-size: .82rem;
            font-weight: 600;
        }

        /* Appointment cards */

        .appointment-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 20px;
        }

        .appointment-card {
            min-width: 0;
            overflow: hidden;

            border: 1px solid var(--border);
            border-radius: 19px;
            background: white;
            box-shadow: var(--shadow);

            transition: transform .2s,
                        border-color .2s,
                        box-shadow .2s;
        }

        .appointment-card:hover {
            transform: translateY(-2px);
            border-color: #b8ddd6;
            box-shadow: 0 16px 37px rgba(20,64,86,.075);
        }

        .appointment-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;

            padding: 23px 24px 19px;
            border-bottom: 1px solid var(--border);
        }

        .reference-caption {
            display: block;
            margin-bottom: 7px;
            color: #8297a4;
            font-size: .71rem;
            font-weight: 800;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .reference {
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
            font-size: .76rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .status-badge.confirmed {
            background: #e7f7ee;
            color: #26734e;
        }

        .status-badge.cancelled {
            background: #fbecee;
            color: #ab535e;
        }

        .status-badge.other {
            background: #edf3f7;
            color: #557185;
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
        }

        .appointment-body {
            padding: 24px;
        }

        .patient-label {
            display: block;
            margin-bottom: 9px;
            color: #8297a4;
            font-size: .71rem;
            font-weight: 800;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .patient-identity {
            display: flex;
            align-items: center;
            gap: 13px;
            min-width: 0;
        }

        .patient-avatar {
            display: grid;
            place-items: center;

            width: 46px;
            height: 46px;
            flex-shrink: 0;

            border-radius: 13px;
            background: #e8f6f3;
            color: var(--teal);
        }

        .patient-avatar svg {
            width: 23px;
            height: 23px;
        }

        .patient-name {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: 1.08rem;
            font-weight: 800;
            letter-spacing: -.035em;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .details-divider {
            height: 1px;
            margin: 22px 0;
            background: var(--border);
        }

        .appointment-details {
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            gap: 16px;
        }

        .detail-caption {
            display: block;
            margin-bottom: 8px;
            color: #8a9da9;
            font-size: .74rem;
            font-weight: 700;
        }

        .detail-value {
            display: block;
            color: #33566a;
            font-size: .87rem;
            font-weight: 700;
            line-height: 1.7;
        }

        .appointment-footer {
            padding: 17px 24px;
            background: #fbfdfd;
            border-top: 1px solid var(--border);

            color: #8096a4;
            font-size: .79rem;
            line-height: 1.7;
        }

        /* Informative empty states */

        .empty-state {
            display: flex;
            align-items: flex-start;
            gap: 16px;

            padding: 26px;
            border: 1px solid var(--border);
            border-radius: 18px;
            background: white;
        }

        .empty-icon {
            display: grid;
            place-items: center;
            width: 49px;
            height: 49px;
            flex-shrink: 0;

            border-radius: 14px;
            background: var(--teal-light);
            color: var(--teal);
        }

        .empty-state h3 {
            margin: 0 0 9px;
            font-family: "Manrope", sans-serif;
            font-size: .97rem;
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

        .error-box {
            margin-top: 24px;
            padding: 20px 23px;
            border: 1px solid #efd0cf;
            border-radius: 14px;
            background: #fff2f1;
            color: #9e3e44;
            font-size: .9rem;
            line-height: 1.75;
        }

        .error-box strong {
            display: block;
            margin-bottom: 6px;
        }

        /* Information panel */

        .information-panel {
            display: flex;
            align-items: flex-start;
            gap: 16px;

            margin-top: 32px;
            padding: 25px 28px;

            border: 1px solid #d7eae6;
            border-radius: 18px;
            background: linear-gradient(130deg,#eaf7f4,#f8fbfa);
        }

        .information-icon {
            display: grid;
            place-items: center;
            width: 42px;
            height: 42px;
            flex-shrink: 0;

            border-radius: 12px;
            background: #dcefe9;
            color: var(--teal);
        }

        .information-panel h3 {
            margin: 1px 0 9px;
            font-family: "Manrope", sans-serif;
            font-size: .98rem;
            font-weight: 800;
        }

        .information-panel p {
            margin: 0;
            color: #607c89;
            font-size: .85rem;
            line-height: 1.8;
        }

        .information-panel a {
            display: inline-block;
            margin-top: 12px;
            color: var(--teal);
            font-size: .84rem;
            font-weight: 800;
        }

        /* Footer */

        .footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-top: 36px;
            color: #8b9da9;
            font-size: .77rem;
        }

        /* Responsive */

        @media (max-width: 1180px) {
            .hero {
                grid-template-columns: 1fr;
            }

            .next-visit {
                max-width: 390px;
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

            .appointment-grid {
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
                width: 17px;
                height: 17px;
            }

            .topbar {
                padding: 16px;
            }

            .topbar-right {
                width: 100%;
                justify-content: space-between;
                flex-wrap: wrap;
            }

            .date-label {
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
                font-size: 1.98rem;
            }

            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-button {
                width: 100%;
            }

            .next-visit {
                max-width: none;
            }

            .summary-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .summary-card {
                padding: 19px 21px;
            }

            .filters {
                width: 100%;
            }

            .filter-button {
                flex: 1 1 auto;
                justify-content: center;
            }

            .appointment-top,
            .appointment-body,
            .appointment-footer {
                padding-left: 20px;
                padding-right: 20px;
            }

            .appointment-details {
                grid-template-columns: 1fr;
            }

            .information-panel {
                padding: 22px;
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

    <!-- Doctor Portal sidebar -->

    <aside class="sidebar">

        <a
            href="../dashboards/doctor-dashboard.php"
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
                    <path d="m9 15 2 2 4-4"/>
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

            <div class="sidebar-role">
                <span class="role-dot" aria-hidden="true"></span>
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

    <!-- Main content -->

    <div class="main-area">

        <header class="topbar">

            <div>
                <span class="topbar-eyebrow">
                    Doctor Portal
                </span>

                <h2 class="topbar-title">
                    My Appointments
                </h2>
            </div>

            <div class="topbar-right">

                <span class="date-label">
                    <?= doctorAppointmentEscape(
                        $now->format('l, F j, Y')
                    ) ?>
                </span>

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
                    >
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21a8 8 0 0 1 16 0"/>
                    </svg>

                    My Account
                </a>

            </div>

        </header>

        <main class="content">

            <!-- Page introduction -->

            <section class="hero">

                <div>

                    <span class="hero-eyebrow">
                        Your Appointment Schedule
                    </span>

                    <h1>
                        Every appointment, in focus.
                    </h1>

                    <p class="hero-description">
                        Review your scheduled patient visits
                        and previous appointments in one
                        organized clinical workspace.
                    </p>

                    <div class="hero-actions">

                        <a
                            href="#appointmentRecords"
                            class="hero-button primary"
                        >
                            View Appointments

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
                            href="availability.php"
                            class="hero-button secondary"
                        >
                            Manage Availability
                        </a>

                    </div>

                </div>

                <!-- Real next appointment, if available -->

                <aside class="next-visit">

                    <span class="next-label">
                        Next Scheduled Visit
                    </span>

                    <?php if (
                        $appointmentsError === '' &&
                        $nextAppointment !== null
                    ): ?>

                        <div class="next-date">
                            <?= doctorAppointmentEscape(
                                doctorAppointmentDate(
                                    $nextAppointment['appointment_date'],
                                    'l, F j'
                                )
                            ) ?>
                        </div>

                        <div class="next-time">
                            <?= doctorAppointmentEscape(
                                doctorAppointmentTime(
                                    $nextAppointment['start_time']
                                )
                            ) ?>

                            –

                            <?= doctorAppointmentEscape(
                                doctorAppointmentTime(
                                    $nextAppointment['end_time']
                                )
                            ) ?>
                        </div>

                        <div class="next-divider"></div>

                        <span class="next-patient-label">
                            Patient
                        </span>

                        <div class="next-patient">
                            <?= doctorAppointmentEscape(
                                $nextAppointment['patient_name']
                            ) ?>
                        </div>

                    <?php elseif ($appointmentsError !== ''): ?>

                        <p class="next-empty">
                            Schedule information is
                            temporarily unavailable.
                        </p>

                    <?php else: ?>

                        <p class="next-empty">
                            No future patient appointments
                            are currently listed.
                        </p>

                    <?php endif; ?>

                </aside>

            </section>

            <?php if ($appointmentsError !== ''): ?>

                <!-- Preserve loading error -->

                <div class="error-box" role="alert">
                    <strong>
                        Unable to Load Appointments
                    </strong>

                    <?= doctorAppointmentEscape(
                        $appointmentsError
                    ) ?>
                </div>

            <?php else: ?>

                <!-- Database-derived counts -->

                <section
                    class="summary-grid"
                    aria-label="Appointment summary"
                >

                    <div class="summary-card">

                        <div>
                            <span class="summary-label">
                                Total Appointments
                            </span>

                            <strong class="summary-value">
                                <?= $totalCount ?>
                            </strong>
                        </div>

                        <span
                            class="summary-icon"
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
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path d="M7 3v4M17 3v4M3 10h18"/>
                            </svg>
                        </span>

                    </div>

                    <div class="summary-card">

                        <div>
                            <span class="summary-label">
                                Upcoming Visits
                            </span>

                            <strong class="summary-value">
                                <?= $upcomingCount ?>
                            </strong>
                        </div>

                        <span
                            class="summary-icon blue"
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
                                <circle cx="12" cy="12" r="9"/>
                                <path d="M12 7v5l3 2"/>
                            </svg>
                        </span>

                    </div>

                    <div class="summary-card">

                        <div>
                            <span class="summary-label">
                                Previous Visits
                            </span>

                            <strong class="summary-value">
                                <?= $previousCount ?>
                            </strong>
                        </div>

                        <span
                            class="summary-icon slate"
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
                                <path d="M3 12a9 9 0 1 0 3-6.7"/>
                                <path d="M3 4v5h5"/>
                                <path d="M12 7v5l3 2"/>
                            </svg>
                        </span>

                    </div>

                </section>

                <!-- Appointment records -->

                <div
                    class="section-heading"
                    id="appointmentRecords"
                >

                    <span class="section-eyebrow">
                        Clinical Schedule
                    </span>

                    <h2 class="section-title">
                        Patient Appointments
                    </h2>

                    <p class="section-description">
                        The appointments listed here
                        belong to your doctor account.
                    </p>

                </div>

                <?php if (empty($doctorAppointments)): ?>

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
                                <rect x="3" y="5" width="18" height="16" rx="2"/>
                                <path d="M7 3v4M17 3v4M3 10h18"/>
                            </svg>
                        </span>

                        <div>

                            <h3>
                                No Appointments Listed
                            </h3>

                            <p>
                                No confirmed patient appointments
                                are currently available in
                                your schedule.
                            </p>

                            <a href="availability.php">
                                Manage My Availability &rarr;
                            </a>

                        </div>

                    </section>

                <?php else: ?>

                    <!-- Interactive group filters -->

                    <div
                        class="filters"
                        role="group"
                        aria-label="Appointment filters"
                    >

                        <button
                            type="button"
                            class="filter-button"
                            data-filter="All"
                            aria-pressed="true"
                        >
                            All

                            <span class="filter-count">
                                <?= $totalCount ?>
                            </span>
                        </button>

                        <button
                            type="button"
                            class="filter-button"
                            data-filter="Upcoming"
                            aria-pressed="false"
                        >
                            Upcoming

                            <span class="filter-count">
                                <?= $upcomingCount ?>
                            </span>
                        </button>

                        <button
                            type="button"
                            class="filter-button"
                            data-filter="Previous"
                            aria-pressed="false"
                        >
                            Previous

                            <span class="filter-count">
                                <?= $previousCount ?>
                            </span>
                        </button>

                    </div>

                    <!-- Real appointments by category -->

                    <?php foreach (
                        $appointmentGroups as
                        $groupName => $appointments
                    ): ?>

                        <section
                            class="appointment-group"
                            data-appointment-group="<?= doctorAppointmentEscape(
                                $groupName
                            ) ?>"
                        >

                            <div class="group-heading">

                                <h3 class="group-name">

                                    <span
                                        class="group-dot <?= $groupName === 'Previous'
                                            ? 'past'
                                            : '' ?>"
                                    ></span>

                                    <?= doctorAppointmentEscape(
                                        $groupName
                                    ) ?>

                                    Appointments

                                </h3>

                                <span class="group-total">
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
                                            No <?= doctorAppointmentEscape(
                                                strtolower($groupName)
                                            ) ?> appointments
                                        </h3>

                                        <p>
                                            There are no patient appointments
                                            in this category.
                                        </p>

                                    </div>

                                </div>

                            <?php else: ?>

                                <div class="appointment-grid">

                                    <?php foreach (
                                        $appointments as $appointment
                                    ): ?>

                                        <?php

                                        $appointmentId = (int) (
                                            $appointment['appointment_id'] ?? 0
                                        );

                                        $patientName = (string) (
                                            $appointment['patient_name'] ?? ''
                                        );

                                        $status = strtolower(
                                            trim(
                                                (string) (
                                                    $appointment['status'] ?? ''
                                                )
                                            )
                                        );

                                        $statusClass = 'other';

                                        if ($status === 'confirmed') {
                                            $statusClass = 'confirmed';
                                        } elseif ($status === 'cancelled') {
                                            $statusClass = 'cancelled';
                                        }

                                        $statusLabel = $status !== ''
                                            ? ucfirst($status)
                                            : 'Unavailable';

                                        ?>

                                        <article class="appointment-card">

                                            <div class="appointment-top">

                                                <div>

                                                    <span class="reference-caption">
                                                        Appointment Reference
                                                    </span>

                                                    <strong class="reference">
                                                        #<?= $appointmentId ?>
                                                    </strong>

                                                </div>

                                                <span
                                                    class="status-badge <?= $statusClass ?>"
                                                >
                                                    <span
                                                        class="status-dot"
                                                        aria-hidden="true"
                                                    ></span>

                                                    <?= doctorAppointmentEscape(
                                                        $statusLabel
                                                    ) ?>
                                                </span>

                                            </div>

                                            <div class="appointment-body">

                                                <span class="patient-label">
                                                    Patient
                                                </span>

                                                <div class="patient-identity">

                                                    <span
                                                        class="patient-avatar"
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

                                                    <h4 class="patient-name">
                                                        <?= doctorAppointmentEscape(
                                                            $patientName
                                                        ) ?>
                                                    </h4>

                                                </div>

                                                <div class="details-divider"></div>

                                                <div class="appointment-details">

                                                    <div>

                                                        <span class="detail-caption">
                                                            Appointment Date
                                                        </span>

                                                        <span class="detail-value">
                                                            <?= doctorAppointmentEscape(
                                                                doctorAppointmentDate(
                                                                    $appointment['appointment_date']
                                                                )
                                                            ) ?>
                                                        </span>

                                                    </div>

                                                    <div>

                                                        <span class="detail-caption">
                                                            Appointment Time
                                                        </span>

                                                        <span class="detail-value">

                                                            <?= doctorAppointmentEscape(
                                                                doctorAppointmentTime(
                                                                    $appointment['start_time']
                                                                )
                                                            ) ?>

                                                            –

                                                            <?= doctorAppointmentEscape(
                                                                doctorAppointmentTime(
                                                                    $appointment['end_time']
                                                                )
                                                            ) ?>

                                                        </span>

                                                    </div>

                                                </div>

                                            </div>

                                            <div class="appointment-footer">

                                                <?php if (
                                                    $groupName === 'Upcoming'
                                                ): ?>

                                                    Scheduled patient visit
                                                    · Afghanistan local time

                                                <?php else: ?>

                                                    Previous appointment record

                                                <?php endif; ?>

                                            </div>

                                        </article>

                                    <?php endforeach; ?>

                                </div>

                            <?php endif; ?>

                        </section>

                    <?php endforeach; ?>

                <?php endif; ?>

            <?php endif; ?>

            <!-- Useful navigation without adding new backend actions -->

            <section class="information-panel">

                <span
                    class="information-icon"
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
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                </span>

                <div>

                    <h3>
                        Keep Your Schedule Current
                    </h3>

                    <p>
                        This page displays your existing
                        patient appointments. To review or
                        update the time periods you make
                        available for bookings, use
                        My Availability.
                    </p>

                    <a href="availability.php">
                        Open Availability Management &rarr;
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

<script>
document.addEventListener('DOMContentLoaded', function () {

    const filterButtons = document.querySelectorAll(
        '[data-filter]'
    );

    const appointmentGroups = document.querySelectorAll(
        '[data-appointment-group]'
    );

    filterButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            const selected = button.dataset.filter;

            filterButtons.forEach(function (otherButton) {

                otherButton.setAttribute(
                    'aria-pressed',
                    otherButton === button
                        ? 'true'
                        : 'false'
                );

            });

            appointmentGroups.forEach(function (group) {

                group.hidden = (
                    selected !== 'All' &&
                    group.dataset.appointmentGroup !== selected
                );

            });

        });

    });

});
</script>

</body>
</html>