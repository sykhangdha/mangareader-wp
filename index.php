<?php
get_header();

// Define get_chapters_for_manga function for reusability
function get_chapters_for_manga($manga_name) {
    $base_path = ABSPATH . 'manga/';
    $manga_name = manga_reader_denormalize_name(manga_reader_normalize_name($manga_name), $base_path);
    $manga_path = $base_path . $manga_name;

    $chapters = [];

    // Fetch chapters from file system
    if (is_dir($manga_path)) {
        $chapter_dirs = array_filter(glob($manga_path . '/*'), 'is_dir');
        foreach ($chapter_dirs as $chapter_dir) {
            $chapter_name = basename($chapter_dir);
            $chapters[] = [
                'id' => 'fs-' . md5($chapter_name), // Pseudo-ID for file system chapters
                'name' => $chapter_name,
                'date' => date('Y-m-d', filemtime($chapter_dir)),
                'source' => 'filesystem'
            ];
        }
    }

    // Fetch chapters from database (manga_chapter post type)
    $args = [
        'post_type' => 'manga_chapter',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'manga_name',
                'value' => $manga_name,
                'compare' => '=',
            ],
        ],
        'posts_per_page' => -1,
    ];
    $query = new WP_Query($args);
    while ($query->have_posts()) {
        $query->the_post();
        $chapters[] = [
            'id' => get_the_ID(),
            'name' => get_the_title(),
            'date' => get_the_date('Y-m-d'),
            'source' => 'database'
        ];
    }
    wp_reset_postdata();

    // Sort chapters by volume and chapter number
    usort($chapters, function ($a, $b) {
        $parse = function ($name) {
            $vol = 0;
            $ch = 0;
            if (preg_match('/Vol\.?\s*(\d+(?:\.\d+)?)/i', $name, $volMatch)) {
                $vol = floatval($volMatch[1]);
            }
            if (preg_match('/Ch\.?\s*(\d+(?:\.\d+)?)/i', $name, $chMatch)) {
                $ch = floatval($chMatch[1]);
            }
            return [$vol, $ch];
        };

        [$volA, $chA] = $parse($a['name']);
        [$volB, $chB] = $parse($b['name']);

        return ($volB <=> $volA) ?: ($chB <=> $chA);
    });

    return $chapters;
}
?>

<!-- Announcement Section -->
<div class="announcement-section">
    <div class="announcement-titlebar">Announcement</div>
    <div class="announcement-content">
        <div class="announcement-image">
            <?php
            $announcement_image = get_option('announcement_image');
            if ($announcement_image) :
            ?>
                <img src="<?php echo esc_url($announcement_image); ?>" alt="Announcement Image" class="announcement-image-flip">
            <?php else : ?>
                <img src="https://via.placeholder.com/200x200?text=No+Image" alt="No Announcement Image" class="announcement-image-flip">
            <?php endif; ?>
        </div>
        <div class="announcement-text">
            <?php
            $announcement_title = get_option('announcement_title', 'Welcome to the new and improved site!');
            $announcement_text  = get_option('announcement_text', 'Monthly chapter resets happen at the start of each month. Only the 3 latest chapters will be available at first.');
            ?>
            <h2><?php echo esc_html($announcement_title); ?></h2>
            <p><?php echo esc_html($announcement_text); ?></p>
        </div>
    </div>
    <div class="announcement-statusbar">
        <button id="select-theme-button" class="theme-selector">Select Theme</button>
    </div>
</div>

<!-- Theme Selection Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery is available (already enqueued in functions.php)
    jQuery(function($) {
        // Hardcoded themes for simplicity (can be fetched via AJAX if needed)
        const themes = ['default', 'manga95']; // Adjust based on available themes in /themes/

        // Handle click on Select Theme button
        $('#select-theme-button').on('click', function() {
            // Prevent multiple dialogs
            if ($('#theme-selector-dialog').length) return;

            // Create dialog HTML
            let selectHtml = '<div id="theme-selector-dialog" style="position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:rgba(40,40,40,0.9); padding:20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.3); z-index:1000; color:#e5e5e5; font-family:Arial,sans-serif;">';
            selectHtml += '<h3 style="margin:0 0 15px; font-size:16pt;">Select Theme</h3>';
            selectHtml += '<select id="theme-select" style="width:100%; padding:8px; border-radius:4px; background:#333; color:#e5e5e5; border:1px solid #555;">';
            themes.forEach(theme => {
                selectHtml += `<option value="${theme}">${theme.charAt(0).toUpperCase() + theme.slice(1)}</option>`;
            });
            selectHtml += '</select>';
            selectHtml += '<div style="margin-top:15px; text-align:right;">';
            selectHtml += '<button id="theme-save" style="background:#0078d4; color:#fff; padding:8px 16px; border:none; border-radius:4px; cursor:pointer; margin-right:10px;">Apply</button>';
            selectHtml += '<button id="theme-cancel" style="background:#555; color:#fff; padding:8px 16px; border:none; border-radius:4px; cursor:pointer;">Cancel</button>';
            selectHtml += '</div></div>';

            $('body').append(selectHtml);

            // Handle Apply button
            $('#theme-save').on('click', function() {
                const selectedTheme = $('#theme-select').val();
                $.ajax({
                    url: mangaAjax.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'manga_reader_change_user_theme',
                        theme: selectedTheme,
                        nonce: mangaAjax.theme_nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#theme-selector-dialog').remove();
                            alert('Theme changed successfully! Reloading...');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.data.message || 'Failed to change theme.'));
                        }
                    },
                    error: function() {
                        alert('An error occurred while changing the theme.');
                    }
                });
            });

            // Handle Cancel button
            $('#theme-cancel').on('click', function() {
                $('#theme-selector-dialog').remove();
            });
        });

        // Optional: Add hover effects for Fluent Design
        $('#select-theme-button').hover(
            function() {
                $(this).css({
                    'background-color': '#0078d4',
                    'transform': 'scale(1.05)'
                });
            },
            function() {
                $(this).css({
                    'background-color': 'rgba(40, 40, 40, 0.9)',
                    'transform': 'scale(1)'
                });
            }
        );
    });
});
</script>

<!-- Manga Library -->
<div class="container">
    <h1>Library</h1>
    <div class="manga-grid">
        <?php
        $manga_base_path = ABSPATH . 'manga/';
        if (!is_dir($manga_base_path)) {
            echo '<p>Manga directory not found. Please ensure the /manga/ directory exists in your WordPress root.</p>';
        } else {
            $mangas = array_filter(glob($manga_base_path . '*'), 'is_dir');

            if (empty($mangas)) {
                echo '<p>No manga found in the /manga/ directory.</p>';
            } else {
                // Preload cover images
                ?>
                <script>
                    (function() {
                        const images = [
                            <?php
                            foreach ($mangas as $manga_path) {
                                $manga_name = basename($manga_path);
                                $cover_path = $manga_path . '/cover.jpg';
                                $cover_url = file_exists($cover_path)
                                    ? site_url('/manga/' . rawurlencode($manga_name) . '/cover.jpg')
                                    : 'https://via.placeholder.com/200x300?text=No+Cover';
                                echo "'" . esc_url($cover_url) . "',";
                            }
                            ?>
                        ];
                        images.forEach(url => {
                            const img = new Image();
                            img.src = url;
                        });
                    })();
                </script>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        document.querySelectorAll('.manga-cover-wrapper img').forEach(img => {
                            if (img.complete && img.naturalWidth !== 0) {
                                img.parentNode.querySelector('.manga-spinner').style.display = 'none';
                            } else {
                                img.addEventListener('load', function() {
                                    this.parentNode.querySelector('.manga-spinner').style.display = 'none';
                                });
                                img.addEventListener('error', function() {
                                    this.parentNode.querySelector('.manga-spinner').style.display = 'none';
                                });
                            }
                        });
                    });
                </script>
                <?php

                foreach ($mangas as $manga_path) {
                    $manga_name            = basename($manga_path);
                    $normalized_manga_name = manga_reader_normalize_name($manga_name);
                    $cover_path            = $manga_path . '/cover.jpg';
                    $cover_url             = site_url('/manga/' . rawurlencode($manga_name) . '/cover.jpg');
                    $cover_image           = file_exists($cover_path)
                        ? $cover_url
                        : 'https://via.placeholder.com/200x300?text=No+Cover';

                    // Fetch chapters using the new function
                    $all_chapters = get_chapters_for_manga($manga_name);

                    $latest_chapter = 'No chapters found';
                    $chapter_date   = 'Unknown';

                    if (!empty($all_chapters)) {
                        // Latest chapter is already sorted by the function (highest vol/ch)
                        $latest = $all_chapters[0];
                        $latest_chapter = $latest['name'];
                        $chapter_date   = $latest['date'];
                    }
                    ?>
                    <div class="manga-item">
                        <div class="manga-item-titlebar"><?php echo esc_html($manga_name); ?></div>
                        <div class="manga-item-content">
                            <a href="<?php echo esc_url(site_url('/manga/' . $normalized_manga_name)); ?>">
                                <div class="manga-cover-wrapper">
                                    <img src="<?php echo esc_url($cover_image); ?>" alt="<?php echo esc_attr($manga_name); ?> Cover">
                                    <div class="manga-spinner"></div>
                                </div>
                                <h3><?php echo esc_html($manga_name); ?></h3>
                                <p>Latest: <?php echo esc_html($latest_chapter); ?></p>
                                <p>Updated: <?php echo esc_html($chapter_date); ?></p>
                            </a>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>