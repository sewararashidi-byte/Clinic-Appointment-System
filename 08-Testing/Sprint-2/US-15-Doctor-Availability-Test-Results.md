# Sprint 2 — US-15 Doctor Availability Management Test Results

## Project

Clinic Appointment System

## Sprint

Sprint 2 — Doctor Availability, Doctor Search, and Appointment Booking

## Testing Period

Sprint 2 — Day 1, October 17, 2026

## Related User Story

**US-15 — Doctor Availability Management**

As a doctor, I want to manage my availability so that patients can book appointments during my available times.

### Acceptance Criteria

1. Doctor can set available dates and times.
2. Doctor can update their availability.
3. Updated availability is reflected when patients view appointment slots.

**Implementation note:** The third acceptance criterion requires patient-side appointment-slot functionality, which was not available on Day 1. Its verification remains pending until the relevant patient booking interface is implemented and tested.

---

## Test Environment

- Local development environments using XAMPP
- PHP backend
- MySQL database through phpMyAdmin
- Google Chrome browser
- Visual Studio Code
- Git and GitHub Desktop
- Database: `clinic_appointment_system`

### Database Tables

- `users`
- `doctor_availability`

The `doctor_profiles` table was also created as part of the Sprint 2 database foundation, but professional profile management was not included in the US-15 tests.

### Implementation Files

- `dashboards/doctor-dashboard.php`
- `doctor/availability.php`
- `actions/availability_create.php`
- `actions/availability_update.php`
- `actions/availability_delete.php`
- `config/auth.php`
- `config/database.php`
- `database/schema.sql`

## Test Data Setup

An existing test account was configured for doctor availability testing.

**Test Doctor:**

- Name: Test Doctor
- Role: `doctor`
- Account Status: `active`

**Test Patient:**

- Role: `patient`
- Account Status: `active`

For local testing, the doctor test account was prepared using a manual role and account-status adjustment in phpMyAdmin.

This was a test fixture only. It does not represent implementation of the administrator's future account-creation functionality.

The test doctor was used to create, view, update, and delete availability records. An active patient account was used to verify access restrictions.

---

## TC-AVAIL-01 — Create Doctor Availability

**Test Procedure**

1. Logged in using the active doctor test account.
2. Opened the Doctor Dashboard.
3. Selected Manage Availability.
4. Entered an availability date, start time, and end time.
5. Submitted the Add Availability form.
6. Checked that the availability was saved in MySQL.

**Expected Result**

- The system accepts valid availability information.
- The availability record is associated with the logged-in doctor.
- The record is stored in `doctor_availability`.
- The doctor receives a success message.

**Actual Result**

The doctor successfully created an availability record.

The system displayed a success response, and the inserted record was verified in the MySQL database.

**Status:** PASS

---

## TC-AVAIL-02 — View Existing Doctor Availability

**Test Procedure**

1. Logged in as Test Doctor.
2. Opened Manage Availability.
3. Checked the My Availability section.
4. Examined the availability records displayed.

**Expected Result**

The doctor can view existing availability records, including the date, start time, end time, and slot duration.

**Actual Result**

The previously created availability was displayed in the My Availability table with its stored date and time information.

**Status:** PASS

---

## TC-AVAIL-03 — Update Doctor Availability

**Test Procedure**

1. Logged in as Test Doctor.
2. Opened Manage Availability.
3. Selected an existing availability record.
4. Changed the availability times.
5. Clicked Update.
6. Checked the updated availability information.

**Expected Result**

- The system allows the doctor to update an existing availability record.
- The modified values are saved in MySQL.
- The updated information is displayed.
- The doctor receives an appropriate success response.

**Actual Result**

The doctor successfully updated the selected availability record.

The updated times were saved and displayed by the Doctor Availability Management interface.

**Status:** PASS

---

## TC-AVAIL-04 — Delete Doctor Availability

**Test Procedure**

1. Logged in as Test Doctor.
2. Opened Manage Availability.
3. Selected an availability record for deletion.
4. Clicked Delete.
5. Confirmed the deletion.
6. Checked the remaining availability records.

**Expected Result**

- The interface requests confirmation.
- The selected availability record is deleted after confirmation.
- The deleted record no longer appears in the doctor's availability list.
- The system displays a success response.

**Actual Result**

The doctor successfully deleted the selected availability record.

The deletion operation completed, and the removed record was no longer shown in the availability list.

**Status:** PASS

---

## TC-AVAIL-05 — Patient Attempts to Access Doctor Availability Management

**Test Account**

- Role: `patient`
- Account Status: `active`

**Test Procedure**

1. Logged in as an active patient.
2. Manually entered the URL of the doctor availability-management page.
3. Attempted to access the page.

**Expected Result**

- A patient must not access doctor-only availability management.
- The system denies access.
- The patient is redirected to the appropriate authorized dashboard.

**Actual Result**

The system blocked the patient's access to the Doctor Availability Management page and redirected the user.

**Status:** PASS

---

## Testing Summary

A total of **5 test cases** were performed successfully during Sprint 2 Day 1.

| Test Case | Result |
|---|---|
| TC-AVAIL-01 — Create availability | PASS |
| TC-AVAIL-02 — View availability | PASS |
| TC-AVAIL-03 — Update availability | PASS |
| TC-AVAIL-04 — Delete availability | PASS |
| TC-AVAIL-05 — Patient access restriction | PASS |
| **Total** | **5 PASS, 0 FAIL** |

## Verified Functionality

The tests confirmed that:

- An authenticated doctor can create availability.
- Availability records are stored in MySQL.
- A doctor can view existing availability.
- A doctor can update availability information.
- A doctor can delete availability records.
- Doctor availability management is restricted to authorized doctor accounts.
- The Doctor Availability interface communicates successfully with the PHP backend and MySQL database.

## Testing Notes

During initial availability entry, an incorrect AM/PM time selection caused the start-time validation to reject the submitted values. After correcting the entered times, the availability was saved successfully.

No PHP code change was required for this input issue.

The availability-management backend was implemented by Sewara, and the user interface was implemented by Sonbol. The components were integrated and tested as part of Sprint 2 Day 1.

Separate local MySQL databases were used during development. Records created on one Developer's computer were not automatically available on the other Developer's computer.

## Remaining Tests and Limitations

The following were not verified during Day 1:

- Whether updated availability appears as individual appointment slots on the patient booking interface.
- Whether existing appointments are protected when a doctor updates or deletes availability.
- Whether the system handles all overlapping availability-range scenarios.
- Whether direct unauthorized update/delete requests are rejected under separate negative test cases.

These results must not be marked PASS until the corresponding tests are performed.

Patient-side doctor availability display was subsequently tested during Sprint 2 Day 2 and documented separately in:

`US-05-US-06-Test-Results.md`

Individual appointment-slot generation and appointment-booking tests remain part of the planned Sprint 2 Day 3 work.

## Day 1 Testing Conclusion

The implemented create, view, update, delete, and role-restricted access functions for **US-15 Doctor Availability Management** passed the five recorded Day 1 tests.

The results provide evidence that the basic doctor availability-management functionality works with PHP and MySQL.

**US-15 is not yet considered fully acceptance-tested**, because its patient-side appointment-slot acceptance criterion remains to be verified during the booking integration.