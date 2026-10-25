# US-04 — Patient Profile Management: Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Testing Date:** October 25, 2026  
**Testers:** Sewara Rashidi (Backend), Sonbol Almas (Frontend)  
**User Story:** US-04 — Patient Profile Management  
**Backend Branch:** `feature/s3-patient-profile-backend`  
**Frontend Branch:** `feature/s3-patient-profile-frontend`  
**Environment:** PHP, MySQL, XAMPP, phpMyAdmin, PHP CLI, Web Browser

---

## 1. Testing Objective

Verify that patients can view and update their own profile information through the Clinic Appointment System.

Testing covers backend database operations, frontend functionality, required and optional field validation, authentication, role-based authorization, profile ownership, CSRF protection, and integration between the Patient Profile interface and its backend.

The objective is to evaluate the implemented functionality against the Product Owner's requirements for US-04.

## 2. User Story and Requirements

**US-04 — Patient Profile Management**

Patients must be able to view and update their own profile information.

The Product Owner confirmed the following field requirements:

| Profile Field | Requirement |
|---|---|
| Full name | Required |
| Phone number | Required |
| Age | Optional |
| Blood type | Optional |
| Type of disease | Optional |
| Number of close relatives | Optional |

Additional requirements:

- Only authenticated patients may access the Patient Profile interface.
- Patients must be restricted to their own profile information.
- Invalid profile submissions must be rejected.
- Changes must be stored and displayed correctly.

## 3. Database Implementation

The existing `users` table was extended to support Patient Profile Management.

The following changes were implemented:

- Changed `age` to allow `NULL`.
- Added `blood_type`.
- Added `disease_type`.
- Added `close_relatives_count`.
- Preserved the existing `full_name` and `phone` columns.

**Migration file:**

`10-Source-Code/database/migrations/US04-patient-profile.sql`

The migration was successfully applied to Sewara's local MySQL database.

Sonbol also created a database backup and imported the migration into her local MySQL database before beginning frontend testing.

The migration supports optional patient profile information without requiring values for every field.

## 4. Implementation

### 4.1 Backend — Sewara Rashidi

**Patient Profile Repository**

File: `10-Source-Code/includes/patient_profile_repository.php`

Implemented:

- `getPatientProfile()` — Retrieves a patient's profile information.
- `updatePatientProfile()` — Updates patient profile fields.
- Prepared SQL statements.
- Database operations restricted to records with the `patient` role.

**Patient Profile Data Loader**

File: `10-Source-Code/includes/patient_profile_data.php`

Implemented:

- Patient role verification.
- Retrieval of the patient ID from the authenticated session.
- Loading the patient's existing profile.
- CSRF token generation.
- Error handling when profile information cannot be retrieved.

**Patient Profile Update Action**

File: `10-Source-Code/actions/patient_profile_update.php`

Implemented:

- POST request restriction.
- Patient authentication and authorization.
- CSRF token validation.
- Required-field validation.
- Input type and length checks.
- Optional numeric field validation.
- Conversion of empty optional fields to `NULL`.
- Profile updates using the authenticated patient's ID.
- Success and error feedback.

### 4.2 Frontend — Sonbol Almas

**Patient Dashboard**

File: `10-Source-Code/dashboards/patient-dashboard.php`

Implemented:

- A new **My Profile** navigation button.
- Navigation from the Patient Dashboard to the Patient Profile interface.

**Patient Profile Page**

File: `10-Source-Code/patient/profile.php`

Implemented:

- Display of existing patient profile information.
- An editable form containing all six profile fields.
- Required-field indicators.
- Browser validation for required fields.
- Optional input fields.
- Submission to the existing profile update backend.
- CSRF token submission.
- Success and error messages.
- Navigation back to the Patient Dashboard.
- HTML escaping when displaying profile information.
- Bootstrap styling consistent with the existing project interface.

## 5. Test Environment and Test Data

### 5.1 Backend Testing — Sewara

**Existing Test Patient ID:** 8

Sewara tested the repository using an existing patient account in her local MySQL database.

Temporary changes were performed inside database transactions and rolled back afterward.

The backend repository tests were performed before frontend integration.

### 5.2 Frontend Testing — Sonbol

Sonbol performed manual browser testing using her local XAMPP environment and existing test accounts.

The tests included:

- An authenticated patient account.
- A doctor account.
- A logged-out browser session.
- Two different patient accounts for profile ownership checks.
- A modified form submission containing a different patient's ID.

Temporary profile values were used to verify database persistence and input validation.

The tests were conducted through the Patient Profile interface connected to Sewara's backend.

After testing, Sonbol restored the temporary profile values using the original recorded information.

## 6. Backend Test Cases and Results — Sewara

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US04-BT01 | Retrieve an existing patient profile using patient ID 8 | Repository returned the existing patient's profile | PASS |
| US04-BT02 | Retrieve a nonexistent patient profile using ID 999999 | Repository returned `null` | PASS |
| US04-BT03 | Update an existing patient's phone number | Updated phone number was retrieved within the transaction | PASS |
| US04-BT04 | Roll back the temporary phone number change | Original phone number was restored | PASS |
| US04-BT05 | Populate and clear optional profile fields | Optional profile fields were successfully stored as `NULL` | PASS |
| US04-BT06 | Roll back temporary optional-field changes | Original optional values were restored | PASS |

**Backend Functional Testing: 6 PASS, 0 FAIL.**

## 7. PHP Syntax Verification

PHP syntax validation was performed for both backend and frontend files.

### 7.1 Backend Syntax Verification — Sewara

| File | Result |
|---|---|
| `includes/patient_profile_repository.php` | PASS |
| `includes/patient_profile_data.php` | PASS |
| `actions/patient_profile_update.php` | PASS |

**Backend PHP Syntax Checks: 3 PASS.**

### 7.2 Frontend Syntax Verification — Sonbol

| File | Result |
|---|---|
| `patient/profile.php` | PASS |
| `dashboards/patient-dashboard.php` | PASS |

**Frontend PHP Syntax Checks: 2 PASS.**

All paths above are relative to `10-Source-Code/`.

Sonbol initially attempted the syntax commands from the repository root without including the `10-Source-Code` directory. PHP reported that the input files could not be opened.

The file paths were corrected, and both frontend PHP syntax checks then passed.

The initial path errors were command-location issues, not PHP syntax failures.

### 7.3 Syntax Verification Summary

| Category | Files Checked | Passed | Failed |
|---|---:|---:|---:|
| Backend PHP | 3 | 3 | 0 |
| Frontend PHP | 2 | 2 | 0 |
| **Total** | **5** | **5** | **0** |

**PHP Syntax Verification: 5 PASS, 0 FAIL.**

Syntax checks are recorded separately and are not included in the functional test count.

## 8. Frontend and Integration Test Cases — Sonbol

Sonbol performed the following manual tests through the Patient Profile interface.

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US04-FT01 | Open the Patient Profile page | Page opened successfully and displayed all six profile fields | PASS |
| US04-FT02 | Update patient age and refresh the page | Updated age remained visible after refreshing | PASS |
| US04-FT03 | Submit the form with an empty full name | Browser prevented submission | PASS |
| US04-FT04 | Submit the form with an empty phone number | Browser prevented submission | PASS |
| US04-FT05 | Save the profile with an empty age | Profile saved successfully; age remained empty | PASS |
| US04-FT06 | Save with blood type, disease type, and close relatives count empty | All three fields remained empty after saving and refreshing | PASS |
| US04-FT07 | Doctor attempts to access the Patient Profile page | Doctor access was blocked | PASS |
| US04-FT08 | Logged-out visitor attempts to access the Patient Profile page | Unauthenticated access was blocked | PASS |
| US04-FT09 | Bypass browser validation and submit an empty full name | PHP backend rejected the update and preserved the original name | PASS |
| US04-FT10 | Bypass browser validation and submit an empty phone number | PHP backend rejected the update and preserved the original number | PASS |
| US04-FT11 | Submit the profile form with an invalid CSRF token | Backend rejected the request; profile information remained unchanged | PASS |
| US04-FT12 | While logged in as Patient B, open the profile URL with Patient A's `patient_id` parameter | Patient B's own profile remained visible; Patient A's information was not displayed | PASS |
| US04-FT13 | While logged in as Patient B, add Patient A's ID as a hidden `patient_id` field and submit the form | Patient A's recorded full name and phone number remained unchanged | PASS |

**Frontend, Integration, Validation, and Security Testing: 13 PASS, 0 FAIL.**

These results were reported during step-by-step manual testing.

Screenshots and automated browser testing logs have not been attached to this document.

## 9. Security and Data Protection Verification

The implemented backend and completed tests address the following security measures.

### 9.1 Authentication

The Patient Profile page requires an authenticated user.

**Verified by:** US04-FT08  
**Result:** PASS

### 9.2 Role-Based Authorization

Only users with the patient role may access the Patient Profile interface.

**Verified by:** US04-FT07  
**Result:** PASS

### 9.3 Profile Viewing Ownership

The backend retrieves the patient ID from the authenticated session rather than relying on a patient ID from the page URL.

During testing, Patient B remained on their own profile even when Patient A's ID was added to the URL.

**Verified by:** US04-FT12  
**Result:** PASS

### 9.4 Profile Update Ownership

Sonbol tested a modified form submission by adding a hidden `patient_id` field containing Patient A's ID while logged in as Patient B.

Following the submission, Patient A's recorded full name and phone number remained unchanged.

**Verified by:** US04-FT13  
**Result:** PASS for the attempted ID-override scenario.

**Test limitation:** The form was submitted without changing its visible field values. This test therefore records the outcome of that specific attempt; it does not establish that every possible unauthorized profile-update request has been tested.

The backend implementation additionally uses the authenticated session's patient ID for update operations.

### 9.5 Server-Side Input Validation

The backend rejects missing required fields even when browser validation is bypassed.

**Verified by:** US04-FT09 and US04-FT10  
**Result:** PASS

### 9.6 CSRF Protection

The backend validates the submitted CSRF token before processing a profile update.

**Verified by:** US04-FT11  
**Result:** PASS

### 9.7 Database Query Protection

The backend uses prepared SQL statements for profile retrieval and updates.

This protection was reviewed as part of the backend implementation.

A separate SQL injection penetration test has not been performed.

### 9.8 Output Escaping

The frontend uses HTML escaping when displaying profile values.

This was included in the frontend implementation.

A separate cross-site scripting test has not been performed.

## 10. Test Data Restoration

After completing the profile update and validation tests, Sonbol reviewed the test patient's profile values.

The original recorded values were restored where temporary test values had been entered.

The profile was saved and refreshed to confirm the restored information remained available.

**Result:** Test data restoration completed.

Sewara's backend repository tests also used database transactions and rollbacks to prevent permanent changes during testing.

These restoration activities support test data integrity and are not counted as separate functional test cases.

## 11. Combined Testing Summary

| Testing Category | Executed | Passed | Failed |
|---|---:|---:|---:|
| Backend functional tests — Sewara | 6 | 6 | 0 |
| Frontend, integration, validation, and security tests — Sonbol | 13 | 13 | 0 |
| **Total Functional and Security Tests** | **19** | **19** | **0** |

**Total Executed Functional and Security Test Cases:** 19  
**Total Passed:** 19  
**Total Failed:** 0

### Additional Verification

| Verification Activity | Passed | Failed |
|---|---:|---:|
| Backend PHP syntax checks | 3 | 0 |
| Frontend PHP syntax checks | 2 | 0 |
| **Total PHP Syntax Checks** | **5** | **0** |

**Overall Recorded Results:**

- **19 functional, integration, validation, and security tests passed.**
- **5 PHP syntax checks passed.**
- **0 failed checks.**
- Temporary test profile data was restored.

The completed tests provide evidence that the Patient Profile feature supports:

- Profile retrieval and display.
- Profile updates saved in MySQL.
- Required full name and phone number fields.
- Optional age, blood type, disease type, and close relatives count.
- Authentication and role-based authorization.
- Server-side validation of required fields.
- CSRF token validation.
- Profile viewing restricted to the authenticated patient in the tested URL scenario.
- Rejection of the tested attempt to select another patient through a submitted profile ID.

## 12. Remaining Verification and Final Integration

The following activities remain pending:

| Verification | Current Status |
|---|---|
| Backend PHP syntax validation | COMPLETED — 3 PASS |
| Frontend PHP syntax validation | COMPLETED — 2 PASS |
| Temporary test data restoration | COMPLETED |
| Patient profile URL ownership test | COMPLETED — PASS |
| Manipulated profile ID form submission test | COMPLETED — PASS for the tested scenario |
| Frontend commit and GitHub Pull Request | PENDING |
| Merge frontend into `main` | PENDING |
| Post-merge regression testing using the latest `main` branch | PENDING |
| Product Owner review and acceptance | PENDING |

The post-merge regression test will verify that the merged Patient Profile interface continues to load and update patient information correctly when combined with the latest project code.

Product Owner acceptance will be recorded only after the Product Owner reviews the implemented user story.

These pending activities are not included in the 19 passed tests.

## 13. Related UML Documentation

**Sequence Diagram:** SD08 — Patient Profile Management

The following files were created by Sewara and merged into `main`:

- `03-UML-Diagrams/Sequence-Diagrams/Sprint-3/SD08-Patient-Profile-Management-Sprint3.mmd`
- `03-UML-Diagrams/Sequence-Diagrams/Sprint-3/SD08-Patient-Profile-Management-Sprint3.png`
- `03-UML-Diagrams/Sequence-Diagrams/Sprint-3/SD08-Patient-Profile-Management-Sprint3-Plan.md`

SD08 models:

- Patient authentication and authorization.
- Profile information retrieval.
- Editing and submitting profile information.
- Server-side input validation.
- CSRF token validation.
- Database updates.
- Success and error responses.

## 14. Development and Documentation Status

| Activity | Responsible Developer | Status |
|---|---|---|
| Database schema and migration | Sewara | Completed |
| Patient Profile repository | Sewara | Completed |
| Patient Profile data loader | Sewara | Completed |
| Patient Profile update action | Sewara | Completed |
| Backend repository testing | Sewara | 6 tests passed |
| Backend PHP syntax verification | Sewara | 3 checks passed |
| SD08 Sequence Diagram | Sewara | Completed and merged |
| Patient Dashboard profile navigation | Sonbol | Implemented locally |
| Patient Profile interface | Sonbol | Implemented locally |
| Frontend and integration testing | Sonbol | 13 tests passed |
| Frontend PHP syntax verification | Sonbol | 2 checks passed |
| Test data restoration | Sonbol | Completed |
| Combined US-04 testing documentation | Development Team | Updated with 19 completed tests and 5 syntax checks |
| Frontend commit and merge | Sonbol | Pending |
| Post-merge regression testing | Development Team | Pending |
| Product Owner acceptance | Product Owner | Pending |

## 15. Conclusion

US-04 — Patient Profile Management has passed **19 documented functional, integration, validation, and security test cases**, including:

- **6 backend functional tests** performed by Sewara Rashidi.
- **13 frontend, integration, validation, and security tests** performed by Sonbol Almas.

Additionally, **five PHP files passed syntax verification**.

The completed tests confirm that the Patient Profile interface retrieves profile information, saves changes, handles optional fields, and enforces required-field validation.

Authentication restrictions, doctor-role authorization, CSRF protection, profile viewing ownership, and an attempted profile ID override during form submission were also tested successfully within the described scenarios.

The temporary profile test data was restored after testing.

**Current Status:** Backend implementation and UML documentation are merged into `main`. Frontend implementation and initial testing are completed locally. The frontend commit, GitHub merge, post-merge regression testing, and Product Owner acceptance are pending.

**Testing Summary: 19 PASS — 0 FAIL.**

**PHP Syntax Verification: 5 PASS — 0 FAIL.**