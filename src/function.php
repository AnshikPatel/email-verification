<?php

/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
    return str_pad(strval(rand(0, 999999)), 6, '0', STR_PAD_LEFT);
}

/**
 * Send a verification code to an email.
 */
function sendVerificationEmail(string $email, string $code): bool {
    $subject = 'Your Verification Code';
    $message = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    return mail($email, $subject, $message, $headers);
}

/**
 * Register an email by storing it in a file.
 */
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    if (!in_array($email, $emails)) {
        return file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
    }

    return true; // Already registered
}

/**
 * Unsubscribe an email by removing it from the list.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    $updatedEmails = array_filter($emails, fn($e) => trim($e) !== trim($email));

    return file_put_contents($file, implode(PHP_EOL, $updatedEmails) . PHP_EOL, LOCK_EX) !== false;
}

/**
 * Fetch random XKCD comic and format data as HTML.
 */
function fetchAndFormatXKCDData(): string {
    $randomId = rand(1, 3000);
    $url = "https://xkcd.com/$randomId/info.0.json";
    $json = @file_get_contents($url);

    if (!$json) return "<p>Could not fetch comic.</p>";

    $data = json_decode($json, true);

    if (!$data || !isset($data['img'])) return "<p>Invalid comic data.</p>";

    return "<h2>XKCD Comic</h2>
            <img src=\"{$data['img']}\" alt=\"XKCD Comic\">
            <p><a href=\"https://yourdomain.com/src/unsubscribe.php\" id=\"unsubscribe-button\">Unsubscribe</a></p>";
}

/**
 * Send the formatted XKCD updates to registered emails.
 */
function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

    $htmlBody = fetchAndFormatXKCDData();
    $subject = "Your XKCD Comic";
    $headers = "From: no-reply@example.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    foreach ($emails as $email) {
        mail($email, $subject, $htmlBody, $headers);
    }
}