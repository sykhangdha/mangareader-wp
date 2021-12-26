<?php
/**
 * Template part for displaying chapter posts
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 */
// Get the chapter parent (manga) 
$relationship = get_field( 'manga' );
http://hasky.epizy.com/mangareader/skyadmin/edit-tags.php?taxonomy=scanlators&post_type=chapters
// Source of images, internal or external.
if( get_field('source') == 'Upload' ) {
    $images = acf_photo_gallery( 'upload', $post->ID );
    sort( $images );
} else {
    $images = get_field( 'external', $post->ID );
}
?>

<?php
echo do_shortcode('[wp_post_nav]') //wp-post-nav display previous and next 
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
                <?php the_title( '<h2 class="uk-text-center">', '</h2>' ); ?>
            </div>
            
            <div class="uk-width-small-1-1 uk-width-medium-1-3">
				<div class="uk-clearfix">
					<div class="uk-align-right">
						<div class="uk-button-group">
							<a class="first uk-button uk-button-primary disabled-button" href="javascript:"><i class="uk-icon-angle-double-left"></i></a>
							<a class="previous uk-button uk-button-primary disabled-button" href="javascript:"><i class="uk-icon-angle-left"></i></a>
						</div>
						
						<div class="uk-button uk-form-select" data-uk-form-select>
							<span id="page-list" class="uk-text-center"></span>
							<i class="uk-icon-angle-down"></i>
							<select id="page-list" class="selectpicker">
								<?php mangastarter_reader_chapter_pages(); ?>
							</select>
						</div>
						
						<div class="uk-button-group">
							<a class="next uk-button uk-button-primary" href="javascript:"><i class="uk-icon-angle-right"></i></a>
							<a class="last uk-button uk-button-primary" href="javascript:"><i class="uk-icon-angle-double-right"></i></a>
						</div>
					</div>
				</div>
            </div>
        </div>
    </header><!-- .entry-header -->

	
    <div class="entry-content uk-margin-top">
        <div class="uk-grid" data-uk-grid-margin>
            <div class="uk-width-small-1-1">
                <div class="page-by-page">
                    <?php mangastarter_reader_first_page(); ?>
                </div>
            </div>
        </div>
        
        <div class="uk-grid" data-uk-grid-margin>
            <div class="uk-width-small-1-1">
                <div class="uk-alert uk-alert-large uk-alert-warning">
                    <h3><?php the_title(); ?></h3>
                    <p><strong><?php _e( 'Tips', 'mangastarter' ); ?>:</strong>
                    <?php printf( __( 'You are reading %1$s, please read %1$s scan online from right to left. You can use left (←) and right (→) keyboard keys or click on the %1$s image to browse between %1$s pages.', 'mangastarter' ), get_the_title() ); ?>
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
                if( get_field('source') == 'Upload' ) {
                    foreach( $images as $image ) {
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
            
            var prev_chapter = "";
            var next_chapter = "";
            var first_chapter = 1;
            var last_chapter = pages.length;
            var preload_next = 3;
            var preload_back = 2;
            var current_page = 1;
            var base_url = window.location.href;
            var initialized = false;
            
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
                        alert('<?php _e( 'This is the last page.', 'mangastarter' ); ?>');
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
                        alert('<?php _e( 'This is the first page.', 'mangastarter' ); ?>');
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
                jQuery('.scan-page').attr('alt', '<?php _e( 'Page', 'mangastarter' ); ?> ' + current_page);
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
            
            $('.previous').click(function() {
                prevPage();
            });
            
            $('.next').click(function() {
                nextPage();
            });
            
            $('.first').click(function() {
                firstPage();
            });
            
            $('.last').click(function() {
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
                $('#page-list').val(current_page);
                $('#page-list').html(current_page);
            }
            
            $('select#page-list').change(function () {
                changePage(this.value);
            });
            
            $(document).on('keyup', function (e) {
                KeyCheck(e);
            });
            
            function KeyCheck(e) {
                var ev = e || window.event;
                ev.preventDefault();
                var KeyID = ev.keyCode;
                switch (KeyID) {
                    case 33:
                    case 37:
                    prevPage();
                    break;
                    case 34:
                    case 39:
                    nextPage();
                    break;
                }
            }
        });
    })(jQuery, this);
</script>
