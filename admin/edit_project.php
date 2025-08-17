<?php
include 'partials/header.php';
require_once __DIR__ . '/../includes/database.php';
require_once 'add_project.php'; // Reuse the helper function

// Get the project ID from the URL
$project_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$project_id) {
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
    echo "Project not found.";
    exit();
}

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input data
    $title = trim($_POST['title'] ?? '');
    $cast = trim($_POST['cast'] ?? '');
    // ... all other text fields ...
    $is_active = filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT);
    $trailer_type = $_POST['trailer_type'] ?? 'file';

    // --- Handle Poster Upload ---
    $poster_image_filename = $project['poster_image_path']; // Keep old if no new one
    if (isset($_FILES['poster_image']) && $_FILES['poster_image']['error'] === UPLOAD_ERR_OK) {
        $new_poster = handle_project_upload('poster_image', 'posters');
        if ($new_poster) {
            // Delete old poster if it exists
            if ($poster_image_filename && file_exists(UPLOADS_PATH . '/posters/' . $poster_image_filename)) {
                unlink(UPLOADS_PATH . '/posters/' . $poster_image_filename);
            }
            $poster_image_filename = $new_poster;
        }
    }

    // --- Handle Trailer Upload/Link ---
    $trailer_source = $project['trailer_source']; // Keep old
    if ($trailer_type === 'file') {
        if (isset($_FILES['trailer_file']) && $_FILES['trailer_file']['error'] === UPLOAD_ERR_OK) {
            $new_trailer = handle_project_upload('trailer_file', 'trailers');
            if ($new_trailer) {
                // Delete old trailer if it was a file
                if ($project['trailer_type'] === 'file' && $trailer_source && file_exists(UPLOADS_PATH . '/trailers/' . $trailer_source)) {
                    unlink(UPLOADS_PATH . '/trailers/' . $trailer_source);
                }
                $trailer_source = $new_trailer;
            }
        }
    } else { // youtube
        $trailer_source = filter_input(INPUT_POST, 'trailer_source_youtube', FILTER_VALIDATE_URL);
        // Delete old trailer file if switching from file to youtube
        if ($project['trailer_type'] === 'file' && $project['trailer_source'] && file_exists(UPLOADS_PATH . '/trailers/' . $project['trailer_source'])) {
            unlink(UPLOADS_PATH . '/trailers/' . $project['trailer_source']);
        }
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare(
            "UPDATE projects SET title = ?, cast = ?, description = ?, returns_range = ?, tenure_months = ?,
             min_investment = ?, management_fee = ?, trailer_type = ?, trailer_source = ?, poster_image_path = ?, is_active = ?
             WHERE id = ?"
        );
        // Bind all params...
        $stmt->bind_param("ssssiddsssii",
            $title, $cast, $description, $returns_range, $tenure_months,
            $min_investment, $management_fee, $trailer_type, $trailer_source, $poster_image_filename, $is_active,
            $project_id
        );

        if ($stmt->execute()) {
            $success_message = "Project updated successfully!";
            // Refresh project data to show updated values in the form
            $result = $mysqli->query("SELECT * FROM projects WHERE id = $project_id");
            $project = $result->fetch_assoc();
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

<?php if (!empty($errors)): ?>
    <div class="errors">
        <strong>Please fix the following errors:</strong>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success_message): ?>
    <div class="flash-message">
        <p><?php echo $success_message; ?></p>
    </div>
<?php endif; ?>

<form action="edit_project.php?id=<?php echo $project['id']; ?>" method="post" class="admin-form" enctype="multipart/form-data">
    <!-- The form fields are the same as add_project.php, just pre-populated -->
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
        <label for="poster_image">Poster Image (Upload)</label>
        <input type="file" name="poster_image" id="poster_image" accept="image/jpeg,image/png,image/webp">
        <?php if (!empty($project['poster_image_path'])): ?>
            <small>Current poster: <?php echo htmlspecialchars($project['poster_image_path']); ?>. Upload a new file to replace it.</small>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>Trailer Type</label>
        <div class="radio-group">
            <label>
                <input type="radio" name="trailer_type" value="file" <?php echo ($project['trailer_type'] == 'file') ? 'checked' : ''; ?>> Direct Upload
            </label>
            <label>
                <input type="radio" name="trailer_type" value="youtube" <?php echo ($project['trailer_type'] == 'youtube') ? 'checked' : ''; ?>> YouTube URL
            </label>
        </div>
    </div>

    <div id="trailer-file-container" class="form-group">
        <label for="trailer_file">Trailer Video File</label>
        <input type="file" name="trailer_file" id="trailer_file" accept="video/mp4,video/webm">
        <?php if ($project['trailer_type'] == 'file' && !empty($project['trailer_source'])): ?>
            <small>Current file: <?php echo htmlspecialchars($project['trailer_source']); ?>. Upload a new file to replace it.</small>
        <?php endif; ?>
    </div>

    <div id="trailer-youtube-container" class="form-group" style="display: none;">
        <label for="trailer_source_youtube">YouTube URL</label>
        <input type="url" name="trailer_source_youtube" id="trailer_source_youtube" value="<?php echo ($project['trailer_type'] == 'youtube') ? htmlspecialchars($project['trailer_source']) : ''; ?>" placeholder="e.g., https://www.youtube.com/watch?v=...">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trailerTypeRadios = document.querySelectorAll('input[name="trailer_type"]');
    const fileContainer = document.getElementById('trailer-file-container');
    const youtubeContainer = document.getElementById('trailer-youtube-container');

    function toggleTrailerInputs() {
        if (document.querySelector('input[name="trailer_type"]:checked').value === 'youtube') {
            youtubeContainer.style.display = 'block';
            fileContainer.style.display = 'none';
        } else {
            youtubeContainer.style.display = 'none';
            fileContainer.style.display = 'block';
        }
    }

    trailerTypeRadios.forEach(radio => radio.addEventListener('change', toggleTrailerInputs));

    // Initial check on page load
    toggleTrailerInputs();
});
</script>

<?php include 'partials/footer.php'; ?>
