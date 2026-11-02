
<?php
/**
 * Create fictional demonstration doctor accounts.
 *
 * Run from the PHP command line only.
 * Do not open this file in a browser.
 *
 * Existing doctors and appointments are not modified.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only run from the command line.');
}

require_once __DIR__ . '/../config/database.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($conn) || !($conn instanceof mysqli)) {
    exit("Database connection is unavailable.\n");
}

$conn->set_charset('utf8mb4');

$doctors = [
    [
        'name' => 'Dr. Laila Rahimi',
        'email' => 'laila.rahimi@clinic-demo.test',
        'phone' => '0000000001',
        'specialty' => 'General Medicine',
        'qualifications' => 'MBBS, General Medicine',
        'experience' => 9,
        'biography' =>
            'Fictional demonstration doctor profile for the university ' .
            'Clinic Appointment System. This example profile represents ' .
            'general medical consultations and routine health concerns.',
        'room' => 'G-101'
    ],
    [
        'name' => 'Dr. Omar Farhadi',
        'email' => 'omar.farhadi@clinic-demo.test',
        'phone' => '0000000002',
        'specialty' => 'Dermatology',
        'qualifications' => 'MBBS, MD in Dermatology',
        'experience' => 7,
        'biography' =>
            'Fictional demonstration doctor profile for the university ' .
            'Clinic Appointment System. This example profile represents ' .
            'consultations relating to skin, hair, and nail conditions.',
        'room' => 'D-202'
    ],
    [
        'name' => 'Dr. Mariam Azizi',
        'email' => 'mariam.azizi@clinic-demo.test',
        'phone' => '0000000003',
        'specialty' => 'Pediatrics',
        'qualifications' => 'MBBS, MD in Pediatrics',
        'experience' => 11,
        'biography' =>
            'Fictional demonstration doctor profile for the university ' .
            'Clinic Appointment System. This example profile represents ' .
            'pediatric appointments and general child health consultations.',
        'room' => 'P-103'
    ],
    [
        'name' => 'Dr. Farid Nouri',
        'email' => 'farid.nouri@clinic-demo.test',
        'phone' => '0000000004',
        'specialty' => 'Orthopedics',
        'qualifications' => 'MBBS, MS in Orthopedics',
        'experience' => 10,
        'biography' =>
            'Fictional demonstration doctor profile for the university ' .
            'Clinic Appointment System. This example profile represents ' .
            'consultations relating to bones, joints, and movement concerns.',
        'room' => 'O-204'
    ]
];

$createdAccounts = [];

try {
    $conn->begin_transaction();

    foreach ($doctors as $doctor) {

        // Avoid creating duplicate demonstration accounts.
        $check = $conn->prepare(
            'SELECT id FROM users WHERE email = ? LIMIT 1'
        );

        $check->bind_param('s', $doctor['email']);
        $check->execute();
        $check->store_result();

        $alreadyExists = $check->num_rows > 0;
        $check->close();

        if ($alreadyExists) {
            throw new RuntimeException(
                'A demonstration account already exists: ' .
                $doctor['email'] .
                '. No records were added.'
            );
        }

        // Generate a different password for every doctor.
        // The password is not hardcoded or saved in the script.
        $password = 'Demo#' . bin2hex(random_bytes(10)) . 'A';

        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        if ($passwordHash === false) {
            throw new RuntimeException(
                'Could not hash the account password.'
            );
        }

        $name = $doctor['name'];
        $phone = $doctor['phone'];
        $email = $doctor['email'];
        $role = 'doctor';
        $status = 'active';

        $userStatement = $conn->prepare(
            'INSERT INTO users
                (
                    full_name,
                    phone,
                    email,
                    password_hash,
                    role,
                    account_status
                )
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $userStatement->bind_param(
            'ssssss',
            $name,
            $phone,
            $email,
            $passwordHash,
            $role,
            $status
        );

        $userStatement->execute();
        $doctorUserId = $conn->insert_id;
        $userStatement->close();

        $specialty = $doctor['specialty'];
        $qualifications = $doctor['qualifications'];
        $experience = $doctor['experience'];
        $biography = $doctor['biography'];
        $room = $doctor['room'];

        $profileStatement = $conn->prepare(
            'INSERT INTO doctor_profiles
                (
                    user_id,
                    specialty,
                    qualifications,
                    years_experience,
                    biography,
                    consultation_room
                )
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $profileStatement->bind_param(
            'ississ',
            $doctorUserId,
            $specialty,
            $qualifications,
            $experience,
            $biography,
            $room
        );

        $profileStatement->execute();
        $profileStatement->close();

        $createdAccounts[] = [
            'name' => $name,
            'email' => $email,
            'password' => $password
        ];
    }

    $conn->commit();

    echo "\nSUCCESS: Four demonstration doctors created.\n";
    echo "Existing doctors and appointments were preserved.\n\n";

    echo "LOCAL DEMONSTRATION LOGIN CREDENTIALS\n";
    echo "------------------------------------\n";

    foreach ($createdAccounts as $account) {
        echo "Doctor: " . $account['name'] . "\n";
        echo "Email: " . $account['email'] . "\n";
        echo "Password: " . $account['password'] . "\n\n";
    }

    echo "Store these credentials privately.\n";
    echo "Do not commit or share the terminal output.\n";

} catch (Throwable $error) {

    $conn->rollback();

    fwrite(
        STDERR,
        "\nSETUP FAILED: " . $error->getMessage() . "\n"
    );

    fwrite(
        STDERR,
        "The transaction was rolled back.\n"
    );

    exit(1);
}

$conn->close();
