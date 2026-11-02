# Sprint 5 — Final Regression Testing Report

**Project:** Clinic Appointment System    
**Methodology:** Scrum  
**Sprint:** Sprint 5 of 5  
**Testing Date:** November 2, 2026   
**Frontend Developer:** Sonbol Almas  
**Backend Developer:** Sewara Rashidi  
**Testing Scope:** Final integration and regression verification of Sprint 5 functionality  
**Testing Status:** All 11 applicable regression checks reported as passed

## 1. Testing Objective

The objective of Sprint 5 final regression testing was to verify that the newly developed staff-management functionality worked correctly after integrating the frontend and backend implementations.

Sprint 5 introduced features allowing authorized receptionists and administrators to manage patient information, doctor information, appointments, and clinic details.

Regression testing examined whether these features were properly connected, whether information was stored and retrieved accurately, and whether existing security and appointment-management rules continued to operate correctly.

The testing also aimed to confirm that integrating Sprint 5 changes did not introduce errors into the existing application.

## 2. Scope of Testing

The testing covered the four user stories implemented during Sprint 5.

| User Story | Functional Requirement | Functionality |
|---|---|---|
| US-17 | FR-19 | Patient Information Management |
| US-18 | FR-20 | Doctor Information Management |
| US-19 | FR-21 | Appointment Management |
| US-20 | FR-22 | Clinic Information Management |

### 2.1 Patient Information Management — US-17

Testing verified that authorized staff could view patient records and update permitted information.

The supported fields included the patient's full name, age, phone number, email address, and optional home address.

The system was also checked for correct information persistence and restrictions preventing unauthorized users from accessing staff-only patient records.

### 2.2 Doctor Information Management — US-18

Testing verified that authorized staff could view doctor records and modify permitted contact information and consultation room assignments.

The receptionist interface preserved the restriction on editing doctor names and specializations directly.

### 2.3 Appointment Management — US-19

Testing examined the integrated staff appointment-management workflow, including appointment viewing, booking, cancellation, and rescheduling.

The system was checked against the approved scheduling rules, including:

- Booking only genuinely available appointment slots.
- Preventing double booking.
- Allowing authorized staff to manage appointments on behalf of patients.
- Requiring a reason when staff cancel an appointment within 24 hours of its scheduled start.
- Preserving the existing two-hour rescheduling deadline.
- Preventing changes to past or ineligible appointments.
- Recording appointment-management actions in the audit log.

### 2.4 Clinic Information Management — US-20

Testing verified that authorized staff could create, view, and update the clinic's general information.

The implemented fields were clinic name, address, contact phone number, and opening hours.

## 3. Testing Environment

**Application:** Clinic Appointment System  
**Programming Language:** PHP  
**Frontend:** HTML, CSS, JavaScript, and Bootstrap  
**Database:** MySQL/MariaDB  
**Local Server:** XAMPP  
**Browser:** Google Chrome  
**Operating System:** Windows  
**Database Name:** `clinic_appointment_system`  
**Application Timezone:** `Asia/Kabul`  
**Version Control:** Git and GitHub

The testing was conducted using fictional patient, doctor, receptionist, and administrator accounts in the local development environment.

**Main application components:**

- `10-Source-Code/staff/`
- `10-Source-Code/actions/`
- `10-Source-Code/includes/`
- `10-Source-Code/config/`
- `10-Source-Code/dashboards/`
- `10-Source-Code/database/`

**Relevant database tables:**

- `users`
- `doctor_profiles`
- `appointments`
- `appointment_change_log`
- `clinic_information`

## 4. Regression Testing Method

Testing was performed through the application's integrated frontend and backend functionality.

The developers examined successful workflows, invalid-input handling, database persistence, role-based authorization, and the consistency of information after saving or updating records.

The testing process included:

1. Opening the relevant staff-management interface.
2. Performing the permitted operation using fictional test data.
3. Checking the application response and displayed information.
4. Reopening the affected record to verify persistence.
5. Checking relevant database information where required.
6. Verifying that unauthorized or invalid operations were rejected.
7. Confirming that integration did not cause navigation errors, PHP warnings, or unexpected database failures.


## 5. Regression Test Cases and Results

| Test ID | Test Scenario | Expected Result | Reported Result |
|---|---|---|---|
| S5-REG-01 | Verify staff Patient Information Management after frontend and backend integration | Authorized staff can view and update permitted patient information; changes persist correctly | Pass |
| S5-REG-02 | Verify staff Doctor Information Management after integration | Authorized staff can update permitted doctor contact information and consultation room; changes persist correctly | Pass |
| S5-REG-03 | Verify staff appointment booking, cancellation, and rescheduling | Permitted operations succeed; scheduling restrictions, conflict protection, and audit logging remain functional | Pass |
| S5-REG-04 | Verify Clinic Information Management | Authorized staff can create, view, update, and reload clinic details correctly | Pass |
| S5-REG-05 | Check PHP syntax across the application source files | No PHP syntax errors are detected | Pass |
| S5-REG-06 | Verify staff dashboard links, management-page navigation, and logout | Navigation works correctly without broken routes or unauthorized access | Pass |
| S5-REG-07 | Verify staff-management pages at desktop and narrow/mobile screen widths | Forms, tables, buttons, and navigation remain readable and usable | Pass |
| S5-REG-08 | Verify role restrictions and invalid CSRF token protection | Unauthorized requests are rejected without changing protected information | Pass |
| S5-REG-09 | Verify appointment integrity and staff action records in MySQL | Appointment records remain consistent and change-log entries identify relevant staff actions | Pass |
| S5-REG-10 | Verify integrated Sprint 5 source code and documentation in the GitHub main branch | Frontend, backend, migrations, and required materials are present without unintended credential exposure | Pass |
| S5-REG-11 | Check integrated staff workflows for unexpected runtime errors | No unexpected PHP warnings, database errors, or broken pages occur during tested workflows | Pass |

## 6. Execution Summary

The development team reported successful completion of all 11 applicable Sprint 5 regression checks.

The results indicate that the integrated staff-management interfaces remained connected to their respective backend operations and that the tested workflows followed the approved business rules.

**Sprint 5 Regression Summary:**

- Total regression checks: 11
- Reported passed: 11
- Reported failed: 0
- Not executed: 0
- Reported pass rate: 100%

### 6.1 Individual User Story Testing

Separate testing reports were prepared for each Sprint 5 user story.

| User Story | Planned Test Cases | Reported Passed | Reported Failed |
|---|---|---|---|
| US-17 — Patient Information Management | 14 | 14 | 0 |
| US-18 — Doctor Information Management | 14 | 14 | 0 |
| US-19 — Appointment Management | 20 | 20 | 0 |
| US-20 — Clinic Information Management | 12 | 12 | 0 |
| **Total** | **60** | **60** | **0** |

The 60 user-story test cases provided more detailed verification of individual functionality, including successful operations, invalid requests, security restrictions, and database behavior.

The 11 regression checks summarize integration and technical verification. 

The results are based on the development team's confirmation.

## 7. Frontend and Backend Integration Verification

### 7.1 Patient Information Management

The integrated Patient Management interface displayed existing patient records and allowed authorized staff to open the editing form.

The team reported that updates to permitted personal and contact information were saved successfully and remained available after reopening the patient record.

Patient and doctor accounts were denied access to staff-only patient-management pages.

### 7.2 Doctor Information Management

The integrated Doctor Management interface displayed existing doctor records and supported updates to approved contact details and consultation room information.

The team reported successful persistence of the updated information.

Doctor names and specializations remained read-only in the receptionist editing interface.

### 7.3 Appointment Management

The integrated Appointment Management interface displayed appointment records with patient, doctor, date, time, status, and available management actions.

Staff booking used genuine appointment availability retrieved from the database.

The team reported successful booking, cancellation, and rescheduling operations, including enforcement of the approved time restrictions and prevention of conflicting bookings.

Appointment-management actions were reported to be recorded in the change log.

### 7.4 Clinic Information Management

The integrated Clinic Information page allowed staff to enter and update clinic details.

The team reported that successful saves persisted in the `clinic_information` table and that previously saved information was displayed after reopening the page.

Required-field validation and access-control restrictions were also reported as functioning correctly.

## 8. Database and Security Verification

### 8.1 Database Integrity

Testing examined whether successful operations updated the appropriate records without incorrectly modifying unrelated information.

The expected database behavior included:

- Preserving patient and doctor record identities during permitted updates.
- Storing optional patient home-address information correctly.
- Storing doctor consultation room information correctly.
- Maintaining accurate patient and doctor information after updates.
- Preventing multiple confirmed appointments from occupying the same time slot.
- Recording successful appointment cancellations and rescheduling operations accurately.
- Maintaining appointment change-log entries for staff actions.
- Preserving clinic information after successful updates.
- Keeping existing records unchanged when invalid requests were rejected.

The team reported that the relevant database-integrity checks passed.

### 8.2 Security Verification

Testing examined authorization and request validation for the staff-management functionality.

The team reported that:

- Receptionists and administrators could access permitted staff-management pages.
- Patients and doctors could not access staff-only management pages.
- Unauthenticated access was rejected or redirected.
- Invalid CSRF tokens were rejected.
- Invalid record identifiers were handled safely.
- Rejected requests did not improperly update protected information.

These results support the correct operation of the tested security controls.



## 9. Defects and Observations

### 9.1 Resolved Integration Issue

During Sprint 5 integration, the patient home-address field initially caused a database error in a local development environment because the required `home_address` column was missing.

The column was added, and the affected functionality was subsequently retested successfully.

### 9.2 Reported Regression Defects

No unresolved failures were reported among the 11 applicable Sprint 5 regression checks.

### 9.3 Scope Observations

The implemented US-20 functionality covered the four core clinic-information fields. Separate management of individual clinic services was not demonstrated by the completed implementation.

Administrator-only creation of doctor and receptionist accounts was recommended by the Product Owner if it formed part of the committed acceptance criteria. Completion of that functionality was not established through the Sprint 5 testing reports.

These items require scope clarification and are not counted as passed functionality in this report.



## 11. Conclusion

Based on the development team's reported results, all 11 applicable Sprint 5 regression checks passed.

The results indicate that the frontend and backend implementations of US-17, US-18, US-19, and US-20 operated correctly together within the tested local environment.

The regression checks also support the continued operation of role-based authorization, database persistence, appointment integrity, CSRF protection, and staff-management navigation.

Separate user-story testing reports recorded 60 passing test cases across the four Sprint 5 user stories.

**Sprint 5 Final Regression Testing Result: Passed — development team reported (11/11).**

