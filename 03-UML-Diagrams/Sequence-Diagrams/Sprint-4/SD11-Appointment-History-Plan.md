
# SD11 — Appointment History: Sequence Diagram Plan

**Project:** Clinic Appointment System
**Sprint:** Sprint 4 (October 27–28, 2026)
**Developer:** Sewara Rashidi
**User Story:** US-12 — Appointment History
**Functional Requirement:** FR-13
**Diagram:** SD11-US12-Appointment-History.mmd


## 1. Purpose

Model how an authenticated patient retrieves and views their appointment history in the Clinic Appointment System.

The workflow must display the patient's own appointments, including upcoming, previous, and cancelled appointments, without exposing other patients' information.

## 2. User Story and Acceptance Criteria

**User Story:**

As a patient, I want to view my upcoming and previous appointments so that I can keep track of my clinic visits.

**Acceptance Criteria:**

1. The patient can view upcoming appointments.
2. The patient can view previous appointments.
3. The system displays relevant appointment details.

**Product Owner Clarification:**

Cancelled appointments must remain visible in the patient's appointment history with a clear Cancelled status.

Completed and upcoming appointments should also be displayed with appropriate statuses.

**Implementation Note:**

The existing appointments table supports confirmed and cancelled statuses.

A confirmed appointment can be classified as upcoming or past by comparing its scheduled date and time with the current time in Asia/Kabul.

A separate completed status is not currently stored in the database. Therefore, the interface must not claim that a past appointment was actually completed unless completion is recorded or otherwise verified.

## 3. Preconditions

- The patient is registered and authenticated.
- The authenticated user has the patient role.
- The patient session contains a valid user ID.
- The database connection is available.
- The application uses Asia/Kabul for appointment time comparisons.

## 4. Participants

| Participant | Responsibility |
|---|---|
| Patient | Requests to view appointment history |
| Patient Appointments Page | Requests data and displays appointment information |
| patient_appointments_data.php | Validates patient access and loads appointment records |
| appointment_repository.php | Retrieves appointments belonging to the patient |
| MariaDB | Stores appointment records and doctor information |

## 5. Main Success Scenario

1. The patient signs in to the Clinic Appointment System.
2. The patient opens the My Appointments page.
3. The page loads patient_appointments_data.php.
4. The data loader verifies the authenticated user's patient role.
5. The data loader retrieves the patient ID from the session.
6. The system opens the database connection.
7. The data loader calls getPatientAppointments().
8. The repository executes a prepared SELECT query using the authenticated patient ID.
9. MariaDB retrieves matching appointment records and related doctor names.
10. The database returns appointment details, including appointment ID, doctor name, appointment date, start time, end time, and stored status.
11. The repository returns the records to the data loader.
12. The data loader makes the appointment records available to the page.
13. The interface organizes the appointments into upcoming, previous, and cancelled views according to their dates and statuses.
14. The page displays the appointment details to the patient.

## 6. Alternative and Exception Scenarios

### 6.1 Unauthenticated User

If the user is not logged in, the system prevents access and follows the existing authentication flow.

### 6.2 Incorrect User Role

If a user does not have the patient role, access to the patient appointments page is rejected.

### 6.3 Invalid Patient Session

If the session does not contain a valid patient ID, the system does not retrieve another user's appointment records.

### 6.4 No Appointment Records

If the patient has never booked an appointment, the repository returns an empty array.

The page displays an appropriate empty-state message.

### 6.5 Cancelled Appointments

Cancelled appointments remain in the result set.

The page displays their status as Cancelled.

### 6.6 Past Appointments

Confirmed appointments with a scheduled start time in the past are classified as previous appointments for display.

A previous appointment is not automatically treated as medically completed.

### 6.7 Database Error

If appointment retrieval fails, the data loader catches the error, records a technical message in the server log, and prepares a user-friendly error message.

## 7. Database and Implementation Mapping

**Primary table:** appointments

**Related table:** users

**Appointment fields:**

- id
- patient_id
- doctor_id
- appointment_date
- start_time
- end_time
- status

**Returned application fields:**

- appointment_id
- doctor_id
- doctor_name
- appointment_date
- start_time
- end_time
- status

**Database behavior:**

- Appointment history is retrieved through a read-only SELECT query.
- The repository filters appointments by the authenticated patient ID.
- Doctor names are retrieved through a join with the users table.
- Cancelled appointments are not excluded.
- Appointment records are ordered by appointment date, start time, and appointment ID in descending order.
- No appointment records are inserted, updated, or deleted.

**Related function:**

getPatientAppointments(mysqli $conn, int $patientId): array

**Related files:**

10-Source-Code/includes/appointment_repository.php

10-Source-Code/includes/patient_appointments_data.php

10-Source-Code/config/timezone.php

**Frontend integration:**

10-Source-Code/patient/appointments.php — pending implementation or integration by Sonbol.

**Database changes:**

No additional database migration is required for US-12.

## 8. Security and Data Protection

- The authenticated patient ID comes from the server-side session.
- The patient cannot specify another patient's ID through the request.
- The database query uses a prepared statement.
- Only appointments belonging to the authenticated patient are returned.
- Appointment information should be HTML-escaped before display.
- The history page must not expose internal database error details.
