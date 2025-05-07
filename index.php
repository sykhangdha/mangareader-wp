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

        <?php if (manga_reader_should_show_update_button()) : ?>
            <a href="<?php echo esc_url(add_query_arg('refresh', time(), home_url())); ?>" class="update-site-btn">🔄 Update Site</a>
        <?php endif; ?>
    </div>
</div>

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
                        <a href="<?php echo esc_url(site_url('/manga/' . $normalized_manga_name)); ?>">
                            <img src="<?php echo esc_url($cover_image); ?>" alt="<?php echo esc_attr($manga_name); ?> Cover">
                            <h3><?php echo esc_html($manga_name); ?></h3>
                            <p>Latest: <?php echo esc_html($latest_chapter); ?></p>
                            <p>Updated: <?php echo esc_html($chapter_date); ?></p>
                        </a>
                    </div>
                    <?php
                }
            }
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>
