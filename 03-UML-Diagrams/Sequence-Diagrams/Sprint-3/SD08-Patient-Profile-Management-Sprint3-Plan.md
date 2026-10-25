# SD08 — Patient Profile Management Sequence Diagram

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Developer:** Sewara Rashidi  
**Date:** October 25, 2026  
**User Story:** US-04 — Patient Profile Management

---

## 1. Purpose

This sequence diagram models how an authenticated patient views and updates their personal profile in the Clinic Appointment System.

The diagram represents the interaction between the patient interface, authentication component, profile data loader, update action, repository, and MySQL database.

## 2. Requirements

According to the Product Owner's clarification, patients can view and update their own profile information.

The profile fields are:

| Field | Requirement |
|---|---|
| Full name | Required |
| Phone number | Required |
| Age | Optional |
| Blood type | Optional |
| Type of disease | Optional |
| Number of close relatives | Optional |

## 3. Participants

- **Patient:** Views and edits personal profile information.
- **Patient Profile Page:** Displays profile information and provides the editing form.
- **Authentication:** Verifies that the user is logged in with the patient role.
- **Patient Profile Data Loader:** Retrieves the authenticated patient's ID and profile information.
- **Profile Update Action:** Validates submitted information and processes profile updates.
- **Patient Profile Repository:** Performs profile retrieval and update database operations.
- **MySQL Database:** Stores patient profile information in the `users` table.

## 4. Main Sequence — View Patient Profile

1. The patient opens the My Profile page.
2. The system verifies authentication and patient-role authorization.
3. The data loader retrieves the patient's ID from the active session.
4. The system generates or reuses a CSRF token.
5. The repository retrieves the patient's profile from MySQL.
6. The profile page displays the patient's information.

## 5. Main Sequence — Update Patient Profile

1. The patient opens the profile editing form.
2. The patient modifies the required or optional information.
3. The patient submits the form.
4. The update action verifies the patient's authentication and CSRF token.
5. The system validates the submitted information.
6. Empty optional fields are converted to `NULL`.
7. The repository updates the logged-in patient's record.
8. The system redirects the patient to the profile page with the appropriate result message.
9. The updated information is retrieved and displayed.

## 6. Alternative Scenarios

### Unauthorized Access

If the user is not an authenticated patient, the system denies access or redirects them according to the existing authentication logic.

### Invalid Form Information

If the full name or phone number is missing, or another field fails validation, the system rejects the update.

### Invalid CSRF Token

If the submitted security token is invalid, the system rejects the request.

### Patient Profile Not Found

If the profile cannot be retrieved, the system returns an appropriate error message.

### Database Update Failure

If the database update fails, the system displays an error instead of reporting a successful update.

## 7. Security Considerations

- Only authenticated patients may use patient profile functionality.
- The patient ID comes from the authenticated session.
- Submitted profile identifiers are not used to select another patient's record.
- Prepared statements are used for database operations.
- The update action validates the CSRF token.
- Profile retrieval excludes password hashes.

## 8. Related Implementation Files

- `10-Source-Code/includes/patient_profile_repository.php`
- `10-Source-Code/includes/patient_profile_data.php`
- `10-Source-Code/actions/patient_profile_update.php`
- `10-Source-Code/database/schema.sql`
- `10-Source-Code/database/migrations/US04-patient-profile.sql`

## 9. Related Testing

**Document:**

`08-Testing/Sprint-3/US-04-Patient-Profile-Backend-Test-Results.md`

Six backend functional checks passed, including profile retrieval, updates, optional field handling, and rollback verification.

Frontend integration and browser-level security testing remain pending.

## 10. Diagram Files

- `SD08-Patient-Profile-Management-Sprint3.mmd` — Editable Mermaid source.
- `SD08-Patient-Profile-Management-Sprint3.png` — Sequence diagram image.
- `SD08-Patient-Profile-Management-Sprint3-Plan.md` — Diagram explanation.

## 11. Conclusion

SD08 documents the patient profile viewing and updating workflow for US-04 in Sprint 3.

The backend implementation and initial repository tests have been completed.

The modeled frontend behavior will be verified after Sonbol implements and integrates the Patient Profile page.

