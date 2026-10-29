
# Sprint 4 - US-13
# Secure Appointment Reminder Launcher
#
# Loads SMTP credentials stored outside GitHub.
# Uses --check mode only: no emails are sent.

param(
    [ValidateSet('check', 'send')]
    [string]$Mode = 'check'
)

$ErrorActionPreference = "Stop"

$secretFolder = Join-Path $env:LOCALAPPDATA "ClinicAppointmentSystem"

$usernameFile = Join-Path $secretFolder "smtp-user.txt"
$passwordFile = Join-Path $secretFolder "smtp-password.dpapi"

# Verify the saved credentials exist.
if (
    -not (Test-Path $usernameFile) -or
    -not (Test-Path $passwordFile)
) {
    Write-Error "Mailtrap credential files are missing."
    exit 1
}

# Configure the Mailtrap testing server.
$env:CLINIC_SMTP_HOST = "sandbox.smtp.mailtrap.io"
$env:CLINIC_SMTP_PORT = "2525"
$env:CLINIC_SMTP_SECURITY = "tls"
$env:CLINIC_SMTP_FROM_EMAIL = "clinic@example.com"

# Read the stored username.
$env:CLINIC_SMTP_USER = (
    Get-Content -LiteralPath $usernameFile -Raw
).Trim()

# Decrypt the password for this process only.

$encryptedPassword = (
    Get-Content -LiteralPath $passwordFile -Raw
).Trim()

$securePassword = ConvertTo-SecureString -String $encryptedPassword

$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR(
    $securePassword
)

try {
    $env:CLINIC_SMTP_PASSWORD =
        [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
}
finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
    Remove-Variable pointer, securePassword
}

Write-Output "SMTP configuration loaded securely."

# Run the existing read-only PHP reminder check.

$phpMode = if ($Mode -eq 'send') {
    '--send'
} else {
    '--check'
}

& "C:\xampp\php\php.exe" `
    "C:\xampp\htdocs\Clinic-Appointment-System\10-Source-Code\scripts\send_appointment_reminders.php" `
    $phpMode

exit $LASTEXITCODE
