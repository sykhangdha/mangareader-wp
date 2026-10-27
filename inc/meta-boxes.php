<?php
/**
 * Meta boxes for manga and chapters, plus the single chapter save handler.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function manga_add_meta_boxes() {
	add_meta_box( 'manga_details', __( 'Manga Details', 'manga-theme' ), 'manga_render_details_box', 'manga', 'normal', 'high' );
	add_meta_box( 'chapter_connection', __( 'Connect to Manga', 'manga-theme' ), 'manga_render_connection_box', 'chapter', 'side', 'high' );
	add_meta_box( 'chapter_images', __( 'Chapter Images', 'manga-theme' ), 'manga_render_images_box', 'chapter', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'manga_add_meta_boxes' );

function manga_admin_assets( $hook ) {
	$uri    = get_template_directory_uri();
	$screen = get_current_screen();
	$is_edit = in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && $screen && 'chapter' === $screen->post_type;
	$is_import = false !== strpos( $hook, 'folder-import' );

	if ( $is_edit || $is_import || ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) && $screen && 'manga' === $screen->post_type ) ) {
		wp_enqueue_style( 'manga-admin', $uri . '/assets/css/admin.css', array(), MANGA_THEME_VERSION );
	}
	if ( $is_edit || $is_import ) {
		if ( $is_edit ) {
			wp_enqueue_media();
		}
		wp_enqueue_script( 'manga-admin', $uri . '/assets/js/admin.js', array( 'jquery' ), MANGA_THEME_VERSION, true );
	}
}
add_action( 'admin_enqueue_scripts', 'manga_admin_assets' );

/* ------------------------------------------------------------
 * Manga details
 * ---------------------------------------------------------- */
function manga_render_details_box( $post ) {
	wp_nonce_field( 'manga_details_save', 'manga_details_nonce' );
	$fields = array(
		'_manga_author' => array( __( 'Author', 'manga-theme' ), 'text' ),
		'_manga_artist' => array( __( 'Artist', 'manga-theme' ), 'text' ),
		'_manga_year'   => array( __( 'Year', 'manga-theme' ), 'number' ),
	);
	foreach ( $fields as $key => $def ) {
		printf(
			'<p class="manga-mb-row"><label for="%1$s">%2$s</label><input type="%3$s" id="%1$s" name="%1$s" value="%4$s"></p>',
			esc_attr( $key ),
			esc_html( $def[0] ),
			esc_attr( $def[1] ),
			esc_attr( get_post_meta( $post->ID, $key, true ) )
		);
	}
}

function manga_save_details( $post_id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['manga_details_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manga_details_nonce'] ) ), 'manga_details_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( '_manga_author', '_manga_artist', '_manga_year' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
		}
	}
}
add_action( 'save_post_manga', 'manga_save_details' );

/* ------------------------------------------------------------
 * Chapter: connection
 * ---------------------------------------------------------- */
function manga_render_connection_box( $post ) {
	wp_nonce_field( 'chapter_connection_save', 'chapter_connection_nonce' );

	$connected = (int) get_post_meta( $post->ID, 'connected_manga_id', true );
	$volume    = get_post_meta( $post->ID, 'volume_number', true );
	$chapter   = get_post_meta( $post->ID, 'chapter_number', true );
	$list      = get_posts( array(
		'post_type'      => 'manga',
		'post_status'    => array( 'publish', 'draft', 'private' ),
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
		'no_found_rows'  => true,
	) );
	?>
	<p class="manga-mb-row">
		<label for="connected_manga"><?php esc_html_e( 'Select manga', 'manga-theme' ); ?></label>
		<select name="connected_manga" id="connected_manga">
			<option value="">— <?php esc_html_e( 'Select manga', 'manga-theme' ); ?> —</option>
			<?php foreach ( $list as $manga ) : ?>
				<option value="<?php echo (int) $manga->ID; ?>" <?php selected( $connected, $manga->ID ); ?>><?php echo esc_html( $manga->post_title ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="manga-mb-row">
		<label for="volume_number"><?php esc_html_e( 'Volume number', 'manga-theme' ); ?></label>
		<input type="number" name="volume_number" id="volume_number" min="0" step="1" value="<?php echo esc_attr( $volume ); ?>" placeholder="<?php esc_attr_e( 'Leave empty if none', 'manga-theme' ); ?>">
	</p>
	<p class="manga-mb-row">
		<label for="chapter_number"><?php esc_html_e( 'Chapter number', 'manga-theme' ); ?></label>
		<input type="number" name="chapter_number" id="chapter_number" min="0" step="0.1" value="<?php echo esc_attr( $chapter ); ?>">
	</p>
	<p class="manga-mb-row">
		<label for="chapter_subtitle"><?php esc_html_e( 'Chapter title (optional)', 'manga-theme' ); ?></label>
		<input type="text" name="chapter_subtitle" id="chapter_subtitle" value="<?php echo esc_attr( get_post_meta( $post->ID, 'chapter_subtitle', true ) ); ?>" placeholder="<?php esc_attr_e( 'e.g. Who Is The Reaper\'s Enemy?', 'manga-theme' ); ?>">
	</p>
	<p class="description"><?php esc_html_e( 'The post title is generated from the numbers above when you save.', 'manga-theme' ); ?></p>
	<?php
}

/* ------------------------------------------------------------
 * Chapter: images
 * ---------------------------------------------------------- */
function manga_render_images_box( $post ) {
	wp_nonce_field( 'chapter_images_save', 'chapter_images_nonce' );
	$links = (string) get_post_meta( $post->ID, 'image_links', true );
	?>
	<div class="manga-images-box">
		<p>
			<button type="button" class="button button-primary" id="chapter-add-media"><?php esc_html_e( 'Add from Media Library', 'manga-theme' ); ?></button>
			<button type="button" class="button" id="chapter-sort-urls"><?php esc_html_e( 'Sort naturally (1, 2, 10)', 'manga-theme' ); ?></button>
		</p>
		<div id="chapter-image-preview" class="manga-image-preview"></div>
		<p><label for="chapter-image-links"><strong><?php esc_html_e( 'Image URLs, one per line, in reading order', 'manga-theme' ); ?></strong></label></p>
		<textarea name="chapter_image_links" id="chapter-image-links" class="manga-image-urls" rows="10"><?php echo esc_textarea( $links ); ?></textarea>
	</div>
	<?php
}

/* ------------------------------------------------------------
 * Chapter: single save handler
 * ---------------------------------------------------------- */
function manga_post_number( $key ) {
	if ( ! isset( $_POST[ $key ] ) || ! is_scalar( $_POST[ $key ] ) ) {
		return null;
	}
	$value = trim( (string) wp_unslash( $_POST[ $key ] ) );
	return ( '' !== $value && is_numeric( $value ) ) ? (float) $value : null;
}

function manga_save_chapter( $post_id, $post ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$old_manga = (int) get_post_meta( $post_id, 'connected_manga_id', true );

	// Connection, numbers and generated title.
	if ( isset( $_POST['chapter_connection_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['chapter_connection_nonce'] ) ), 'chapter_connection_save' ) ) {
		$manga_id = isset( $_POST['connected_manga'] ) ? absint( $_POST['connected_manga'] ) : 0;
		$volume   = manga_post_number( 'volume_number' );
		$chapter  = manga_post_number( 'chapter_number' );

		$subtitle = isset( $_POST['chapter_subtitle'] ) && is_string( $_POST['chapter_subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['chapter_subtitle'] ) ) : '';
		if ( '' !== $subtitle ) {
			update_post_meta( $post_id, 'chapter_subtitle', $subtitle );
		} else {
			delete_post_meta( $post_id, 'chapter_subtitle' );
		}

		if ( $manga_id && 'manga' === get_post_type( $manga_id ) ) {
			update_post_meta( $post_id, 'connected_manga_id', $manga_id );

			if ( $volume && $volume > 0 ) {
				update_post_meta( $post_id, 'volume_number', $volume );
			} else {
				delete_post_meta( $post_id, 'volume_number' );
			}
			if ( null !== $chapter ) {
				update_post_meta( $post_id, 'chapter_number', $chapter );
			} else {
				delete_post_meta( $post_id, 'chapter_number' );
			}

			$parts = array();
			if ( $volume && $volume > 0 ) {
				$parts[] = 'Vol. ' . manga_fmt_num( $volume );
			}
			if ( null !== $chapter ) {
				$parts[] = 'Ch. ' . manga_fmt_num( $chapter );
			}

			if ( $parts ) {
				$new_title = get_the_title( $manga_id ) . ' - ' . implode( ' ', $parts );
				$update    = array(
					'ID'         => $post_id,
					'post_title' => $new_title,
				);
				// Replace the auto-generated slug (numeric or auto-draft) with a readable one.
				if ( '' === $post->post_name || (string) $post_id === $post->post_name || 0 === strpos( $post->post_name, 'auto-draft' ) ) {
					$update['post_name'] = sanitize_title( $new_title );
				}

				remove_action( 'save_post_chapter', 'manga_save_chapter', 10 );
				wp_update_post( $update );
				add_action( 'save_post_chapter', 'manga_save_chapter', 10, 2 );
			}
		}
	}

	// Images.
	if ( isset( $_POST['chapter_images_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['chapter_images_nonce'] ) ), 'chapter_images_save' ) && isset( $_POST['chapter_image_links'] ) && is_string( $_POST['chapter_image_links'] ) ) {
		$lines = preg_split( '/\r\n|\r|\n/', wp_unslash( $_POST['chapter_image_links'] ) );
		$urls  = array_values( array_filter( array_map( 'esc_url_raw', array_map( 'trim', $lines ) ) ) );
		update_post_meta( $post_id, 'image_links', implode( "\n", $urls ) );
	}

	// Keep the stats of both the old and the new manga correct.
	$new_manga = (int) get_post_meta( $post_id, 'connected_manga_id', true );
	manga_refresh_stats( $new_manga );
	if ( $old_manga && $old_manga !== $new_manga ) {
		manga_refresh_stats( $old_manga );
	}
}
add_action( 'save_post_chapter', 'manga_save_chapter', 10, 2 );
