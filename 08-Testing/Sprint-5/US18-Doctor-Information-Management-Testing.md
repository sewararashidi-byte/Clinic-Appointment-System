# US-18 — Doctor Information Management Testing Report

**Project:** Clinic Appointment System  
**Sprint:** Sprint 5  
**Testing Date:** November 2, 2026  
**User Story:** US-18 — Doctor Information Management  
**Frontend Developer:** Sonbol Almas  
**Backend Developer:** Sewara Rashidi  
**Related Sequence Diagram:** SD15 — Doctor Information Management  
**Testing Status:** All 14 test cases reported as passed

## 1. Testing Objective

The objective of this testing activity was to verify that authorized clinic staff can view doctor records, access doctor information, and update permitted doctor details successfully.

Testing also examined data persistence, consultation room management, required-field validation, role-based access control, handling of invalid doctor IDs, and protection against unauthorized updates using invalid CSRF tokens.

The testing aimed to ensure that receptionists can manage approved doctor contact information without modifying restricted professional details.

## 2. Requirements Under Test

Based on the Product Backlog and Product Owner clarifications, the following requirements were considered:

- Receptionists and administrators can access doctor information management.
- Authorized staff can view existing doctor records and their details.
- Staff can update permitted doctor contact information, including phone number and email address.
- Staff can assign or update a doctor's consultation room.
- Consultation room information is optional and may be left empty.
- Doctor names and specializations are read-only in the receptionist editing interface.
- Updated doctor information must be saved in the database and remain available after reopening the record.
- Invalid email addresses and missing required contact information must be rejected.
- Patients and doctors must not access staff-only doctor-management pages.
- Unauthenticated users must be denied access.
- Invalid doctor IDs must be handled safely.
- Invalid CSRF tokens must not permit unauthorized database modifications.

## 3. Testing Environment

**Application:** Clinic Appointment System  
**Backend:** PHP and MySQL/MariaDB  
**Local Server:** XAMPP  
**Frontend:** Bootstrap-based doctor-management interface  
**Browser:** Google Chrome  
**Database:** `clinic_appointment_system`  
**Application Timezone:** `Asia/Kabul`

**Relevant files:**

- `10-Source-Code/staff/doctors.php`
- `10-Source-Code/staff/doctor-edit.php`
- `10-Source-Code/actions/staff_doctor_update.php`
- `10-Source-Code/includes/staff_doctor_repository.php`
- `10-Source-Code/config/auth.php`
- `10-Source-Code/config/database.php`

**Database modification:**

The `doctor_profiles` table includes the `consultation_room` column for storing optional consultation room information.

Testing was performed using fictional doctor accounts in the local development environment.

## 4. Test Cases and Results

| Test ID | Test Scenario | Expected Result | Reported Result |
|---|---|---|---|
| TC-US18-01 | Log in as a receptionist and open Doctor Management | Existing doctor records are displayed with the correct names, specializations, and available details | Pass |
| TC-US18-02 | Open Edit Doctor Information for an existing doctor | The selected doctor's details are displayed correctly in the editing form | Pass |
| TC-US18-03 | Update the doctor's phone number and email address, save, and reopen the record | Both updated values are saved successfully and remain available after reopening | Pass |
| TC-US18-04 | Change the consultation room to `Room 102`, save, and reopen | The new consultation room remains saved | Pass |
| TC-US18-05 | Clear the consultation room, save, and reopen the record | The optional consultation room field accepts an empty value | Pass |
| TC-US18-06 | Attempt to edit the doctor's name or specialization as a receptionist | Name and specialization remain read-only in the editing form | Pass |
| TC-US18-07 | Enter an invalid email address and attempt to save | The invalid email address is rejected | Pass |
| TC-US18-08 | Remove the required phone number and attempt to save | The empty required phone field is rejected | Pass |
| TC-US18-09 | Open `staff/doctor-edit.php?id=999999` | The nonexistent doctor ID is handled safely without exposing unrelated information | Pass |
| TC-US18-10 | Log in as a patient and attempt to access the staff doctor list and editing page | Both staff-only pages deny unauthorized patient access | Pass |
| TC-US18-11 | Log in as a doctor and attempt to access the staff doctor list and editing page | Both staff-only pages deny unauthorized doctor access | Pass |
| TC-US18-12 | Log out and attempt to access the Doctor Management page directly | Unauthenticated access is denied or redirected | Pass |
| TC-US18-13 | Log in as an administrator and open Doctor Management and Edit Doctor Information | Administrator can access both pages successfully | Pass |
| TC-US18-14 | Submit a doctor-information update with an invalid CSRF token | The request is rejected and existing doctor information remains unchanged | Pass |

## 5. Execution Summary

The development team reported that all 14 planned US-18 test cases passed.

Frontend testing verified the correct display of doctor records, the doctor editing interface, successful updates to contact details and consultation room information, and rejection of invalid inputs.

Backend and security testing examined role-based access restrictions, invalid doctor ID handling, unauthenticated access, administrator permissions, and invalid CSRF token protection.

The reported results indicate that the implemented Doctor Information Management functionality supports authorized updates while restricting access to staff-only operations.

**Summary:**

- Total test cases: 14
- Reported passed: 14
- Reported failed: 0
- Outstanding test cases from this checklist: 0

These results are based on the development team's confirmation. Individual execution logs, exact database values, and screenshots for every test case have not been independently reviewed in this report.

## 6. Database Verification

For successful doctor-information updates, the expected database behavior is:

- The existing doctor user ID remains unchanged.
- Updated phone number and email address values are stored in the `users` table.
- Consultation room information is stored in the `doctor_profiles` table.
- The consultation room can be updated or cleared without modifying unrelated doctor information.
- Doctor name and specialization remain unchanged during receptionist contact-information updates.
- Saved information remains available after the doctor record is reopened.
- Rejected or unauthorized update requests do not modify the existing doctor record.

The development team reported successful completion of the relevant update and persistence tests.

For the invalid CSRF token test, the team reported that the request was rejected and the existing doctor information remained unchanged.

Direct before-and-after database screenshots were not supplied for every test case.



## 7. Defects and Observations

No failures were reported among the 14 executed US-18 test cases.

The following observations were recorded:

- The consultation room field required an additional database column, which had been added before completion of the frontend integration.
- Doctor name and specialization were intentionally displayed as read-only in the receptionist editing interface.
- The consultation room field was confirmed to support both populated and empty values.
- Invalid email and empty phone input tests were reported as passed; the exact distinction between browser-side and PHP-side rejection was not recorded.
- The CSRF security test was reported as passed, but the exact HTTP response and associated database evidence were not retained.


No unresolved defects were reported from this testing checklist.

## 8. Conclusion

Based on the development team's reported results, all 14 planned US-18 Doctor Information Management test cases passed.

The tested functionality allows authorized receptionists and administrators to access doctor records and maintain approved contact information and consultation room assignments.

The reported results also support the correct operation of role-based access restrictions, handling of invalid doctor IDs, unauthenticated access protection, required-field validation, and CSRF security.

Doctor name and specialization remained protected from direct modification through the receptionist editing interface.

**US-18 Testing Result: Passed — development team reported.**

