
<?php

/**
 * Sprint 4 - US-14: Doctor Profile Management
 *
 * Retrieve and save professional information
 * belonging to an authenticated doctor.
 */

/**
 * Retrieve the doctor's professional profile.
 */
function getDoctorProfessionalProfile(
    mysqli $conn,
    int $doctorId
): ?array {

    if ($doctorId <= 0) {
        return null;
    }

    $sql = "
        SELECT
            u.id AS doctor_id,
            u.full_name,
            u.phone,
            p.specialty,
            p.qualifications,
            p.years_experience,
            p.biography
        FROM users u
        LEFT JOIN doctor_profiles p
            ON p.user_id = u.id
        WHERE u.id = ?
          AND u.role = 'doctor'
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $doctorId);
    $stmt->execute();

    $profile = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    return $profile ?: null;
}


/**
 * Save a doctor's professional profile.
 *
 * Updates both users and doctor_profiles
 * within one database transaction.
 *
 * Creates the doctor_profiles record if
 * the doctor does not already have one.
 */
function saveDoctorProfessionalProfile(
    mysqli $conn,
    int $doctorId,
    string $fullName,
    string $phone,
    string $specialty,
    string $qualifications,
    int $yearsExperience,
    ?string $biography
): bool {

    $fullName = trim($fullName);
    $phone = trim($phone);
    $specialty = trim($specialty);
    $qualifications = trim($qualifications);

    if (
        $doctorId <= 0 ||
        $fullName === '' ||
        $phone === '' ||
        $specialty === '' ||
        $qualifications === '' ||
        strlen($fullName) > 100 ||
        strlen($phone) > 20 ||
        strlen($specialty) > 100 ||
        strlen($qualifications) > 255 ||
        $yearsExperience < 0 ||
        $yearsExperience > 255
    ) {
        throw new DomainException(
            'Invalid doctor profile information.'
        );
    }

    $conn->begin_transaction();

    try {

        // Lock and verify the doctor account.
        $doctorSql = "
            SELECT id
            FROM users
            WHERE id = ?
              AND role = 'doctor'
            FOR UPDATE
        ";

        $doctorStmt = $conn->prepare($doctorSql);
        $doctorStmt->bind_param('i', $doctorId);
        $doctorStmt->execute();

        $doctor = $doctorStmt
            ->get_result()
            ->fetch_assoc();

        $doctorStmt->close();

        if (!$doctor) {
            throw new DomainException(
                'Doctor account not found.'
            );
        }

        // Check whether the doctor already has
        // a professional profile.
        $checkSql = "
            SELECT id
            FROM doctor_profiles
            WHERE user_id = ?
            FOR UPDATE
        ";

        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param('i', $doctorId);
        $checkStmt->execute();

        $existingProfile = $checkStmt
            ->get_result()
            ->fetch_assoc();

        $checkStmt->close();

        // Update the doctor's name and phone.
        $userSql = "
            UPDATE users
            SET
                full_name = ?,
                phone = ?
            WHERE id = ?
              AND role = 'doctor'
        ";

        $userStmt = $conn->prepare($userSql);

        $userStmt->bind_param(
            'ssi',
            $fullName,
            $phone,
            $doctorId
        );

        $userStmt->execute();
        $userStmt->close();

        if ($existingProfile) {

            // Update an existing professional profile.
            $profileSql = "
                UPDATE doctor_profiles
                SET
                    specialty = ?,
                    qualifications = ?,
                    years_experience = ?,
                    biography = ?
                WHERE user_id = ?
            ";

            $profileStmt = $conn->prepare(
                $profileSql
            );

            $profileStmt->bind_param(
                'ssisi',
                $specialty,
                $qualifications,
                $yearsExperience,
                $biography,
                $doctorId
            );

        } else {

            // Create a professional profile
            // when one does not exist.
            $profileSql = "
                INSERT INTO doctor_profiles (
                    user_id,
                    specialty,
                    qualifications,
                    years_experience,
                    biography
                )
                VALUES (?, ?, ?, ?, ?)
            ";

            $profileStmt = $conn->prepare(
                $profileSql
            );

            $profileStmt->bind_param(
                'issis',
                $doctorId,
                $specialty,
                $qualifications,
                $yearsExperience,
                $biography
            );
        }

        $profileStmt->execute();
        $profileStmt->close();

        $conn->commit();

        return true;

    } catch (Throwable $e) {

        $conn->rollback();

        throw $e;
    }
}
