<?php include 'includes/header.php'; ?>

<main>
    <div class="content-wrapper">
        <h1>Recent Trailers</h1>
        <div class="trailers-grid">
            <?php for ($i = 1; $i <= 6; $i++): ?>
            <div class="trailer-item">
                <div class="video-container">
                    <video controls poster="https://via.placeholder.com/400x225.png?text=Trailer+<?php echo $i; ?>">
                        <!-- <source src="path/to/trailer-<?php echo $i; ?>.mp4" type="video/mp4"> -->
                        Your browser does not support the video tag.
                    </video>
                </div>
                <div class="investment-snippet">
                    <div class="investment-details">
                        <span>Invested Amount: <strong>₹XX,XX,XXX</strong></span>
                        <span>Collected Amount: <strong>₹YY,YY,YYY</strong></span>
                    </div>
                    <p class="hot-deal">🎬 Hot Deal from Velan Productions (WEB) 🔥 Limited Ticket Size – Grab Yours Before It’s Gone!</p>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
