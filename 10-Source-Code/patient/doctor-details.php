<?php

require_once __DIR__ . '/../config/auth.php';

requireRole(['patient']);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/doctor_repository.php';
require_once __DIR__ . '/../includes/appointment_repository.php';

// Retrieve booking messages from the session.
$bookingSuccess = $_SESSION['booking_success'] ?? '';
$bookingError = $_SESSION['booking_error'] ?? '';

// Clear messages so they appear only once.
unset($_SESSION['booking_success']);
unset($_SESSION['booking_error']);

// Create a security token for appointment booking forms.
if (
    empty($_SESSION['booking_csrf_token']) ||
    !is_string($_SESSION['booking_csrf_token'])
) {
    $_SESSION['booking_csrf_token'] = bin2hex(
        random_bytes(32)
    );
}

$bookingToken = $_SESSION['booking_csrf_token'];

// Get the selected doctor ID from the URL.
$doctorId = filter_var(
    $_GET['doctor_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$doctor = null;
$availability = [];
$appointmentSlots = [];

// Retrieve information only for a valid doctor ID.
if (is_int($doctorId)) {

    $doctor = getDoctorById($conn, $doctorId);

    if ($doctor !== null) {

        // Retrieve the doctor's availability periods.
        $availability = getDoctorAvailability(
            $conn,
            $doctorId
        );

        // Generate available appointment slots.
        // Already-booked slots are excluded.
        $appointmentSlots = getAvailableAppointmentSlots(
            $conn,
            $doctorId,
            $availability
        );
    }
}

// Escape output before displaying it.
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

    <!-- Page Header -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Doctor Information and Availability</h2>

        <a
            href="doctor-search.php"
            class="btn btn-secondary"
        >
            Back to Doctor Search
        </a>

    </div>

    <!-- Booking Success Message -->

    <?php if ($bookingSuccess !== ''): ?>

        <div class="alert alert-success" role="alert">

            <strong>Success!</strong>

            <?php echo escapeHtml($bookingSuccess); ?>

        </div>

    <?php endif; ?>

    <!-- Booking Error Message -->

    <?php if ($bookingError !== ''): ?>

        <div class="alert alert-danger" role="alert">

            <strong>Booking Error:</strong>

            <?php echo escapeHtml($bookingError); ?>

        </div>

    <?php endif; ?>

    <!-- Check Whether Doctor Exists -->

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

                    <?php
                    echo escapeHtml($doctor['full_name']);
                    ?>

                </h3>

                <p>

                    <strong>Medical Specialty:</strong>

                    <?php
                    echo escapeHtml($doctor['specialty']);
                    ?>

                </p>

                <p>

                    <strong>Qualifications:</strong>

                    <?php
                    echo escapeHtml($doctor['qualifications']);
                    ?>

                </p>

                <p>

                    <strong>Years of Experience:</strong>

                    <?php
                    echo escapeHtml($doctor['years_experience']);
                    ?>

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

        <!-- Doctor Availability Table -->

        <div class="card shadow-sm mb-4">

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
                                                    strtotime(
                                                        $entry['start_time']
                                                    )
                                                )
                                            );
                                            ?>

                                        </td>

                                        <td>

                                            <?php
                                            echo escapeHtml(
                                                date(
                                                    'g:i A',
                                                    strtotime(
                                                        $entry['end_time']
                                                    )
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

                <?php endif; ?>

            </div>

        </div>

        <!-- Patient Appointment Booking Section -->

        <div class="card shadow-sm mb-4">

            <div class="card-header bg-primary text-white">

                <h4 class="mb-0">
                    Book an Appointment
                </h4>

            </div>

            <div class="card-body">

                <p class="text-muted">

                    Choose one of the available appointment
                    times below to book an appointment
                    with this doctor.

                </p>

                <!-- No Available Slots -->

                <?php if (empty($appointmentSlots)): ?>

                    <div class="alert alert-info mb-0">

                        No appointment slots are currently
                        available for this doctor.

                        Please check again later.

                    </div>

                <?php else: ?>

                    <!-- Available Appointment Slots -->

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover align-middle">

                            <thead class="table-light">

                                <tr>
                                    <th>Date</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                <?php foreach ($appointmentSlots as $slot): ?>

                                    <tr>

                                        <!-- Appointment Date -->

                                        <td>

                                            <?php
                                            echo escapeHtml(
                                                date(
                                                    'M j, Y',
                                                    strtotime(
                                                        $slot['appointment_date']
                                                    )
                                                )
                                            );
                                            ?>

                                        </td>

                                        <!-- Appointment Start Time -->

                                        <td>

                                            <?php
                                            echo escapeHtml(
                                                date(
                                                    'g:i A',
                                                    strtotime(
                                                        $slot['start_time']
                                                    )
                                                )
                                            );
                                            ?>

                                        </td>

                                        <!-- Appointment End Time -->

                                        <td>

                                            <?php
                                            echo escapeHtml(
                                                date(
                                                    'g:i A',
                                                    strtotime(
                                                        $slot['end_time']
                                                    )
                                                )
                                            );
                                            ?>

                                        </td>

                                        <!-- Appointment Booking Form -->

                                        <td>

                                            <form
                                                method="POST"
                                                action="../actions/appointment_book.php"
                                                class="m-0"
                                            >

                                                <!-- Selected Doctor -->

                                                <input
                                                    type="hidden"
                                                    name="doctor_id"
                                                    value="<?php echo (int) $doctorId; ?>"
                                                >

                                                <!-- Selected Availability -->

                                                <input
                                                    type="hidden"
                                                    name="availability_id"
                                                    value="<?php echo (int) $slot['availability_id']; ?>"
                                                >

                                                <!-- Selected Date -->

                                                <input
                                                    type="hidden"
                                                    name="appointment_date"
                                                    value="<?php echo escapeHtml($slot['appointment_date']); ?>"
                                                >

                                                <!-- Selected Start Time -->

                                                <input
                                                    type="hidden"
                                                    name="start_time"
                                                    value="<?php echo escapeHtml($slot['start_time']); ?>"
                                                >

                                                <!-- CSRF Security Token -->

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?php echo escapeHtml($bookingToken); ?>"
                                                >

                                                <!-- Booking Button -->

                                                <button
                                                    type="submit"
                                                    class="btn btn-primary btn-sm"
                                                >
                                                    Book Appointment
                                                </button>

                                            </form>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                    <p class="text-muted small mb-0">

                        Only available appointment slots are
                        displayed. Once a slot is booked,
                        it will no longer appear as available.

                    </p>

                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>

</div>

</body>

</html>