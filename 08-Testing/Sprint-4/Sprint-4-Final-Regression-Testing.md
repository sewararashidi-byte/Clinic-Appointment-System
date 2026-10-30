# Sprint 4 — Final Regression Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 4   
**Tester:** Sewara Rashidi  
**Testing Method:** Manual browser testing, phpMyAdmin checks, PHP command-line checks, and code review  
**Environment:** PHP, MySQL, XAMPP, Web Browser  
**Git Branch:** Local `main` after Sprint 4 integration

---

## 1. Testing Objective

The purpose of Sprint 4 regression testing was to check that the new features and important existing features still worked correctly after the backend and frontend code were merged.

The testing covered all five Sprint 4 user stories.

## 2. Testing Approach

The developer tested the integrated system using fictional patient and doctor accounts.

Testing included checking web pages, appointment changes, database records, error messages, and PHP terminal output.

The results below describe tests actually performed. Detailed feature testing is documented separately in each user story's testing report.

## 3. Scope of Testing

| User Story | Description |
|---|---|
| US-09 | Appointment Cancellation |
| US-10 | Appointment Rescheduling |
| US-12 | Appointment History |
| US-13 | Appointment Notifications and Reminders |
| US-14 | Doctor Profile Management |

Important existing features from earlier sprints were also checked.

## 4. Test Environment

- **Backend:** PHP
- **Database:** MySQL/MariaDB
- **Local Server:** XAMPP
- **Frontend:** HTML, Bootstrap, Web Browser
- **Database Tool:** phpMyAdmin
- **Test Accounts:** Fictional patients and Test Doctor
- **Test Appointments:** #9 and #10

## 5. Regression Test Cases and Results

| Test ID | User Story | Test Scenario | Actual Result | Status |
|---|---|---|---|---|
| S4-RT01 | US-09 | Cancel appointment #10 | Cancellation succeeded and appointment appeared in Cancelled Appointments | PASS |
| S4-RT02 | US-09 | Check cancelled appointment slot | Database showed cancelled status and released slot; the time became available again | PASS |
| S4-RT03 | US-09 | Open cancellation action using GET | System rejected the invalid request | PASS |
| S4-RT04 | US-10 | Reschedule appointment #10 | Time changed from 2:30 PM to 3:00 PM successfully | PASS |
| S4-RT05 | US-10 | Try to reschedule another patient's or a cancelled appointment | Both actions were blocked | PASS |
| S4-RT06 | US-10 | Open rescheduling action using GET | System rejected the invalid request | PASS |
| S4-RT07 | US-12 | View My Appointments | Upcoming, previous, and cancelled appointments displayed correctly | PASS |
| S4-RT08 | US-12 | Open My Appointments from Patient Dashboard | New dashboard button opened the correct page | PASS |
| S4-RT09 | US-13 | Run reminder checker after integration | Script ran without errors, found 0 due reminders, and sent no emails | PASS |
| S4-RT10 | US-14 | View and edit Doctor Profile | Profile opened, changes were saved, and updated details appeared on the patient-facing page | PASS |
| S4-RT11 | US-14 | Patient tries to open Doctor Profile editing | Unauthorized access was blocked | PASS |
| S4-RT12 | US-14 | Open My Profile from Doctor Dashboard | New dashboard button opened the correct page | PASS |
| S4-RT13 | Earlier features | Check login and access protection | Patient and doctor dashboards worked; logged-out access was blocked | PASS |
| S4-RT14 | Earlier features | Check doctor search, details, and patient profile | Pages displayed correctly | PASS |
| S4-RT15 | Earlier features | Check Doctor Appointments and Manage Availability | Both pages opened successfully | PASS |
| S4-RT16 | Earlier features | Check booked and cancelled time slots | Booked 2:00 PM slot was unavailable; cancelled 3:00 PM slot was available | PASS |
| S4-RT17 | PHP Syntax | Check PHP files for syntax errors | Terminal reported 0 syntax failures | PASS |

## 6. Testing Summary

| Category | Executed | Passed | Failed |
|---|---:|---:|---:|
| US-09 — Appointment Cancellation | 3 | 3 | 0 |
| US-10 — Appointment Rescheduling | 3 | 3 | 0 |
| US-12 — Appointment History | 2 | 2 | 0 |
| US-13 — Appointment Reminders | 1 | 1 | 0 |
| US-14 — Doctor Profile Management | 3 | 3 | 0 |
| Earlier Features | 4 | 4 | 0 |
| PHP Syntax Testing | 1 | 1 | 0 |
| **Total** | **17** | **17** | **0** |

**Total Tests Executed:** 17  
**Passed:** 17  
**Failed:** 0  
**Pass Rate:** 100% of the selected executed tests

## 7. Integration Verification

The regression tests confirmed that the five Sprint 4 features continued to work in the tested scenarios.

During integration testing, two navigation problems were found and corrected:

- **Patient Dashboard:** Added the missing My Appointments button.
- **Doctor Dashboard:** Added the missing My Profile button and placed Logout at the end.

Both navigation buttons were tested successfully.

The two dashboard fixes and this regression report still need to be committed and merged into GitHub's `main` branch.

## 8. Testing Limitations

The regression testing covered selected important workflows rather than every possible test scenario.

The following tests were not fully repeated during this regression session:

- Actual email sending after the merge. Email delivery, automatic scheduling, and retry behavior were tested earlier and recorded in the separate US-13 testing report.
- Simultaneous booking requests and forced rescheduling conflicts.
- Every security and validation case for all five user stories.
- Every registration, booking, and availability-editing test from earlier sprints.

These limitations do not represent failures in the 17 tests performed.




## 9. Conclusion

Sprint 4 regression testing covered all five Sprint 4 user stories and important features from earlier sprints.

All **17 selected regression tests passed**, with no failures reported.

The results show that the tested functionality worked correctly after integration. Additional testing limitations are documented above.

**Final Regression Testing Result: 17 PASS — 0 FAIL.**

**Regression Testing Status:** Completed for the selected Sprint 4 regression tests.

