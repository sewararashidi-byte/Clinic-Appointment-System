# Sprint 3 — Final Regression Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Report Date:** October 26, 2026  
**Testers:** Sewara Rashidi and Sonbol Almas  
**Testing Method:** Collaborative manual testing through screen sharing  
**Branch:** `main`  
**Environment:** PHP, MySQL, XAMPP, Web Browser

---

## 1. Testing Objective

The objective of the final Sprint 3 regression testing was to verify that the completed user stories continued to function correctly after integration into the shared `main` branch.

The testing focused on Doctor Appointment Management, patient access restrictions, Patient Profile Management, Appointment Confirmation, and Double-Booking Prevention.

## 2. Testing Approach

Sewara Rashidi and Sonbol Almas performed the regression testing collaboratively through a screen-sharing session.

During the session, the developers examined the integrated system, executed or observed the test scenarios together, and checked the results against the expected behavior.

Both developers participated in the verification of the Sprint 3 functionality.

This was one collaborative testing session, and the results are counted once per test case rather than separately for each developer.

## 3. Scope of Testing

| User Story | Description |
|---|---|
| US-08 | Prevent Double Booking |
| US-11 | Appointment Confirmation |
| US-04 | Patient Profile Management |
| US-16 | Doctor Appointment Management |

## 4. Test Environment

The tests were performed using the integrated Clinic Appointment System.

**Technologies:**
- PHP
- MySQL
- XAMPP
- Web Browser

**Git Branch:** `main`

The developers used screen sharing to collaboratively verify the application's behavior.

## 5. Regression Test Cases and Results

| Test ID | User Story | Test Scenario | Reported Actual Result | Status |
|---|---|---|---|---|
| S3-RT01 | US-16 | Doctor opens My Appointments | Doctor appointment information displayed correctly | PASS |
| S3-RT02 | US-16 | Patient attempts to access Doctor Appointments page | Unauthorized access was blocked | PASS |
| S3-RT03 | US-04 | Patient opens, updates, and reloads their profile | Profile information saved and reloaded correctly | PASS |
| S3-RT04 | US-11 | Patient books an available appointment | Appointment confirmation displayed successfully | PASS |
| S3-RT05 | US-08 | Attempt to book an already occupied appointment slot | Duplicate booking was prevented | PASS |

**Testers:** Sewara Rashidi and Sonbol Almas

**Testing Method:** Collaborative manual browser testing through screen sharing.

## 6. Testing Summary

| Category | Executed | Passed | Failed |
|---|---:|---:|---:|
| Doctor appointments and access control | 2 | 2 | 0 |
| Patient profile management | 1 | 1 | 0 |
| Appointment confirmation | 1 | 1 | 0 |
| Double-booking prevention | 1 | 1 | 0 |
| **Total** | **5** | **5** | **0** |

**Total Tests Executed:** 5  
**Passed:** 5  
**Failed:** 0  
**Pass Rate:** 100%

## 7. Integration Verification

The collaborative regression tests verified the following workflows:

- Doctors can view their confirmed appointments.
- Patient accounts cannot access the Doctor Appointments interface.
- Patients can update and reload their profile information.
- Successfully booked appointments produce a confirmation page.
- Duplicate bookings for an occupied appointment slot are prevented.

Both developers reported successful results for all five test scenarios.

## 8. Testing Limitations

The regression session covered five selected workflows and was not a complete repetition of every test performed during Sprint 3.

Detailed functional, validation, and security testing is documented in the individual user-story testing reports.

This report records the jointly reported manual test results. No separate automated execution logs or screen-sharing recordings are attached as evidence.

Successful developer testing does not constitute formal Product Owner acceptance.

## 9. Related Documentation

The Sprint 3 testing documentation includes:

- US-08 — Double Booking Test Results
- US-11 — Appointment Confirmation Test Results
- US-04 — Patient Profile Test Results
- US-16 — Doctor Appointment Test Results

Related sequence diagrams:

- SD04 — Doctor Availability Management (Sprint 3 update)
- SD06 — Appointment Booking (Sprint 3 update)
- SD07 — Doctor Appointment Management
- SD08 — Patient Profile Management

## 10. Conclusion

The final Sprint 3 regression testing was performed collaboratively by **Sewara Rashidi and Sonbol Almas through screen sharing**.

All five regression test cases passed, with no failures reported.

The results provide evidence that the tested functionality for US-08, US-11, US-04, and US-16 continued to operate after integration into the `main` branch.

**Regression Testing Status:** Completed.


**Final Regression Testing Result: 5 PASS — 0 FAIL.**
