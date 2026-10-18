# SD-05 — Doctor Search and Information Sequence Diagram Plan

## 1. Related User Stories

**US-05 — Doctor Search**

As a patient, I want to search for doctors so that I can find a suitable doctor for my appointment.

**US-06 — Doctor Information and Availability**

As a patient, I want to view a doctor's professional information and availability so that I can choose a suitable doctor and time.

## 2. Purpose

This sequence diagram models how an authenticated patient searches for doctors, selects a doctor, and views the doctor's professional information and current availability.

The diagram covers:

- Doctor search by name and medical specialty.
- Retrieval of matching doctor records.
- Selection of a doctor.
- Retrieval of professional information.
- Retrieval of current and future availability.
- Authorization and alternative responses.

## 3. Main Participants

- Patient
- Doctor Search Page
- Doctor Details Page
- Authentication (`auth.php`)
- Doctor Repository (`doctor_repository.php`)
- MySQL Database

## 4. Main Flow

### 4.1 Search for Doctors — US-05

1. The patient opens the Doctor Search page.
2. The system verifies that the user is authenticated and authorized as a patient.
3. The patient enters a doctor name, medical specialty, or both.
4. The page requests matching doctors using `searchDoctors()`.
5. The backend queries the `users` and `doctor_profiles` tables.
6. The database returns matching active doctors with professional profiles.
7. The page displays the matching doctors.
8. The patient selects a doctor to view more information.

### 4.2 View Doctor Information — US-06

1. The patient selects a doctor from the search results.
2. The Doctor Details page receives the selected doctor's ID.
3. The system verifies patient authorization.
4. The backend calls `getDoctorById()`.
5. The database retrieves the selected doctor's professional information.
6. The page displays the doctor's name, specialty, qualifications, years of experience, and biography.

### 4.3 View Doctor Availability — US-06

1. The backend calls `getDoctorAvailability()` for the selected doctor.
2. The database retrieves the doctor's current and future availability ranges.
3. The backend returns availability dates, start times, end times, and slot durations.
4. The Doctor Details page displays the availability information.

## 5. Alternative and Error Flows

- If no doctors match the search criteria, the system displays a no-results message.
- If the selected doctor ID is invalid or does not identify an active doctor with a profile, the system displays a doctor-not-found message.
- If no current or future availability exists, the system indicates that no availability is currently listed.
- If the user is not authorized as a patient, access to the patient-facing pages is restricted.
- Database failures must be handled without exposing sensitive database details to users.

## 6. Implementation Files

The backend functions have been implemented and locally tested in:

`10-Source-Code/includes/doctor_repository.php`

Functions:

- `searchDoctors()`
- `getDoctorById()`
- `getDoctorAvailability()`

Patient-facing interface files:

- `10-Source-Code/patient/doctor-search.php`
- `10-Source-Code/patient/doctor-details.php` (planned)

Supporting files:

- `10-Source-Code/config/auth.php`
- `10-Source-Code/config/database.php`

**Implementation status:** The backend functions have been developed and tested. The complete patient-facing Doctor Search and Doctor Details integration is still pending.

## 7. Database Tables

- `users`
- `doctor_profiles`
- `doctor_availability`

The diagram uses the existing database relationships. No new tables are required for US-05 or US-06.

## 8. Testing Considerations

The following backend tests have been performed successfully:

- Search by medical specialty.
- Search by doctor name.
- Search with no matching results.
- Retrieve an existing doctor's professional information.
- Retrieve an existing doctor's availability.
- Reject an invalid doctor ID.

Patient-facing integration, navigation, and authorization testing will be completed after frontend integration.

## 9. Expected Outcome

The diagram should clearly demonstrate how an authenticated patient searches for doctors, selects an appropriate doctor, and views professional information and current availability through PHP/MySQL interactions.

## 10. Sprint Information

**Sprint:** Sprint 2

**Day:** Day 2

**Diagram ID:** SD-05

**Diagram Name:** Doctor Search and Information