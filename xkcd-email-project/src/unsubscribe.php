<?php
// This file handles the unsubscription process for users.

require_once __DIR__ . '/functions.php';

$message = '';

// Step 1: Send code for unsubscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsubscribe_email']) && !isset($_POST['verification_code'])) {
    $unsubscribe_email = trim($_POST['unsubscribe_email']);
    if (filter_var($unsubscribe_email, FILTER_VALIDATE_EMAIL)) {
        $code = generateVerificationCode();
        file_put_contents(__DIR__ . '/verification_codes.txt', "$unsubscribe_email:$code\n", FILE_APPEND);
        sendUnsubscribeVerificationEmail($unsubscribe_email, $code);
        $message = "Verification code sent to $unsubscribe_email. Please check your email and enter the code below to confirm unsubscription.";
    } else {
        $message = "Invalid email address.";
    }
}

// Step 2: Handle verification code submission for unsubscription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unsubscribe_email']) && isset($_POST['verification_code'])) {
    $unsubscribe_email = trim($_POST['unsubscribe_email']);
    $verification_code = trim($_POST['verification_code']);
    if ($unsubscribe_email && $verification_code) {
        if (verifyCode($unsubscribe_email, $verification_code)) {
            unsubscribeEmail($unsubscribe_email);
            removeVerificationCode($unsubscribe_email, $verification_code);
            $message = "You have been successfully unsubscribed.";
        } else {
            $message = "Invalid verification code for this email.";
        }
    } else {
        $message = "Please provide both email and verification code.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unsubscribe</title>
</head>
<body>
    <h1>Unsubscribe from XKCD Comics</h1>
    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Unsubscribe request form (always visible) -->
    <form method="POST" action="">
        <input type="email" name="unsubscribe_email" required>
        <button type="submit" id="submit-unsubscribe">Unsubscribe</button>
    </form>

    <!-- Verification code form (always visible) -->
    <form method="POST" action="">
        <input type="email" name="unsubscribe_email" required placeholder="Enter your email">
        <input type="text" name="verification_code" maxlength="6" required placeholder="Verification code">
        <button type="submit" id="submit-verification">Verify</button>
    </form>
</body>
</html>