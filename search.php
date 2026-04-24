<?php get_header(); ?>

<div class="search-results">
    <h1>Search Results for: <?php echo get_search_query(); ?></h1>
    
    <?php if (have_posts()) : ?>
        <div class="manga-grid">
            <?php while (have_posts()) : the_post(); ?>
                <?php if (get_post_type() === 'manga'): ?>
                    <?php get_template_part('template-parts/manga-card'); ?>
                <?php elseif (get_post_type() === 'chapter'): 
                    $chapter_num = get_post_meta(get_the_ID(), 'chapter_number', true);
                    if (empty($chapter_num)) {
                        preg_match('/Chapter\s*(\d+(?:\.\d+)?)/i', get_the_title(), $matches);
                        $chapter_num = isset($matches[1]) ? $matches[1] : '?';
                    }
                    $manga_id = get_post_meta(get_the_ID(), 'connected_manga_id', true);
                    $manga_title = $manga_id ? get_the_title($manga_id) : 'Unknown';
                ?>
                    <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 10px;">
                        <a href="<?php the_permalink(); ?>" style="text-decoration: none;">
                            <strong><?php echo esc_html($manga_title); ?> - Chapter <?php echo esc_html($chapter_num); ?></strong>
                            <br>
                            <small><?php echo get_the_date(); ?></small>
                        </a>
                    </div>
                <?php endif; ?>
            <?php endwhile; ?>
        </div>
        
        <div class="pagination">
            <?php echo paginate_links(); ?>
        </div>
    <?php else : ?>
        <p class="no-results">No results found for "<?php echo get_search_query(); ?>".</p>
    <?php endif; ?>
</div>

<?php get_footer(); ?>