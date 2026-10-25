# US-04 — Patient Profile Management: Backend Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Testing Date:** October 25, 2026  
**Tester:** Sewara Rashidi  
**User Story:** US-04 — Patient Profile Management  
**Development Branch:** `feature/s3-patient-profile-backend`  
**Environment:** PHP, MySQL, XAMPP, phpMyAdmin, PHP CLI

---

## 1. Testing Objective

Verify that the patient profile backend retrieves and updates profile information correctly while supporting the required and optional fields specified by the Product Owner.

The tests also verify that temporary database changes can be rolled back without permanently modifying existing patient information.

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

## 3. Database Implementation

The existing `users` table was extended to support patient profile management.

The following changes were made:

- Changed `age` to allow `NULL`.
- Added `blood_type`.
- Added `disease_type`.
- Added `close_relatives_count`.
- Preserved the existing `full_name` and `phone` columns.

The database migration was executed successfully on Sewara's local MySQL database.

**Migration file:**

`10-Source-Code/database/migrations/US04-patient-profile.sql`

The updated columns were verified through phpMyAdmin.

A database backup was created before applying the migration.

## 4. Backend Implementation

### 4.1 Patient Profile Repository

**File:** `10-Source-Code/includes/patient_profile_repository.php`

Implemented:

- `getPatientProfile()` — Retrieves the specified patient's profile.
- `updatePatientProfile()` — Updates the patient's profile fields.

Both functions use prepared SQL statements.

The repository restricts profile operations to records belonging to accounts with the `patient` role.

### 4.2 Patient Profile Data Loader

**File:** `10-Source-Code/includes/patient_profile_data.php`

Implemented:

- Patient role verification.
- Retrieval of patient ID from the authenticated session.
- Loading the patient's profile through the repository.
- CSRF token generation for the profile form.
- Error handling when the patient profile cannot be retrieved.

### 4.3 Patient Profile Update Action

**File:** `10-Source-Code/actions/patient_profile_update.php`

Implemented:

- POST request restriction.
- Patient authentication and authorization.
- CSRF token validation.
- Required-field validation.
- Input type and length checks.
- Validation of optional numeric fields.
- Conversion of empty optional fields to `NULL`.
- Profile updating using the authenticated patient's ID.
- Success and error feedback.

## 5. Backend Test Data

**Existing Test Patient ID:** 8

The test used an existing patient account in Sewara's local database.

Temporary test values were used inside database transactions and rolled back after execution.

No permanent changes to the patient's profile were intended during these CLI tests.

## 6. Test Cases and Results

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US04-BT01 | Retrieve an existing patient profile using patient ID 8 | The repository returned the existing patient's profile | PASS |
| US04-BT02 | Retrieve a nonexistent patient profile using ID 999999 | The repository returned `null` | PASS |
| US04-BT03 | Update an existing patient's phone number through the repository | The updated phone number was retrieved successfully within the transaction | PASS |
| US04-BT04 | Roll back the temporary phone number change | The original phone number was restored | PASS |
| US04-BT05 | Populate and then clear all optional profile fields | Age, blood type, disease type, and close relatives count were stored as `NULL` | PASS |
| US04-BT06 | Roll back temporary optional-field changes | The original optional profile values were restored | PASS |

## 7. PHP Syntax Verification

The following files passed PHP syntax validation:

| File | Result |
|---|---|
| `includes/patient_profile_repository.php` | PASS |
| `includes/patient_profile_data.php` | PASS |
| `actions/patient_profile_update.php` | PASS |

All paths above are relative to `10-Source-Code/`.

No PHP syntax errors were reported.

## 8. Data Protection and Testing Method

The backend uses prepared SQL statements to handle profile database operations.

The profile data loader obtains the patient ID from the authenticated session rather than from a user-submitted profile ID.

The update action also uses the authenticated session and validates the submitted CSRF token.

Temporary repository update tests were performed inside database transactions. The transactions were rolled back to preserve existing patient information.

The temporary test file `us04_profile_read_test_temp.php` was deleted after testing.

## 9. Backend Test Summary

| Category | Executed | Passed | Failed |
|---|---:|---:|---:|
| Profile retrieval | 2 | 2 | 0 |
| Profile update and rollback | 2 | 2 | 0 |
| Optional fields and rollback | 2 | 2 | 0 |
| **Total** | **6** | **6** | **0** |

**Backend Functional Checks:** 6 PASS, 0 FAIL

Additionally, three PHP files passed syntax validation.

## 10. Remaining Tests and Integration Work

The following activities are not yet verified:

1. Required full name and phone validation through the actual profile form.
2. Rejection of invalid CSRF tokens.
3. Browser-level patient authorization and profile ownership restrictions.
4. End-to-end profile update and reload.
5. Sonbol's frontend integration.
6. Deployment of the database migration to Sonbol's local database.
7. Post-merge regression testing.
8. SD08 — Patient Profile Management Sequence Diagram.

These items must be tested and documented using actual results.

## 11. Conclusion

The US-04 backend repository passed six functional checks involving patient profile retrieval, updates, optional-field handling, and database rollback.

The database migration was applied successfully, and all three backend PHP files passed syntax verification.

**Current Status:** Sewara's initial US-04 backend implementation and repository testing completed locally. Frontend integration, validation testing, GitHub merge, and final acceptance remain pending.
