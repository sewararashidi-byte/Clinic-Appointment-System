<?php
/**
 * Delete one unbooked availability period owned by the logged-in doctor.
 * Preserves historical appointments and verifies CSRF.
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['doctor']);

function availabilityDeleteResponse(string $type, string $message): void
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
    availabilityDeleteResponse(
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

if ($doctorId < 1 || !is_int($availabilityId)) {
    availabilityDeleteResponse('availability_error', 'Invalid availability record.');
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

    // Any linked appointment, regardless of status, prevents deletion.
    $bookingStmt = $conn->prepare(
        'SELECT id FROM appointments WHERE availability_id = ? LIMIT 1'
    );
    $bookingStmt->bind_param('i', $availabilityId);
    $bookingStmt->execute();
    $linkedAppointment = $bookingStmt->get_result()->fetch_assoc();
    $bookingStmt->close();

    if ($linkedAppointment) {
        throw new DomainException(
            'This availability is linked to an appointment and cannot be deleted.'
        );
    }

    $deleteStmt = $conn->prepare(
        'DELETE FROM doctor_availability WHERE id = ? AND doctor_id = ?'
    );
    $deleteStmt->bind_param('ii', $availabilityId, $doctorId);
    $deleteStmt->execute();
    $deleteStmt->close();

    $conn->commit();
    $transactionStarted = false;
    $conn->close();
    availabilityDeleteResponse('availability_success', 'Availability deleted successfully.');
} catch (DomainException $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }
    $conn->close();
    availabilityDeleteResponse('availability_error', $e->getMessage());
} catch (Throwable $e) {
    if ($transactionStarted) {
        $conn->rollback();
    }
    $conn->close();
    availabilityDeleteResponse(
        'availability_error',
        'Unable to delete availability. Please try again.'
    );
}
