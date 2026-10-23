# US-11 — Appointment Confirmation: Backend Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Date:** October 23, 2026  
**Tester:** Sewara Rashidi  
**User Story:** US-11 — Appointment Confirmation  
**Task:** T3.4 — Implement Backend Confirmation Retrieval

## 1. Testing Objective

Verify that the appointment confirmation backend retrieves accurate appointment details and restricts retrieval to the patient who owns the appointment.

## 2. Test Environment

- Backend: PHP
- Database: MySQL
- Local server: XAMPP
- Database management: phpMyAdmin
- Testing method: SQL query, PHP CLI test, and syntax validation

## 3. Test Data

| Field | Value |
|---|---|
| Appointment ID | 5 |
| Patient ID | 8 |
| Doctor | Test Doctor |
| Appointment Date | October 23, 2026 |
| Start Time | 10:00 AM |
| End Time | 10:30 AM |
| Status | confirmed |

## 4. Test Cases and Results

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US11-BT01 | Retrieve confirmation data through a MySQL query | Returned appointment ID, doctor name, date, times, and confirmed status | PASS |
| US11-BT02 | Retrieve appointment 5 using its owner's patient ID 8 | PHP repository returned the appointment confirmation | PASS |
| US11-BT03 | Retrieve appointment 5 using a different, nonexistent patient ID 999999 | PHP repository returned null | PASS |

**Total backend checks:** 3  
**Passed:** 3  
**Failed:** 0

## 5. PHP Syntax Verification

The following files passed PHP syntax checks:

- `includes/appointment_repository.php`
- `actions/appointment_book.php`
- `includes/appointment_confirmation_data.php`

## 6. Security Findings

The repository uses both appointment ID and patient ID when retrieving confirmation information.

The tests confirmed that the appointment owner could retrieve the record and that an unrelated, nonexistent patient ID could not retrieve it.

This does not yet constitute a full browser-level authorization test.

## 7. Remaining Tests

The following tests will be performed after frontend integration:

- Verify detailed confirmation is displayed after successful booking.
- Verify appointment details match the database.
- Verify a second real patient cannot view the first patient's confirmation.
- Verify invalid appointment references are handled safely.
- Verify the full booking-to-confirmation workflow.

## 8. Conclusion

The US-11 backend successfully retrieved accurate confirmation details in the tested scenarios.

Three backend checks passed, and the modified PHP files passed syntax validation.

The temporary command-line test file was deleted after testing.

**Current Status:** Backend implementation merged; full US-11 integration and acceptance testing pending.
