# US-10 — Appointment Rescheduling Testing Report

**Project:** Clinic Appointment System  
**Sprint:** Sprint 4  
**Testing Date:** October 29, 2026  
**User Story:** US-10 — Appointment Rescheduling  
**Functional Requirement:** FR-11  
**Frontend Developer:** Sonbol Almas  
**Backend Developer:** Sewara Rashidi  
**Related Sequence Diagram:** SD10 — Appointment Rescheduling  
**Testing Status:** All eight test cases reported as passed

## 1. Testing Objective

The objective of this testing activity was to verify that authenticated patients can reschedule their own confirmed appointments to another available time with the same doctor.

Testing also examined the Product Owner's two-hour rescheduling restriction, prevention of appointment conflicts, authorization checks, and preservation of the original appointment if a rescheduling operation fails.

## 2. Requirements Under Test

The Product Owner approved the following rules:

- A patient may reschedule their own confirmed appointment.
- The doctor must remain unchanged.
- The patient must select another available date and time.
- Rescheduling is permitted at least two hours before the original appointment.
- The system must prevent double booking.
- Invalid or unsuccessful requests must not incorrectly modify the original appointment.

The application uses `Asia/Kabul` for appointment time calculations.

## 3. Testing Environment

**Application:** Clinic Appointment System  
**Backend:** PHP and MariaDB  
**Local Server:** XAMPP  
**Frontend:** Bootstrap-based appointment rescheduling interface  
**Database:** `clinic_appointment_system`

**Relevant files:**

- `10-Source-Code/patient/appointments.php`
- `10-Source-Code/patient/reschedule.php`
- `10-Source-Code/actions/appointment_reschedule.php`
- `10-Source-Code/includes/appointment_repository.php`
- `10-Source-Code/includes/doctor_repository.php`

**Database migration:**

`US09-US10-confirmed-slot-uniqueness.sql`

The required four-column unique index was verified before testing.

## 4. Test Cases and Results

| Test ID | Test Scenario | Expected Result | Reported Result |
|---|---|---|---|
| TC-US10-01 | Reschedule an eligible appointment to an available slot | Appointment date/time updates successfully | Pass |
| TC-US10-02 | Verify doctor after rescheduling | Original doctor remains unchanged | Pass |
| TC-US10-03 | Attempt rescheduling inside the two-hour cutoff | Request is rejected | Pass |
| TC-US10-04 | Attempt rescheduling a cancelled appointment | Request is rejected | Pass |
| TC-US10-05 | Select an already-booked time slot | Conflicting slot is rejected | Pass |
| TC-US10-06 | Attempt rescheduling another patient's appointment | Unauthorized modification is prevented | Pass |
| TC-US10-07 | Submit an invalid CSRF token | Request is rejected | Pass |
| TC-US10-08 | Trigger an unsuccessful rescheduling request | Original appointment details remain unchanged | Pass |

## 5. Execution Summary

The development team reported that all eight planned US-10 test cases passed.

The reported results indicate that appointment rescheduling followed the approved same-doctor and two-hour policies, rejected invalid and conflicting requests, and preserved appointment data when an operation was unsuccessful.

**Summary:**

- Total test cases: 8
- Reported passed: 8
- Reported failed: 0
- Outstanding test cases from this checklist: 0

These results are based on the team's confirmation. Individual execution logs and screenshots have not been independently reviewed in this report.

## 6. Database Verification

For a successful rescheduling operation, the expected database behavior is:

- The appointment ID remains unchanged.
- The patient ID remains unchanged.
- The doctor ID remains unchanged.
- The availability ID reflects the selected alternative slot.
- The appointment date and start/end times reflect the new slot.
- The appointment status remains `confirmed`.

For a rejected or failed rescheduling request, the original appointment must remain unchanged.

The team reported successful completion of the relevant tests.

## 7. Test Evidence

Attach actual evidence where available:

- Screenshot of the original appointment details
- Screenshot of the alternative available slots
- Screenshot of the successful rescheduling result
- Screenshot of the updated appointment history
- phpMyAdmin evidence showing the new date/time and unchanged doctor
- Evidence of rejected conflicting, unauthorized, or invalid requests

**Evidence note:** Include only evidence from actual test execution. Exact test appointment IDs and database values were not supplied for this report.

## 8. Defects and Observations

No failures were reported for the eight executed test scenarios.

No additional defects were reported during this testing activity.

## 9. Conclusion

Based on the development team's reported results, all eight US-10 appointment rescheduling test cases passed.

The tested functionality supports same-doctor rescheduling, time-based eligibility, slot conflict prevention, authorization, and preservation of existing appointment data following rejected operations.

**US-10 Testing Result: Passed — team reported.**

Final Sprint acceptance remains subject to the team's overall integration review.