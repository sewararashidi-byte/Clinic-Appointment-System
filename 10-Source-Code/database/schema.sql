CREATE DATABASE IF NOT EXISTS clinic_appointment_system
CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE clinic_appointment_system;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('patient', 'doctor', 'receptionist', 'administrator')
        NOT NULL DEFAULT 'patient',
    account_status ENUM('pending', 'active', 'rejected')
        NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- Sprint 2 - Doctor Professional Profile
-- =====================================================

CREATE TABLE IF NOT EXISTS doctor_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id INT UNSIGNED NOT NULL UNIQUE,

    specialty VARCHAR(100) NOT NULL,
    qualifications VARCHAR(255) NOT NULL,
    years_experience TINYINT UNSIGNED NOT NULL DEFAULT 0,
    biography TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_doctor_profile_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
);

-- =====================================================
-- Sprint 2 - Doctor Availability
-- =====================================================

CREATE TABLE IF NOT EXISTS doctor_availability (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    doctor_id INT UNSIGNED NOT NULL,

    availability_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,

    slot_duration_minutes SMALLINT UNSIGNED
        NOT NULL DEFAULT 30,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_availability_doctor
        FOREIGN KEY (doctor_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_doctor_availability_date
        (doctor_id, availability_date)
);