
-- =====================================================
-- Sprint 3 - US-04: Patient Profile Management
-- Database Migration
-- Developer: Sewara Rashidi
-- =====================================================

USE clinic_appointment_system;

-- Make age optional and add patient profile fields.
-- Existing user records will be preserved.

ALTER TABLE users
    MODIFY COLUMN age TINYINT UNSIGNED NULL,
    ADD COLUMN blood_type VARCHAR(5) NULL AFTER phone,
    ADD COLUMN disease_type VARCHAR(255) NULL AFTER blood_type,
    ADD COLUMN close_relatives_count SMALLINT UNSIGNED NULL
        AFTER disease_type;
