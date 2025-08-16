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
                <a href="#" class="btn-secondary disabled">View My Portfolio</a>
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
    </div>
</main>

<?php include 'includes/footer.php'; ?>
