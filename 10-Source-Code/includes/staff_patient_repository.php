
<?php

/**
 * Sprint 5 — US-17: Patient Information Management
 * Database functions for staff patient management.
 */

function getStaffPatients(mysqli $conn): array
{
    $sql = "
        SELECT
            id,
            full_name,
            email,
            phone,
            account_status
        FROM users
        WHERE role = 'patient'
        ORDER BY full_name ASC, id ASC
    ";

    $result = $conn->query($sql);

    return $result->fetch_all(MYSQLI_ASSOC);
}

function getStaffPatientById(
    mysqli $conn,
    int $patientId
): ?array {

    if ($patientId <= 0) {
        return null;
    }

    $sql = "
        SELECT
            id,
            full_name,
            age,
            email,
            phone,
            home_address,
            account_status
        FROM users
        WHERE id = ?
          AND role = 'patient'
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param('i', $patientId);
    $stmt->execute();

    $patient = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $patient ?: null;
}


/**
 * Sprint 5 — US-17
 * Update permitted general patient information.
 *
 * A null homeAddress argument means that an older
 * form did not submit the field, so preserve it.
 */
function updateStaffPatient(
    mysqli $conn,
    int $patientId,
    string $fullName,
    ?int $age,
    string $phone,
    string $email,
    ?string $homeAddress = null
): bool {

    $fullName = trim($fullName);
    $phone = trim($phone);
    $email = trim($email);

    if ($patientId <= 0) {
        throw new DomainException('Invalid patient.');
    }

    if ($fullName === '' || strlen($fullName) > 100) {
        throw new DomainException('Invalid patient name.');
    }

    if ($age !== null && ($age < 1 || $age > 120)) {
        throw new DomainException('Invalid patient age.');
    }

    if (!preg_match('/^[0-9+() .-]{7,20}$/', $phone)) {
        throw new DomainException('Invalid phone number.');
    }

    if (
        strlen($email) > 150 ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        throw new DomainException('Invalid email address.');
    }

    // Only change the address when the form supplies it.
    $updateAddress = $homeAddress !== null;

    if ($updateAddress) {
        $homeAddress = trim($homeAddress);

        if (strlen($homeAddress) > 255) {
            throw new DomainException(
                'Home address must not exceed 255 characters.'
            );
        }

        // The address is optional.
        if ($homeAddress === '') {
            $homeAddress = null;
        }
    }

    $sql = "
        UPDATE users
        SET
            full_name = ?,
            age = ?,
            phone = ?,
            email = ?
    ";

    if ($updateAddress) {
        $sql .= ", home_address = ?";
    }

    $sql .= "
        WHERE id = ?
          AND role = 'patient'
    ";

    $stmt = $conn->prepare($sql);

    try {
        if ($updateAddress) {
            $stmt->bind_param(
                'sisssi',
                $fullName,
                $age,
                $phone,
                $email,
                $homeAddress,
                $patientId
            );
        } else {
            $stmt->bind_param(
                'sissi',
                $fullName,
                $age,
                $phone,
                $email,
                $patientId
            );
        }

        $stmt->execute();

        return $stmt->affected_rows === 1;

    } finally {
        $stmt->close();
    }
}

