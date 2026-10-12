# Sprint 1 Database Plan

## Purpose

The Sprint 1 database will support:

- US-01 User Registration
- US-02 Secure Login
- US-03 Role-Based Access

## Users Table

The initial `users` table will contain:

- id
- full_name
- email
- password
- role
- created_at

## Roles

The system will support the following authentication roles:

- patient
- doctor
- admin

## Security Note

Passwords must not be stored as plain text.
PHP password hashing will be used during registration and password verification will be used during login.

Public registration should not allow users to assign themselves the administrator role.
