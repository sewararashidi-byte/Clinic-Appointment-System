# SD-01 - User Registration Sequence Diagram

## Related Requirement
FR-01 - User Registration

## Related User Story
US-01 - User Registration

## Actor
Patient

## System Participants

1. Patient
2. Registration Page (`register.php`)
3. Registration Processing (`register_process.php`)
4. Database Connection (`database.php`)
5. MySQL Database (`users` table)

## Main Registration Flow

1. Patient opens the registration page.
2. `register.php` displays the patient registration form.
3. Patient enters:
   - Full name
   - Age
   - Phone number
   - Email
   - Password
   - Confirm password
4. JavaScript performs client-side validation.
5. The form submits registration data to `register_process.php`.
6. PHP performs server-side validation.
7. PHP checks whether the email already exists in the `users` table.
8. If the email is unique and the data is valid:
   - PHP hashes the password using `password_hash()`.
   - Role is assigned as `patient`.
   - Account status is assigned as `pending`.
9. PHP inserts the new patient record into the `users` table.
10. The database confirms successful insertion.
11. The system redirects the patient back to the registration page.
12. The patient sees a success message indicating that registration was submitted and is awaiting administrator approval.

## Alternative Flows

### Invalid Input

If required information is missing or invalid:

- Registration is rejected.
- Validation errors are returned.
- No database record is created.

### Duplicate Email

If the email already exists:

- The database check finds the existing account.
- Registration is rejected.
- The patient receives a duplicate email error message.
- No additional account is created.

### Invalid Password

If the password does not meet the required policy:

- Registration is rejected.
- The user receives password validation messages.
- No account is inserted into the database.

## Security Notes

- Public registration creates patient accounts only.
- Passwords are stored as hashes and never as plain text.
- New patient registrations are stored with `pending` account status.
- Administrator approval is required before the patient receives normal authenticated access.

## Implementation Status

Updated during Sprint 1 Day 3 after implementation and testing of US-01.

The sequence model now reflects the implemented PHP, MySQL, JavaScript validation, password hashing, duplicate-email checking, patient role assignment, and pending-approval workflow.