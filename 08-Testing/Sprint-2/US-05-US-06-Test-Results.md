# Sprint 2 — US-05 and US-06 Test Results

## Project

Clinic Appointment System

## Sprint

Sprint 2 — Doctor Availability, Doctor Search, and Appointment Booking

## Testing Period

Sprint 2 — Day 2, October 18, 2026

## Related User Stories

**US-05 — Doctor Search**

As a patient, I want to search for doctors so that I can find a suitable doctor for my appointment.

**Acceptance Criteria:**

1. Patient can enter search criteria.
2. System displays matching doctors.
3. Patient can select a doctor to view their information.

**US-06 — Doctor Information and Availability**

As a patient, I want to view a doctor's professional information and availability so that I can choose a suitable doctor and time.

**Acceptance Criteria:**

1. Patient can view the doctor's professional information.
2. Patient can view the doctor's available dates and times.
3. Displayed availability reflects the doctor's current availability.

---

## Test Environment

- Local development environments on Sewara's and Sonbol's computers
- XAMPP with Apache and MySQL
- PHP backend
- MySQL database managed through phpMyAdmin
- Google Chrome browser
- Visual Studio Code
- Git and GitHub Desktop
- Database: `clinic_appointment_system`

**Database Tables:**

- `users`
- `doctor_profiles`
- `doctor_availability`

**Main Implementation Files:**

- `includes/doctor_repository.php`
- `patient/doctor-search.php`
- `patient/doctor-details.php`
- `dashboards/patient-dashboard.php`
- `config/auth.php`
- `config/database.php`

## Test Data Setup

An existing active doctor test account was used for the implementation tests.

**Test Doctor:**

- User ID: `3`
- Name: Test Doctor
- Role: `doctor`
- Account Status: `active`
- Specialty: Cardiology
- Qualifications: MBBS, MD in Cardiology
- Years of Experience: 8
- Biography: Sample doctor profile for testing the Clinic Appointment System. Specializes in cardiovascular care.

**Test Patient:**

- Name: Active Patient
- Role: `patient`
- Account Status: `active`

The doctor profile information was inserted into the `doctor_profiles` table for testing purposes.

Sewara and Sonbol used separate local MySQL databases. Therefore, the test doctor profile was added to each local database independently.

The professional information is fictional test data and does not represent verified medical credentials.

These database changes were used as development test fixtures and do not represent implementation of the later Doctor Profile Management user story.

---

# Part A — Backend Testing (Sewara)

## TC-SEARCH-01 — Search by Medical Specialty

**Test Data**

- Specialty: `Cardiology`

**Test Procedure**

1. Logged in as an active patient.
2. Executed the `searchDoctors()` function using a temporary local PHP test file.
3. Supplied `Cardiology` as the specialty search criterion.
4. Examined the returned doctor records.

**Expected Result**

The function returns active doctors whose specialty matches Cardiology.

**Actual Result**

The function returned one matching doctor:

- Doctor ID: `3`
- Name: Test Doctor
- Specialty: Cardiology

**Status:** PASS

---

## TC-SEARCH-02 — Search by Doctor Name

**Test Data**

- Doctor Name: `Test`

**Test Procedure**

1. Executed the `searchDoctors()` function.
2. Supplied `Test` as the doctor-name search criterion.
3. Examined the returned results.

**Expected Result**

The function returns active doctors whose names contain the entered search text.

**Actual Result**

The function returned:

- Doctor ID: `3`
- Name: Test Doctor
- Specialty: Cardiology

**Status:** PASS

---

## TC-SEARCH-03 — Search With No Matching Doctor

**Test Data**

- Doctor Name: `NonexistentDoctorXYZ`

**Test Procedure**

1. Executed `searchDoctors()` using a nonexistent doctor name.
2. Checked the number of returned records.

**Expected Result**

The function returns an empty result set without a PHP error.

**Actual Result**

The output displayed:

`Matching doctors: 0`

**Status:** PASS

---

## TC-DETAILS-01 — Retrieve Doctor Professional Information

**Test Data**

- Doctor ID: `3`

**Test Procedure**

1. Executed `getDoctorById()` using a temporary local PHP test file.
2. Supplied Doctor ID `3`.
3. Examined the returned professional information.

**Expected Result**

The function retrieves the selected active doctor's professional information from the database.

**Actual Result**

The function returned:

- Doctor ID: 3
- Name: Test Doctor
- Specialty: Cardiology
- Qualifications: MBBS, MD in Cardiology
- Years of Experience: 8
- Professional Biography: Sample doctor profile for testing the Clinic Appointment System. Specializes in cardiovascular care.

**Status:** PASS

---

## TC-AVAIL-01 — Retrieve Current Doctor Availability

**Test Data**

- Doctor ID: `3`

**Test Procedure**

1. Executed `getDoctorAvailability()` using a temporary local PHP test file.
2. Initially received zero availability records.
3. Checked the `doctor_availability` table and confirmed it was empty.
4. Logged in as Test Doctor and created an availability record through the Doctor Availability Management interface.
5. Logged back in as Active Patient.
6. Executed the availability retrieval test again.

**Expected Result**

The function returns the doctor's stored current or future availability records.

**Actual Result**

The function returned one record:

- Availability ID: `1`
- Doctor ID: `3`
- Date: October 19, 2026
- Start Time: 05:30 AM
- End Time: 06:00 AM
- Slot Duration: 30 minutes

The initial empty result was consistent with the database state. After availability was created, the function successfully retrieved the record.

**Status:** PASS

---

## TC-DETAILS-02 — Invalid Doctor ID

**Test Data**

- Doctor ID: `999999`

**Test Procedure**

1. Executed `getDoctorById()` with the nonexistent doctor ID.
2. Examined the returned result.

**Expected Result**

The function must not return another doctor's information when an invalid ID is supplied.

**Actual Result**

The test displayed:

`Doctor not found.`

**Status:** PASS

---

# Part B — Frontend and Integration Testing (Sonbol)

## TC-UI-SEARCH-01 — Display Matching Doctors

**Test Data**

- Medical Specialty: `Cardiology`

**Test Procedure**

1. Logged in as Active Patient.
2. Opened the Doctor Search page.
3. Entered `Cardiology` in the Medical Specialty field.
4. Clicked Search Doctors.

**Expected Result**

The system displays matching doctors retrieved from MySQL.

**Actual Result**

The Doctor Search page displayed Test Doctor, including the Cardiology specialty and qualifications.

**Status:** PASS

---

## TC-UI-SEARCH-02 — Display No-Results Message

**Test Data**

- Doctor Name: `UnknownDoctorXYZ`

**Test Procedure**

1. Opened the Doctor Search page.
2. Entered a nonexistent doctor name.
3. Submitted the search.

**Expected Result**

The interface displays a clear message when no doctors match.

**Actual Result**

The page displayed:

`No doctors found matching your search criteria.`

**Status:** PASS

---

## TC-UI-DETAILS-01 — Select Doctor and View Professional Information

**Test Procedure**

1. Logged in as Active Patient.
2. Searched for Cardiology.
3. Selected View Doctor for Test Doctor.
4. Examined the Doctor Details page.

**Expected Result**

The selected doctor's professional information is displayed.

**Actual Result**

The Doctor Details page opened and displayed:

- Test Doctor
- Cardiology
- MBBS, MD in Cardiology
- 8 years of experience
- Professional biography

**Status:** PASS

---

## TC-UI-AVAIL-01 — Display Doctor Availability

**Test Data**

- Doctor ID: `3`

**Test Procedure**

1. Opened Test Doctor's details page as an active patient.
2. Scrolled to Current Doctor Availability.
3. Examined the availability table.

**Expected Result**

The page displays the doctor's current availability date, start time, end time, and slot duration.

**Actual Result**

The page displayed:

- Date: October 20, 2026
- Start Time: 10:00 AM
- End Time: 10:30 AM
- Slot Duration: 30 minutes

**Status:** PASS

---

## TC-UI-AVAIL-02 — Updated Availability Reflected to Patient

**Test Procedure**

1. Logged in as Test Doctor.
2. Opened Manage Availability.
3. Updated the October 20 availability record from 10:00–10:30 AM to 11:00–11:30 AM.
4. Saved the updated availability.
5. Logged out of the doctor account.
6. Logged in as Active Patient.
7. Opened Test Doctor's details page.
8. Checked the availability table.

**Expected Result**

The patient should see the updated availability instead of the previous time.

**Actual Result**

The patient-facing Doctor Details page displayed the updated time of 11:00–11:30 AM.

**Status:** PASS

---

## TC-UI-DETAILS-02 — Invalid Doctor ID in Browser

**Test Data**

- Doctor ID: `999999`

**Test Procedure**

1. Logged in as an active patient.
2. Manually opened the Doctor Details page with an invalid doctor ID.

**Expected Result**

The page displays an appropriate message without exposing another doctor's data.

**Actual Result**

The page displayed:

`Doctor not found or unavailable.`

**Status:** PASS

---

## TC-UI-NAV-01 — Patient Dashboard Navigation

**Test Procedure**

1. Logged in as Active Patient.
2. Opened the Patient Dashboard.
3. Clicked Find a Doctor.
4. Searched for Cardiology.

**Expected Result**

The patient can access Doctor Search from the Patient Dashboard and view matching results.

**Actual Result**

The Doctor Search page opened successfully, and Test Doctor appeared in the search results.

**Status:** PASS

---

## TC-UI-SEARCH-03 — Search by Doctor Name

**Test Data**

- Doctor Name: `Test`

**Test Procedure**

1. Opened the Doctor Search page.
2. Entered `Test` in the Doctor Name field.
3. Left Medical Specialty empty.
4. Clicked Search Doctors.

**Expected Result**

The system displays doctors whose names match the entered search criterion.

**Actual Result**

Test Doctor appeared in the search results.

**Status:** PASS

---

## TC-UI-AUTH-01 — Unauthenticated Doctor Search Access

**Test Procedure**

1. Logged out of the system.
2. Manually opened the Doctor Search page URL.

**Expected Result**

Unauthenticated users cannot access the protected Doctor Search page.

**Actual Result**

The system redirected the user to the Login page.

**Status:** PASS

---

## TC-UI-AUTH-02 — Doctor Attempts to Access Patient-Only Search

**Test Account**

- Role: `doctor`
- Account Status: `active`

**Test Procedure**

1. Logged in as Test Doctor.
2. Manually entered the patient Doctor Search page URL.

**Expected Result**

A doctor must not be allowed to access the patient-only Doctor Search interface.

**Actual Result**

The system blocked access and redirected the doctor to the Doctor Dashboard.

**Status:** PASS

---

# Part C — Final Merged Integration Testing (Sewara)

## TC-INTEGRATION-01 — Complete Doctor Search and Information Workflow

**Test Environment**

Sewara's local XAMPP and MySQL environment after pulling the merged `main` branch.

**Test Procedure**

1. Updated the local `main` branch with the merged frontend and backend changes.
2. Logged in as Active Patient.
3. Opened the Patient Dashboard.
4. Clicked Find a Doctor.
5. Entered `Cardiology` as the medical specialty.
6. Submitted the search.
7. Selected Test Doctor.
8. Opened the Doctor Details page.
9. Checked the doctor's professional information and availability.

**Expected Result**

The complete workflow works with the merged PHP frontend, backend, and MySQL database.

**Actual Result**

The workflow completed successfully on Sewara's computer. The patient was able to search for Test Doctor, open the correct Doctor Details page, and view professional information and stored availability.

**Status:** PASS

---

# Testing Summary

A total of **17 test cases** were completed successfully during Sprint 2 Day 2.

| Test Category | Passed | Failed |
|---|---:|---:|
| Backend Testing — Sewara | 6 | 0 |
| Frontend and Integration Testing — Sonbol | 10 | 0 |
| Final Merged Integration — Sewara | 1 | 0 |
| **Total** | **17** | **0** |

## Verified Functionality

The tests confirmed that:

- Active doctors with professional profiles can be retrieved from MySQL.
- Doctor search supports name and medical specialty criteria.
- Matching doctors are displayed on the patient interface.
- Searches with no results display a clear message.
- Patients can select a doctor and view professional information.
- Patients can view stored current and future doctor availability.
- Doctor availability updates are reflected on the patient-facing Doctor Details page.
- Invalid doctor IDs are handled correctly.
- Patients can navigate from the Patient Dashboard to Doctor Search.
- Unauthenticated users cannot access protected Doctor Search.
- Doctor-role users cannot access patient-only Doctor Search.
- The merged frontend and backend function together on Sewara's local development environment.

## Testing Notes

During frontend integration, the Doctor Search page initially generated a PHP error because the `__DIR__` constant had been entered incorrectly in file include statements.

The affected lines were corrected, and the page was retested successfully.

Sonbol's Doctor Search initially returned no matching Cardiology doctors because her local `doctor_profiles` table was empty. After inserting the required fictional test doctor profile, the matching-doctor search succeeded.

On Sewara's computer, the initial doctor-availability retrieval returned zero records because the local `doctor_availability` table was empty. After creating availability through the Doctor Availability Management interface, the availability retrieval test passed.

These issues were identified and resolved during actual development and integration work.

Temporary diagnostic PHP test pages were removed after the backend tests were completed.

## Remaining Work

The following functionality has not yet been completed or verified as part of Day 2:

- Generation and display of individual bookable appointment slots.
- Selecting an appointment slot.
- Saving a patient appointment to MySQL.
- Displaying the booking-success message.
- Full prevention of double-booking under US-08.
- End-to-end appointment-booking regression testing.

These activities belong to subsequent Sprint 2 work and separate backlog items.

## Day 2 Testing Conclusion

The implemented functionality for **US-05 Doctor Search** and **US-06 Doctor Information and Availability** passed the recorded backend, frontend, authorization, and integration tests.

The results demonstrate that an authenticated patient can search for a doctor, select a matching doctor, and view that doctor's professional information and current stored availability.

The tests provide evidence for evaluating the US-05 and US-06 acceptance criteria. Final acceptance remains subject to the team's Sprint Review and Product Owner confirmation.