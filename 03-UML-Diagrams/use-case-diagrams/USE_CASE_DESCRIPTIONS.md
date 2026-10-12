# Clinic Appointment System — Use Case Descriptions

## Scope and notation
These descriptions cover the main interactions shown in the complete-system use case diagram and map them to the 20 user stories and 22 functional requirements in the product backlog. Some user stories map to more than one use case. Role-Based Access is a cross-cutting authorization rule; Validate Slot Availability is included behavior.

The six table fields follow ITC315 Chapter 6, slide 11: Actors, Description, Data, Stimulus, Response, Comments. These are planned requirements, not claims of implemented functionality.

## UC-01: Register
**Backlog mapping:** US-01 / FR-01

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient creates a new account to access the clinic system. |
| Data | Required registration information. |
| Stimulus | Patient selects Register and submits information. |
| Response | System validates the information and creates an account when valid. |
| Comments | Invalid registration information must not create an account. |

## UC-02: Log In
**Backlog mapping:** US-02 / FR-02

| Field | Description |
|---|---|
| Actors | Patient; Doctor; Receptionist / Administrator |
| Description | Registered user securely accesses the system. |
| Data | Login credentials and account record. |
| Stimulus | User submits login credentials. |
| Response | System validates credentials and grants access only when valid. |
| Comments | The user’s role determines subsequent access; see US-03. |

## UC-03: Role-Based Access (authorization rule)
**Backlog mapping:** US-03 / FR-03

| Field | Description |
|---|---|
| Actors | Patient; Doctor; Receptionist / Administrator |
| Description | System limits each authenticated user to permitted functions and information. |
| Data | User role and access permissions. |
| Stimulus | User logs in or requests a protected function. |
| Response | System identifies role and permits or denies the requested access. |
| Comments | Documented as a cross-cutting rule, not necessarily a standalone use-case oval. |

## UC-04: Manage Patient Profile
**Backlog mapping:** US-04 / FR-04

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient views and updates permitted profile details. |
| Data | Patient profile information. |
| Stimulus | Patient opens profile and submits changes. |
| Response | System saves permitted changes. |
| Comments | Only permitted profile fields may be changed. |

## UC-05: Search Doctors
**Backlog mapping:** US-05 / FR-05

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient finds doctors using search criteria. |
| Data | Doctor search criteria and matching records. |
| Stimulus | Patient submits search criteria. |
| Response | System displays matching doctors for selection. |
| Comments | Patient can select a doctor to view information. |

## UC-06: View Doctor Information and Availability
**Backlog mapping:** US-06 / FR-06

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient reviews doctor professional information and current availability. |
| Data | Doctor profile and available dates/times. |
| Stimulus | Patient selects a doctor. |
| Response | System displays the doctor information and current availability. |
| Comments | Availability must reflect current doctor availability. |

## UC-07: View Available Appointment Slots
**Backlog mapping:** US-07 / FR-07

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient examines open dates and time slots before booking. |
| Data | Doctor availability and open slots. |
| Stimulus | Patient requests available slots. |
| Response | System displays slots available for selection. |
| Comments | This supports booking described in US-07. |

## UC-08: Book Appointment
**Backlog mapping:** US-07 / FR-08

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient selects an available slot to schedule a consultation. |
| Data | Patient, doctor, date, time and selected slot. |
| Stimulus | Patient submits a booking request. |
| Response | System books a valid available slot and provides confirmation. |
| Comments | Must validate slot availability to prevent double-booking (US-08 / FR-09). |

## UC-09: Validate Slot Availability (included behavior)
**Backlog mapping:** US-08 / FR-09

| Field | Description |
|---|---|
| Actors | Patient (through booking or rescheduling) |
| Description | System ensures the selected appointment slot has not already been reserved. |
| Data | Requested slot and existing bookings. |
| Stimulus | A booking or rescheduling attempt requires slot validation. |
| Response | System accepts a free slot or rejects an unavailable one with a message. |
| Comments | This is required behavior within booking/rescheduling, not a separate patient goal. |

## UC-10: Cancel Appointment
**Backlog mapping:** US-09 / FR-10

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient cancels an eligible existing appointment. |
| Data | Existing appointment and cancellation selection. |
| Stimulus | Patient selects an eligible appointment to cancel. |
| Response | System updates the appointment status. |
| Comments | Clinic cancellation rules apply. |

## UC-11: Reschedule Appointment
**Backlog mapping:** US-10 / FR-11

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient changes an existing appointment to an available alternative slot. |
| Data | Existing appointment and alternative slot. |
| Stimulus | Patient requests rescheduling and chooses another slot. |
| Response | System validates availability and updates the appointment. |
| Comments | Slot validation prevents double-booking (US-08). |

## UC-12: View Appointment Confirmation
**Backlog mapping:** US-11 / FR-12

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient views confirmation after successful appointment booking. |
| Data | Confirmed appointment details. |
| Stimulus | Successful booking; patient views confirmation. |
| Response | System generates and displays the appointment confirmation. |
| Comments | Confirmation includes relevant appointment details. |

## UC-13: View Appointment History
**Backlog mapping:** US-12 / FR-13

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient reviews upcoming and previous appointments. |
| Data | Patient appointment records and details. |
| Stimulus | Patient opens appointment history. |
| Response | System displays accurate upcoming and previous appointment details. |
| Comments | Access is limited to the patient’s permitted records. |

## UC-14: Receive Appointment Reminders
**Backlog mapping:** US-13 / FR-14

| Field | Description |
|---|---|
| Actors | Patient |
| Description | Patient receives reminders for upcoming appointments. |
| Data | Upcoming appointment details and reminder schedule. |
| Stimulus | An appointment reaches the configured reminder time. |
| Response | System sends a reminder with relevant appointment details. |
| Comments | Delivery channel is not specified in the backlog; this is time-triggered system behavior. |

## UC-15: Manage Doctor Profile
**Backlog mapping:** US-14 / FR-15

| Field | Description |
|---|---|
| Actors | Doctor |
| Description | Doctor reviews and updates permitted professional profile information. |
| Data | Doctor professional profile. |
| Stimulus | Doctor submits a permitted profile update. |
| Response | System saves the updated professional information. |
| Comments | Updated details should be accurate for patients. |

## UC-16: Manage Availability
**Backlog mapping:** US-15 / FR-16

| Field | Description |
|---|---|
| Actors | Doctor |
| Description | Doctor sets and updates available appointment dates and times. |
| Data | Doctor availability dates and time slots. |
| Stimulus | Doctor creates or changes availability. |
| Response | System saves changes and reflects them in patient-facing availability. |
| Comments | Availability changes must be reflected in displayed appointment slots. |

## UC-17: View Scheduled Appointments
**Backlog mapping:** US-16 / FR-17

| Field | Description |
|---|---|
| Actors | Doctor |
| Description | Doctor views scheduled appointments and relevant details. |
| Data | Doctor appointment schedule and details. |
| Stimulus | Doctor opens scheduled appointments. |
| Response | System displays scheduled appointments and their details. |
| Comments | Doctor sees appointments within their permissions. |

## UC-18: Manage Doctor Schedule
**Backlog mapping:** US-16 / FR-18

| Field | Description |
|---|---|
| Actors | Doctor |
| Description | Doctor manages appointment schedule within authorized permissions. |
| Data | Scheduled appointments and permitted changes. |
| Stimulus | Doctor requests a schedule change. |
| Response | System updates the schedule as permitted. |
| Comments | Scope of allowed changes depends on role permissions. |

## UC-19: Manage Patient Information
**Backlog mapping:** US-17 / FR-19

| Field | Description |
|---|---|
| Actors | Receptionist / Administrator |
| Description | Authorized staff view and update patient information. |
| Data | Patient information and permitted updates. |
| Stimulus | Staff requests to view or update patient records. |
| Response | System shows or saves permitted patient information. |
| Comments | Only authorized staff can make permitted changes. |

## UC-20: Manage Doctor Information
**Backlog mapping:** US-18 / FR-20

| Field | Description |
|---|---|
| Actors | Receptionist / Administrator |
| Description | Authorized staff view and update doctor records. |
| Data | Doctor information and permitted updates. |
| Stimulus | Staff requests to view or update doctor records. |
| Response | System shows or saves permitted doctor information. |
| Comments | Only authorized staff can make permitted changes. |

## UC-21: Manage Appointments
**Backlog mapping:** US-19 / FR-21

| Field | Description |
|---|---|
| Actors | Receptionist / Administrator |
| Description | Authorized staff manage clinic appointment information. |
| Data | Appointment records and permitted changes. |
| Stimulus | Staff opens or updates an appointment record. |
| Response | System displays or updates appointment information successfully. |
| Comments | Actions must follow role permissions; avoid appointment conflicts. |

## UC-22: Manage Clinic Information
**Backlog mapping:** US-20 / FR-22

| Field | Description |
|---|---|
| Actors | Receptionist / Administrator |
| Description | Authorized staff maintain relevant clinic information. |
| Data | Clinic information and permitted updates. |
| Stimulus | Staff requests to view or update clinic details. |
| Response | System shows or saves permitted clinic information. |
| Comments | Only authorized staff can update clinic details. |

## Modeling and maintenance notes
- All three actors share Log In. Register is independent of Log In.
- The use case diagram models the complete planned system, not only Sprint 1.
- US-03 is represented as an authorization rule in the descriptions; US-08 is mandatory included behavior for booking/rescheduling.
- Revisit the diagram and tables only when approved requirements, roles, or interactions change. Review accuracy at sprint reviews.
- This document supplements the mandatory diagram; the assignment does not explicitly demand one table per user story.

## Sources
- Clinic Appointment System Product Backlog (9 October 2026), FR-01–FR-22 and US-01–US-20.
- ITC315 Chapter 6 System Modeling, slides 9 and 11.