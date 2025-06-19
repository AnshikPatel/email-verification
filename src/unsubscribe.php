<?php
require_once 'functions.php';

$unsubscribeMessage = '';
$confirmationMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Step 1: User submits email to unsubscribe
    if (isset($_POST['unsubscribe_email']) && !isset($_POST['verification_code'])) {
        $email = trim($_POST['unsubscribe_email']);

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();

            // Save the unsubscribe code
            $codes = file_exists('unsubscribe_codes.json') ? json_decode(file_get_contents('unsubscribe_codes.json'), true) : [];
            $codes[$email] = $code;
            file_put_contents('unsubscribe_codes.json', json_encode($codes));

            // Send the code
            $subject = 'Confirm Un-subscription';
            $message = "<p>To confirm un-subscription, use this code: <strong>$code</strong></p>";
            $headers = "From: no-reply@example.com\r\nContent-Type: text/html";
            mail($email, $subject, $message, $headers);

            $unsubscribeMessage = "A confirmation code has been sent to <strong>$email</strong>.";
        } else {
            $unsubscribeMessage = "Invalid email address.";
        }

    // Step 2: User enters verification code
    } elseif (isset($_POST['verification_code']) && isset($_POST['unsubscribe_email'])) {
        $email = trim($_POST['unsubscribe_email']);
        $inputCode = trim($_POST['verification_code']);

        $codes = file_exists('unsubscribe_codes.json') ? json_decode(file_get_contents('unsubscribe_codes.json'), true) : [];

        if (isset($codes[$email]) && $codes[$email] === $inputCode) {
            unsubscribeEmail($email);
            unset($codes[$email]);
            file_put_contents('unsubscribe_codes.json', json_encode($codes));
            $confirmationMessage = "✅ You have been unsubscribed successfully.";
        } else {
            $confirmationMessage = "❌ Invalid confirmation code.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe from XKCD Comics</title>
</head>
<body>
    <h2>Unsubscribe from XKCD Comics</h2>

    <!-- Unsubscribe Email Form -->
    <form method="POST">
        <input type="email" name="unsubscribe_email" required placeholder="Enter your email">
        <button type="submit" id="submit-unsubscribe">Unsubscribe</button>
    </form>
    <p style="color:blue;"><?= $unsubscribeMessage ?></p>

    <!-- Verification Code Form -->
    <form method="POST">
        <input type="hidden" name="unsubscribe_email" value="<?= htmlspecialchars($_POST['unsubscribe_email'] ?? '') ?>">
        <input type="text" name="verification_code" maxlength="6" required placeholder="Enter verification code">
        <button type="submit" id="submit-verification">Verify</button>
    </form>
    <p style="color:green;"><?= $confirmationMessage ?></p>
</body>
</html>