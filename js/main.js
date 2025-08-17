document.addEventListener('DOMContentLoaded', function() {
    const splashScreen = document.getElementById('splash-screen');
    const mainContent = document.getElementById('main-content');
    const splashVideo = document.getElementById('splash-video');
    const skipButton = document.getElementById('skip-btn');

    // Only run this logic if the splash screen element actually exists on the page
    if (splashScreen && splashVideo && mainContent) {

        const endSplashScreen = () => {
            // Add 'hidden' class to start the fade-out transition
            splashScreen.classList.add('hidden');

            // Make the main content visible
            mainContent.classList.remove('hidden');
            mainContent.style.display = 'block'; // Ensure display is set to block if it was none

            // After the fade-out transition is complete, set display to none
            // to remove it from the accessibility tree and prevent interaction.
            splashScreen.addEventListener('transitionend', () => {
                splashScreen.style.display = 'none';
            }, { once: true });
        };

        // Event listener for when the video finishes playing
        splashVideo.addEventListener('ended', endSplashScreen);

        // Event listener for the skip button
        skipButton.addEventListener('click', endSplashScreen);

        // A fallback in case the video fails to play
        splashVideo.addEventListener('error', endSplashScreen);
    }
});
