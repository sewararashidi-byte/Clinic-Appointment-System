
<?php

/**
 * Sprint 4 - US-13: Appointment Reminder Processor
 *
 * Connects reminder candidates, appointment
 * validation, email delivery and database tracking.
 */

require_once __DIR__ . '/../config/timezone.php';

require_once __DIR__ .
    '/appointment_reminder_repository.php';

require_once __DIR__ .
    '/appointment_reminder_mailer.php';

/**
 * Process eligible appointment email reminders.
 *
 * This function must only be called by a trusted
 * server-side scheduled task, not a public webpage.
 */
function processAppointmentReminders(
    mysqli $conn
): array {

    $counts = [
        'sent' => 0,
        'failed' => 0,
        'skipped' => 0
    ];

    $timezone = new DateTimeZone('Asia/Kabul');

    $candidates = getAppointmentReminderCandidates(
        $conn
    );

    foreach ($candidates as $candidate) {

        $appointmentId =
            (int) $candidate['appointment_id'];

        $doctorId =
            (int) $candidate['doctor_id'];

        if ($appointmentId <= 0 || $doctorId <= 0) {
            $counts['skipped']++;
            continue;
        }

        $conn->begin_transaction();

        try {

            // Use the same doctor lock as
            // booking, cancellation and rescheduling.
            $doctorStmt = $conn->prepare("
                SELECT id
                FROM users
                WHERE id = ?
                  AND role = 'doctor'
                FOR UPDATE
            ");

            $doctorStmt->bind_param('i', $doctorId);
            $doctorStmt->execute();

            $doctor = $doctorStmt
                ->get_result()
                ->fetch_assoc();

            $doctorStmt->close();

            if (!$doctor) {
                $conn->rollback();
                $counts['skipped']++;
                continue;
            }

            // Re-read and lock the current appointment.
            // This protects against sending reminders
            // using an old cancelled/rescheduled slot.
            $appointmentStmt = $conn->prepare("
                SELECT
                    a.id AS appointment_id,
                    a.doctor_id,
                    a.appointment_date,
                    a.start_time,
                    a.end_time,
                    a.status,
                    patient.full_name AS patient_name,
                    patient.email AS patient_email,
                    doctor.full_name AS doctor_name
                FROM appointments a
                INNER JOIN users patient
                    ON patient.id = a.patient_id
                INNER JOIN users doctor
                    ON doctor.id = a.doctor_id
                WHERE a.id = ?
                  AND a.doctor_id = ?
                FOR UPDATE
            ");

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

            if (
                !$appointment ||
                $appointment['status'] !== 'confirmed' ||
                $appointment['appointment_date'] !==
                    $candidate['appointment_date'] ||
                $appointment['start_time'] !==
                    $candidate['start_time']
            ) {
                $conn->rollback();
                $counts['skipped']++;
                continue;
            }

            $appointmentStart =
                DateTimeImmutable::createFromFormat(
                    '!Y-m-d H:i:s',
                    $appointment['appointment_date'] . ' ' .
                    $appointment['start_time'],
                    $timezone
                );

            $now = new DateTimeImmutable(
                'now',
                $timezone
            );

            if (
                $appointmentStart === false ||
                $appointmentStart <
                    $now->modify('+23 hours 55 minutes') ||
                $appointmentStart >
                    $now->modify('+24 hours 5 minutes')
            ) {
                $conn->rollback();
                $counts['skipped']++;
                continue;
            }

            // Lock an existing reminder tracking record,
            // if one exists for this appointment slot.
            $trackingStmt = $conn->prepare("
                SELECT id, status, attempt_count
                FROM appointment_reminders
                WHERE appointment_id = ?
                  AND appointment_date = ?
                  AND start_time = ?
                FOR UPDATE
            ");

            $trackingStmt->bind_param(
                'iss',
                $appointmentId,
                $appointment['appointment_date'],
                $appointment['start_time']
            );

            $trackingStmt->execute();

            $tracking = $trackingStmt
                ->get_result()
                ->fetch_assoc();

            $trackingStmt->close();

            if (
                $tracking &&
                (
                    $tracking['status'] !== 'failed' ||
                    (int) $tracking['attempt_count'] >= 3
                )
            ) {
                $conn->rollback();
                $counts['skipped']++;
                continue;
            }

            $attemptedAt = $now->format('Y-m-d H:i:s');

            if ($tracking) {

                $trackingId = (int) $tracking['id'];

                $claimStmt = $conn->prepare("
                    UPDATE appointment_reminders
                    SET status = 'processing',
                        attempt_count = attempt_count + 1,
                        last_attempt_at = ?,
                        last_error = NULL
                    WHERE id = ?
                ");

                $claimStmt->bind_param(
                    'si',
                    $attemptedAt,
                    $trackingId
                );

            } else {

                $claimStmt = $conn->prepare("
                    INSERT INTO appointment_reminders (
                        appointment_id,
                        appointment_date,
                        start_time,
                        status,
                        attempt_count,
                        last_attempt_at
                    )
                    VALUES (?, ?, ?, 'processing', 1, ?)
                ");

                $claimStmt->bind_param(
                    'isss',
                    $appointmentId,
                    $appointment['appointment_date'],
                    $appointment['start_time'],
                    $attemptedAt
                );
            }

            $claimStmt->execute();

            if (!$tracking) {
                $trackingId = (int) $conn->insert_id;
            }

            $claimStmt->close();

            // Keep the appointment locked during
            // delivery so cancellation/rescheduling
            // cannot change its status mid-send.
            try {

                sendAppointmentReminderEmail(
                    $appointment
                );

                $deliveryStatus = 'sent';
                $sentAt = (new DateTimeImmutable(
                    'now',
                    $timezone
                ))->format('Y-m-d H:i:s');

                $lastError = null;

            } catch (Throwable $mailError) {

                $deliveryStatus = 'failed';
                $sentAt = null;
                $lastError = 'Email delivery failed.';
            }

            $resultStmt = $conn->prepare("
                UPDATE appointment_reminders
                SET status = ?,
                    sent_at = ?,
                    last_error = ?
                WHERE id = ?
            ");

            $resultStmt->bind_param(
                'sssi',
                $deliveryStatus,
                $sentAt,
                $lastError,
                $trackingId
            );

            $resultStmt->execute();
            $resultStmt->close();

            $conn->commit();

            $counts[$deliveryStatus]++;

        } catch (Throwable $e) {

            $conn->rollback();
            throw $e;
        }
    }

    return $counts;
}
