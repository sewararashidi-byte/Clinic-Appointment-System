<?php
/**
 * Update the logged-in doctor's own availability.
 * Protects appointments, checks overlap, and verifies CSRF.
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['doctor']);

function availabilityUpdateResponse(string $type, string $message): void
{
    $_SESSION[$type] = $message;
    header('Location: ../doctor/availability.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$submittedToken = $_POST['csrf_token'] ?? null;
$sessionToken = $_SESSION['availability_csrf_token'] ?? null;
if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    availabilityUpdateResponse(
        'availability_error',
        'Your session has changed. Reload the availability page and try again.'
    );
}

$doctorId = (int) ($_SESSION['user_id'] ?? 0);
$availabilityId = filter_var(
    $_POST['availability_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$availabilityDate = $_POST['availability_date'] ?? null;
$startTime = $_POST['start_time'] ?? null;
$endTime = $_POST['end_time'] ?? null;

if (
    $doctorId < 1 ||
    !is_int($availabilityId) ||
    !is_string($availabilityDate) ||
    !is_string($startTime) ||
    !is_string($endTime)
) {
    availabilityUpdateResponse('availability_error', 'Invalid availability information.');
}

$availabilityDate = trim($availabilityDate);
$startTime = trim($startTime);
$endTime = trim($endTime);

if ($availabilityDate === '' || $startTime === '' || $endTime === '') {
    availabilityUpdateResponse(
        'availability_error',
        'Please enter the availability date, start time, and end time.'
    );
}

$dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $availabilityDate);
if (!$dateObject || $dateObject->format('Y-m-d') !== $availabilityDate) {
    availabilityUpdateResponse(
        'availability_error',
        'Please enter a valid availability date.'
    );
}

$timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';
if (!preg_match($timePattern, $startTime) || !preg_match($timePattern, $endTime)) {
    availabilityUpdateResponse(
        'availability_error',
        'Please enter valid start and end times.'
    );
}

if (strlen($startTime) === 5) {
    $startTime .= ':00';
}
if (strlen($endTime) === 5) {
    $endTime .= ':00';
}

if ($startTime >= $endTime) {
    availabilityUpdateResponse(
        'availability_error',
        'Start time must be earlier than end time.'
    );
}

// Preserve the approved 30-minute slot duration.
$startSeconds = ((int) substr($startTime, 0, 2)) * 3600
    + ((int) substr($startTime, 3, 2)) * 60
    + (int) substr($startTime, 6, 2);
$endSeconds = ((int) substr($endTime, 0, 2)) * 3600
    + ((int) substr($endTime, 3, 2)) * 60
    + (int) substr($endTime, 6, 2);
if ($endSeconds - $startSeconds < 1800) {
    availabilityUpdateResponse(
        'availability_error',
        'An availability period must be at least 30 minutes.'
    );
}

$transactionStarted = false;

try {
    $conn->begin_transaction();
    $transactionStarted = true;

    $doctorStmt = $conn->prepare(
        "SELECT id FROM users
         WHERE id = ? AND role = 'doctor' AND account_status = 'active'
         FOR UPDATE"
    );
    $doctorStmt->bind_param('i', $doctorId);
    $doctorStmt->execute();
    $doctorRecord = $doctorStmt->get_result()->fetch_assoc();
    $doctorStmt->close();

    if (!$doctorRecord) {
        throw new DomainException('Doctor account not found or inactive.');
    }

    // Only the owner may modify the record.
    $ownershipStmt = $conn->prepare(
        'SELECT id FROM doctor_availability
         WHERE id = ? AND doctor_id = ?
         FOR UPDATE'
    );
    $ownershipStmt->bind_param('ii', $availabilityId, $doctorId);
    $ownershipStmt->execute();
    $availabilityRecord = $ownershipStmt->get_result()->fetch_assoc();
    $ownershipStmt->close();

    if (!$availabilityRecord) {
        throw new DomainException('Availability record not found or access denied.');
    }

    // Preserve the existing rule: confirmed bookings block updates.
    $bookingStmt = $conn->prepare(
        "SELECT id FROM appointments
         WHERE availability_id = ?
           AND doctor_id = ?
           AND status = 'confirmed'
         LIMIT 1"
    );
    $bookingStmt->bind_param('ii', $availabilityId, $doctorId);
    $bookingStmt->execute();
    $existingBooking = $bookingStmt->get_result()->fetch_assoc();
    $bookingStmt->close();

    if ($existingBooking) {
        throw new DomainException(
            'This availability contains confirmed appointments and cannot be updated until those bookings are resolved.'
        );
    }

    $overlapStmt = $conn->prepare(
        'SELECT id FROM doctor_availability
         WHERE doctor_id = ?
           AND availability_date = ?
           AND id <> ?
           AND start_time < ?
           AND end_time > ?
         LIMIT 1'
    );
    $overlapStmt->bind_param(
        'isiss',
        $doctorId,
        $availabilityDate,
        $availabilityId,
        $endTime,
        $startTime
    );
    $overlapStmt->execute();
    $overlap = $overlapStmt->get_result()->fetch_assoc();
    $overlapStmt->close();

    if ($overlap) {
        throw new DomainException(
            'This availability period overlaps an existing period.'
        );
    }

    $updateStmt = $conn->prepare(
        'UPDATE doctor_availability
         SET availability_date = ?, start_time = ?, end_time = ?
         WHERE id = ? AND doctor_id = ?'
    );
    $updateStmt->bind_param(
        'sssii',
        $availabilityDate,
        $startTime,
        $endTime,
        $availabilityId,
        $doctorId
    );
    $updateStmt->execute();
    $updateStmt->close();

    $conn->commit();
    $transactionStarted = false;
    $conn->close();
    availabilityUpdateResponse('availability_success', 'Availability updated successfully.');
} catch (DomainException $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }
    $conn->close();
    availabilityUpdateResponse('availability_error', $e->getMessage());
} catch (Throwable $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }
    $conn->close();
    availabilityUpdateResponse(
        'availability_error',
        'Unable to update availability. Please try again.'
    );
}
