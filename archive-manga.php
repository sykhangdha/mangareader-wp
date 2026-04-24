<?php get_header(); ?>

<div class="manga-archive">
    <h1>Manga Library</h1>
    
    <div class="alphabet-filter">
        <a href="#" data-letter="all" class="active">All</a>
        <?php foreach (range('A', 'Z') as $letter): ?>
            <a href="#" data-letter="<?php echo $letter; ?>"><?php echo $letter; ?></a>
        <?php endforeach; ?>
    </div>
    
    <div class="sort-options">
        <button class="sort-btn active" data-sort="alphabetical">Alphabetical</button>
        <button class="sort-btn" data-sort="updated">Recently Updated</button>
    </div>
    
    <div id="manga-container">
        <?php
        $paged = get_query_var('paged') ? get_query_var('paged') : 1;
        $manga_query = new WP_Query(array(
            'post_type' => 'manga',
            'posts_per_page' => 20,
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
                'prev_text' => 'Previous',
                'next_text' => 'Next'
            ));
            echo '</div>';
            
            wp_reset_postdata();
        else :
            echo '<p class="no-results">No manga found.</p>';
        endif;
        ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
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
            }
        });
    });
    
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
            }
        });
    });
});
</script>

<?php get_footer(); ?>