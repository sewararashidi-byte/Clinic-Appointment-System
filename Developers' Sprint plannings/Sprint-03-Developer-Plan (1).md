# Sprint 3 — Developer Pre-Sprint Plan

**Project:** Clinic Appointment System  
**Developers:** Sewara Rashidi and Sonbol Almas  
**Sprint duration:** 3 development days  
**Focus:** Booking Reliability and Account/Appointment Management

## 1. Sprint Goal

Strengthen appointment-booking reliability and improve account and appointment management by providing double-booking protection, detailed appointment confirmation, doctor appointment management, and patient profile management.

## 2. User Stories and Acceptance Criteria

### US-08 — Prevent Double-Booking

**User Story:** As a patient, I want the system to prevent double-booking of appointment slots so that my confirmed appointment remains reserved for me.

**Acceptance Criteria:**

1.  The system marks a booked slot as unavailable.

2.  Another patient cannot book the same slot.

3.  The system displays an appropriate message when the slot is no longer available.

**Implementation Focus:**

Review and strengthen the booking validation, database constraints, and transaction logic created during Sprint 2.

**Important:** The initial booking backend already includes basic double-booking protection. Sprint 3 should verify and improve it rather than unnecessarily rewrite it.

### US-11 — Appointment Confirmation

**User Story:** As a patient, I want to receive appointment confirmation so that I know my appointment has been successfully scheduled.

**Acceptance Criteria:**

1.  The system generates confirmation after successful booking.

2.  Confirmation includes relevant appointment details.

3.  The patient can view the confirmation.

**Implementation Focus:**

Extend the existing booking success message into a detailed appointment confirmation view.

Proposed display details include doctor name, appointment date, starting time, ending time, and confirmed status. Exact confirmation content should be approved by the Product Owner.

### US-16 — Doctor Appointment Management

**User Story:** As a doctor, I want to view and manage my appointment schedule so that my appointments remain organized and up to date.

**Acceptance Criteria:**

1.  The doctor can view scheduled appointments.

2.  The doctor can view relevant appointment details.

3.  The doctor can manage their appointment schedule according to their permissions.

**Implementation Focus:**

Create a Doctor Appointments page and supporting PHP backend functions.

**Required Product Owner clarification:** The team must define what schedule-management actions doctors are permitted to perform. Viewing appointments alone must not be treated as satisfying the third acceptance criterion without agreement.

### US-04 — Patient Profile Management

**User Story:** As a patient, I want to manage my profile so that my personal information remains accurate and up to date.

**Acceptance Criteria:**

1.  The patient can view profile information.

2.  The patient can update permitted profile information.

3.  The system saves the updated information.

**Implementation Focus:**

Create a patient profile interface and PHP update functionality.

Proposed editable fields are full name, age, and phone number, using the existing users table. Husna should confirm the permitted fields; changes to email, password, and account status must not be assumed.

## 3. Division of Development Work

### Developer Responsibility Summary

**Sewara Rashidi — Backend, MySQL, UML, and Testing**

Primary responsibilities:

- PHP booking validation and conflict prevention.

- MySQL integrity and transaction review.

- Secure appointment confirmation retrieval.

- Doctor appointment repository and authorization.

- Patient profile backend validation and updates.

- Sequence diagrams and technical documentation.

- Backend, database, security, and regression testing.

- Meaningful Git commits and pull-request contributions.

**Sonbol Almas — Frontend, Integration, and Testing**

Primary responsibilities:

- Appointment confirmation interface.

- Booking-conflict feedback.

- Doctor Appointments page.

- Patient profile view and edit forms.

- Dashboard navigation.

- Frontend and backend integration.

- Interface and functional testing.

- Git commits and pull-request contributions.

**Shared Responsibilities**

- Review acceptance criteria.

- Coordinate backend/frontend interfaces.

- Run integration tests.

- Review completed work.

- Report daily progress accurately.

- Support Sprint Review preparation.




## 4. Detailed Work Plan by Day

### Day 1

**Focus:** US-08 Double-Booking Protection and US-11 Confirmation Foundation

**Sewara's tasks:**

1.  Review the existing appointment-booking backend and MySQL constraints.

2.  Check how the application behaves when two patients request the same slot.

3.  Review overlap detection and transaction behavior.

4.  Implement necessary US-08 corrections based on actual findings.

5.  Begin backend retrieval of appointment confirmation details.

6.  Identify any required changes to the existing SD06 sequence diagram.

**Sonbol's tasks:**

1.  Review the confirmation-related acceptance criteria.

2.  Design the detailed appointment confirmation interface.

3.  Prepare the confirmation page or section using the existing Bootstrap design.

4.  Connect the interface to the agreed confirmation data supplied by the backend.

5.  Review the booking error message shown when a slot is already taken.

**Together:**

- Test booking requests from separate patient accounts.

- Verify that a confirmed slot is not booked twice.

- Verify appropriate success and error messages.

- Record actual test results.

- Commit and merge completed, reviewed work.

**Proposed Day 1 Increment:**

Double-booking protection verified or strengthened, with the appointment confirmation implementation underway.

### Day 2

**Focus:** US-11 Completion, US-04 Patient Profile Management, and US-16 Foundation

**Sewara's tasks:**

1.  Complete secure appointment confirmation retrieval.

2.  Implement patient profile retrieval from the existing users table.

3.  Implement validation and updates for approved profile fields.

4.  Ensure patients cannot modify another patient's account.

5.  Begin doctor-specific appointment queries after the Product Owner confirms US-16 permissions.

**Sonbol's tasks:**

1.  Finalize the appointment confirmation interface.

2.  Create the patient profile viewing and editing interface.

3.  Connect profile forms to Sewara's backend.

4.  Prepare the Doctor Appointments page and navigation.

5.  Test that the confirmation and profile interfaces display correct information.

**Together:**

- Test confirmation after booking.

- Test profile viewing and permitted updates.

- Verify database persistence and access control.

- Integrate the initial doctor appointment schedule display.

- Update development documentation and GitHub.

**Proposed Day 2 Increment:**

Detailed confirmation and patient profile features functioning, with the doctor appointment-management interface and backend foundation in place.

### Day 3

**Focus:** US-16 Completion, Testing, UML, and Integration

**Sewara's tasks:**

1.  Complete US-16 backend functionality within the permissions approved by Husna.

2.  Verify that doctors can access only their own appointments.

3.  Implement safeguards for any approved schedule-management action.

4.  Complete required UML updates.

5.  Review database integrity, authorization, and booking-conflict handling.

6.  Prepare and update formal testing documentation.

**Sonbol's tasks:**

1.  Complete the Doctor Appointments page.

2.  Integrate any approved schedule-management controls.

3.  Verify confirmation and patient profile navigation.

4.  Check frontend feedback messages and basic responsive layout.

5.  Run frontend regression tests.

**Together:**

- Test the complete approved Sprint 3 functionality.

- Retest important Sprint 1 and Sprint 2 workflows.

- Fix defects found during testing.

- Review and merge GitHub pull requests.

- Prepare the Sprint 3 Review demonstration.

- Record actual task progress and remaining work.

**Proposed Day 3 Increment:**

A tested Sprint 3 increment containing the user stories that satisfy all agreed acceptance criteria and the team's Definition of Done.




## 5. Planned Testing and Integration

**US-08 — Double-Booking**

Planned test scenarios:

1.  Successfully book an available slot.

2.  Attempt to book the same slot from another patient account.

3.  Verify that the second booking is rejected.

4.  Verify that only one confirmed appointment occupies the slot.

5.  Attempt overlapping requests and inspect the result.

6.  Test competing requests using separate authenticated sessions, where feasible.

**US-11 — Appointment Confirmation**

Planned test scenarios:

1.  Book an appointment successfully.

2.  Verify that detailed confirmation is displayed.

3.  Confirm the doctor, date, time, and status match MySQL.

4.  Verify that confirmation cannot be viewed by an unauthorized user.

5.  Verify appropriate behavior for an invalid appointment reference.

**US-16 — Doctor Appointment Management**

Planned test scenarios:

1.  View the doctor's scheduled appointments.

2.  Verify accurate appointment details.

3.  Verify that a doctor cannot access another doctor's appointments.

4.  Test approved schedule-management actions.

5.  Verify that any permitted changes preserve appointment integrity.

**US-04 — Patient Profile Management**

Planned test scenarios:

1.  View the logged-in patient's profile.

2.  Update an approved profile field.

3.  Verify that the change is saved in MySQL.

4.  Refresh and confirm persistence.

5.  Reject invalid input.

6.  Block attempts to modify another user's profile.

**Regression Testing**

Retest relevant existing functionality, including:

- Registration and login.

- Role-based access restrictions.

- Doctor search.

- Doctor details.

- Doctor availability.

- Appointment date selection.

- Appointment booking.








