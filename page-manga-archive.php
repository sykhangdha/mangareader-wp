<?php
/**
 * Template Name: Manga Archive
 * Description: Display all manga series with filtering options
 */

get_header(); ?>

<div class="manga-archive">
    <div class="archive-header">
        <h1>Manga Library</h1>
        <p>Browse through our collection of manga series</p>
    </div>
    
    <div class="alphabet-filter">
        <a href="#" data-letter="all" class="active">All</a>
        <?php foreach (range('A', 'Z') as $letter): ?>
            <a href="#" data-letter="<?php echo $letter; ?>"><?php echo $letter; ?></a>
        <?php endforeach; ?>
    </div>
    
    <div class="sort-options">
        <button class="sort-btn active" data-sort="alphabetical">🔤 Alphabetical</button>
        <button class="sort-btn" data-sort="updated">🔄 Recently Updated</button>
        <button class="sort-btn" data-sort="popular">⭐ Most Popular</button>
    </div>
    
    <div id="manga-container">
        <?php
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        $manga_query = new WP_Query(array(
            'post_type' => 'manga',
            'posts_per_page' => 24,
            'paged' => $paged,
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
        if ($manga_query->have_posts()) :
            echo '<div class="manga-grid">';
            while ($manga_query->have_posts()) : $manga_query->the_post();
                get_template_part('template-parts/manga-card');
            endwhile;
            echo '</div>';
            
            echo '<div class="pagination">';
            echo paginate_links(array(
                'total' => $manga_query->max_num_pages,
                'current' => $paged,
                'prev_text' => '← Previous',
                'next_text' => 'Next →',
                'mid_size' => 2
            ));
            echo '</div>';
            
            wp_reset_postdata();
        else :
            echo '<div class="no-results">';
            echo '<p>No manga found. Create your first manga series!</p>';
            echo '</div>';
        endif;
        ?>
    </div>
</div>

<style>
/* Manga Archive Page Styles */
.manga-archive {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 0;
}

.archive-header {
    text-align: center;
    margin-bottom: 30px;
}

.archive-header h1 {
    font-size: 36px;
    color: #333;
    margin-bottom: 10px;
}

.archive-header p {
    color: #666;
    font-size: 16px;
}

/* Responsive */
@media (max-width: 768px) {
    .manga-archive {
        padding: 15px;
    }
    
    .archive-header h1 {
        font-size: 28px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Alphabet filter click handler
    $('.alphabet-filter a').click(function(e) {
        e.preventDefault();
        $('.alphabet-filter a').removeClass('active');
        $(this).addClass('active');
        
        var letter = $(this).data('letter');
        var sortBy = $('.sort-btn.active').data('sort');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'filter_manga',
                letter: letter,
                sort_by: sortBy,
                nonce: '<?php echo wp_create_nonce("manga_filter_nonce"); ?>'
            },
            beforeSend: function() {
                $('#manga-container').html('<div class="loading-container"><div class="spinner"></div></div>');
            },
            success: function(response) {
                $('#manga-container').html(response);
                // Scroll to top of results
                $('html, body').animate({
                    scrollTop: $('#manga-container').offset().top - 50
                }, 300);
            },
            error: function() {
                $('#manga-container').html('<div class="no-results"><p>Error loading manga. Please refresh the page.</p></div>');
            }
        });
    });
    
    // Sort button click handler
    $('.sort-btn').click(function() {
        $('.sort-btn').removeClass('active');
        $(this).addClass('active');
        
        var sortBy = $(this).data('sort');
        var letter = $('.alphabet-filter a.active').data('letter') || 'all';
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'filter_manga',
                letter: letter,
                sort_by: sortBy,
                nonce: '<?php echo wp_create_nonce("manga_filter_nonce"); ?>'
            },
            beforeSend: function() {
                $('#manga-container').html('<div class="loading-container"><div class="spinner"></div></div>');
            },
            success: function(response) {
                $('#manga-container').html(response);
                // Scroll to top of results
                $('html, body').animate({
                    scrollTop: $('#manga-container').offset().top - 50
                }, 300);
            },
            error: function() {
                $('#manga-container').html('<div class="no-results"><p>Error loading manga. Please refresh the page.</p></div>');
            }
        });
    });
});
</script>

<?php get_footer(); ?>