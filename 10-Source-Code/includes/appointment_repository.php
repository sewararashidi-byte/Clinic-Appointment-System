
<?php

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
