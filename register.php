<?php
require_once 'includes/database.php';

$errors = [];
$success_message = '';

// Helper function to handle file uploads
function handle_upload($file_key, $upload_path) {
    global $errors;

    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        // ID back is optional, so don't error if it's not present
        if ($file_key === 'id_back' && $_FILES[$file_key]['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        $errors[] = "File upload error for {$file_key}. Please try again.";
        return false;
    }

    $file = $_FILES[$file_key];
    $max_size = 5 * 1024 * 1024; // 5 MB
    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];

    if ($file['size'] > $max_size) {
        $errors[] = "File {$file['name']} is too large. Maximum size is 5 MB.";
        return false;
    }

    if (!in_array($file['type'], $allowed_types)) {
        $errors[] = "Invalid file type for {$file['name']}. Only JPG, PNG, and PDF are allowed.";
        return false;
    }

    // Ensure the uploads directory exists
    if (!is_dir($upload_path)) {
        if (!mkdir($upload_path, 0755, true)) {
            $errors[] = "Could not create uploads directory.";
            return false;
        }
    }

    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid($file_key . '_', true) . '.' . $file_ext;
    $destination = $upload_path . '/' . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $new_filename; // Return the new filename to store in DB
    } else {
        $errors[] = "Failed to move uploaded file {$file['name']}.";
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input data
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $contact_address = trim($_POST['contact_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $country = trim($_POST['country'] ?? '');

    // --- Form Validation ---
    if (empty($full_name)) $errors[] = "Full Name is required.";
    if (empty($contact_address)) $errors[] = "Contact Address is required.";
    if (empty($city)) $errors[] = "City is required.";
    if (empty($state)) $errors[] = "State is required.";
    if (empty($country)) $errors[] = "Country is required.";

    if (empty($email)) {
        $errors[] = "Email Address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Email Address format.";
    } else {
        // Check if email already exists
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "An account with this email address already exists.";
        }
        $stmt->close();
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    // --- File Validation and Upload ---
    $id_front_filename = null;
    $id_back_filename = null;
    if (empty($errors)) {
        $id_front_filename = handle_upload('id_front', UPLOADS_PATH);
        $id_back_filename = handle_upload('id_back', UPLOADS_PATH);
    }

    // --- If all validation passes, proceed to final registration steps ---
    if (empty($errors)) {
        // 1. Hash the password for secure storage
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            $errors[] = "Failed to process password. Please try again.";
        }

        // 2. Generate a 6-digit OTP and its expiration time (e.g., 15 minutes)
        $otp = random_int(100000, 999999);
        $otp_expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        // 3. Insert the new user record into the database
        if (empty($errors)) {
            $stmt = $mysqli->prepare(
                "INSERT INTO users (full_name, email, password, contact_address, city, state, country, id_front_path, id_back_path, otp, otp_expires_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            // 'sssssssssis' -> 9 strings, 1 integer (otp), 1 string
            $stmt->bind_param("sssssssssss",
                $full_name, $email, $hashed_password, $contact_address, $city,
                $state, $country, $id_front_filename, $id_back_filename, $otp, $otp_expires_at
            );

            if ($stmt->execute()) {
                // 4. Send OTP Email (Placeholder)
                // In a real application with PHPMailer, the code would be here.
                // This is commented out as Composer is not available in the environment.
                // $email_sent = send_otp_email($email, $otp);
                // if (!$email_sent) {
                //     $errors[] = "Could not send OTP email. Please contact support.";
                // }

                // 5. If successful, show a success message and hide the form.
                // A real implementation would redirect to an OTP verification page.
                // header('Location: verify_otp.php?email=' . urlencode($email));
                // exit();
                $success_message = "Registration successful! An OTP has been generated (but not sent due to environment limitations). Please check your email and proceed to the verification page.";

            } else {
                $errors[] = "Database error: Failed to register user. Please try again later.";
                // For debugging: error_log('Register user DB error: ' . $stmt->error);
            }
            $stmt->close();
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<main>
    <div class="content-wrapper">
        <h1>Create Your Investor Account</h1>
        <p>Join Flix9 Hub and turn your passion for cinema into profit.</p>

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
            </div>
        <?php else: ?>
            <form action="register.php" method="post" enctype="multipart/form-data" class="styled-form">
                <!-- Form fields remain the same as before -->
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" minlength="8" required>
                </div>
                <div class="form-group">
                    <label for="contact_address">Contact Address</label>
                    <textarea id="contact_address" name="contact_address" rows="3" required><?php echo htmlspecialchars($_POST['contact_address'] ?? ''); ?></textarea>
                </div>
                <div class="form-group-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" required value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state" required value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country" required value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>">
                </div>

                <fieldset>
                    <legend>Identity Verification</legend>
                    <p>Please upload a clear copy of your National ID card (front and back) or your Passport.</p>
                    <div class="form-group">
                        <label for="id_front">National ID (Front) / Passport</label>
                        <input type="file" id="id_front" name="id_front" accept="image/*,.pdf" required>
                    </div>
                    <div class="form-group">
                        <label for="id_back">National ID (Back)</label>
                        <input type="file" id="id_back" name="id_back" accept="image/*,.pdf">
                        <small>Leave empty if uploading a passport.</small>
                    </div>
                </fieldset>

                <div class="form-group">
                    <button type="submit" class="btn-primary">Register</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
