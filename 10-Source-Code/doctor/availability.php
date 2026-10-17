<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

requireRole(['doctor']);

$doctorId = (int) $_SESSION['user_id'];
$fullName = $_SESSION['full_name'] ?? 'Doctor';

$availabilitySuccess = $_SESSION['availability_success'] ?? '';
unset($_SESSION['availability_success']);

$availabilityError = $_SESSION['availability_error'] ?? '';
unset($_SESSION['availability_error']);

/*
 * Retrieve availability records belonging
 * to the currently logged-in doctor.
 */
$stmt = $conn->prepare(
    "SELECT
        id,
        availability_date,
        start_time,
        end_time,
        slot_duration_minutes
     FROM doctor_availability
     WHERE doctor_id = ?
     ORDER BY availability_date ASC, start_time ASC"
);

$stmt->bind_param(
    "i",
    $doctorId
);

$stmt->execute();

$availabilityResult = $stmt->get_result();

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
        Manage Availability - Clinic Appointment System
    </title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container mt-5 mb-5">

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>
                Manage Availability
            </h2>

            <p class="text-muted mb-0">
                Logged in as
                <strong>
                    <?php echo htmlspecialchars($fullName); ?>
                </strong>
            </p>

        </div>

        <a
            href="../dashboards/doctor-dashboard.php"
            class="btn btn-secondary"
        >
            Back to Dashboard
        </a>

    </div>


    <!-- Success Message -->
    <?php if ($availabilitySuccess !== ''): ?>

        <div class="alert alert-success">

            <?php
            echo htmlspecialchars($availabilitySuccess);
            ?>

        </div>

    <?php endif; ?>


    <!-- Error Message -->
    <?php if ($availabilityError !== ''): ?>

        <div class="alert alert-danger">

            <?php
            echo htmlspecialchars($availabilityError);
            ?>

        </div>

    <?php endif; ?>


    <!-- Add Availability -->
    <div class="card shadow p-4 mb-4">

        <h4 class="mb-4">
            Add Availability
        </h4>

        <form
            action="../actions/availability_create.php"
            method="POST"
        >

            <div class="mb-3">

                <label
                    for="availability_date"
                    class="form-label"
                >
                    Availability Date
                </label>

                <input
                    type="date"
                    class="form-control"
                    id="availability_date"
                    name="availability_date"
                    required
                >

            </div>


            <div class="row">

                <div class="col-md-6 mb-3">

                    <label
                        for="start_time"
                        class="form-label"
                    >
                        Start Time
                    </label>

                    <input
                        type="time"
                        class="form-control"
                        id="start_time"
                        name="start_time"
                        required
                    >

                </div>


                <div class="col-md-6 mb-3">

                    <label
                        for="end_time"
                        class="form-label"
                    >
                        End Time
                    </label>

                    <input
                        type="time"
                        class="form-control"
                        id="end_time"
                        name="end_time"
                        required
                    >

                </div>

            </div>


            <div class="mb-3">

                <label class="form-label">
                    Appointment Duration
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="30 minutes"
                    disabled
                >

                <div class="form-text">
                    Appointment slots are currently set to 30 minutes.
                </div>

            </div>


            <button
                type="submit"
                class="btn btn-primary"
            >
                Add Availability
            </button>

        </form>

    </div>


    <!-- Existing Availability -->
    <div class="card shadow p-4">

        <h4 class="mb-4">
            My Availability
        </h4>


        <?php if ($availabilityResult->num_rows === 0): ?>

            <div class="alert alert-info mb-0">
                You have not added any availability yet.
            </div>

        <?php else: ?>

            <div class="table-responsive">

                <table class="table table-bordered align-middle">

                    <thead class="table-light">

                        <tr>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($availability = $availabilityResult->fetch_assoc()): ?>

                        <?php
                        $availabilityId = (int) $availability['id'];
                        $updateFormId = 'update-form-' . $availabilityId;
                        ?>

                        <tr>

                            <td>

                                <input
                                    type="date"
                                    class="form-control"
                                    name="availability_date"
                                    form="<?php echo $updateFormId; ?>"
                                    value="<?php
                                    echo htmlspecialchars(
                                        $availability['availability_date']
                                    );
                                    ?>"
                                    required
                                >

                            </td>


                            <td>

                                <input
                                    type="time"
                                    class="form-control"
                                    name="start_time"
                                    form="<?php echo $updateFormId; ?>"
                                    value="<?php
                                    echo htmlspecialchars(
                                        substr(
                                            $availability['start_time'],
                                            0,
                                            5
                                        )
                                    );
                                    ?>"
                                    required
                                >

                            </td>


                            <td>

                                <input
                                    type="time"
                                    class="form-control"
                                    name="end_time"
                                    form="<?php echo $updateFormId; ?>"
                                    value="<?php
                                    echo htmlspecialchars(
                                        substr(
                                            $availability['end_time'],
                                            0,
                                            5
                                        )
                                    );
                                    ?>"
                                    required
                                >

                            </td>


                            <td>

                                <?php
                                echo (int)
                                    $availability['slot_duration_minutes'];
                                ?>
                                minutes

                            </td>


                            <td>

                                <div class="d-flex gap-2 flex-wrap">

                                    <!-- Update Form -->
                                    <form
                                        id="<?php echo $updateFormId; ?>"
                                        action="../actions/availability_update.php"
                                        method="POST"
                                    >

                                        <input
                                            type="hidden"
                                            name="availability_id"
                                            value="<?php echo $availabilityId; ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-warning btn-sm"
                                        >
                                            Update
                                        </button>

                                    </form>


                                    <!-- Delete Form -->
                                    <form
                                        action="../actions/availability_delete.php"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this availability?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="availability_id"
                                            value="<?php echo $availabilityId; ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>

<?php

$stmt->close();
$conn->close();

?>