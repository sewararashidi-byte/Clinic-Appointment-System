# SD07 — Doctor Appointment Management Sequence Diagram

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Developer:** Sewara Rashidi  
**Date:** October 25, 2026  
**User Story:** US-16 — Doctor Appointment Management

## 1. Purpose

This sequence diagram models the process through which an authenticated doctor views their scheduled appointments in the Clinic Appointment System.

## 2. Requirements

Based on the Product Owner's clarification:

- Doctors can view their scheduled appointments.
- Appointments include the patient's name, date, and time.
- Doctors cannot directly edit or cancel appointments.
- Each doctor can access only their own appointment records.

## 3. Participants

- **Doctor:** Requests appointment information.
- **Doctor Appointments Page:** Displays the appointment schedule.
- **Authentication:** Verifies that the user has the doctor role.
- **Doctor Appointments Data Loader:** Retrieves the authenticated doctor's ID.
- **Appointment Repository:** Retrieves confirmed appointments.
- **MySQL Database:** Stores appointment and patient information.

## 4. Main Sequence

1. The doctor opens the Doctor Appointments page.
2. The system verifies the doctor's authentication and role.
3. The system retrieves the doctor's ID from the session.
4. The repository queries confirmed appointments belonging to that doctor.
5. The database returns matching appointment records.
6. The page displays appointment references, patient names, dates, and times.

## 5. Alternative Scenarios

- Unauthorized users are denied access.
- An invalid doctor session produces an error.
- If the doctor has no confirmed appointments, the interface displays an empty-state message.

## 6. Security Considerations

Appointment retrieval is restricted using the authenticated doctor's ID.

The feature is read-only. Doctors are not provided with appointment editing or cancellation operations.

## 7. Related Implementation

- `10-Source-Code/includes/appointment_repository.php`
- `10-Source-Code/includes/doctor_appointments_data.php`

## 8. Diagram Files

- `SD07-Doctor-Appointment-Management-Sprint3.mmd`
- `SD07-Doctor-Appointment-Management-Sprint3.png`




