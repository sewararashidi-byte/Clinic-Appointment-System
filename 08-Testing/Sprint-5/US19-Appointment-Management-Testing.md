# US-19 — Appointment Management Testing Report

**Project:** Clinic Appointment System  
**Sprint:** Sprint 5  
**User Story:** US-19 — Appointment Management  
**Testing Date:** November 1–2, 2026 
**Frontend Developer:** Sonbol Almas  
**Backend Developer:** Sewara Rashidi  
**Related Sequence Diagrams:** SD16 — Staff Appointment Booking; SD17 — Staff Appointment Cancellation; SD18 — Staff Appointment Rescheduling  
**Testing Status:** All 20 test cases reported as passed

## 1. Testing Objective

The objective of this testing activity was to verify that authorized clinic staff can manage patient appointments through booking, cancellation, and rescheduling operations.

Testing examined appointment availability, database persistence, prevention of double booking, cancellation restrictions, rescheduling deadlines, role-based authorization, CSRF protection, and appointment change logging.

The testing also verified the integration between the staff-facing frontend developed by Sonbol and the backend functionality developed by Sewara.

## 2. Requirements Under Test

Based on the Product Backlog and Product Owner clarifications, the following requirements were considered:

- Receptionists and administrators can view and manage clinic appointments.
- Authorized staff can book appointments on behalf of registered patients.
- Only active patients and doctors may be selected for new bookings.
- Appointment booking must use genuinely available future time slots.
- The system must prevent double booking.
- Staff can cancel eligible future appointments.
- A cancellation performed within 24 hours of the appointment requires a reason.
- Past and already cancelled appointments cannot be cancelled again.
- Successful cancellation must release the reserved appointment slot.
- Staff can reschedule eligible appointments to another available time.
- The existing two-hour rescheduling restriction must be enforced.
- Rescheduling must preserve the original patient and doctor.
- Invalid or unsuccessful requests must not incorrectly modify appointment records.
- Appointment changes must be recorded in the audit log.
- Patients, doctors, and unauthenticated users must not access staff-only appointment-management operations.
- Invalid CSRF tokens and invalid appointment identifiers must be rejected.

The application uses `Asia/Kabul` for appointment time calculations.

## 3. Testing Environment

**Application:** Clinic Appointment System  
**Backend:** PHP and MySQL/MariaDB  
**Local Server:** XAMPP  
**Frontend:** Bootstrap-based staff appointment-management interface  
**Browser:** Google Chrome  
**Database:** `clinic_appointment_system`  
**Application Timezone:** `Asia/Kabul`

**Relevant files:**

- `10-Source-Code/staff/appointments.php`
- `10-Source-Code/staff/appointment-book.php`
- `10-Source-Code/staff/appointment-cancel.php`
- `10-Source-Code/staff/appointment-reschedule.php`
- `10-Source-Code/actions/staff_appointment_book.php`
- `10-Source-Code/actions/staff_appointment_cancel.php`
- `10-Source-Code/actions/staff_appointment_reschedule.php`
- `10-Source-Code/includes/staff_appointment_repository.php`
- `10-Source-Code/includes/appointment_repository.php`
- `10-Source-Code/includes/doctor_repository.php`
- `10-Source-Code/config/auth.php`
- `10-Source-Code/config/database.php`
- `10-Source-Code/config/timezone.php`

**Relevant database tables:**

- `appointments`
- `users`
- `doctor_availability`
- `appointment_change_log`

The Sprint 5 database migration introduced the appointment change log required for recording staff appointment-management operations.

Testing was performed using fictional patient and doctor accounts in the local development environment.

## 4. Test Cases and Results

| Test ID | Test Scenario | Expected Result | Reported Result |
|---|---|---|---|
| TC-US19-01 | Log in as a receptionist and open Appointment Management | Appointment records display with correct patients, doctors, dates, times, and statuses | Pass |
| TC-US19-02 | Select an active doctor in the booking form | Genuine available future slots appear, or an appropriate no-availability message is displayed | Pass |
| TC-US19-03 | Book an available appointment for an active test patient | Booking succeeds, a confirmation message appears, and the appointment is saved | Pass |
| TC-US19-04 | Verify the new appointment and booking audit record in MySQL | Booking information and the associated staff action are recorded correctly | Pass |
| TC-US19-05 | Attempt to book an already occupied appointment slot | Conflicting booking is rejected and no duplicate confirmed appointment is created | Pass |
| TC-US19-06 | Attempt booking with an inactive patient or doctor | Invalid booking request is rejected | Pass |
| TC-US19-07 | Cancel a confirmed future appointment more than 24 hours before its start | Cancellation succeeds and the appointment status becomes `cancelled` | Pass |
| TC-US19-08 | Attempt staff cancellation within 24 hours without a reason | Request is rejected and the appointment remains confirmed | Pass |
| TC-US19-09 | Cancel an appointment within 24 hours with a valid reason | Cancellation succeeds and the reason is recorded | Pass |
| TC-US19-10 | Attempt to cancel an already cancelled or past appointment | Invalid cancellation is rejected | Pass |
| TC-US19-11 | Check availability after cancelling an eligible appointment | Previously reserved time becomes available for an eligible new booking | Pass |
| TC-US19-12 | Reschedule an eligible appointment to another available slot with the same doctor | Appointment date and time are updated successfully | Pass |
| TC-US19-13 | Attempt rescheduling inside the two-hour deadline | Rescheduling request is rejected | Pass |
| TC-US19-14 | Attempt rescheduling to an occupied slot | Conflicting slot is rejected and original appointment details remain unchanged | Pass |
| TC-US19-15 | Verify appointment information after successful rescheduling | Original patient and doctor remain unchanged, and the previous slot is released | Pass |
| TC-US19-16 | Check the appointment change log after booking, cancellation, and rescheduling | Correct appointment changes and staff actors are recorded | Pass |
| TC-US19-17 | Attempt to access staff appointment-management pages using patient and doctor accounts | Unauthorized access is denied | Pass |
| TC-US19-18 | Submit invalid CSRF tokens for booking, cancellation, and rescheduling | Invalid requests are rejected without modifying appointment data | Pass |
| TC-US19-19 | Log in as an administrator and perform permitted appointment-management operations | Administrator access and permitted actions work successfully | Pass |
| TC-US19-20 | Submit invalid appointment IDs and invalid appointment-management values | Invalid input is handled safely without unauthorized data modification | Pass |

## 5. Execution Summary

The development team reported successful completion of all 20 planned US-19 test cases.

During backend development, Sewara tested the staff booking and rescheduling functionality. A successful test booking was previously recorded as appointment #13, and its associated booking audit information was checked.

After frontend integration, Sonbol verified the Appointment Management page, doctor selection, and display of available appointment times.

Following completion of the Sprint 5 frontend integration, the team reported successful execution of the remaining appointment-management, security, validation, and database-integrity tests.

The reported results indicate that staff appointment-management operations follow the approved business rules and preserve appointment information when requests are rejected.

**Summary:**

- Total test cases: 20
- Reported passed: 20
- Reported failed: 0
- Outstanding test cases from this checklist: 0

These results are based on the development team's confirmations. Individual screenshots, HTTP responses, and database execution logs for every test case have not been independently reviewed in this report.

## 6. Database Verification

### 6.1 Appointment Booking

For successful staff booking, the expected database behavior is:

- A new appointment record is created.
- The correct patient ID and doctor ID are stored.
- The availability ID corresponds to the selected appointment slot.
- The appointment date and start/end times match the selected availability.
- The appointment status is recorded as `confirmed`.
- The same appointment slot cannot be assigned to another confirmed appointment.
- The booking action is recorded in the appointment change log.

The team reported successful booking and database-verification tests.

### 6.2 Appointment Cancellation

For successful staff cancellation, the expected database behavior is:

- The existing appointment ID remains unchanged.
- The appointment status changes from `confirmed` to `cancelled`.
- The reserved appointment time is released.
- The cancellation operation is recorded in the audit log.
- A required reason is recorded for staff cancellations performed within 24 hours.

For rejected cancellation requests, the original appointment must remain unchanged.

The team reported successful completion of the relevant cancellation tests.

### 6.3 Appointment Rescheduling

For successful staff rescheduling, the expected database behavior is:

- The appointment ID remains unchanged.
- The original patient ID remains unchanged.
- The original doctor ID remains unchanged.
- The availability ID reflects the newly selected slot.
- The appointment date and start/end times reflect the new slot.
- The appointment status remains `confirmed`.
- The previous appointment slot is released.
- The rescheduling operation is recorded in the appointment change log.

For invalid or unsuccessful rescheduling attempts, the original appointment details must remain unchanged.

The team reported successful completion of the relevant rescheduling and data-integrity tests.

### 6.4 Audit Logging

The `appointment_change_log` table is used to maintain a record of appointment-management changes.

The team reported that booking, cancellation, and rescheduling operations generated the expected audit records and identified the responsible staff account.



## 7. Defects and Observations

No unresolved failures were reported among the 20 completed US-19 test cases.



## 8. Conclusion

Based on the development team's reported results, all 20 planned US-19 Appointment Management test cases passed.

The tested functionality supports authorized staff appointment booking, cancellation, and rescheduling while enforcing appointment availability, time-based restrictions, prevention of double booking, and protection against unauthorized modifications.

The results also support correct integration between the frontend appointment-management pages and the backend PHP/MySQL implementation.

Appointment information was reported to remain consistent following successful operations and protected from unintended modification when invalid requests were submitted.

**US-19 Testing Result: Passed — development team reported.**

