<?php
/**
 * Import chapters from a server folder.
 *
 * Folder layout:  wp-content/manga-import/Series Name/Chapter Name/01.jpg
 * Originals are copied into the media library and left untouched.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function manga_import_dir() {
	$path = trailingslashit( WP_CONTENT_DIR ) . 'manga-import/';
	if ( ! file_exists( $path ) ) {
		wp_mkdir_p( $path );
	}
	return $path;
}

// Only allow real directories inside the import folder.
function manga_import_valid_path( $path ) {
	$base = realpath( manga_import_dir() );
	$real = realpath( $path );
	return $base && $real && is_dir( $real ) && 0 === strpos( $real, trailingslashit( $base ) );
}

function manga_imported_list() {
	return (array) get_option( 'manga_imported_chapters', array() );
}

function manga_is_imported( $path ) {
	$list = manga_imported_list();
	return isset( $list[ md5( $path ) ] );
}

function manga_mark_imported( $path ) {
	$list                = manga_imported_list();
	$list[ md5( $path ) ] = time();
	update_option( 'manga_imported_chapters', $list, false );
}

function manga_list_subdirs( $dir ) {
	$out = array();
	if ( is_dir( $dir ) ) {
		foreach ( scandir( $dir ) as $item ) {
			if ( '.' !== $item[0] && is_dir( $dir . '/' . $item ) ) {
				$out[] = $item;
			}
		}
	}
	natcasesort( $out );
	return array_values( $out );
}

function manga_list_images( $dir ) {
	$out = array();
	if ( is_dir( $dir ) ) {
		foreach ( scandir( $dir ) as $file ) {
			if ( in_array( strtolower( pathinfo( $file, PATHINFO_EXTENSION ) ), array( 'jpg', 'jpeg', 'png', 'gif', 'webp' ), true ) && is_readable( $dir . '/' . $file ) ) {
				$out[] = $file;
			}
		}
	}
	natcasesort( $out );
	return array_values( $out );
}

function manga_find_or_create_manga( $name ) {
	$found = get_posts( array(
		'post_type'      => 'manga',
		'post_status'    => 'any',
		'title'          => $name,
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	if ( $found ) {
		return (int) $found[0];
	}
	$id = wp_insert_post( array(
		'post_title'  => $name,
		'post_type'   => 'manga',
		'post_status' => 'publish',
	) );
	return is_wp_error( $id ) ? 0 : (int) $id;
}

function manga_import_chapter( $manga_dir, $chapter_dir ) {
	$path = manga_import_dir() . $manga_dir . '/' . $chapter_dir;

	if ( ! manga_import_valid_path( $path ) ) {
		return array( false, sprintf( __( 'Invalid folder: %s', 'manga-theme' ), $chapter_dir ) );
	}
	if ( manga_is_imported( $path ) ) {
		return array( false, sprintf( __( 'Already imported: %s', 'manga-theme' ), $chapter_dir ) );
	}

	$manga_id = manga_find_or_create_manga( $manga_dir );
	if ( ! $manga_id ) {
		return array( false, sprintf( __( 'Could not create manga: %s', 'manga-theme' ), $manga_dir ) );
	}

	$volume  = extract_volume_number( $chapter_dir );
	$chapter = extract_chapter_number( $chapter_dir );
	if ( ! $chapter && ! $volume && preg_match( '/(\d+(?:\.\d+)?)/', $chapter_dir, $m ) ) {
		$chapter = (float) $m[1];
	}
	$label = ( $volume || $chapter ) ? manga_make_label( $volume, $chapter ) : $chapter_dir;
	$title = get_the_title( $manga_id ) . ' - ' . $label;

	$dupe = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'any',
		'title'          => $title,
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	if ( $dupe ) {
		manga_mark_imported( $path );
		return array( false, sprintf( __( 'Chapter already exists: %s', 'manga-theme' ), $title ) );
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	// Pages are shown at full size, so skip thumbnail generation and downscaling.
	add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );
	add_filter( 'big_image_size_threshold', '__return_false' );

	$urls = array();
	foreach ( manga_list_images( $path ) as $file ) {
		$source = $path . '/' . $file;
		$tmp    = wp_tempnam( $file );
		if ( ! $tmp || ! copy( $source, $tmp ) ) {
			continue;
		}
		$upload = wp_handle_sideload(
			array(
				'name'     => $file,
				'tmp_name' => $tmp,
				'error'    => 0,
				'size'     => filesize( $source ),
			),
			array( 'test_form' => false )
		);
		if ( isset( $upload['error'] ) ) {
			@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
			continue;
		}
		$attachment_id = wp_insert_attachment( array(
			'post_mime_type' => $upload['type'],
			'post_title'     => sanitize_file_name( $file ),
			'post_status'    => 'inherit',
		), $upload['file'] );
		if ( $attachment_id && ! is_wp_error( $attachment_id ) ) {
			wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
			$urls[] = wp_get_attachment_url( $attachment_id );
		}
	}

	remove_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );
	remove_filter( 'big_image_size_threshold', '__return_false' );

	if ( ! $urls ) {
		return array( false, sprintf( __( 'No images imported from: %s', 'manga-theme' ), $chapter_dir ) );
	}

	$meta = array(
		'connected_manga_id' => $manga_id,
		'image_links'        => implode( "\n", $urls ),
		'imported_from_path' => $path,
	);
	if ( $volume ) {
		$meta['volume_number'] = $volume;
	}
	if ( $chapter || $volume ) {
		$meta['chapter_number'] = $chapter;
	}

	$chapter_id = wp_insert_post( array(
		'post_title'  => $title,
		'post_type'   => 'chapter',
		'post_status' => 'publish',
		'meta_input'  => $meta,
	) );

	if ( ! $chapter_id || is_wp_error( $chapter_id ) ) {
		return array( false, sprintf( __( 'Could not create chapter: %s', 'manga-theme' ), $title ) );
	}

	manga_mark_imported( $path );
	manga_refresh_stats( $manga_id );
	return array( true, sprintf( __( 'Imported %1$s (%2$d pages)', 'manga-theme' ), $label, count( $urls ) ) );
}

/* ------------------------------------------------------------
 * Admin page
 * ---------------------------------------------------------- */
function manga_register_import_page() {
	add_submenu_page(
		'edit.php?post_type=manga',
		__( 'Import from Folder', 'manga-theme' ),
		__( 'Import from Folder', 'manga-theme' ),
		'manage_options',
		'folder-import',
		'manga_render_import_page'
	);
}
add_action( 'admin_menu', 'manga_register_import_page' );

function manga_render_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$base     = manga_import_dir();
	$page_url = admin_url( 'edit.php?post_type=manga&page=folder-import' );
	$current  = isset( $_GET['manga'] ) ? wp_unslash( $_GET['manga'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$notices  = array();

	if ( ! is_string( $current ) || ! manga_import_valid_path( $base . $current ) ) {
		$current = '';
	}

	if ( isset( $_GET['clear_import_list'] ) ) {
		check_admin_referer( 'manga_import_clear' );
		delete_option( 'manga_imported_chapters' );
		$notices[] = array( 'success', __( 'Import history cleared.', 'manga-theme' ) );
	}

	if ( 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['manga_import_nonce'] ) ) {
		check_admin_referer( 'manga_import', 'manga_import_nonce' );
		wp_raise_memory_limit( 'image' );
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}

		$manga_dir = isset( $_POST['manga_dir'] ) && is_string( $_POST['manga_dir'] ) ? wp_unslash( $_POST['manga_dir'] ) : '';

		if ( '' !== $manga_dir && manga_import_valid_path( $base . $manga_dir ) ) {
			if ( isset( $_POST['create_manga'] ) ) {
				$id        = manga_find_or_create_manga( $manga_dir );
				$notices[] = $id
					? array( 'success', sprintf( __( 'Manga entry ready: %s', 'manga-theme' ), $manga_dir ) )
					: array( 'error', __( 'Could not create the manga entry.', 'manga-theme' ) );
			} else {
				$chapters = array();
				if ( ! empty( $_POST['import_one'] ) && is_string( $_POST['import_one'] ) ) {
					$chapters[] = wp_unslash( $_POST['import_one'] );
				} elseif ( ! empty( $_POST['selected_chapters'] ) && is_array( $_POST['selected_chapters'] ) ) {
					$chapters = array_filter( array_map( 'strval', wp_unslash( $_POST['selected_chapters'] ) ) );
				}
				$ok   = 0;
				$fail = array();
				foreach ( $chapters as $chapter_dir ) {
					list( $success, $message ) = manga_import_chapter( $manga_dir, $chapter_dir );
					if ( $success ) {
						$ok++;
					} else {
						$fail[] = $message;
					}
				}
				if ( $ok ) {
					$notices[] = array( 'success', sprintf( _n( 'Imported %d chapter.', 'Imported %d chapters.', $ok, 'manga-theme' ), $ok ) );
				}
				foreach ( $fail as $message ) {
					$notices[] = array( 'error', $message );
				}
				if ( ! $chapters ) {
					$notices[] = array( 'warning', __( 'No chapters selected.', 'manga-theme' ) );
				}
			}
			$current = $manga_dir;
		}
	}

	clearstatcache();
	$manga_dirs = manga_list_subdirs( $base );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import from Folder', 'manga-theme' ); ?></h1>

		<div class="notice notice-info inline">
			<p><?php esc_html_e( 'Upload folders with FTP or your file manager, then import them here.', 'manga-theme' ); ?></p>
			<p><code><?php echo esc_html( $base ); ?>Series Name/Chapter 1/01.jpg</code></p>
			<p class="description"><?php esc_html_e( 'Folder names like "Vol. 2 Ch. 5", "Chapter 5" or "5" are understood. Your original files are copied, not moved.', 'manga-theme' ); ?></p>
		</div>

		<?php foreach ( $notices as $notice ) : ?>
			<div class="notice notice-<?php echo esc_attr( $notice[0] ); ?> is-dismissible"><p><?php echo esc_html( $notice[1] ); ?></p></div>
		<?php endforeach; ?>

		<p>
			<a class="button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'clear_import_list', '1', $page_url ), 'manga_import_clear' ) ); ?>"><?php esc_html_e( 'Clear import history', 'manga-theme' ); ?></a>
			<span class="description"><?php esc_html_e( 'Use this to make already imported folders show up again.', 'manga-theme' ); ?></span>
		</p>

		<div class="manga-import-grid">
			<div class="manga-import-col manga-import-series">
				<h2><?php esc_html_e( 'Series folders', 'manga-theme' ); ?></h2>
				<?php if ( ! $manga_dirs ) : ?>
					<p class="description"><?php esc_html_e( 'No folders found yet.', 'manga-theme' ); ?></p>
				<?php else : ?>
					<ul>
						<?php foreach ( $manga_dirs as $dir ) :
							$pending = 0;
							foreach ( manga_list_subdirs( $base . $dir ) as $sub ) {
								if ( ! manga_is_imported( $base . $dir . '/' . $sub ) ) {
									$pending++;
								}
							}
							?>
							<li>
								<a class="<?php echo $dir === $current ? 'is-current' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'manga', rawurlencode( $dir ), $page_url ) ); ?>">
									<strong><?php echo esc_html( $dir ); ?></strong>
									<span><?php echo esc_html( sprintf( _n( '%d new chapter', '%d new chapters', $pending, 'manga-theme' ), $pending ) ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<div class="manga-import-col manga-import-chapters">
				<?php if ( '' === $current ) : ?>
					<p class="description"><?php esc_html_e( 'Choose a series on the left.', 'manga-theme' ); ?></p>
				<?php else :
					$rows = array();
					foreach ( manga_list_subdirs( $base . $current ) as $sub ) {
						$path = $base . $current . '/' . $sub;
						if ( ! manga_is_imported( $path ) ) {
							$rows[ $sub ] = count( manga_list_images( $path ) );
						}
					}
					?>
					<h2><?php echo esc_html( $current ); ?></h2>

					<?php if ( ! $rows ) : ?>
						<p><?php esc_html_e( 'No new chapters in this folder.', 'manga-theme' ); ?></p>
					<?php else : ?>
						<form method="post">
							<?php wp_nonce_field( 'manga_import', 'manga_import_nonce' ); ?>
							<input type="hidden" name="manga_dir" value="<?php echo esc_attr( $current ); ?>">
							<table class="wp-list-table widefat fixed striped">
								<thead>
									<tr>
										<td class="check-column"><input type="checkbox" id="manga-import-all"></td>
										<th><?php esc_html_e( 'Chapter folder', 'manga-theme' ); ?></th>
										<th><?php esc_html_e( 'Images', 'manga-theme' ); ?></th>
										<th><?php esc_html_e( 'Action', 'manga-theme' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $rows as $sub => $count ) : ?>
										<tr>
											<th class="check-column"><input type="checkbox" class="manga-import-check" name="selected_chapters[]" value="<?php echo esc_attr( $sub ); ?>"></th>
											<td><strong><?php echo esc_html( $sub ); ?></strong></td>
											<td><?php echo (int) $count; ?></td>
											<td><button type="submit" name="import_one" value="<?php echo esc_attr( $sub ); ?>" class="button button-small"><?php esc_html_e( 'Import', 'manga-theme' ); ?></button></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<p><button type="submit" class="button button-primary"><?php esc_html_e( 'Import selected chapters', 'manga-theme' ); ?></button></p>
						</form>
					<?php endif; ?>

					<form method="post" class="manga-import-create">
						<?php wp_nonce_field( 'manga_import', 'manga_import_nonce' ); ?>
						<input type="hidden" name="manga_dir" value="<?php echo esc_attr( $current ); ?>">
						<button type="submit" name="create_manga" value="1" class="button"><?php esc_html_e( 'Create manga entry only', 'manga-theme' ); ?></button>
						<span class="description"><?php esc_html_e( 'Importing chapters creates the entry automatically if it is missing.', 'manga-theme' ); ?></span>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
