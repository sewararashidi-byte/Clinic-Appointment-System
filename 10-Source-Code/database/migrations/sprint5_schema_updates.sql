
-- Sprint 5 database changes
-- US-17: Optional patient home address
-- US-18: Doctor consultation room
-- US-19: Appointment change audit log
-- US-20: Clinic information

ALTER TABLE users
ADD COLUMN home_address VARCHAR(255) NULL;

ALTER TABLE doctor_profiles
ADD COLUMN consultation_room VARCHAR(100) NULL;

CREATE TABLE IF NOT EXISTS appointment_change_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    appointment_id INT UNSIGNED NOT NULL,
    actor_user_id INT UNSIGNED NOT NULL,
    action_type ENUM(
        'book', 'cancel', 'reschedule'
    ) NOT NULL,
    reason VARCHAR(500) NULL,
    old_appointment_date DATE NULL,
    old_start_time TIME NULL,
    new_appointment_date DATE NULL,
    new_start_time TIME NULL,
    recorded_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_appointment (appointment_id),
    INDEX idx_actor (actor_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS clinic_information (
    id TINYINT UNSIGNED NOT NULL PRIMARY KEY,
    clinic_name VARCHAR(150) NOT NULL,
    clinic_address VARCHAR(255) NOT NULL,
    contact_phone VARCHAR(30) NOT NULL,
    opening_hours VARCHAR(255) NOT NULL,
    clinic_email VARCHAR(150) NULL,
    available_services TEXT NULL,
    clinic_description TEXT NULL,
    updated_by INT UNSIGNED NULL,
    updated_at TIMESTAMP NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_clinic_single_record
        CHECK (id = 1),
    CONSTRAINT fk_clinic_updated_by
        FOREIGN KEY (updated_by)
        REFERENCES users(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
