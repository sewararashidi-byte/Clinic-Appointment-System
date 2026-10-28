
-- Sprint 4: US-09 Appointment Cancellation
-- and US-10 Appointment Rescheduling
--
-- Keep cancelled appointment history while
-- enforcing uniqueness for confirmed slots.
--


USE clinic_appointment_system;

ALTER TABLE appointments
    ADD COLUMN confirmed_slot TINYINT
        GENERATED ALWAYS AS (
            CASE
                WHEN status = 'confirmed' THEN 1
                ELSE NULL
            END
        ) PERSISTENT,

    DROP INDEX uq_doctor_appointment_slot,

    ADD UNIQUE INDEX uq_doctor_appointment_slot (
        doctor_id,
        appointment_date,
        start_time,
        confirmed_slot
    );
