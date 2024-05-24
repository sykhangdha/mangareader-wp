<?php
/**
 * Displays content for the index, front page, and search results
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 */
$relationship = get_field('manga');
?>

<style>
    
.chapter-container {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr)); /* Two columns */
    gap: 20px;
    border-bottom: 1px solid #ddd;
    padding: 15px;
}

.chapter-thumbnail-container {
    margin-right: 20px;
}

.chapter-thumbnail {
    width: 100%;
    height: auto;
}

.chapter-content-container {
}

.chapter-title {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1.2rem;
}

.chapter-info {
    margin-bottom: 5px;
    display: block;
    color: #666;
}

.post-date-container {
    margin-bottom: 10px;
}

.mangas-name-container {
    color: #888;
}

.mangas-link {
    color: #888;
    text-decoration: none;
}

.mangas-link:hover {
    color: #555;
}
</style>

<?php
foreach ($relationship as $parent) {
    // Get the categories for the post
    $categories = get_the_category($parent->ID);

    // Initialize a variable to check if the post belongs to the excluded category
    $exclude_post = false;

    // Check if the post belongs to the excluded category
    foreach ($categories as $category) {
        if ($category->term_id == 32) {
            $exclude_post = true;
            break;
        }
    }

    // If the post belongs to the excluded category, skip it
    if ($exclude_post) {
        continue;
    }

    $mangas_post = get_post($parent->ID); // Get the "mangas" post associated with this chapter
    $mangas_name = $mangas_post->post_title; // Get the title of the "mangas" post
    $mangas_link = get_permalink($parent->ID); // Get the link to the "mangas" post
    $chapter_title = get_the_title(); // Get the current chapter title

    // Use preg_replace to format the chapter title as "Chapter X" (handles decimal points)
    $formatted_chapter_title = preg_replace('/^\D*(\d+(\.\d+)?).*$/', 'Chapter $1', $chapter_title);
    ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('chapter-container'); ?>>
        <div class="chapter-thumbnail-container">
            <a href="<?php echo site_url(); ?>/manga/<?php echo $parent->post_name; ?>/<?php echo $post->post_name; ?>" title="<?php echo esc_attr($formatted_chapter_title); ?>">
                <?php echo get_the_post_thumbnail($parent->ID, 'thumbnail', array('alt' => esc_attr($formatted_chapter_title), 'class' => 'chapter-thumbnail')); ?>
            </a>
        </div>

        <div class="chapter-content-container">
            <div>
                <a href="<?php echo site_url(); ?>/manga/<?php echo $parent->post_name; ?>/<?php echo $post->post_name; ?>" title="<?php echo esc_attr($formatted_chapter_title); ?>">
                    <h3 class="chapter-title"><?php echo esc_html($formatted_chapter_title); ?></h3>
                </a>
            </div>

            <?php if (get_field('title')) { ?>
                <div>
                    <small class="chapter-info"><i class="uk-icon-file"></i> "<?php the_field('title'); ?>"</small>
                </div>
            <?php } ?>

            <div class="post-date-container">
                <small class="chapter-info"><i class="uk-icon-clock-o"></i> <?php the_time('F d, Y'); ?></small>
            </div>
            <?php if ($mangas_name) { ?>
                <div class="mangas-name-container">
                    <i class="uk-icon-book"></i> <a href="<?php echo esc_url($mangas_link); ?>" class="mangas-link"><?php echo esc_html($mangas_name); ?></a>
                </div>
            <?php } ?>
        </div>
    </article>
<?php
}
?>
