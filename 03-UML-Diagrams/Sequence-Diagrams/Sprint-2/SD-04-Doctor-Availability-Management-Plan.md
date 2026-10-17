# SD-04 — Doctor Availability Management Sequence Diagram Plan

## Related User Story
**US-15 — Doctor Availability Management**

As a doctor, I want to manage my availability so that patients can book appointments during my available times.

## Purpose
This sequence diagram models how an authenticated doctor manages availability in the Clinic Appointment System.

The diagram covers the main operations implemented during Sprint 2 Day 1:

- Create availability
- Update availability
- Delete availability
- Doctor-role authorization
- Validation and error responses

## Main Participants

- Doctor
- Doctor Dashboard
- Manage Availability Page
- auth.php
- Availability Action
- MySQL Database

## Main Flow

### 1. Open Availability Management
1. Doctor opens the Doctor Dashboard.
2. Doctor selects Manage Availability.
3. The system opens the availability management page.
4. `auth.php` verifies that the current user has the doctor role.
5. If authorized, the system retrieves the doctor's existing availability records.
6. The page displays the availability management interface.

### 2. Create Availability
1. Doctor enters availability date, start time, and end time.
2. Doctor submits the form.
3. The system verifies authorization.
4. The system validates the submitted values.
5. If valid, the availability record is inserted into MySQL.
6. The system redirects with a success message.
7. If the input is invalid, the system returns an error message.

### 3. Update Availability
1. Doctor modifies an existing availability record.
2. Doctor submits the update.
3. The system verifies the doctor role.
4. The system verifies that the availability record belongs to the logged-in doctor.
5. The system validates the updated values.
6. If valid, the availability record is updated in MySQL.
7. The system returns a success message.
8. Invalid or unauthorized updates return an error message.

### 4. Delete Availability
1. Doctor selects Delete for an availability record.
2. The interface requests confirmation.
3. Doctor confirms deletion.
4. The system verifies the doctor role.
5. The system verifies that the record belongs to the logged-in doctor.
6. The record is deleted from MySQL.
7. The system returns a success message.
8. Invalid or unauthorized deletion attempts return an error message.

## Alternative / Error Flows

- Non-doctor users are denied access to doctor availability management.
- Missing or invalid availability values are rejected.
- Start time must be earlier than end time.
- A doctor cannot update or delete another doctor's availability record.
- Database operation failures return an appropriate error response.

## Implementation Files Represented

- `dashboards/doctor-dashboard.php`
- `doctor/availability.php`
- `config/auth.php`
- `actions/availability_create.php`
- `actions/availability_update.php`
- `actions/availability_delete.php`
- `database/schema.sql`

## Database Tables Used

- `users`
- `doctor_availability`

## Expected Outcome
The sequence diagram should clearly show how the doctor interacts with the availability interface and how the PHP backend communicates with MySQL during create, update, and delete operations.

## Sprint
Sprint 2 — Day 1