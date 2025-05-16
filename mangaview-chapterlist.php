<?php
get_header();

$folder = get_query_var('manga_name');
if (!$folder) {
    echo '<p>Manga name not specified.</p>';
    get_footer();
    exit;
}

$base_path = ABSPATH . 'manga/';
$actual_folder = manga_reader_denormalize_name($folder, $base_path);
$cover_path = $base_path . $actual_folder . '/cover.jpg';
$cover_url = file_exists($cover_path) ? site_url("/manga/$actual_folder/cover.jpg") : 'https://via.placeholder.com/200x300?text=No+Cover';
?>

<div class="manga-viewer" data-manga-name="<?php echo esc_attr($actual_folder); ?>">
    <!-- Display Manga Cover and Title -->
    <div class="manga-header">
        <img id="manga-cover" class="cover" src="<?php echo esc_url($cover_url); ?>" alt="<?php echo esc_attr($actual_folder); ?> Cover">
        <h2 id="manga-heading"><?php echo esc_html($actual_folder); ?></h2>
    </div>

    <!-- This section will be dynamically updated with the chapter list -->
    <div id="chapter-list-container">
        <ul id="mangaview-chapterlist" class="chapter-list">
            <!-- Chapter list will be populated dynamically by JavaScript via AJAX -->
        </ul>
        <button id="back-to-home">Back to Home</button>
    </div>

    <!-- This section will be dynamically updated with the manga images after a chapter is selected -->
    <div id="manga-images-container" style="display: none;">
        <div id="manga-images"></div>

        <div id="manga-sidebar" class="sidebar sidebar-hidden">
            <button class="sidebar-close">Close</button>
            <p style="color:#bbb; font-size:1em; text-align:center; margin-bottom:15px;">MangaViewer v1.0</p>
            <div class="view-toggle">
                <button id="list-view-btn" data-view="list">List View</button>
                <button id="paged-view-btn" data-view="paged">Paged View</button>
            </div>
            <ul id="mangaview-chapterlist-sidebar"></ul>
            <button id="back-to-home-sidebar">Back to Home</button>
        </div>
    </div>
</div>

<?php get_footer(); ?>