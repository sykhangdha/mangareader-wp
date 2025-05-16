<?php
// Fetch mangas for the Start menu
$manga_base_path = ABSPATH . 'manga/';
$mangas = is_dir($manga_base_path) ? array_filter(glob($manga_base_path . '*'), 'is_dir') : [];

// Get the server's timezone (optional, for reference)
$timezone = wp_timezone_string();

// Get server-side Pacific Time as a fallback
date_default_timezone_set('America/Los_Angeles');
$pacific_time = date('g:i A'); // e.g., "2:50 AM"
?>

<footer id="colophon" class="site-footer">
    <div class="start-menu-wrapper">
        <button class="start-button" data-start-button>Start</button>
        <div class="start-menu" style="display: none;">
            <ul>
                <?php if (empty($mangas)) : ?>
                    <li>No manga found</li>
                <?php else : ?>
                    <?php foreach ($mangas as $manga_path) : 
                        $manga_name = basename($manga_path);
                        $normalized_manga_name = manga_reader_normalize_name($manga_name);
                    ?>
                        <li>
                            <a href="<?php echo esc_url(site_url('/manga/' . $normalized_manga_name)); ?>">
                                <?php echo esc_html($manga_name); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
    <div class="footer-content">
        <span>Powered by MangaViewer. Theme Applied: Manga95 - A retro style mangaviewer</span>
    </div>
    <span id="current-time" class="current-time"><?php echo esc_html($pacific_time); ?></span>
</footer><!-- #colophon -->
</div><!-- #page -->

<script type="text/javascript">
    // Pass the server's timezone and Pacific timezone to JavaScript
    const serverTimezone = <?php echo json_encode($timezone); ?>;
    const pacificTimezone = 'America/Los_Angeles';

    // Function to update the current time in Pacific Time
    function updatePacificTime() {
        const timeElement = document.getElementById('current-time');
        if (!timeElement) {
            console.error('Element #current-time not found');
            return;
        }
        const options = {
            timeZone: pacificTimezone,
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        };
        const formatter = new Intl.DateTimeFormat('en-US', options);
        const timeString = formatter.format(new Date()); // e.g., "2:50 AM"
        timeElement.textContent = timeString; // e.g., "2:50 AM"
        console.log('Updated time:', timeString); // Debug log
    }

    // Run on page load
    try {
        // Update time immediately and every minute (less frequent than seconds for performance)
        updatePacificTime();
        const intervalId = setInterval(updatePacificTime, 60000); // Update every minute
        // Clean up interval on page unload
        window.addEventListener('unload', () => clearInterval(intervalId));
    } catch (error) {
        console.error('Failed to initialize time update:', error);
    }
</script>

<?php wp_footer(); ?>
</body>
</html>