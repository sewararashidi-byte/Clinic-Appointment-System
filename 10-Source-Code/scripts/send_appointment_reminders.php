
<?php

/**
 * Sprint 4 - US-13: Appointment Reminder Runner
 *
 * Run from the command line only.
 *
 * --check : List the number of eligible reminders.
 * --send  : Process and send reminders.
 */



// Never allow this script to run through a web browser.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden');
}

$mode = $argv[1] ?? '--check';

if (
    count($argv) > 2 ||
    !in_array($mode, ['--check', '--send'], true)
) {
    fwrite(
        STDERR,
        "Usage: php send_appointment_reminders.php " .
        "[--check|--send]\n"
    );

    exit(2);
}

require_once __DIR__ . '/../config/timezone.php';
require_once __DIR__ . '/../config/database.php';

require_once __DIR__ .
    '/../includes/appointment_reminder_repository.php';

try {

    if ($mode === '--check') {

        // Safe, read-only verification.
        $candidates = getAppointmentReminderCandidates(
            $conn
        );

        echo "Reminder candidates: " .
            count($candidates) .
            PHP_EOL;

        echo "No emails were sent." . PHP_EOL;

    } else {

        // Never attempt real delivery without SMTP settings.
        $requiredSettings = [
            'CLINIC_SMTP_HOST',
            'CLINIC_SMTP_USER',
            'CLINIC_SMTP_PASSWORD',
            'CLINIC_SMTP_FROM_EMAIL'
        ];

        foreach ($requiredSettings as $setting) {

            $value = getenv($setting);

            if (
                $value === false ||
                trim($value) === ''
            ) {
                throw new RuntimeException(
                    'Missing SMTP setting: ' . $setting
                );
            }
        }

        require_once __DIR__ .
            '/../includes/appointment_reminder_processor.php';

        $results = processAppointmentReminders(
            $conn
        );

        echo "Sent: " . $results['sent'] . PHP_EOL;
        echo "Failed: " . $results['failed'] . PHP_EOL;
        echo "Skipped: " . $results['skipped'] . PHP_EOL;
    }

} catch (Throwable $e) {

    error_log(
        'Reminder runner error: ' . $e->getMessage()
    );

    fwrite(
        STDERR,
        "Reminder runner failed. Check the PHP error log.\n"
    );

    exit(1);

} finally {

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}
