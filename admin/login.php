<?php
// Admin files use the main config and database connection.
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/database.php';

$errors = [];

// If an admin is already logged in, redirect them to the admin dashboard.
// We use a separate session variable to distinguish from user sessions.
if (isset($_SESSION['admin_user_id'])) {
    header('Location: index.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id, full_name, password, is_admin FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            // User and password are correct. Now, check if they are an admin.
            if ($user['is_admin']) {
                // --- Admin Login Successful ---
                // Regenerate session ID for security.
                session_regenerate_id(true);

                // Store admin-specific session data.
                $_SESSION['admin_user_id'] = $user['id'];
                $_SESSION['admin_full_name'] = $user['full_name'];

                // Redirect to the admin dashboard.
                header('Location: index.php');
                exit();
            } else {
                // It's a valid user, but not an admin.
                $errors[] = "You do not have permission to access this area.";
            }
        } else {
            // Invalid credentials (user not found or password incorrect).
            $errors[] = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Flix9 Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h1>Flix9 Hub Admin</h1>
        <p>Please log in to continue.</p>
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
