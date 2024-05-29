<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 */

// Check if index-2.php is selected as the homepage style
$selected_style = get_option('selected_homepage_style', 'index.php');

if ($selected_style === 'index-2.php') {
    // If index-2.php is selected, include the template file directly
    include(get_template_directory() . '/index-2.php');
    exit(); // Exit to prevent further execution of index.php
}

// Otherwise, continue loading index.php content
get_header();

// Find the page that uses the "Recent Chapters List" template
$recent_chapters_page = get_pages(array(
    'meta_key' => '_wp_page_template',
    'meta_value' => 'recent-chapters-list.php', // Update this to match your template file
));

if (!empty($recent_chapters_page)) {
    $recent_chapters_url = get_permalink($recent_chapters_page[0]->ID);
} else {
    // Set a default URL if the page is not found
    $recent_chapters_url = '/recent-chapters/';
}

?>

<div class="wrap">
    <style>
        .manga-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .manga-item {
            flex: 1;
            min-width: calc(50% - 20px); /* Adjust for the desired number of columns */
            background-color: #f7f7f7;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .manga-thumbnail img {
            max-width: 100%;
            max-height: auto;
        }

        .manga-item a {
            text-decoration: none;
            color: #333;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            transition: background-color 0.3s;
        }

        .manga-item a:hover {
            background-color: #ddd;
        }

        .chapter-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .chapter-button {
            background-color: #f7f7f7;
            color: #333;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .chapter-button:hover {
            background-color: #ddd;
        }

        .more-button {
            background-color: #ddd;
            color: #333;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: block;
            transition: background-color 0.3s;
        }

        .more-button:hover {
            background-color: #999;
        }

        @media screen and (max-width: 767px) {
            .manga-item {
                min-width: 100%; /* Full width on mobile */
            }
        }

        /* Style for the "View more" button */
        .recently-updated-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .view-more-button {
            background-color: #0073e6;
            color: #fff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .view-more-button:hover {
            background-color: #005aad;
        }

        .wrap {
            display: flex;
            flex-wrap: wrap; /* Allow content and sidebar to wrap to next line */
        }

        #primary {
            flex: 1;
            margin-right: 20px; /* Adjust spacing between content and sidebar */
        }

        #secondary {
            flex-basis: 300px; /* Adjust as needed */
            margin-top: 20px; /* Add space between content and sidebar */
        }
        
        #sidebar {
            width: auto !important;
        }

        @media screen and (max-width: 768px) {
            .wrap {
                flex-direction: column; /* Change to column layout on smaller screens */
            }

            #primary {
                margin-right: 0; /* Remove right margin on primary content */
            }

            #secondary {
                flex-basis: 100%; /* Make sidebar full-width on smaller screens */
                margin-top: 0; /* Remove top margin on sidebar */
            }
        }
    </style>

    <div id="primary" class="content-area">
        <!-- Add the heading and button here -->
        <div class="recently-updated-heading">
            <h1>Recently Updated Mangas</h1>
            <a href="<?php echo esc_url($recent_chapters_url); ?>" class="view-more-button">View more chapter updates</a>
        </div>

        <?php
        // Query manga posts
        $manga_query = new WP_Query(array(
            'post_type' => 'mangas',
            'posts_per_page' => -1, // Display all manga posts
        ));

        $manga_chapter_data = array();

        // Check if there are mangas to display
        if ($manga_query->have_posts()) :

            // Loop through manga posts and get the latest chapter date
            while ($manga_query->have_posts()) : $manga_query->the_post();
                $manga_id = get_the_ID();

                // Query the latest chapter for the current manga
                $latest_chapter_query = new WP_Query(array(
                    'post_type' => 'chapters',
                    'meta_query' => array(
                        array(
                            'key' => 'manga', // Custom field key for chapters relationship
                            'value' => $manga_id, // Matches exactly the manga ID
                            'compare' => 'LIKE',
                        ),
                    ),
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'posts_per_page' => 1, // Get only the latest chapter
                ));

                // If the manga has at least one chapter, get the latest chapter date
                if ($latest_chapter_query->have_posts()) {
                    $latest_chapter_query->the_post();
                    $latest_chapter_date = get_the_date('Y-m-d H:i:s');
                    $manga_chapter_data[] = array(
                        'manga_id' => $manga_id,
                        'latest_chapter_date' => $latest_chapter_date,
                        'manga_title' => get_the_title(),
                        'manga_thumbnail' => get_the_post_thumbnail($manga_id, 'medium', array('class' => 'manga-thumbnail')),
                    );
                }
                wp_reset_postdata();
            endwhile;

            // Sort mangas by latest chapter date in descending order
            usort($manga_chapter_data, function($a, $b) {
                return strtotime($b['latest_chapter_date']) - strtotime($a['latest_chapter_date']);
            });

            // Display mangas in the sorted order
            echo '<div class="manga-container">';
            foreach ($manga_chapter_data as $manga) {
                $manga_id = $manga['manga_id'];
                $manga_title = $manga['manga_title'];
                $manga_thumbnail = $manga['manga_thumbnail'];

                // Query chapters for the current manga, sorted by date to get the latest chapters
                $chapters_query = new WP_Query(array(
                    'post_type' => 'chapters',
                    'meta_query' => array(
                        array(
                            'key' => 'manga', // Custom field key for chapters relationship
                            'value' => $manga_id, // Matches exactly the manga ID
                            'compare' => 'LIKE',
                        ),
                    ),
                    'orderby' => 'date',
                    'order' => 'DESC',
                    'posts_per_page' => 3, // Display up to 3 chapters per Manga
                ));

                // Display Manga as a clickable container with title, featured image, and border
                echo '<div class="manga-item">';
                echo '<div class="manga-thumbnail">' . $manga_thumbnail . '</div>';
                echo '<div class="manga-details">';
                echo '<div class="manga-title"><a href="' . get_permalink($manga_id) . '">' . esc_html($manga_title) . '</a></div>';
                echo '<ul class="chapter-list">';

                // Loop through chapters
                while ($chapters_query->have_posts()) : $chapters_query->the_post();
                    $chapter_title = get_the_title();
                    $chapter_date = get_the_date('F j, Y');
                    // Extract the last number from the title as the chapter number
                    preg_match('/\b(\d+(?:\.\d+)?)\b(?!.*\b\d+(?:\.\d+)?\b)/', $chapter_title, $matches);
                    $chapter_number = isset($matches[1]) ? $matches[1] : __('N/A', 'mangastarter'); // Set a default value if chapter number is not found
                    $chapter_display_title = 'Chapter ' . $chapter_number;

                    // Display chapter as a button with title and date
                    echo '<li class="chapter-item">';
                    echo '<a href="' . get_permalink() . '" class="chapter-button">' . esc_html($chapter_display_title) . ' - ' . esc_html($chapter_date) . '</a>';
                    echo '</li>';
                endwhile;

                if ($chapters_query->found_posts > 3) {
                    // If there are more than 3 chapters, show the "More" button
                    echo '<li>';
                    echo '<a href="' . get_permalink($manga_id) . '" class="more-button">More</a>';
                    echo '</li>';
                }
                echo '</ul>'; // Close the chapter-list container
                echo '</div>'; // Close the manga-details container
                echo '</div>'; // Close the manga-item container

                wp_reset_postdata(); // Reset the chapters query
            }
            echo '</div>'; // Close the manga container

        else :
            echo 'No Manga with chapters found.';
        endif;
        ?>
    </div><!-- end primary -->

    <!-- Sidebar -->
    <div id="secondary" class="widget-area sidebar-container">
        <?php get_sidebar(); ?>
    </div><!-- end secondary -->
</div><!-- end wrap -->
<?php get_footer(); ?>
