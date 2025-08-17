<?php
require_once 'includes/database.php';

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect_url={$redirect_url}");
    exit();
}

// --- Project Validation ---
$project_id = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);
if (!$project_id) {
    header('Location: investments.php');
    exit();
}

$stmt = $mysqli->prepare("SELECT id, title, min_investment FROM projects WHERE id = ? AND is_active = TRUE");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

if (!$project) {
    header('Location: investments.php');
    exit();
}

$errors = [];
$success_message = '';

// --- Form Submission Logic: Simulate Investment ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $investment_amount = filter_input(INPUT_POST, 'investment_amount', FILTER_VALIDATE_FLOAT);

    // --- Validation ---
    if ($investment_amount === false || $investment_amount <= 0) {
        $errors[] = "Please enter a valid investment amount.";
    } elseif ($investment_amount < $project['min_investment']) {
        $errors[] = "Investment amount must be at least ₹" . number_format($project['min_investment']) . ".";
    }

    // --- If validation passes, insert the investment ---
    if (empty($errors)) {
        $user_id = $_SESSION['user_id'];
        $insert_stmt = $mysqli->prepare("INSERT INTO user_investments (user_id, project_id, investment_amount) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iid", $user_id, $project_id, $investment_amount);

        if ($insert_stmt->execute()) {
            $success_message = "Congratulations! Your investment of ₹" . number_format($investment_amount) . " in '" . htmlspecialchars($project['title']) . "' has been recorded successfully.";
        } else {
            $errors[] = "Database error: Could not record your investment. Please try again.";
            // For debugging: error_log('Investment insert error: ' . $insert_stmt->error);
        }
        $insert_stmt->close();
    }
}

?>
<?php include 'includes/header.php'; ?>

<main>
    <div class="content-wrapper">
        <h1>Invest in: <?php echo htmlspecialchars($project['title']); ?></h1>

        <?php if (!$user_is_active): ?>
            <div class="notice">
                <h3>Account Pending Approval</h3>
                <p>Thank you for your interest in investing. Your account must be manually approved by an administrator before you can proceed.</p>
                <p>This step is required for all new investors. If you have completed the necessary off-platform discussions, please allow some time for an admin to activate your account. If you believe this is an error, please contact support.</p>
                <p><a href="dashboard.php" class="btn-secondary">Back to Dashboard</a></p>
            </div>
        <?php else: ?>
            <p>This form simulates the final step of the investment process. In a real application, this would follow the agreement and payment gateway steps.</p>

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
                    <p><a href="dashboard.php" class="btn-primary" style="text-decoration:none;display:inline-block;margin-top:10px;padding:10px 20px;">View My Dashboard</a></p>
                </div>
            <?php else: ?>
                <form action="invest.php?project_id=<?php echo $project['id']; ?>" method="post" class="styled-form">
                    <div class="form-group">
                        <label for="investment_amount">Investment Amount (INR)</label>
                        <input type="number" id="investment_amount" name="investment_amount"
                               min="<?php echo htmlspecialchars($project['min_investment']); ?>"
                               step="1000"
                               placeholder="Minimum: ₹<?php echo number_format($project['min_investment']); ?>"
                               required>
                        <small>Minimum investment for this project is ₹<?php echo number_format($project['min_investment']); ?></small>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-primary">Confirm Investment</button>
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
