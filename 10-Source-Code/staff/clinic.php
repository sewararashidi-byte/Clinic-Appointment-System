

<?php


/**
 * Clinic Information Management
 * Authorized staff frontend.
 */


require_once __DIR__ . '/../config/auth.php';


requireRole(['receptionist', 'administrator']);


require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/staff_clinic_repository.php';


// Retrieve existing clinic information.
$clinic = getClinicInformation($conn);


$conn->close();


// Generate or reuse the CSRF token.
if (empty($_SESSION['staff_clinic_csrf_token'])) {
    $_SESSION['staff_clinic_csrf_token'] =
        bin2hex(random_bytes(32));
}


// Read one-time messages.
$success = $_SESSION['staff_clinic_success'] ?? '';
$error = $_SESSION['staff_clinic_error'] ?? '';


unset(
    $_SESSION['staff_clinic_success'],
    $_SESSION['staff_clinic_error']
);


function escapeClinic($value): string
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


    <title>
        Clinic Information | Clinic Appointment System
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        href="../assets/css/style.css"
        rel="stylesheet"
    >


    <style>
        .staff-navbar {
            background: #13385b;
            padding: 18px 0;
        }


        .staff-brand {
            color: #ffffff;
            font-size: 1.1rem;
            font-weight: 700;
        }


        .staff-brand:hover {
            color: #a3eee1;
            text-decoration: none;
        }


        .clinic-container {
            max-width: 900px;
        }


        .page-title {
            color: #173e61;
            font-weight: 700;
        }


        .back-link {
            color: #087d85;
            font-weight: 700;
        }


        .back-link:hover {
            color: #075a60;
        }


        .clinic-card {
            background: #ffffff;
            border: 1px solid #e1ebf0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 8px 25px
                rgba(25, 71, 103, 0.07);
        }


        .clinic-card-header {
            background: #f4f9fb;
            padding: 25px 30px;
            border-bottom: 1px solid #e5edf2;
        }


        .clinic-card-header h2 {
            color: #173e61;
            font-size: 1.2rem;
            font-weight: 700;
        }


        .clinic-card-body {
            padding: 32px;
        }


        .section-heading {
            color: #173e61;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 20px;
        }


        .form-label {
            color: #344e63;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 8px;
        }


        .form-control {
            min-height: 48px;
            border: 1px solid #d5e0e8;
            border-radius: 10px;
            padding: 11px 14px;
        }


        .form-control:focus {
            border-color: #16888e;
            box-shadow: 0 0 0 0.2rem
                rgba(22, 136, 142, 0.13);
        }


        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }


        .required-mark {
            color: #b73a43;
        }


        .info-panel {
            background: #edf8f7;
            border: 1px solid #d5eeea;
            color: #376a66;
            border-radius: 12px;
            padding: 18px;
        }


        .btn-save {
            background: #087d85;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px 22px;
            font-weight: 700;
        }


        .btn-save:hover {
            background: #08656b;
            color: #ffffff;
        }


        .btn-back {
            background: #eef3f6;
            color: #38536a;
            border-radius: 10px;
            padding: 12px 22px;
            font-weight: 700;
        }


        .btn-back:hover {
            background: #dfe9ef;
            color: #173e61;
        }


        .updated-info {
            color: #738599;
            font-size: 0.85rem;
        }


        @media (max-width: 576px) {
            .page-title {
                font-size: 1.6rem;
            }


            .clinic-card-header,
            .clinic-card-body {
                padding: 20px;
            }


            .btn-save,
            .btn-back {
                width: 100%;
                text-align: center;
            }
        }
    </style>


</head>


<body>


<nav class="staff-navbar">


    <div class="container">


        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">


            <a
                href="../dashboards/admin-dashboard.php"
                class="staff-brand"
            >
                🏥 Clinic Appointment System
            </a>


            <a
                href="../logout.php"
                class="btn btn-outline-light btn-sm"
            >
                Logout
            </a>


        </div>


    </div>


</nav>


<main class="container clinic-container py-5">


    <a
        href="../dashboards/admin-dashboard.php"
        class="back-link"
    >
        &larr; Back to Dashboard
    </a>


    <div class="mt-4 mb-4">


        <h1 class="page-title">
            Clinic Information
        </h1>


        <p class="text-muted">
            Manage the clinic's contact details,
            location and opening hours.
        </p>


    </div>


    <?php if ($success !== ''): ?>


        <div class="alert alert-success" role="status">
            <?= escapeClinic($success) ?>
        </div>


    <?php endif; ?>


    <?php if ($error !== ''): ?>


        <div class="alert alert-danger" role="alert">
            <?= escapeClinic($error) ?>
        </div>


    <?php endif; ?>


    <?php if ($clinic === null): ?>


        <div class="info-panel mb-4">


            <strong>
                Clinic information has not been added yet.
            </strong>


            <p class="mb-0 mt-2">
                Enter the approved clinic details below
                to create the clinic information record.
            </p>


        </div>


    <?php endif; ?>


    <section class="clinic-card">


        <div class="clinic-card-header">


            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">


                <div>


                    <h2 class="mb-1">
                        Clinic Details
                    </h2>


                    <p class="text-muted small mb-0">
                        Keep the clinic's information accurate
                        and up to date.
                    </p>


                </div>


                <?php if ($clinic !== null): ?>


                    <span class="badge bg-success rounded-pill px-3 py-2">
                        Information Saved
                    </span>


                <?php else: ?>


                    <span class="badge bg-secondary rounded-pill px-3 py-2">
                        Not Yet Added
                    </span>


                <?php endif; ?>


            </div>


        </div>


        <div class="clinic-card-body">


            <form
                method="POST"
                action="../actions/staff_clinic_update.php"
            >


                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= escapeClinic(
                        $_SESSION['staff_clinic_csrf_token']
                    ) ?>"
                >


                <h3 class="section-heading">
                    General Information
                </h3>


                <div class="mb-4">


                    <label
                        for="clinicName"
                        class="form-label"
                    >
                        Clinic Name
                        <span class="required-mark">*</span>
                    </label>


                    <input
                        id="clinicName"
                        type="text"
                        name="clinic_name"
                        class="form-control"
                        maxlength="150"
                        required
                        placeholder="Enter clinic name"
                        value="<?= escapeClinic(
                            $clinic['clinic_name'] ?? ''
                        ) ?>"
                    >


                </div>


                <div class="mb-4">


                    <label
                        for="clinicAddress"
                        class="form-label"
                    >
                        Clinic Address
                        <span class="required-mark">*</span>
                    </label>


                    <textarea
                        id="clinicAddress"
                        name="clinic_address"
                        class="form-control"
                        rows="3"
                        maxlength="255"
                        required
                        placeholder="Enter the clinic address"
                    ><?= escapeClinic(
                        $clinic['clinic_address'] ?? ''
                    ) ?></textarea>


                </div>


                <hr class="my-4">


                <h3 class="section-heading">
                    Contact Information
                </h3>


                <div class="mb-4">


                    <label
                        for="contactPhone"
                        class="form-label"
                    >
                        Contact Phone Number
                        <span class="required-mark">*</span>
                    </label>


                    <input
                        id="contactPhone"
                        type="tel"
                        name="contact_phone"
                        class="form-control"
                        maxlength="30"
                        required
                        placeholder="Enter clinic phone number"
                        value="<?= escapeClinic(
                            $clinic['contact_phone'] ?? ''
                        ) ?>"
                    >


                </div>


                <hr class="my-4">


                <h3 class="section-heading">
                    Operating Hours
                </h3>


                <div class="mb-4">


                    <label
                        for="openingHours"
                        class="form-label"
                    >
                        Opening Hours
                        <span class="required-mark">*</span>
                    </label>


                    <input
                        id="openingHours"
                        type="text"
                        name="opening_hours"
                        class="form-control"
                        maxlength="255"
                        required
                        placeholder="e.g. Saturday–Thursday, 8:00 AM–5:00 PM"
                        value="<?= escapeClinic(
                            $clinic['opening_hours'] ?? ''
                        ) ?>"
                    >


                    <div class="form-text">
                        Enter the clinic's official operating days
                        and hours.
                    </div>


                </div>


                <div class="info-panel mb-4">


                    <strong>
                        Keep information accurate
                    </strong>


                    <p class="small mb-0 mt-2">
                        Confirm the clinic name, address,
                        contact number and operating hours
                        before saving.
                    </p>


                </div>


                <div class="d-flex flex-wrap gap-3">


                    <button
                        type="submit"
                        class="btn btn-save"
                    >
                        Save Clinic Information
                    </button>


                    <a
                        href="../dashboards/admin-dashboard.php"
                        class="btn btn-back"
                    >
                        Back to Dashboard
                    </a>


                </div>


            </form>


        </div>


    </section>


    <?php if ($clinic !== null): ?>


        <p class="updated-info text-center mt-4">
            Last updated:
            <?= escapeClinic(
                $clinic['updated_at'] ?? ''
            ) ?>
        </p>


    <?php endif; ?>


</main>


</body>
</html>