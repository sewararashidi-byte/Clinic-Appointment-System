
<?php

/**
 * Sprint 5 — US-20
 * Clinic Information Repository
 */

/**
 * Retrieve the clinic's saved information.
 */
function getClinicInformation(mysqli $conn): ?array
{
    $sql = "
        SELECT
            id,
            clinic_name,
            clinic_address,
            contact_phone,
            opening_hours,
            clinic_email,
            available_services,
            clinic_description,
            updated_at
        FROM clinic_information
        WHERE id = 1
        LIMIT 1
    ";

    $result = $conn->query($sql);

    $clinic = $result->fetch_assoc();

    return $clinic ?: null;
}

/**
 * Create or update the clinic's information.
 *
 * The calling action must validate the CSRF token.
 */
function saveClinicInformation(
    mysqli $conn,
    int $staffUserId,
    string $clinicName,
    string $clinicAddress,
    string $contactPhone,
    string $openingHours
): bool {

    $clinicName = trim($clinicName);
    $clinicAddress = trim($clinicAddress);
    $contactPhone = trim($contactPhone);
    $openingHours = trim($openingHours);

    // Validate required fields.
    if (
        $clinicName === '' ||
        strlen($clinicName) > 150
    ) {
        throw new DomainException(
            'Please enter a valid clinic name.'
        );
    }

    if (
        $clinicAddress === '' ||
        strlen($clinicAddress) > 255
    ) {
        throw new DomainException(
            'Please enter a valid clinic address.'
        );
    }

    if (
        !preg_match('/^[0-9+() .-]{7,30}$/', $contactPhone)
    ) {
        throw new DomainException(
            'Please enter a valid contact phone number.'
        );
    }

    if (
        $openingHours === '' ||
        strlen($openingHours) > 255
    ) {
        throw new DomainException(
            'Please enter valid opening hours.'
        );
    }

    // Verify authorized, active staff.
    $staffSql = "
        SELECT id
        FROM users
        WHERE id = ?
          AND role IN ('receptionist', 'administrator')
          AND account_status = 'active'
        LIMIT 1
    ";

    $staffStmt = $conn->prepare($staffSql);
    $staffStmt->bind_param('i', $staffUserId);
    $staffStmt->execute();

    $staff = $staffStmt->get_result()->fetch_assoc();
    $staffStmt->close();

    if (!$staff) {
        throw new DomainException(
            'You are not authorized to update clinic information.'
        );
    }

    // One clinic record, with ID 1.
    $sql = "
        INSERT INTO clinic_information (
            id,
            clinic_name,
            clinic_address,
            contact_phone,
            opening_hours,
            updated_by
        )
        VALUES (1, ?, ?, ?, ?, ?)

        ON DUPLICATE KEY UPDATE
            clinic_name = VALUES(clinic_name),
            clinic_address = VALUES(clinic_address),
            contact_phone = VALUES(contact_phone),
            opening_hours = VALUES(opening_hours),
            updated_by = VALUES(updated_by)
    ";

    $stmt = $conn->prepare($sql);

    try {

        $stmt->bind_param(
            'ssssi',
            $clinicName,
            $clinicAddress,
            $contactPhone,
            $openingHours,
            $staffUserId
        );

        $stmt->execute();

        return true;

    } finally {

        $stmt->close();
    }
}
