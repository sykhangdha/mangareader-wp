<?php
/**
 * Manga Theme Functions
 * Version: 6.2
 */

// Theme Setup
function manga_theme_setup() {
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'manga-theme'),
        'footer' => __('Footer Menu', 'manga-theme'),
    ));
    
    add_image_size('manga-cover', 300, 420, true);
    add_image_size('manga-cover-small', 180, 252, true);
}
add_action('after_setup_theme', 'manga_theme_setup');

// Register Manga Post Type
function register_manga_post_type() {
    $labels = array(
        'name'               => __('Manga', 'manga-theme'),
        'singular_name'      => __('Manga', 'manga-theme'),
        'menu_name'          => __('Manga', 'manga-theme'),
        'add_new'            => __('Add New Manga', 'manga-theme'),
        'add_new_item'       => __('Add New Manga', 'manga-theme'),
        'edit_item'          => __('Edit Manga', 'manga-theme'),
        'new_item'           => __('New Manga', 'manga-theme'),
        'view_item'          => __('View Manga', 'manga-theme'),
        'search_items'       => __('Search Manga', 'manga-theme'),
        'not_found'          => __('No manga found', 'manga-theme'),
        'not_found_in_trash' => __('No manga found in trash', 'manga-theme'),
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'manga', 'with_front' => false),
        'capability_type'     => 'post',
        'has_archive'         => true,
        'hierarchical'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-book',
        'supports'            => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'        => true,
    );
    
    register_post_type('manga', $args);
}
add_action('init', 'register_manga_post_type');

// Register Chapter Post Type
function register_chapter_post_type() {
    $labels = array(
        'name'               => __('Chapters', 'manga-theme'),
        'singular_name'      => __('Chapter', 'manga-theme'),
        'menu_name'          => __('Chapters', 'manga-theme'),
        'add_new'            => __('Add New Chapter', 'manga-theme'),
        'add_new_item'       => __('Add New Chapter', 'manga-theme'),
        'edit_item'          => __('Edit Chapter', 'manga-theme'),
        'new_item'           => __('New Chapter', 'manga-theme'),
        'view_item'          => __('View Chapter', 'manga-theme'),
        'search_items'       => __('Search Chapters', 'manga-theme'),
        'not_found'          => __('No chapters found', 'manga-theme'),
        'not_found_in_trash' => __('No chapters found in trash', 'manga-theme'),
        'all_items'          => __('All Chapters', 'manga-theme'),
    );
    
    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'query_var'           => true,
        'rewrite'             => array('slug' => 'chapter', 'with_front' => false),
        'capability_type'     => 'post',
        'has_archive'         => false,
        'hierarchical'        => false,
        'menu_position'       => 6,
        'menu_icon'           => 'dashicons-media-text',
        'supports'            => array('title', 'editor'),
        'show_in_rest'        => true,
    );
    
    register_post_type('chapter', $args);
}
add_action('init', 'register_chapter_post_type');

// ============================================
// HELPER FUNCTIONS FOR EXTRACTING VOLUME & CHAPTER NUMBERS
// ============================================

// Extract volume number from title
function extract_volume_number($title) {
    preg_match('/Vol\.?\s*(\d+(?:\.\d+)?)/i', $title, $matches);
    return isset($matches[1]) ? floatval($matches[1]) : 0;
}

// Extract chapter number from title
function extract_chapter_number($title) {
    preg_match('/Ch\.?\s*(\d+(?:\.\d+)?)/i', $title, $matches);
    return isset($matches[1]) ? floatval($matches[1]) : 0;
}

// Get combined sort key (volume * 1000 + chapter for proper ordering)
function get_chapter_sort_key($chapter_id, $title) {
    $volume = extract_volume_number($title);
    $chapter = extract_chapter_number($title);
    
    // If no volume found, just use chapter number
    if ($volume == 0) {
        return $chapter;
    }
    
    // Combine volume and chapter: Vol. 3 Ch. 12 = 3012, Vol. 2 Ch. 10 = 2010
    return ($volume * 1000) + $chapter;
}

// Format chapter display name
function format_chapter_display_name($title) {
    $volume = extract_volume_number($title);
    $chapter = extract_chapter_number($title);
    
    if ($volume > 0) {
        return "Vol. {$volume} Ch. {$chapter}";
    }
    
    return "Ch. {$chapter}";
}

// Register Genre Taxonomy
function register_genre_taxonomy() {
    $labels = array(
        'name'              => __('Genres', 'manga-theme'),
        'singular_name'     => __('Genre', 'manga-theme'),
        'search_items'      => __('Search Genres', 'manga-theme'),
        'all_items'         => __('All Genres', 'manga-theme'),
        'edit_item'         => __('Edit Genre', 'manga-theme'),
        'update_item'       => __('Update Genre', 'manga-theme'),
        'add_new_item'      => __('Add New Genre', 'manga-theme'),
        'new_item_name'     => __('New Genre Name', 'manga-theme'),
        'menu_name'         => __('Genres', 'manga-theme'),
    );
    
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'genre'),
        'show_in_rest'      => true,
    );
    
    register_taxonomy('genre', array('manga'), $args);
}
add_action('init', 'register_genre_taxonomy');

// Register Status Taxonomy
function register_status_taxonomy() {
    $labels = array(
        'name'              => __('Status', 'manga-theme'),
        'singular_name'     => __('Status', 'manga-theme'),
        'search_items'      => __('Search Status', 'manga-theme'),
        'all_items'         => __('All Statuses', 'manga-theme'),
        'edit_item'         => __('Edit Status', 'manga-theme'),
        'update_item'       => __('Update Status', 'manga-theme'),
        'add_new_item'      => __('Add New Status', 'manga-theme'),
        'new_item_name'     => __('New Status Name', 'manga-theme'),
        'menu_name'         => __('Status', 'manga-theme'),
    );
    
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'status'),
        'show_in_rest'      => true,
    );
    
    register_taxonomy('manga_status', array('manga'), $args);
    
    $default_statuses = array('Ongoing', 'Completed', 'Hiatus', 'Cancelled');
    foreach ($default_statuses as $status) {
        if (!term_exists($status, 'manga_status')) {
            wp_insert_term($status, 'manga_status');
        }
    }
}
add_action('init', 'register_status_taxonomy');

// Manga Details Meta Box
function add_manga_details_meta_box() {
    add_meta_box(
        'manga_details',
        __('Manga Details', 'manga-theme'),
        'render_manga_details_meta_box',
        'manga',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_manga_details_meta_box');

function render_manga_details_meta_box($post) {
    wp_nonce_field('manga_details_save', 'manga_details_nonce');
    
    $author = get_post_meta($post->ID, '_manga_author', true);
    $artist = get_post_meta($post->ID, '_manga_artist', true);
    $year = get_post_meta($post->ID, '_manga_year', true);
    ?>
    <div style="margin-bottom: 15px;">
        <label style="display: inline-block; width: 150px; font-weight: 600;">Author:</label>
        <input type="text" name="manga_author" value="<?php echo esc_attr($author); ?>" style="width: 300px; padding: 5px;">
    </div>
    <div style="margin-bottom: 15px;">
        <label style="display: inline-block; width: 150px; font-weight: 600;">Artist:</label>
        <input type="text" name="manga_artist" value="<?php echo esc_attr($artist); ?>" style="width: 300px; padding: 5px;">
    </div>
    <div style="margin-bottom: 15px;">
        <label style="display: inline-block; width: 150px; font-weight: 600;">Year:</label>
        <input type="number" name="manga_year" value="<?php echo esc_attr($year); ?>" style="width: 300px; padding: 5px;">
    </div>
    <?php
}

function save_manga_details_meta_box($post_id) {
    if (!isset($_POST['manga_details_nonce']) || !wp_verify_nonce($_POST['manga_details_nonce'], 'manga_details_save')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['manga_author'])) {
        update_post_meta($post_id, '_manga_author', sanitize_text_field($_POST['manga_author']));
    }
    if (isset($_POST['manga_artist'])) {
        update_post_meta($post_id, '_manga_artist', sanitize_text_field($_POST['manga_artist']));
    }
    if (isset($_POST['manga_year'])) {
        update_post_meta($post_id, '_manga_year', sanitize_text_field($_POST['manga_year']));
    }
}
add_action('save_post_manga', 'save_manga_details_meta_box');

// Chapter Connection Meta Box
function add_chapter_connection_meta_box() {
    add_meta_box(
        'chapter_connection',
        __('Connect to Manga', 'manga-theme'),
        'render_chapter_connection_meta_box',
        'chapter',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'add_chapter_connection_meta_box');

function render_chapter_connection_meta_box($post) {
    wp_nonce_field('chapter_connection_save', 'chapter_connection_nonce');
    
    $connected_manga = get_post_meta($post->ID, 'connected_manga_id', true);
    $chapter_number = get_post_meta($post->ID, 'chapter_number', true);
    $volume_number = get_post_meta($post->ID, 'volume_number', true);
    
    $manga_list = get_posts(array(
        'post_type' => 'manga',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));
    ?>
    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: 600; margin-bottom: 5px;">Select Manga:</label>
        <select name="connected_manga" style="width: 100%; padding: 8px;">
            <option value="">-- Select Manga --</option>
            <?php foreach ($manga_list as $manga): ?>
                <option value="<?php echo $manga->ID; ?>" <?php selected($connected_manga, $manga->ID); ?>>
                    <?php echo esc_html($manga->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: 600; margin-bottom: 5px;">Volume Number:</label>
        <input type="number" name="volume_number" value="<?php echo esc_attr($volume_number); ?>" step="1" style="width: 100%; padding: 8px;" placeholder="Leave empty if no volume">
    </div>
    <div style="margin-bottom: 15px;">
        <label style="display: block; font-weight: 600; margin-bottom: 5px;">Chapter Number:</label>
        <input type="number" name="chapter_number" value="<?php echo esc_attr($chapter_number); ?>" step="0.1" style="width: 100%; padding: 8px;">
    </div>
    <?php
}

function save_chapter_connection_meta_box($post_id) {
    if (!isset($_POST['chapter_connection_nonce']) || !wp_verify_nonce($_POST['chapter_connection_nonce'], 'chapter_connection_save')) {
        return;
    }
    
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    if (isset($_POST['connected_manga']) && !empty($_POST['connected_manga'])) {
        $manga_id = intval($_POST['connected_manga']);
        update_post_meta($post_id, 'connected_manga_id', $manga_id);
        
        $volume_num = isset($_POST['volume_number']) ? intval($_POST['volume_number']) : 0;
        $chapter_num = isset($_POST['chapter_number']) ? floatval($_POST['chapter_number']) : 0;
        
        if ($volume_num > 0) {
            update_post_meta($post_id, 'volume_number', $volume_num);
        }
        if ($chapter_num > 0) {
            update_post_meta($post_id, 'chapter_number', $chapter_num);
        }
        
        // Generate title
        $manga_title = get_the_title($manga_id);
        if ($volume_num > 0) {
            $new_title = $manga_title . ' - Vol. ' . $volume_num . ' Ch. ' . $chapter_num;
        } else {
            $new_title = $manga_title . ' - Chapter ' . $chapter_num;
        }
        
        remove_action('save_post_chapter', 'save_chapter_connection_meta_box');
        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $new_title
        ));
        add_action('save_post_chapter', 'save_chapter_connection_meta_box');
    }
}
add_action('save_post_chapter', 'save_chapter_connection_meta_box');

// Chapter Images Meta Box
function add_chapter_images_meta_box() {
    add_meta_box(
        'chapter_images',
        __('Chapter Images', 'manga-theme'),
        'render_chapter_images_meta_box',
        'chapter',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_chapter_images_meta_box');

function render_chapter_images_meta_box($post) {
    wp_nonce_field('chapter_images_save', 'chapter_images_nonce');
    
    $image_links = get_post_meta($post->ID, 'image_links', true);
    wp_enqueue_media();
    ?>
    <style>
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 20px 0;
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            background: #fafafa;
            border-radius: 5px;
        }
        .image-preview-item {
            position: relative;
            width: 120px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            background: white;
        }
        .image-preview-item img {
            width: 100%;
            height: 120px;
            object-fit: cover;
        }
        .image-preview-item .remove-image {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(220,53,69,0.9);
            color: white;
            border: none;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            cursor: pointer;
        }
        .image-urls-textarea {
            width: 100%;
            min-height: 200px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 10px;
        }
    </style>
    
    <div>
        <p><strong>Option 1: Upload Images</strong></p>
        <button type="button" id="upload-images-btn" class="button button-primary">Select Images</button>
        <input type="file" id="image-files" multiple accept="image/*" style="display:none">
        <div id="image-preview" class="image-preview-container">
            <?php 
            if (!empty($image_links)) {
                $images = explode("\n", $image_links);
                foreach ($images as $image_url) {
                    $image_url = trim($image_url);
                    if (!empty($image_url)) {
                        echo '<div class="image-preview-item" data-url="' . esc_attr($image_url) . '">';
                        echo '<img src="' . esc_url($image_url) . '">';
                        echo '<button type="button" class="remove-image" onclick="removeImage(this)">×</button>';
                        echo '</div>';
                    }
                }
            }
            ?>
        </div>
        
        <p><strong>Option 2: Enter Image URLs</strong></p>
        <textarea id="image-urls" class="image-urls-textarea" placeholder="Enter one image URL per line"><?php echo esc_textarea($image_links); ?></textarea>
        
        <input type="hidden" id="final-image-links" name="final_image_links" value="<?php echo esc_attr($image_links); ?>">
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#upload-images-btn').on('click', function() {
            $('#image-files').trigger('click');
        });
        
        $('#image-files').on('change', function(e) {
            var files = e.target.files;
            var formData = new FormData();
            formData.append('action', 'upload_chapter_images');
            formData.append('nonce', '<?php echo wp_create_nonce("chapter_upload_nonce"); ?>');
            
            for (var i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        $.each(response.data.images, function(index, image) {
                            addImageToPreview(image.url);
                        });
                        updateImageLinksField();
                    } else {
                        alert('Upload failed: ' + response.data);
                    }
                }
            });
        });
        
        window.addImageToPreview = function(imageUrl) {
            var previewHtml = '<div class="image-preview-item" data-url="' + imageUrl + '">' +
                '<img src="' + imageUrl + '">' +
                '<button type="button" class="remove-image" onclick="removeImage(this)">×</button>' +
                '</div>';
            $('#image-preview').append(previewHtml);
        };
        
        window.removeImage = function(btn) {
            $(btn).closest('.image-preview-item').remove();
            updateImageLinksField();
        };
        
        function updateImageLinksField() {
            var imageUrls = [];
            $('#image-preview .image-preview-item').each(function() {
                var url = $(this).data('url');
                if (url) {
                    imageUrls.push(url);
                }
            });
            $('#image-urls').val(imageUrls.join('\n'));
            $('#final-image-links').val(imageUrls.join('\n'));
        }
        
        $('#image-urls').on('change keyup', function() {
            $('#final-image-links').val($(this).val());
        });
        
        $('#publish, #save-post').on('click', function() {
            $('#final-image-links').val($('#image-urls').val());
        });
    });
    </script>
    <?php
}

function save_chapter_images_meta_box($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    if (isset($_POST['final_image_links'])) {
        update_post_meta($post_id, 'image_links', sanitize_textarea_field($_POST['final_image_links']));
    }
}
add_action('save_post_chapter', 'save_chapter_images_meta_box');

// AJAX Upload Handler
function ajax_upload_chapter_images() {
    check_ajax_referer('chapter_upload_nonce', 'nonce');
    
    if (!current_user_can('upload_files')) {
        wp_send_json_error('Permission denied');
    }
    
    $uploaded_images = array();
    
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    
    foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
        if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
            $file = array(
                'name' => $_FILES['files']['name'][$key],
                'type' => $_FILES['files']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['files']['error'][$key],
                'size' => $_FILES['files']['size'][$key]
            );
            
            $upload = wp_handle_upload($file, array('test_form' => false));
            
            if (!isset($upload['error'])) {
                $attachment = array(
                    'post_mime_type' => $upload['type'],
                    'post_title' => sanitize_file_name($file['name']),
                    'post_content' => '',
                    'post_status' => 'inherit'
                );
                
                $attachment_id = wp_insert_attachment($attachment, $upload['file']);
                $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                wp_update_attachment_metadata($attachment_id, $attachment_data);
                
                $uploaded_images[] = array(
                    'url' => wp_get_attachment_url($attachment_id),
                    'id' => $attachment_id
                );
            }
        }
    }
    
    if (!empty($uploaded_images)) {
        wp_send_json_success(array('images' => $uploaded_images));
    } else {
        wp_send_json_error('No images uploaded');
    }
}
add_action('wp_ajax_upload_chapter_images', 'ajax_upload_chapter_images');

// ============================================
// LOCAL FOLDER IMPORT FEATURE (FIXED - Uses ABSPATH)
// ============================================

// Add Import from Folder menu page
function add_folder_import_page() {
    add_submenu_page(
        'edit.php?post_type=manga',
        __('Import from Folder', 'manga-theme'),
        __('Import from Folder', 'manga-theme'),
        'manage_options',
        'folder-import',
        'render_folder_import_page'
    );
}
add_action('admin_menu', 'add_folder_import_page');

// Define the base manga directory path - FIXED to use ABSPATH (WordPress root)
function get_manga_base_directory() {
    // Use ABSPATH which points to WordPress root directory (where wp-config.php is)
    $base_path = ABSPATH . 'manga/';
    
    // Create directory if it doesn't exist
    if (!file_exists($base_path)) {
        wp_mkdir_p($base_path);
    }
    
    return $base_path;
}

// Get the URL for manga images
function get_manga_base_url() {
    return site_url('/manga/');
}

// Helper function to mark chapter as imported
function mark_chapter_as_imported($chapter_path) {
    $option_name = 'imported_chapters_' . md5($chapter_path);
    update_option($option_name, array(
        'path' => $chapter_path,
        'imported_at' => current_time('mysql'),
        'status' => 'imported'
    ));
}

// Helper function to check if chapter is already imported
function is_chapter_imported($chapter_path) {
    $option_name = 'imported_chapters_' . md5($chapter_path);
    $imported = get_option($option_name);
    return !empty($imported);
}

// Clear imported chapters cache
function clear_imported_chapters_cache() {
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'imported_chapters_%'");
}

// Render the import page
function render_folder_import_page() {
    $base_path = get_manga_base_directory();
    $base_url = get_manga_base_url();
    $current_manga = isset($_GET['manga']) ? sanitize_text_field($_GET['manga']) : '';
    $import_status = '';
    
    // Handle clear cache request
    if (isset($_GET['clear_cache'])) {
        clear_imported_chapters_cache();
        $import_status = '<div class="notice notice-success"><p>✓ Import cache cleared! Refresh the page to see all chapters again.</p></div>';
    }
    
    // Handle single chapter import
    if (isset($_POST['import_single_chapter']) && isset($_POST['chapter_path'])) {
        $chapter_path = sanitize_text_field($_POST['chapter_path']);
        $selected_manga = sanitize_text_field($_POST['manga_name']);
        $chapter_name = sanitize_text_field($_POST['chapter_name']);
        
        $result = import_chapter_from_folder($chapter_path, $selected_manga, $chapter_name);
        if ($result['success']) {
            $import_status = '<div class="notice notice-success"><p>✓ ' . $result['message'] . '</p></div>';
        } else {
            $import_status = '<div class="notice notice-error"><p>✗ ' . $result['message'] . '</p></div>';
        }
    }
    
    // Handle bulk chapter import
    if (isset($_POST['import_bulk_chapters']) && isset($_POST['selected_chapters'])) {
        $selected_chapters = $_POST['selected_chapters'];
        $selected_manga = sanitize_text_field($_POST['bulk_manga_name']);
        $imported_count = 0;
        $failed_count = 0;
        
        foreach ($selected_chapters as $chapter_path) {
            $chapter_name = basename($chapter_path);
            $result = import_chapter_from_folder($chapter_path, $selected_manga, $chapter_name);
            if ($result['success']) {
                $imported_count++;
            } else {
                $failed_count++;
            }
        }
        
        $import_status = '<div class="notice notice-success"><p>✓ Imported ' . $imported_count . ' chapters. Failed: ' . $failed_count . '</p></div>';
    }
    
    // Handle manga creation from folder
    if (isset($_POST['create_manga_from_folder']) && isset($_POST['manga_folder_path'])) {
        $manga_folder_path = sanitize_text_field($_POST['manga_folder_path']);
        $manga_name = basename($manga_folder_path);
        
        $existing_manga = get_posts(array(
            'post_type' => 'manga',
            'title' => $manga_name,
            'posts_per_page' => 1
        ));
        
        if (empty($existing_manga)) {
            $manga_id = wp_insert_post(array(
                'post_title' => $manga_name,
                'post_type' => 'manga',
                'post_status' => 'publish',
                'post_content' => 'Imported from folder: ' . $manga_folder_path
            ));
            
            if ($manga_id && !is_wp_error($manga_id)) {
                $import_status = '<div class="notice notice-success"><p>✓ Created manga: ' . $manga_name . '</p></div>';
            } else {
                $import_status = '<div class="notice notice-error"><p>✗ Failed to create manga: ' . $manga_name . '</p></div>';
            }
        } else {
            $import_status = '<div class="notice notice-warning"><p>⚠ Manga already exists: ' . $manga_name . '</p></div>';
        }
    }
    
    // Clear PHP cache before scanning
    clearstatcache();
    
    // Scan for manga folders
    $manga_folders = array();
    if (is_dir($base_path)) {
        $items = scandir($base_path);
        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..' && is_dir($base_path . $item)) {
                $manga_folders[] = $item;
            }
        }
    }
    ?>
    <div class="wrap">
        <h1>📁 Import from Folder</h1>
        <p>Import manga chapters from your local folder structure.</p>
        
        <div class="notice notice-info">
            <p><strong>📂 Folder Location:</strong> <code><?php echo esc_html($base_path); ?></code></p>
            <p><strong>🔗 Public URL:</strong> <code><?php echo esc_html($base_url); ?></code></p>
            <p><strong>📁 Folder Structure:</strong><br>
            <code><?php echo esc_html($base_path); ?><strong>Manga Name/</strong>Chapter Name/01.jpg</code></p>
        </div>
        
        <?php echo $import_status; ?>
        
        <div style="margin-bottom: 20px;">
            <a href="<?php echo add_query_arg(array('page' => 'folder-import', 'clear_cache' => '1'), admin_url('admin.php')); ?>" 
               class="button button-secondary" 
               onclick="return confirm('Clear import cache? This will allow you to re-import chapters that may have had issues.');">
                ⟳ Clear Import Cache
            </a>
            <span class="description">Use this if chapters are showing 0 images after multiple imports</span>
        </div>
        
        <div class="folder-import-container" style="display: flex; gap: 30px; margin-top: 20px; flex-wrap: wrap;">
            <!-- Left Panel: Manga List -->
            <div class="manga-list-panel" style="flex: 1; min-width: 250px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2>📁 Manga Folders</h2>
                
                <?php if (empty($manga_folders)): ?>
                    <p class="description">No manga folders found in <?php echo esc_html($base_path); ?></p>
                    <div class="notice notice-warning">
                        <p><strong>How to set up:</strong></p>
                        <ol>
                            <li>Using FTP or file manager, create folder: <code><?php echo esc_html($base_path); ?>Yotsubato/</code></li>
                            <li>Create chapter folder: <code><?php echo esc_html($base_path); ?>Yotsubato/Chapter 1/</code></li>
                            <li>Add images: <code><?php echo esc_html($base_path); ?>Yotsubato/Chapter 1/01.jpg</code></li>
                            <li>Refresh this page</li>
                        </ol>
                    </div>
                <?php else: ?>
                    <ul style="list-style: none; padding: 0;">
                        <?php foreach ($manga_folders as $manga): 
                            $folder_path = $base_path . $manga;
                            $chapter_count = 0;
                            if (is_dir($folder_path)) {
                                clearstatcache();
                                $items = scandir($folder_path);
                                foreach ($items as $item) {
                                    if ($item !== '.' && $item !== '..' && is_dir($folder_path . '/' . $item)) {
                                        // Check if not already imported
                                        $chapter_path_check = $folder_path . '/' . $item;
                                        if (!is_chapter_imported($chapter_path_check)) {
                                            $chapter_count++;
                                        }
                                    }
                                }
                            }
                        ?>
                            <li style="margin-bottom: 10px;">
                                <a href="<?php echo add_query_arg(array('page' => 'folder-import', 'manga' => urlencode($manga)), admin_url('admin.php')); ?>" 
                                   style="display: block; padding: 10px; background: <?php echo ($current_manga === $manga) ? '#e94560' : '#f5f5f5'; ?>; color: <?php echo ($current_manga === $manga) ? 'white' : '#333'; ?>; text-decoration: none; border-radius: 6px;">
                                    📖 <strong><?php echo esc_html($manga); ?></strong>
                                    <span style="float: right; font-size: 12px;">📄 <?php echo $chapter_count; ?> chapters</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
            
            <!-- Right Panel: Chapters List -->
            <div class="chapters-list-panel" style="flex: 2; min-width: 400px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <?php if ($current_manga): 
                    $manga_path = $base_path . $current_manga;
                    $chapters = array();
                    
                    if (is_dir($manga_path)) {
                        clearstatcache();
                        $items = scandir($manga_path);
                        foreach ($items as $item) {
                            if ($item !== '.' && $item !== '..' && is_dir($manga_path . '/' . $item)) {
                                $chapter_path = $manga_path . '/' . $item;
                                
                                // Skip if already imported
                                if (is_chapter_imported($chapter_path)) {
                                    continue;
                                }
                                
                                $images = array();
                                $image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
                                
                                if (is_dir($chapter_path)) {
                                    clearstatcache();
                                    $scan_images = scandir($chapter_path);
                                    foreach ($scan_images as $img) {
                                        $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
                                        if (in_array($ext, $image_extensions)) {
                                            // Verify file exists and is readable
                                            $img_path = $chapter_path . '/' . $img;
                                            if (file_exists($img_path) && is_readable($img_path)) {
                                                $images[] = $img;
                                            }
                                        }
                                    }
                                }
                                
                                $chapters[] = array(
                                    'name' => $item,
                                    'path' => $chapter_path,
                                    'image_count' => count($images),
                                    'images' => $images
                                );
                            }
                        }
                        
                        // Sort chapters naturally
                        usort($chapters, function($a, $b) {
                            preg_match('/(\d+(?:\.\d+)?)/', $a['name'], $matches_a);
                            preg_match('/(\d+(?:\.\d+)?)/', $b['name'], $matches_b);
                            $num_a = isset($matches_a[1]) ? floatval($matches_a[1]) : 0;
                            $num_b = isset($matches_b[1]) ? floatval($matches_b[1]) : 0;
                            return $num_a - $num_b;
                        });
                    }
                    ?>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
                        <h2>📖 <?php echo esc_html($current_manga); ?></h2>
                        <div>
                            <button id="selectAllChapters" class="button">Select All</button>
                            <button id="deselectAllChapters" class="button">Deselect All</button>
                            <button id="refreshScanBtn" class="button button-secondary">⟳ Refresh Scan</button>
                        </div>
                    </div>
                    
                    <?php if (empty($chapters)): ?>
                        <p>No new chapters found in this manga folder. All chapters may have been imported already.</p>
                        <p><a href="<?php echo add_query_arg(array('page' => 'folder-import', 'clear_cache' => '1'), admin_url('admin.php')); ?>" class="button">Clear Import Cache</a></p>
                    <?php else: ?>
                        <form method="post" id="bulkImportForm">
                            <input type="hidden" name="bulk_manga_name" value="<?php echo esc_attr($current_manga); ?>">
                            <div style="max-height: 500px; overflow-y: auto;">
                                <table class="wp-list-table widefat fixed striped">
                                    <thead>
                                        <tr>
                                            <th width="50"><input type="checkbox" id="selectAllCheckbox"></th>
                                            <th>Chapter Name</th>
                                            <th>Images Found</th>
                                            <th width="120">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($chapters as $chapter): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_chapters[]" value="<?php echo esc_attr($chapter['path']); ?>" 
                                                           class="chapter-checkbox">
                                                </td>
                                                <td>
                                                    <strong><?php echo esc_html($chapter['name']); ?></strong>
                                                  </td>
                                                  <td><?php echo $chapter['image_count']; ?> images</td>
                                                  <td>
                                                    <form method="post" style="display: inline;">
                                                        <input type="hidden" name="chapter_path" value="<?php echo esc_attr($chapter['path']); ?>">
                                                        <input type="hidden" name="manga_name" value="<?php echo esc_attr($current_manga); ?>">
                                                        <input type="hidden" name="chapter_name" value="<?php echo esc_attr($chapter['name']); ?>">
                                                        <button type="submit" name="import_single_chapter" class="button button-small" 
                                                                onclick="return confirm('Import chapter <?php echo esc_js($chapter['name']); ?>?');">
                                                            Import
                                                        </button>
                                                    </form>
                                                  </td>
                                              </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div style="margin-top: 20px;">
                                <button type="submit" name="import_bulk_chapters" class="button button-primary" 
                                        onclick="return confirm('Import selected chapters? This may take a while.');">
                                    📥 Import Selected Chapters
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd;">
                        <h3>📚 Create Manga Entry</h3>
                        <form method="post">
                            <input type="hidden" name="manga_folder_path" value="<?php echo esc_attr($manga_path); ?>">
                            <button type="submit" name="create_manga_from_folder" class="button" 
                                    onclick="return confirm('Create manga "<?php echo esc_js($current_manga); ?>" from this folder?');">
                                Create Manga Entry
                            </button>
                            <span class="description">Creates a manga entry in WordPress for this series.</span>
                        </form>
                    </div>
                    
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 50px;">Select a manga from the left panel to view its chapters.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#selectAllChapters').on('click', function(e) {
            e.preventDefault();
            $('.chapter-checkbox:not(:disabled)').prop('checked', true);
        });
        
        $('#deselectAllChapters').on('click', function(e) {
            e.preventDefault();
            $('.chapter-checkbox').prop('checked', false);
        });
        
        $('#selectAllCheckbox').on('change', function() {
            $('.chapter-checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
        });
        
        $('#refreshScanBtn').on('click', function(e) {
            e.preventDefault();
            location.reload();
        });
    });
    </script>
    <?php
}

// Function to import a chapter from folder - FIXED VERSION
function import_chapter_from_folder($chapter_path, $manga_name, $chapter_name) {
    // Check if already imported
    if (is_chapter_imported($chapter_path)) {
        return array('success' => false, 'message' => 'Chapter already imported: ' . $chapter_name);
    }
    
    // Check if manga exists, if not create it
    $existing_manga = get_posts(array(
        'post_type' => 'manga',
        'title' => $manga_name,
        'posts_per_page' => 1
    ));
    
    if (empty($existing_manga)) {
        $manga_id = wp_insert_post(array(
            'post_title' => $manga_name,
            'post_type' => 'manga',
            'post_status' => 'publish',
            'post_content' => 'Imported from folder: ' . $chapter_path
        ));
        
        if (is_wp_error($manga_id)) {
            return array('success' => false, 'message' => 'Failed to create manga: ' . $manga_name);
        }
    } else {
        $manga_id = $existing_manga[0]->ID;
    }
    
    // Check if chapter already exists in database
    $existing_chapter = get_posts(array(
        'post_type' => 'chapter',
        'title' => $manga_name . ' - ' . $chapter_name,
        'posts_per_page' => 1
    ));
    
    if (!empty($existing_chapter)) {
        mark_chapter_as_imported($chapter_path);
        return array('success' => false, 'message' => 'Chapter already exists: ' . $chapter_name);
    }
    
    // Clear cache before reading files
    clearstatcache();
    
    // Get all images from chapter folder
    $images = array();
    $image_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    if (is_dir($chapter_path)) {
        $files = scandir($chapter_path);
        sort($files);
        
        foreach ($files as $file) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (in_array($ext, $image_extensions)) {
                $file_path = $chapter_path . '/' . $file;
                
                // Verify file exists and is readable
                if (!file_exists($file_path) || !is_readable($file_path)) {
                    continue;
                }
                
                // Prepare file for upload
                $file_array = array(
                    'name' => $file,
                    'tmp_name' => $file_path,
                    'error' => 0,
                    'size' => filesize($file_path)
                );
                
                // Upload to WordPress media library
                $upload = wp_handle_sideload($file_array, array('test_form' => false));
                
                if (!isset($upload['error'])) {
                    $attachment = array(
                        'post_mime_type' => mime_content_type($file_path),
                        'post_title' => sanitize_file_name($file),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );
                    
                    $attachment_id = wp_insert_attachment($attachment, $upload['file']);
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attachment_data = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                    wp_update_attachment_metadata($attachment_id, $attachment_data);
                    
                    $images[] = wp_get_attachment_url($attachment_id);
                }
            }
        }
    }
    
    if (empty($images)) {
        return array('success' => false, 'message' => 'No images found in chapter folder: ' . $chapter_name);
    }
    
    // Extract volume and chapter numbers from folder name
    preg_match('/(?:Vol\.?\s*(\d+))?\s*(?:Ch\.?\s*(\d+(?:\.\d+)?))/i', $chapter_name, $matches);
    $volume_number = isset($matches[1]) ? intval($matches[1]) : 0;
    $chapter_number = isset($matches[2]) ? floatval($matches[2]) : 0;
    
    // Create chapter post with volume and chapter numbers
    $chapter_id = wp_insert_post(array(
        'post_title' => $manga_name . ' - ' . $chapter_name,
        'post_type' => 'chapter',
        'post_status' => 'publish',
        'meta_input' => array(
            'connected_manga_id' => $manga_id,
            'volume_number' => $volume_number,
            'chapter_number' => $chapter_number,
            'image_links' => implode("\n", $images),
            'imported_from_path' => $chapter_path,
            'import_date' => current_time('mysql')
        )
    ));
    
    if ($chapter_id && !is_wp_error($chapter_id)) {
        // Mark as imported to prevent re-importing
        mark_chapter_as_imported($chapter_path);
        
        return array(
            'success' => true, 
            'message' => 'Imported: ' . $chapter_name . ' (' . count($images) . ' images)',
            'chapter_id' => $chapter_id
        );
    } else {
        return array('success' => false, 'message' => 'Failed to create chapter: ' . $chapter_name);
    }
}

// ============================================
// AJAX FILTERS FOR MANGA ARCHIVE (UPDATED SORTING)
// ============================================

// AJAX Filter for Homepage
function ajax_filter_manga_home() {
    check_ajax_referer('manga_home_filter_nonce', 'nonce');
    
    $sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : 'recent';
    
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => -1
    );
    
    switch ($sort_by) {
        case 'alphabetical':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'recent':
        default:
            $args['orderby'] = 'modified';
            $args['order'] = 'DESC';
            break;
    }
    
    $manga_query = new WP_Query($args);
    output_manga_grid($manga_query->posts);
    wp_die();
}
add_action('wp_ajax_filter_manga_home', 'ajax_filter_manga_home');
add_action('wp_ajax_nopriv_filter_manga_home', 'ajax_filter_manga_home');

// Helper function to get latest chapter date for a manga
function get_latest_chapter_date($manga_id) {
    $latest_chapter = get_posts(array(
        'post_type' => 'chapter',
        'posts_per_page' => 1,
        'meta_key' => 'connected_manga_id',
        'meta_value' => $manga_id,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC'
    ));
    
    if (!empty($latest_chapter)) {
        return $latest_chapter[0]->post_date;
    }
    
    return get_the_date('Y-m-d H:i:s', $manga_id);
}

// Helper function to get sorted chapters for a manga (with volume support)
function get_sorted_chapters_for_manga($manga_id) {
    $chapters = get_posts(array(
        'post_type' => 'chapter',
        'posts_per_page' => -1,
        'meta_key' => 'connected_manga_id',
        'meta_value' => $manga_id,
        'post_status' => 'publish'
    ));
    
    // Sort by volume number first, then chapter number
    usort($chapters, function($a, $b) {
        $vol_a = get_post_meta($a->ID, 'volume_number', true) ?: 0;
        $vol_b = get_post_meta($b->ID, 'volume_number', true) ?: 0;
        $chap_a = get_post_meta($a->ID, 'chapter_number', true) ?: 0;
        $chap_b = get_post_meta($b->ID, 'chapter_number', true) ?: 0;
        
        if ($vol_a != $vol_b) {
            return $vol_b - $vol_a; // Higher volume first
        }
        return $chap_b - $chap_a; // Higher chapter first within same volume
    });
    
    return $chapters;
}

// Helper function to output manga grid for custom sorted arrays
function output_manga_grid_custom($manga_list) {
    if (empty($manga_list)) {
        echo '<p class="no-results">No manga found.</p>';
        return;
    }
    
    echo '<div class="manga-grid">';
    foreach ($manga_list as $manga) {
        $manga_id = $manga->ID;
        $manga_title = get_the_title($manga_id);
        $manga_cover = has_post_thumbnail($manga_id) ? get_the_post_thumbnail_url($manga_id, 'manga-cover-small') : 'https://via.placeholder.com/180x252?text=No+Cover';
        
        // Get sorted recent chapters
        $all_chapters = get_sorted_chapters_for_manga($manga_id);
        $recent_chapters = array_slice($all_chapters, 0, 3);
        ?>
        <div class="manga-item">
            <div class="manga-item-cover">
                <a href="<?php echo get_permalink($manga_id); ?>">
                    <img src="<?php echo esc_url($manga_cover); ?>" alt="<?php echo esc_attr($manga_title); ?>">
                    <div class="manga-item-overlay">
                        <span class="view-details">View Details</span>
                    </div>
                </a>
                <?php if (!empty($recent_chapters)): 
                    $latest = $recent_chapters[0];
                    $vol_num = get_post_meta($latest->ID, 'volume_number', true);
                    $chap_num = get_post_meta($latest->ID, 'chapter_number', true);
                    $display = $vol_num ? "Vol. {$vol_num} Ch. {$chap_num}" : "Ch. {$chap_num}";
                ?>
                    <div class="latest-chapter-badge">
                        <?php echo esc_html($display); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="manga-item-info">
                <h3 class="manga-item-title">
                    <a href="<?php echo get_permalink($manga_id); ?>"><?php echo esc_html($manga_title); ?></a>
                </h3>
                <?php if (!empty($recent_chapters)) : ?>
                    <div class="manga-item-chapters">
                        <?php foreach ($recent_chapters as $chapter) : 
                            $vol_num = get_post_meta($chapter->ID, 'volume_number', true);
                            $chap_num = get_post_meta($chapter->ID, 'chapter_number', true);
                            $display = $vol_num ? "Vol. {$vol_num} Ch. {$chap_num}" : "Ch. {$chap_num}";
                            $chapter_date = get_the_date('M j, Y', $chapter->ID);
                        ?>
                            <a href="<?php echo get_permalink($chapter->ID); ?>" class="chapter-link">
                                <span class="chapter-num"><?php echo esc_html($display); ?></span>
                                <span class="chapter-date"><?php echo esc_html($chapter_date); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="no-chapters">No chapters yet</div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    echo '</div>';
}

// AJAX Filter for Manga Archive
function ajax_filter_manga() {
    check_ajax_referer('manga_filter_nonce', 'nonce');
    
    $letter = isset($_POST['letter']) ? sanitize_text_field($_POST['letter']) : 'all';
    $sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : 'alphabetical';
    
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    );
    
    // Handle sorting
    switch ($sort_by) {
        case 'alphabetical':
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
        case 'updated':
            $all_manga = get_posts($args);
            usort($all_manga, function($a, $b) {
                $latest_chapter_a = get_latest_chapter_date($a->ID);
                $latest_chapter_b = get_latest_chapter_date($b->ID);
                return strtotime($latest_chapter_b) - strtotime($latest_chapter_a);
            });
            output_manga_grid_custom($all_manga);
            wp_die();
            break;
        case 'popular':
            $all_manga = get_posts($args);
            usort($all_manga, function($a, $b) {
                $chapters_a = count(get_posts(array(
                    'post_type' => 'chapter',
                    'meta_key' => 'connected_manga_id',
                    'meta_value' => $a->ID,
                    'posts_per_page' => -1
                )));
                $chapters_b = count(get_posts(array(
                    'post_type' => 'chapter',
                    'meta_key' => 'connected_manga_id',
                    'meta_value' => $b->ID,
                    'posts_per_page' => -1
                )));
                return $chapters_b - $chapters_a;
            });
            output_manga_grid_custom($all_manga);
            wp_die();
            break;
        default:
            $args['orderby'] = 'title';
            $args['order'] = 'ASC';
            break;
    }
    
    // Handle letter filtering - ONLY for alphabetical sort
    if ($sort_by === 'alphabetical' && $letter && $letter !== 'all') {
        $all_manga = get_posts($args);
        $filtered_manga = array();
        foreach ($all_manga as $manga) {
            $title = get_the_title($manga->ID);
            $first_letter = strtoupper(substr($title, 0, 1));
            if ($first_letter === strtoupper($letter)) {
                $filtered_manga[] = $manga;
            }
        }
        output_manga_grid_custom($filtered_manga);
        wp_die();
    } else {
        $query = new WP_Query($args);
        if ($query->have_posts()) {
            echo '<div class="manga-grid">';
            while ($query->have_posts()) {
                $query->the_post();
                get_template_part('template-parts/manga-card');
            }
            echo '</div>';
        } else {
            echo '<p class="no-results">No manga found.</p>';
        }
    }
    
    wp_die();
}
add_action('wp_ajax_filter_manga', 'ajax_filter_manga');
add_action('wp_ajax_nopriv_filter_manga', 'ajax_filter_manga');

// AJAX Filter Manga by Status
function ajax_filter_manga_by_status() {
    check_ajax_referer('manga_status_filter_nonce', 'nonce');
    
    $status_slug = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
    
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    );
    
    if ($status_slug !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'manga_status',
                'field' => 'slug',
                'terms' => $status_slug
            )
        );
    }
    
    $manga_query = new WP_Query($args);
    
    if ($manga_query->have_posts()) {
        echo '<div class="manga-grid">';
        while ($manga_query->have_posts()) {
            $manga_query->the_post();
            get_template_part('template-parts/manga-card');
        }
        echo '</div>';
    } else {
        echo '<p class="no-results">No manga found with this status.</p>';
    }
    
    wp_die();
}
add_action('wp_ajax_filter_manga_by_status', 'ajax_filter_manga_by_status');
add_action('wp_ajax_nopriv_filter_manga_by_status', 'ajax_filter_manga_by_status');

// AJAX Filter Manga by Genre
function ajax_filter_manga_by_genre() {
    check_ajax_referer('manga_genre_filter_nonce', 'nonce');
    
    $genre_slug = isset($_POST['genre']) ? sanitize_text_field($_POST['genre']) : '';
    
    $args = array(
        'post_type' => 'manga',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'tax_query' => array(
            array(
                'taxonomy' => 'genre',
                'field' => 'slug',
                'terms' => $genre_slug
            )
        )
    );
    
    $manga_query = new WP_Query($args);
    
    if ($manga_query->have_posts()) {
        echo '<div class="manga-grid">';
        while ($manga_query->have_posts()) {
            $manga_query->the_post();
            get_template_part('template-parts/manga-card');
        }
        echo '</div>';
    } else {
        echo '<p class="no-results">No manga found in this genre.</p>';
    }
    
    wp_die();
}
add_action('wp_ajax_filter_manga_by_genre', 'ajax_filter_manga_by_genre');
add_action('wp_ajax_nopriv_filter_manga_by_genre', 'ajax_filter_manga_by_genre');

// Helper Functions
function output_manga_grid($manga_list) {
    if (empty($manga_list)) {
        echo '<p class="no-results">No manga found.</p>';
        return;
    }
    
    echo '<div class="manga-grid">';
    foreach ($manga_list as $manga) {
        $manga_id = $manga->ID;
        $manga_title = get_the_title($manga_id);
        $manga_cover = has_post_thumbnail($manga_id) ? get_the_post_thumbnail_url($manga_id, 'manga-cover-small') : 'https://via.placeholder.com/180x252?text=No+Cover';
        
        // Get sorted recent chapters
        $all_chapters = get_sorted_chapters_for_manga($manga_id);
        $recent_chapters = array_slice($all_chapters, 0, 3);
        ?>
        <div class="manga-item">
            <div class="manga-item-cover">
                <a href="<?php echo get_permalink($manga_id); ?>">
                    <img src="<?php echo esc_url($manga_cover); ?>" alt="<?php echo esc_attr($manga_title); ?>">
                    <div class="manga-item-overlay">
                        <span class="view-details">View Details</span>
                    </div>
                </a>
                <?php if (!empty($recent_chapters)): 
                    $latest = $recent_chapters[0];
                    $vol_num = get_post_meta($latest->ID, 'volume_number', true);
                    $chap_num = get_post_meta($latest->ID, 'chapter_number', true);
                    $display = $vol_num ? "Vol. {$vol_num} Ch. {$chap_num}" : "Ch. {$chap_num}";
                ?>
                    <div class="latest-chapter-badge">
                        <?php echo esc_html($display); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="manga-item-info">
                <h3 class="manga-item-title">
                    <a href="<?php echo get_permalink($manga_id); ?>"><?php echo esc_html($manga_title); ?></a>
                </h3>
                <?php if (!empty($recent_chapters)) : ?>
                    <div class="manga-item-chapters">
                        <?php foreach ($recent_chapters as $chapter) : 
                            $vol_num = get_post_meta($chapter->ID, 'volume_number', true);
                            $chap_num = get_post_meta($chapter->ID, 'chapter_number', true);
                            $display = $vol_num ? "Vol. {$vol_num} Ch. {$chap_num}" : "Ch. {$chap_num}";
                            $chapter_date = get_the_date('M j, Y', $chapter->ID);
                        ?>
                            <a href="<?php echo get_permalink($chapter->ID); ?>" class="chapter-link">
                                <span class="chapter-num"><?php echo esc_html($display); ?></span>
                                <span class="chapter-date"><?php echo esc_html($chapter_date); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div class="no-chapters">No chapters yet</div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    echo '</div>';
}

// Enqueue Scripts
function manga_theme_scripts() {
    wp_enqueue_style('manga-theme-style', get_stylesheet_uri(), array(), '6.2');
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'manga_theme_scripts');

// Register Widgets
function manga_theme_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'manga-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'manga-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'manga_theme_widgets_init');

// Ensure chapter_number is saved
add_action('save_post_chapter', 'ensure_chapter_number_saved', 5, 2);
function ensure_chapter_number_saved($post_id, $post) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    $chapter_num = get_post_meta($post_id, 'chapter_number', true);
    $volume_num = get_post_meta($post_id, 'volume_number', true);
    
    if (empty($chapter_num)) {
        preg_match('/Ch\.?\s*(\d+(?:\.\d+)?)/i', $post->post_title, $matches);
        if (isset($matches[1])) {
            update_post_meta($post_id, 'chapter_number', floatval($matches[1]));
        }
    }
    
    if (empty($volume_num)) {
        preg_match('/Vol\.?\s*(\d+)/i', $post->post_title, $matches);
        if (isset($matches[1])) {
            update_post_meta($post_id, 'volume_number', intval($matches[1]));
        }
    }
}

?>