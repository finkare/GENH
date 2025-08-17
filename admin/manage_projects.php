<?php
include 'partials/header.php';
require_once __DIR__ . '/../includes/database.php';

// Fetch all projects to display in the table
$projects = [];
$result = $mysqli->query("SELECT id, title, cast, tenure_months, min_investment, is_active FROM projects ORDER BY created_at DESC");
if ($result) {
    $projects = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
?>

<?php
// Display a flash message if one is set in the session
if (isset($_SESSION['flash_message'])) {
    echo '<div class="flash-message">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
    unset($_SESSION['flash_message']); // Clear the message so it doesn't show again
}
?>

<div class="page-header">
    <h2>Manage Projects</h2>
    <a href="add_project.php" class="btn">Add New Project</a>
</div>

<div class="data-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Cast</th>
                <th>Tenure</th>
                <th>Min. Investment</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($projects)): ?>
                <tr>
                    <td colspan="6">No projects found. Click "Add New Project" to get started.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($project['title']); ?></td>
                        <td><?php echo htmlspecialchars($project['cast']); ?></td>
                        <td><?php echo htmlspecialchars($project['tenure_months']); ?> months</td>
                        <td>₹<?php echo number_format($project['min_investment']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $project['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $project['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <a href="edit_project.php?id=<?php echo $project['id']; ?>" class="btn-edit">Edit</a>
                            <a href="delete_project.php?id=<?php echo $project['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this project? This action cannot be undone.');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'partials/footer.php'; ?>
