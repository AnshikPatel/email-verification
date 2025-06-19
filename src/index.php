<?php
require_once 'functions.php';

$verificationMessage = '';
$registrationMessage = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Step 1: User submits email
    if (isset($_POST['email']) && !isset($_POST['verification_code'])) {
        $email = trim($_POST['email']);

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();

            // Save the code temporarily
            $codes = file_exists('verification_codes.json') ? json_decode(file_get_contents('verification_codes.json'), true) : [];
            $codes[$email] = $code;
            file_put_contents('verification_codes.json', json_encode($codes));

            // Send the code via email
            if (sendVerificationEmail($email, $code)) {
                $verificationMessage = "Verification code sent to <strong>$email</strong>.";
            } else {
                $verificationMessage = "Failed to send verification email.";
            }
        } else {
            $verificationMessage = "Please enter a valid email address.";
        }

    // Step 2: User submits verification code
    } elseif (isset($_POST['verification_code']) && isset($_POST['email'])) {
        $email = trim($_POST['email']);
        $inputCode = trim($_POST['verification_code']);

        $codes = file_exists('verification_codes.json') ? json_decode(file_get_contents('verification_codes.json'), true) : [];

        if (isset($codes[$email]) && $codes[$email] === $inputCode) {
            registerEmail($email);
            unset($codes[$email]);
            file_put_contents('verification_codes.json', json_encode($codes));
            $registrationMessage = "✅ Email verified and registered for XKCD comics!";
        } else {
            $registrationMessage = "❌ Invalid verification code.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Email Verification</title>
</head>
<body>
    <h2>Subscribe to XKCD Comics</h2>

    <!-- Email Submission Form -->
    <form method="POST">
        <input type="email" name="email" required placeholder="Enter your email">
        <button type="submit" id="submit-email">Submit</button>
    </form>

    <p style="color:blue;"><?= $verificationMessage ?></p>

    <!-- Verification Code Form -->
    <form method="POST">
        <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        <input type="text" name="verification_code" maxlength="6" required placeholder="Enter verification code">
        <button type="submit" id="submit-verification">Verify</button>
    </form>

    <p style="color:green;"><?= $registrationMessage ?></p>
</body>
</html>