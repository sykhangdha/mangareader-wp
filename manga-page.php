<?php
/* Template Name: Manga Viewer Page */
get_header();

$manga_name = get_query_var('manga_name');
$manga_folder = manga_reader_denormalize_name($manga_name);
$cover_image_url = site_url("/manga/{$manga_folder}/cover.jpg");
?>

<div id="manga-main" class="manga-viewer" data-manga-name="<?php echo esc_attr($manga_folder); ?>">
    <h1 id="manga-heading" style="text-align:center; margin-top:20px;">
        <?php echo esc_html($manga_folder); ?> - Chapters
    </h1>

    <div id="manga-images-container">
        <!-- Manga Images -->
        <div id="manga-images"></div>

        <!-- Sidebar -->
        <div id="manga-sidebar" class="sidebar sidebar-hidden">
            <button class="sidebar-close">Close</button>
            <p style="color:#bbb; font-size:1em; text-align:center; margin-bottom:15px;">MangaViewer v1.0</p>

            <!-- Chapter List in Sidebar -->
            <ul id="mangaview-chapterlist-sidebar" style="list-style:none; padding:0; margin:0; width:100%; display:flex; flex-direction:column; gap:15px; align-items:center; margin-bottom:15px;"></ul>

            <!-- View Mode Buttons -->
            <div class="view-toggle" style="margin-bottom: 15px;">
                <button data-view="list">List View</button>
                <button data-view="paged">Paged View</button>
            </div>

            <!-- Back to Home Button in Sidebar -->
            <button id="back-to-home-sidebar" style="background-color:#222; color:#fff; padding:12px 20px; font-size:1em; border-radius:6px; border:none; margin-top:15px; cursor:pointer; text-align:center; width:100%; transition:background-color 0.3s ease;" onclick="window.location.href='<?php echo esc_url(home_url()); ?>';">
                Back to Home
            </button>
        </div>
    </div>

    <!-- Chapter List Container -->
    <div id="chapter-list-container" style="width:100%; padding: 20px; background-color: #1e1f23; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">
        <!-- Manga Cover Image -->
        <div id="manga-cover" style="width:100%; text-align:center; margin-bottom:20px;">
            <img src="<?php echo esc_url($cover_image_url); ?>" alt="Cover Image" style="max-width:100%; height:auto;">
        </div>

        <ul id="mangaview-chapterlist" style="list-style:none; padding:0; margin:0; width:100%; display:flex; flex-direction:column; gap:15px; align-items:center;"></ul>

        <!-- Back to Home Button in Chapter List -->
        <button id="back-to-home" style="background-color:#222; color:#fff; padding:12px 20px; font-size:1em; border-radius:6px; border:none; margin-top:15px; cursor:pointer; text-align:center; width:100%; transition:background-color 0.3s ease;" onclick="window.location.href='<?php echo esc_url(home_url()); ?>';">
            Back to Home
        </button>
    </div>
</div>

<?php get_footer(); ?>
