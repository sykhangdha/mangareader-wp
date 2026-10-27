<?php
/**
 * Shared helpers: chapter parsing/ordering, cached stats, grid output.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------
 * Parsing
 * ---------------------------------------------------------- */
function extract_volume_number( $title ) {
	return preg_match( '/\bVol(?:ume|\.)?\s*(\d+(?:\.\d+)?)/i', (string) $title, $m ) ? (float) $m[1] : 0;
}

function extract_chapter_number( $title ) {
	return preg_match( '/\bCh(?:apter|\.)?\s*(\d+(?:\.\d+)?)/i', (string) $title, $m ) ? (float) $m[1] : 0;
}

function manga_fmt_num( $n ) {
	$n = (float) $n;
	return ( $n == floor( $n ) ) ? (string) (int) $n : (string) $n;
}

function manga_make_label( $volume, $chapter ) {
	return $volume
		? sprintf( 'Vol. %s Ch. %s', manga_fmt_num( $volume ), manga_fmt_num( $chapter ) )
		: sprintf( 'Ch. %s', manga_fmt_num( $chapter ) );
}

/* ------------------------------------------------------------
 * Chapter info + ordering
 * ---------------------------------------------------------- */

// Volume / chapter numbers for one chapter. Uses meta, falls back to the title.
function manga_chapter_info( $chapter_id ) {
	$title = get_the_title( $chapter_id );
	$ch    = (float) get_post_meta( $chapter_id, 'chapter_number', true );
	$vol   = (float) get_post_meta( $chapter_id, 'volume_number', true );

	// Titles look like "Series - Vol. 2 Ch. 5": parse only the part after the series name.
	$pos  = strrpos( $title, ' - ' );
	$part = ( false !== $pos ) ? substr( $title, $pos + 3 ) : $title;

	if ( ! $ch ) {
		$ch = extract_chapter_number( $part );
	}
	if ( ! $vol ) {
		$vol = extract_volume_number( $part );
	}

	return array(
		'id'      => (int) $chapter_id,
		'chapter' => $ch,
		'volume'  => $vol,
		'label'   => manga_make_label( $vol, $ch ),
		'subtitle' => (string) get_post_meta( $chapter_id, 'chapter_subtitle', true ),
	);
}

function manga_chapter_label( $chapter_id ) {
	$info = manga_chapter_info( $chapter_id );
	return $info['label'];
}

// Load chapters for many manga with one query and cache them for this request.
function manga_prime_chapters( $manga_ids ) {
	global $manga_chapter_cache;
	if ( ! is_array( $manga_chapter_cache ) ) {
		$manga_chapter_cache = array();
	}

	$ids = array_values( array_diff( array_map( 'intval', (array) $manga_ids ), array_keys( $manga_chapter_cache ) ) );
	if ( ! $ids ) {
		return;
	}

	$posts = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'no_found_rows'  => true,
		'meta_query'     => array(
			array(
				'key'     => 'connected_manga_id',
				'value'   => $ids,
				'compare' => 'IN',
			),
		),
	) );

	$grouped = array_fill_keys( $ids, array() );
	foreach ( $posts as $post ) {
		$mid = (int) get_post_meta( $post->ID, 'connected_manga_id', true );
		if ( ! isset( $grouped[ $mid ] ) ) {
			continue;
		}
		$info              = manga_chapter_info( $post->ID );
		$info['post']      = $post;
		$info['vol_key']   = $info['volume'] ? $info['volume'] : PHP_INT_MAX;
		$grouped[ $mid ][] = $info;
	}

	foreach ( $grouped as $mid => $items ) {
		// Newest first. Chapters without a volume count as newer than any volume.
		usort( $items, function ( $a, $b ) {
			return array( $b['vol_key'], $b['chapter'] ) <=> array( $a['vol_key'], $a['chapter'] );
		} );
		$manga_chapter_cache[ $mid ] = $items;
	}
}

function manga_get_chapters( $manga_id ) {
	global $manga_chapter_cache;
	manga_prime_chapters( array( $manga_id ) );
	return isset( $manga_chapter_cache[ (int) $manga_id ] ) ? $manga_chapter_cache[ (int) $manga_id ] : array();
}

/* ------------------------------------------------------------
 * Covers
 * ---------------------------------------------------------- */
function manga_cover_url( $manga_id, $size = 'manga-cover-small' ) {
	$url = has_post_thumbnail( $manga_id ) ? get_the_post_thumbnail_url( $manga_id, $size ) : '';
	return $url ? $url : get_template_directory_uri() . '/assets/img/no-cover.svg';
}

/* ------------------------------------------------------------
 * Per-manga stats (latest chapter date + chapter count)
 * Stored as post meta so listings can sort in SQL.
 * ---------------------------------------------------------- */
function manga_refresh_stats( $manga_id, $exclude_chapter = 0 ) {
	global $wpdb;

	$manga_id = (int) $manga_id;
	if ( ! $manga_id || 'manga' !== get_post_type( $manga_id ) ) {
		return;
	}

	$row = $wpdb->get_row( $wpdb->prepare(
		"SELECT COUNT(*) AS total, MAX(p.post_date) AS latest
		 FROM {$wpdb->posts} p
		 INNER JOIN {$wpdb->postmeta} pm ON pm.post_id = p.ID
		 WHERE pm.meta_key = 'connected_manga_id'
		   AND pm.meta_value = %s
		   AND p.post_type = 'chapter'
		   AND p.post_status = 'publish'
		   AND p.ID <> %d",
		(string) $manga_id,
		(int) $exclude_chapter
	) );

	$latest = ( $row && $row->latest ) ? $row->latest : get_post_field( 'post_date', $manga_id );
	update_post_meta( $manga_id, '_latest_chapter_date', $latest );
	update_post_meta( $manga_id, '_chapter_count', $row ? (int) $row->total : 0 );
	delete_transient( 'manga_popular_ids' );
}

// One-time fill for manga that existed before the theme stored these values.
function manga_maybe_backfill() {
	if ( get_option( 'manga_stats_ready' ) ) {
		return;
	}
	$ids = get_posts( array(
		'post_type'      => 'manga',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	foreach ( $ids as $id ) {
		manga_refresh_stats( $id );
	}
	update_option( 'manga_stats_ready', 1, false );
}
add_action( 'admin_init', 'manga_maybe_backfill' );

function manga_on_manga_saved( $post_id ) {
	if ( ! wp_is_post_revision( $post_id ) ) {
		manga_refresh_stats( $post_id );
	}
}
add_action( 'save_post_manga', 'manga_on_manga_saved', 20 );

// Publishing, unpublishing or trashing a chapter changes the numbers.
function manga_on_chapter_transition( $new_status, $old_status, $post ) {
	if ( 'chapter' === $post->post_type && $new_status !== $old_status ) {
		manga_refresh_stats( (int) get_post_meta( $post->ID, 'connected_manga_id', true ) );
	}
}
add_action( 'transition_post_status', 'manga_on_chapter_transition', 10, 3 );

function manga_on_chapter_delete( $post_id ) {
	if ( 'chapter' === get_post_type( $post_id ) ) {
		manga_refresh_stats( (int) get_post_meta( $post_id, 'connected_manga_id', true ), $post_id );
	}
}
add_action( 'before_delete_post', 'manga_on_chapter_delete' );

// Series with the most chapters (used by the search modal).
function manga_popular_ids( $limit = 6 ) {
	$ids = get_transient( 'manga_popular_ids' );
	if ( false === $ids ) {
		$ids = get_posts( array(
			'post_type'      => 'manga',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'meta_key'       => '_chapter_count',
			'orderby'        => array( 'meta_value_num' => 'DESC', 'title' => 'ASC' ),
			'fields'         => 'ids',
			'no_found_rows'  => true,
		) );
		set_transient( 'manga_popular_ids', $ids, 12 * HOUR_IN_SECONDS );
	}
	return array_slice( $ids, 0, $limit );
}

/* ------------------------------------------------------------
 * Output
 * ---------------------------------------------------------- */
function output_manga_grid( $manga_list ) {
	if ( empty( $manga_list ) ) {
		echo '<p class="no-results">' . esc_html__( 'No manga found.', 'manga-theme' ) . '</p>';
		return;
	}

	manga_prime_chapters( wp_list_pluck( $manga_list, 'ID' ) );

	echo '<div class="manga-grid">';
	foreach ( $manga_list as $manga ) {
		get_template_part( 'template-parts/manga-card', null, array( 'manga_id' => $manga->ID ) );
	}
	echo '</div>';
}

// Pagination for the main query (search and fallback lists).
function manga_main_pagination() {
	$links = paginate_links( array(
		'prev_text' => __( '← Prev', 'manga-theme' ),
		'next_text' => __( 'Next →', 'manga-theme' ),
		'mid_size'  => 2,
	) );
	if ( $links ) {
		echo '<nav class="pagination" aria-label="' . esc_attr__( 'Pagination', 'manga-theme' ) . '">' . $links . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}
