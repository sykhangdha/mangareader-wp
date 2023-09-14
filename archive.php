<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 */
get_header(); ?>

<div class="wrap">
    <div id="primary" class="content-area uk-grid">
        <main id="main" class="site-main uk-width-small-1-1 uk-width-medium-7-10" role="main">

            <header class="page-header">
                <?php the_archive_title( '<h2 class="page-title">', '</h2>' ); ?>
            </header><!-- .page-header -->

            <?php
            if ( have_posts() ) { ?>

                <!-- Add the A-Z filter here -->
                <style>
                    /* Custom styles for the A-Z navigation */
                    .az-nav {
                        margin: 20px 0;
                    }

                    .az-nav a {
                        margin: 0 5px;
                        text-decoration: none;
                        font-weight: bold;
                    }
                </style>

                <?php
                // Get the clicked letter (if any)
                $selected_letter = isset( $_GET['letter'] ) ? sanitize_text_field( $_GET['letter'] ) : '';

                // Generate A-Z navigation links
                $az_nav_letters = range( 'A', 'Z' );
                $az_nav_links = '';

                foreach ( $az_nav_letters as $letter ) {
                    $az_nav_links .= '<a href="#manga-section-' . $letter . '">' . $letter . '</a> ';
                }

                // Output the A-Z navigation links
                echo '<div class="az-nav">Filter by Letter: ' . $az_nav_links . '</div>';
                ?>

                <?php
                // Initialize a variable to store the current letter
                $current_letter = '';

                while ( have_posts() ) {
                    the_post();
                    $manga_title = get_the_title();
                    $first_letter = strtoupper( substr( $manga_title, 0, 1 ) ); // Get the first letter

                    // Check if the letter has changed
                    if ( $first_letter !== $current_letter ) {
                        // Output the new letter section
                        echo '<section id="manga-section-' . $first_letter . '" class="archive-mangas" data-letter="' . $first_letter . '">';
                        echo '<h3>' . $first_letter . '</h3>';
                        $current_letter = $first_letter;
                    }

                    // Display the manga content here
                    echo '<div class="uk-width-small-1-1 uk-width-medium-5-10">';
                    get_template_part( 'components/loop/content', 'archive' );
                    echo '</div>';
                }

                // Close the last section
                echo '</section>';
                ?>

                <?php get_template_part( 'components/loop/content', 'pagination' ); ?>

            <?php
            } else {
                get_template_part( 'components/post/content', 'none' );
            }
            ?>

        </main><!-- #main -->
        <?php get_sidebar(); ?>
    </div><!-- #primary -->
</div><!-- .wrap -->

<?php get_footer(); ?>
