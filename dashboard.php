<?php
require_once 'includes/database.php'; // This also starts the session via config.php

// --- Authentication Check ---
// This is the core of protecting the page. If the user is not logged in,
// redirect them to the login page and stop script execution.
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// If the user is logged in, get their name from the session to personalize the page.
$user_name = $_SESSION['full_name'];
$user_id = $_SESSION['user_id'];

// --- Fetch User's Investments ---
$investments = [];
$stmt = $mysqli->prepare(
    "SELECT p.title, ui.investment_amount, ui.investment_date
     FROM user_investments ui
     JOIN projects p ON ui.project_id = p.id
     WHERE ui.user_id = ?
     ORDER BY ui.investment_date DESC"
);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $investments = $result->fetch_all(MYSQLI_ASSOC);
}
$stmt->close();
?>
<?php include 'includes/header.php'; ?>

<main>
    <div class="content-wrapper">
        <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
        <p>This is your investor dashboard. From here, you can view upcoming investment opportunities, manage your current investments, and track your returns.</p>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Upcoming Investments</h2>
                <p>View projects currently open for investment.</p>
                <a href="investments.php" class="btn-secondary">View Projects</a>
            </div>
            <div class="dashboard-card">
                <h2>My Investments</h2>
                <p>Track your portfolio performance and returns.</p>
                <a href="#my-investments-section" class="btn-secondary">View My Portfolio</a>
            </div>
            <div class="dashboard-card">
                <h2>Analytics</h2>
                <p>Visualize your investment growth over time.</p>
                <a href="#" class="btn-secondary disabled">View Analytics</a>
            </div>
             <div class="dashboard-card">
                <h2>Support</h2>
                <p>Need help? Raise a support ticket.</p>
                <a href="#" class="btn-secondary disabled">Get Support</a>
            </div>
        </div>

        <!-- "My Investments" Section -->
        <div id="my-investments-section" class="my-investments">
            <h2>My Investments</h2>
            <?php if (empty($investments)): ?>
                <div class="notice">
                    <p>You have not made any investments yet. <a href="investments.php">Explore projects to get started!</a></p>
                </div>
            <?php else: ?>
                <table class="investments-table">
                    <thead>
                        <tr>
                            <th>Project Title</th>
                            <th>Amount Invested</th>
                            <th>Investment Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($investments as $investment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($investment['title']); ?></td>
                                <td>₹<?php echo number_format($investment['investment_amount'], 2); ?></td>
                                <td><?php echo date('F j, Y', strtotime($investment['investment_date'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
