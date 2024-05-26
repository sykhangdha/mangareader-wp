<?php
/**
 * Displays content for the archive and search pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 */
?>

<style>
    /* Custom styles for the chapters list */
    .custom-post {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 20px;
        background-color: #f9f9f9;
        display: flex;
        flex-direction: column;
    }

    .custom-post h3 {
        margin-top: 0;
        font-size: 18px; /* Increase the font size for the manga name */
    }

    .chapter-list {
        list-style-type: none;
        padding: 0;
        margin-top: 10px;
    }

    .chapter-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
    }

    .latest-chapters-heading {
        font-size: 16px; /* Reduce the font size for the "Latest Chapters" heading */
        margin-top: 15px; /* Add some space between manga name and "Latest Chapters" heading */
    }

    .chapter-button {
        background-color: #0073e6;
        color: #fff;
        padding: 5px 10px;
        border: none;
        cursor: pointer;
        text-align: center;
        text-decoration: none;
        font-size: 14px;
        border-radius: 5px;
    }

    .chapter-button:hover {
        background-color: #0056b3;
        text-decoration: none;
    }

    /* Media query for mobile devices (max-width: 767px) */
    @media (max-width: 767px) {
        .chapter-button {
            width: 100%; /* Make the button fill the entire width of the container */
        }
    }

    .more-chapters-link {
        text-align: center;
        margin-top: 15px;
    }
</style>

<article id="post-<?php the_ID(); ?>" <?php post_class('custom-post'); ?>>
    <div class="uk-grid uk-grid-small uk-margin-bottom">
        <div class="uk-width-small-1-6 uk-width-medium-3-10 chapter-thumb">
            <?php echo the_post_thumbnail('thumbnail', array('alt' => get_the_title())); ?>
        </div>

        <div class="uk-width-small-5-6 uk-width-medium-7-10 chapter-content">
            <div>
                <a href="<?php the_permalink(); ?>">
                     <h3 class="manga-title"><?php the_title(); ?></h3>
                </a>
            </div>

            <?php
            // Check if the current post is of post_type 'mangas'
            if (get_post_type() === 'mangas') {
                // Retrieve the latest chapters related to this manga
                $manga_chapters = get_posts(array(
                    'post_type' => 'chapters',
                    'orderby' => 'date',
                    'order' => 'DESC', // Display latest chapters first
                    'numberposts' => -1, // Retrieve all chapters
                    'meta_query' => array(
                        array(
                            'key' => 'manga', // name of custom field
                            'value' => get_the_ID(), // Use get_the_ID() to get the current manga post ID
                            'compare' => 'LIKE'
                        )
                    )
                ));

                // Sort the chapters by date in descending order
                usort($manga_chapters, function ($a, $b) {
                    return strtotime(get_post_field('post_date', $b)) - strtotime(get_post_field('post_date', $a));
                });

                // Display the list of the latest chapters with dates
                if ($manga_chapters) {
                    echo '<h3 class="latest-chapters-heading">Latest Chapters:</h3>';
                    echo '<ul class="chapter-list">';
                    $chapterCount = 0; // Initialize a chapter count
                    foreach ($manga_chapters as $chapter) {
                        if ($chapterCount < 3) { // Only display the first 3 chapters initially
                            $chapter_title = get_the_title($chapter->ID);
                            $chapter_title_parts = explode(' ', $chapter_title);
                            $chapter_title = 'Chapter ' . end($chapter_title_parts); // Get the last part
                            echo '<li class="chapter-list-item">';
                            echo '<a href="' . esc_url(get_permalink($chapter->ID)) . '" class="chapter-button">';
                            echo $chapter_title . ' - ' . get_the_date('F d, Y', $chapter->ID);
                            echo '</a>';
                            echo '</li>';
                            $chapterCount++; // Increment chapter count
                        }
                    }
                    echo '</ul>';
                } else {
                    echo '<p>No chapters available</p>';
                }
            } else {
                // Display the time for other post types
                the_time('F d, Y');
            }
            ?>

            <!-- Load More Button (Redirects to post_type=mangas page for this manga) -->
            <div class="more-chapters-link">
                <a href="<?php echo esc_url(get_permalink()); ?>" class="chapter-button">Load More...</a>
            </div>
        </div>
    </div>
</article><!-- end article -->
