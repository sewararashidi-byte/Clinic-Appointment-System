
<?php

/**
 * Sprint 4 - US-13: Appointment Email Reminders
 *
 * Find confirmed appointments that are due
 * for an email reminder approximately 24 hours
 * before their scheduled start time.
 */

require_once __DIR__ . '/../config/timezone.php';

/**
 * Retrieve appointments eligible for reminders.
 *
 * Intended to work with a scheduled task
 * that runs every 5 minutes.
 *
 * This function only READS data.
 * It does not send emails or change records.
 */
function getAppointmentReminderCandidates(
    mysqli $conn
): array {

    $now = new DateTimeImmutable(
        'now',
        new DateTimeZone('Asia/Kabul')
    );

    // Allow a small scheduling window around
    // the preferred 24-hour reminder time.
    $windowStart = $now
        ->modify('+23 hours 55 minutes')
        ->format('Y-m-d H:i:s');

    $windowEnd = $now
        ->modify('+24 hours 5 minutes')
        ->format('Y-m-d H:i:s');

    $sql = "
        SELECT
            a.id AS appointment_id,
            a.patient_id,
            a.doctor_id,
            a.appointment_date,
            a.start_time,
            a.end_time,

            patient.full_name AS patient_name,
            patient.email AS patient_email,

            doctor.full_name AS doctor_name

        FROM appointments a

        INNER JOIN users patient
            ON patient.id = a.patient_id

        INNER JOIN users doctor
            ON doctor.id = a.doctor_id

        LEFT JOIN appointment_reminders r
            ON r.appointment_id = a.id
            AND r.appointment_date = a.appointment_date
            AND r.start_time = a.start_time

        WHERE a.status = 'confirmed'

          AND TIMESTAMP(
              a.appointment_date,
              a.start_time
          ) BETWEEN ? AND ?


AND (
    r.id IS NULL
    OR (
        r.status = 'failed'
        AND r.attempt_count < 3
    )
)

        ORDER BY
            a.appointment_date ASC,
            a.start_time ASC,
            a.id ASC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        'ss',
        $windowStart,
        $windowEnd
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $appointments = $result->fetch_all(
        MYSQLI_ASSOC
    );

    $stmt->close();

    return $appointments;
}
