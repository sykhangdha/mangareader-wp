<?php
/**
 * The template for displaying all pages
 *
 * @package Manga Reader Theme
 */

get_header(); ?>

<div class="container">
    <?php
    // Start the Loop.
    while ( have_posts() ) :
        the_post();
    ?>
        <header class="page-header">
            <h1 class="page-title"><?php the_title(); ?></h1>
        </header>

        <div class="page-content">
            <?php
            // Display the page content
            the_content();
            ?>
        </div>

        <?php
        // If the page has comments enabled, display them
        if ( comments_open() || get_comments_number() ) :
            comments_template();
        endif;

    endwhile; // End of the Loop.
    ?>
</div>

<?php get_footer(); ?>
