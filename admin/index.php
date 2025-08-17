<?php include 'partials/header.php'; ?>

<h2>Admin Dashboard</h2>
<p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['admin_full_name']); ?></strong>!</p>
<p>This is the central hub for managing the Flix9 Hub website. From here you can manage investment projects, view registered users, and configure site settings.</p>
<p>Please use the navigation links above to get started.</p>

<div class="dashboard-stats">
    <div class="stat-card">
        <h3>Total Projects</h3>
        <p class="stat-number">0</p>
    </div>
    <div class="stat-card">
        <h3>Total Users</h3>
        <p class="stat-number">0</p>
    </div>
    <div class="stat-card">
        <h3>Total Investments</h3>
        <p class="stat-number">0</p>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
