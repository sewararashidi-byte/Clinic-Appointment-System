
<?php

/**
 * Sprint 4 - US-13: Appointment Reminder Mailer
 *
 * Sends appointment reminder emails using PHPMailer.
 * SMTP credentials must come from environment variables.
 */

require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send a reminder for an eligible appointment.
 *
 * The calling service must verify that the appointment
 * is still confirmed and record the delivery result.
 */
function sendAppointmentReminderEmail(array $appointment): void
{
    $smtpHost = trim((string) getenv('CLINIC_SMTP_HOST'));
    $smtpUser = trim((string) getenv('CLINIC_SMTP_USER'));
    $smtpPassword = (string) getenv('CLINIC_SMTP_PASSWORD');
    $fromEmail = trim((string) getenv('CLINIC_SMTP_FROM_EMAIL'));

    $portInput = getenv('CLINIC_SMTP_PORT') ?: '587';
    $security = strtolower(
        trim((string) (getenv('CLINIC_SMTP_SECURITY') ?: 'tls'))
    );

    $smtpPort = filter_var(
        $portInput,
        FILTER_VALIDATE_INT,
        [
            'options' => [
                'min_range' => 1,
                'max_range' => 65535
            ]
        ]
    );

    if (
        $smtpHost === '' ||
        $smtpUser === '' ||
        $smtpPassword === '' ||
        !filter_var($fromEmail, FILTER_VALIDATE_EMAIL) ||
        $smtpPort === false ||
        !in_array($security, ['tls', 'ssl'], true)
    ) {
        throw new RuntimeException(
            'Email sending is not configured correctly.'
        );
    }

    $patientEmail = (string) ($appointment['patient_email'] ?? '');
    $patientName = trim((string) ($appointment['patient_name'] ?? ''));
    $doctorName = trim((string) ($appointment['doctor_name'] ?? ''));

    $appointmentDate = (string) (
        $appointment['appointment_date'] ?? ''
    );

    $startTime = (string) ($appointment['start_time'] ?? '');

    if (
        !filter_var($patientEmail, FILTER_VALIDATE_EMAIL) ||
        $patientName === '' ||
        $doctorName === ''
    ) {
        throw new DomainException(
            'Appointment reminder recipient information is invalid.'
        );
    }

    $start = DateTimeImmutable::createFromFormat(
        '!Y-m-d H:i:s',
        $appointmentDate . ' ' . $startTime,
        new DateTimeZone('Asia/Kabul')
    );

    if ($start === false) {
        throw new DomainException(
            'Appointment date or time is invalid.'
        );
    }

    $formattedDate = $start->format('l, F j, Y');
    $formattedTime = $start->format('g:i A');

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Timeout = 15;

    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPassword;
    $mail->Port = $smtpPort;

    $mail->SMTPSecure = $security === 'ssl'
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;

    $mail->CharSet = PHPMailer::CHARSET_UTF8;

    $mail->setFrom(
        $fromEmail,
        'Clinic Appointment System'
    );

    $mail->addAddress(
        $patientEmail,
        $patientName
    );

    $mail->isHTML(false);



    $mail->Subject = 'Reminder: Your Clinic Appointment';

    $mail->Body =
        "Hello {$patientName},\n\n" .
        "This is a reminder of your upcoming clinic appointment.\n\n" .
        "Doctor: {$doctorName}\n" .
        "Date: {$formattedDate}\n" .
        "Time: {$formattedTime} (Afghanistan Time)\n\n" .
        "If you need to cancel or reschedule, please visit " .
        "the Clinic Appointment System.\n\n" .
        "Thank you,\n" .
        "Clinic Appointment System";

    $mail->send();
}
