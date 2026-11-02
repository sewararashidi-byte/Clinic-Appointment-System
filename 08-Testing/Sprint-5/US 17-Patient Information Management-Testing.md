# US-17 — Patient Information Management Testing Report

**Project:** Clinic Appointment System  
**Sprint:** Sprint 5  
**Testing Date:** November 1–2, 2026  
**User Story:** US-17 — Patient Information Management  
**Functional Requirement:** FR-19  
**Frontend Developer:** Sonbol Almas  
**Backend Developer:** Sewara Rashidi  
**Related Sequence Diagram:** SD14 — Patient Information Management  
**Testing Status:** All 14 planned test cases reported as passed

## 1. Testing Objective

The objective of this testing activity was to verify that authorized clinic staff can view patient records, edit permitted patient information, and save updates successfully.

Testing also examined role-based access control, required-field validation, CSRF protection, handling of invalid patient IDs, and the restriction of sensitive medical information.

## 2. Requirements Under Test

Based on the Product Backlog and Product Owner clarifications, the following requirements were considered:

- Receptionists and administrators can access patient information management.
- Authorized staff can view existing patient records.
- Staff can update permitted patient details, including full name, age, phone number, email address, and home address.
- Updated information must be saved in the database and remain available after reopening the record.
- Sensitive medical information must not be exposed through the receptionist's patient-editing interface.
- Patients and doctors must not access staff-only patient-management pages.
- Unauthenticated users must be denied access.
- Invalid patient IDs and invalid security tokens must be handled safely.
- Required fields must not accept empty values.

## 3. Testing Environment

**Application:** Clinic Appointment System  
**Backend:** PHP and MySQL/MariaDB  
**Local Server:** XAMPP  
**Frontend:** Bootstrap-based patient-management interface  
**Browser:** Google Chrome  
**Database:** `clinic_appointment_system`  
**Application Timezone:** `Asia/Kabul`

**Relevant files:**

- `10-Source-Code/staff/patients.php`
- `10-Source-Code/staff/patient-edit.php`
- `10-Source-Code/actions/staff_patient_update.php`
- `10-Source-Code/includes/staff_patient_repository.php`
- `10-Source-Code/config/auth.php`
- `10-Source-Code/config/database.php`

**Database modification:**

The `users` table includes the `home_address` column required for storing patient home-address information.

Testing was performed using fictional patient accounts in the local development environment.

## 4. Test Cases and Results

| Test ID | Test Scenario | Expected Result | Reported Result |
|---|---|---|---|
| TC-US17-01 | Log in as a receptionist and open Patient Management | Existing patient records are displayed correctly | Pass |
| TC-US17-02 | Open the Edit Patient Information form | Permitted patient information is displayed in the editing form | Pass |
| TC-US17-03 | Update a patient's age | Updated age is saved successfully | Pass |
| TC-US17-04 | Update a patient's full name | Updated name remains saved after reopening the record | Pass |
| TC-US17-05 | Update patient phone number and email address | Both updated fields are saved and remain available after reopening | Pass |
| TC-US17-06 | Update a patient's home address | Home address is saved and remains available after reopening | Pass |
| TC-US17-07 | Attempt to save a patient record with an empty full-name field | Empty required field is rejected | Pass |
| TC-US17-08 | Attempt to access patient-management pages using a patient account | Unauthorized access is denied | Pass |
| TC-US17-09 | Attempt to access patient-management pages using a doctor account | Unauthorized access is denied | Pass |
| TC-US17-10 | Attempt to access the patient-management page after logging out | Unauthenticated access is denied or redirected | Pass |
| TC-US17-11 | Inspect the receptionist patient-editing form for sensitive medical fields | Medical history, diagnosis, and treatment fields are not exposed | Pass |
| TC-US17-12 | Submit a patient update using an invalid CSRF token | Update is rejected and existing information remains unchanged | Pass |
| TC-US17-13 | Request a nonexistent patient record using `id=999999` | System handles the invalid record safely without exposing patient information | Pass |
| TC-US17-14 | Log in as an administrator and open patient-management pages | Administrator can access patient records and the editing form | Pass |

## 5. Execution Summary

The development team reported successful completion of all 14 planned US-17 test cases.

Sonbol verified the patient-management interface and successful saving of permitted patient information, including name, phone number, email address, and home address.

Sewara verified access restrictions for patient and doctor accounts, administrator access, logged-out access protection, invalid patient ID handling, required-field rejection, and invalid CSRF token protection.

The results indicate that the implemented patient-management functionality supports permitted information updates and prevents unauthorized access through the tested staff pages.

**Summary:**

- Total test cases: 14
- Reported passed: 14
- Reported failed: 0
- Outstanding cases from the planned checklist: 0

These results are based on the developers' confirmations and the screenshots shared during development. Individual database execution logs and screenshots for every test case have not been independently reviewed.

## 6. Database Verification

For successful patient-information updates, the expected database behavior is:

- The existing patient record remains associated with the same user ID.
- Approved changes to full name, age, phone number, email address, and home address are stored in the `users` table.
- Updated values remain available when the patient-editing form is reopened.
- Unrelated patient information is preserved.
- Rejected or unauthorized requests do not modify patient records.

The team reported successful saving and persistence of the tested patient information fields.

For the invalid CSRF token test, the team reported that the update was rejected and the existing patient information remained unchanged.

Direct database screenshots showing the before-and-after values were not supplied for every test.



## 7. Defects and Observations

During integration on Sonbol's local environment, a missing `home_address` database column initially caused an error.

The required column was subsequently added, and the home-address editing workflow was retested successfully on November 2, 2026.

No unresolved failures were reported among the 14 planned test cases.


## 8. Conclusion

Based on the development team's reported results, all 14 planned US-17 Patient Information Management test cases passed.

The tested functionality allows authorized receptionists and administrators to view patient records and update permitted personal and contact information.

The recorded tests also support the correct operation of role-based access restrictions, logged-out access protection, invalid-record handling, required-field validation at the interface level, and CSRF protection.

The previously encountered home-address database issue was resolved, and the related update workflow passed subsequent testing.

**US-17 Testing Result: Passed — development team reported.**

