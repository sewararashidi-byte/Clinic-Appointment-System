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