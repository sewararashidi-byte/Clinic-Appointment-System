# Sprint 1 Database Plan

## Project
Clinic Appointment System

## Sprint
Sprint 1 - User Accounts and Authentication

## Related User Stories

- US-01 - User Registration
- US-02 - Secure Login
- US-03 - Role-Based Access

## Database

Database name:

`clinic_appointment_system`

## Sprint 1 User Table

The `users` table stores authentication and basic account information required during Sprint 1.

### Fields

| Field | Purpose |
|---|---|
| id | Unique identifier for each user |
| full_name | Stores the user's full name |
| age | Stores the user's age |
| phone | Stores the user's phone number |
| email | Stores the user's unique email address |
| password_hash | Stores the securely hashed password |
| role | Stores the user's system role |
| account_status | Stores the account approval status |
| created_at | Records when the account was created |

## User Roles

The system supports the following roles:

- patient
- doctor
- receptionist
- administrator

The Product Owner clarified that only patients can register themselves through the public registration page.

Doctor and receptionist accounts are created by authorized staff according to their permissions.

Public registration does not allow users to create administrator accounts.

## Registration Fields

For US-01, the patient registration form collects:

- Full name
- Age
- Phone number
- Email
- Password
- Confirm password

The confirm password field is used for validation and is not stored in the database.

## Account Status

The following account statuses are currently supported:

- `pending`
- `active`
- `rejected`

A patient registering through the public registration page is stored with:

`role = patient`

and

`account_status = pending`

The patient must receive administrator approval before normal authenticated access is allowed.

## Security Decisions

- Email addresses must be unique.
- Passwords are never stored in plain text.
- PHP `password_hash()` is used before storing passwords.
- PHP `password_verify()` will be used during login implementation.
- Prepared SQL statements are used for database operations.
- Public users cannot select their own role.
- Public users cannot create administrator accounts.

## Password Requirements

Passwords must contain:

- At least 8 characters
- At least one uppercase letter
- At least one special character

## Sprint 1 Implementation Status

### Completed during Day 3

- MySQL database created
- `users` table created
- SQL schema saved in `schema.sql`
- PHP database connection created
- PHP-to-MySQL connection tested
- Patient registration backend implemented
- Duplicate email checking implemented
- Password hashing implemented
- Patient role assignment implemented
- Pending account status implemented
- Registration functionality tested with valid and invalid input

### Remaining Sprint 1 Database Work

US-02 and US-03 will use the same `users` table for:

- Secure login
- Password verification
- Account-status checking
- Role identification
- Role-based access