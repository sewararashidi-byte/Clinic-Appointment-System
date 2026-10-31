
<?php

/**
 * Sprint 5 — US-19: Staff Appointment Management
 *
 * Read-only appointment retrieval.
 * The calling page must verify staff authorization.
 */

function getStaffAppointments(mysqli $conn): array
{
    $sql = "
        SELECT
            a.id,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.status,
            p.full_name AS patient_name,
            d.full_name AS doctor_name
        FROM appointments AS a
        INNER JOIN users AS p
            ON p.id = a.patient_id
        INNER JOIN users AS d
            ON d.id = a.doctor_id
        ORDER BY
            a.appointment_date DESC,
            a.start_time DESC,
            a.id DESC
        LIMIT 100
    ";

    $result = $conn->query($sql);

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Sprint 5 — US-19: Staff Appointment Cancellation
 *
 * Authorized receptionists and administrators may cancel
 * a future, confirmed appointment on behalf of a patient.
 *
 * Within 24 hours of the appointment, a reason is required.
 * Every successful cancellation is recorded in the audit log.
 *
 * The calling action must also enforce POST and CSRF checks.
 */
function cancelStaffAppointment(
    mysqli $conn,
    int $appointmentId,
    int $staffUserId,
    string $reason = ''
): bool {

    if ($appointmentId <= 0 || $staffUserId <= 0) {
        throw new DomainException(
            'Invalid cancellation request.'
        );
    }

    $reason = trim($reason);

    if (strlen($reason) > 500) {
        throw new DomainException(
            'Cancellation reason is too long.'
        );
    }

    // Verify the staff member's role and active status.
    $staffSql = "
        SELECT role, account_status
        FROM users
        WHERE id = ?
        LIMIT 1
    ";

    $staffStmt = $conn->prepare($staffSql);
    $staffStmt->bind_param('i', $staffUserId);
    $staffStmt->execute();

    $staff = $staffStmt->get_result()->fetch_assoc();
    $staffStmt->close();

    if (
        !$staff ||
        !in_array(
            $staff['role'],
            ['receptionist', 'administrator'],
            true
        ) ||
        $staff['account_status'] !== 'active'
    ) {
        throw new DomainException(
            'You are not authorized to cancel appointments.'
        );
    }

    // Identify the appointment's doctor.
    $lookupSql = "
        SELECT doctor_id
        FROM appointments
        WHERE id = ?
        LIMIT 1
    ";

    $lookupStmt = $conn->prepare($lookupSql);
    $lookupStmt->bind_param('i', $appointmentId);
    $lookupStmt->execute();

    $lookup = $lookupStmt->get_result()->fetch_assoc();
    $lookupStmt->close();

    if (!$lookup) {
        throw new DomainException(
            'Appointment not found.'
        );
    }

    $doctorId = (int) $lookup['doctor_id'];

    $conn->begin_transaction();

    try {

        // Use the same doctor lock as booking and rescheduling.
        $doctorSql = "
            SELECT id
            FROM users
            WHERE id = ?
              AND role = 'doctor'
            FOR UPDATE
        ";

        $doctorStmt = $conn->prepare($doctorSql);
        $doctorStmt->bind_param('i', $doctorId);
        $doctorStmt->execute();

        $doctor = $doctorStmt
            ->get_result()
            ->fetch_assoc();

        $doctorStmt->close();

        if (!$doctor) {
            throw new DomainException(
                'Doctor record not found.'
            );
        }

        // Lock and recheck the appointment.
        $appointmentSql = "
            SELECT
                appointment_date,
                start_time,
                status
            FROM appointments
            WHERE id = ?
              AND doctor_id = ?
            FOR UPDATE
        ";

        $appointmentStmt = $conn->prepare(
            $appointmentSql
        );

        $appointmentStmt->bind_param(
            'ii',
            $appointmentId,
            $doctorId
        );

        $appointmentStmt->execute();

        $appointment = $appointmentStmt
            ->get_result()
            ->fetch_assoc();

        $appointmentStmt->close();

        if (!$appointment) {
            throw new DomainException(
                'Appointment not found.'
            );
        }

        if ($appointment['status'] !== 'confirmed') {
            throw new DomainException(
                'Only confirmed appointments can be cancelled.'
            );
        }

        $appointmentStart = new DateTimeImmutable(
            $appointment['appointment_date'] . ' ' .
            $appointment['start_time']
        );

        $now = new DateTimeImmutable('now');

        // Past or already-started appointments cannot be cancelled.
        if ($now >= $appointmentStart) {
            throw new DomainException(
                'Past or started appointments cannot be cancelled.'
            );
        }

        $deadline = $appointmentStart
            ->modify('-24 hours');

        // Late cancellations require a recorded reason.
        if ($now > $deadline && $reason === '') {
            throw new DomainException(
                'A cancellation reason is required within 24 hours.'
            );
        }

        // Update the appointment.
        $updateSql = "
            UPDATE appointments
            SET status = 'cancelled'
            WHERE id = ?
              AND doctor_id = ?
              AND status = 'confirmed'
        ";

        $updateStmt = $conn->prepare($updateSql);

        $updateStmt->bind_param(
            'ii',
            $appointmentId,
            $doctorId
        );

        $updateStmt->execute();

        $updatedRows = $updateStmt->affected_rows;
        $updateStmt->close();

        if ($updatedRows !== 1) {
            throw new RuntimeException(
                'Unable to cancel this appointment.'
            );
        }

        // Store who cancelled the appointment and why.
        $logSql = "
            INSERT INTO appointment_change_log (
                appointment_id,
                actor_user_id,
                action_type,
                reason,
                old_appointment_date,
                old_start_time
            )
            VALUES (?, ?, 'cancel', ?, ?, ?)
        ";

        $oldDate = $appointment['appointment_date'];
        $oldStart = $appointment['start_time'];
        $logReason = $reason !== '' ? $reason : null;

        $logStmt = $conn->prepare($logSql);

        $logStmt->bind_param(
            'iisss',
            $appointmentId,
            $staffUserId,
            $logReason,
            $oldDate,
            $oldStart
        );

        $logStmt->execute();
        $logStmt->close();

        // Both changes must succeed together.
        $conn->commit();

        return true;

    } catch (Throwable $e) {

        $conn->rollback();

        throw $e;
    }
}
