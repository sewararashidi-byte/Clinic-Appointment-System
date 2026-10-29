
# SD12 — Appointment Email Reminders: Sequence Diagram Plan

**Project:** Clinic Appointment System
**Sprint:** Sprint 4 (October 27–28, 2026)
**Developer:** Sewara Rashidi
**User Story:** US-13 — Appointment Notifications and Reminders
**Functional Requirement:** FR-14
**Diagram:** SD12-US13-Appointment-Email-Reminders.mmd

## 1. Purpose

Model how the Clinic Appointment System identifies upcoming confirmed appointments and sends email reminders to patients approximately 24 hours before their scheduled appointment time.

The workflow must prevent reminders for cancelled appointments, support updated appointment dates after rescheduling, track delivery attempts, and reduce duplicate email delivery.

The implementation uses PHPMailer for SMTP delivery, MariaDB for reminder tracking, and Windows Task Scheduler for periodic execution.

## 2. User Story and Acceptance Criteria

**User Story:**

As a patient, I want to receive appointment notifications and reminders so that I do not forget my scheduled clinic visits.

**Acceptance Criteria:**

1. The system identifies upcoming appointments.
2. The system sends appointment reminders to patients.
3. The reminder contains correct appointment information.

**Product Owner Clarification:**

- Reminders should be delivered through email.
- Reminders should preferably be sent 24 hours before the appointment.
- Cancelled appointments must not receive reminders.

**Implementation Decision:**

The application checks for confirmed appointments scheduled approximately 24 hours ahead.

The implemented reminder window is from 23 hours 55 minutes to 24 hours 5 minutes before appointment start.

The system is designed to run the reminder check every five minutes.

All appointment time calculations use Asia/Kabul.

## 3. Preconditions

- The patient has a valid email address.
- The appointment exists and has confirmed status.
- The appointment start time falls within the reminder window.
- The appointment has not already received a successful reminder for the same date and start time.
- The number of failed delivery attempts has not exceeded the configured retry limit.
- The database connection is available.
- PHPMailer is installed through Composer.
- SMTP settings are configured.
- A trusted command-line process invokes the reminder script.

## 4. Participants

| Participant | Responsibility |
|---|---|
| Windows Task Scheduler | Starts the reminder process periodically |
| PowerShell Launcher | Loads SMTP credentials and starts the PHP runner |
| Reminder Runner | Selects checking or sending mode |
| Reminder Repository | Retrieves eligible confirmed appointments |
| Reminder Processor | Revalidates appointments and manages reminder transactions |
| MariaDB | Stores appointments, users, and reminder tracking records |
| PHPMailer | Sends reminder emails through SMTP |
| SMTP Server | Accepts reminder messages for delivery or testing |

## 5. Main Success Scenario

1. Windows Task Scheduler starts the scheduled reminder task.
2. The PowerShell launcher loads the SMTP configuration from files stored outside the GitHub repository.
3. The launcher starts the PHP reminder runner.
4. The runner connects to the database.
5. In sending mode, the runner calls processAppointmentReminders().
6. The processor requests eligible appointments from getAppointmentReminderCandidates().
7. The repository queries the appointments, users, and appointment_reminders tables.
8. The query selects confirmed appointments within the 24-hour reminder window.
9. Previously sent or processing reminders are excluded.
10. Failed reminders may be retried up to the configured attempt limit.
11. The repository returns the eligible appointment records.
12. For each candidate, the processor begins a database transaction.
13. The processor locks the related doctor record.
14. The processor re-reads and locks the current appointment record.
15. The processor confirms that the appointment is still confirmed.
16. The processor verifies that the appointment date and time have not changed.
17. The processor verifies that the appointment still falls within the reminder window.
18. The processor checks for an existing reminder tracking record.
19. The system inserts or updates the reminder record with processing status.
20. The processor calls sendAppointmentReminderEmail().
21. PHPMailer prepares an email containing the patient's name, doctor's name, appointment date, and start time.
22. PHPMailer submits the email through the configured SMTP server.
23. If SMTP submission succeeds, the reminder status becomes sent.
24. The system records the successful sending time.
25. The database transaction is committed.
26. The runner reports the number of sent, failed, and skipped reminders.

## 6. Alternative and Exception Scenarios

### 6.1 Safe Checking Mode

When the runner uses --check, it retrieves eligible appointment candidates and displays their count.

No emails are sent and no reminder records are modified.

### 6.2 No Upcoming Appointments

If no confirmed appointments fall within the reminder window, the repository returns an empty array.

No reminders are sent.

### 6.3 Cancelled Appointment

If the appointment has cancelled status, it is excluded from the initial query.

If its status changes before processing, the processor revalidates the appointment and skips it.

### 6.4 Rescheduled Appointment

If an appointment's date or start time changes, the processor rejects any candidate containing outdated appointment details.

A reminder can become eligible for the new appointment date and start time.

Previously submitted emails cannot be recalled.

### 6.5 Reminder Already Sent

If a reminder has already been marked sent for the same appointment, date, and start time, the system excludes it from another normal delivery attempt.

### 6.6 Reminder Currently Processing

A reminder with processing status is not selected again by the candidate query.

The system avoids starting another ordinary attempt for that tracked appointment slot.

### 6.7 SMTP Failure

If the SMTP operation throws an error, the processor marks the reminder attempt as failed.

The reminder may be retried while it remains eligible and has fewer than three attempts.

### 6.8 Database Error

If a database operation fails, the transaction is rolled back and the error is logged.

### 6.9 Missing SMTP Configuration

If required SMTP environment variables are missing, the runner rejects sending mode before processing reminders.

### 6.10 Process Interruption

A failure between SMTP submission and database commit may leave the database without a confirmed sent record.

Therefore, the current implementation reduces duplicate reminders but does not guarantee exactly-once email delivery.

## 7. Database and Implementation Mapping

**Primary table:** appointments

**Related tables:**

- users
- appointment_reminders

**Relevant appointment fields:**

- id
- patient_id
- doctor_id
- appointment_date
- start_time
- end_time
- status

**Reminder tracking fields:**

- id
- appointment_id
- appointment_date
- start_time
- status
- attempt_count
- last_attempt_at
- sent_at
- last_error

**Reminder tracking statuses:**

- processing
- sent
- failed

**Database behavior:**

- Only confirmed appointments qualify for reminders.
- Reminder eligibility is calculated using Asia/Kabul time.
- The current appointment record is rechecked before sending.
- Reminder attempts are recorded in appointment_reminders.
- A unique constraint identifies each appointment/date/start-time reminder combination.
- Rescheduled appointment slots can have separate reminder tracking records.
- Failed reminders may be retried up to three attempts.
- Database transactions protect reminder tracking updates.
- SMTP delivery and database commits are separate systems; perfect exactly-once delivery is not guaranteed.





## 9. Security and Reliability

- The reminder runner rejects browser-based execution.
- SMTP credentials are not stored in the GitHub repository.
- The PowerShell launcher reads locally protected credentials.
- The system uses authenticated SMTP.
- Appointment status is checked again before email submission.
- Reminder tracking helps prevent duplicate attempts.
- The processor uses database transactions and row locking.
- Technical errors should be logged without exposing SMTP passwords.
- Mailtrap sandbox tests use fictional appointment information.
