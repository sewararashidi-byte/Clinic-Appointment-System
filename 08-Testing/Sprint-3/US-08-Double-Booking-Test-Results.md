
# US-08 — Prevent Double-Booking: Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Testing Dates:** October 22–23, 2026  
**Tester:** Sewara Rashidi  
**User Story:** US-08 — Prevent Double-Booking  
**Environment:** PHP, MySQL, XAMPP, web browser, PHP CLI

---

## 1. Testing Objective

Verify that the Clinic Appointment System prevents two patients from booking the same appointment slot and excludes appointment slots that overlap existing confirmed bookings.

## 2. Test Scenario

Two different authenticated patients attempted to book the same appointment slot.

**Test Appointment:**

- Doctor ID: 3
- Date: October 23, 2026
- Time: 10:00–10:30 AM
- Confirmed Appointment ID: 5
- Appointment Status: confirmed

The first patient successfully booked the appointment. The second patient attempted to book the same slot using a previously loaded Doctor Details page.

The system rejected the second booking attempt.

## 3. Test Cases and Results

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US08-T01 | Patient 1 books an available appointment slot | Booking succeeded and a confirmed appointment was created | PASS |
| US08-T02 | Patient 2 attempts to book the same slot from an unchanged page | The system rejected the booking and displayed an already-booked message | PASS |
| US08-T03 | Verify duplicate-booking prevention in MySQL | Only one appointment was stored for the tested doctor, date, and starting time | PASS |
| US08-T04 | Refresh Patient 2's Doctor Details page | The booked 10:00–10:30 AM slot disappeared; the available 10:30–11:00 AM slot remained visible | PASS |
| US08-T05 | Check whether a hypothetical overlapping appointment slot is excluded | One candidate slot was generated, but zero available slots were returned | PASS |

## 4. Test Summary

**Total Tests Executed:** 5  
**Tests Passed:** 5  
**Tests Failed:** 0

The results demonstrate that the application rejected a duplicate booking and correctly excluded an overlapping appointment slot in the tested scenarios.

The existing MySQL unique index, `uq_doctor_appointment_slot`, was also verified.

This index protects the combination of:

- `doctor_id`
- `appointment_date`
- `start_time`

The database unique constraint prevents duplicate appointment records with the same doctor, date, and starting time.

However, prevention of overlapping appointments with different starting times also depends on the application's booking validation.

## 5. Additional Database Inspection

**Date:** October 22, 2026  
**Tester:** Sewara Rashidi

### Objective

Determine whether overlapping doctor availability periods currently exist in the local database.

### Testing Method

A read-only SQL query compared availability records belonging to the same doctor on the same date.

### Actual Result

MySQL returned zero rows.

### Finding

No overlapping doctor availability periods were found in the local test database at the time of inspection.

This result does not prove that the system prevents doctors from creating overlapping availability periods.

### Follow-up

The Product Owner should clarify whether doctors are allowed to create overlapping availability periods.

The appointment overlap-filtering test was subsequently performed and passed on October 23, 2026.

Truly simultaneous booking requests and end-to-end booking with overlapping availability periods remain untested.

## 6. Overlapping Appointment Test

**Date:** October 23, 2026  
**Tester:** Sewara Rashidi  
**Test ID:** US08-T05

### Objective

Verify that the appointment repository excludes a candidate appointment that overlaps existing confirmed bookings.

### Test Data

- Doctor ID: 3
- Appointment Date: October 26, 2026
- Hypothetical Appointment Start Time: 2:15 PM
- Hypothetical Appointment End Time: 2:45 PM
- Appointment Duration: 30 minutes

### Test Procedure

1. A temporary PHP CLI test file was created.
2. A hypothetical availability record was defined in PHP without inserting it into MySQL.
3. The `generateAppointmentSlots()` function generated a candidate appointment slot.
4. The `getAvailableAppointmentSlots()` function checked the candidate against existing confirmed appointments.
5. The returned available slots were counted.

### Expected Result

The hypothetical appointment must not be displayed as available because its time overlaps existing confirmed appointments.

### Actual Terminal Output

```text
Candidate slots: 1
Available slots: 0
PASS: Overlapping slot correctly excluded.
```

### Test Result

**PASS**

The PHP appointment repository correctly excluded the hypothetical overlapping slot.

This was a read-only backend test. No appointment or availability records were created, modified, or deleted.

The temporary test file `us08_overlap_temp_check.php` was deleted after testing.

## 7. Remaining Verification

The following scenarios remain untested:

1. Truly simultaneous booking requests from two separate authenticated patient sessions.
2. End-to-end booking behavior when overlapping doctor availability periods exist.
3. Product Owner confirmation of whether overlapping doctor availability periods should be prohibited.

These items should remain open until verified or addressed through an agreed follow-up.

## 8. Conclusion

Five US-08 tests were executed during October 22–23, 2026.

**All five tests passed.**

The results confirm that the system prevents duplicate booking of the same appointment slot through the tested workflow and correctly excludes overlapping appointment times in the tested PHP repository scenario.

The existing PHP booking validation and MySQL unique constraint provide important protections against double-booking.

No change to the appointment-booking implementation was required based on the completed tests.

However, concurrent booking and end-to-end overlapping-availability scenarios have not yet been fully verified.

**Current Status:** Five tests passed; additional verification remains pending. Full US-08 completion has not yet been confirmed.


---

## Additional US-08 Testing — Doctor Availability Overlap Protection

**Date:** October 24, 2026
**Tester:** Sewara Rashidi
**Branch:** feature/s3-availability-overlap-protection

### Objective

Verify that doctors cannot create overlapping availability
periods on the same date, as confirmed by the Product Owner.

### Test Cases and Results

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US08-AV01 | Create partially overlapping availability (14:30–15:30) when 14:00–15:00 exists | Request rejected; MySQL retained only the original period | PASS |
| US08-AV02 | Create an exact duplicate (14:00–15:00) | System rejected the duplicate | PASS |
| US08-AV03 | Create adjacent availability (15:00–16:00) | Request accepted; MySQL confirmed both adjacent periods | PASS |
| US08-AV04 | Create availability on a different date (October 27, 14:00–15:00) | Request accepted and record verified in MySQL | PASS |
| US08-AV05 | Submit competing overlapping periods using two browser sessions | First request accepted; second rejected; MySQL contained only one record | PASS |
| US08-AV06 | Check database storage engines | Both users and doctor_availability tables use InnoDB | PASS |

### Database Evidence

Doctor ID 3, October 26, 2026:
- Availability ID 4: 14:00–15:00
- Availability ID 6: 15:00–16:00

Doctor ID 3, October 27, 2026:
- Availability ID 7: 14:00–15:00

Doctor ID 3, October 29, 2026:
- Availability ID 8: 10:00–11:00

Only one record existed for October 29 after the
competing overlapping requests.

### Technical Implementation

Updated availability_create.php to:
- Check existing availability for time overlaps.
- Use a MySQL transaction.
- Lock the doctor's row with SELECT ... FOR UPDATE.
- Commit successful inserts.
- Roll back rejected or failed inserts.

### Testing Limitation

The two-browser test involved closely timed submissions.
It does not constitute a controlled simultaneous-request
stress test.

### Summary

Additional tests executed: 6
Passed: 6
Failed: 0

The availability overlap protection passed all six
additional checks.

The new code still requires GitHub integration and
post-merge regression verification.


---

## US-08 Post-Merge Regression Testing

**Test ID:** US08-RT01
**Date:** October 24, 2026
**Tester:** Sewara Rashidi
**Code Tested:** Merged main branch
**User Story:** US-08 — Prevent Double Booking

### Objective

Verify that the merged implementation continues to reject
overlapping doctor availability and does not save an
invalid availability record.

### Test Procedure

1. Update the local main branch from GitHub.
2. Start Apache and MySQL using XAMPP.
3. Log in as Test Doctor (Doctor ID 3).
4. Open Manage Availability.
5. Attempt to add availability with these details:
   - Date: October 26, 2026
   - Start Time: 14:30
   - End Time: 15:30
6. Observe the application's response.
7. Verify the database records using phpMyAdmin.

### Expected Result

The system should reject the overlapping availability.

No new availability record should be inserted.

### Actual Result

The application rejected the overlapping availability.

The database query returned only these two records:

| ID | Doctor ID | Date | Start | End |
|---|---|---|---|---|
| 4 | 3 | 2026-10-26 | 14:00 | 15:00 |
| 6 | 3 | 2026-10-26 | 15:00 | 16:00 |

No record for 14:30–15:30 was created.

**Test Status: PASS**

### Remaining Limitation

A controlled simultaneous-request stress test has
not yet been performed.

The earlier two-browser-session test passed, but
it does not independently prove behavior under
precisely simultaneous execution.

### Regression Conclusion

The US-08 doctor availability overlap protection
continued to work correctly after merging into main.

**Post-Merge Regression Tests: 1 PASS, 0 FAIL**
