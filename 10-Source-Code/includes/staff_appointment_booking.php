
<?php

/**
 * Sprint 5 — US-19
 * Staff Appointment Booking Backend
 *
 * Allows authorized staff to book appointments
 * on behalf of existing patients.
 */

require_once __DIR__ . '/doctor_repository.php';
require_once __DIR__ . '/appointment_repository.php';

function bookStaffAppointmentForPatient(
    mysqli $conn,
    int $staffUserId,
    int $patientId,
    int $doctorId,
    int $availabilityId,
    string $appointmentDate,
    string $startTime
): int {

    // Validate submitted identifiers and date/time format.
    if (
        $staffUserId <= 0 ||
        $patientId <= 0 ||
        $doctorId <= 0 ||
        $availabilityId <= 0 ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointmentDate) ||
        !preg_match('/^\d{2}:\d{2}:\d{2}$/', $startTime)
    ) {
        throw new DomainException(
            'Invalid appointment booking request.'
        );
    }

    $conn->begin_transaction();

    try {

        // Verify the staff member is authorized.
        $staffSql = "
            SELECT id
            FROM users
            WHERE id = ?
              AND role IN ('receptionist', 'administrator')
              AND account_status = 'active'
            LIMIT 1
        ";

        $staffStmt = $conn->prepare($staffSql);
        $staffStmt->bind_param('i', $staffUserId);
        $staffStmt->execute();

        $staff = $staffStmt->get_result()->fetch_assoc();
        $staffStmt->close();

        if (!$staff) {
            throw new DomainException(
                'You are not authorized to book appointments.'
            );
        }

        // Verify the selected patient.
        $patientSql = "
            SELECT id
            FROM users
            WHERE id = ?
              AND role = 'patient'
              AND account_status = 'active'
            LIMIT 1
        ";

        $patientStmt = $conn->prepare($patientSql);
        $patientStmt->bind_param('i', $patientId);
        $patientStmt->execute();

        $patient = $patientStmt->get_result()->fetch_assoc();
        $patientStmt->close();

        if (!$patient) {
            throw new DomainException(
                'The selected patient is not available.'
            );
        }

        // Lock the doctor to prevent concurrent booking.
        $doctorSql = "
            SELECT id
            FROM users
            WHERE id = ?
              AND role = 'doctor'
              AND account_status = 'active'
            FOR UPDATE
        ";

        $doctorStmt = $conn->prepare($doctorSql);
        $doctorStmt->bind_param('i', $doctorId);
        $doctorStmt->execute();

        $doctor = $doctorStmt->get_result()->fetch_assoc();
        $doctorStmt->close();

        if (!$doctor) {
            throw new DomainException(
                'The selected doctor is not available.'
            );
        }

        // Reuse existing doctor availability logic.
        $availabilityRecords = getDoctorAvailability(
            $conn,
            $doctorId
        );

        // Reuse existing slot validation and conflict checks.
        $availableSlots = getAvailableAppointmentSlots(
            $conn,
            $doctorId,
            $availabilityRecords
        );

        $selectedSlot = null;

        foreach ($availableSlots as $slot) {

            if (
                (int) $slot['availability_id'] === $availabilityId &&
                $slot['appointment_date'] === $appointmentDate &&
                $slot['start_time'] === $startTime
            ) {
                $selectedSlot = $slot;
                break;
            }
        }

        if ($selectedSlot === null) {
            throw new DomainException(
                'This appointment slot is unavailable.'
            );
        }

        // Use the validated end time from the server.
        $endTime = $selectedSlot['end_time'];

        // Create the confirmed appointment.
        $insertSql = "
            INSERT INTO appointments (
                patient_id,
                doctor_id,
                availability_id,
                appointment_date,
                start_time,
                end_time,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
        ";

        $insertStmt = $conn->prepare($insertSql);

        $insertStmt->bind_param(
            'iiisss',
            $patientId,
            $doctorId,
            $availabilityId,
            $appointmentDate,
            $startTime,
            $endTime
        );

        $insertStmt->execute();

        $appointmentId = (int) $conn->insert_id;

        $insertStmt->close();

        // Record who booked this appointment.
        $logSql = "
            INSERT INTO appointment_change_log (
                appointment_id,
                actor_user_id,
                action_type,
                new_appointment_date,
                new_start_time
            )
            VALUES (?, ?, 'book', ?, ?)
        ";

        $logStmt = $conn->prepare($logSql);

        $logStmt->bind_param(
            'iiss',
            $appointmentId,
            $staffUserId,
            $appointmentDate,
            $startTime
        );

        $logStmt->execute();
        $logStmt->close();

        // Save booking and audit log together.
        $conn->commit();

        return $appointmentId;

    } catch (Throwable $e) {

        $conn->rollback();

        throw $e;
    }
}
