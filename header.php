<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
        /* Prevent flash of incorrect theme */
        .dark-mode-transition {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
    </style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <header class="site-header">
        <div class="container">
            <div class="header-wrapper">
                <div class="site-branding">
                    <h1 class="site-title">
                        <a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a>
                    </h1>
                    <p class="site-description"><?php bloginfo('description'); ?></p>
                </div>
                
                <div class="header-actions">
                    <!-- Animated Dark Mode Toggle -->
                    <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Toggle Dark Mode">
                        <div class="toggle-track">
                            <div class="toggle-thumb">
                                <svg class="sun-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="5"></circle>
                                    <line x1="12" y1="1" x2="12" y2="3"></line>
                                    <line x1="12" y1="21" x2="12" y2="23"></line>
                                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                                    <line x1="1" y1="12" x2="3" y2="12"></line>
                                    <line x1="21" y1="12" x2="23" y2="12"></line>
                                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                                </svg>
                                <svg class="moon-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                                </svg>
                            </div>
                        </div>
                    </button>
                    
                    <button class="search-toggle" id="searchToggle" aria-label="Search">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                    
                    <button class="mobile-menu-toggle" id="mobileMenuToggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Search Modal -->
        <div class="search-modal" id="searchModal">
            <div class="search-modal-overlay"></div>
            <div class="search-modal-content">
                <button class="search-modal-close" id="searchModalClose">×</button>
                <h3>Search Manga</h3>
                <form role="search" method="get" class="search-form-modal" action="<?php echo home_url('/'); ?>">
                    <input type="search" class="search-input-modal" placeholder="Search by manga title..." value="<?php echo get_search_query(); ?>" name="s">
                    <input type="hidden" name="post_type" value="manga">
                    <button type="submit" class="search-button-modal">Search</button>
                </form>
                <div class="search-suggestions">
                    <p>Popular Manga:</p>
                    <div class="popular-manga-list">
                        <?php
                        $popular_manga = get_posts(array(
                            'post_type' => 'manga',
                            'posts_per_page' => 6,
                            'orderby' => 'comment_count',
                            'order' => 'DESC'
                        ));
                        foreach ($popular_manga as $manga) {
                            echo '<a href="' . get_permalink($manga->ID) . '">' . esc_html($manga->post_title) . '</a>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <nav class="main-nav">
        <div class="container">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_class' => 'primary-menu',
                'container' => false,
                'fallback_cb' => false,
                'depth' => 2
            ));
            ?>
        </div>
    </nav>
    
    <main id="main" class="site-main">
        <div class="container">

<script>
// Animated Dark Mode Toggle
(function() {
    // Check for saved theme preference or system preference
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
        document.body.classList.add('dark-mode');
    }
    
    // Dark mode toggle button with animation
    const darkModeToggle = document.getElementById('darkModeToggle');
    
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function(e) {
            // Add ripple effect
            const ripple = document.createElement('span');
            ripple.classList.add('toggle-ripple');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = (e.clientX - rect.left - size / 2) + 'px';
            ripple.style.top = (e.clientY - rect.top - size / 2) + 'px';
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
            
            // Toggle dark mode
            document.body.classList.toggle('dark-mode');
            
            // Save preference to localStorage
            if (document.body.classList.contains('dark-mode')) {
                localStorage.setItem('theme', 'dark');
            } else {
                localStorage.setItem('theme', 'light');
            }
        });
    }
})();
</script>