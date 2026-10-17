<?php

require_once '../config/auth.php';
require_once '../config/database.php';

requireRole(['doctor']);

/*
 * This action only accepts form submissions
 * using the POST method.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

/*
 * Get the logged-in doctor's ID
 * from the session.
 */
$doctorId = (int) $_SESSION['user_id'];

/*
 * Get availability information
 * submitted by the doctor.
 */
$availabilityDate = trim($_POST['availability_date'] ?? '');
$startTime = trim($_POST['start_time'] ?? '');
$endTime = trim($_POST['end_time'] ?? '');

/*
 * Sprint 2 default appointment duration
 * confirmed by the Product Owner.
 */
$slotDuration = 30;

/*
 * Validate required availability fields.
 */
if (
    empty($availabilityDate) ||
    empty($startTime) ||
    empty($endTime)
) {
    $_SESSION['availability_error'] =
        'Please enter the availability date, start time, and end time.';

    header('Location: ../dashboards/doctor-dashboard.php');
    exit;
}

/*
 * Validate the date format.
 */
$dateObject = DateTime::createFromFormat(
    'Y-m-d',
    $availabilityDate
);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $availabilityDate
) {
    $_SESSION['availability_error'] =
        'Please enter a valid availability date.';

    header('Location: ../dashboards/doctor-dashboard.php');
    exit;
}

/*
 * The start time must be earlier than the end time.
 */
if ($startTime >= $endTime) {
    $_SESSION['availability_error'] =
        'Start time must be earlier than end time.';

    header('Location: ../dashboards/doctor-dashboard.php');
    exit;
}

/*
 * Insert the doctor's availability
 * using a prepared statement.
 */
$stmt = $conn->prepare(
    "INSERT INTO doctor_availability
        (
            doctor_id,
            availability_date,
            start_time,
            end_time,
            slot_duration_minutes
        )
     VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "isssi",
    $doctorId,
    $availabilityDate,
    $startTime,
    $endTime,
    $slotDuration
);

/*
 * Execute the insert and store
 * a success or error message.
 */
if ($stmt->execute()) {
    $_SESSION['availability_success'] =
        'Availability added successfully.';
} else {
    $_SESSION['availability_error'] =
        'Unable to add availability. Please try again.';
}

$stmt->close();
$conn->close();

/*
 * Temporary Sprint 2 redirect.
 * We will redirect to the availability page
 * after Sonbol's UI is integrated.
 */
header('Location: ../dashboards/doctor-dashboard.php');
exit;