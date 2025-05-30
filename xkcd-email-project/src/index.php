<?php
require_once __DIR__ . '/functions.php';

$message = '';

// Handle email submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && !isset($_POST['verification_code'])) {
    $email = trim($_POST['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $code = generateVerificationCode();
        // Store code for this email (simple file-based storage)
        file_put_contents(__DIR__ . '/verification_codes.txt', "$email:$code\n", FILE_APPEND);
        sendVerificationEmail($email, $code);
        $message = "Verification code sent to $email.";
    } else {
        $message = "Invalid email address.";
    }
}

// Handle verification code submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verification_code'])) {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $code = trim($_POST['verification_code']);
    if ($email && $code) {
        if (verifyCode($email, $code)) {
            registerEmail($email);
            removeVerificationCode($email, $code);
            $message = "Email verified and registered successfully!";
        } else {
            $message = "Invalid verification code.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XKCD Email Verification</title>
</head>
<body>
    <h1>XKCD Email Verification</h1>

    <?php if ($message): ?>
        <p><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Email input form (always visible) -->
    <form method="POST" action="">
        <label for="email">Enter your email to receive a verification code:</label>
        <input type="email" name="email" required>
        <button id="submit-email" type="submit">Submit</button>
    </form>

    <!-- Verification code input form (always visible) -->
    <form method="POST" action="">
        <label for="verification_code">Enter the verification code sent to your email:</label>
        <input type="email" name="email" required placeholder="Enter your email">
        <input type="text" name="verification_code" maxlength="6" required placeholder="Verification code">
        <button id="submit-verification" type="submit">Verify</button>
    </form>
</body>
</html>