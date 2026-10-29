
# SD10 — Appointment Rescheduling: Sequence Diagram Plan

**Project:** Clinic Appointment System
**Sprint:** Sprint 4 (October 27–28, 2026)
**Developer:** Sewara Rashidi
**User Story:** US-10 — Appointment Rescheduling
**Functional Requirement:** FR-11
**Diagram:** SD10-US10-Appointment-Rescheduling.mmd


## 1. Purpose

Model how an authenticated patient reschedules an existing appointment to another available date and time with the same doctor.

The process must enforce the Product Owner's approved two-hour rescheduling restriction, verify doctor availability, prevent double booking, and preserve the original appointment if the operation fails.

## 2. User Story and Acceptance Criteria

**User Story:**

As a patient, I want to reschedule my appointment so that I can choose another available time when my plans change.

**Acceptance Criteria:**

1. The patient can select an existing appointment to reschedule.
2. The system displays available alternative appointment slots.
3. The system updates the appointment after a new slot is selected.

**Product Owner Clarification:**

Patients may reschedule appointments with the same doctor only.

They may select another available date and time.

Rescheduling is permitted up to two hours before the original appointment.

The system must verify doctor availability and prevent double booking.

## 3. Preconditions

- The patient is authenticated with the patient role.
- The patient has a valid CSRF token.
- The original appointment belongs to the authenticated patient.
- The original appointment has confirmed status.
- At least two hours remain before the original appointment starts.
- The patient selects a different date or time.
- The new slot belongs to the same doctor.
- The selected slot is available and does not conflict with another confirmed appointment.
- Appointment times are evaluated using Asia/Kabul time.

## 4. Participants

| Participant | Responsibility |
|---|---|
| Patient | Selects an appointment and requests rescheduling |
| Patient Appointments Page | Displays appointments and alternative slots; submits request |
| appointment_reschedule.php | Validates patient authentication, request data, and CSRF token |
| appointment_repository.php | Implements rescheduling rules, availability checks, and transaction |
| doctor_repository.php | Retrieves the doctor's current availability |
| MariaDB | Stores appointment and availability records and enforces database constraints |

## 5. Main Success Scenario

1. The patient opens the My Appointments page.
2. The patient selects an appointment to reschedule.
3. The interface displays alternative available appointment slots for the same doctor.
4. The patient selects a different available date and time.
5. The frontend submits the appointment ID, availability ID, new date, new start time, and CSRF token using HTTP POST.
6. The rescheduling action validates the patient session and role.
7. The action validates the request method, submitted values, and CSRF token.
8. The action calls reschedulePatientAppointment().
9. The repository verifies that the original appointment belongs to the authenticated patient.
10. The repository obtains the original doctor ID from the appointment record.
11. The repository begins a database transaction.
12. The doctor's record is locked using FOR UPDATE.
13. The original appointment record is locked using FOR UPDATE.
14. The repository checks that the appointment is confirmed.
15. The repository verifies that rescheduling occurs at least two hours before the original appointment.
16. The repository confirms that the selected date and time differ from the original appointment.
17. The system retrieves the original doctor's current availability.
18. The system generates and checks available slots, excluding conflicts with confirmed appointments.
19. The repository verifies that the requested slot matches an available slot for the same doctor.
20. The repository obtains the validated end time from the selected slot.
21. The system updates the existing appointment's availability ID, date, start time, and end time.
22. The transaction is committed.
23. The patient is redirected to the appointments page with a success message.
24. The updated appointment details are displayed to the patient.

## 6. Alternative and Exception Scenarios

### 6.1 Invalid Authentication

If the user is not authenticated as a patient, access is rejected.

### 6.2 Invalid Request or CSRF Token

If the request method, appointment ID, availability ID, date, time, or CSRF token is invalid, the action rejects the request.

No database changes are made.

### 6.3 Appointment Not Found or Not Owned

If the appointment does not exist or belongs to another patient, rescheduling is rejected.

### 6.4 Appointment Already Cancelled

If the original appointment is not confirmed, rescheduling is rejected.

### 6.5 Rescheduling Deadline Passed

If the request is made after the two-hour cutoff for the original appointment, the operation is rejected.

The patient is instructed to contact the clinic.

### 6.6 Same Date and Time Selected

If the patient selects the existing appointment date and time again, the system requests a different slot.

### 6.7 Different Doctor Attempted

The repository obtains the doctor ID from the existing appointment.

It does not accept a replacement doctor ID from the patient.

Therefore, the rescheduling operation is restricted to the original doctor.

### 6.8 Unavailable or Conflicting Slot

If the selected availability record or time slot is invalid, occupied, or no longer available, the operation is rejected.

The original appointment remains unchanged.

### 6.9 Database Error

If any database operation fails, the transaction is rolled back.

The original appointment date and time remain unchanged.

## 7. Database and Implementation Mapping

**Primary table:** appointments

**Relevant fields:**

- id
- patient_id
- doctor_id
- availability_id
- appointment_date
- start_time
- end_time
- status
- confirmed_slot

**Related table:** doctor_availability

**Database behavior:**

- The existing appointment record is updated.
- The appointment ID remains unchanged.
- The doctor ID remains unchanged.
- The appointment retains confirmed status.
- The availability ID and appointment date/time are updated.
- The server determines the validated end time.
- Database locking protects concurrent scheduling operations.
- The confirmed-slot unique constraint prevents duplicate confirmed appointment starts.
- Existing availability checks protect against overlapping confirmed appointments.
- The transaction is rolled back if validation or updating fails.

**Related migration:**

US09-US10-confirmed-slot-uniqueness.sql

**Timezone configuration:**

config/timezone.php — Asia/Kabul

**No additional database changes are required for US-10.**

**Note:** The existing implementation updates the current appointment record. It does not create a separate historical record of the original time slot.

## 8. Implementation Files

**Backend action:**

10-Source-Code/actions/appointment_reschedule.php

**Appointment repository:**

10-Source-Code/includes/appointment_repository.php

**Doctor availability repository:**

10-Source-Code/includes/doctor_repository.php

**Timezone configuration:**

10-Source-Code/config/timezone.php

**Frontend integration:**

Patient appointments and rescheduling interface — pending Sonbol's implementation.
