<?php
require_once 'includes/database.php';

// --- Authentication Check ---
// If the user is not logged in, they cannot invest.
// We redirect them to the login page. We can also pass a `redirect_url`
// so the login page can send them back here after a successful login.
if (!isset($_SESSION['user_id'])) {
    // Get the current URL to use for the redirect
    $redirect_url = urlencode($_SERVER['REQUEST_URI']);
    header("Location: login.php?redirect_url={$redirect_url}");
    exit();
}

// Get the project ID from the URL query string.
$project_id = filter_input(INPUT_GET, 'project_id', FILTER_VALIDATE_INT);

if (!$project_id) {
    // If no valid project ID is provided, redirect to the main investments page.
    header('Location: investments.php');
    exit();
}

// Fetch project details from the database to ensure it's a valid project.
$stmt = $mysqli->prepare("SELECT title FROM projects WHERE id = ? AND is_active = TRUE");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

if (!$project) {
    // If the project doesn't exist or isn't active, redirect away.
    header('Location: investments.php');
    exit();
}

?>
<?php include 'includes/header.php'; ?>

<main>
    <div class="content-wrapper">
        <h1>Invest in: <?php echo htmlspecialchars($project['title']); ?></h1>
        <p>This is the first step of the investment process. The next steps will involve reviewing the investment agreement and proceeding to payment.</p>

        <div class="notice" style="margin-top: 30px;">
            <p><strong>Next Steps (Under Development):</strong></p>
            <ul style="text-align: left; max-width: 400px; margin: 15px auto;">
                <li>Review and sign the Investment Agreement.</li>
                <li>Proceed to payment via our secure gateway.</li>
            </ul>
            <p>This functionality is currently under construction. Please check back soon!</p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
