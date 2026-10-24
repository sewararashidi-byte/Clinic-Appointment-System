# US-11 — Appointment Confirmation: Complete Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Testing Dates:** October 23–24, 2026  
**Testers:** Sewara Rashidi and Sonbol Almas  
**User Story:** US-11 — Appointment Confirmation  
**Tasks:** T3.4 — Backend Confirmation Retrieval, T3.5 — Confirmation Interface, T3.6 — Confirmation Testing  
**Environment:** PHP, MySQL, XAMPP, Bootstrap, Web Browser, PHP CLI

---

## 1. Testing Objective

The objective of this testing activity was to verify that the Clinic Appointment System generates and displays accurate appointment confirmation information after a successful booking.

The tests also examined secure access to appointment information, database accuracy, frontend integration, and the handling of invalid appointment references and booking requests.

## 2. User Story and Acceptance Criteria

**US-11 — Appointment Confirmation**

As a patient, I want to receive appointment confirmation so that I know my appointment has been successfully scheduled.

**Acceptance Criteria:**

1. The system generates confirmation after successful booking.
2. The confirmation includes relevant appointment details.
3. The patient can view their appointment confirmation.

## 3. Test Environment

- **Frontend:** HTML and Bootstrap
- **Backend:** PHP
- **Database:** MySQL
- **Local Server:** XAMPP
- **Database Management:** phpMyAdmin
- **Testing Methods:** SQL queries, PHP CLI tests, syntax validation, and browser-based testing
- **Version Control:** Git and GitHub

Tests were performed using the developers' local application and database environments.

## 4. Developer Responsibilities

### Sewara Rashidi — Backend Developer

Sewara was responsible for:

- Implementing secure appointment confirmation retrieval.
- Updating the booking backend to preserve the newly created appointment ID.
- Creating the appointment confirmation data loader.
- Testing database queries and patient ownership filtering.
- Performing PHP syntax checks.
- Preparing backend testing documentation.

### Sonbol Almas — Frontend Developer

Sonbol was responsible for:

- Creating the Appointment Confirmation page.
- Displaying the information supplied by the backend.
- Connecting successful appointment bookings to the confirmation page.
- Testing the complete booking-to-confirmation workflow.
- Verifying frontend data against MySQL.
- Testing unauthorized access, invalid references, and invalid booking requests.

---

## 5. Sewara's Backend Testing

**Date:** October 23, 2026  
**Tester:** Sewara Rashidi  
**Task:** T3.4 — Backend Confirmation Retrieval

### 5.1 Backend Test Data

| Field | Value |
|---|---|
| Appointment ID | 5 |
| Patient ID | 8 |
| Doctor | Test Doctor |
| Appointment Date | October 23, 2026 |
| Start Time | 10:00 AM |
| End Time | 10:30 AM |
| Status | confirmed |

### 5.2 Backend Test Cases

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US11-BT01 | Retrieve appointment confirmation data using a MySQL query | Returned the appointment reference, doctor name, date, time, and confirmed status | PASS |
| US11-BT02 | Retrieve appointment 5 using its owner's patient ID 8 | PHP repository returned the correct appointment information | PASS |
| US11-BT03 | Retrieve appointment 5 using the unrelated, nonexistent patient ID 999999 | PHP repository returned null | PASS |

### 5.3 Backend Test Summary

**Tests Executed:** 3  
**Passed:** 3  
**Failed:** 0

### 5.4 PHP Syntax Verification

The following files passed PHP syntax checks:

- `10-Source-Code/includes/appointment_repository.php`
- `10-Source-Code/actions/appointment_book.php`
- `10-Source-Code/includes/appointment_confirmation_data.php`

### 5.5 Backend Security Findings

The confirmation retrieval function requires both an appointment ID and a patient ID.

The SQL query restricts the result to an appointment belonging to the specified patient.

Testing confirmed that the owner could retrieve appointment 5, while an unrelated patient ID could not retrieve it.

The command-line tests verified repository behavior. Browser-level authentication and access restrictions were tested separately by Sonbol.

### 5.6 Backend Conclusion

All three backend checks passed.

The appointment confirmation repository returned accurate information and enforced patient ownership filtering in the tested scenarios.

The temporary PHP testing file was deleted after testing.

---

## 6. Sonbol's Frontend and Integration Testing

**Date:** October 24, 2026  
**Tester:** Sonbol Almas  
**Tasks:** T3.5 — Confirmation Interface; T3.6 — Confirmation Testing

### 6.1 Testing Objective

Verify that the patient confirmation page displays accurate information after booking, prevents unauthorized access to another patient's information, and handles invalid requests appropriately.

### 6.2 Frontend and Integration Test Cases

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US11-FT01 | Open an existing appointment confirmation as its owner | Appointment reference, doctor name, date, time, and status displayed correctly | PASS |
| US11-FT02 | Access Patient 1's confirmation using another authenticated patient account | The second patient could not view Patient 1's appointment information | PASS |
| US11-FT03 | Book an available appointment | Application automatically opened the Appointment Confirmation page | PASS |
| US11-FT04 | Compare the newly booked appointment confirmation with MySQL | Appointment reference, doctor, date, time, and status matched the database | PASS |
| US11-FT05 | Open the confirmation page with a nonexistent appointment reference | System displayed an appropriate not-found/permission message without revealing appointment details | PASS |
| US11-FT06 | Open the appointment booking action directly using a GET request | System rejected the invalid request and returned to the Doctor Details flow | PASS |

### 6.3 Frontend Test Summary

**Tests Executed:** 6  
**Passed:** 6  
**Failed:** 0

---

## 7. Appointment Confirmation Integration Test

**Test IDs:** US11-FT03 and US11-FT04

### 7.1 Test Procedure

1. The patient logged into the Clinic Appointment System.
2. The patient searched for a doctor.
3. The patient selected an available appointment date and time.
4. The patient clicked **Book Appointment**.
5. The backend saved the appointment in MySQL.
6. The application automatically opened the Appointment Confirmation page.
7. The displayed information was compared with the appointment record in phpMyAdmin.

### 7.2 Test Data

| Field | Actual Value |
|---|---|
| Appointment Reference | #2 |
| Patient ID | 1 |
| Doctor | Test Doctor |
| Appointment Date | October 28, 2026 |
| Start Time | 9:00 AM |
| End Time | 9:30 AM |
| Status | confirmed |

### 7.3 Expected Result

The system should open the Appointment Confirmation page automatically after successful booking.

The page should display the correct appointment reference, doctor, date, time, and confirmation status.

### 7.4 Actual Result

The Appointment Confirmation page opened automatically after the booking was completed.

The displayed details matched the newly created appointment record in MySQL.

**Result: PASS**

---

## 8. Patient Authorization Test

**Test ID:** US11-FT02  
**Tester:** Sonbol Almas

### Test Procedure

1. The owner of an appointment logged in and opened the confirmation page.
2. A second, different patient account was authenticated in a separate browser session.
3. The second patient attempted to open the first patient's appointment confirmation URL.

### Expected Result

The second patient must not be able to view another patient's appointment details.

### Actual Result

The second authenticated patient could not view the first patient's appointment confirmation details.

**Result: PASS**

### Security Finding

The tested workflow correctly restricted access to the appointment owner.

---

## 9. Invalid Appointment Reference Test

**Test ID:** US11-FT05  
**Date:** October 24, 2026  
**Tester:** Sonbol Almas

### Test Procedure

The authenticated patient opened:

`appointment-confirmation.php?appointment_id=999999`

### Expected Result

The system should display an appropriate error without revealing another appointment or exposing internal PHP errors.

### Actual Result

The page handled the nonexistent appointment reference and displayed the expected not-found/permission feedback.

No appointment details were revealed.

**Result: PASS**

---

## 10. Invalid Booking Request Test

**Test ID:** US11-FT06  
**Date:** October 24, 2026  
**Tester:** Sonbol Almas

### Test Procedure

The authenticated patient opened the booking action directly in the browser using a GET request:

`actions/appointment_book.php`

### Expected Result

The booking action must reject requests that do not use POST.

The patient should be redirected to the Doctor Details flow with appropriate error feedback.

### Actual Result

The system rejected the invalid request and returned to the Doctor Details flow.

**Result: PASS**

### Testing Limitation

This test verified the direct GET request handling. It did not independently test a failed POST booking attempt or use a database query to verify appointment counts immediately afterward.

---

## 11. PHP Syntax Verification

The following files passed PHP syntax validation during development:

- `10-Source-Code/includes/appointment_repository.php`
- `10-Source-Code/includes/appointment_confirmation_data.php`
- `10-Source-Code/actions/appointment_book.php`
- `10-Source-Code/patient/appointment-confirmation.php`

No PHP syntax errors were reported.

---

## 12. Combined Testing Summary

| Testing Category | Tester | Executed | Passed | Failed |
|---|---|---:|---:|---:|
| Backend and database | Sewara Rashidi | 3 | 3 | 0 |
| Frontend and integration | Sonbol Almas | 6 | 6 | 0 |
| **Total** | **Both Developers** | **9** | **9** | **0** |

**Total Tests Executed:** 9  
**Passed:** 9  
**Failed:** 0

All nine documented test cases passed.

---

## 13. Remaining Work

Although the tested scenarios passed, the following activities remain:

1. Commit and merge Sonbol's frontend and integration work into GitHub.
2. Pull the updated code on Sewara's computer.
3. Perform final regression testing on the merged implementation.
4. Update the SD06 Appointment Booking Sequence Diagram to reflect detailed appointment confirmation.
5. Complete the team's Definition of Done review.

The broader US-08 concurrency testing remains a separate Sprint 3 activity.

---

## 14. Conclusion

The Sprint 3 US-11 Appointment Confirmation functionality passed nine documented backend, frontend, security, and integration test cases.

Sewara successfully developed the backend confirmation retrieval functionality and verified the database and ownership-filtering behavior.

Sonbol successfully implemented the confirmation interface and connected the appointment-booking process to the confirmation page.

Testing confirmed that the system displays accurate appointment information, automatically redirects after a successful booking, rejects invalid confirmation references, and restricts access to another patient's appointment information in the tested scenarios.

**Overall Test Result:** 9 PASS, 0 FAIL.

**Current Status:** Implementation tested locally. Final GitHub integration, regression testing, and UML updates remain pending.

US-11 should be marked Done only after the remaining activities and agreed Definition of Done requirements are satisfied.