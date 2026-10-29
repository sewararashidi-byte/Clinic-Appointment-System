<?php

/**
 * Sprint 4 - US-14: Doctor Profile Management
 * Frontend Developer: Sonbol Almas
 *
 * Displays and updates the authenticated doctor's
 * professional profile.
 */

// Load authenticated doctor's professional profile.
require_once __DIR__ .
    '/../includes/doctor_profile_data.php';

/**
 * Escape output to prevent HTML injection.
 */
function escapeDoctorProfileValue($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Retrieve one-time success and error messages.
$profileSuccess =
    $_SESSION['doctor_profile_success'] ?? '';

$profileActionError =
    $_SESSION['doctor_profile_error'] ?? '';

unset(
    $_SESSION['doctor_profile_success'],
    $_SESSION['doctor_profile_error']
);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        Doctor Profile - Clinic Appointment System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container mt-5 mb-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <h2 class="text-center mb-3">
                My Professional Profile
            </h2>

            <p class="text-center text-muted mb-4">
                View and update your professional information.
            </p>

            <!-- Success Message -->

            <?php if (
                is_string($profileSuccess) &&
                $profileSuccess !== ''
            ): ?>

                <div class="alert alert-success" role="alert">

                    <?php
                    echo escapeDoctorProfileValue(
                        $profileSuccess
                    );
                    ?>

                </div>

            <?php endif; ?>

            <!-- Action Error Message -->

            <?php if (
                is_string($profileActionError) &&
                $profileActionError !== ''
            ): ?>

                <div class="alert alert-danger" role="alert">

                    <?php
                    echo escapeDoctorProfileValue(
                        $profileActionError
                    );
                    ?>

                </div>

            <?php endif; ?>

            <!-- Profile Loading Error -->

            <?php if ($profileError !== ''): ?>

                <div class="alert alert-warning" role="alert">

                    <?php
                    echo escapeDoctorProfileValue(
                        $profileError
                    );
                    ?>

                </div>

            <?php elseif ($doctorProfile !== null): ?>

                <div class="card shadow-sm">

                    <div class="card-header bg-primary text-white">

                        <h4 class="mb-0">
                            Edit Professional Profile
                        </h4>

                    </div>

                    <div class="card-body">

                        <form
                            action="../actions/doctor_profile_update.php"
                            method="POST"
                        >

                            <!-- CSRF Protection -->

                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php
                                    echo escapeDoctorProfileValue(
                                        $_SESSION['doctor_profile_csrf_token'] ?? ''
                                    );
                                ?>"
                            >

                            <!-- Doctor Full Name -->

                            <div class="mb-3">

                                <label
                                    for="full_name"
                                    class="form-label"
                                >
                                    Full Name
                                </label>

                                <input
                                    type="text"
                                    id="full_name"
                                    name="full_name"
                                    class="form-control"
                                    maxlength="100"
                                    required
                                    value="<?php
                                        echo escapeDoctorProfileValue(
                                            $doctorProfile['full_name']
                                        );
                                    ?>"
                                >

                            </div>

                            <!-- Professional Phone -->

                            <div class="mb-3">

                                <label
                                    for="phone"
                                    class="form-label"
                                >
                                    Professional Phone Number
                                </label>

                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    class="form-control"
                                    maxlength="20"
                                    required
                                    value="<?php
                                        echo escapeDoctorProfileValue(
                                            $doctorProfile['phone']
                                        );
                                    ?>"
                                >

                            </div>

                            <!-- Specialty -->

                            <div class="mb-3">

                                <label
                                    for="specialty"
                                    class="form-label"
                                >
                                    Specialty
                                </label>

                                <input
                                    type="text"
                                    id="specialty"
                                    name="specialty"
                                    class="form-control"
                                    maxlength="100"
                                    required
                                    value="<?php
                                        echo escapeDoctorProfileValue(
                                            $doctorProfile['specialty']
                                        );
                                    ?>"
                                >

                            </div>

                            <!-- Qualifications -->

                            <div class="mb-3">

                                <label
                                    for="qualifications"
                                    class="form-label"
                                >
                                    Qualifications
                                </label>

                                <input
                                    type="text"
                                    id="qualifications"
                                    name="qualifications"
                                    class="form-control"
                                    maxlength="255"
                                    required
                                    value="<?php
                                        echo escapeDoctorProfileValue(
                                            $doctorProfile['qualifications']
                                        );
                                    ?>"
                                >

                            </div>

                            <!-- Years of Experience -->

                            <div class="mb-3">

                                <label
                                    for="years_experience"
                                    class="form-label"
                                >
                                    Years of Experience
                                </label>

                                <input
                                    type="number"
                                    id="years_experience"
                                    name="years_experience"
                                    class="form-control"
                                    min="0"
                                    max="255"
                                    step="1"
                                    required
                                    value="<?php
                                        echo escapeDoctorProfileValue(
                                            $doctorProfile['years_experience'] ?? 0
                                        );
                                    ?>"
                                >

                            </div>

                            <!-- Biography -->

                            <div class="mb-3">

                                <label
                                    for="biography"
                                    class="form-label"
                                >
                                    Professional Biography
                                </label>

                                <textarea
                                    id="biography"
                                    name="biography"
                                    class="form-control"
                                    rows="5"
                                    maxlength="65535"
                                    placeholder="Describe your professional background and experience."
                                ><?php
                                    echo escapeDoctorProfileValue(
                                        $doctorProfile['biography']
                                    );
                                ?></textarea>

                                <div class="form-text">
                                    Biography is optional.
                                </div>

                            </div>

                            <!-- Submit Button -->

                            <div class="d-grid">

                                <button
                                    type="submit"
                                    class="btn btn-success"
                                >
                                    Save Profile Changes
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            <?php endif; ?>

            <!-- Navigation -->

            <div class="text-center mt-4">

                <a
                    href="appointments.php"
                    class="btn btn-outline-primary me-2"
                >
                    My Appointments
                </a>

                <a
                    href="availability.php"
                    class="btn btn-secondary"
                >
                    My Availability
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>