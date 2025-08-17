<?php
include 'partials/header.php';
require_once __DIR__ . '/../includes/database.php';

// Get the project ID from the URL
$project_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$project_id) {
    // Redirect if no valid ID is provided
    header('Location: manage_projects.php');
    exit();
}

// Fetch the project data from the database
$stmt = $mysqli->prepare("SELECT * FROM projects WHERE id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();
$stmt->close();

if (!$project) {
    // Redirect if project not found
    echo "Project not found.";
    exit(); // Or a more graceful error page
}

// The backend logic for handling the form submission will be added in the next plan step.
$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input data
    $title = trim($_POST['title'] ?? '');
    $cast = trim($_POST['cast'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $returns_range = trim($_POST['returns_range'] ?? '');
    $tenure_months = filter_input(INPUT_POST, 'tenure_months', FILTER_VALIDATE_INT);
    $min_investment = filter_input(INPUT_POST, 'min_investment', FILTER_VALIDATE_FLOAT);
    $management_fee = filter_input(INPUT_POST, 'management_fee', FILTER_VALIDATE_FLOAT);
    $trailer_url = filter_input(INPUT_POST, 'trailer_url', FILTER_VALIDATE_URL);
    $poster_image_url = filter_input(INPUT_POST, 'poster_image_url', FILTER_VALIDATE_URL);
    $is_active = filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT);

    // --- Validation ---
    if (empty($title)) $errors[] = "Title is required.";
    if (empty($cast)) $errors[] = "Cast is required.";
    if (empty($description)) $errors[] = "Description is required.";
    if ($tenure_months === false || $tenure_months <= 0) $errors[] = "Valid tenure in months is required.";
    if ($min_investment === false || $min_investment <= 0) $errors[] = "Valid minimum investment is required.";
    if ($management_fee === false || $management_fee < 0) $errors[] = "Valid management fee is required.";

    if (empty($errors)) {
        $stmt = $mysqli->prepare(
            "UPDATE projects SET title = ?, cast = ?, description = ?, returns_range = ?, tenure_months = ?,
             min_investment = ?, management_fee = ?, trailer_url = ?, poster_image_url = ?, is_active = ?
             WHERE id = ?"
        );
        $stmt->bind_param("ssssiddssii",
            $title, $cast, $description, $returns_range, $tenure_months,
            $min_investment, $management_fee, $trailer_url, $poster_image_url, $is_active,
            $project_id
        );

        if ($stmt->execute()) {
            $success_message = "Project updated successfully! <a href='manage_projects.php'>Back to projects list</a>.";
            // Refresh project data to show updated values in the form
            $project['title'] = $title;
            $project['cast'] = $cast;
            // ... and so on for all fields
        } else {
            $errors[] = "Database error: Could not update project. " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<div class="page-header">
    <h2>Edit Project: <?php echo htmlspecialchars($project['title']); ?></h2>
    <a href="manage_projects.php" class="btn">Back to Projects List</a>
</div>

<form action="edit_project.php?id=<?php echo $project['id']; ?>" method="post" class="admin-form">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($project['title']); ?>" required>
    </div>

    <div class="form-group">
        <label for="cast">Cast</label>
        <input type="text" name="cast" id="cast" value="<?php echo htmlspecialchars($project['cast']); ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" rows="4" required><?php echo htmlspecialchars($project['description']); ?></textarea>
    </div>

    <div class="form-group-flex">
        <div class="form-group">
            <label for="returns_range">Returns Range (e.g., "5% - 10%")</label>
            <input type="text" name="returns_range" id="returns_range" value="<?php echo htmlspecialchars($project['returns_range']); ?>" required>
        </div>
        <div class="form-group">
            <label for="tenure_months">Tenure (Months)</label>
            <input type="number" name="tenure_months" id="tenure_months" value="<?php echo htmlspecialchars($project['tenure_months']); ?>" required>
        </div>
    </div>

    <div class="form-group-flex">
        <div class="form-group">
            <label for="min_investment">Minimum Investment (₹)</label>
            <input type="number" name="min_investment" id="min_investment" step="0.01" value="<?php echo htmlspecialchars($project['min_investment']); ?>" required>
        </div>
        <div class="form-group">
            <label for="management_fee">Asset Management Fee (₹)</label>
            <input type="number" name="management_fee" id="management_fee" step="0.01" value="<?php echo htmlspecialchars($project['management_fee']); ?>" required>
        </div>
    </div>

    <div class="form-group">
        <label for="trailer_url">Trailer URL (Optional)</label>
        <input type="url" name="trailer_url" id="trailer_url" value="<?php echo htmlspecialchars($project['trailer_url']); ?>">
    </div>

    <div class="form-group">
        <label for="poster_image_url">Poster Image URL (Optional)</label>
        <input type="url" name="poster_image_url" id="poster_image_url" value="<?php echo htmlspecialchars($project['poster_image_url']); ?>">
    </div>

    <div class="form-group">
        <label for="is_active">Status</label>
        <select name="is_active" id="is_active">
            <option value="1" <?php echo $project['is_active'] ? 'selected' : ''; ?>>Active</option>
            <option value="0" <?php echo !$project['is_active'] ? 'selected' : ''; ?>>Inactive</option>
        </select>
    </div>

    <div class="form-group">
        <button type="submit" class="btn">Update Project</button>
    </div>
</form>

<?php include 'partials/footer.php'; ?>
