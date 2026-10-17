<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
 * Redirect an authenticated user
 * to the correct dashboard for their role.
 */
function redirectToRoleDashboard(): void
{
    $role = $_SESSION['role'] ?? '';

    switch ($role) {
        case 'patient':
    header('Location: ../dashboards/patient-dashboard.php');
    exit;

case 'doctor':
    header('Location: ../dashboards/doctor-dashboard.php');
    exit;

case 'receptionist':
case 'administrator':
    header('Location: ../dashboards/admin-dashboard.php');
    exit;

        default:
            session_unset();
            session_destroy();

            header('Location: ../login.php');
            exit;
    }
}

/*
 * Require a logged-in user.
 */
function requireLogin(): void
{
    if (
        empty($_SESSION['user_id']) ||
        empty($_SESSION['role'])
    ) {
        $_SESSION['login_error'] =
            'Please log in to access this page.';

        header('Location: ../login.php');
        exit;
    }
}

/*
 * Require one of the allowed roles.
 */
function requireRole(array $allowedRoles): void
{
    requireLogin();

    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        $_SESSION['access_error'] =
            'You are not authorized to access that page.';

        redirectToRoleDashboard();
    }
}
?>