
<?php

require_once __DIR__ . '/../config/timezone.php';
/**
 * US-07: Generate appointment slots from doctor availability.
 *
 * This function creates candidate slots only.
 * Booked slots will be excluded in the next step.
 */
function generateAppointmentSlots(array $availabilityRecords): array
{
    $slots = [];
    $seenSlots = [];

    $now = new DateTimeImmutable('now');

    foreach ($availabilityRecords as $record) {

        $duration = (int) $record['slot_duration_minutes'];

        if ($duration <= 0) {
            continue;
        }

        $date = $record['availability_date'];

        $start = new DateTimeImmutable(
            $date . ' ' . $record['start_time']
        );

        $end = new DateTimeImmutable(
            $date . ' ' . $record['end_time']
        );

        $interval = new DateInterval('PT' . $duration . 'M');

        while ($start < $end) {

            $slotEnd = $start->add($interval);

            // Do not create incomplete slots.
            if ($slotEnd > $end) {
                break;
            }

            // Exclude slots that have already started.
            if ($start > $now) {

                $slotDate = $start->format('Y-m-d');
                $slotStart = $start->format('H:i:s');

                // Avoid displaying duplicate slots.
                $slotKey = $slotDate . ' ' . $slotStart;

                if (!isset($seenSlots[$slotKey])) {

                    $slots[] = [
                        'availability_id' => (int) $record['availability_id'],
                        'appointment_date' => $slotDate,
                        'start_time' => $slotStart,
                        'end_time' => $slotEnd->format('H:i:s')
                    ];

                    $seenSlots[$slotKey] = true;
                }
            }

            $start = $slotEnd;
        }
    }

    return $slots;
}

/**
 * US-07: Retrieve appointment slots that are not already booked.
 *
 * Generate candidate slots from doctor availability,
 * then exclude slots overlapping confirmed appointments.
 */
function getAvailableAppointmentSlots(
    mysqli $conn,
    int $doctorId,
    array $availabilityRecords
): array {

    if ($doctorId <= 0) {
        return [];
    }

    // Generate candidate slots from availability ranges.
    $slots = generateAppointmentSlots($availabilityRecords);

    if (empty($slots)) {
        return [];
    }

    // Retrieve confirmed appointments for this doctor.
    $sql = "
        SELECT
            appointment_date,
            start_time,
            end_time
        FROM appointments
        WHERE doctor_id = ?
          AND status = 'confirmed'
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();

    $result = $stmt->get_result();
    $bookedAppointments = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    $availableSlots = [];

    foreach ($slots as $slot) {

        $alreadyBooked = false;

        foreach ($bookedAppointments as $booking) {

            // Only compare appointments on the same date.
            if (
                $slot['appointment_date']
                !== $booking['appointment_date']
            ) {
                continue;
            }

            // Check whether the time ranges overlap.
            if (
                $slot['start_time'] < $booking['end_time']
                &&
                $slot['end_time'] > $booking['start_time']
            ) {
                $alreadyBooked = true;
                break;
            }
        }

        if (!$alreadyBooked) {
            $availableSlots[] = $slot;
        }
    }

    return $availableSlots;
}


/**
 * US-11: Retrieve appointment confirmation details.
 *
 * A patient can only retrieve their own appointment.
 */
function getPatientAppointmentConfirmation(
    mysqli $conn,
    int $appointmentId,
    int $patientId
): ?array {

    if ($appointmentId <= 0 || $patientId <= 0) {
        return null;
    }

    $sql = "
        SELECT
            a.id AS appointment_id,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.status,
            u.full_name AS doctor_name
        FROM appointments a
        INNER JOIN users u
            ON u.id = a.doctor_id
        WHERE a.id = ?
          AND a.patient_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'ii',
        $appointmentId,
        $patientId
    );

    $stmt->execute();

    $appointment = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $appointment ?: null;
}


/**
 * Sprint 3 - US-16: Doctor Appointment Management
 *
 * Retrieve confirmed appointments belonging to
 * a specific doctor.
 *
 * This function only reads appointment information.
 * Doctors cannot update or cancel appointments here.
 */
function getDoctorAppointments(
    mysqli $conn,
    int $doctorId
): array {

    if ($doctorId <= 0) {
        return [];
    }

    $sql = "
        SELECT
            a.id AS appointment_id,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.status,
            u.full_name AS patient_name
        FROM appointments a
        INNER JOIN users u
            ON u.id = a.patient_id
        WHERE a.doctor_id = ?
          AND a.status = 'confirmed'
        ORDER BY
            a.appointment_date ASC,
            a.start_time ASC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'i',
        $doctorId
    );

    $stmt->execute();

    $appointments = $stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $appointments;
}


/**
 * Sprint 4 - US-12: Appointment History
 *
 * Retrieve appointments belonging to one patient.
 *
 * Includes confirmed and cancelled appointments,
 * allowing the interface to display upcoming
 * and previous appointment information.
 */
function getPatientAppointments(
    mysqli $conn,
    int $patientId
): array {

    if ($patientId <= 0) {
        return [];
    }

    $sql = "
        SELECT
            a.id AS appointment_id,
            a.doctor_id,
            d.full_name AS doctor_name,
            a.appointment_date,
            a.start_time,
            a.end_time,
            a.status
        FROM appointments a
        INNER JOIN users d
            ON d.id = a.doctor_id
        WHERE a.patient_id = ?
        ORDER BY
            a.appointment_date DESC,
            a.start_time DESC,
            a.id DESC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'i',
        $patientId
    );

    $stmt->execute();

    $appointments = $stmt
        ->get_result()
        ->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    return $appointments;
}

/**
 * Sprint 4 - US-09: Appointment Cancellation
 *
 * Cancel an appointment belonging to the authenticated patient.
 *
 * Rules:
 * - Only a confirmed appointment can be cancelled.
 * - Cancellation must occur at least 2 hours before
 *   the scheduled appointment.
 * - Keep the appointment record for patient history.
 * - Use the same doctor lock as appointment booking.
 */
function cancelPatientAppointment(
    mysqli $conn,
    int $appointmentId,
    int $patientId
): bool {

    if ($appointmentId <= 0 || $patientId <= 0) {
        throw new DomainException(
            'Invalid appointment request.'
        );
    }

    // Find the doctor associated with this patient's appointment.
    $lookupSql = "
        SELECT doctor_id
        FROM appointments
        WHERE id = ?
          AND patient_id = ?
        LIMIT 1
    ";

    $lookupStmt = $conn->prepare($lookupSql);
    $lookupStmt->bind_param('ii', $appointmentId, $patientId);
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

        // Serialize appointment changes with the booking handler.
        $doctorSql = "
            SELECT id
            FROM users
            WHERE id = ?
            FOR UPDATE
        ";

        $doctorStmt = $conn->prepare($doctorSql);
        $doctorStmt->bind_param('i', $doctorId);
        $doctorStmt->execute();

        $doctor = $doctorStmt->get_result()->fetch_assoc();
        $doctorStmt->close();

        if (!$doctor) {
            throw new DomainException(
                'Doctor record not found.'
            );
        }

        // Lock the patient's appointment before modifying it.
        $appointmentSql = "
            SELECT
                appointment_date,
                start_time,
                status
            FROM appointments
            WHERE id = ?
              AND patient_id = ?
              AND doctor_id = ?
            FOR UPDATE
        ";

        $appointmentStmt = $conn->prepare($appointmentSql);

        $appointmentStmt->bind_param(
            'iii',
            $appointmentId,
            $patientId,
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
                'This appointment cannot be cancelled.'
            );
        }

        // Apply Husna's approved two-hour cancellation rule.
        // This uses the application's configured PHP timezone.
        $appointmentStart = new DateTimeImmutable(
            $appointment['appointment_date'] . ' ' .
            $appointment['start_time']
        );

        $cancellationDeadline = $appointmentStart
            ->modify('-2 hours');

        $now = new DateTimeImmutable('now');

        if ($now > $cancellationDeadline) {
            throw new DomainException(
                'Online cancellation is no longer available. ' .
                'Please contact the clinic.'
            );
        }

        // Preserve the appointment in history.
        // The generated confirmed_slot becomes NULL,
        // releasing its uniqueness key.
        $updateSql = "
            UPDATE appointments
            SET status = 'cancelled'
            WHERE id = ?
              AND patient_id = ?
              AND status = 'confirmed'
        ";

        $updateStmt = $conn->prepare($updateSql);

        $updateStmt->bind_param(
            'ii',
            $appointmentId,
            $patientId
        );

        $updateStmt->execute();

        $updatedRows = $updateStmt->affected_rows;

        $updateStmt->close();

        if ($updatedRows !== 1) {
            throw new RuntimeException(
                'The appointment could not be cancelled.'
            );
        }

        $conn->commit();

        return true;

    } catch (Throwable $e) {

        $conn->rollback();

        throw $e;
    }
}

/**
 * Sprint 4 - US-10: Appointment Rescheduling
 *
 * Reschedule a patient's confirmed appointment.
 *
 * Product Owner rules:
 * - Same doctor only.
 * - At least 2 hours before original appointment.
 * - New slot must be available.
 * - Prevent double booking.
 * - Preserve original appointment if update fails.
 *
 * Requires getDoctorAvailability() from doctor_repository.php
 * to be loaded by the calling action.
 */
function reschedulePatientAppointment(
    mysqli $conn,
    int $appointmentId,
    int $patientId,
    int $availabilityId,
    string $newDate,
    string $newStartTime
): bool {

    if (
        $appointmentId <= 0 ||
        $patientId <= 0 ||
        $availabilityId <= 0 ||
        !preg_match('/^\d{4}-\d{2}-\d{2}$/', $newDate) ||
        !preg_match('/^\d{2}:\d{2}:\d{2}$/', $newStartTime)
    ) {
        throw new DomainException(
            'Invalid rescheduling request.'
        );
    }

    // Identify the appointment's original doctor.
    $lookupSql = "
        SELECT doctor_id
        FROM appointments
        WHERE id = ?
          AND patient_id = ?
        LIMIT 1
    ";

    $lookupStmt = $conn->prepare($lookupSql);

    $lookupStmt->bind_param(
        'ii',
        $appointmentId,
        $patientId
    );

    $lookupStmt->execute();

    $lookup = $lookupStmt
        ->get_result()
        ->fetch_assoc();

    $lookupStmt->close();

    if (!$lookup) {
        throw new DomainException(
            'Appointment not found.'
        );
    }

    $doctorId = (int) $lookup['doctor_id'];

    $conn->begin_transaction();

    try {

        // Same doctor lock used by the booking handler.
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

        $doctor = $doctorStmt
            ->get_result()
            ->fetch_assoc();

        $doctorStmt->close();

        if (!$doctor) {
            throw new DomainException(
                'The doctor is not available.'
            );
        }

        // Lock the original appointment record.
        $appointmentSql = "
            SELECT
                appointment_date,
                start_time,
                status
            FROM appointments
            WHERE id = ?
              AND patient_id = ?
              AND doctor_id = ?
            FOR UPDATE
        ";

        $appointmentStmt = $conn->prepare(
            $appointmentSql
        );

        $appointmentStmt->bind_param(
            'iii',
            $appointmentId,
            $patientId,
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
                'Only confirmed appointments can be rescheduled.'
            );
        }

        // Apply the 2-hour rule to the ORIGINAL appointment.
        // The appointment repository loads Asia/Kabul timezone.
        $originalStart = new DateTimeImmutable(
            $appointment['appointment_date'] . ' ' .
            $appointment['start_time']
        );

        $deadline = $originalStart->modify('-2 hours');

        $now = new DateTimeImmutable('now');

        if ($now > $deadline) {
            throw new DomainException(
                'Online rescheduling is no longer available. ' .
                'Please contact the clinic.'
            );
        }

        // The selected time must be different.
        if (
            $appointment['appointment_date'] === $newDate &&
            $appointment['start_time'] === $newStartTime
        ) {
            throw new DomainException(
                'Please choose a different appointment time.'
            );
        }

        // Retrieve fresh availability for the SAME doctor.
        $availabilityRecords = getDoctorAvailability(
            $conn,
            $doctorId
        );

        // Reuse confirmed-booking conflict prevention.
        $availableSlots = getAvailableAppointmentSlots(
            $conn,
            $doctorId,
            $availabilityRecords
        );

        $selectedSlot = null;

        foreach ($availableSlots as $slot) {

            if (
                (int) $slot['availability_id'] === $availabilityId &&
                $slot['appointment_date'] === $newDate &&
                $slot['start_time'] === $newStartTime
            ) {
                $selectedSlot = $slot;
                break;
            }
        }

        if ($selectedSlot === null) {
            throw new DomainException(
                'The selected appointment slot is unavailable.'
            );
        }

        // Always use the server-validated end time.
        $newEndTime = $selectedSlot['end_time'];

        // Update the EXISTING appointment in one transaction.
        $updateSql = "
            UPDATE appointments
            SET
                availability_id = ?,
                appointment_date = ?,
                start_time = ?,
                end_time = ?
            WHERE id = ?
              AND patient_id = ?
              AND doctor_id = ?
              AND status = 'confirmed'
        ";

        $updateStmt = $conn->prepare($updateSql);

        $updateStmt->bind_param(
            'isssiii',
            $availabilityId,
            $newDate,
            $newStartTime,
            $newEndTime,
            $appointmentId,
            $patientId,
            $doctorId
        );

        $updateStmt->execute();

        $updatedRows = $updateStmt->affected_rows;

        $updateStmt->close();

        if ($updatedRows !== 1) {
            throw new RuntimeException(
                'Unable to update the appointment.'
            );
        }

        $conn->commit();

        return true;

    } catch (Throwable $e) {

        $conn->rollback();

        throw $e;
    }
}
