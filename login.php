<?php
require_once 'includes/database.php'; // This also starts the session via config.php

$errors = [];

// If a user is already logged in, they shouldn't be on the login page.
// Redirect them to their dashboard.
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // --- Basic Validation ---
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    // --- If initial validation passes, proceed to check credentials ---
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id, full_name, password, is_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            // User with that email exists. Now, verify the password.
            if (password_verify($password, $user['password'])) {
                // Password is correct. Now, check if the account has been verified via OTP.
                if ($user['is_verified']) {
                    // --- Login Successful ---
                    // Regenerate the session ID to prevent session fixation attacks.
                    session_regenerate_id(true);

                    // Store essential user data in the session.
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];

                    // Redirect the user to their dashboard.
                    header('Location: dashboard.php');
                    exit();
                } else {
                    // Account exists but is not verified.
                    $errors[] = 'Your account is not yet verified. Please check your email for the OTP or <a href="verify_otp.php?email=' . urlencode($email) . '">click here to verify</a>.';
                }
            } else {
                // Password does not match.
                $errors[] = "The email or password you entered is incorrect.";
            }
        } else {
            // No user found with that email address.
            $errors[] = "The email or password you entered is incorrect.";
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<main>
    <div class="content-wrapper">
        <h1>Login to Your Account</h1>

        <?php if (!empty($errors)): ?>
            <div class="form-errors">
                <strong>Login failed:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; // Allow HTML for the verification link ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post" class="styled-form">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-primary">Login</button>
            </div>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
