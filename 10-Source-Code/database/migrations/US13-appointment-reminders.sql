
-- Sprint 4 - US-13
-- Appointment Email Reminder Tracking
--
-- Records reminder attempts so the system
-- can track delivery and avoid duplicate
-- reminders for the same appointment slot.

USE clinic_appointment_system;

CREATE TABLE IF NOT EXISTS appointment_reminders (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    appointment_id INT UNSIGNED NOT NULL,

    appointment_date DATE NOT NULL,

    start_time TIME NOT NULL,

    status ENUM(
        'processing',
        'sent',
        'failed'
    ) NOT NULL DEFAULT 'processing',

    attempt_count INT UNSIGNED
        NOT NULL DEFAULT 0,

    last_attempt_at DATETIME NULL,

    sent_at DATETIME NULL,

    last_error VARCHAR(255) NULL,

    created_at TIMESTAMP
        DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_reminder_appointment
        FOREIGN KEY (appointment_id)
        REFERENCES appointments(id)
        ON DELETE RESTRICT,

    CONSTRAINT uq_appointment_reminder_slot
        UNIQUE (
            appointment_id,
            appointment_date,
            start_time
        )

) ENGINE=InnoDB;
