<?php
/* Template Name: Manga Viewer Page */
get_header();

$manga_name = get_query_var('manga_name');
$manga_folder = manga_reader_denormalize_name($manga_name);
$cover_image_url = site_url("/manga/{$manga_folder}/cover.jpg");
?>

<div id="manga-main" class="manga-viewer" data-manga-name="<?php echo esc_attr($manga_folder); ?>">
    <h1 id="manga-heading"><?php echo esc_html($manga_folder); ?> - Chapters</h1>

    <div id="manga-images-container">
        <!-- Manga Images -->
        <div id="manga-images"></div>

        <!-- Sidebar Toggle Button -->
        <button class="sidebar-toggle" data-toggle-sidebar>Chapters/Settings</button>

        <!-- Sidebar -->
        <div id="manga-sidebar" class="sidebar sidebar-hidden">
            <button class="sidebar-close" data-toggle-sidebar>X</button>
            <div class="sidebar-content">
                <p class="viewer-version">MangaViewer v1.0</p>
                <!-- Chapter List in Sidebar -->
                <ul id="mangaview-chapterlist-sidebar"></ul>
                <!-- View Mode Buttons -->
                <div class="view-toggle">
                    <button data-view="list">List View</button>
                    <button data-view="paged">Paged View</button>
                </div>
                <!-- Back to Home Button in Sidebar -->
                <button id="back-to-home-sidebar" class="back-to-home">Back to Home</button>
            </div>
        </div>
    </div>

    <!-- Chapter List Container -->
    <div id="chapter-list-container">
        <!-- Manga Cover Image -->
        <div id="manga-cover">
            <img src="<?php echo esc_url($cover_image_url); ?>" alt="Cover Image">
        </div>
        <ul id="mangaview-chapterlist"></ul>
        <!-- Back to Home Button in Chapter List -->
        <button id="back-to-home" class="back-to-home">Back to Home</button>
    </div>
</div>

<?php get_footer(); ?>