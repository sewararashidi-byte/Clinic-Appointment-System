# US-14 — Doctor Profile Management Testing Report

**Project:** Clinic Appointment System  
**Sprint:** Sprint 4  
**Testing Date:** October 29, 2026  
**User Story:** US-14 — Doctor Profile Management  
**Functional Requirement:** FR-15  
**Frontend Developer:** Sonbol Almas  
**Backend Developer:** Sewara Rashidi  
**Related Sequence Diagram:** SD13 — Doctor Profile Management  
**Testing Status:** All 10 test cases reported as passed

## 1. Testing Objective

The objective of this testing activity was to verify that authenticated doctors can view and update their professional profiles securely and accurately.

Testing also aimed to confirm that updated professional information is saved in the database, remains available after refreshing the page, and is displayed correctly in the patient-facing interface.

## 2. Requirements Under Test

The Doctor Profile Management feature supports the following functionality:

- Viewing an existing doctor's professional profile.
- Updating full name and professional contact information.
- Updating specialty and qualifications.
- Updating years of experience and biography.
- Saving changes to the existing database.
- Restricting profile management to authorized doctors.
- Preventing unauthorized modifications.
- Validating submitted information before saving.

## 3. Testing Environment

**Application:** Clinic Appointment System  
**Technology:** PHP, MariaDB, Bootstrap  
**Development Server:** XAMPP  
**Database:** `clinic_appointment_system`  
**Testing Type:** Functional and integration testing

**Relevant implementation files:**

- `10-Source-Code/doctor/profile.php`
- `10-Source-Code/actions/doctor_profile_update.php`
- `10-Source-Code/includes/doctor_profile_data.php`
- `10-Source-Code/includes/doctor_profile_repository.php`
- `10-Source-Code/config/auth.php`
- `10-Source-Code/config/database.php`

**Relevant database tables:**

- `users`
- `doctor_profiles`

## 4. Test Cases and Results

| Test ID | Test Scenario | Expected Result | Reported Result |
|---|---|---|---|
| TC-US14-01 | View an existing doctor profile | Correct professional information is displayed | Pass |
| TC-US14-02 | Update full name and phone | Updated information is saved | Pass |
| TC-US14-03 | Update specialty and qualifications | Updated details persist in the database | Pass |
| TC-US14-04 | Update experience and biography | Updated values are saved correctly | Pass |
| TC-US14-05 | Refresh the profile after updating | Previously saved changes remain visible | Pass |
| TC-US14-06 | View updated doctor information as a patient | Updated professional details appear correctly | Pass |
| TC-US14-07 | Attempt doctor-profile access using a patient account | Unauthorized access is rejected | Pass |
| TC-US14-08 | Submit an invalid CSRF token | Profile modification is rejected | Pass |
| TC-US14-09 | Submit invalid experience or missing required fields | Server-side validation rejects invalid values | Pass |
| TC-US14-10 | Attempt to modify another doctor's profile | Unauthorized modification is prevented | Pass |

## 5. Testing Execution Summary

The development team reported successful execution of all ten test cases.

The reported results indicate that the Doctor Profile Management functionality supports professional profile retrieval, updating, validation, database persistence, and role-based access control.

**Results Summary:**

| Measurement | Result |
|---|---|
| Total test cases | 10 |
| Reported passed | 10 |
| Reported failed | 0 |
| Reported untested | 0 |
| Overall reported result | Passed |

The report records team-confirmed results. Individual test executions and supporting screenshots have not been independently reviewed here.

## 6. Database Verification

The feature uses two related database tables.

**users**

Stores the doctor's account-related information, including full name and phone number.

**doctor_profiles**

Stores the doctor's specialty, qualifications, professional experience, and biography.

The team reported that profile changes were successfully saved and remained visible after refreshing the interface.

No additional database migration was necessary for this user story.

## 7. Security and Validation Testing

The reported tests covered the following protections:

- Doctor-only access to the professional profile workflow.
- Session-based identification of the authenticated doctor.
- CSRF validation for profile updates.
- Validation of required fields.
- Validation of years of experience.
- Prevention of unauthorized profile modification.

All corresponding test scenarios were reported as passed.

## 8. Test Evidence

Attach available evidence from the actual testing session, such as:

- Screenshot of the doctor profile before updating.
- Screenshot showing the successfully updated profile.
- Screenshot showing that changes persist after refreshing.
- Screenshot of the updated details in the patient-facing interface.
- phpMyAdmin verification of the saved profile fields.
- Evidence of rejected invalid or unauthorized requests.

**Evidence status:** Supporting screenshots and exact test data should be added only where they were actually captured. Do not include passwords or personal patient information.

## 9. Defects and Observations

No failures were reported during execution of the ten planned test scenarios.

No unresolved defects were reported for US-14 during this testing activity.

## 10. Conclusion

Based on the development team's reported testing results, all ten US-14 Doctor Profile Management test cases passed.

The tested functionality supports secure doctor profile access, professional information updates, database persistence, validation, and patient-facing visibility.

**US-14 Testing Result: Passed — team reported.**

Final Sprint acceptance remains subject to the team's overall integration review.