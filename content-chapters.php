<?php
/**
 * Template part for displaying chapter posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 */
// Get the chapter parent (manga)
$relationship = get_field('manga');

// Source of images, internal or external.
if (get_field('source') == 'Upload') {
    $images = acf_photo_gallery('upload', $post->ID);
    sort($images);
} else {
    $images = get_field('external', $post->ID);
}

// Retrieve the previous and next chapter URLs
$prev_chapter_url = '';
$next_chapter_url = '';

if ($relationship && is_array($relationship) && count($relationship) > 0) {
    $manga = $relationship[0];

    // Get all chapters related to the manga
    $manga_chapters = get_posts(array(
        'post_type' => 'chapters',
        'orderby' => 'date',
        'order' => 'ASC', // Order by ascending date to get the previous and next chapters
        'numberposts' => -1, // Retrieve all chapters
        'meta_query' => array(
            array(
                'key' => 'manga', // name of custom field
                'value' => '"' . $manga->ID . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
                'compare' => 'LIKE'
            )
        )
    ));

    // Find the current chapter's index in the manga chapters
    $current_chapter_index = -1;
    foreach ($manga_chapters as $index => $chapter) {
        if ($chapter->ID === $post->ID) {
            $current_chapter_index = $index;
            break;
        }
    }

    // Calculate the previous and next chapter URLs
    if ($current_chapter_index > 0) {
        $prev_chapter = $manga_chapters[$current_chapter_index - 1];
        $prev_chapter_url = get_permalink($prev_chapter->ID);
    }

    if ($current_chapter_index < count($manga_chapters) - 1) {
        $next_chapter = $manga_chapters[$current_chapter_index + 1];
        $next_chapter_url = get_permalink($next_chapter->ID);
    }
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <header class="entry-header">
        <div class="uk-grid" data-uk-grid-margin>
            <div class="uk-width-small-1-1 uk-width-medium-1-3">
                <div class="uk-clearfix">
                    <div class="uk-align-left">
                        <div class="uk-button uk-form-select" data-uk-form-select>
                            <span class="uk-text-center"></span>
                            <i class="uk-icon-angle-down"></i>
                            <select onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
                                <?php mangastarter_reader_chapter_list(); ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="uk-width-small-1-1 uk-width-medium-1-3">
                <?php the_title('<h2 class="uk-text-center">', '</h2>'); ?>
                <!-- PAGED and LIST View Buttons -->
                <div class="uk-clearfix">
                    <div class="uk-align-center">
                        <button id="paged-view-button" class="uk-button uk-button-primary active">Page by Page</button>
                        <button id="list-view-button" class="uk-button uk-button-primary">List View</button>
                    </div>
                </div>
                <!-- End of PAGED and LIST View Buttons -->
            </div>

            <div class="uk-width-small-1-1 uk-width-medium-1-3">
                <div class="uk-clearfix">
                    <div class="uk-align-right">
                        <div class="uk-button-group">
                            <?php
                            // Check if there's a previous chapter
                            if (!empty($prev_chapter_url)) :
                            ?>
                                <a class="previous-chapter uk-button uk-button-primary" href="<?php echo esc_url($prev_chapter_url); ?>"><?php _e('Previous Chapter', 'your-theme-textdomain'); ?></a>
                            <?php endif; ?>
                            
                            <?php
                            // Check if there's a next chapter
                            if (!empty($next_chapter_url)) :
                            ?>
                                <a class="next-chapter uk-button uk-button-primary" href="<?php echo esc_url($next_chapter_url); ?>"><?php _e('Next Chapter', 'your-theme-textdomain'); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header><!-- .entry-header -->

    <div class="entry-content uk-margin-top">
        <div class="uk-grid" data-uk-grid-margin>
            <div class="uk-width-small-1-1">
                <!-- Page-by-Page View -->
                <div class="page-by-page">
                    <?php mangastarter_reader_first_page(); ?>
                </div>
                <!-- List View -->
                <div class="list-view" style="display:none;">
                    <?php
                    if (get_field('source') == 'Upload') {
                        echo '<ul class="image-list">';
                        foreach ($images as $image) {
                            echo '<li><img src="' . $image['full_image_url'] . '" alt="Image" class="list-view-image"></li>';
                        }
                        echo '</ul>';
                    } else {
                        $imageUrls = explode("\n", $images);
                        echo '<ul class="image-list">';
                        foreach ($imageUrls as $imageUrl) {
                            $imageUrl = preg_replace('/\s+/', '', $imageUrl);
                            echo '<li><img src="' . $imageUrl . '" alt="Image" class="list-view-image"></li>';
                        }
                        echo '</ul>';
                    }
                    ?>
                </div>
                <!-- End of List View -->
            </div>
        </div>

        <!-- Add the Previous and Next chapter buttons below the chapter title -->
        <div class="chapter-navigation-bottom uk-margin-top">
            <div class="uk-width-small-1-1">
                <div class="uk-clearfix">
                    <div class="uk-align-left">
                        <?php
                        // Check if there's a previous chapter
                        if (!empty($prev_chapter_url)) :
                        ?>
                            <a class="previous-chapter uk-button uk-button-primary" href="<?php echo esc_url($prev_chapter_url); ?>"><?php _e('Previous Chapter', 'your-theme-textdomain'); ?></a>
                        <?php endif; ?>
                    </div>

                    <div class="uk-align-right">
                        <?php
                        // Check if there's a next chapter
                        if (!empty($next_chapter_url)) :
                        ?>
                            <a class="next-chapter uk-button uk-button-primary" href="<?php echo esc_url($next_chapter_url); ?>"><?php _e('Next Chapter', 'your-theme-textdomain'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="uk-grid" data-uk-grid-margin>
            <div class="uk-width-small-1-1">
                <div class="uk-alert uk-alert-large uk-alert-warning">
                    <h3><?php the_title(); ?></h3>
                    <p><strong><?php _e('Tips', 'mangastarter'); ?>:</strong>
                        <?php printf(__('You are reading %1$s, please read %1$s scan online from right to left. You can use left (←) and right (→) keyboard keys or click on the %1$s image to browse between %1$s pages.', 'mangastarter'), get_the_title()); ?>
                </div>
            </div>
        </div>
    </div>
</article>

<script>
    (function ($, root, undefined) {
        $(function () {
            var title = document.title;
            var pages = [
                <?php
                if (get_field('source') == 'Upload') {
                    foreach ($images as $image) {
                        echo '{"page_image":"' . $image['full_image_url'] . '"},';
                    }
                } else {
                    foreach (explode("\n", $images) as $external) {
                        $external = preg_replace('/\s+/', '', $external);
                        echo '{"page_image":"' . $external . '"},';
                    }
                }
                ?>
            ];

            var prev_chapter = "<?php echo esc_url($prev_chapter_url); ?>";
            var next_chapter = "<?php echo esc_url($next_chapter_url); ?>";
            var first_chapter = 1;
            var last_chapter = pages.length;
            var preload_next = 3;
            var preload_back = 2;
            var current_page = 1;
            var base_url = window.location.href;
            var initialized = false;

            // Function to set button styles based on the active view
            function setActiveViewButton(view) {
                if (view === 'list') {
                    $('#list-view-button').addClass('active');
                    $('#paged-view-button').removeClass('active');
                } else {
                    $('#paged-view-button').addClass('active');
                    $('#list-view-button').removeClass('active');
                }
            }

            function changePage(id, noscroll, nohash) {
                id = parseInt(id);
                if (initialized && id == current_page)
                    return false;
                initialized = true;
                if (id == pages.length) {
                    if (next_chapter == "") {
                        $('a.next').addClass('disabled-button');
                        $('a.last').addClass('disabled-button');
                    }
                } else if (id > pages.length) {
                    if (next_chapter == "") {
                        alert('<?php _e('This is the last page.', 'mangastarter'); ?>');
                    } else {
                        location.href = next_chapter;
                    }
                    return false;
                } else {
                    $('.next').show();
                    $('a.next').removeClass('disabled-button');
                    $('a.last').removeClass('disabled-button');
                }

                if (id == 1) {
                    if (prev_chapter == "") {
                        $('a.previous').addClass('disabled-button');
                        $('a.first').addClass('disabled-button');
                    }
                } else if (id <= 0) {
                    if (prev_chapter == "") {
                        alert('<?php _e('This is the first page.', 'mangastarter'); ?>');
                    } else {
                        location.href = prev_chapter;
                    }
                    return false;
                } else {
                    $('.previous').show();
                    $('a.previous').removeClass('disabled-button');
                    $('a.first').removeClass('disabled-button');
                }

                preload(id);
                current_page = id;
                next = parseInt(id + 1);
                jQuery("html, body").stop(true, true);
                if (!noscroll)
                    $("html, body").animate({scrollTop: $('div.page-by-page').eq(0).offset().top});
                jQuery('.scan-page').attr('src', pages[current_page - 1].page_image);
                jQuery('.scan-page').attr('alt', '<?php _e('Page', 'mangastarter'); ?> ' + current_page);
                if (!nohash)
                    History.pushState(null, null, base_url);
                document.title = title;
                update_numberPanel();
                return false;
            }

            function prevPage() {
                changePage(current_page - 1);
                return false;
            }

            function nextPage() {
                changePage(current_page + 1);
                return false;
            }

            function firstPage() {
                changePage(first_chapter);
                return false;
            }

            function lastPage() {
                changePage(last_chapter);
                return false;
            }

            // Keyboard navigation function
            function handleArrowKeys(event) {
                if (event.keyCode === 37) { // Left arrow key
                    prevPage();
                } else if (event.keyCode === 39) { // Right arrow key
                    nextPage();
                }
            }

            // Add event listener for arrow keys
            $(document).keydown(handleArrowKeys);

            $('.previous').click(function () {
                prevPage();
            });

            $('.next').click(function () {
                nextPage();
            });

            $('.first').click(function () {
                firstPage();
            });

            $('.last').click(function () {
                lastPage();
            });

            function preload(id) {
                var array = [];
                var arraydata = [];
                for (i = -preload_back; i < preload_next; i++) {
                    if (id + i >= 0 && id + i < pages.length) {
                        array.push(pages[(id + i)].page_image);
                        arraydata.push(id + i);
                    }
                }

                jQuery.preload(array, {
                    threshold: 40,
                    enforceCache: true,
                    onComplete: function (data) {
                    }
                });
            }

            function update_numberPanel() {
                $('#page-list-select').val(current_page);
                $('#page-list-select').html(current_page);
            }

            $('select#page-list-select').change(function () {
                changePage(this.value);
            });

            // Toggle View Functionality
            function togglePagedView() {
                $('.page-by-page').show();
                $('.list-view').hide();
                setActiveViewButton('paged'); // Set Page by Page as active view
                setViewModeCookie('paged'); // Store the user's selection in a cookie
            }

            function toggleListView() {
                $('.page-by-page').hide();
                $('.list-view').show();
                setupListViewClickHandlers();
                setActiveViewButton('list'); // Set List View as active view
                setViewModeCookie('list'); // Store the user's selection in a cookie
            }

            function setupListViewClickHandlers() {
                // Scroll to the next image in LIST View when clicking an image
                $('.list-view-image').click(function () {
                    var currentImage = $(this);
                    var nextImage = currentImage.parent().next().find('.list-view-image');
                    if (nextImage.length > 0) {
                        $('html, body').animate({
                            scrollTop: nextImage.offset().top
                        }, 'fast');
                    } else {
                        // If there's no next image, go to the next chapter
                        if (next_chapter != "") {
                            location.href = next_chapter;
                        } else {
                            alert('<?php _e('This is the last page of the last chapter.', 'mangastarter'); ?>');
                        }
                    }
                });
            }

            $('#paged-view-button').click(function () {
                togglePagedView();
            });

            $('#list-view-button').click(function () {
                toggleListView();
            });

            // Initial View Mode based on Cookie
            var viewMode = getViewModeCookie();
            if (viewMode === 'list') {
                toggleListView();
            } else {
                togglePagedView();
            }

            // Function to set the user's selected view mode in a cookie
            function setViewModeCookie(view) {
                document.cookie = "viewMode=" + view + "; path=/";
            }

            // Function to get the user's selected view mode from the cookie
            function getViewModeCookie() {
                var name = "viewMode=";
                var decodedCookie = decodeURIComponent(document.cookie);
                var cookieArray = decodedCookie.split(';');
                for (var i = 0; i < cookieArray.length; i++) {
                    var cookie = cookieArray[i];
                    while (cookie.charAt(0) === ' ') {
                        cookie = cookie.substring(1);
                    }
                    if (cookie.indexOf(name) === 0) {
                        return cookie.substring(name.length, cookie.length);
                    }
                }
                return "";
            }
        });
    })(jQuery, this);
</script>
