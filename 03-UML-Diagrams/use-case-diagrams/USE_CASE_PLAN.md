# Clinic Appointment System — Use Case Diagram Plan

## Purpose
The use case diagram provides an overview of interactions between the three primary user roles and the complete planned Clinic Appointment System. It models the project scope described in the product backlog (US-01–US-20; FR-01–FR-22), not only the current sprint.

## Actors and main use cases

### Patient
- Register; Log In
- Manage Patient Profile
- Search Doctors
- View Doctor Information and Availability
- View Available Appointment Slots
- Book Appointment
- Cancel Appointment; Reschedule Appointment
- View Appointment Confirmation; View Appointment History
- Receive Appointment Reminders

### Doctor
- Log In
- Manage Doctor Profile; Manage Availability
- View Scheduled Appointments; Manage Doctor Schedule

### Administrator / Receptionist
- Log In
- Manage Patient Information; Manage Doctor Information
- Manage Appointments; Manage Clinic Information

## Shared requirements and UML modeling decisions
- Log In is shared by all three actors; successful login authenticates the user and access is restricted by role (US-02 and US-03).
- Register is independent of login/authentication; users do not need to log in to register.
- Book Appointment and Reschedule Appointment include mandatory slot-availability validation to prevent double-booking (US-08).
- Dashboards are not separate use cases because they represent interface screens rather than distinct user goals.
- Appointment reminders are included because they are specified in US-13; their delivery method is not assumed.
- Clinic management is a stakeholder in the backlog, but no separate interaction is specified for a fourth actor.

## Sprint 1 scope
The following features are **planned for Sprint 1** according to the provided use case plan, subject to the approved sprint backlog:
- Register — US-01 / FR-01
- Log In — US-02 / FR-02
- Role-Based Access — US-03 / FR-03 (an authorization rule documented in the textual specification)

## Change management
This diagram describes the full planned product. It should be updated only when approved requirements, actor responsibilities, or use-case relationships change. It does not need to be redrawn after every sprint. Track implementation progress separately in the sprint backlog and review documents.

## References
- Clinic Appointment System Product Backlog, 9 October 2026, US-01–US-20 and FR-01–FR-22.
- ITC315 Chapter 6, System Modeling, slides 6, 9 and 11.
- ITC315 Chapter 5, Requirements Engineering, slide 21.
