<?php
$manga_id = get_the_ID();
$manga_title = get_the_title();
$manga_cover = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'manga-cover-small') : 'https://via.placeholder.com/180x252?text=No+Cover';

$latest_chapter = get_posts(array(
    'post_type' => 'chapter',
    'posts_per_page' => 1,
    'meta_key' => 'connected_manga_id',
    'meta_value' => $manga_id,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
));

$chapter_num = '';
if (!empty($latest_chapter)) {
    $chapter_num = get_post_meta($latest_chapter[0]->ID, 'chapter_number', true);
    if (empty($chapter_num)) {
        preg_match('/Chapter\s*(\d+(?:\.\d+)?)/i', $latest_chapter[0]->post_title, $matches);
        $chapter_num = isset($matches[1]) ? $matches[1] : '?';
    }
    $chapter_url = get_permalink($latest_chapter[0]->ID);
    $chapter_date = get_the_date('M j, Y', $latest_chapter[0]->ID);
}
?>
<div class="manga-item">
    <div class="manga-item-cover">
        <a href="<?php the_permalink(); ?>">
            <img src="<?php echo esc_url($manga_cover); ?>" alt="<?php echo esc_attr($manga_title); ?>">
            <div class="manga-item-overlay">
                <span class="view-details">View Details</span>
            </div>
        </a>
        <?php if (!empty($latest_chapter)): ?>
            <div class="latest-chapter-badge">
                Ch. <?php echo esc_html($chapter_num); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="manga-item-info">
        <h3 class="manga-item-title">
            <a href="<?php the_permalink(); ?>"><?php echo esc_html($manga_title); ?></a>
        </h3>
        <?php if (!empty($latest_chapter)) : ?>
            <div class="manga-item-chapters">
                <a href="<?php echo esc_url($chapter_url); ?>" class="chapter-link">
                    <span class="chapter-num">Chapter <?php echo esc_html($chapter_num); ?></span>
                    <span class="chapter-date"><?php echo esc_html($chapter_date); ?></span>
                </a>
            </div>
        <?php else: ?>
            <div class="no-chapters">No chapters yet</div>
        <?php endif; ?>
    </div>
</div>