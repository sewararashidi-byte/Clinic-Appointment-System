<?php
/**
 * Create doctor availability.
 * Doctor-only, CSRF-protected, 30-minute appointment intervals.
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['doctor']);

function availabilityCreateResponse(string $type, string $message): void
{
    $_SESSION[$type] = $message;
    header('Location: ../doctor/availability.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// Verify the token against the logged-in doctor's session.
$submittedToken = $_POST['csrf_token'] ?? null;
$sessionToken = $_SESSION['availability_csrf_token'] ?? null;

if (
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    availabilityCreateResponse(
        'availability_error',
        'Your session has changed. Reload the availability page and try again.'
    );
}

$doctorId = (int) ($_SESSION['user_id'] ?? 0);
$availabilityDate = $_POST['availability_date'] ?? null;
$startTime = $_POST['start_time'] ?? null;
$endTime = $_POST['end_time'] ?? null;
$slotDuration = 30; // Product Owner-approved appointment duration.

if (
    $doctorId < 1 ||
    !is_string($availabilityDate) ||
    !is_string($startTime) ||
    !is_string($endTime)
) {
    availabilityCreateResponse(
        'availability_error',
        'Invalid availability information.'
    );
}

$availabilityDate = trim($availabilityDate);
$startTime = trim($startTime);
$endTime = trim($endTime);

if ($availabilityDate === '' || $startTime === '' || $endTime === '') {
    availabilityCreateResponse(
        'availability_error',
        'Please enter the availability date, start time, and end time.'
    );
}

$dateObject = DateTimeImmutable::createFromFormat('!Y-m-d', $availabilityDate);
if (!$dateObject || $dateObject->format('Y-m-d') !== $availabilityDate) {
    availabilityCreateResponse(
        'availability_error',
        'Please enter a valid availability date.'
    );
}

// HTML time inputs normally submit HH:MM, but HH:MM:SS is also supported.
$timePattern = '/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/';
if (!preg_match($timePattern, $startTime) || !preg_match($timePattern, $endTime)) {
    availabilityCreateResponse(
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
    availabilityCreateResponse(
        'availability_error',
        'Start time must be earlier than end time.'
    );
}

// A new period must contain at least one 30-minute booking slot.
$startSeconds = ((int) substr($startTime, 0, 2)) * 3600
    + ((int) substr($startTime, 3, 2)) * 60
    + (int) substr($startTime, 6, 2);
$endSeconds = ((int) substr($endTime, 0, 2)) * 3600
    + ((int) substr($endTime, 3, 2)) * 60
    + (int) substr($endTime, 6, 2);

if ($endSeconds - $startSeconds < $slotDuration * 60) {
    availabilityCreateResponse(
        'availability_error',
        'An availability period must be at least 30 minutes.'
    );
}

$transactionStarted = false;

try {
    $conn->begin_transaction();
    $transactionStarted = true;

    // Matches the doctor-row locking approach used by appointment booking.
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

    $overlapStmt = $conn->prepare(
        "SELECT id FROM doctor_availability
         WHERE doctor_id = ?
           AND availability_date = ?
           AND start_time < ?
           AND end_time > ?
         LIMIT 1"
    );
    $overlapStmt->bind_param(
        'isss',
        $doctorId,
        $availabilityDate,
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

    $insertStmt = $conn->prepare(
        'INSERT INTO doctor_availability
            (doctor_id, availability_date, start_time, end_time, slot_duration_minutes)
         VALUES (?, ?, ?, ?, ?)'
    );
    $insertStmt->bind_param(
        'isssi',
        $doctorId,
        $availabilityDate,
        $startTime,
        $endTime,
        $slotDuration
    );
    $insertStmt->execute();
    $insertStmt->close();

    $conn->commit();
    $transactionStarted = false;
    $conn->close();

    availabilityCreateResponse(
        'availability_success',
        'Availability added successfully.'
    );
} catch (DomainException $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }
    $conn->close();
    availabilityCreateResponse('availability_error', $e->getMessage());
} catch (Throwable $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }
    $conn->close();
    availabilityCreateResponse(
        'availability_error',
        'Unable to add availability. Please try again.'
    );
}
