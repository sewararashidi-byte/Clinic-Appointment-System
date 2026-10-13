# SD-02 - User Login Sequence Diagram

## Related Requirement
FR-02 - Secure Login

## Related User Story
US-02 - Secure Login

## Actor
Registered User

## System Participants

1. Registered User
2. Login Page (`login.php`)
3. Login Processing (`login_process.php`)
4. Database Connection (`database.php`)
5. MySQL Database (`users` table)

## Main Login Flow

1. Registered user opens the login page.
2. `login.php` displays the login form.
3. User enters email and password.
4. Login form submits the credentials to `login_process.php`.
5. PHP validates that the email and password fields are provided.
6. PHP validates the email format.
7. `login_process.php` requests the database connection.
8. PHP searches the `users` table using the submitted email.
9. The database returns the matching user record.
10. PHP verifies the submitted password using `password_verify()`.
11. PHP checks the user's `account_status`.
12. If the account is `active`, PHP creates the authenticated session.
13. The user's ID, name, email, and role are stored in the session.
14. The system redirects the user according to their role.

## Alternative Flows

### Invalid Email or Password

If the email does not exist or the password is incorrect:

- Authentication is rejected.
- No authenticated session is created.
- The user is redirected to the login page.
- An `Invalid email or password` message is displayed.

### Pending Account

If the credentials are correct but `account_status = pending`:

- Authentication is blocked.
- No authenticated session is created.
- The user receives a message that the account is awaiting administrator approval.

### Rejected Account

If `account_status = rejected`:

- Authentication is blocked.
- No authenticated session is created.
- The user receives an account-not-approved message.

## Security Notes

- Login uses email and password.
- Password hashes are verified using PHP `password_verify()`.
- Plain-text passwords are never stored in the database.
- The session ID is regenerated after successful authentication.
- Only active accounts are allowed to continue.
- Role information is stored in the authenticated session for role-based authorization.

## Implementation Status

Created during Sprint 1 Day 4 to document the implemented US-02 login backend flow.