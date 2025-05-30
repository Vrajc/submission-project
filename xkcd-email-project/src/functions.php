<?php
function generateVerificationCode() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function registerEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    if (!in_array($email, $emails)) {
        file_put_contents($file, $email . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

function unsubscribeEmail($email) {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $emails = array_filter($emails, function($registeredEmail) use ($email) {
        return trim($registeredEmail) !== trim($email);
    });
    file_put_contents($file, implode(PHP_EOL, $emails) . (count($emails) ? PHP_EOL : ''));
}

function sendVerificationEmail($email, $code) {
    $subject = 'Your Verification Code';
    $message = "<p>Your verification code is: <strong>{$code}</strong></p>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= 'From: no-reply@example.com' . "\r\n";
    mail($email, $subject, $message, $headers);
}

function sendUnsubscribeVerificationEmail($email, $code) {
    $subject = 'Confirm Un-subscription';
    $message = "<p>To confirm un-subscription, use this code: <strong>{$code}</strong></p>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= 'From: no-reply@example.com' . "\r\n";
    mail($email, $subject, $message, $headers);
}

function verifyCode($email, $code) {
    $file = __DIR__ . '/verification_codes.txt';
    if (!file_exists($file)) return false;
    $verificationCodes = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($verificationCodes as $line) {
        if (strpos($line, ':') !== false) {
            list($registeredEmail, $storedCode) = explode(':', $line, 2);
        } else {
            list($registeredEmail, $storedCode) = explode(',', $line, 2);
        }
        if (trim($registeredEmail) === trim($email) && trim($storedCode) === trim($code)) {
            return true;
        }
    }
    return false;
}

// Remove verification code after use
function removeVerificationCode($email, $code) {
    $file = __DIR__ . '/verification_codes.txt';
    if (!file_exists($file)) return;
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lines = array_filter($lines, function($line) use ($email, $code) {
        if (strpos($line, ':') !== false) {
            list($registeredEmail, $storedCode) = explode(':', $line, 2);
        } else {
            list($registeredEmail, $storedCode) = explode(',', $line, 2);
        }
        return !(trim($registeredEmail) === trim($email) && trim($storedCode) === trim($code));
    });
    file_put_contents($file, implode(PHP_EOL, $lines) . (count($lines) ? PHP_EOL : ''));
}

function fetchAndFormatXKCDData() {
    $randomComicID = rand(1, 2800);
    $url = "https://xkcd.com/{$randomComicID}/info.0.json";
    $comicData = @file_get_contents($url);
    if ($comicData === false) {
        return '<h2>XKCD Comic</h2><p>Could not fetch XKCD comic at this time.</p>';
    }
    $comic = json_decode($comicData, true);
    if ($comic && isset($comic['img'])) {
        $img = htmlspecialchars($comic['img']);
        // The unsubscribe link will be replaced in sendXKCDUpdatesToSubscribers
        return '<h2>XKCD Comic</h2>
                <img src="' . $img . '" alt="XKCD Comic">
                <p><a href="#" id="unsubscribe-button">Unsubscribe</a></p>';
    }
    return '<h2>XKCD Comic</h2><p>Could not fetch XKCD comic at this time.</p>';
}

function sendXKCDUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$emails) return;

    foreach ($emails as $email) {
        $comicHTML = fetchAndFormatXKCDData();
        // Build unsubscribe link
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
        $dir = dirname($_SERVER['PHP_SELF'] ?? '/src/cron.php');
        $unsubscribeLink = "http://$host$dir/unsubscribe.php?email=" . urlencode($email);
        // Replace the # in the unsubscribe link
        $comicHTML = str_replace('href="#"', 'href="' . $unsubscribeLink . '"', $comicHTML);

        $subject = "Your XKCD Comic";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: no-reply@example.com\r\n";

        mail($email, $subject, $comicHTML, $headers);
    }
}
?>