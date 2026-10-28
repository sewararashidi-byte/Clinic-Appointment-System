
# SD09 — Appointment Cancellation: Sequence Diagram Plan

**Project:** Clinic Appointment System
**Sprint:** Sprint 4 (October 27–28, 2026)
**Developer:** Sewara Rashidi
**User Story:** US-09 — Appointment Cancellation
**Functional Requirement:** FR-10
**Diagram:** SD09-US09-Appointment-Cancellation.mmd


## 1. Purpose

Model how an authenticated patient cancels an existing appointment while preserving the appointment in their history and respecting the Product Owner's approved cancellation policy.

## 2. User Story and Acceptance Criteria

**User Story:**

As a patient, I want to cancel my appointment so that I can inform the clinic when I can no longer attend.

**Acceptance Criteria:**

1. The patient can view appointments eligible for cancellation.
2. The patient can select an appointment to cancel.
3. The system updates the appointment status after cancellation.

**Product Owner Clarification:**

Patients may cancel appointments up to two hours before the scheduled appointment. Cancellations within two hours are not permitted through the system and require contacting the clinic.

Cancelled appointments must remain visible in appointment history.

## 3. Preconditions

- The patient is authenticated with the patient role.
- The patient has a valid CSRF token.
- The appointment belongs to the authenticated patient.
- The appointment has confirmed status.
- At least two hours remain before the appointment starts.
- Appointment time is evaluated using Asia/Kabul time.

## 4. Participants

| Participant | Responsibility |
|---|---|
| Patient | Requests appointment cancellation |
| Patient Appointments Page | Displays appointments and submits cancellation request |
| appointment_cancel.php | Validates authentication, request method, appointment ID, and CSRF token |
| appointment_repository.php | Implements cancellation rules and database transaction |
| MariaDB | Retrieves, locks, and updates appointment records |

## 5. Main Success Scenario

1. The patient opens the My Appointments page.
2. The patient selects Cancel Appointment.
3. The frontend submits the appointment ID and CSRF token through HTTP POST.
4. The cancellation action validates the authenticated patient's session.
5. The action checks the request method, appointment ID, and CSRF token.
6. The action calls cancelPatientAppointment().
7. The repository confirms that the appointment belongs to the patient.
8. The repository begins a database transaction.
9. The doctor's record is locked using FOR UPDATE.
10. The appointment record is locked using FOR UPDATE.
11. The repository verifies that the appointment is confirmed.
12. The system checks the two-hour cancellation deadline using Asia/Kabul time.
13. The appointment status changes from confirmed to cancelled.
14. The transaction is committed.
15. The patient is redirected to the appointments page with a success message.
16. The cancelled appointment remains in appointment history.

## 6. Alternative and Exception Scenarios

### 6.1 Invalid Authentication

If the user is not authenticated as a patient, access is rejected.

### 6.2 Invalid Request

If the request method, appointment ID, or CSRF token is invalid, the action rejects the request without modifying the database.

### 6.3 Appointment Not Found

If the appointment does not exist or belongs to another patient, cancellation is rejected.

### 6.4 Appointment Already Cancelled

If the appointment status is cancelled, the system rejects repeated cancellation.

### 6.5 Cancellation Deadline Passed

If less than two hours remain before the appointment, the system rejects online cancellation and instructs the patient to contact the clinic.

### 6.6 Database Error

If a database operation fails, the transaction is rolled back and the appointment remains unchanged.

## 7. Database and Implementation Mapping

**Table:** appointments

**Relevant fields:**

- id
- patient_id
- doctor_id
- appointment_date
- start_time
- status
- confirmed_slot

**Database behavior:**

- The appointment record is not deleted.
- The status changes from confirmed to cancelled.
- The generated confirmed_slot value becomes NULL.
- The previously confirmed slot can become available again.
- The unique constraint continues to prevent duplicate confirmed appointment starts.

**Related migration:**

US09-US10-confirmed-slot-uniqueness.sql

**Timezone configuration:**

config/timezone.php — Asia/Kabul

**No additional database changes are required for US-09.**
