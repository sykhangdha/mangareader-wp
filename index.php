<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 */
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
    <div id="primary" class="content-area">
        <!-- Add the heading and button here -->
        <div class="recently-updated-heading">
            <h1>Recently Updated Mangas</h1>
            <a href="<?php echo esc_url($recent_chapters_url); ?>" class="view-more-button">View more chapter updates</a>
        </div>

        <?php
        // Initialize an array to store manga posts with chapters and their latest chapter dates
        $mangas_with_chapters = array();

        // Query manga posts with chapters
        $manga_query = new WP_Query(array(
            'post_type' => 'mangas',
            'posts_per_page' => -1, // Display all manga posts
        ));

        while ($manga_query->have_posts()) : $manga_query->the_post();

            $manga_id = get_the_ID();

            // Query chapters for the current manga, sorted by date to get the latest chapter
            $chapters_query = new WP_Query(array(
                'post_type' => 'chapters',
                'posts_per_page' => -1, // Display all chapters
                'meta_query' => array(
                    array(
                        'key' => 'manga', // Custom field key for chapters relationship
                        'value' => '"' . $manga_id . '"', // Matches exactly the manga ID
                        'compare' => 'LIKE',
                    ),
                ),
                'orderby' => 'date',
                'order' => 'DESC',
                'suppress_filters' => false, // Refresh the query
            ));

            // If the manga has at least one chapter, add it to the list with its latest chapter date
            if ($chapters_query->have_posts()) {
                $latest_chapter = $chapters_query->posts[0];
                $latest_chapter_date = get_post_field('post_date', $latest_chapter); // Get the post date of the latest chapter
                $mangas_with_chapters[] = array(
                    'manga_post' => get_post(),
                    'latest_chapter_date' => $latest_chapter_date,
                );
            }

        endwhile;

        // Sort the array of manga posts with chapters by their latest chapter date in descending order
        usort($mangas_with_chapters, function ($a, $b) {
            return strtotime($b['latest_chapter_date']) - strtotime($a['latest_chapter_date']);
        });

        // Limit the number of displayed mangas to 15
        $mangas_to_display = array_slice($mangas_with_chapters, 0, 15);

        // Check if there are mangas with chapters to display
        if (!empty($mangas_to_display)) :
        ?>

        <!-- CSS -->
        <style>
            /* Add your CSS styles here */
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
        </style>

        <div class="manga-container">
            <?php
            // Loop through sorted mangas with chapters
            foreach ($mangas_to_display as $manga_with_chapters) {
                $manga_post = $manga_with_chapters['manga_post'];
                $manga_id = $manga_post->ID;
                $manga_title = get_the_title($manga_id);
                $manga_thumbnail = get_the_post_thumbnail($manga_id, 'medium', array('class' => 'manga-thumbnail')); // Use 'medium' size for responsive thumbnail

                // Display Manga as a clickable container with title, featured image, and border
                echo '<div class="manga-item">';
                echo '<a href="' . get_permalink($manga_id) . '">' . $manga_thumbnail . '<br>' . esc_html($manga_title) . '</a>';
                echo '<div class="chapter-list">';

                // Query chapters for the current manga, sorted by date to get the latest chapter
                $chapters_query = new WP_Query(array(
                    'post_type' => 'chapters',
                    'order' => 'DESC',
                    'numberposts' => 3, // Display up to 3 chapters per Manga
                    'meta_query' => array(
                        array(
                            'key' => 'manga', // Custom field key for chapters relationship
                            'value' => '"' . $manga_id . '"', // Matches exactly the manga ID
                            'compare' => 'LIKE'
                        )
                    ),
                    'suppress_filters' => false, // Refresh the query
                ));

                $displayed_chapters = 0;

                while ($chapters_query->have_posts() && $displayed_chapters < 3) :
                    $chapters_query->the_post();
                    // Extract the last number from the title as the chapter number
                    $chapter_title = get_the_title();
                    $chapter_number = '';
                    if (preg_match('/\b(\d+(?:\.\d+)?)\b(?!.*\b\d+(?:\.\d+)?\b)/', $chapter_title, $matches)) {
                        $chapter_number = $matches[1];
                    } else {
                        $chapter_number = __('N/A', 'mangastarter'); // Set a default value if chapter number is not found
                    }
                    $chapter_title = 'Chapter ' . $chapter_number;
                    $chapter_date = get_the_date('F j, Y');

                    // Display chapter as a button with title and date
                    echo '<a href="' . get_permalink() . '" class="chapter-button">' . esc_html($chapter_title) . ' - ' . esc_html($chapter_date) . '</a>';
                    $displayed_chapters++;
                endwhile;

                if ($chapters_query->found_posts > 3) {
                    // If there are more than 3 chapters, show the "More" button
                    echo '<a href="' . get_permalink($manga_id) . '" class="more-button">More</a>';
                }

                echo '</div>'; // Close the chapter-list container
                echo '</div>'; // Close the manga-item container
            }
            ?>
        </div> <!-- Close the manga container -->

        <?php
        else :
            echo 'No Manga with chapters found.';
        endif;
        ?>
    </div><!-- end primary -->
</div><!-- end wrap -->
<?php get_footer(); ?>
