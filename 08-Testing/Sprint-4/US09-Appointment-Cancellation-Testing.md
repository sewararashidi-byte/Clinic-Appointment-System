# US-09 — Appointment Cancellation Testing Report

**Project:** Clinic Appointment System  
**Sprint:** Sprint 4  
**Testing Date:** October 29, 2026  
**User Story:** US-09 — Appointment Cancellation  
**Functional Requirement:** FR-10  
**Frontend Developer:** Sonbol Almas  
**Backend Developer:** Sewara Rashidi  
**Related Sequence Diagram:** SD09 — Appointment Cancellation  
**Testing Status:** All six test cases reported as passed

## 1. Testing Objective

The objective of this testing activity was to verify that authenticated patients can cancel their own eligible appointments while respecting the Product Owner's approved cancellation policy.

The testing also aimed to confirm that cancelled appointment records remain available in appointment history and that previously reserved appointment slots can be released without violating database constraints.

## 2. Requirements Under Test

The Product Owner confirmed the following rules:

- Patients can cancel confirmed appointments at least two hours before the scheduled start time.
- Cancellation requests within the two-hour restriction are rejected.
- Patients cannot cancel appointments belonging to other patients.
- Cancelled appointments remain in appointment history.
- Already-cancelled appointments cannot be cancelled again.

The application uses the `Asia/Kabul` timezone for appointment calculations.

## 3. Testing Environment

**Application:** Clinic Appointment System  
**Backend:** PHP and MariaDB  
**Local Server:** XAMPP  
**Frontend:** Bootstrap-based patient appointments interface  
**Database:** `clinic_appointment_system`

**Relevant files:**

- `10-Source-Code/patient/appointments.php`
- `10-Source-Code/actions/appointment_cancel.php`
- `10-Source-Code/includes/appointment_repository.php`

**Database migration:**

`US09-US10-confirmed-slot-uniqueness.sql`

The four-column unique index was verified on the frontend developer's local database before cancellation testing.

## 4. Test Cases and Results

| Test ID | Test Scenario | Expected Result | Reported Result |
|---|---|---|---|
| TC-US09-01 | Cancel an eligible confirmed appointment | Appointment is cancelled successfully | Pass |
| TC-US09-02 | View cancelled appointment history | Cancelled appointment remains visible | Pass |
| TC-US09-03 | Verify database record after cancellation | Status becomes `cancelled` and `confirmed_slot` becomes `NULL` | Pass |
| TC-US09-04 | Attempt cancellation within the two-hour cutoff | Cancellation is rejected | Pass |
| TC-US09-05 | Attempt cancellation of another patient's appointment | Unauthorized request is rejected | Pass |
| TC-US09-06 | Attempt to cancel an already-cancelled appointment | Repeated cancellation is rejected | Pass |

## 5. Execution Summary

The development team reported that all six planned US-09 test cases passed.

The reported results indicate that the cancellation workflow satisfied the tested acceptance criteria, including appointment ownership, cancellation eligibility, database status updates, and retention of cancelled records.

**Summary:**

- Total test cases: 6
- Reported passed: 6
- Reported failed: 0
- Outstanding test cases from this checklist: 0

These results are based on the team's confirmation. Individual execution logs and screenshots have not been independently reviewed in this report.

## 6. Test Evidence

Attach actual evidence where available:

- Screenshot of the appointment before cancellation
- Screenshot of the cancellation result
- Screenshot of the cancelled appointment in history
- phpMyAdmin result showing `status = cancelled`
- phpMyAdmin result showing `confirmed_slot = NULL`
- Evidence of rejected invalid or unauthorized cancellation requests

**Evidence note:** Do not add fabricated screenshots or test values. Include only evidence captured during actual testing.

## 7. Defects and Observations

No test failures were reported for the six executed scenarios.

No additional defects were reported during this testing activity.

## 8. Conclusion

Based on the development team's reported results, the six US-09 cancellation test cases passed.

The tested functionality supports cancellation eligibility checks, patient authorization, preservation of appointment history, and appropriate database status changes.

**US-09 Testing Result: Passed — team reported.**

Final Sprint acceptance remains subject to the team's overall integration review.