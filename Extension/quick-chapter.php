<?php
/*
Plugin Name: Quick Chapter[MangaStarter Extension]
Description: Custom plugin for adding chapters to the manga (Fills out forms for you after selecting)
Version: 1.0
Author: Sky Ha
*/

// Enqueue custom CSS
function enqueue_custom_css() {
    wp_enqueue_style('chapter-plugin-styles', plugins_url('/css/chapter-plugin-styles.css', __FILE__));
}
add_action('admin_enqueue_scripts', 'enqueue_custom_css');

// Create the admin menu page
function create_chapter_page() {
    add_submenu_page(
        'edit.php?post_type=chapters', // Parent menu (chapters)
        'Add Chapter',                // Page title
        'Add Chapter',                // Menu title
        'manage_options',             // Capability required to access
        'add-chapter',                // Menu slug
        'render_chapter_page'         // Callback function to render the page
    );
}
add_action('admin_menu', 'create_chapter_page');

// Render the chapter input form
function render_chapter_page() {
    $selectedManga = get_option('selected_manga', ''); // Get the previously selected manga
    $chapterNumber = get_option('chapter_number', 1); // Get the chapter number and initialize to 1

    echo '<div class="wrap chapter-form">';
    echo '<style>
        /* Add your CSS styles here */
        .persistent-alert {
            font-size: 18px;
            background-color: yellow;
            border: 2px solid orange;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>';
    echo '<div class="persistent-alert">';
    echo 'After hitting "Add Chapter," this plugin will remember the selected manga and automatically increment the chapter number for you.';
    echo '<br>';
    echo 'To add the chapter, remember to hit "Publish" on the chapter edit page.';
    echo '</div>';
    echo '<h2>Add Chapter</h2>';
    echo '<form method="post" action="">';

    // Select manga
    echo '<div class="form-group">';
    echo '<label for="manga">Select Manga:</label>';
    echo '<select name="manga" id="manga">';
    // Query the manga posts using ACF relationship field
    $manga_args = array(
        'post_type' => 'mangas',
        'posts_per_page' => -1,
    );
    $manga_query = new WP_Query($manga_args);
    if ($manga_query->have_posts()) {
        while ($manga_query->have_posts()) {
            $manga_query->the_post();
            $mangaId = get_the_ID();
            $selected = ($mangaId == $selectedManga) ? 'selected="selected"' : '';
            echo '<option value="' . $mangaId . '" ' . $selected . '>' . get_the_title() . '</option>';
        }
        wp_reset_postdata();
    }
    echo '</select>';
    echo '</div>';

    // Chapter number input
    echo '<div class="form-group">';
    echo '<label for="chapter">Chapter Number:</label>';
    echo '<input type="text" name="chapter" id="chapter" value="' . $chapterNumber . '">';
    echo '</div>';

    // External image links textarea
    echo '<div class="form-group">';
    echo '<label for="image_links">Image Links (one per line):</label>';
    echo '<textarea name="image_links" id="image_links" rows="5"></textarea>';
    echo '</div>';

    // Pre-select the "External" radio button
    echo '<div class="form-group">';
    echo '<label for="external">Source:</label>';
    echo '<input type="radio" name="source" id="external" value="External" checked> External';
    echo '</div>';

    echo '<input type="submit" name="add_chapter" value="Add Chapter">';
    echo '</form>';
    echo '</div>';
}

// Handle form submission
function handle_form_submission() {
    if (isset($_POST['add_chapter'])) {
        // Retrieve user inputs
        $manga_id = intval($_POST['manga']);
        $chapter_number = intval($_POST['chapter']);
        $image_links = sanitize_textarea_field($_POST['image_links']);

        // Make sure all required fields are filled
        if (empty($manga_id) || empty($chapter_number) || empty($image_links)) {
            echo '<div class="error"><p>All fields are required.</p></div>';
            return; // Don't proceed if any fields are empty
        }

        // Update selected manga and chapter number options
        update_option('selected_manga', $manga_id);
        update_option('chapter_number', $chapter_number + 1);

        // Get the selected Manga title
        $manga_title = get_the_title($manga_id);

        // Generate the chapter title (Manga Title + Chapter Number)
        $post_title = $manga_title . ' ' . $chapter_number;

        // Create the chapter post
        $post_content = 'Chapter content goes here'; // You can customize this
        $post_type = 'chapters'; // Change to your chapter post type

        $chapter_args = array(
            'post_title' => $post_title,
            'post_content' => $post_content,
            'post_type' => $post_type,
            'post_status' => 'draft', // Set the status to draft initially
        );

        $chapter_id = wp_insert_post($chapter_args);

        // Check if the chapter was created successfully
        if (is_wp_error($chapter_id)) {
            echo '<div class="error"><p>Error creating the chapter: ' . $chapter_id->get_error_message() . '</p></div>';
        } else {
            // Associate the selected manga with the chapter using ACF
            update_field('manga', $manga_id, $chapter_id);

            // Save image links as ACF field
            update_field('external', $image_links, $chapter_id);

            // Redirect to the edit screen of the newly created chapter
            wp_redirect(admin_url('post.php?post=' . $chapter_id . '&action=edit'));
            exit;
        }
    }
}
add_action('admin_init', 'handle_form_submission');

// Add JavaScript to force "External" radio button selection
function add_custom_js() {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var externalRadio = document.getElementById("external");
            if (externalRadio) {
                externalRadio.checked = true;
            }
        });
    </script>';
}
add_action('admin_footer', 'add_custom_js');
?>
