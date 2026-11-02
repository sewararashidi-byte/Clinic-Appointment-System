# Final System Regression Testing Report — Sprints 1–5

**Project:** Clinic Appointment System  
**Course:** ITC315 — Software Engineering  
**Development Methodology:** Scrum  
**Project Duration:** Five Sprints  
**Testing Date:** November 2, 2026  
**Product Owner:** Husna Zala Khan  
**Scrum Master:** Mineh Jami  
**Frontend Developer:** Sonbol Almas  
**Backend Developer:** Sewara Rashidi  
**Testing Type:** Final Functional and Integration Regression Testing  
**Testing Status:** All 27 test cases reported as passed

## 1. Testing Objective

The purpose of final regression testing was to verify that the complete Clinic Appointment System worked correctly after the development and integration of all five sprints.

During the project, new features were added for patients, doctors, receptionists, and administrators. Each new feature needed to work correctly without breaking functionality developed in previous sprints.

Final regression testing therefore checked whether:

- All major functions from the Product Backlog worked correctly.
- Patients, doctors, receptionists, and administrators could perform their permitted activities.
- Appointment booking, cancellation, and rescheduling followed the approved rules.
- Information was saved and retrieved correctly from the database.
- Unauthorized users were prevented from accessing restricted functions.
- The frontend and backend worked together without unexpected errors.

The final testing covered all 20 user stories and additional technical and integration checks.

## 2. Testing Environment

| Item | Description |
|---|---|
| Application | Clinic Appointment System |
| Backend | PHP |
| Frontend | HTML, CSS, JavaScript, Bootstrap |
| Database | MySQL/MariaDB |
| Local Server | XAMPP |
| Browser | Google Chrome |
| Operating System | Windows |
| Database Name | `clinic_appointment_system` |
| Timezone | `Asia/Kabul` |
| Version Control | Git and GitHub |
| Test Accounts | Fictional patient, doctor, receptionist, and administrator accounts |

Testing was performed in the local development environment using the integrated application.

## 3. Testing Method

The developers performed manual functional testing, browser-based interface testing, database verification, and technical checks.

For each major function, the team checked that the expected operation could be completed successfully.

Where applicable, the team also tested invalid inputs, unauthorized access, appointment conflicts, and other conditions that the system should reject.

Successful updates were checked by reopening the affected information or inspecting the relevant database records.

The team recorded the test outcomes as **Pass**, **Fail**, or **Not Tested**.

The final checklist contained 27 test cases: 20 functional tests corresponding to the Product Backlog and seven technical and integration tests.

## 4. Functional Regression Testing Results

The following tests verified the main functionality of all 20 user stories.

| Test ID | User Story | What Was Tested | Expected Result | Reported Result |
|---|---|---|---|---|
| REG-01 | US-01 — User Registration | Register a new patient using valid information and test invalid or duplicate details. | Valid accounts are created; invalid registration is rejected. | Pass |
| REG-02 | US-02 — Secure Login | Log in with valid and incorrect credentials and test logout. | Valid login succeeds; incorrect credentials are rejected; logout ends the session. | Pass |
| REG-03 | US-03 — Role-Based Access | Check access permissions for patients, doctors, receptionists, and administrators. | Each role accesses only its permitted pages and functions. | Pass |
| REG-04 | US-04 — Patient Profile Management | View, edit, save, and reopen patient profile information. | Permitted information changes are saved correctly. | Pass |
| REG-05 | US-05 — Doctor Search | Search for doctors and open a matching doctor's information. | Relevant doctors are displayed and can be selected. | Pass |
| REG-06 | US-06 — Doctor Information and Availability | View a doctor's professional information and available appointment times. | Correct doctor details and current availability are displayed. | Pass |
| REG-07 | US-07 — Available Slots and Appointment Booking | Select an available appointment slot and complete a booking. | The appointment is created successfully using the selected date and time. | Pass |
| REG-08 | US-08 — Prevent Double-Booking | Attempt to book a slot that another patient has already reserved. | The system rejects the conflicting booking. | Pass |
| REG-09 | US-09 — Appointment Cancellation | Cancel an eligible patient appointment and test cancellation restrictions. | Eligible cancellation succeeds; cancellation inside the restricted period is rejected. | Pass |
| REG-10 | US-10 — Appointment Rescheduling | Move an eligible appointment to another available time with the same doctor. | Valid changes are saved; the two-hour rescheduling restriction is enforced. | Pass |
| REG-11 | US-11 — Appointment Confirmation | Complete a booking and view the confirmation. | Confirmation shows the correct appointment information. | Pass |
| REG-12 | US-12 — Appointment History | View upcoming, previous, and cancelled appointments. | Appointment records display accurate details and statuses. | Pass |
| REG-13 | US-13 — Appointment Notifications/Reminders | Verify the reminder checker and configured reminder delivery in the test environment. | Eligible reminders are processed with correct appointment details. | Pass |
| REG-14 | US-14 — Doctor Profile Management | Update permitted doctor profile information and reopen the record. | Approved changes are saved and displayed correctly. | Pass |
| REG-15 | US-15 — Doctor Availability Management | Add or update doctor availability and check patient-facing appointment times. | Saved availability changes are reflected correctly. | Pass |
| REG-16 | US-16 — Doctor Appointment Management | View a doctor's scheduled appointments and test permitted schedule-management operations. | The doctor can manage permitted appointment information without accessing another doctor's records. | Pass |
| REG-17 | US-17 — Patient Information Management | Receptionist updates permitted patient information and reopens the record. | Patient information is updated and saved correctly. | Pass |
| REG-18 | US-18 — Doctor Information Management | Receptionist updates permitted doctor contact details and consultation room. | Approved doctor information changes are saved correctly. | Pass |
| REG-19 | US-19 — Appointment Management | Staff books, cancels, and reschedules appointments while checking restrictions and audit records. | Permitted actions succeed; conflicting and unauthorized changes are prevented. | Pass |
| REG-20 | US-20 — Clinic Information Management | Save, reopen, and update clinic name, address, phone number, and opening hours. | Clinic information is saved and remains accurate after reopening. | Pass |

### Functional Testing Summary

- Total functional regression tests: **20**
- Reported passed: **20**
- Reported failed: **0**
- Not tested: **0**

The team reported that all major functions associated with the 20 user stories continued to operate correctly in the integrated application.

## 5. Technical and Integration Testing Results

In addition to the 20 functional tests, seven checks were performed to verify the technical quality of the complete system.

| Test ID | What Was Tested | Expected Result | Reported Result |
|---|---|---|---|
| REG-21 | PHP syntax checking | Application PHP files contain no syntax errors. | Pass |
| REG-22 | Navigation and logout | Dashboard buttons, page links, and logout work correctly for the supported roles. | Pass |
| REG-23 | Responsive interface | Main forms, tables, buttons, and navigation remain usable on desktop and narrow/mobile screen widths. | Pass |
| REG-24 | Authorization and CSRF protection | Unauthorized operations and requests with invalid security tokens are rejected. | Pass |
| REG-25 | Database and appointment integrity | Appointment records remain accurate, double booking is prevented, and staff appointment changes are logged correctly. | Pass |
| REG-26 | GitHub integration | The main branch contains the integrated application and required project materials without unintended credential exposure. | Pass |
| REG-27 | Application error checking | No unexpected PHP warnings, database errors, or broken pages appear during the tested workflows. | Pass |

### Technical Testing Summary

- Total technical and integration tests: **7**
- Reported passed: **7**
- Reported failed: **0**
- Not tested: **0**

The team reported that the tested frontend, backend, database, and repository components were correctly integrated.

## 6. Overall Test Results

The development team reported that all 27 planned final regression test cases passed.

| Testing Category | Total Tests | Passed | Failed |
|---|---:|---:|---:|
| Functional Regression Testing | 20 | 20 | 0 |
| Technical and Integration Testing | 7 | 7 | 0 |
| **Total** | **27** | **27** | **0** |

**Overall Reported Result: 27/27 Passed (100%)**

No failures were reported during the final regression checklist.

These results are based on confirmation from the development team. Individual execution logs and screenshots for every test case were not independently reviewed in this report.

## 7. Database and Security Verification

Database and security checks were included in the final regression testing to ensure that the system handled information correctly.

### 7.1 Database Verification

The following behavior was verified through the reported tests:

- New user accounts are stored correctly.
- Patient and doctor information changes remain saved after reopening records.
- Doctor availability updates are reflected in appointment booking.
- Appointment bookings contain the correct patient, doctor, date, and time.
- Already booked appointment slots cannot be booked again.
- Cancellation updates the appointment status correctly.
- Rescheduling updates appointment information while preserving the original patient and doctor.
- Appointment history displays accurate information.
- Staff appointment changes are recorded in the audit log.
- Clinic information remains saved after updates.
- Invalid or rejected operations do not incorrectly modify existing information.

### 7.2 Security Verification

The following security behavior was verified through the reported tests:

- Users must log in to access protected functionality.
- Users can access only the pages permitted for their roles.
- Patients cannot access staff-only management pages.
- Doctors cannot access staff-only management pages.
- Logged-out users cannot access protected information.
- Invalid CSRF tokens are rejected.
- Unauthorized appointment changes are prevented.
- Restricted patient and doctor information remains protected.

The results support the correct operation of the tested database and security controls.

## 8. Test Evidence

The final regression testing was supported by the development and testing work completed throughout the five sprints.

Available evidence includes:

- Individual user-story testing reports from the completed sprints.
- Earlier sprint regression testing reports.
- Database verification performed during development and testing.
- Appointment booking, cancellation, and rescheduling test results.
- Reminder testing records, including the configured local scheduler and Mailtrap Email Sandbox.
- GitHub commits and pull requests showing frontend and backend integration.
- Developer confirmation of the 27 final regression test results.

The individual testing reports provide more detailed information about specific features and test cases.



## 9. Problems and Testing Limitations

### 9.1 Problems Found

No failures were reported during the final regression testing checklist.

Some integration issues had been identified and corrected during earlier development activities, including a missing database column required for patient home-address information.

The affected functionality was retested successfully after correction.

### 9.2 Testing Limitations

The following limitations apply to the final results:

- US-20 testing covered the implemented core clinic-information fields. Separate clinic-service management was not verified.
- Administrator-only creation of doctor and receptionist accounts was not established as completed functionality and remains subject to the agreed Product Owner scope.

These limitations do not change the reported results of the 27 completed checks, but they identify functionality and conditions outside the verified testing scope.

## 10. Conclusion

Final system regression testing was performed to verify that the Clinic Appointment System continued to work correctly after completing and integrating all five Scrum sprints.

The team reported that all 27 planned tests passed, including 20 functional tests covering the complete Product Backlog and seven technical and integration tests.

The results indicate that the system's major workflows remained functional, including registration, login, patient and doctor management, appointment booking, cancellation, rescheduling, appointment history, reminders, and staff-management operations.

The technical tests also supported the correct operation of authorization, database persistence, navigation, application syntax, and frontend/backend integration.

No unresolved failures were reported from the final regression checklist.

**Final System Regression Testing Result: Passed — development team reported (27/27).**

