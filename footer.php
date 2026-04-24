        </div>
    </main>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About</h3>
                    <p><?php bloginfo('description'); ?></p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer',
                        'menu_class' => 'footer-menu',
                        'container' => false,
                        'depth' => 1,
                        'fallback_cb' => false
                    ));
                    ?>
                </div>
                
                <div class="footer-section">
                    <h3>Browse</h3>
                    <ul>
                        <li><a href="<?php echo get_post_type_archive_link('manga'); ?>">All Manga</a></li>
                        <li><a href="<?php echo home_url('/'); ?>">Latest Chapters</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>

<script>
jQuery(document).ready(function($) {
    // Mobile menu toggle
    $('#mobileMenuToggle').on('click', function() {
        $(this).toggleClass('active');
        $('.main-nav').toggleClass('active');
        $('body').toggleClass('menu-open');
    });
    
    // Close menu when clicking on a link (mobile)
    $('.main-nav a').on('click', function() {
        if ($(window).width() <= 768) {
            $('#mobileMenuToggle').removeClass('active');
            $('.main-nav').removeClass('active');
            $('body').removeClass('menu-open');
        }
    });
    
    // Search modal functionality
    $('#searchToggle').on('click', function() {
        $('#searchModal').addClass('active');
        $('body').css('overflow', 'hidden');
        setTimeout(function() {
            $('.search-input-modal').focus();
        }, 100);
    });
    
    $('#searchModalClose, .search-modal-overlay').on('click', function() {
        $('#searchModal').removeClass('active');
        $('body').css('overflow', '');
    });
    
    // Close modal with ESC key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#searchModal').hasClass('active')) {
            $('#searchModal').removeClass('active');
            $('body').css('overflow', '');
        }
    });
    
    // Mobile submenu toggle
    if ($(window).width() <= 768) {
        $('.primary-menu .menu-item-has-children > a').on('click', function(e) {
            e.preventDefault();
            $(this).parent().toggleClass('active');
        });
    }
    
    // Handle window resize
    $(window).on('resize', function() {
        if ($(window).width() > 768) {
            $('.main-nav').removeClass('active');
            $('#mobileMenuToggle').removeClass('active');
            $('body').removeClass('menu-open');
        }
    });
});
</script>

<?php wp_footer(); ?>
</body>
</html>