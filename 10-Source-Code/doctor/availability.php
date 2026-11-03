
<?php

/**
 * Doctor Availability Management
 * Clinic Appointment System
 *
 * Doctor-only scheduling workspace.
 * Existing create, update and delete actions preserved.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['doctor']);

// A single session-bound token protects all availability changes.
if (
    !isset($_SESSION['availability_csrf_token']) ||
    !is_string($_SESSION['availability_csrf_token'])
) {
    $_SESSION['availability_csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';

$doctorId = (int) ($_SESSION['user_id'] ?? 0);

$fullName = trim(
    (string) ($_SESSION['full_name'] ?? 'Doctor')
);

if ($fullName === '') {
    $fullName = 'Doctor';
}

// Read existing one-time action messages.
$availabilitySuccess = (string) (
    $_SESSION['availability_success'] ?? ''
);

$availabilityError = (string) (
    $_SESSION['availability_error'] ?? ''
);

unset(
    $_SESSION['availability_success'],
    $_SESSION['availability_error']
);

function availabilityEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function availabilityDisplayDate($value): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date('l, F j, Y', $timestamp);
}

function availabilityDisplayTime($value): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date('g:i A', $timestamp);
}

// Get only the logged-in doctor's availability.
$stmt = $conn->prepare(
    "SELECT
        id,
        availability_date,
        start_time,
        end_time,
        slot_duration_minutes
     FROM doctor_availability
     WHERE doctor_id = ?
     ORDER BY availability_date ASC, start_time ASC"
);

$stmt->bind_param('i', $doctorId);
$stmt->execute();

$availabilityRecords = $stmt
    ->get_result()
    ->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

// Use Afghanistan local time.
$clinicTimezone = new DateTimeZone('Asia/Kabul');

$now = new DateTimeImmutable(
    'now',
    $clinicTimezone
);

// Organize genuine saved availability records.
$upcomingRecords = [];
$pastRecords = [];

foreach ($availabilityRecords as $record) {

    $start = new DateTimeImmutable(
        $record['availability_date'] . ' ' .
        $record['start_time'],
        $clinicTimezone
    );

    $end = new DateTimeImmutable(
        $record['availability_date'] . ' ' .
        $record['end_time'],
        $clinicTimezone
    );

    if ($end <= $now) {

        $record['_display_status'] = 'Past';

        $pastRecords[] = $record;

    } else {

        $record['_display_status'] = (
            $start <= $now
                ? 'In Progress'
                : 'Upcoming'
        );

        $upcomingRecords[] = $record;
    }
}

$totalRecords = count($availabilityRecords);
$upcomingCount = count($upcomingRecords);
$pastCount = count($pastRecords);

// Today's date is genuine, not static page content.
$today = $now->format('l, F j, Y');

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
        My Availability | Clinic Appointment System
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
            --navy-deep: #0b2639;
            --teal: #087d85;
            --teal-dark: #08666e;

            --ink: #17384d;
            --muted: #718697;
            --border: #e2ebef;
            --background: #f3f8fa;

            --teal-soft: #e8f6f3;
            --blue-soft: #eaf2fa;
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
        button:focus-visible,
        input:focus-visible,
        summary:focus-visible {
            outline: 3px solid rgba(8,125,133,.35);
            outline-offset: 3px;
        }

        svg {
            display: block;
            width: 20px;
            height: 20px;
            flex-shrink: 0;
        }

        /* Application shell */

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
            font-family: "Manrope",sans-serif;
            font-size: .88rem;
            font-weight: 800;
            letter-spacing: -.035em;
            line-height: 1.45;
        }

        .nav-label {
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

            transition: background .2s,color .2s;
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

        /* Top bar */

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

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 17px;
        }

        .today-label {
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

        /* Main content */

        .content {
            width: 100%;
            max-width: 1500px;

            margin: 0 auto;
            padding: 37px 43px 55px;
        }

        /* Page hero */

        .hero {
            position: relative;
            isolation: isolate;
            overflow: hidden;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;

            min-height: 224px;
            padding: 39px 43px;

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
                    #102d46 0%,
                    #173e56 59%,
                    #0b5860 100%
                );
        }

        .hero::after {
            content: "";
            position: absolute;
            z-index: -1;

            width: 370px;
            height: 370px;
            right: -190px;
            bottom: -290px;

            border: 1px solid rgba(179,242,230,.2);
            border-radius: 50%;
            box-shadow: 0 0 0 48px rgba(179,242,230,.03);
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
            font-size: clamp(1.95rem,3vw,2.8rem);
            font-weight: 800;
            letter-spacing: -.06em;
            line-height: 1.2;
        }

        .hero p {
            max-width: 610px;
            margin: 0;

            color: #cce1e7;
            font-size: .94rem;
            line-height: 1.85;
        }

        .hero-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            min-height: 48px;
            padding: 12px 19px;

            border-radius: 11px;
            background: #b2eee1;
            color: #0c444d;

            font-size: .85rem;
            font-weight: 800;
            white-space: nowrap;

            transition: background .2s,transform .2s;
        }

        .hero-action:hover {
            background: #cbf7ec;
            transform: translateY(-2px);
        }

        .hero-action svg {
            width: 17px;
            height: 17px;
        }

        /* Summary */

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

            background: white;
            border: 1px solid var(--border);
            border-radius: 17px;
            box-shadow: var(--shadow);
        }

        .summary-label {
            display: block;
            margin-bottom: 8px;
            color: var(--muted);
            font-size: .83rem;
            font-weight: 600;
        }

        .summary-value {
            font-family: "Manrope",sans-serif;
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
            background: #e8f6f3;
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

        /* Notices */

        .notice {
            margin-top: 22px;
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
            background: #eaf8ef;
            border-color: #c8ead5;
            color: #246c4d;
        }

        .notice.error {
            background: #fff2f1;
            border-color: #efd0cf;
            color: #a13d44;
        }

        /* Section heading */

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

            font-family: "Manrope",sans-serif;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -.05em;
        }

        .section-description {
            margin: 9px 0 0;

            color: var(--muted);
            font-size: .88rem;
            line-height: 1.75;
        }

        /* Two-column workspace */

        .workspace-grid {
            display: grid;
            grid-template-columns: minmax(0,1.55fr)
                                   minmax(255px,.75fr);
            gap: 22px;
            align-items: start;
        }

        .surface-card {
            min-width: 0;
            overflow: hidden;

            background: white;
            border: 1px solid var(--border);
            border-radius: 19px;
            box-shadow: var(--shadow);
        }

        .card-heading {
            padding: 25px 28px;
            border-bottom: 1px solid var(--border);
        }

        .card-eyebrow {
            display: block;
            margin-bottom: 8px;

            color: var(--teal);
            font-size: .69rem;
            font-weight: 800;
            letter-spacing: .13em;
            text-transform: uppercase;
        }

        .card-title {
            margin: 0;

            font-family: "Manrope",sans-serif;
            font-size: 1.08rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .card-description {
            margin: 9px 0 0;

            color: var(--muted);
            font-size: .85rem;
            line-height: 1.75;
        }

        .card-body {
            padding: 28px;
        }

        /* Add availability form */

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

            color: #294d62;
            font-size: .87rem;
            font-weight: 700;
        }

        .required {
            color: var(--teal);
        }

        .field-input {
            display: block;
            width: 100%;
            min-height: 51px;

            padding: 12px 15px;

            border: 1px solid #dce7eb;
            border-radius: 11px;
            background: #f8fbfc;
            color: #214358;

            font-size: .92rem;
            outline: none;

            transition: border-color .2s,box-shadow .2s;
        }

        .field-input:hover {
            border-color: #bad6da;
        }

        .field-input:focus {
            background: white;
            border-color: var(--teal);
            box-shadow: 0 0 0 4px rgba(8,125,133,.1);
        }

        .field-hint {
            display: block;
            margin-top: 9px;

            color: #879ba8;
            font-size: .78rem;
            line-height: 1.7;
        }

        .duration-display {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;

            min-height: 53px;
            padding: 12px 16px;

            border: 1px solid #dceae7;
            border-radius: 11px;
            background: #f0f9f6;

            color: #28596a;
            font-size: .88rem;
            font-weight: 700;
        }

        .duration-value {
            color: var(--teal);
            font-family: "Manrope",sans-serif;
            font-weight: 800;
            white-space: nowrap;
        }

        .form-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 15px;

            margin-top: 27px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
        }

        .primary-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;

            min-height: 48px;
            padding: 12px 20px;

            border: 1px solid var(--teal);
            border-radius: 11px;

            background: var(--teal);
            color: white;

            font-size: .87rem;
            font-weight: 800;

            transition: background .2s,transform .2s;
        }

        .primary-button:hover {
            background: var(--teal-dark);
            transform: translateY(-1px);
        }

        .primary-button svg {
            width: 17px;
            height: 17px;
        }

        /* Scheduling guidance */

        .guide-card {
            background:
                linear-gradient(155deg,#eaf7f4,#ffffff 92%);
            border-color: #d7ebe6;
        }

        .guide-step {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            margin-bottom: 24px;
        }

        .guide-step:last-child {
            margin-bottom: 0;
        }

        .step-number {
            display: grid;
            place-items: center;

            width: 34px;
            height: 34px;
            flex-shrink: 0;

            border-radius: 10px;
            background: #dcefe9;
            color: var(--teal);

            font-size: .77rem;
            font-weight: 800;
        }

        .guide-step h3 {
            margin: 2px 0 6px;

            font-family: "Manrope",sans-serif;
            font-size: .87rem;
            font-weight: 800;
        }

        .guide-step p {
            margin: 0;

            color: #617e8b;
            font-size: .82rem;
            line-height: 1.75;
        }

        /* Existing schedules */

        .group-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 14px;

            margin-bottom: 17px;
        }

        .group-title {
            display: flex;
            align-items: center;
            gap: 11px;

            margin: 0;

            font-family: "Manrope",sans-serif;
            font-size: 1.04rem;
            font-weight: 800;
        }

        .group-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #169486;
        }

        .group-dot.past {
            background: #91a5b3;
        }

        .group-count {
            color: var(--muted);
            font-size: .81rem;
            font-weight: 700;
        }

        .schedule-group {
            margin-bottom: 31px;
        }

        .schedule-group:last-child {
            margin-bottom: 0;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 19px;
        }

        /* Individual real availability record */

        .schedule-card {
            min-width: 0;
            overflow: hidden;

            background: white;
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: var(--shadow);

            transition: border-color .2s,box-shadow .2s;
        }

        .schedule-card:hover {
            border-color: #b9dcd6;
            box-shadow: 0 16px 36px rgba(20,64,86,.075);
        }

        .schedule-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 13px;

            padding: 23px 23px 19px;
            border-bottom: 1px solid var(--border);
        }

        .schedule-eyebrow {
            display: block;
            margin-bottom: 8px;

            color: #879ba7;
            font-size: .69rem;
            font-weight: 800;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        .schedule-date {
            margin: 0;

            color: #1c4358;
            font-family: "Manrope",sans-serif;
            font-size: .99rem;
            font-weight: 800;
            line-height: 1.5;
        }

        .schedule-status {
            display: inline-flex;
            padding: 8px 10px;
            border-radius: 9px;

            background: #e7f7ee;
            color: #257450;

            font-size: .74rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .schedule-status.past {
            background: #edf2f6;
            color: #60798d;
        }

        .schedule-status.in-progress {
            background: #e8f3fa;
            color: #346c92;
        }

        .schedule-card-body {
            padding: 23px;
        }

        .schedule-time {
            display: block;
            margin-bottom: 13px;

            color: #173d52;
            font-family: "Manrope",sans-serif;
            font-size: 1.13rem;
            font-weight: 800;
            letter-spacing: -.04em;
            line-height: 1.5;
        }

        .schedule-meta {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            padding: 8px 11px;
            border-radius: 9px;

            background: #e8f6f3;
            color: #11717a;

            font-size: .79rem;
            font-weight: 700;
        }

        .schedule-meta svg {
            width: 16px;
            height: 16px;
        }

        /* Native expandable edit section */

        .edit-details {
            border-top: 1px solid var(--border);
            background: #fcfdfd;
        }

        .edit-details summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;

            padding: 17px 23px;

            color: var(--teal);
            font-size: .84rem;
            font-weight: 800;

            cursor: pointer;
            list-style: none;
        }

        .edit-details summary::-webkit-details-marker {
            display: none;
        }

        .edit-details summary::after {
            content: "+";
            font-size: 1.35rem;
            line-height: 1;
        }

        .edit-details[open] summary::after {
            content: "−";
        }

        .edit-details summary:hover {
            background: #f2faf8;
        }

        .edit-body {
            padding: 20px 23px 23px;
            border-top: 1px solid var(--border);
        }

        .edit-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .edit-fields .field.full {
            grid-column: 1 / -1;
        }

        .edit-fields .field-label {
            font-size: .79rem;
            margin-bottom: 7px;
        }

        .edit-fields .field-input {
            min-height: 44px;
            padding: 9px 11px;
            font-size: .85rem;
        }

        .edit-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;

            margin-top: 20px;
        }

        .save-button,
        .delete-button {
            min-height: 42px;
            padding: 10px 14px;

            border-radius: 10px;

            font-size: .81rem;
            font-weight: 800;
        }

        .save-button {
            border: 1px solid var(--teal);
            background: var(--teal);
            color: white;
        }

        .save-button:hover {
            background: var(--teal-dark);
        }

        .delete-button {
            border: 1px solid #e9c9cc;
            background: white;
            color: #a2515b;
        }

        .delete-button:hover {
            background: #fff0f1;
            border-color: #dbaeb4;
        }

        /* Empty state */

        .empty-state {
            display: flex;
            align-items: flex-start;
            gap: 17px;

            padding: 25px;

            background: white;
            border: 1px solid var(--border);
            border-radius: 17px;
        }

        .empty-icon {
            display: grid;
            place-items: center;

            width: 48px;
            height: 48px;
            flex-shrink: 0;

            border-radius: 13px;
            background: var(--teal-soft);
            color: var(--teal);
        }

        .empty-state h3 {
            margin: 0 0 8px;

            font-family: "Manrope",sans-serif;
            font-size: .96rem;
            font-weight: 800;
        }

        .empty-state p {
            margin: 0;

            color: var(--muted);
            font-size: .86rem;
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
            font-size: .77rem;
        }

        /* Responsive */

        @media (max-width: 1200px) {
            .workspace-grid {
                grid-template-columns: 1fr;
            }

            .guide-card {
                max-width: none;
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

            .schedule-grid {
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

            .nav-label,
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

            .today-label {
                font-size: .77rem;
            }

            .content {
                padding: 19px 13px 32px;
            }

            .hero {
                display: block;
                padding: 30px 24px;
            }

            .hero h1 {
                font-size: 1.95rem;
            }

            .hero-action {
                width: 100%;
                margin-top: 22px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .summary-card {
                padding: 19px 21px;
            }

            .field-grid {
                grid-template-columns: 1fr;
            }

            .field.full {
                grid-column: auto;
            }

            .card-heading,
            .card-body {
                padding: 23px 20px;
            }

            .form-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .primary-button {
                width: 100%;
            }

            .schedule-card-top,
            .schedule-card-body {
                padding-left: 19px;
                padding-right: 19px;
            }

            .edit-body {
                padding: 19px;
            }

            .edit-details summary {
                padding-left: 19px;
                padding-right: 19px;
            }

            .edit-fields {
                grid-template-columns: 1fr;
            }

            .edit-fields .field.full {
                grid-column: auto;
            }

            .edit-actions,
            .edit-actions form,
            .save-button,
            .delete-button {
                width: 100%;
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

    <!-- Doctor portal sidebar -->

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

        <div class="nav-label">
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

    <!-- Main page -->

    <div class="main-area">

        <header class="topbar">

            <div>

                <span class="topbar-eyebrow">
                    Doctor Portal
                </span>

                <h2 class="topbar-title">
                    Availability Management
                </h2>

            </div>

            <div class="topbar-right">

                <span class="today-label">
                    <?= availabilityEscape($today) ?>
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

            <!-- Introduction -->

            <section class="hero">

                <div>

                    <span class="hero-eyebrow">
                        Doctor Scheduling
                    </span>

                    <h1>
                        Your time, thoughtfully organized.
                    </h1>

                    <p>
                        Create and maintain the availability
                        periods used for patient appointment
                        booking. Review your existing schedule
                        and keep your working times up to date.
                    </p>

                </div>

                <a
                    href="#createAvailability"
                    class="hero-action"
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
                        <path d="M12 5v14M5 12h14"/>
                    </svg>

                    Add Availability

                </a>

            </section>

            <!-- Real scheduling totals -->

            <section
                class="summary-grid"
                aria-label="Availability summary"
            >

                <div class="summary-card">

                    <div>

                        <span class="summary-label">
                            Total Schedule Periods
                        </span>

                        <strong class="summary-value">
                            <?= $totalRecords ?>
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
                        >
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M7 3v4M17 3v4M3 10h18"/>
                        </svg>

                    </span>

                </div>

                <div class="summary-card">

                    <div>

                        <span class="summary-label">
                            Current & Upcoming
                        </span>

                        <strong class="summary-value">
                            <?= $upcomingCount ?>
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
                        >
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>

                    </span>

                </div>

                <div class="summary-card">

                    <div>

                        <span class="summary-label">
                            Past Schedule Periods
                        </span>

                        <strong class="summary-value">
                            <?= $pastCount ?>
                        </strong>

                    </div>

                    <span class="summary-icon slate">

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

            <!-- Existing action feedback -->

            <?php if ($availabilitySuccess !== ''): ?>

                <div
                    class="notice success"
                    role="status"
                >
                    <strong>
                        Availability Updated
                    </strong>

                    <?= availabilityEscape(
                        $availabilitySuccess
                    ) ?>
                </div>

            <?php endif; ?>

            <?php if ($availabilityError !== ''): ?>

                <div
                    class="notice error"
                    role="alert"
                >
                    <strong>
                        Availability Action Unsuccessful
                    </strong>

                    <?= availabilityEscape(
                        $availabilityError
                    ) ?>
                </div>

            <?php endif; ?>

            <!-- Create availability -->

            <div
                class="section-heading"
                id="createAvailability"
            >

                <span class="section-eyebrow">
                    Schedule Setup
                </span>

                <h2 class="section-title">
                    Add a New Availability Period
                </h2>

                <p class="section-description">
                    Choose the day and working hours
                    you want to publish.
                </p>

            </div>

            <div class="workspace-grid">

                <section class="surface-card">

                    <div class="card-heading">

                        <span class="card-eyebrow">
                            New Availability
                        </span>

                        <h2 class="card-title">
                            Set Your Working Hours
                        </h2>

                        <p class="card-description">
                            Provide the date, start time,
                            and end time for a new period.
                        </p>

                    </div>

                    <div class="card-body">

                        <form
                            action="../actions/availability_create.php"
                            method="POST"
                            class="availability-form"
                        >

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?= availabilityEscape($_SESSION['availability_csrf_token']) ?>"
                            >

                            <div class="field-grid">

                                <div class="field full">

                                    <label
                                        for="availability_date"
                                        class="field-label"
                                    >
                                        Availability Date
                                        <span class="required">*</span>
                                    </label>

                                    <input
                                        type="date"
                                        id="availability_date"
                                        name="availability_date"
                                        class="field-input"
                                        required
                                    >

                                </div>

                                <div class="field">

                                    <label
                                        for="start_time"
                                        class="field-label"
                                    >
                                        Start Time
                                        <span class="required">*</span>
                                    </label>

                                    <input
                                        type="time"
                                        id="start_time"
                                        name="start_time"
                                        class="field-input"
                                        required
                                    >

                                </div>

                                <div class="field">

                                    <label
                                        for="end_time"
                                        class="field-label"
                                    >
                                        End Time
                                        <span class="required">*</span>
                                    </label>

                                    <input
                                        type="time"
                                        id="end_time"
                                        name="end_time"
                                        class="field-input"
                                        required
                                    >

                                </div>

                                <div class="field full">

                                    <label class="field-label">
                                        Appointment Duration
                                    </label>

                                    <div class="duration-display">

                                        <span>
                                            Standard appointment interval
                                        </span>

                                        <span class="duration-value">
                                            30 minutes
                                        </span>

                                    </div>

                                    <span class="field-hint">
                                        New appointment slots use
                                        the system's existing
                                        30-minute interval.
                                        All times are entered
                                        in Afghanistan local time.
                                    </span>

                                </div>

                            </div>

                            <div class="form-footer">

                                <button
                                    type="submit"
                                    class="primary-button"
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
                                        <path d="M12 5v14M5 12h14"/>
                                    </svg>

                                    Add Availability

                                </button>

                            </div>

                        </form>

                    </div>

                </section>

                <!-- Scheduling guidance -->

                <aside class="surface-card guide-card">

                    <div class="card-heading">

                        <span class="card-eyebrow">
                            Scheduling Guide
                        </span>

                        <h2 class="card-title">
                            Plan Your Availability
                        </h2>

                        <p class="card-description">
                            Keep your published working
                            times clear and accurate.
                        </p>

                    </div>

                    <div class="card-body">

                        <div class="guide-step">

                            <span class="step-number">
                                01
                            </span>

                            <div>

                                <h3>
                                    Select a Date
                                </h3>

                                <p>
                                    Choose the date for
                                    your availability period.
                                </p>

                            </div>

                        </div>

                        <div class="guide-step">

                            <span class="step-number">
                                02
                            </span>

                            <div>

                                <h3>
                                    Define Working Hours
                                </h3>

                                <p>
                                    Enter the beginning and
                                    ending time of the period.
                                </p>

                            </div>

                        </div>

                        <div class="guide-step">

                            <span class="step-number">
                                03
                            </span>

                            <div>

                                <h3>
                                    Review Your Schedule
                                </h3>

                                <p>
                                    Check your saved periods
                                    below and make changes
                                    when necessary.
                                </p>

                            </div>

                        </div>

                    </div>

                </aside>

            </div>

            <!-- Existing availability records -->

            <div
                class="section-heading"
                id="savedAvailability"
            >

                <span class="section-eyebrow">
                    Published Schedule
                </span>

                <h2 class="section-title">
                    My Availability
                </h2>

                <p class="section-description">
                    All records below belong to
                    your doctor account.
                </p>

            </div>

            <?php if ($totalRecords === 0): ?>

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
                            No Availability Added Yet
                        </h3>

                        <p>
                            Your schedule is currently empty.
                            Use the form above to add your
                            first availability period.
                        </p>

                    </div>

                </section>

            <?php else: ?>

                <?php

                $availabilityGroups = [
                    'Current & Upcoming' => $upcomingRecords,
                    'Past' => $pastRecords
                ];

                ?>

                <?php foreach (
                    $availabilityGroups as
                    $groupName => $records
                ): ?>

                    <section class="schedule-group">

                        <div class="group-heading">

                            <h3 class="group-title">

                                <span
                                    class="group-dot <?= $groupName === 'Past'
                                        ? 'past'
                                        : '' ?>"
                                ></span>

                                <?= availabilityEscape($groupName) ?>

                            </h3>

                            <span class="group-count">

                                <?= count($records) ?>

                                <?= count($records) === 1
                                    ? 'period'
                                    : 'periods' ?>

                            </span>

                        </div>

                        <?php if (empty($records)): ?>

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
                                        No Periods in This Category
                                    </h3>

                                    <p>
                                        There are currently no
                                        availability records
                                        in this section.
                                    </p>

                                </div>

                            </div>

                        <?php else: ?>

                            <div class="schedule-grid">

                                <?php foreach (
                                    $records as $availability
                                ): ?>

                                    <?php

                                    $availabilityId = (int) (
                                        $availability['id']
                                    );

                                    $recordStatus = (string) (
                                        $availability['_display_status']
                                    );

                                    $statusClass = '';

                                    if ($recordStatus === 'Past') {
                                        $statusClass = 'past';
                                    } elseif (
                                        $recordStatus === 'In Progress'
                                    ) {
                                        $statusClass = 'in-progress';
                                    }

                                    ?>

                                    <article class="schedule-card">

                                        <div class="schedule-card-top">

                                            <div>

                                                <span class="schedule-eyebrow">
                                                    Availability Period
                                                </span>

                                                <h4 class="schedule-date">

                                                    <?= availabilityEscape(
                                                        availabilityDisplayDate(
                                                            $availability[
                                                                'availability_date'
                                                            ]
                                                        )
                                                    ) ?>

                                                </h4>

                                            </div>

                                            <span
                                                class="schedule-status <?= $statusClass ?>"
                                            >
                                                <?= availabilityEscape(
                                                    $recordStatus
                                                ) ?>
                                            </span>

                                        </div>

                                        <div class="schedule-card-body">

                                            <span class="schedule-time">

                                                <?= availabilityEscape(
                                                    availabilityDisplayTime(
                                                        $availability[
                                                            'start_time'
                                                        ]
                                                    )
                                                ) ?>

                                                –

                                                <?= availabilityEscape(
                                                    availabilityDisplayTime(
                                                        $availability[
                                                            'end_time'
                                                        ]
                                                    )
                                                ) ?>

                                            </span>

                                            <span class="schedule-meta">

                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="1.8"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    aria-hidden="true"
                                                >
                                                    <circle
                                                        cx="12"
                                                        cy="12"
                                                        r="9"
                                                    />
                                                    <path d="M12 7v5l3 2"/>
                                                </svg>

                                                <?= (int) (
                                                    $availability[
                                                        'slot_duration_minutes'
                                                    ]
                                                ) ?>
                                                minute slots

                                            </span>

                                        </div>

                                        <!-- Edit and delete existing record -->

                                        <details class="edit-details">

                                            <summary>
                                                Edit Availability
                                            </summary>

                                            <div class="edit-body">

                                                <form
                                                    method="POST"
                                                    action="../actions/availability_update.php"
                                                    class="availability-form"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= availabilityEscape($_SESSION['availability_csrf_token']) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="availability_id"
                                                        value="<?= $availabilityId ?>"
                                                    >

                                                    <div class="edit-fields">

                                                        <div class="field full">

                                                            <label
                                                                class="field-label"
                                                                for="date-<?= $availabilityId ?>"
                                                            >
                                                                Date
                                                            </label>

                                                            <input
                                                                type="date"
                                                                id="date-<?= $availabilityId ?>"
                                                                name="availability_date"
                                                                class="field-input"
                                                                value="<?= availabilityEscape(
                                                                    $availability[
                                                                        'availability_date'
                                                                    ]
                                                                ) ?>"
                                                                required
                                                            >

                                                        </div>

                                                        <div class="field">

                                                            <label
                                                                class="field-label"
                                                                for="start-<?= $availabilityId ?>"
                                                            >
                                                                Start Time
                                                            </label>

                                                            <input
                                                                type="time"
                                                                id="start-<?= $availabilityId ?>"
                                                                name="start_time"
                                                                class="field-input"
                                                                value="<?= availabilityEscape(
                                                                    substr(
                                                                        $availability[
                                                                            'start_time'
                                                                        ],
                                                                        0,
                                                                        5
                                                                    )
                                                                ) ?>"
                                                                required
                                                            >

                                                        </div>

                                                        <div class="field">

                                                            <label
                                                                class="field-label"
                                                                for="end-<?= $availabilityId ?>"
                                                            >
                                                                End Time
                                                            </label>

                                                            <input
                                                                type="time"
                                                                id="end-<?= $availabilityId ?>"
                                                                name="end_time"
                                                                class="field-input"
                                                                value="<?= availabilityEscape(
                                                                    substr(
                                                                        $availability[
                                                                            'end_time'
                                                                        ],
                                                                        0,
                                                                        5
                                                                    )
                                                                ) ?>"
                                                                required
                                                            >

                                                        </div>

                                                    </div>

                                                    <div class="edit-actions">

                                                        <button
                                                            type="submit"
                                                            class="save-button"
                                                        >
                                                            Save Changes
                                                        </button>

                                                    </div>

                                                </form>

                                                <!-- Separate delete form -->

                                                <form
                                                    method="POST"
                                                    action="../actions/availability_delete.php"
                                                    onsubmit="return confirm('Are you sure you want to delete this availability period?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= availabilityEscape($_SESSION['availability_csrf_token']) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="availability_id"
                                                        value="<?= $availabilityId ?>"
                                                    >

                                                    <div class="edit-actions">

                                                        <button
                                                            type="submit"
                                                            class="delete-button"
                                                        >
                                                            Delete Availability
                                                        </button>

                                                    </div>

                                                </form>

                                            </div>

                                        </details>

                                    </article>

                                <?php endforeach; ?>

                            </div>

                        <?php endif; ?>

                    </section>

                <?php endforeach; ?>

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

    // Check time ranges before submitting.
    // Server-side validation remains authoritative.

    const forms = document.querySelectorAll(
        '.availability-form'
    );

    forms.forEach(function (form) {

        form.addEventListener('submit', function (event) {

            const start = form.querySelector(
                '[name="start_time"]'
            );

            const end = form.querySelector(
                '[name="end_time"]'
            );

            if (!start || !end) {
                return;
            }

            end.setCustomValidity('');

            if (
                start.value &&
                end.value &&
                end.value <= start.value
            ) {

                event.preventDefault();

                end.setCustomValidity(
                    'End time must be later than start time.'
                );

                end.reportValidity();
            }

        });

        const end = form.querySelector(
            '[name="end_time"]'
        );

        if (end) {

            end.addEventListener('input', function () {
                end.setCustomValidity('');
            });

        }

    });

});
</script>

</body>
</html>