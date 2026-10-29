
# SD13 — Doctor Profile Management: Sequence Diagram Plan

**Project:** Clinic Appointment System
**Sprint:** Sprint 4 (October 27–28, 2026)
**Developer:** Sewara Rashidi
**User Story:** US-14 — Doctor Profile Management
**Functional Requirement:** FR-15
**Diagram:** SD13-US14-Doctor-Profile-Management.mmd

## 1. Purpose

Model how an authenticated doctor views and updates their professional profile in the Clinic Appointment System.

The process must ensure that doctors can manage their own professional information, that changes are saved securely, and that updated information can be displayed to patients.

## 2. User Story and Acceptance Criteria

**User Story:**

As a doctor, I want to manage my professional profile so that patients can see accurate information about me.

**Acceptance Criteria:**

1. The doctor can view their professional profile.
2. The doctor can update permitted professional information.
3. The system saves the updated profile information.

**Product Owner Clarification:**

The approved professional profile information includes:

- Doctor's full name
- Specialty
- Qualifications
- Years of professional experience
- Professional contact information
- Professional biography, as previously approved

Only the owning doctor or an authorized administrator should be permitted to modify the profile.

**Implementation Scope:**

The current Sprint 4 backend implements the authenticated doctor's own profile management.

An administrator profile-editing workflow is not included in this sequence diagram.

## 3. Preconditions

- The doctor has a registered account.
- The doctor is authenticated.
- The authenticated user has the doctor role.
- The session contains the doctor's user ID.
- The database is available.
- The doctor submits a valid CSRF token when updating their profile.

## 4. Participants

| Participant | Responsibility |
|---|---|
| Doctor | Views and updates their professional profile |
| Doctor Profile Page | Displays existing information and provides the update form |
| doctor_profile_data.php | Verifies doctor access and retrieves profile information |
| doctor_profile_update.php | Validates submitted information and processes update requests |
| doctor_profile_repository.php | Retrieves and saves professional profile data |
| MariaDB | Stores doctor account and professional profile information |

## 5. Main Success Scenario

### 5.1 Viewing the Doctor Profile

1. The doctor signs in to the Clinic Appointment System.
2. The doctor opens the Doctor Profile page.
3. The page loads doctor_profile_data.php.
4. The data loader verifies the authenticated doctor's role.
5. The data loader generates or retrieves the profile CSRF token.
6. The system retrieves the doctor ID from the authenticated session.
7. The data loader establishes the database connection.
8. The data loader calls getDoctorProfessionalProfile().
9. The repository executes a prepared SELECT query.
10. MariaDB retrieves the doctor's account details from users and professional details from doctor_profiles.
11. The repository returns the profile information.
12. The data loader supplies the profile to the page.
13. The page displays the doctor's existing professional information.

### 5.2 Updating the Doctor Profile

1. The doctor selects Edit Profile.
2. The doctor modifies the permitted professional fields.
3. The doctor submits the update form.
4. The frontend sends the profile fields and CSRF token through HTTP POST.
5. doctor_profile_update.php verifies the authenticated doctor role.
6. The action retrieves the doctor ID from the session.
7. The action validates the request method and CSRF token.
8. The action validates required fields, input types, field lengths, and years of experience.
9. The action calls saveDoctorProfessionalProfile().
10. The repository begins a database transaction.
11. The repository locks the doctor's user record using FOR UPDATE.
12. The repository verifies that the doctor account exists.
13. The repository checks whether a doctor_profiles record already exists.
14. The system updates the doctor's full name and phone number in users.
15. The system updates the existing doctor_profiles record or creates one if necessary.
16. The repository commits the transaction.
17. The action updates the doctor's name in the session.
18. The action redirects to the Doctor Profile page with a success message.
19. The page retrieves and displays the updated professional information.

## 6. Alternative and Exception Scenarios

### 6.1 Unauthenticated User

If the user is not authenticated, access is denied through the existing authentication process.

### 6.2 Incorrect User Role

If the authenticated user does not have the doctor role, access to the doctor-only profile workflow is rejected.

### 6.3 Invalid Doctor Session

If the doctor ID in the session is invalid, the system rejects the operation.

### 6.4 Doctor Profile Not Found

If the doctor account does not exist, the repository cannot retrieve a valid profile.

The page displays a safe error message.

### 6.5 Missing Professional Profile Record

If the doctor account exists but no corresponding doctor_profiles record is present, the repository can create the professional profile during a valid update.

### 6.6 Invalid CSRF Token

If the submitted CSRF token does not match the session token, the update is rejected.

No profile information is changed.

### 6.7 Missing Required Information

If the doctor's full name, phone number, specialty, or qualifications are empty, the update request is rejected.

### 6.8 Invalid Experience Value

If years of experience is not a valid integer in the supported range, the update is rejected.

### 6.9 Excessive Field Length

If submitted information exceeds the allowed field lengths, the update action rejects the request.

### 6.10 Database Error

If an update or insert operation fails, the transaction is rolled back.

The system prevents partially updated information across users and doctor_profiles.

### 6.11 Attempt to Modify Another Doctor

The update action obtains the doctor ID from the authenticated session rather than from the submitted form.

Therefore, a doctor cannot use this workflow to select another doctor's profile for updating.

## 7. Database and Implementation Mapping

**Primary tables:**

- users
- doctor_profiles

**Relevant users fields:**

- id
- full_name
- phone
- role

**Relevant doctor_profiles fields:**

- id
- user_id
- specialty
- qualifications
- years_experience
- biography

**Database relationship:**

doctor_profiles.user_id references the doctor's users.id.

**Database behavior:**

- Profile retrieval uses a prepared SELECT query.
- The system verifies the user has the doctor role.
- The doctor's own user ID is obtained from the authenticated session.
- The system updates full_name and phone in users.
- The system updates specialty, qualifications, years_experience, and biography in doctor_profiles.
- A professional profile can be inserted if one does not already exist.
- A transaction ensures both tables are updated consistently.
- A failure causes a transaction rollback.

**Database migration:**

No additional database migration is required for US-14.

The implementation reuses existing tables from previous sprints.


## 8. Security and Data Validation

- Doctor-only authentication is required.
- The doctor's identity comes from the authenticated session.
- The update request must use HTTP POST.
- CSRF token validation protects profile updates.
- Required fields are validated on the server.
- Input types and field lengths are checked.
- Years of experience must be a valid integer.
- Prepared statements protect database queries.
- Transactions prevent partial updates.
- Database errors are logged without revealing internal details to users.
- Profile values must be HTML-escaped before display.
