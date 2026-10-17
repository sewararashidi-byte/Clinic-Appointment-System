<?php

require_once '../config/auth.php';
require_once '../config/database.php';

requireRole(['doctor']);

/*
 * This action only accepts POST requests.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$doctorId = (int) $_SESSION['user_id'];

$availabilityId = (int) ($_POST['availability_id'] ?? 0);
$availabilityDate = trim($_POST['availability_date'] ?? '');
$startTime = trim($_POST['start_time'] ?? '');
$endTime = trim($_POST['end_time'] ?? '');

/*
 * Validate the availability record ID.
 */
if ($availabilityId <= 0) {
    $_SESSION['availability_error'] =
        'Invalid availability record.';

    header('Location: ../dashboards/doctor-dashboard.php');
    exit;
}

/*
 * Validate required fields.
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
 * Validate the date.
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
 * Start time must be earlier than end time.
 */
if ($startTime >= $endTime) {
    $_SESSION['availability_error'] =
        'Start time must be earlier than end time.';

    header('Location: ../dashboards/doctor-dashboard.php');
    exit;
}

/*
 * Check that this availability record
 * belongs to the logged-in doctor.
 */
$checkStmt = $conn->prepare(
    "SELECT id
     FROM doctor_availability
     WHERE id = ? AND doctor_id = ?"
);

$checkStmt->bind_param(
    "ii",
    $availabilityId,
    $doctorId
);

$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows !== 1) {
    $checkStmt->close();
    $conn->close();

    $_SESSION['availability_error'] =
        'Availability record not found or access denied.';

    header('Location: ../dashboards/doctor-dashboard.php');
    exit;
}

$checkStmt->close();

/*
 * Update the doctor's availability.
 */
$stmt = $conn->prepare(
    "UPDATE doctor_availability
     SET availability_date = ?,
         start_time = ?,
         end_time = ?
     WHERE id = ? AND doctor_id = ?"
);

$stmt->bind_param(
    "sssii",
    $availabilityDate,
    $startTime,
    $endTime,
    $availabilityId,
    $doctorId
);

if ($stmt->execute()) {
    $_SESSION['availability_success'] =
        'Availability updated successfully.';
} else {
    $_SESSION['availability_error'] =
        'Unable to update availability. Please try again.';
}

$stmt->close();
$conn->close();

header('Location: ../dashboards/doctor-dashboard.php');
exit;