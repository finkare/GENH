<?php
require_once 'includes/database.php';

$errors = [];
$success_message = '';

// Get the email from the URL query string. It's essential for identifying the user.
$email = trim($_GET['email'] ?? '');

// If no email is provided in the URL, we cannot proceed. Redirect to registration.
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: register.php');
    exit();
}

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_entered = trim($_POST['otp'] ?? '');

    // Basic validation for the OTP format
    if (empty($otp_entered) || !is_numeric($otp_entered) || strlen($otp_entered) !== 6) {
        $errors[] = "Please enter a valid 6-digit OTP.";
    } else {
        // Find the user by email, ensuring they are not already verified
        $stmt = $mysqli->prepare("SELECT id, otp, otp_expires_at FROM users WHERE email = ? AND is_verified = FALSE");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $errors[] = "No pending verification found for this email address, or the account is already verified.";
        } else {
            // Check if the OTP has expired
            $now = new DateTime();
            $otp_expires = new DateTime($user['otp_expires_at']);

            if ($now > $otp_expires) {
                $errors[] = "The OTP has expired. Please request a new one on the registration page.";
            } elseif ($user['otp'] !== $otp_entered) {
                $errors[] = "The OTP you entered is incorrect. Please try again.";
            } else {
                // --- Success! OTP is correct and valid. ---
                // Update the user's status to verified and clear the OTP fields.
                $update_stmt = $mysqli->prepare("UPDATE users SET is_verified = TRUE, otp = NULL, otp_expires_at = NULL WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                if ($update_stmt->execute()) {
                    $success_message = "Thank you! Your email has been verified successfully. You can now log in.";
                } else {
                    $errors[] = "Database error: Could not verify your account. Please try again later.";
                }
                $update_stmt->close();
            }
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<main>
    <div class="content-wrapper">
        <h1>Verify Your Account</h1>
        <p>An OTP was generated for your account. Please enter the 6-digit code below to complete your registration.</p>
        <p>Verifying account for: <strong><?php echo htmlspecialchars($email); ?></strong></p>

        <?php if (!empty($errors)): ?>
            <div class="form-errors">
                <strong>Please fix the following errors:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="form-success">
                <p><?php echo htmlspecialchars($success_message); ?></p>
                <p><a href="login.php" class="btn-primary" style="text-decoration:none;display:inline-block;margin-top:10px;padding:10px 20px;">Click here to Login</a></p>
            </div>
        <?php else: ?>
            <form action="verify_otp.php?email=<?php echo urlencode($email); ?>" method="post" class="styled-form">
                <div class="form-group">
                    <label for="otp">One-Time Password (OTP)</label>
                    <input type="text" id="otp" name="otp" class="otp-input" required maxlength="6" pattern="\d{6}" title="Enter a 6-digit number" autocomplete="one-time-code">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">Verify Account</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
