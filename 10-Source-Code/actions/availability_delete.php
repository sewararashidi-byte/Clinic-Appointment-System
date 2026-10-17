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
 * Check that the availability record belongs
 * to the currently logged-in doctor.
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
 * Delete the doctor's availability.
 *
 * Sprint 2 note:
 * Once appointment booking is implemented,
 * this action must also check whether the
 * availability contains an existing booking
 * before deletion is allowed.
 */
$stmt = $conn->prepare(
    "DELETE FROM doctor_availability
     WHERE id = ? AND doctor_id = ?"
);

$stmt->bind_param(
    "ii",
    $availabilityId,
    $doctorId
);

if ($stmt->execute()) {
    $_SESSION['availability_success'] =
        'Availability deleted successfully.';
} else {
    $_SESSION['availability_error'] =
        'Unable to delete availability. Please try again.';
}

$stmt->close();
$conn->close();

header('Location: ../dashboards/doctor-dashboard.php');
exit;