# US-08 — Prevent Double-Booking: Complete Test Results

**Project:** Clinic Appointment System  
**Sprint:** Sprint 3  
**Testing Dates:** October 22–24, 2026  
**Tester:** Sewara Rashidi  
**User Story:** US-08 — Prevent Double-Booking  
**Environment:** PHP, MySQL, XAMPP, phpMyAdmin, web browser, PHP CLI

---

## 1. Testing Objective

The objective of US-08 testing was to verify that the Clinic Appointment System prevents scheduling conflicts during appointment booking and doctor availability management.

Testing focused on three main areas:

1. Preventing different patients from booking the same appointment slot.
2. Preventing doctors from creating overlapping availability periods.
3. Preventing doctors from updating availability periods in ways that create overlaps.

The tests also verified database integrity, valid scheduling operations, patient appointment visibility, and system behavior after merging changes into the main GitHub branch.

## 2. Product Owner Clarification

During Sprint 3, the Product Owner, Husna Zala Khan, clarified that:

- Doctors must not create overlapping availability periods on the same date.
- The system must prevent overlapping confirmed appointments.
- Scheduling conflicts should be prevented.

These requirements guided the availability creation and update improvements implemented during Sprint 3.

## 3. Test Environment

| Component | Technology |
|---|---|
| Backend | PHP |
| Database | MySQL |
| Local server | XAMPP |
| Database inspection | phpMyAdmin |
| User interface testing | Web browser |
| Backend function testing | PHP CLI |
| Version control | Git and GitHub |

Testing was performed using the local Clinic Appointment System development environment.

---

## 4. Appointment Double-Booking Tests

**Testing Dates:** October 22–23, 2026  
**Tester:** Sewara Rashidi  
**Test IDs:** US08-T01 to US08-T05

### 4.1 Objective

Verify that two patients cannot successfully book the same appointment slot and that candidate slots overlapping confirmed appointments are excluded.

### 4.2 Test Data

| Field | Value |
|---|---|
| Doctor ID | 3 |
| Appointment Date | October 23, 2026 |
| Appointment Time | 10:00–10:30 AM |
| Confirmed Appointment ID | 5 |
| Appointment Status | confirmed |

### 4.3 Test Cases and Results

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US08-T01 | Patient 1 books an available appointment slot | Booking succeeded and a confirmed appointment was created | PASS |
| US08-T02 | Patient 2 attempts to book the same slot using a previously loaded page | The system rejected the request and displayed an already-booked message | PASS |
| US08-T03 | Inspect appointment records in MySQL | Only one appointment existed for the tested doctor, date, and start time | PASS |
| US08-T04 | Refresh Patient 2's Doctor Details page | The booked 10:00–10:30 AM slot disappeared, while 10:30–11:00 AM remained available | PASS |
| US08-T05 | Test a hypothetical appointment slot overlapping confirmed bookings | One candidate slot was generated, but zero available slots were returned | PASS |

### 4.4 Overlapping Appointment Slot Test

**Test ID:** US08-T05  
**Date:** October 23, 2026

A temporary PHP CLI test was performed using:

- Doctor ID: 3
- Appointment date: October 26, 2026
- Candidate start time: 2:15 PM
- Candidate end time: 2:45 PM
- Appointment duration: 30 minutes

The test used a hypothetical availability record created in PHP memory without inserting it into MySQL.

The `generateAppointmentSlots()` function generated a candidate appointment slot. The `getAvailableAppointmentSlots()` function then checked the candidate against confirmed appointments.

**Actual terminal output:**

```text
Candidate slots: 1
Available slots: 0
PASS: Overlapping slot correctly excluded.
```

**Result: PASS**

No appointment or availability records were modified. The temporary file `us08_overlap_temp_check.php` was deleted after testing.

### 4.5 Database Constraint Verification

The existing unique index `uq_doctor_appointment_slot` was verified.

It applies to:

- `doctor_id`
- `appointment_date`
- `start_time`

The unique index prevents multiple appointment records from using the same combination of doctor, date, and starting time.

Overlapping appointments with different starting times are also checked by application-level booking validation.

### 4.6 Initial Availability Inspection

**Date:** October 22, 2026

A read-only SQL query checked whether overlapping availability periods already existed in the local database.

**Actual Result:** Zero overlapping records were found.

This inspection established the initial database condition but did not prove that the application prevented doctors from creating overlaps.

That prevention was implemented and tested later in Sprint 3.

### 4.7 Appointment Booking Test Summary

**Tests Executed:** 5  
**Passed:** 5  
**Failed:** 0

---

## 5. Doctor Availability Creation Tests

**Date:** October 24, 2026  
**Tester:** Sewara Rashidi  
**Feature Branch:** `feature/s3-availability-overlap-protection`  
**Test IDs:** US08-AV01 to US08-AV06

### 5.1 Objective

Verify that the system rejects overlapping or duplicate availability while allowing valid adjacent periods and availability on different dates.

### 5.2 Implementation

**Updated file:**

`10-Source-Code/actions/availability_create.php`

The implementation:

- Checks whether new availability overlaps an existing period belonging to the same doctor on the same date.
- Begins a MySQL transaction.
- Locks the doctor's database row using `SELECT ... FOR UPDATE`.
- Commits successful inserts.
- Rolls back rejected overlaps or unsuccessful inserts.
- Displays an error when overlapping availability is detected.

### 5.3 Test Cases and Results

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US08-AV01 | Create 14:30–15:30 availability when 14:00–15:00 already exists | Request rejected; database retained the original availability | PASS |
| US08-AV02 | Create an exact duplicate of the 14:00–15:00 period | Duplicate rejected | PASS |
| US08-AV03 | Create adjacent availability from 15:00–16:00 | Request accepted; both periods were verified in MySQL | PASS |
| US08-AV04 | Create 14:00–15:00 availability on October 27 instead of October 26 | Request accepted and record verified in MySQL | PASS |
| US08-AV05 | Submit overlapping availability through two separate authenticated browser sessions | First request accepted; second rejected; database contained only one record | PASS |
| US08-AV06 | Verify the database storage engines | `users` and `doctor_availability` were both confirmed to use InnoDB | PASS |

### 5.4 Database Evidence

The following records were verified during availability creation testing.

**Doctor ID 3 — October 26, 2026**

| Availability ID | Time |
|---|---|
| 4 | 14:00–15:00 |
| 6 | 15:00–16:00 |

The periods were adjacent and did not overlap.

**Doctor ID 3 — October 27, 2026**

| Availability ID | Time |
|---|---|
| 7 | 14:00–15:00 |

The period was accepted because the date differed from October 26.

**Doctor ID 3 — October 29, 2026**

| Availability ID | Time |
|---|---|
| 8 | 10:00–11:00 |

During the two-browser-session test, the competing 10:30–11:30 request was rejected.

The database contained only one availability record for that date after the test.

### 5.5 Transaction Support

The `users` and `doctor_availability` tables were confirmed to use the InnoDB storage engine.

InnoDB supports transactions and row-level locking, which are required by the implemented doctor-row locking approach.

### 5.6 Testing Limitation

The two-browser-session test involved closely timed requests but was not a controlled simultaneous-request stress test.

Its successful result demonstrates correct behavior in the tested scenario, not conclusive proof of safety under precisely simultaneous execution.

### 5.7 Availability Creation Test Summary

**Tests Executed:** 6  
**Passed:** 6  
**Failed:** 0

---

## 6. Availability Creation Post-Merge Regression Test

**Test ID:** US08-RT01  
**Date:** October 24, 2026  
**Tester:** Sewara Rashidi  
**Code Tested:** Merged `main` branch

### 6.1 Objective

Verify that the merged availability creation code still prevents overlaps without inserting invalid records.

### 6.2 Test Procedure

1. Update the local `main` branch from GitHub.
2. Start Apache and MySQL using XAMPP.
3. Log in as Test Doctor (Doctor ID 3).
4. Open Manage Availability.
5. Attempt to create availability on October 26, 2026, from 14:30–15:30.
6. Observe the error response.
7. Inspect the database using phpMyAdmin.

### 6.3 Expected Result

The system must reject the overlapping period and must not create another availability record.

### 6.4 Actual Result

The application rejected the request.

MySQL returned only the two existing availability records:

| ID | Doctor ID | Date | Start | End |
|---|---|---|---|---|
| 4 | 3 | 2026-10-26 | 14:00 | 15:00 |
| 6 | 3 | 2026-10-26 | 15:00 | 16:00 |

No 14:30–15:30 record was inserted.

**Result: PASS**

---

## 7. Doctor Availability Update Tests

**Date:** October 24, 2026  
**Tester:** Sewara Rashidi  
**Feature Branch:** `feature/s3-us08-availability-update-fix`  
**Test IDs:** US08-UP01 to US08-UP04

### 7.1 Objective

Verify that doctors cannot update an availability record to overlap another period on the same date.

The tests also verify that valid updates remain possible and that a record does not incorrectly conflict with itself.

### 7.2 Implementation

**Updated file:**

`10-Source-Code/actions/availability_update.php`

The implementation:

- Checks existing availability for overlapping periods before updating.
- Restricts overlap checking to the same doctor and date.
- Excludes the availability record currently being edited using `id <> ?`.
- Reuses the existing transaction and doctor-row locking.
- Preserves existing ownership and confirmed-appointment checks.
- Rolls back the transaction when an overlap is detected.

### 7.3 Test Preparation

Before updating availability ID 6, a read-only MySQL query checked for associated confirmed appointments.

The query returned no rows, confirming that no confirmed appointments were associated with availability ID 6 in that test.

### 7.4 Test Cases and Results

| Test ID | Test Case | Actual Result | Status |
|---|---|---|---|
| US08-UP01 | Attempt to update availability #6 from 15:00–16:00 to 14:30–15:30 while #4 covers 14:00–15:00 | Overlapping update rejected | PASS |
| US08-UP02 | Verify MySQL records after the rejected update | Availability #4 and #6 remained unchanged | PASS |
| US08-UP03 | Update availability #6 to the non-overlapping period 15:30–16:30 | Update accepted and database result confirmed | PASS |
| US08-UP04 | Save availability #6 again without changing its date or time | Update accepted without a false overlap error | PASS |

### 7.5 Database Verification

**After the rejected update:**

| Availability ID | Date | Time |
|---|---|---|
| 4 | October 26, 2026 | 14:00–15:00 |
| 6 | October 26, 2026 | 15:00–16:00 |

**After the successful update:**

| Availability ID | Date | Time |
|---|---|---|
| 4 | October 26, 2026 | 14:00–15:00 |
| 6 | October 26, 2026 | 15:30–16:30 |

This demonstrates that the overlap check rejected conflicting changes while allowing a valid update.

### 7.6 PHP Syntax Verification

The updated `availability_update.php` file passed PHP syntax validation.

### 7.7 Availability Update Test Summary

**Tests Executed:** 4  
**Passed:** 4  
**Failed:** 0

---

## 8. Availability Update Post-Merge Regression Test

**Test ID:** US08-RT02  
**Date:** October 24, 2026  
**Tester:** Sewara Rashidi  
**Code Tested:** Merged `main` branch

### 8.1 Objective

Verify that the availability update protection continues to reject overlapping modifications after merging the correction into `main`.

### 8.2 Test Procedure

1. Pull the latest `main` branch.
2. Start the Clinic Appointment System locally.
3. Log in as Test Doctor (ID 3).
4. Open Manage Availability.
5. Attempt to update availability #6 to October 26, 2026, from 14:30–15:30.
6. Check the application's response.
7. Verify availability #4 and #6 in MySQL.

### 8.3 Expected Result

The system must reject the overlap and preserve both original records.

### 8.4 Actual Result

The application rejected the overlapping update.

The database values remained:

| Availability ID | Date | Start | End |
|---|---|---|---|
| 4 | 2026-10-26 | 14:00 | 15:00 |
| 6 | 2026-10-26 | 15:30 | 16:30 |

**Result: PASS**

---

## 9. Combined Testing Summary

| Testing Category | Test IDs | Executed | Passed | Failed |
|---|---|---:|---:|---:|
| Appointment double-booking | US08-T01–T05 | 5 | 5 | 0 |
| Availability creation and database checks | US08-AV01–AV06 | 6 | 6 | 0 |
| Availability update | US08-UP01–UP04 | 4 | 4 | 0 |
| Post-merge regression | US08-RT01–RT02 | 2 | 2 | 0 |
| **Total** | | **17** | **17** | **0** |

**Total Tests Executed:** 17  
**Passed:** 17  
**Failed:** 0

All seventeen documented tests passed in their respective test environments.

## 10. Remaining Limitations and Activities

The following items have not yet been completed or confirmed:

1. **Controlled simultaneous-request testing:** Precisely concurrent patient booking and availability requests have not been independently stress-tested.
2. **End-to-end overlapping-availability scenario:** This was originally identified as a possible test. The Product Owner subsequently required overlapping availability to be prohibited, so the normal application should reject such periods rather than permit their creation.
3. **SD04 UML update:** The Doctor Availability Management Sequence Diagram must be updated to represent the implemented overlap validation, transactions, and rollback behavior.
4. **Final acceptance:** Product Owner acceptance and final Definition of Done confirmation have not been recorded.

The original US-08 test results and both availability improvements have been preserved in this report.

## 11. Conclusion

During Sprint 3, US-08 functionality was tested across appointment booking, availability creation, availability updates, database integrity, and post-merge regression scenarios.

The appointment-booking tests confirmed that the tested duplicate booking was rejected and that a hypothetical overlapping appointment slot was excluded from available booking times.

The availability creation tests confirmed that the system rejected duplicate and overlapping availability periods while allowing valid adjacent periods and different-date entries.

The availability update tests confirmed that doctors could not move existing availability into conflicting periods, while legitimate updates remained possible.

Both availability improvements were merged into the main GitHub branch and passed their recorded post-merge regression tests.

**Overall Testing Result: 17 PASS, 0 FAIL.**

**Current Status:** Implementation merged and documented tests passed. SD04 modeling, final acceptance, and any required additional concurrency verification remain pending.
