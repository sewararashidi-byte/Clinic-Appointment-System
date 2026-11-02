<?php

/**
 * Patient Appointment Rescheduling
 * Clinic Appointment System
 *
 * Premium patient interface.
 * Original authorization, scheduling rules,
 * CSRF protection and backend action preserved.
 */

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/doctor_repository.php';
require_once __DIR__ . '/../includes/appointment_repository.php';

function rescheduleEscape($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

function rescheduleDate($value): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date('l, F j, Y', $timestamp);
}

function rescheduleTime($value): string
{
    $timestamp = strtotime((string) $value);

    return $timestamp === false
        ? (string) $value
        : date('g:i A', $timestamp);
}

// Use the existing appointment-action CSRF token.
if (
    !isset($_SESSION['appointment_action_csrf_token']) ||
    !is_string($_SESSION['appointment_action_csrf_token'])
) {
    $_SESSION['appointment_action_csrf_token'] =
        bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['appointment_action_csrf_token'];

$clinicTimezone = new DateTimeZone('Asia/Kabul');

$now = new DateTimeImmutable(
    'now',
    $clinicTimezone
);

$patientId = (int) ($_SESSION['user_id'] ?? 0);

$appointmentInput = $_GET['appointment_id'] ?? null;

$appointmentId = (
    is_string($appointmentInput) ||
    is_int($appointmentInput)
)
    ? filter_var(
        $appointmentInput,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    )
    : false;

$selectedAppointment = null;
$availableSlots = [];
$slotsByDate = [];
$pageError = '';

try {

    if (
        $patientId <= 0 ||
        !is_int($appointmentId)
    ) {
        throw new DomainException(
            'Invalid appointment request.'
        );
    }

    // Load only appointments belonging to this patient.
    $patientAppointments = getPatientAppointments(
        $conn,
        $patientId
    );

    foreach ($patientAppointments as $appointment) {

        if (
            (int) $appointment['appointment_id'] ===
            $appointmentId
        ) {
            $selectedAppointment = $appointment;
            break;
        }
    }

    if ($selectedAppointment === null) {
        throw new DomainException(
            'Appointment not found.'
        );
    }

    // Only confirmed appointments are eligible.
    if ($selectedAppointment['status'] !== 'confirmed') {
        throw new DomainException(
            'Only confirmed appointments can be rescheduled.'
        );
    }

    $originalStart = new DateTimeImmutable(
        $selectedAppointment['appointment_date'] . ' ' .
        $selectedAppointment['start_time'],
        $clinicTimezone
    );

    // Approved two-hour rescheduling restriction.
    $rescheduleDeadline =
        $originalStart->modify('-2 hours');

    if ($now > $rescheduleDeadline) {
        throw new DomainException(
            'Online rescheduling is no longer available. ' .
            'Please contact the clinic.'
        );
    }

    // The doctor must remain unchanged.
    $doctorId = (int) $selectedAppointment['doctor_id'];

    // Retrieve genuine available slots.
    $availabilityRecords = getDoctorAvailability(
        $conn,
        $doctorId
    );

    $availableSlots = getAvailableAppointmentSlots(
        $conn,
        $doctorId,
        $availabilityRecords
    );

    // Do not offer the original appointment time.
    $availableSlots = array_values(
        array_filter(
            $availableSlots,
            function ($slot) use ($selectedAppointment): bool {

                return !(
                    $slot['appointment_date'] ===
                        $selectedAppointment['appointment_date'] &&
                    $slot['start_time'] ===
                        $selectedAppointment['start_time']
                );
            }
        )
    );

    // Present the earliest genuine slots first.
    usort(
        $availableSlots,
        function ($a, $b): int {

            $first =
                $a['appointment_date'] . ' ' .
                $a['start_time'];

            $second =
                $b['appointment_date'] . ' ' .
                $b['start_time'];

            return strcmp($first, $second);
        }
    );

    // Preserve the manageable limit.
    $availableSlots = array_slice(
        $availableSlots,
        0,
        100
    );

    // Group appointments visually by date.
    foreach ($availableSlots as $slot) {

        $date = $slot['appointment_date'];

        if (!isset($slotsByDate[$date])) {
            $slotsByDate[$date] = [];
        }

        $slotsByDate[$date][] = $slot;
    }

} catch (DomainException $e) {

    $pageError = $e->getMessage();

} catch (Throwable $e) {

    error_log(
        'Reschedule page error: ' . $e->getMessage()
    );

    $pageError =
        'Unable to load appointment options. Please try again.';

} finally {

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

$totalSlots = count($availableSlots);
$availableDates = count($slotsByDate);

$fullName = trim(
    (string) ($_SESSION['full_name'] ?? 'Patient')
);

if ($fullName === '') {
    $fullName = 'Patient';
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
        Reschedule Appointment | Clinic Appointment System
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
            --navy-soft: #173e61;
            --teal: #087d85;
            --teal-dark: #08666e;
            --teal-pale: #e8f6f3;
            --ink: #17374d;
            --muted: #718697;
            --border: #e2ebef;
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

            font-family:
                "DM Sans",
                "Segoe UI",
                sans-serif;

            -webkit-font-smoothing: antialiased;
        }

        button,
        select {
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
        select:focus-visible {
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

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;

            display: flex;
            flex-direction: column;

            padding: 30px 18px 22px;
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

        .nav-caption {
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

        /* Main content */

        .main-area {
            min-width: 0;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
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
            font-family: "Manrope", sans-serif;
            font-size: 1.07rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .account-link {
            display: flex;
            align-items: center;
            gap: 10px;

            max-width: 245px;
            padding: 8px 13px;
            border: 1px solid var(--border);
            border-radius: 30px;

            color: var(--ink);
            background: white;

            font-size: .84rem;
            font-weight: 700;
        }

        .account-link:hover {
            background: #f5faf9;
        }

        .account-link svg {
            color: var(--teal);
            width: 17px;
            height: 17px;
        }

        .account-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .page-content {
            max-width: 1450px;
            margin: 0 auto;
            padding: 36px 42px 55px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 23px;
            color: var(--teal);
            font-size: .85rem;
            font-weight: 800;
        }

        .back-link svg {
            width: 17px;
            height: 17px;
        }

        .back-link:hover {
            color: var(--teal-dark);
            text-decoration: underline;
        }

        /* Page introduction */

        .hero {
            position: relative;
            isolation: isolate;
            overflow: hidden;

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 28px;

            min-height: 215px;
            padding: 39px 42px;

            border-radius: 23px;
            color: white;

            background:
                radial-gradient(
                    circle at 92% 15%,
                    rgba(78,199,183,.23),
                    transparent 41%
                ),
                linear-gradient(
                    115deg,
                    #102d46,
                    #17455a 68%,
                    #0d5960
                );
        }

        .hero::after {
            content: "";
            position: absolute;
            z-index: -1;

            width: 350px;
            height: 350px;
            right: -180px;
            bottom: -270px;

            border: 1px solid rgba(178,236,227,.2);
            border-radius: 50%;

            box-shadow:
                0 0 0 45px rgba(178,236,227,.035);

            pointer-events: none;
        }

        .hero-eyebrow {
            display: block;
            margin-bottom: 14px;
            color: #a6e9de;
            font-size: .71rem;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 0 0 15px;

            font-family: "Manrope", sans-serif;
            font-size: clamp(1.9rem,3vw,2.8rem);
            font-weight: 800;
            letter-spacing: -.06em;
            line-height: 1.22;
        }

        .hero p {
            max-width: 610px;
            margin: 0;
            color: #cce1e7;
            font-size: .94rem;
            line-height: 1.85;
        }

        .hero-icon {
            display: grid;
            place-items: center;

            width: 94px;
            height: 94px;
            flex-shrink: 0;

            border: 1px solid rgba(223,250,245,.2);
            border-radius: 24px;
            background: rgba(255,255,255,.09);
            color: #b4ecdf;
        }

        .hero-icon svg {
            width: 47px;
            height: 47px;
        }

        /* Feedback and error state */

        .notice {
            margin-top: 24px;
            padding: 19px 21px;
            border: 1px solid #efdcb6;
            border-radius: 14px;
            background: #fff9eb;
            color: #836522;
            line-height: 1.7;
            font-size: .9rem;
        }

        .notice strong {
            display: block;
            margin-bottom: 6px;
            font-family: "Manrope", sans-serif;
        }

        .notice p {
            margin: 0 0 14px;
        }

        .notice-link {
            display: inline-flex;
            align-items: center;
            min-height: 40px;
            padding: 10px 15px;
            border-radius: 9px;
            background: white;
            border: 1px solid #e4d4ac;
            font-size: .82rem;
            font-weight: 800;
        }

        /* Section headings */

        .section-heading {
            margin: 35px 0 19px;
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
            font-size: 1.32rem;
            font-weight: 800;
            letter-spacing: -.045em;
        }

        .section-description {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: .87rem;
            line-height: 1.75;
        }

        /* Current appointment and policy */

        .details-grid {
            display: grid;
            grid-template-columns: minmax(0,1.25fr)
                                   minmax(260px,.75fr);
            gap: 20px;
        }

        .surface-card {
            min-width: 0;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 19px;
            background: white;
            box-shadow: 0 10px 32px rgba(20,64,86,.045);
        }

        .card-heading {
            padding: 24px 28px;
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
            font-family: "Manrope", sans-serif;
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .card-description {
            margin: 8px 0 0;
            font-size: .85rem;
            line-height: 1.7;
            color: var(--muted);
        }

        .card-body {
            padding: 26px 28px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2,minmax(0,1fr));
            gap: 25px 22px;
        }

        .detail-label {
            display: block;
            margin-bottom: 8px;
            color: #879aa7;
            font-size: .72rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .detail-value {
            display: block;
            color: #294b60;
            font-size: .94rem;
            font-weight: 700;
            line-height: 1.7;
            overflow-wrap: anywhere;
        }

        .reference {
            color: var(--teal);
            font-family: "Manrope", sans-serif;
            font-weight: 800;
        }

        .status {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 8px;
            color: #25784f;
            background: #e7f6ed;
            font-size: .79rem;
            font-weight: 800;
        }

        .policy-card {
            background: linear-gradient(155deg,#eaf7f4,#ffffff);
            border-color: #d8eae6;
        }

        .policy-item {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            margin-bottom: 23px;
        }

        .policy-item:last-child {
            margin-bottom: 0;
        }

        .policy-number {
            display: grid;
            place-items: center;
            width: 34px;
            height: 34px;
            flex-shrink: 0;
            border-radius: 10px;
            background: #d8f0e9;
            color: #087880;
            font-size: .78rem;
            font-weight: 800;
        }

        .policy-item h3 {
            margin: 2px 0 6px;
            font-family: "Manrope", sans-serif;
            font-size: .85rem;
            font-weight: 800;
        }

        .policy-item p {
            margin: 0;
            color: #607f88;
            font-size: .82rem;
            line-height: 1.7;
        }

        /* Alternative slots */

        .slots-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .count-badge {
            display: inline-flex;
            padding: 9px 13px;
            border-radius: 30px;
            color: #08676e;
            background: #e8f6f3;
            font-size: .8rem;
            font-weight: 800;
        }

        .slots-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
            padding: 21px 28px;
            border-bottom: 1px solid var(--border);
            background: #fbfdfd;
        }

        .slots-toolbar p {
            margin: 0;
            color: var(--muted);
            font-size: .84rem;
            line-height: 1.6;
        }

        .filter-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .filter-wrap label {
            color: #5c7787;
            font-size: .82rem;
            font-weight: 700;
        }

        .date-select {
            min-height: 42px;
            max-width: 230px;
            padding: 9px 12px;
            border: 1px solid #d8e5e9;
            border-radius: 10px;
            background: white;
            color: var(--ink);
            font-size: .84rem;
        }

        .slots-content {
            padding: 27px 28px;
        }

        .date-group {
            margin-bottom: 31px;
        }

        .date-group:last-child {
            margin-bottom: 0;
        }

        .date-group[hidden] {
            display: none !important;
        }

        .date-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;

            margin-bottom: 16px;
        }

        .date-heading h3 {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: .98rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .date-heading span {
            color: #8397a4;
            font-size: .81rem;
            font-weight: 600;
        }

        .slots-grid {
            display: grid;
            grid-template-columns: repeat(3,minmax(0,1fr));
            gap: 15px;
        }

        .slot-card {
            display: flex;
            flex-direction: column;
            min-width: 0;
            padding: 20px;

            border: 1px solid #dce9ec;
            border-radius: 15px;
            background: white;

            transition:
                transform .2s,
                border-color .2s,
                box-shadow .2s;
        }

        .slot-card:hover {
            transform: translateY(-2px);
            border-color: #a7d7d0;
            box-shadow: 0 12px 27px rgba(20,80,88,.07);
        }

        .slot-time {
            display: block;
            margin-bottom: 8px;

            font-family: "Manrope", sans-serif;
            font-size: 1rem;
            font-weight: 800;
            color: #24475b;
            letter-spacing: -.04em;
            line-height: 1.6;
        }

        .slot-caption {
            display: block;
            margin-bottom: 22px;
            color: #8195a3;
            font-size: .8rem;
        }

        .slot-form {
            margin-top: auto;
        }

        .select-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;

            width: 100%;
            min-height: 45px;
            padding: 11px 13px;

            border: 1px solid var(--teal);
            border-radius: 10px;

            background: var(--teal);
            color: white;

            font-size: .84rem;
            font-weight: 800;

            transition: background .2s, transform .2s;
        }

        .select-button:hover {
            background: var(--teal-dark);
        }

        .select-button svg {
            width: 17px;
            height: 17px;
        }

        /* Empty state */

        .empty-state {
            padding: 27px;
            border-radius: 15px;
            border: 1px solid #e0ecef;
            background: #f7fafb;
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
            font-size: .87rem;
            line-height: 1.8;
        }

        .slots-note {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            color: #8396a3;
            font-size: .82rem;
            line-height: 1.8;
        }

        /* Footer */

        .footer {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            padding-top: 38px;
            color: #8a9ca8;
            font-size: .77rem;
        }

        /* Responsive */

        @media (max-width: 1200px) {
            .slots-grid {
                grid-template-columns: repeat(2,minmax(0,1fr));
            }
        }

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

            .details-grid {
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

            .account-name {
                max-width: 110px;
            }

            .page-content {
                padding: 19px 13px 34px;
            }

            .hero {
                padding: 30px 24px;
            }

            .hero h1 {
                font-size: 1.95rem;
            }

            .hero-icon {
                display: none;
            }

            .card-heading,
            .card-body,
            .slots-content {
                padding: 22px 20px;
            }

            .detail-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .slots-toolbar {
                padding: 19px 20px;
            }

            .filter-wrap {
                width: 100%;
            }

            .date-select {
                flex: 1;
                min-width: 0;
            }

            .slots-grid {
                grid-template-columns: 1fr;
            }

            .slot-card {
                padding: 19px;
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

    <!-- Patient portal sidebar -->

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

    <!-- Main area -->

    <div class="main-area">

        <header class="topbar">

            <div>
                <span class="topbar-eyebrow">
                    Patient Portal
                </span>

                <div class="topbar-title">
                    Appointment Rescheduling
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
                    <?= rescheduleEscape($fullName) ?>
                </span>
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

            <!-- Main introduction -->

            <section class="hero">

                <div>

                    <span class="hero-eyebrow">
                        Appointment Management
                    </span>

                    <h1>
                        Find a time that works for you.
                    </h1>

                    <p>
                        Review your current appointment
                        and choose another available
                        time with the same doctor.
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
                        <rect x="3" y="5" width="18" height="16" rx="2"/>
                        <path d="M7 3v4M17 3v4M3 10h18"/>
                        <path d="M8 15h8m-3-3 3 3-3 3"/>
                    </svg>
                </span>

            </section>

            <?php if ($pageError !== ''): ?>

                <!-- Invalid or ineligible appointment -->

                <div class="notice" role="alert">

                    <strong>
                        Rescheduling Unavailable
                    </strong>

                    <p>
                        <?= rescheduleEscape($pageError) ?>
                    </p>

                    <a
                        href="appointments.php"
                        class="notice-link"
                    >
                        Return to My Appointments
                    </a>

                </div>

            <?php elseif ($selectedAppointment !== null): ?>

                <!-- Current appointment details -->

                <div class="section-heading">

                    <span class="section-eyebrow">
                        Your Current Booking
                    </span>

                    <h2 class="section-title">
                        Current Appointment
                    </h2>

                    <p class="section-description">
                        Review the booking you are about
                        to reschedule.
                    </p>

                </div>

                <div class="details-grid">

                    <section class="surface-card">

                        <div class="card-heading">

                            <span class="card-eyebrow">
                                Appointment Details
                            </span>

                            <h2 class="card-title">
                                Booking Information
                            </h2>

                            <p class="card-description">
                                The doctor will remain
                                unchanged after rescheduling.
                            </p>

                        </div>

                        <div class="card-body">

                            <div class="detail-grid">

                                <div>

                                    <span class="detail-label">
                                        Reference
                                    </span>

                                    <span class="detail-value reference">
                                        #<?= (int) (
                                            $selectedAppointment[
                                                'appointment_id'
                                            ]
                                        ) ?>
                                    </span>

                                </div>

                                <div>

                                    <span class="detail-label">
                                        Status
                                    </span>

                                    <span class="status">
                                        Confirmed
                                    </span>

                                </div>

                                <div>

                                    <span class="detail-label">
                                        Doctor
                                    </span>

                                    <span class="detail-value">
                                        <?= rescheduleEscape(
                                            $selectedAppointment[
                                                'doctor_name'
                                            ]
                                        ) ?>
                                    </span>

                                </div>

                                <div>

                                    <span class="detail-label">
                                        Current Date
                                    </span>

                                    <span class="detail-value">
                                        <?= rescheduleEscape(
                                            rescheduleDate(
                                                $selectedAppointment[
                                                    'appointment_date'
                                                ]
                                            )
                                        ) ?>
                                    </span>

                                </div>

                                <div>

                                    <span class="detail-label">
                                        Current Start Time
                                    </span>

                                    <span class="detail-value">
                                        <?= rescheduleEscape(
                                            rescheduleTime(
                                                $selectedAppointment[
                                                    'start_time'
                                                ]
                                            )
                                        ) ?>
                                    </span>

                                </div>

                                <div>

                                    <span class="detail-label">
                                        Current End Time
                                    </span>

                                    <span class="detail-value">
                                        <?= rescheduleEscape(
                                            rescheduleTime(
                                                $selectedAppointment[
                                                    'end_time'
                                                ] ?? ''
                                            )
                                        ) ?>
                                    </span>

                                </div>

                            </div>

                        </div>

                    </section>

                    <!-- Approved rescheduling rules -->

                    <aside class="surface-card policy-card">

                        <div class="card-heading">

                            <span class="card-eyebrow">
                                Scheduling Policy
                            </span>

                            <h2 class="card-title">
                                Before You Change
                            </h2>

                        </div>

                        <div class="card-body">

                            <div class="policy-item">

                                <span class="policy-number">
                                    01
                                </span>

                                <div>
                                    <h3>
                                        Same Doctor
                                    </h3>

                                    <p>
                                        Your appointment stays
                                        with the original doctor.
                                    </p>
                                </div>

                            </div>

                            <div class="policy-item">

                                <span class="policy-number">
                                    02
                                </span>

                                <div>
                                    <h3>
                                        Two-Hour Deadline
                                    </h3>

                                    <p>
                                        Changes must be requested
                                        at least two hours before
                                        the original appointment.
                                    </p>
                                </div>

                            </div>

                            <div class="policy-item">

                                <span class="policy-number">
                                    03
                                </span>

                                <div>
                                    <h3>
                                        Available Times Only
                                    </h3>

                                    <p>
                                        Choose another free slot.
                                        The server checks availability
                                        again before saving.
                                    </p>
                                </div>

                            </div>

                        </div>

                    </aside>

                </div>

                <!-- Alternative appointment times -->

                <div class="section-heading">

                    <span class="section-eyebrow">
                        Select a New Time
                    </span>

                    <h2 class="section-title">
                        Available Alternatives
                    </h2>

                    <p class="section-description">
                        All appointment times are shown
                        in Afghanistan local time.
                    </p>

                </div>

                <section class="surface-card">

                    <div class="card-heading slots-heading">

                        <div>

                            <span class="card-eyebrow">
                                Appointment Options
                            </span>

                            <h2 class="card-title">
                                Choose Your New Appointment
                            </h2>

                            <p class="card-description">
                                Select one of the doctor's
                                available appointment times.
                            </p>

                        </div>

                        <span class="count-badge">
                            <?= $totalSlots ?>
                            <?= $totalSlots === 1
                                ? 'available time'
                                : 'available times' ?>
                        </span>

                    </div>

                    <?php if (
                        !empty($slotsByDate) &&
                        $availableDates > 1
                    ): ?>

                        <!-- Optional client-side date filter -->

                        <div class="slots-toolbar">

                            <p>
                                Browse <?= $availableDates ?>
                                available dates.
                            </p>

                            <div class="filter-wrap">

                                <label for="dateFilter">
                                    Date
                                </label>

                                <select
                                    id="dateFilter"
                                    class="date-select"
                                >

                                    <option value="all">
                                        All Available Dates
                                    </option>

                                    <?php foreach (
                                        $slotsByDate as
                                        $date => $dateSlots
                                    ): ?>

                                        <option
                                            value="<?= rescheduleEscape(
                                                $date
                                            ) ?>"
                                        >
                                            <?= rescheduleEscape(
                                                rescheduleDate($date)
                                            ) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>

                        </div>

                    <?php endif; ?>

                    <div class="slots-content">

                        <?php if (empty($availableSlots)): ?>

                            <div class="empty-state">

                                <h3>
                                    No Alternative Times Available
                                </h3>

                                <p>
                                    This doctor currently has
                                    no alternative bookable
                                    appointment slots.
                                    Your existing appointment
                                    has not been changed.
                                </p>

                            </div>

                        <?php else: ?>

                            <?php foreach (
                                $slotsByDate as
                                $date => $dateSlots
                            ): ?>

                                <div
                                    class="date-group"
                                    data-slot-date="<?= rescheduleEscape(
                                        $date
                                    ) ?>"
                                >

                                    <div class="date-heading">

                                        <h3>
                                            <?= rescheduleEscape(
                                                rescheduleDate($date)
                                            ) ?>
                                        </h3>

                                        <span>
                                            <?= count($dateSlots) ?>
                                            <?= count($dateSlots) === 1
                                                ? 'time available'
                                                : 'times available' ?>
                                        </span>

                                    </div>

                                    <div class="slots-grid">

                                        <?php foreach (
                                            $dateSlots as $slot
                                        ): ?>

                                            <article class="slot-card">

                                                <span class="slot-time">

                                                    <?= rescheduleEscape(
                                                        rescheduleTime(
                                                            $slot[
                                                                'start_time'
                                                            ]
                                                        )
                                                    ) ?>

                                                    –

                                                    <?= rescheduleEscape(
                                                        rescheduleTime(
                                                            $slot[
                                                                'end_time'
                                                            ]
                                                        )
                                                    ) ?>

                                                </span>

                                                <span class="slot-caption">
                                                    Available appointment time
                                                </span>

                                                <!-- Existing secure action -->

                                                <form
                                                    class="slot-form"
                                                    method="POST"
                                                    action="../actions/appointment_reschedule.php"
                                                    onsubmit="return confirm('Reschedule your appointment to this time?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="appointment_id"
                                                        value="<?= (int) $selectedAppointment[
                                                            'appointment_id'
                                                        ] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="availability_id"
                                                        value="<?= (int) $slot[
                                                            'availability_id'
                                                        ] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="appointment_date"
                                                        value="<?= rescheduleEscape(
                                                            $slot[
                                                                'appointment_date'
                                                            ]
                                                        ) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="start_time"
                                                        value="<?= rescheduleEscape(
                                                            $slot[
                                                                'start_time'
                                                            ]
                                                        ) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= rescheduleEscape(
                                                            $csrfToken
                                                        ) ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="select-button"
                                                        aria-label="Reschedule appointment to <?= rescheduleEscape(
                                                            rescheduleDate(
                                                                $slot[
                                                                    'appointment_date'
                                                                ]
                                                            ) . ' at ' .
                                                            rescheduleTime(
                                                                $slot[
                                                                    'start_time'
                                                                ]
                                                            )
                                                        ) ?>"
                                                    >
                                                        Select New Time

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

                                                    </button>

                                                </form>

                                            </article>

                                        <?php endforeach; ?>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                            <p class="slots-note">
                                Your appointment will change
                                only after a successful
                                submission. If the selected
                                time becomes unavailable,
                                the server will reject the
                                request and preserve your
                                existing appointment.
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

<script>
document.addEventListener('DOMContentLoaded', function () {

    const dateFilter = document.getElementById('dateFilter');

    if (!dateFilter) {
        return;
    }

    const dateGroups = document.querySelectorAll(
        '[data-slot-date]'
    );

    dateFilter.addEventListener('change', function () {

        const selectedDate = dateFilter.value;

        dateGroups.forEach(function (group) {

            group.hidden = (
                selectedDate !== 'all' &&
                group.dataset.slotDate !== selectedDate
            );

        });

    });

});
</script>

</body>
</html>