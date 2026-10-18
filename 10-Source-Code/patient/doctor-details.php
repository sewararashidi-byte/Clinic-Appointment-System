<?php

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/doctor_repository.php';

// Get the selected doctor ID from the URL.
$doctorId = filter_var(
    $_GET['doctor_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$doctor = null;
$availability = [];

// Retrieve information only for a valid doctor ID.
if (is_int($doctorId)) {

    $doctor = getDoctorById($conn, $doctorId);

    if ($doctor !== null) {
        $availability = getDoctorAvailability($conn, $doctorId);
    }
}

// Escape database values before displaying them.
function escapeHtml($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Doctor Details - Clinic Appointment System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Doctor Information and Availability</h2>

        <a
            href="doctor-search.php"
            class="btn btn-secondary"
        >
            Back to Doctor Search
        </a>

    </div>

    <?php if ($doctor === null): ?>

        <div class="alert alert-warning">
            Doctor not found or unavailable.
        </div>

    <?php else: ?>

        <!-- Doctor Professional Information -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    Doctor Professional Information
                </h4>
            </div>

            <div class="card-body">

                <h3 class="mb-3">
                    <?php echo escapeHtml($doctor['full_name']); ?>
                </h3>

                <p>
                    <strong>Medical Specialty:</strong>
                    <?php echo escapeHtml($doctor['specialty']); ?>
                </p>

                <p>
                    <strong>Qualifications:</strong>
                    <?php echo escapeHtml($doctor['qualifications']); ?>
                </p>

                <p>
                    <strong>Years of Experience:</strong>
                    <?php echo escapeHtml($doctor['years_experience']); ?>
                </p>

                <hr>

                <h5>Professional Biography</h5>

                <p>
                    <?php
                    if (!empty($doctor['biography'])) {
                        echo nl2br(
                            escapeHtml($doctor['biography'])
                        );
                    } else {
                        echo 'No professional biography is available.';
                    }
                    ?>
                </p>

            </div>

        </div>

        <!-- Doctor Availability -->

        <div class="card shadow-sm">

            <div class="card-header bg-success text-white">

                <h4 class="mb-0">
                    Current Doctor Availability
                </h4>

            </div>

            <div class="card-body">

                <?php if (empty($availability)): ?>

                    <div class="alert alert-info mb-0">
                        No current or future availability
                        is listed for this doctor.
                    </div>

                <?php else: ?>

                    <p class="text-muted">
                        The following availability has been
                        provided by the doctor.
                    </p>

                    <div class="table-responsive">

                        <table class="table table-bordered table-striped">

                            <thead class="table-light">

                                <tr>
                                    <th>Date</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Slot Duration</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($availability as $entry): ?>

                                    <tr>

                                        <td>
                                            <?php
                                            echo escapeHtml(
                                                $entry['availability_date']
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo escapeHtml(
                                                date(
                                                    'g:i A',
                                                    strtotime($entry['start_time'])
                                                )
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo escapeHtml(
                                                date(
                                                    'g:i A',
                                                    strtotime($entry['end_time'])
                                                )
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo escapeHtml(
                                                $entry['slot_duration_minutes']
                                            );
                                            ?>
                                            minutes
                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                    <p class="text-muted small">
                        Individual appointment-slot selection
                        and booking will be implemented in
                        Sprint 2 Day 3.
                    </p>

                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>

</div>

</body>

</html>