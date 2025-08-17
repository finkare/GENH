<?php
include 'partials/header.php';
require_once __DIR__ . '/../includes/database.php';

// Helper function for handling file uploads for projects
function handle_project_upload($file_key, $upload_sub_dir) {
    global $errors;

    if (!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] !== UPLOAD_ERR_OK) {
        if ($_FILES[$file_key]['error'] === UPLOAD_ERR_NO_FILE) {
            return null; // No file was uploaded, which is okay for optional fields
        }
        $errors[] = "File upload error for {$file_key}: " . $_FILES[$file_key]['error'];
        return false;
    }

    $file = $_FILES[$file_key];
    $upload_path = UPLOADS_PATH . '/' . $upload_sub_dir;

    // Ensure the specific uploads directory exists
    if (!is_dir($upload_path)) {
        if (!mkdir($upload_path, 0755, true)) {
            $errors[] = "Could not create uploads directory: {$upload_sub_dir}";
            return false;
        }
    }

    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = uniqid('', true) . '.' . $file_ext;
    $destination = $upload_path . '/' . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $new_filename; // Return just the filename
    } else {
        $errors[] = "Failed to move uploaded file: {$file['name']}";
        return false;
    }
}


$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve text input data
    $title = trim($_POST['title'] ?? '');
    $cast = trim($_POST['cast'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $returns_range = trim($_POST['returns_range'] ?? '');
    $tenure_months = filter_input(INPUT_POST, 'tenure_months', FILTER_VALIDATE_INT);
    $min_investment = filter_input(INPUT_POST, 'min_investment', FILTER_VALIDATE_FLOAT);
    $management_fee = filter_input(INPUT_POST, 'management_fee', FILTER_VALIDATE_FLOAT);
    $is_active = filter_input(INPUT_POST, 'is_active', FILTER_VALIDATE_INT);
    $trailer_type = $_POST['trailer_type'] ?? 'file';

    // --- Validation for text inputs ---
    if (empty($title)) $errors[] = "Title is required.";
    if (empty($cast)) $errors[] = "Cast is required.";
    // ... other text validations ...

    // --- Handle File Uploads and Trailer Source ---
    $poster_image_filename = handle_project_upload('poster_image', 'posters');

    $trailer_source = null;
    if ($trailer_type === 'file') {
        $trailer_source = handle_project_upload('trailer_file', 'trailers');
    } else { // 'youtube'
        $trailer_source = filter_input(INPUT_POST, 'trailer_source_youtube', FILTER_VALIDATE_URL);
        if (empty($trailer_source)) {
            $errors[] = "A valid YouTube URL is required when YouTube URL is selected.";
        }
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare(
            "INSERT INTO projects (title, cast, description, returns_range, tenure_months, min_investment, management_fee, trailer_type, trailer_source, poster_image_path, is_active)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("ssssiddsssi",
            $title, $cast, $description, $returns_range, $tenure_months,
            $min_investment, $management_fee, $trailer_type, $trailer_source, $poster_image_filename, $is_active
        );

        if ($stmt->execute()) {
            $success_message = "Project added successfully! <a href='manage_projects.php'>View all projects</a>.";
        } else {
            $errors[] = "Database error: Could not add project. " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<div class="page-header">
    <h2>Add New Project</h2>
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


<form action="add_project.php" method="post" class="admin-form" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" required>
    </div>

    <div class="form-group">
        <label for="cast">Cast</label>
        <input type="text" name="cast" id="cast" required>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea name="description" id="description" rows="4" required></textarea>
    </div>

    <div class="form-group-flex">
        <div class="form-group">
            <label for="returns_range">Returns Range (e.g., "5% - 10%")</label>
            <input type="text" name="returns_range" id="returns_range" required>
        </div>
        <div class="form-group">
            <label for="tenure_months">Tenure (Months)</label>
            <input type="number" name="tenure_months" id="tenure_months" required>
        </div>
    </div>

    <div class="form-group-flex">
        <div class="form-group">
            <label for="min_investment">Minimum Investment (₹)</label>
            <input type="number" name="min_investment" id="min_investment" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="management_fee">Asset Management Fee (₹)</label>
            <input type="number" name="management_fee" id="management_fee" step="0.01" required>
        </div>
    </div>

    <div class="form-group">
        <label for="poster_image">Poster Image (Upload)</label>
        <input type="file" name="poster_image" id="poster_image" accept="image/jpeg,image/png,image/webp">
    </div>

    <div class="form-group">
        <label>Trailer Type</label>
        <div class="radio-group">
            <label>
                <input type="radio" name="trailer_type" value="file" checked> Direct Upload
            </label>
            <label>
                <input type="radio" name="trailer_type" value="youtube"> YouTube URL
            </label>
        </div>
    </div>

    <div id="trailer-file-container" class="form-group">
        <label for="trailer_file">Trailer Video File</label>
        <input type="file" name="trailer_file" id="trailer_file" accept="video/mp4,video/webm">
    </div>

    <div id="trailer-youtube-container" class="form-group" style="display: none;">
        <label for="trailer_source_youtube">YouTube URL</label>
        <input type="url" name="trailer_source_youtube" id="trailer_source_youtube" placeholder="e.g., https://www.youtube.com/watch?v=...">
    </div>

    <div class="form-group">
        <label for="is_active">Status</label>
        <select name="is_active" id="is_active">
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
    </div>

    <div class="form-group">
        <button type="submit" class="btn">Save Project</button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trailerTypeRadios = document.querySelectorAll('input[name="trailer_type"]');
    const fileContainer = document.getElementById('trailer-file-container');
    const youtubeContainer = document.getElementById('trailer-youtube-container');
    const fileInput = document.getElementById('trailer_file');
    const youtubeInput = document.getElementById('trailer_source_youtube');

    function toggleTrailerInputs() {
        if (document.querySelector('input[name="trailer_type"]:checked').value === 'youtube') {
            youtubeContainer.style.display = 'block';
            fileInput.required = false;
        } else {
            youtubeContainer.style.display = 'none';
            fileInput.required = false; // Not strictly required on add
        }
    }

    trailerTypeRadios.forEach(radio => radio.addEventListener('change', toggleTrailerInputs));

    // Initial check on page load
    toggleTrailerInputs();
});
</script>

<?php include 'partials/footer.php'; ?>
