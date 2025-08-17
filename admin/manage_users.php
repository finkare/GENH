<?php
include 'partials/header.php';
require_once __DIR__ . '/../includes/database.php';

// Fetch all users to display in the table
$users = [];
$result = $mysqli->query("SELECT id, full_name, email, country, is_verified, is_active FROM users ORDER BY created_at DESC");
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
?>

<div class="page-header">
    <h2>Manage Users</h2>
</div>

<?php
// Display a flash message if one is set from the toggle status action
if (isset($_SESSION['flash_message'])) {
    echo '<div class="flash-message">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
    unset($_SESSION['flash_message']);
}
?>

<div class="data-table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Country</th>
                <th>Email Verified</th>
                <th>Investment Active</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6">No users found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['country']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $user['is_verified'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $user['is_verified'] ? 'Yes' : 'No'; ?>
                            </span>
                        </td>
                        <td>
                             <span class="status-badge <?php echo $user['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td class="actions">
                            <form action="toggle_user_status.php" method="post" style="display:inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="<?php echo $user['is_active'] ? 'btn-delete' : 'btn-edit'; ?>" title="Toggle investment activation status">
                                    <?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'partials/footer.php'; ?>
