# US-20 — Clinic Information Management Testing Report

**Project:** Clinic Appointment System  
**Sprint:** Sprint 5  
**Testing Date:** November 2, 2026  
**User Story:** US-20 — Clinic Information Management  
**Frontend Developer:** Sonbol Almas  
**Backend Developer:** Sewara Rashidi  
**Related Sequence Diagram:** SD19 — Clinic Information Management  
**Testing Status:** All 12 test cases reported as passed

## 1. Testing Objective

The objective of this testing activity was to verify that authorized clinic staff can create, view, and update the clinic's general information successfully.

Testing examined the saving and persistence of clinic details, required-field validation, database accuracy, role-based access control, protection against unauthorized requests, and correct handling of invalid CSRF tokens.

The testing also verified the integration between the clinic-information frontend developed by Sonbol and the PHP/MySQL backend developed by Sewara.

## 2. Requirements Under Test

Based on the Product Backlog and Product Owner clarifications, the following requirements were considered:

- Receptionists and administrators can access the Clinic Information Management page.
- Authorized staff can enter and save the clinic's name, address, contact phone number, and opening hours.
- The system can create a clinic-information record when no existing information is available.
- Previously saved clinic information must be displayed when the page is reopened.
- Authorized staff can update existing clinic details.
- Updated information must be stored correctly in the database.
- All four clinic-information fields are required.
- Invalid or excessively long input must be rejected.
- Patients and doctors must not access the staff-only clinic-management page.
- Unauthenticated users must be denied access.
- Invalid CSRF tokens must not permit unauthorized modifications.
- The database must retain accurate clinic details and the last-updated timestamp.

The implemented functionality focuses on the four core clinic-information fields supported by the existing backend.

## 3. Testing Environment

**Application:** Clinic Appointment System  
**Backend:** PHP and MySQL/MariaDB  
**Local Server:** XAMPP  
**Frontend:** Bootstrap-based clinic-information management interface  
**Browser:** Google Chrome  
**Database:** `clinic_appointment_system`  
**Application Timezone:** `Asia/Kabul`

**Relevant files:**

- `10-Source-Code/staff/clinic.php`
- `10-Source-Code/actions/staff_clinic_update.php`
- `10-Source-Code/includes/staff_clinic_repository.php`
- `10-Source-Code/config/auth.php`
- `10-Source-Code/config/database.php`

**Relevant database table:**

- `clinic_information`

**Database migration:**

`10-Source-Code/database/migrations/sprint5_schema_updates.sql`

The Sprint 5 database migration introduced the `clinic_information` table for storing the clinic's general information.

Testing was performed using fictional clinic details in the local development environment.

## 4. Test Cases and Results

| Test ID | Test Scenario | Expected Result | Reported Result |
|---|---|---|---|
| TC-US20-01 | Log in as a receptionist and open Clinic Information Management | The page displays correctly with clinic name, address, phone number, and opening hours fields | Pass |
| TC-US20-02 | Enter valid clinic details in all four fields and save | Information is saved successfully and a success message appears | Pass |
| TC-US20-03 | Close and reopen the Clinic Information page after saving | Previously saved clinic details remain displayed correctly | Pass |
| TC-US20-04 | Modify an existing clinic detail, save, and reopen the page | Updated information is saved and remains available after reopening | Pass |
| TC-US20-05 | Attempt to save clinic information with an empty Clinic Name | The required clinic name is rejected | Pass |
| TC-US20-06 | Attempt to save with an empty address, phone number, or opening hours | Each required-field validation prevents invalid submission | Pass |
| TC-US20-07 | Submit excessively long or otherwise invalid clinic-information values | Invalid values are rejected without corrupting the existing clinic record | Pass |
| TC-US20-08 | Attempt to access `staff/clinic.php` using patient and doctor accounts | Both unauthorized roles are denied access | Pass |
| TC-US20-09 | Log out and attempt to access `staff/clinic.php` directly | Unauthenticated access is denied or redirected | Pass |
| TC-US20-10 | Log in as an administrator and update approved clinic information | Administrator can access the page and save changes successfully | Pass |
| TC-US20-11 | Submit a clinic-information update with an invalid CSRF token | The request is rejected and previously saved information remains unchanged | Pass |
| TC-US20-12 | Verify the saved clinic details and last-updated timestamp in phpMyAdmin | Database values match the saved information and the update timestamp reflects the latest successful modification | Pass |

## 5. Execution Summary

The development team reported successful completion of all 12 planned US-20 test cases.

During frontend development, Sonbol verified that the Clinic Information Management page displayed correctly, including the four required input fields and the message shown when no clinic information had been saved.

Subsequent testing covered creation of clinic information, modification of existing details, persistence after reopening the page, and validation of required fields.

Backend and security testing examined invalid input handling, access restrictions for unauthorized roles, logged-out access protection, administrator permissions, CSRF validation, and database consistency.

The reported results indicate that the implemented Clinic Information Management functionality supports authorized creation and updating of clinic details while protecting the stored information against invalid and unauthorized modifications.

**Summary:**

- Total test cases: 12
- Reported passed: 12
- Reported failed: 0
- Outstanding test cases from this checklist: 0



## 6. Database Verification

The clinic information is stored in the `clinic_information` table.

For successful creation or updating of clinic information, the expected database behavior is:

- The clinic name is stored correctly.
- The clinic address is stored correctly.
- The contact phone number is stored correctly.
- The opening hours are stored correctly.
- Previously saved information remains available after reopening the page.
- Updating existing details modifies the stored clinic information rather than creating unnecessary duplicate clinic records.
- The `updated_at` value reflects the latest successful update.
- Unrelated clinic information remains unchanged when individual fields are modified.

For rejected or unsuccessful requests, the database must preserve the previously saved values.

The team reported successful completion of the clinic-information persistence and database-verification tests.

The invalid CSRF token test was also reported as passed, with previously saved clinic information remaining unchanged after the rejected request.



## 7. Defects and Observations

No failures were reported among the 12 executed US-20 test cases.


**Scope observation:**

The implemented US-20 functionality covers the four core clinic-information fields. Management of individual clinic services was not included in this testing checklist because a corresponding working interface and backend workflow had not been demonstrated.

This scope item should be reviewed separately against the final Product Owner-approved requirements and must not be considered tested or passed through the results of this report.

No unresolved defects were reported from the 12 executed core-functionality tests.

## 8. Conclusion

Based on the development team's reported results, all 12 planned US-20 Clinic Information Management test cases passed.

The tested functionality allows authorized receptionists and administrators to create, view, and update the clinic's general information through the staff management interface.

The results also support the correct operation of database persistence, required-field validation, unauthorized access restrictions, invalid-input handling, and CSRF protection.

Successful frontend and backend integration was reported for the four core clinic-information fields.

**US-20 Testing Result: Passed — development team reported for the implemented core functionality.**

