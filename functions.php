<?php
// ===============================
// THEME SETUP
// ===============================
function manga_reader_theme_setup() {
    add_theme_support('post-thumbnails');
    add_theme_support('menus');
    register_nav_menus([
        'primary' => __('Primary Menu', 'manga-reader-theme'),
    ]);
}
add_action('after_setup_theme', 'manga_reader_theme_setup');

// ===============================
// ASSET VERSION (CACHE BUSTING)
// ===============================
function manga_reader_get_asset_version($file) {
    $file_path = get_template_directory() . '/' . ltrim($file, '/');
    if (file_exists($file_path)) {
        return filemtime($file_path);
    }
    return '1.0';
}

// ===============================
// GET AVAILABLE THEMES
// ===============================
function manga_reader_get_available_themes() {
    $themes_dir = get_template_directory() . '/themes/';
    $themes = [];

    if (!is_dir($themes_dir)) {
        return $themes;
    }

    $dirs = array_filter(glob($themes_dir . '*'), 'is_dir');
    foreach ($dirs as $dir) {
        $theme_name = basename($dir);
        $style_path = $dir . '/style.css';
        $manga_style_path = $dir . '/style-manga.css';

        if (file_exists($style_path) && file_exists($manga_style_path)) {
            $themes[] = $theme_name;
        }
    }

    return $themes;
}

// ===============================
// FRONTEND ASSETS
// ===============================
function manga_reader_enqueue_assets() {
    if (is_admin()) return;

    $dir = get_template_directory_uri();
    // Use user-specific theme if set, otherwise fall back to site-wide theme
    $user_id = get_current_user_id();
    $selected_theme = $user_id ? get_user_meta($user_id, 'manga_reader_user_theme', true) : '';
    if (!$selected_theme) {
        $selected_theme = get_option('manga_reader_theme', 'default');
    }
    $style_path = 'style.css';
    $manga_style_path = 'style-manga.css';

    // Validate custom theme
    if ($selected_theme !== 'default') {
        $theme_dir = get_template_directory() . '/themes/' . sanitize_key($selected_theme);
        $theme_style = $theme_dir . '/style.css';
        $theme_manga_style = $theme_dir . '/style-manga.css';

        if (is_dir($theme_dir) && file_exists($theme_style) && file_exists($theme_manga_style)) {
            $style_path = "themes/$selected_theme/style.css";
            $manga_style_path = "themes/$selected_theme/style-manga.css";
            $dir = get_template_directory_uri() . '/themes/' . sanitize_key($selected_theme);
        }
    }

    wp_enqueue_style('manga-theme-style', "$dir/" . basename($style_path), [], manga_reader_get_asset_version($style_path));
    wp_enqueue_style('manga-reader-manga-style', "$dir/" . basename($manga_style_path), ['manga-theme-style'], manga_reader_get_asset_version($manga_style_path));
    wp_enqueue_script('manga-reader-script', get_template_directory_uri() . '/script.js', ['jquery'], manga_reader_get_asset_version('script.js'), true);
    wp_localize_script('manga-reader-script', 'mangaAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'theme_nonce' => wp_create_nonce('manga_reader_theme_switch'),
    ]);

    add_filter('style_loader_tag', function ($tag, $handle) {
        if (strpos($handle, 'manga') !== false) {
            $tag = str_replace('<link', '<link data-cache-control="no-cache, no-store, must-revalidate"', $tag);
        }
        return $tag;
    }, 10, 2);

    add_filter('script_loader_tag', function ($tag, $handle) {
        if (strpos($handle, 'manga') !== false) {
            $tag = str_replace('<script', '<script data-cache-control="no-cache, no-store, must-revalidate"', $tag);
        }
        return $tag;
    }, 10, 2);
}
add_action('wp_enqueue_scripts', 'manga_reader_enqueue_assets');

// ===============================
// ADMIN ASSETS
// ===============================
function manga_reader_admin_assets($hook) {
    if (!isset($_GET['page']) || $_GET['page'] !== 'manga-reader-settings') return;

    $dir = get_template_directory_uri();

    wp_enqueue_style('manga-reader-admin-style', "$dir/admin-style.css", [], manga_reader_get_asset_version('admin-style.css'));
    wp_enqueue_script('manga-reader-admin-script', "$dir/admin-script.js", ['jquery'], manga_reader_get_asset_version('admin-script.js'), true);
    wp_localize_script('manga-reader-admin-script', 'mangaAdminAjax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('manga_reader_admin_nonce'),
    ]);

    wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'manga_reader_admin_assets');

// ===============================
// URL REWRITE
// ===============================
function manga_reader_rewrite_rules() {
    add_rewrite_rule('manga/([^/]+)/?$', 'index.php?manga_name=$matches[1]', 'top');
}
add_action('init', 'manga_reader_rewrite_rules');

function manga_reader_query_vars($vars) {
    $vars[] = 'manga_name';
    return $vars;
}
add_filter('query_vars', 'manga_reader_query_vars');

function manga_reader_template_redirect() {
    $manga_name = get_query_var('manga_name');
    if ($manga_name) {
        include get_template_directory() . '/manga-page.php';
        exit;
    }
}
add_action('template_redirect', 'manga_reader_template_redirect');

// ===============================
// NAME NORMALIZATION HELPERS
// ===============================
function manga_reader_normalize_name($name) {
    $name = preg_replace('/[^\w\s-]/', '', $name);
    $name = str_replace(' ', '-', $name);
    return strtolower($name);
}

function manga_reader_denormalize_name($normalized, $base_path = null) {
    $base_path = $base_path ?? ABSPATH . 'manga/';
    if (!is_dir($base_path)) return $normalized;

    foreach (array_filter(glob($base_path . '*'), 'is_dir') as $dir) {
        $dir_name = basename($dir);
        $normalized_dir = manga_reader_normalize_name($dir_name);
        if ($normalized_dir === $normalized) {
            return $dir_name;
        }
    }
    return $normalized;
}

// ===============================
// CHAPTER NAME FORMATTING
// ===============================
function manga_reader_format_chapter_name($name, $use_vol_ch = false, $vol = null, $ch = null) {
    if ($use_vol_ch && $vol !== null && $ch !== null) {
        return "Vol. " . trim($vol) . " Ch. " . trim($ch);
    }

    $name = trim($name);
    if (preg_match('/^(vol\.?|ch\.?|chapter)\s*/i', $name)) {
        return $name;
    }
    if (is_numeric($name) || preg_match('/^\d+(\.\d+)?$/', $name)) {
        return "Ch. $name";
    }
    return $name;
}

// ===============================
// CUSTOM POST TYPE: MANGA CHAPTERS
// ===============================
function manga_reader_register_chapter_post_type() {
    register_post_type('manga_chapter', [
        'labels' => [
            'name' => __('Manga Chapters'),
            'singular_name' => __('Manga Chapter'),
        ],
        'public' => false,
        'show_ui' => false,
        'supports' => ['title', 'custom-fields'],
        'capability_type' => 'post',
        'capabilities' => [
            'create_posts' => 'manage_options',
        ],
        'map_meta_cap' => true,
    ]);
}
add_action('init', 'manga_reader_register_chapter_post_type');

// ===============================
// SHORTCODE: DISPLAY MANGA VIEWER
// ===============================
function manga_reader_display_manga($manga_name) {
    if (!$manga_name) return '<p>Please provide a manga name.</p>';

    $normalized = manga_reader_normalize_name($manga_name);
    $base_path = ABSPATH . 'manga/';
    $actual = manga_reader_denormalize_name($normalized, $base_path);

    $cover_path = $base_path . $actual . '/cover.jpg';
    $cover_url = file_exists($cover_path) ? site_url("/manga/$actual/cover.jpg") : '';

    ob_start(); ?>
    <div class="manga-viewer" data-manga-name="<?= esc_attr($actual) ?>">
        <?php if ($cover_url): ?>
            <div id="manga-cover">
                <img class="cover" src="<?= esc_url($cover_url) ?>" alt="<?= esc_attr($actual) ?> Cover">
            </div>
        <?php endif; ?>
        <h2 id="manga-heading"><?= esc_html($actual) ?></h2>
        <div id="manga-sidebar" class="sidebar sidebar-hidden">
            <button class="sidebar-close">×</button>
            <div class="sidebar-content">
                <h3 class="viewer-version">MangaViewer 1.0</h3>
                <ul id="mangaview-chapterlist-sidebar"></ul>
            </div>
        </div>
        <div id="manga-images-container">
            <div id="manga-images" class="manga-images"></div>
        </div>
        <div class="view-toggle">
            <button data-view="list">List View</button>
            <button data-view="paged">Paged View</button>
        </div>
        <button class="sidebar-toggle">Toggle Sidebar</button>
        <button id="back-to-chapters" data-action="back-to-chapters" style="display:none;">Back to Chapter List</button>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('display_manga', function ($atts) {
    $atts = shortcode_atts(['name' => ''], $atts, 'display_manga');
    return manga_reader_display_manga($atts['name']);
});

// ===============================
// AJAX: GET CHAPTERS
// ===============================
function manga_reader_get_chapters() {
    $manga = sanitize_text_field($_POST['manga'] ?? '');
    $base_path = ABSPATH . 'manga/';
    $actual = manga_reader_denormalize_name(manga_reader_normalize_name($manga), $base_path);
    $path = $base_path . $actual;

    $chapters = [];

    if (is_dir($path)) {
        $dirs = array_filter(glob($path . '/*'), 'is_dir');
        foreach ($dirs as $dir) {
            $chapters[] = [
                'name' => basename($dir),
                'date' => date("Y-m-d", filemtime($dir)),
                'source' => 'folder',
            ];
        }
    }

    $args = [
        'post_type' => 'manga_chapter',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'manga_name',
                'value' => $actual,
                'compare' => '=',
            ],
        ],
        'posts_per_page' => -1,
    ];
    $query = new WP_Query($args);
    while ($query->have_posts()) {
        $query->the_post();
        $chapters[] = [
            'name' => get_the_title(),
            'date' => get_the_date('Y-m-d'),
            'source' => 'database',
        ];
    }
    wp_reset_postdata();

    if (!$chapters) {
        wp_send_json_error(['message' => 'No chapters found.']);
    }

    usort($chapters, fn($a, $b) => strcmp($b['name'], $a['name']));

    wp_send_json_success(['chapters' => $chapters]);
}
add_action('wp_ajax_get_chapters', 'manga_reader_get_chapters');
add_action('wp_ajax_nopriv_get_chapters', 'manga_reader_get_chapters');

// ===============================
// AJAX: GET IMAGES
// ===============================
function manga_reader_get_images() {
    $manga = sanitize_text_field($_POST['manga'] ?? '');
    $chapter = sanitize_text_field($_POST['chapter'] ?? '');
    $base_path = ABSPATH . 'manga/';
    $actual = manga_reader_denormalize_name(manga_reader_normalize_name($manga), $base_path);
    $path = "$base_path$actual/$chapter";
    $url = site_url("/manga/$actual/$chapter");

    $images = [];

    if (is_dir($path)) {
        $files = glob("$path/*.{jpg,jpeg,png}", GLOB_BRACE);
        if ($files) {
            usort($files, fn($a, $b) => filemtime($a) - filemtime($b));
            $images = array_map(fn($img) => "$url/" . basename($img), $files);
        }
    } else {
        $args = [
            'post_type' => 'manga_chapter',
            'post_status' => 'publish',
            'title' => $chapter,
            'meta_query' => [
                [
                    'key' => 'manga_name',
                    'value' => $actual,
                    'compare' => '=',
                ],
            ],
            'posts_per_page' => 1,
        ];
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            $query->the_post();
            $image_ids = get_post_meta(get_the_ID(), 'chapter_images', true);
            if ($image_ids && is_array($image_ids)) {
                $images = array_map(function ($id) {
                    return wp_get_attachment_url($id);
                }, $image_ids);
            }
            $image_urls = get_post_meta(get_the_ID(), 'chapter_image_urls', true);
            if ($image_urls && is_array($image_urls)) {
                $images = array_merge($images, array_filter($image_urls));
            }
        }
        wp_reset_postdata();
    }

    if (!$images) {
        wp_send_json_error(['message' => 'No images found.']);
    }

    wp_send_json_success($images);
}
add_action('wp_ajax_get_images', 'manga_reader_get_images');
add_action('wp_ajax_nopriv_get_images', 'manga_reader_get_images');

// ===============================
// AJAX: CHANGE USER THEME
// ===============================
function manga_reader_change_user_theme() {
    check_ajax_referer('manga_reader_theme_switch', 'nonce');

    // Allow any logged-in user to change their theme
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You must be logged in to change the theme.']);
    }

    $theme = sanitize_key($_POST['theme'] ?? '');
    $available_themes = manga_reader_get_available_themes();
    $available_themes[] = 'default'; // Include default theme

    if (empty($theme) || !in_array($theme, $available_themes)) {
        wp_send_json_error(['message' => 'Invalid theme selected.']);
    }

    $user_id = get_current_user_id();
    update_user_meta($user_id, 'manga_reader_user_theme', $theme);

    wp_send_json_success(['message' => 'Theme changed successfully!']);
}
add_action('wp_ajax_manga_reader_change_user_theme', 'manga_reader_change_user_theme');
add_action('wp_ajax_nopriv_manga_reader_change_user_theme', 'manga_reader_change_user_theme');

// ===============================
// AJAX: CHECK CHAPTER NAMING CONVENTION
// ===============================
function manga_reader_check_chapter_naming() {
    check_ajax_referer('manga_reader_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied.']);
    }

    $manga = sanitize_text_field($_POST['manga'] ?? '');
    if (empty($manga)) {
        wp_send_json_error(['message' => 'Manga name is required.']);
    }

    $base_path = ABSPATH . 'manga/';
    $actual = manga_reader_denormalize_name(manga_reader_normalize_name($manga), $base_path);
    $path = $base_path . $actual;

    $chapters = [];

    if (is_dir($path)) {
        $dirs = array_filter(glob($path . '/*'), 'is_dir');
        foreach ($dirs as $dir) {
            $chapters[] = basename($dir);
        }
    }

    $args = [
        'post_type' => 'manga_chapter',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'manga_name',
                'value' => $actual,
                'compare' => '=',
            ],
        ],
        'posts_per_page' => -1,
    ];
    $query = new WP_Query($args);
    while ($query->have_posts()) {
        $query->the_post();
        $chapters[] = get_the_title();
    }
    wp_reset_postdata();

    $uses_vol_ch = false;
    foreach ($chapters as $chapter_name) {
        if (preg_match('/Vol\.?\s*\d+\s*Ch\.?\s*\d+/i', $chapter_name)) {
            $uses_vol_ch = true;
            break;
        }
    }

    wp_send_json_success(['uses_vol_ch' => $uses_vol_ch]);
}
add_action('wp_ajax_manga_reader_check_chapter_naming', 'manga_reader_check_chapter_naming');

// ===============================
// AJAX: GET CHAPTER DATA FOR EDIT
// ===============================
function manga_reader_get_chapter_data() {
    check_ajax_referer('manga_reader_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied.']);
    }

    $chapter_id = intval($_POST['chapter_id'] ?? 0);
    if (!$chapter_id) {
        wp_send_json_error(['message' => 'Invalid chapter ID.']);
    }

    $post = get_post($chapter_id);
    if (!$post || $post->post_type !== 'manga_chapter') {
        wp_send_json_error(['message' => 'Chapter not found.']);
    }

    $manga_name = get_post_meta($chapter_id, 'manga_name', true);
    $image_ids = get_post_meta($chapter_id, 'chapter_images', true) ?: [];
    $image_urls = get_post_meta($chapter_id, 'chapter_image_urls', true) ?: [];

    $image_previews = array_map(function ($id) {
        $url = wp_get_attachment_url($id);
        return $url ? "<img src='$url' style='max-width:100px; margin:5px;' />" : '';
    }, is_array($image_ids) ? $image_ids : []);

    wp_send_json_success([
        'chapter_name' => $post->post_title,
        'manga_name' => $manga_name,
        'image_ids' => is_array($image_ids) ? $image_ids : [],
        'image_urls' => is_array($image_urls) ? implode("\n", $image_urls) : '',
        'image_previews' => implode('', $image_previews),
        'chapter_date' => get_the_date('Y-m-d', $chapter_id),
    ]);
}
add_action('wp_ajax_manga_reader_get_chapter_data', 'manga_reader_get_chapter_data');

// ===============================
// AJAX: ADD CHAPTER
// ===============================
function manga_reader_add_chapter() {
    check_ajax_referer('manga_reader_add_chapter', 'manga_reader_chapter_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied.']);
    }

    $manga = sanitize_text_field($_POST['chapter_manga'] ?? '');
    $chapter_name = sanitize_text_field($_POST['chapter_name'] ?? '');
    $chapter_vol = sanitize_text_field($_POST['chapter_vol'] ?? '');
    $chapter_num = sanitize_text_field($_POST['chapter_num'] ?? '');
    $chapter_date = sanitize_text_field($_POST['chapter_date'] ?? '');
    $image_ids = !empty($_POST['chapter_image_ids']) ? array_map('intval', explode(',', $_POST['chapter_image_ids'])) : [];
    $image_urls = !empty($_POST['chapter_image_urls']) ? array_map('esc_url_raw', array_filter(explode("\n", trim($_POST['chapter_image_urls'])))) : [];

    if (empty($manga)) {
        wp_send_json_error(['message' => 'Manga name is required.']);
    }

    $use_vol_ch = !empty($chapter_vol) && !empty($chapter_num);
    if ($use_vol_ch) {
        if (!is_numeric($chapter_vol) || !is_numeric($chapter_num)) {
            wp_send_json_error(['message' => 'Volume and chapter numbers must be numeric.']);
        }
        $chapter_name = manga_reader_format_chapter_name('', true, $chapter_vol, $chapter_num);
    } else {
        if (empty($chapter_name)) {
            wp_send_json_error(['message' => 'Chapter name is required.']);
        }
        $chapter_name = manga_reader_format_chapter_name($chapter_name);
    }

    $post_args = [
        'post_title' => $chapter_name,
        'post_type' => 'manga_chapter',
        'post_status' => 'publish',
    ];

    if ($chapter_date) {
        $post_args['post_date'] = $chapter_date;
    }

    $post_id = wp_insert_post($post_args);

    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'Error creating chapter: ' . $post_id->get_error_message()]);
    }

    update_post_meta($post_id, 'manga_name', $manga);
    if ($image_ids) {
        update_post_meta($post_id, 'chapter_images', $image_ids);
    }
    if ($image_urls) {
        update_post_meta($post_id, 'chapter_image_urls', $image_urls);
    }

    wp_send_json_success(['message' => 'Chapter added successfully!']);
}
add_action('wp_ajax_manga_reader_add_chapter', 'manga_reader_add_chapter');

// ===============================
// AJAX: UPDATE CHAPTER
// ===============================
function manga_reader_update_chapter() {
    check_ajax_referer('manga_reader_update_chapter', 'manga_reader_update_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied.']);
    }

    $chapter_id = intval($_POST['chapter_id'] ?? 0);
    $chapter_name = sanitize_text_field($_POST['chapter_name'] ?? '');
    $chapter_date = sanitize_text_field($_POST['chapter_date'] ?? '');
    $image_ids = !empty($_POST['chapter_image_ids']) ? array_map('intval', explode(',', $_POST['chapter_image_ids'])) : [];
    $image_urls = !empty($_POST['chapter_image_urls']) ? array_map('esc_url_raw', array_filter(explode("\n", trim($_POST['chapter_image_urls'])))) : [];

    if (!$chapter_id || empty($chapter_name)) {
        wp_send_json_error(['message' => 'Chapter ID and name are required.']);
    }

    $chapter_name = manga_reader_format_chapter_name($chapter_name);

    $update_args = [
        'ID' => $chapter_id,
        'post_title' => $chapter_name,
        'post_status' => 'publish',
    ];

    if ($chapter_date) {
        $update_args['post_date'] = $chapter_date;
    }

    $update = wp_update_post($update_args);

    if (is_wp_error($update)) {
        wp_send_json_error(['message' => 'Error updating chapter: ' . $update->get_error_message()]);
    }

    $manga = sanitize_text_field($_POST['chapter_manga'] ?? '');
    if ($manga) {
        update_post_meta($chapter_id, 'manga_name', $manga);
    }
    update_post_meta($chapter_id, 'chapter_images', $image_ids);
    update_post_meta($chapter_id, 'chapter_image_urls', $image_urls);

    wp_send_json_success(['message' => 'Chapter updated successfully!']);
}
add_action('wp_ajax_manga_reader_update_chapter', 'manga_reader_update_chapter');

// ===============================
// AJAX: DELETE CHAPTER
// ===============================
function manga_reader_delete_chapter() {
    check_ajax_referer('manga_reader_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied.']);
    }

    $chapter_id = intval($_POST['chapter_id'] ?? 0);
    if (!$chapter_id) {
        wp_send_json_error(['message' => 'Invalid chapter ID.']);
    }

    $post = get_post($chapter_id);
    if (!$post || $post->post_type !== 'manga_chapter') {
        wp_send_json_error(['message' => 'Chapter not found.']);
    }

    $image_ids = get_post_meta($chapter_id, 'chapter_images', true) ?: [];
    if (is_array($image_ids)) {
        foreach ($image_ids as $image_id) {
            wp_delete_attachment($image_id, true);
        }
    }

    $deleted = wp_delete_post($chapter_id, true);

    if ($deleted) {
        wp_send_json_success(['message' => 'Chapter deleted successfully!']);
    } else {
        wp_send_json_error(['message' => 'Failed to delete chapter.']);
    }
}
add_action('wp_ajax_manga_reader_delete_chapter', 'manga_reader_delete_chapter');

// ===============================
// AJAX: SCAN MANGA FOLDER
// ===============================
function manga_reader_scan_manga_folder() {
    check_ajax_referer('manga_reader_admin_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied.']);
    }

    $base_path = ABSPATH . 'manga/';
    $mangas = [];

    if (is_dir($base_path)) {
        $dirs = array_filter(glob($base_path . '*'), 'is_dir');
        if (!empty($dirs)) {
            foreach ($dirs as $dir) {
                $manga_name = basename($dir);
                $cover_path = $base_path . $manga_name . '/cover.jpg';
                $cover_url = file_exists($cover_path) ? site_url("/manga/$manga_name/cover.jpg") : '';
                $mangas[] = [
                    'name' => $manga_name,
                    'cover' => $cover_url,
                ];
            }
            wp_send_json_success([
                'message' => 'All manga folders successfully scanned!',
                'mangas' => $mangas
            ]);
        } else {
            wp_send_json_error(['message' => 'No manga folders found in /manga/ directory.']);
        }
    } else {
        wp_send_json_error(['message' => 'Manga directory not found. Please create /manga/ folder in your WordPress root.']);
    }
}
add_action('wp_ajax_manga_reader_scan_manga_folder', 'manga_reader_scan_manga_folder');

// ===============================
// AJAX: ADD NEW MANGA FOLDER
// ===============================
function manga_reader_add_manga_folder() {
    check_ajax_referer('manga_reader_add_manga', 'manga_reader_manga_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied.']);
    }

    $manga_name = sanitize_text_field($_POST['manga_name'] ?? '');
    if (empty($manga_name)) {
        wp_send_json_error(['message' => 'Manga name is required.']);
    }

    $base_path = ABSPATH . 'manga/';
    $manga_path = $base_path . $manga_name;

    if (is_dir($manga_path)) {
        wp_send_json_error(['message' => 'Manga folder already exists.']);
    }

    if (!is_dir($base_path)) {
        if (!mkdir($base_path, 0755, true)) {
            wp_send_json_error(['message' => 'Failed to create manga directory.']);
        }
    }

    if (mkdir($manga_path, 0755)) {
        wp_send_json_success(['message' => 'Manga folder created successfully!']);
    } else {
        wp_send_json_error(['message' => 'Failed to create manga folder.']);
    }
}
add_action('wp_ajax_manga_reader_add_manga_folder', 'manga_reader_add_manga_folder');

// ===============================
// AJAX: UPDATE MANGA COVER
// ===============================
function manga_reader_update_manga_cover() {
    check_ajax_referer('manga_reader_update_manga', 'manga_reader_manga_update_nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Permission denied.']);
    }

    $manga_name = sanitize_text_field($_POST['manga_name'] ?? '');
    $remove_cover = isset($_POST['remove_cover']) && $_POST['remove_cover'] === 'yes';
    $base_path = ABSPATH . 'manga/';
    $manga_path = $base_path . $manga_name;
    $cover_path = $manga_path . '/cover.jpg';

    if (empty($manga_name)) {
        wp_send_json_error(['message' => 'Manga name is required.']);
    }

    if (!is_dir($manga_path)) {
        wp_send_json_error(['message' => 'Manga folder does not exist.']);
    }

    if ($remove_cover) {
        if (file_exists($cover_path)) {
            if (unlink($cover_path)) {
                wp_send_json_success(['message' => 'Cover image removed successfully!', 'cover_url' => '']);
            } else {
                wp_send_json_error(['message' => 'Failed to remove cover image.']);
            }
        } else {
            wp_send_json_success(['message' => 'No cover image to remove.', 'cover_url' => '']);
        }
        return;
    }

    if (!empty($_FILES['cover_image']['name'])) {
        $file = $_FILES['cover_image'];
        $allowed_types = ['image/jpeg', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(['message' => 'Only JPG images are allowed.']);
        }

        if ($file['size'] > $max_size) {
            wp_send_json_error(['message' => 'Image size exceeds 5MB limit.']);
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(['message' => 'Error uploading image.']);
        }

        if (file_exists($cover_path)) {
            unlink($cover_path);
        }

        if (move_uploaded_file($file['tmp_name'], $cover_path)) {
            wp_send_json_success([
                'message' => 'Cover image updated successfully!',
                'cover_url' => site_url("/manga/$manga_name/cover.jpg")
            ]);
        } else {
            wp_send_json_error(['message' => 'Failed to save cover image.']);
        }
    } else {
        wp_send_json_error(['message' => 'No cover image provided.']);
    }
}
add_action('wp_ajax_manga_reader_update_manga_cover', 'manga_reader_update_manga_cover');

// ===============================
// ADMIN SETTINGS & PAGE
// ===============================
function manga_reader_add_admin_menu() {
    add_menu_page(
        'Manga Reader Settings',
        'Manga Reader',
        'manage_options',
        'manga-reader-settings',
        'manga_reader_settings_page',
        'dashicons-book-alt',
        80
    );
}
add_action('admin_menu', 'manga_reader_add_admin_menu');

function manga_reader_register_settings() {
    register_setting('manga_reader_announcement_group', 'announcement_title');
    register_setting('manga_reader_announcement_group', 'announcement_text');
    register_setting('manga_reader_announcement_group', 'announcement_image');
    register_setting('manga_reader_settings_group', 'manga_reader_theme', [
        'sanitize_callback' => 'sanitize_key',
        'default' => 'default',
    ]);
}
add_action('admin_init', 'manga_reader_register_settings');

function manga_reader_settings_page() {
    $base_path = ABSPATH . 'manga/';
    $mangas = is_dir($base_path) ? array_filter(glob($base_path . '*'), 'is_dir') : [];
    $manga_options = array_map(fn($dir) => basename($dir), $mangas);

    $per_page = 15;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;
    $selected_manga = isset($_GET['manga_filter']) ? sanitize_text_field($_GET['manga_filter']) : '';

    $args = [
        'post_type' => 'manga_chapter',
        'post_status' => 'publish',
        'posts_per_page' => $per_page,
        'offset' => $offset,
        'meta_query' => [],
    ];

    if ($selected_manga) {
        $args['meta_query'][] = [
            'key' => 'manga_name',
            'value' => $selected_manga,
            'compare' => '=',
        ];
    }

    $query = new WP_Query($args);
    $db_chapters = [];
    while ($query->have_posts()) {
        $query->the_post();
        $db_chapters[] = [
            'id' => get_the_ID(),
            'title' => get_the_title(),
            'manga' => get_post_meta(get_the_ID(), 'manga_name', true),
            'date' => get_the_date('Y-m-d'),
            'source' => 'database',
        ];
    }
    $total_db_chapters = $query->found_posts;
    wp_reset_postdata();

    $fs_chapters = [];
    $total_fs_chapters = 0;
    if (is_dir($base_path)) {
        $manga_dirs = $selected_manga ? [$base_path . $selected_manga] : $mangas;
        foreach ($manga_dirs as $manga_dir) {
            if (!is_dir($manga_dir)) {
                continue;
            }
            $manga_name = basename($manga_dir);
            $chapter_dirs = array_filter(glob($manga_dir . '/*'), 'is_dir');
            foreach ($chapter_dirs as $chapter_dir) {
                $chapter_name = basename($chapter_dir);
                $fs_chapters[] = [
                    'id' => null,
                    'title' => $chapter_name,
                    'manga' => $manga_name,
                    'date' => file_exists($chapter_dir) ? date("Y-m-d", filemtime($chapter_dir)) : '',
                    'source' => 'folder',
                ];
                $total_fs_chapters++;
            }
        }
    }

    $all_chapters = array_merge($db_chapters, $fs_chapters);
    usort($all_chapters, fn($a, $b) => strcmp($b['title'], $a['title']));

    $total_chapters = $total_db_chapters + $total_fs_chapters;
    $total_pages = ceil($total_chapters / $per_page);
    $paged_chapters = array_slice($all_chapters, $offset, $per_page);

    $available_themes = manga_reader_get_available_themes();
    $selected_theme = get_option('manga_reader_theme', 'default');

    ?>
    <div class="wrap manga-reader-settings">
        <h1><span class="dashicons dashicons-book-alt"></span> Manga Reader Settings</h1>

        <div class="manga-reader-sections">
            <!-- Manga Management Section -->
            <div class="manga-reader-section">
                <h2 class="section-title">
                    <span class="dashicons dashicons-book"></span> Manga Management
                    <span class="toggle-section dashicons dashicons-arrow-down-alt2"></span>
                </h2>
                <div class="section-content">
                    <div class="manga-reader-card">
                        <h3>Add New Manga</h3>
                        <p>Create a new manga folder in the <code>/manga/</code> directory.</p>
                        <form id="add-manga-form" method="post" class="manga-reader-form">
                            <?php wp_nonce_field('manga_reader_add_manga', 'manga_reader_manga_nonce'); ?>
                            <div class="form-row">
                                <label for="manga_name">Manga Name:</label>
                                <input type="text" name="manga_name" id="manga_name" required placeholder="e.g., My New Manga" />
                            </div>
                            <div class="form-row">
                                <button type="submit" class="button button-primary">Add Manga</button>
                                <span class="spinner"></span>
                            </div>
                        </form>
                        <div id="manga-add-results" class="form-results"></div>
                    </div>

                    <div class="manga-reader-card">
                        <h3>Edit Manga Cover</h3>
                        <p>Upload, change, or remove the cover image for a manga.</p>
                        <form id="edit-manga-form" method="post" enctype="multipart/form-data" class="manga-reader-form">
                            <?php wp_nonce_field('manga_reader_update_manga', 'manga_reader_manga_update_nonce'); ?>
                            <div class="form-row">
                                <label for="edit_manga_name">Select Manga:</label>
                                <select name="manga_name" id="edit_manga_name" required>
                                    <option value="">Select a Manga</option>
                                    <?php foreach ($manga_options as $manga): ?>
                                        <option value="<?= esc_attr($manga) ?>"><?= esc_html($manga) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-row">
                                <label for="cover_image">Cover Image (JPG):</label>
                                <input type="file" name="cover_image" id="cover_image" accept="image/jpeg,image/jpg" />
                            </div>
                            <div class="form-row">
                                <label for="remove_cover">
                                    <input type="checkbox" name="remove_cover" id="remove_cover" value="yes">
                                    Remove Existing Cover
                                </label>
                            </div>
                            <div id="cover-preview" class="cover-preview"></div>
                            <div class="form-row">
                                <button type="submit" class="button button-primary">Update Cover</button>
                                <span class="spinner"></span>
                            </div>
                        </form>
                        <div id="manga-edit-results" class="form-results"></div>
                    </div>

                    <div class="manga-reader-card">
                        <h3>Scan Manga Folders</h3>
                        <p>Scan the <code>/manga/</code> directory to list all manga and their covers.</p>
                        <div class="form-row">
                            <button id="scan-manga-folder" class="button button-primary">Scan Folders</button>
                            <span class="spinner"></span>
                        </div>
                        <div id="manga-scan-results" class="form-results"></div>
                    </div>
                </div>
            </div>

            <!-- Chapter Management Section -->
            <div class="manga-reader-section">
                <h2 class="section-title">
                    <span class="dashicons dashicons-format-aside"></span> Chapter Management
                    <span class="toggle-section dashicons dashicons-arrow-down-alt2"></span>
                </h2>
                <div class="section-content">
                    <div class="manga-reader-card">
                        <h3>Add New Chapter</h3>
                        <p>Add a new chapter to a manga with images or URLs.</p>
                        <form id="add-chapter-form" method="post" enctype="multipart/form-data" class="manga-reader-form">
                            <?php wp_nonce_field('manga_reader_add_chapter', 'manga_reader_chapter_nonce'); ?>
                            <div class="form-row">
                                <label for="chapter_manga">Select Manga:</label>
                                <select name="chapter_manga" id="chapter_manga" required>
                                    <option value="">Select a Manga</option>
                                    <?php foreach ($manga_options as $manga): ?>
                                        <option value="<?= esc_attr($manga) ?>"><?= esc_html($manga) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="chapter-name-container" class="form-row">
                                <label for="chapter_name">Chapter Name:</label>
                                <input type="text" name="chapter_name" id="chapter_name" required placeholder="e.g., 1 or Ch. 1" />
                            </div>
                            <div id="chapter-naming-message" class="form-row" style="display:none;">
                                <p class="description">Manga uses Vol. and Ch. in the title. Please enter Vol. number and chapter number.</p>
                            </div>
                            <div id="chapter-vol-ch-container" class="form-row" style="display:none;">
                                <label for="chapter_vol">Volume Number:</label>
                                <input type="number" name="chapter_vol" id="chapter_vol" min="0" step="1" placeholder="e.g., 3" style="width:100px;" />
                                <label for="chapter_num" style="margin-left:15px;">Chapter Number:</label>
                                <input type="number" name="chapter_num" id="chapter_num" min="0" step="0.1" placeholder="e.g., 13" style="width:100px;" />
                            </div>
                            <div class="form-row">
                                <label for="chapter_date">Chapter Date (optional):</label>
                                <input type="date" name="chapter_date" id="chapter_date">
                            </div>
                            <div class="form-row">
                                <label for="chapter_images">Upload Images:</label>
                                <input type="button" id="chapter_images_upload" class="button" value="Select Images" />
                            </div>
                            <div id="image-preview" class="image-preview"></div>
                            <input type="hidden" name="chapter_image_ids" id="chapter_image_ids" />
                            <div class="form-row">
                                <label for="chapter_image_urls">Image URLs (one per line):</label>
                                <textarea name="chapter_image_urls" id="chapter_image_urls" rows="5" placeholder="https://example.com/image1.jpg
https://example.com/image2.jpg"></textarea>
                            </div>
                            <div class="form-row">
                                <button type="submit" class="button button-primary">Add Chapter</button>
                                <span class="spinner"></span>
                            </div>
                        </form>
                        <div id="chapter-add-results" class="form-results"></div>
                    </div>

                    <div class="manga-reader-card">
                        <h3>Edit Chapters</h3>
                        <p>Modify or delete existing chapters stored in the database. Folder-based chapters are managed directly in the <code>/manga/</code> directory.</p>
                        <div class="form-row">
                            <label for="manga_filter">Filter by Manga:</label>
                            <select name="manga_filter" id="manga_filter">
                                <option value="">All Manga</option>
                                <?php foreach ($manga_options as $manga): ?>
                                    <option value="<?= esc_attr($manga) ?>" <?php selected($selected_manga, $manga); ?>>
                                        <?= esc_html($manga) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($paged_chapters): ?>
                            <table class="wp-list-table widefat fixed striped">
                                <thead>
                                    <tr>
                                        <th>Manga</th>
                                        <th>Chapter</th>
                                        <th>Date</th>
                                        <th>Source</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paged_chapters as $chapter): ?>
                                        <tr data-chapter-id="<?= esc_attr($chapter['id'] ?: 'fs-' . esc_attr($chapter['title'])) ?>">
                                            <td><?= esc_html($chapter['manga']) ?></td>
                                            <td><?= esc_html($chapter['title']) ?></td>
                                            <td><?= esc_html($chapter['date'] ?: 'N/A') ?></td>
                                            <td><?= esc_html(ucfirst($chapter['source'])) ?></td>
                                            <td>
                                                <?php if ($chapter['source'] === 'database'): ?>
                                                    <button class="button edit-chapter" data-id="<?= esc_attr($chapter['id']) ?>">Edit</button>
                                                    <button class="button delete-chapter" data-id="<?= esc_attr($chapter['id']) ?>">Delete</button>
                                                <?php else: ?>
                                                    <span class="description">FileSys: Use FTP to modify</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php if ($chapter['source'] === 'database'): ?>
                                            <tr class="edit-chapter-form" data-chapter-id="<?= esc_attr($chapter['id']) ?>" style="display:none;">
                                                <td colspan="5">
                                                    <div class="edit-chapter-container">
                                                        <h4>Edit Chapter: <?= esc_html($chapter['title']) ?></h4>
                                                        <form class="update-chapter-form manga-reader-form" method="post" enctype="multipart/form-data">
                                                            <?php wp_nonce_field('manga_reader_update_chapter', 'manga_reader_update_nonce'); ?>
                                                            <input type="hidden" name="chapter_id" value="<?= esc_attr($chapter['id']) ?>" />
                                                            <div class="form-row">
                                                                <label for="edit_chapter_manga_<?= esc_attr($chapter['id']) ?>">Select Manga:</label>
                                                                <select name="chapter_manga" id="edit_chapter_manga_<?= esc_attr($chapter['id']) ?>" required>
                                                                    <option value="">Select a Manga</option>
                                                                    <?php foreach ($manga_options as $manga): ?>
                                                                        <option value="<?= esc_attr($manga) ?>" <?php selected($chapter['manga'], $manga); ?>>
                                                                            <?= esc_html($manga) ?>
                                                                        </option>
                                                                    <?php endforeach; ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-row">
                                                                <label for="edit_chapter_name_<?= esc_attr($chapter['id']) ?>">Chapter Name:</label>
                                                                <input type="text" name="chapter_name" id="edit_chapter_name_<?= esc_attr($chapter['id']) ?>" value="<?= esc_attr($chapter['title']) ?>" required />
                                                            </div>
                                                            <div class="form-row">
                                                                <label for="edit_chapter_date_<?= esc_attr($chapter['id']) ?>">Chapter Date (optional):</label>
                                                                <input type="date" name="chapter_date" id="edit_chapter_date_<?= esc_attr($chapter['id']) ?>" value="<?= esc_attr($chapter['date']) ?>">
                                                            </div>
                                                            <div class="form-row">
                                                                <label for="edit_chapter_images_<?= esc_attr($chapter['id']) ?>">Upload Images:</label>
                                                                <input type="button" class="edit-chapter-images-upload button" data-id="<?= esc_attr($chapter['id']) ?>" value="Select Images" />
                                                            </div>
                                                            <div class="edit-image-preview" data-id="<?= esc_attr($chapter['id']) ?>"></div>
                                                            <input type="hidden" name="chapter_image_ids" class="edit-chapter-image-ids" data-id="<?= esc_attr($chapter['id']) ?>" />
                                                            <div class="form-row">
                                                                <label for="edit_chapter_image_urls_<?= esc_attr($chapter['id']) ?>">Image URLs (one per line):</label>
                                                                <textarea name="chapter_image_urls" id="edit_chapter_image_urls_<?= esc_attr($chapter['id']) ?>" rows="5"></textarea>
                                                            </div>
                                                            <div class="form-row">
                                                                <button type="submit" class="button button-primary">Update Chapter</button>
                                                                <button type="button" class="button close-edit-form">Close</button>
                                                                <span class="spinner"></span>
                                                            </div>
                                                        </form>
                                                        <div class="chapter-update-results form-results"></div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if ($total_pages > 1): ?>
                                <div class="tablenav">
                                    <div class="tablenav-pages">
                                        <span class="displaying-num"><?= esc_html($total_chapters) ?> items</span>
                                        <span class="pagination-links">
                                            <?php
                                            $base_url = add_query_arg(['page' => 'manga-reader-settings'], admin_url('admin.php'));
                                            if ($selected_manga) {
                                                $base_url = add_query_arg(['manga_filter' => $selected_manga], $base_url);
                                            }

                                            if ($current_page > 1) {
                                                $first_url = remove_query_arg('paged', $base_url);
                                                echo '<a class="first-page button" href="' . esc_url($first_url) . '">«</a> ';
                                            } else {
                                                echo '<span class="first-page button disabled">«</span> ';
                                            }

                                            if ($current_page > 1) {
                                                $prev_page = $current_page - 1;
                                                $prev_url = $prev_page == 1 ? remove_query_arg('paged', $base_url) : add_query_arg(['paged' => $prev_page], $base_url);
                                                echo '<a class="prev-page button" href="' . esc_url($prev_url) . '">‹</a> ';
                                            } else {
                                                echo '<span class="prev-page button disabled">‹</span> ';
                                            }

                                            echo '<span class="paging-input">';
                                            echo sprintf(
                                                '%d of <span class="total-pages">%d</span>',
                                                esc_html($current_page),
                                                esc_html($total_pages)
                                            );
                                            echo '</span>';

                                            if ($current_page < $total_pages) {
                                                $next_url = add_query_arg(['paged' => $current_page + 1], $base_url);
                                                echo '<a class="next-page button" href="' . esc_url($next_url) . '">›</a> ';
                                            } else {
                                                echo '<span class="next-page button disabled">›</span> ';
                                            }

                                            if ($current_page < $total_pages) {
                                                $last_url = add_query_arg(['paged' => $total_pages], $base_url);
                                                echo '<a class="last-page button" href="' . esc_url($last_url) . '">»</a>';
                                            } else {
                                                echo '<span class="last-page button disabled">»</span>';
                                            }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p>No chapters found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Site Settings Section -->
            <div class="manga-reader-section">
                <h2 class="section-title">
                    <span class="dashicons dashicons-admin-settings"></span> Site Settings
                    <span class="toggle-section dashicons dashicons-arrow-down-alt2"></span>
                </h2>
                <div class="section-content">
                    <div class="manga-reader-card">
                        <h3>Site Announcement</h3>
                        <p>Display a custom announcement with an optional image on the frontend.</p>
                        <form method="post" action="options.php" class="manga-reader-form">
                            <?php settings_fields('manga_reader_announcement_group'); ?>
                            <div class="form-row">
                                <label for="announcement_title">Announcement Heading:</label>
                                <input type="text" name="announcement_title" id="announcement_title" value="<?= esc_attr(get_option('announcement_title')) ?>" />
                            </div>
                            <div class="form-row">
                                <label for="announcement_text">Announcement Text:</label>
                                <textarea name="announcement_text" id="announcement_text" rows="5"><?= esc_textarea(get_option('announcement_text')) ?></textarea>
                            </div>
                            <div class="form-row">
                                <label for="announcement_image_url">Announcement Image URL:</label>
                                <input type="text" name="announcement_image" id="announcement_image_url" value="<?= esc_attr(get_option('announcement_image')) ?>" placeholder="e.g., https://example.com/image.jpg" />
                            </div>
                            <div class="form-row">
                                <label>Upload Image:</label>
                                <input type="button" id="announcement_image_upload" class="button" value="Select Image" />
                                <div id="announcement_image_preview" class="image-preview">
                                    <?php
                                    $image_url = get_option('announcement_image');
                                    if ($image_url) {
                                        echo '<img src="' . esc_url($image_url) . '" style="max-width:100px; margin:5px;" />';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <button type="submit" class="button button-primary">Save Announcement</button>
                            </div>
                        </form>
                    </div>

                    <div class="manga-reader-card">
                        <h3>Theme Selection</h3>
                        <p>Select a theme to customize the appearance of the manga reader.</p>
                        <form method="post" action="options.php" class="manga-reader-form">
                            <?php settings_fields('manga_reader_settings_group'); ?>
                            <div class="form-row">
                                <label for="manga_reader_theme">Select Theme:</label>
                                <select name="manga_reader_theme" id="manga_reader_theme">
                                    <option value="default" <?php selected($selected_theme, 'default'); ?>>Default</option>
                                    <?php foreach ($available_themes as $theme): ?>
                                        <option value="<?= esc_attr($theme) ?>" <?php selected($selected_theme, $theme); ?>>
                                            <?= esc_html(ucfirst($theme)) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-row">
                                <button type="submit" class="button button-primary">Save Theme</button>
                            </div>
                        </form>
                    </div>

                    <div class="manga-reader-card">
                        <h3>Customize Colors</h3>
                        <p>Adjust the site's color scheme via the WordPress Customizer.</p>
                        <div class="form-row">
                            <a href="<?= esc_url(admin_url('customize.php?autofocus[section]=colors')); ?>" class="button button-secondary">Go to Customizer</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// ===============================
// UTILITIES
// ===============================
function manga_reader_get_latest_chapter($manga_name) {
    $base_path = ABSPATH . 'manga/';
    $actual = manga_reader_denormalize_name(manga_reader_normalize_name($manga_name), $base_path);
    $path = "$base_path$actual";

    $chapters = [];

    if (is_dir($path)) {
        $dirs = array_filter(glob("$path/*"), 'is_dir');
        foreach ($dirs as $dir) {
            $chapters[] = [
                'name' => basename($dir),
                'date' => filemtime($dir),
            ];
        }
    }

    $args = [
        'post_type' => 'manga_chapter',
        'post_status' => 'publish',
        'meta_query' => [
            [
                'key' => 'manga_name',
                'value' => $actual,
                'compare' => '=',
            ],
        ],
        'posts_per_page' => -1,
    ];
    $query = new WP_Query($args);
    while ($query->have_posts()) {
        $query->the_post();
        $chapters[] = [
            'name' => get_the_title(),
            'date' => get_post_time('U'),
        ];
    }
    wp_reset_postdata();

    if (!$chapters) return 'No chapters found';

    usort($chapters, fn($a, $b) => $b['date'] - $a['date']);
    return $chapters[0]['name'];
}

// ===============================
// THEME CUSTOMIZER: COLORS
// ===============================
function manga_reader_customize_register($wp_customize) {
    $wp_customize->add_section('manga_reader_colors', [
        'title'       => __('Color Settings', 'manga-reader-theme'),
        'description' => __('Customize the colors of your site.', 'manga-reader-theme'),
        'priority'    => 30,
    ]);

    $wp_customize->add_setting('accent_color', [
        'default'   => '#57b2ff',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'accent_color', [
        'label'    => __('Accent Color (Links, Hover, Buttons)', 'manga-reader-theme'),
        'section'  => 'manga_reader_colors',
        'settings' => 'accent_color',
    ]));

    $wp_customize->add_setting('background_color_custom', [
        'default'   => '#1a1a1a',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'background_color_custom', [
        'label'    => __('Site Background Color', 'manga-reader-theme'),
        'section'  => 'manga_reader_colors',
        'settings' => 'background_color_custom',
    ]));

    $wp_customize->add_setting('header_background_color', [
        'default'   => '#111111',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_background_color', [
        'label'    => __('Header Background Color', 'manga-reader-theme'),
        'section' => 'manga_reader_colors',
        'settings' => 'header_background_color',
    ]));

    $wp_customize->add_setting('menu_background_color', [
        'default'   => '#1e1e1e',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'menu_background_color', [
        'label'    => __('Menu Background Color', 'manga-reader-theme'),
        'section'  => 'manga_reader_colors',
        'settings' => 'menu_background_color',
    ]));

    $wp_customize->add_setting('glass_panel_color', [
        'default'   => 'rgba(35, 35, 35, 0.5)',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('glass_panel_color', [
        'type'     => 'text',
        'label'    => __('Glass Panel Tint (RGBA)', 'manga-reader-theme'),
        'section'  => 'manga_reader_colors',
        'settings' => 'glass_panel_color',
        'description' => __('Example: rgba(35,35,35,0.5)', 'manga-reader-theme'),
    ]);

    $wp_customize->add_setting('footer_text_color', [
        'default'   => '#aaaaaa',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_text_color', [
        'label'    => __('Footer Text Color', 'manga-reader-theme'),
        'section'  => 'manga_reader_colors',
        'settings' => 'footer_text_color',
    ]));

    $wp_customize->add_setting('body_text_color', [
        'default'   => '#e5e5e5',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'body_text_color', [
        'label'    => __('Body Text Color', 'manga-reader-theme'),
        'section'  => 'manga_reader_colors',
        'settings' => 'body_text_color',
    ]));
}
add_action('customize_register', 'manga_reader_customize_register');

function manga_reader_dynamic_styles() {
    ?>
    <style type="text/css">
        body {
            background-color: <?php echo esc_attr(get_theme_mod('background_color_custom', '#1a1a1a')); ?>;
            color: <?php echo esc_attr(get_theme_mod('body_text_color', '#e5e5e5')); ?>;
        }
        a {
            color: <?php echo esc_attr(get_theme_mod('accent_color', '#57b2ff')); ?>;
        }
        a:hover {
            color: <?php echo esc_attr(get_theme_mod('accent_color', '#57b2ff')); ?>;
        }
        .site-header {
            background-color: <?php echo esc_attr(get_theme_mod('header_background_color', '#111111')); ?>;
        }
        .main-navigation {
            background-color: <?php echo esc_attr(get_theme_mod('menu_background_color', '#1e1e1e')); ?>;
        }
        .announcement-section {
            background: <?php echo esc_attr(get_theme_mod('glass_panel_color', 'rgba(35, 35, 35, 0.5)')); ?>;
        }
        .site-footer {
            color: <?php echo esc_attr(get_theme_mod('footer_text_color', '#aaaaaa')); ?>;
        }
        .edit-chapter-form {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #ddd;
        }
    </style>
    <?php
}
add_action('wp_head', 'manga_reader_dynamic_styles');
?>