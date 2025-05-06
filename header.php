<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <div id="page" class="site">
        <header id="masthead" class="site-header">
            <div class="header-container">
                <!-- Display the site title -->
                <h1 class="site-title">
                    <a href="<?php echo esc_url(home_url('/')); ?>" rel="home">
                        <?php bloginfo('name'); ?>
                    </a>
                </h1>

                <!-- Display the WordPress menu -->
                <nav id="site-navigation" class="main-navigation" role="navigation" aria-label="<?php esc_attr_e('Primary Menu', 'your-theme-textdomain'); ?>">
                    <?php
                    // Display the Primary Menu if assigned, else show a default message
                    wp_nav_menu(array(
                        'theme_location' => 'primary', // Change this to 'primary'
                        'container' => false,  // Remove the wrapper <nav> tag
                        'menu_class' => 'menu', // Add the class to the <ul> for styling
                        'fallback_cb' => function() { // Fallback message if no menu is assigned
                            echo '<ul class="menu"><li>' . __('No menu assigned', 'your-theme-textdomain') . '</li></ul>';
                        }
                    ));
                    ?>
                </nav><!-- #site-navigation -->
            </div> <!-- .header-container -->
        </header><!-- #masthead -->

        <!-- Main Content -->
        <main id="content" class="site-content">
