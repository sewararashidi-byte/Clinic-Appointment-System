<?php

/**
 * US-11: Patient Appointment Confirmation Page
 *
 * Displays confirmation details loaded securely
 * from Sewara's backend.
 */

require_once __DIR__ . '/../includes/appointment_confirmation_data.php';

// Escape all database values before displaying them.
function escapeConfirmationValue($value): string
{
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Appointment Confirmation - Clinic Appointment System</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container mt-5 mb-5">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <h2 class="text-center mb-4">
                Appointment Confirmation
            </h2>

            <?php if ($confirmation === null): ?>

                <!-- Invalid or Unauthorized Appointment -->

                <div class="alert alert-warning" role="alert">

                    <?php
                    echo escapeConfirmationValue(
                        $confirmationError
                    );
                    ?>

                </div>

            <?php else: ?>

                <!-- Appointment Confirmation Details -->

                <div class="card shadow-sm">

                    <div class="card-header bg-success text-white">

                        <h4 class="mb-0">
                            Appointment Details
                        </h4>

                    </div>

                    <div class="card-body">

                        <div class="alert alert-success">

                            Your appointment details
                            are shown below.

                        </div>

                        <table class="table table-bordered">

                            <tbody>

                                <tr>
                                    <th>Appointment Reference</th>
                                    <td>
                                        #<?php
                                        echo (int) $confirmation['appointment_id'];
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Doctor</th>
                                    <td>
                                        <?php
                                        echo escapeConfirmationValue(
                                            $confirmation['doctor_name']
                                        );
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Appointment Date</th>
                                    <td>
                                        <?php
                                        echo escapeConfirmationValue(
                                            date(
                                                'F j, Y',
                                                strtotime(
                                                    $confirmation['appointment_date']
                                                )
                                            )
                                        );
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Start Time</th>
                                    <td>
                                        <?php
                                        echo escapeConfirmationValue(
                                            date(
                                                'g:i A',
                                                strtotime(
                                                    $confirmation['start_time']
                                                )
                                            )
                                        );
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th>End Time</th>
                                    <td>
                                        <?php
                                        echo escapeConfirmationValue(
                                            date(
                                                'g:i A',
                                                strtotime(
                                                    $confirmation['end_time']
                                                )
                                            )
                                        );
                                        ?>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Status</th>
                                    <td>
                                        <span class="badge bg-primary">
                                            <?php
                                            echo escapeConfirmationValue(
                                                ucfirst($confirmation['status'])
                                            );
                                            ?>
                                        </span>
                                    </td>
                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            <?php endif; ?>

            <div class="text-center mt-4">

                <a
                    href="doctor-search.php"
                    class="btn btn-primary"
                >
                    Back to Doctor Search
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>