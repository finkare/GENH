<?php
require_once 'includes/database.php';

// Fetch all active projects from the database, ordered by the most recent first.
$projects = [];
$result = $mysqli->query("SELECT * FROM projects WHERE is_active = TRUE ORDER BY created_at DESC");
if ($result) {
    $projects = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
?>
<?php include 'includes/header.php'; ?>

<main>
    <div class="content-wrapper">
        <h1>Upcoming Projects</h1>
        <p>Browse our curated list of high-potential film projects open for investment.</p>

        <div class="projects-list">
            <?php if (empty($projects)): ?>
                <div class="notice">
                    <p>There are no active investment opportunities at the moment. Please check back later.</p>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="project-item">
                        <div class="project-trailer">
                            <video controls poster="https://via.placeholder.com/560x315.png?text=<?php echo urlencode($project['title']); ?>+Trailer">
                                <?php if (!empty($project['trailer_url'])): ?>
                                    <!-- <source src="<?php echo htmlspecialchars($project['trailer_url']); ?>" type="video/mp4"> -->
                                <?php endif; ?>
                                Your browser does not support the video tag.
                            </video>
                        </div>
                        <div class="project-details">
                            <h2>🎥 Title: <?php echo htmlspecialchars($project['title']); ?></h2>
                            <p><strong>🌟 Cast:</strong> <?php echo htmlspecialchars($project['cast']); ?></p>
                            <p><strong>📺 <?php echo htmlspecialchars($project['ott_rights']); ?></strong></p>

                            <ul class="investment-terms">
                                <li>💰 Earn <strong><?php echo htmlspecialchars($project['returns_range']); ?></strong></li>
                                <li>⏳ Tenure: <strong><?php echo htmlspecialchars($project['tenure_months']); ?> Months</strong></li>
                                <li>💵 Minimum Investment: <strong>₹<?php echo number_format($project['min_investment']); ?></strong></li>
                                <li>⚙️ Asset Management Fee: <strong>₹<?php echo number_format($project['management_fee']); ?></strong></li>
                            </ul>

                            <p class="investment-pitch"><?php echo htmlspecialchars($project['description']); ?></p>
                            <p class="hot-deal">🎬 Hot Deal from Velan Productions (WEB) 🔥 Limited Ticket Size – Grab Yours Before It’s Gone!</p>

                            <div class="invest-now-container">
                                <a href="invest.php?project_id=<?php echo $project['id']; ?>" class="btn-primary">Invest Now</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
