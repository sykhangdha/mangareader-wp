<?php
/**
 * Features for the addition, sorting and displaying of chapters
 * in the back end and the front
 */
 
/**
 * Adds new canocical urls
 */
function mangastarter_chapter_canonical_urls() {
    global $wp_the_query;
    if ( !$id = $wp_the_query->get_queried_object_id() ) {
        return;
    }

    if ( is_singular( 'chapters' ) ) {
        $relationship = get_field( 'manga' );
        $post_slug = get_post_field( 'post_name' );
        $link = get_permalink( $id );
        foreach( $relationship as $parent ) {
            echo '<link rel="canonical" href="' . site_url() . '/manga/' . $parent->post_name . '/' . $post_slug . '">';
        }
    } else {
        $link = get_permalink( $id );
        echo '<link rel="canonical" href="' . $link . '">';
    }
}
remove_action( 'wp_head', 'rel_canonical' );
add_action( 'wp_head', 'mangastarter_chapter_canonical_urls' );

/**
 * Adds support for hierarchical urls in chapters
 */
function mangastarter_hierarchical_urls() {
    add_rewrite_rule( '^manga/([^/]*)/([^/]*)/?', 'index.php?chapters=$matches[2]', 'top' );
}
add_action( 'init', 'mangastarter_hierarchical_urls' );
 
/**
 * The chapter list in the manga page
 */
function mangastarter_chapter_list() {
    $manga_chapters = get_posts( array(
        'post_type' => 'chapters',
        'order' => 'DESC',
        'numberposts' => -1,
        'meta_query' => array( array(
            'key' => 'manga', // name of custom field
            'value' => '"' . get_the_ID() . '"', // matches exactly "123", not just 123. This prevents a match for "1234"
            'compare' => 'LIKE'
        ) )
    ) );
    $post_slug = get_post_field( 'post_name' );
    ?>

    <div class="chapter-list">
        <?php
        if ( $manga_chapters ) {
            foreach ( $manga_chapters as $chapters ) {
                $chapter_title = get_the_title( $chapters->ID );
                // Remove the post type manga name from the chapter title
                $chapter_number = preg_replace('/^[^\d]+(\d+).*$/i', '$1', $chapter_title);
                ?>
                <a href="<?php echo site_url(); ?>/manga/<?php echo $post_slug; ?>/<?php echo $chapters->post_name; ?>" class="chapter-item">
                    <div>
                        Chapter <?php echo $chapter_number; ?>
                    </div>
                    <small><?php the_field( 'title', $chapters->ID ); ?> </small>
                    <div class="date">
                        <?php echo get_the_date( 'F d, Y', $chapters->ID ); ?>
                    </div>
                </a>
            <?php
            }
        } else {
            echo '<p>' . __( 'There are no chapters available.', 'mangastarter' ) . '</p>';
        }
        ?>
    </div>

    <style>
        .chapter-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .chapter-item {
            width: calc(33.33% - 10px); /* Adjust the width as needed for your grid layout */
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            text-decoration: none; /* Remove the default underline */
            color: #333; /* Text color */
        }

        .chapter-item:hover {
            background-color: #f5f5f5; /* Add a background color on hover */
        }

        @media screen and (max-width: 768px) {
            .chapter-item {
                width: calc(50% - 10px); /* Adjust the width for smaller screens */
            }
        }

        .date {
            margin-top: 10px;
            font-size: 12px;
        }
    </style>
    <?php
}


/**
 * All the other chapters of the same manga
 */
function mangastarter_reader_chapter_list() {
	$relationship = get_field( 'manga' );
	
	foreach( $relationship as $parent ) {
		$manga_chapters = get_posts( array(
			'post_type' => 'chapters',
			'order' => 'DESC',
			'numberposts' => -1,
			'meta_query' => array( array(
				'key' => 'manga',
				'value' => '"' . $parent->ID . '"',
				'compare' => 'LIKE'
			) )
		) );
		
	$post_slug = get_post_field( 'post_name', $parent->ID );
		echo '<option selected>' . get_the_title( $parent->ID ) . ' - ' . __( 'Chapter List', 'mangastarter' ) . '</option>';		
		echo '<option value="' . site_url() . '/manga/' . $post_slug . '">' . __( 'Return to', 'mangastarter' ) . ' ' . get_the_title( $parent->ID ) . '</option>';
		
		foreach( $manga_chapters as $chapters ) {
			echo '<option value="' . site_url() . '/manga/' . $post_slug . '/' . $chapters->post_name . '">' . get_the_title( $chapters->ID ) . '</option>';
		}
	}
}

/**
 * All the chapter pages in the select form
 */
function mangastarter_reader_chapter_pages() {
	global $post;
	$relationship = get_field( 'manga' );
	
	if( get_field( 'source' ) == 'Upload' ) {
		$images = acf_photo_gallery( 'upload', $post->ID );
		sort( $images );
	} else {
		$images = get_field( 'external', $post->ID );
	}
	$ch_op = 1;
	
	if( get_field( 'source' ) == 'Upload' ) {
		foreach( $images as $image ) {
			echo '<option value="' . $ch_op . '">' . $ch_op++ . '</option>';
		}
	} else {
		foreach(explode("\n", $images) as $external) {
			echo '<option value="' . $ch_op . '">' . $ch_op++ . '</option>';
		}
	}
}

/**
 * The first page of the chapter in the viewer
 */
function mangastarter_reader_first_page() {
	global $post;
	$relationship = get_field( 'manga' );
	
	if( get_field( 'source' ) == 'Upload' ) {
		$images = acf_photo_gallery( 'upload', $post->ID );
		sort( $images );
	} else {
		$images = get_field( 'external', $post->ID );
	}

	if( get_field( 'source' ) == 'Upload' ) {
		foreach( $images as $image ) {
			echo '<a href="javascript:"><img class="uk-align-center scan-page next" src="' . $image['full_image_url'] . '" alt="' .get_the_title() . ' - ' . __( 'Page', 'mangastarter' ) . ' #1"></a>';
			break;
		}
	} else {
		foreach(explode("\n", $images) as $external) {
			echo '<a href="javascript:"><img class="uk-align-center scan-page next" src="' . $external . '" alt="' . get_the_title() . ' - ' . __( 'Page', 'mangastarter' ) . ' #1"></a>';
			break;
		}
	}
}

/**
 * Changes the default placeholder title while adding a new chapter
 */
function mangastarter_chapter_title( $title ){
    $screen = get_current_screen();
    if  ( 'chapters' == $screen->post_type ) {
        $title = __( 'Ex: One Punch Man 001', 'mangastarter' );
    }
    return $title;
}
add_filter( 'enter_title_here', 'mangastarter_chapter_title' );

/**
 * Lock media panel to "Uploaded to this post" in post creation/edit
 */
function mangastarter_mediapanel_lock_uploaded() {
?>
    <script>
        jQuery(document).on("DOMNodeInserted", function(){
            jQuery('select.attachment-filters [value="uploaded"]').attr( 'selected', true ).parent().trigger('change');
        });
    </script>
<?php
}
add_action( 'admin_footer-post-new.php', 'mangastarter_mediapanel_lock_uploaded' );
add_action( 'admin_footer-post.php', 'mangastarter_mediapanel_lock_uploaded' );

/**
 * Create custom directories inside the uploads folder for individual chapter image uploads
 */
function mangastarter_uploads_directories( $args ) {

	if ( !is_customize_preview() ) {
		if ( is_admin() ) {
			$id = !empty( $_REQUEST['post_id'] ) ? $_REQUEST['post_id'] : '';
			$parent = get_post( $id )->post_parent;
			$slug = str_replace(' ', '-', get_post( $id )->post_title);
 
			// Check the post-type of the current post
			// assign directory to upload to
			// assign URL to connect to
			if( "mangas" == get_post_type( $id ) || "mangas" == get_post_type( $parent ) ) {
				$args['path'] = WP_CONTENT_DIR . '/uploads/covers/' . $slug . '';
				$args['url']  = WP_CONTENT_URL . '/uploads/covers/' . $slug . '';
			}
			if( "chapters" == get_post_type( $id ) || "chapters" == get_post_type( $parent ) ) {
				$args['path'] = WP_CONTENT_DIR . '/uploads/chapters/' . $slug . '';
				$args['url']  = WP_CONTENT_URL . '/uploads/chapters/' . $slug . '';
			}
		}
		return $args;
	}
}
add_filter( 'upload_dir', 'mangastarter_uploads_directories' );
