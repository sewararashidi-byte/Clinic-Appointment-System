# US-13 — Appointment Email Reminders Testing Report

**Project:** Clinic Appointment System  
**Sprint:** Sprint 4  
**Testing date:** October 29, 2026  
**User story:** US-13 — Appointment Notifications and Reminders  
**Functional requirement:** FR-14  
**Backend developer:** Sewara Rashidi  
**Frontend/integration developer:** Sonbol Almas  
**Related sequence diagram:** SD12 — Appointment Email Reminders

## 1. Testing Objective

Verify that the Clinic Appointment System identifies eligible confirmed appointments approximately 24 hours before their scheduled start time, sends appointment reminder emails through PHPMailer and the Mailtrap Email Sandbox, records delivery attempts and outcomes, avoids inappropriate or duplicate reminders, retries eligible failed submissions, and supports automatic execution through Windows Task Scheduler.

## 2. Testing Environment

- **Backend:** PHP
- **Database:** MariaDB/MySQL through XAMPP and phpMyAdmin
- **Local development environment:** XAMPP on Windows
- **Email library:** PHPMailer
- **SMTP test service:** Mailtrap Email Sandbox (`sandbox.smtp.mailtrap.io`)
- **Scheduler:** Windows Task Scheduler, configured to repeat every five minutes
- **Scheduled launcher:** PowerShell `task.ps1`, supporting `-Mode check` and `-Mode send`
- **Application timezone:** `Asia/Kabul`
- **Reminder eligibility window:** Approximately 24 hours before an appointment (23 hours 55 minutes to 24 hours 5 minutes)

Mailtrap was used for fictional test messages; this report does not claim delivery to real patient inboxes.

## 3. Requirements Under Test

The system should:

1. Identify upcoming **confirmed** appointments that fall within the configured reminder window.
2. Include the fictional patient's name, doctor's name, appointment date, time, and relevant instructions in a reminder.
3. Send reminders through the configured SMTP service.
4. Exclude cancelled appointments and avoid obsolete appointment-slot details.
5. Prevent duplicate reminders for an already-sent appointment slot.
6. Record successful and failed sending attempts in `appointment_reminders`.
7. Retry eligible failed reminders without exceeding the configured maximum of three total attempts.
8. Execute reminder sending automatically according to the configured schedule.

## 4. Controlled Test Data and Safeguards

- **Fictional patient:** Reminder Test
- **Test email:** `reminder-test@example.com`
- **Test doctor:** Test Doctor
- **Patient ID:** 10
- **Doctor ID:** 3
- **Appointment ID:** 9
- **Original appointment:** October 31, 2026, **14:00–14:30**, confirmed

The appointment was temporarily moved into the 24-hour reminder window for controlled tests. Before the changes, the team checked for confirmed appointment conflicts. Testing used Mailtrap's sandbox, rather than a real patient mailbox. The appointment was restored to its original date and time following testing, as confirmed by the tester. The automated sending task was disabled after the scheduled-delivery test to avoid unattended test emails. Earlier reminder and failure records were retained as testing evidence.

## 5. Test Cases and Results

| Test ID | Test scenario | Actual result / evidence | Status |
|---|---|---|---|
| TC-US13-01 | Verify reminder database table | `appointment_reminders` table and expected fields confirmed during earlier testing. | Pass |
| TC-US13-02 | Run checker without sending | Eligible test appointment detected; terminal confirmed that no emails were sent. | Pass |
| TC-US13-03 | Send reminder via PHPMailer and Mailtrap | Test reminder received in the Mailtrap Email Sandbox. | Pass |
| TC-US13-04 | Verify reminder content | Fictional patient, test doctor, appointment date, and appointment time appeared in the sandbox message. | Pass |
| TC-US13-05 | Verify successful reminder tracking | `sent` status, one attempt, and sending timestamp recorded; see original record in Section 6. | Pass |
| TC-US13-06 | Restore controlled test appointment | Original October 31, 2026, 14:00–14:30 appointment restored; restoration was also repeated after later controlled tests. | Pass |
| TC-US13-07 | Verify scheduled safe-check | Windows Task Scheduler executed the check-only task and returned success (`0x0`). | Pass |
| TC-US13-08 | Verify cancelled appointments receive no reminder | Recorded as **Pass** in the earlier testing report; the detailed execution output was not included in the supplied report. | Pass (previously recorded) |
| TC-US13-09 | Verify reminder details after a controlled appointment-slot change | A changed appointment slot was identified, a new reminder was received in Mailtrap with the changed details, and the original appointment was restored. This was a controlled database slot-change test, not proof of the complete application-interface rescheduling workflow. | Pass |
| TC-US13-10 | Verify duplicate reminder prevention | Recorded as **Pass** in the earlier testing report; the detailed execution output was not included in the supplied report. | Pass (previously recorded) |
| TC-US13-11 | Verify SMTP failure and successful retry | With a deliberately invalid SMTP port, the first attempt recorded `Failed: 1`; after correcting SMTP security to `tls` and restoring the port, a subsequent controlled test recorded `Sent: 1`. Reminder **ID 6** ended as `sent`, with `attempt_count = 2`, `last_error = NULL`, and a sending timestamp. Mailtrap receipt was confirmed. | Pass |
| TC-US13-12 | Verify automatic scheduled sending | Task Scheduler automatically ran the sending task at **20:11:23** on October 29 with result **`0x0`**. Reminder **ID 7** for the October 30 **20:11:32** test slot was recorded `sent` at **20:11:29**, with one attempt. The tester confirmed the corresponding latest Mailtrap email and disabled the task afterward. | Pass |

**Test case numbering correction:** Two original entries both used `TC-US13-09`. They have been consolidated into one slot-change/details test rather than falsely counted as separate planned cases. The report therefore contains **12 unique test cases (TC-US13-01 through TC-US13-12)**.

## 6. Database Verification

### 6.1 Initial successful reminder

| Field | Verified value |
|---|---|
| `appointment_id` | 9 |
| `appointment_date` | `2026-10-30` |
| `start_time` | `10:23:54` |
| `status` | `sent` |
| `attempt_count` | 1 |
| `sent_at` | `2026-10-29 10:26:38` |

### 6.2 Controlled SMTP failure and recovery — TC-US13-11

The first controlled failure was recorded in reminder **ID 4** (`failed`, one attempt). A subsequent test initially recorded two failed attempts in reminder **ID 5** because the SMTP security environment variable contained a descriptive phrase rather than the code-supported value `tls` or `ssl`. The setting was corrected to `tls` before the successful recovery test.

| Field | Final successful retry record |
|---|---|
| `id` | 6 |
| `appointment_id` | 9 |
| `appointment_date` | `2026-10-30` |
| `start_time` | `19:44:16` |
| `status` | `sent` |
| `attempt_count` | 2 |
| `last_error` | `NULL` |
| `sent_at` | `2026-10-29 19:42:50` |

The failed and successful attempts were reported by the PHP runner. The same reminder record progressed to `sent` on attempt two, demonstrating successful retry within the configured eligibility window.

### 6.3 Automatic scheduled sending — TC-US13-12

| Field | Verified value |
|---|---|
| `id` | 7 |
| `appointment_id` | 9 |
| `appointment_date` | `2026-10-30` |
| `start_time` | `20:11:32` |
| `status` | `sent` |
| `attempt_count` | 1 |
| `sent_at` | `2026-10-29 20:11:29` |

This record corresponds to the automatic Task Scheduler run at **20:11:23** on October 29, 2026.

## 7. Email Verification

Mailtrap Email Sandbox received reminder messages addressed to the fictional test email account. The messages contained the expected patient and doctor information, date, time, and appointment-management guidance.

For **TC-US13-11**, the tester confirmed receipt of the successful retry email showing the controlled **19:44** appointment time. For **TC-US13-12**, the tester confirmed the newest sandbox message showing the automatically processed **20:11** appointment time. These observations support successful SMTP submission to the testing sandbox, not delivery to a real-world recipient.

## 8. Scheduling Verification

Windows Task Scheduler contained **Clinic Appointment Reminder Check**, configured with a trigger repeating every **five minutes**. The PowerShell launcher `task.ps1` securely loads SMTP credentials from files outside the GitHub repository and supports two explicit modes:

- **`-Mode check`**: Executes PHP with `--check`; reads eligible reminders without sending messages.
- **`-Mode send`**: Executes PHP with `--send`; processes and sends eligible reminders.

The task was initially in check-only mode. During TC-US13-12, the Task Scheduler action was configured to pass `-Mode send`. Windows automatically executed the task on **October 29, 2026, at 20:11:23** with last run result **`0x0`**. The corresponding reminder record was marked `sent` at **20:11:29** and the tester confirmed the resulting Mailtrap email.

**Post-test safety state:** The tester reported disabling the scheduled task after the controlled send. Automatic sending should remain disabled until the team intentionally approves and verifies the desired operating configuration. Because the configured task runs under a local Windows account and uses local credential storage, this is a **local test-environment demonstration**, not a production deployment.

## 9. Evidence and Traceability

Evidence seen or reported during the testing activity includes:

- VS Code terminal output for `--check` showing one candidate and no email sent.
- Terminal output for intentional SMTP failure (`Sent: 0`, `Failed: 1`, `Skipped: 0`) and successful retry (`Sent: 1`, `Failed: 0`, `Skipped: 0`).
- phpMyAdmin screenshots showing the failed reminder records and successful retry record **ID 6**, with `attempt_count = 2`.
- Task Scheduler screenshots showing the scheduled task and successful **20:11:23** execution (`0x0`).
- phpMyAdmin screenshot showing automatically sent reminder **ID 7**.
- Mailtrap screenshots and tester confirmation of the reminder emails.
- phpMyAdmin SQL restoration output and tester confirmation that the test appointment was returned to October 31, 2026, 14:00–14:30.

Store the screenshots in the Sprint 4 testing evidence location and reference them by filename if they are committed to GitHub. Detailed supporting output for **TC-US13-08** and **TC-US13-10** was not included in the supplied report and should be attached if available.

## 10. Test Summary

| Measurement | Result |
|---|---|
| Unique planned test cases | 12 |
| Recorded as passed | 12 |
| Failed test cases | 0 |
| Not tested | 0 |
| Overall result | **Passed in the controlled local/Mailtrap test environment**, with evidence limitations noted below |

An intentionally failed SMTP submission is an **expected negative-test event**, not a failed test case. Earlier intermediate configuration failures were resolved before the successful retry verification.

## 11. Limitations and Follow-up

- Mailtrap confirms acceptance in a sandbox, not delivery to real patients.
- TC-US13-09 verifies reminder behavior after a **controlled slot change**, not end-to-end appointment rescheduling through the application interface.
- The original supplied report recorded TC-US13-08 and TC-US13-10 as passed but lacked their detailed execution logs; retain their status as **previously recorded**, and attach supporting evidence if available.
- The automatic sender was demonstrated on the local Windows/XAMPP setup. Production scheduling, hosting, recipient handling, and operational monitoring were not assessed in this test.
- The Task Scheduler sending task was disabled after the controlled test to avoid unattended sandbox traffic.

## 12. Conclusion

The US-13 test suite records **12 passed test cases**, after consolidating the duplicate `TC-US13-09` entry. Tests confirmed eligible-appointment detection, PHPMailer/Mailtrap reminder submission, content verification, database tracking, controlled failure/retry recovery, and automatic scheduled sending in the local test environment. The fictional appointment was restored after testing and the automatic sending task was disabled.

These results provide evidence for the agreed US-13 reminder functionality **within the controlled test setup**. They do not establish production email delivery or constitute independent verification of other user stories' full workflows.

**US-13 Testing Result: Passed (controlled local/Mailtrap environment; limitations documented).**
