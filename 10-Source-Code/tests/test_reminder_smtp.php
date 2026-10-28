
<?php

/**
 * US-13 - SMTP sandbox smoke test.
 * Uses fictional appointment information.
 * Does not modify the database.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__ .
    '/../includes/appointment_reminder_mailer.php';

$testDate = (new DateTimeImmutable(
    'tomorrow',
    new DateTimeZone('Asia/Kabul')
))->format('Y-m-d');

$appointment = [
    'patient_name' => 'Test Patient',
    'patient_email' => 'test-patient@example.com',
    'doctor_name' => 'Test Doctor',
    'appointment_date' => $testDate,
    'start_time' => '10:00:00'
];

try {
    sendAppointmentReminderEmail($appointment);
    echo "Test email submitted to Mailtrap sandbox." .
        PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, "SMTP test failed. Check configuration." .
        PHP_EOL);
    exit(1);
}
