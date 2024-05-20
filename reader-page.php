<?php
/**
 * Template Name: Recent Chapters List
 */

get_header();
?>

<div class="wrap">
    <div id="primary" class="content-area uk-grid">
        <main id="main" class="site-main uk-width-small-1-1 uk-width-medium-7-10 uk-margin-large-bottom" role="main">

            <header class="page-header">
                <h1 class="page-title">Recent Chapters List</h1>
            </header>

            <div class="recent-chapters-list">
                <table class="chapters-table">
                    <thead>
                        <tr>
                            <th>Chapter Title</th>
                            <th>Manga Name</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="loaded-chapters-container">
                        <?php
                        // Create a custom query to fetch recent chapters
                        $args = array(
                            'post_type'      => 'chapters',
                            'posts_per_page' => 5, // Initial number of chapters to display
                            'orderby'        => 'date',
                            'order'          => 'DESC',
                        );

                        $recent_chapters_query = new WP_Query($args);

                        if ($recent_chapters_query->have_posts()) :

                            while ($recent_chapters_query->have_posts()) : $recent_chapters_query->the_post();
                                $chapter_date = get_the_date('F j, Y'); // Get chapter date
                                $manga = get_field('manga'); // Get associated manga

                                if ($manga) {
                                    foreach ($manga as $manga_item) {
                                        // Get manga post object
                                        $manga_post = get_post($manga_item->ID);

                                        if ($manga_post) {
                                            $manga_title = $manga_post->post_title; // Get manga title
                                            $manga_link = get_permalink($manga_item->ID); // Get manga link
                                            $chapter_title = get_the_title(); // Get chapter title
                                            $chapter_link = get_permalink(); // Get chapter link

                                            // Display the chapter information within table row
                                            ?>
                                            <tr class="chapter-item">
                                                <td class="chapter-title"><a href="<?php echo esc_url($chapter_link); ?>"><?php echo esc_html($chapter_title); ?></a></td>
                                                <td class="manga-name"><a href="<?php echo esc_url($manga_link); ?>"><?php echo esc_html($manga_title); ?></a></td>
                                                <td class="chapter-date"><?php echo esc_html($chapter_date); ?></td>
                                            </tr>
                                            <?php
                                        }
                                    }
                                }
                            endwhile;

                            wp_reset_postdata();
                        else :
                            echo '<tr><td colspan="3">No recent chapters found.</td></tr>';
                        endif;
                        ?>
                    </tbody>
                </table>

                <!-- "Load More" button is placed here -->
                <div class="load-more-button-container">
                    <button id="load-more-button" class="button">Load More</button>
                </div>
            </div>

        </main><!-- end main -->

        <?php get_sidebar(); ?>
    </div><!-- end primary -->
</div><!-- end wrap -->


<style>
    /* CSS for the "Load More" button */
    .load-more-button-container {
        text-align: center;
        margin-top: 20px;
    }

    #load-more-button {
        padding: 10px 20px;
        background-color: #0073e6;
        color: #fff;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    #load-more-button:hover {
        background-color: #0055a5;
    }

    /* CSS for the chapters table */
    .chapters-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    .chapters-table th, .chapters-table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .chapters-table th {
        background-color: #f2f2f2;
    }

    .manga-name a,
    .chapter-title a {
        color: #0073e6;
        text-decoration: none;
    }

    .manga-name a:hover,
    .chapter-title a:hover {
        text-decoration: underline;
    }
</style>

<script>
    (function ($) {
        $(document).ready(function () {
            var displayedChapters = <?php echo $recent_chapters_query->post_count; ?>; // Initialize with the initial number of displayed chapters
            var totalChapters = <?php echo $recent_chapters_query->found_posts; ?>; // Initialize with the total number of chapters

            var chaptersPerPage = 5;

            $('#load-more-button').on('click', function () {
                if (displayedChapters < totalChapters) {
                    // Calculate the number of chapters to load in this batch
                    var chaptersToLoad = Math.min(totalChapters - displayedChapters, chaptersPerPage);

                    // Create a new query to fetch additional chapters
                    var additionalArgs = {
                        'action': 'load_more_chapters',
                        'query': {
                            'post_type': 'chapters',
                            'posts_per_page': chaptersToLoad,
                            'orderby': 'date',
                            'order': 'DESC',
                            'offset': displayedChapters, // Offset by the number of displayed chapters
                        },
                    };

                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: additionalArgs,
                        success: function (response) {
                            // Append the new chapters within table rows
                            $('#loaded-chapters-container').append('<tr>' + response + '</tr>');
                            displayedChapters += chaptersToLoad;

                            // If all chapters are displayed, hide the "Load More" button
                            if (displayedChapters >= totalChapters) {
                                $('#load-more-button').hide();
                            }
                        },
                    });
                }
            });
        });
    })(jQuery);
</script>

<?php get_footer(); ?>
