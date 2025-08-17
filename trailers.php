<?php
require_once 'includes/database.php';

// Fetch all active projects that have a trailer source, ordered by the most recent first.
$projects = [];
$result = $mysqli->query("SELECT title, trailer_type, trailer_source, poster_image_path FROM projects WHERE is_active = TRUE AND trailer_source IS NOT NULL AND trailer_source != '' ORDER BY created_at DESC");
if ($result) {
    $projects = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
?>
<?php include 'includes/header.php'; ?>

<main>
    <div class="content-wrapper">
        <h1>Recent Trailers</h1>
        <p>Watch the latest trailers from projects on Flix9 Hub.</p>

        <div class="trailers-grid" style="margin-top: 30px;">
            <?php if (empty($projects)): ?>
                <div class="notice">
                    <p>No trailers are available at the moment. Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="trailer-item">
                        <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                        <div class="video-container">
                             <?php if ($project['trailer_type'] === 'youtube'):
                                $youtube_id = '';
                                if (preg_match('/(v=|\/v\/|youtu\.be\/|embed\/|\/watch\?v=|\&v=)([^#\&\?]*).*/', $project['trailer_source'], $matches)) {
                                    $youtube_id = $matches[2];
                                }
                                ?>
                                <?php if ($youtube_id): ?>
                                    <div class="video-container-yt">
                                        <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    </div>
                                <?php else: ?>
                                    <p>Invalid YouTube URL provided.</p>
                                <?php endif; ?>
                            <?php else: // 'file' type ?>
                                <video controls poster="<?php echo !empty($project['poster_image_path']) ? 'uploads/posters/' . htmlspecialchars($project['poster_image_path']) : 'https://via.placeholder.com/400x225.png?text=No+Poster'; ?>">
                                    <source src="<?php echo 'uploads/trailers/' . htmlspecialchars($project['trailer_source']); ?>" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            <?php endif; ?>
                        </div>
                        <div class="investment-snippet">
                            <p class="hot-deal">🎬 Hot Deal from Velan Productions (WEB) 🔥 Limited Ticket Size – Grab Yours Before It’s Gone!</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
