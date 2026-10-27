<?php
/**
 * Library: filters (genre include/exclude, status, letter, search, sort),
 * the shared query, rendering, pagination and the AJAX endpoint.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------
 * Where the library lives
 * The published page with the slug "manga-list" is used when it exists,
 * otherwise the /manga/ post type archive.
 * ---------------------------------------------------------- */
function manga_library_page() {
	static $page = null;
	if ( null === $page ) {
		$found = get_page_by_path( 'manga-list' );
		$page  = ( $found && 'publish' === $found->post_status ) ? $found : false;
	}
	return $page;
}

function manga_library_base_url() {
	$page = manga_library_page();
	if ( $page ) {
		return get_permalink( $page );
	}
	$archive = get_post_type_archive_link( 'manga' );
	return $archive ? $archive : home_url( '/' );
}

// True on the Manga List page, the /manga/ archive, or any page using the library template.
function manga_is_library_page() {
	if ( is_post_type_archive( 'manga' ) || is_page_template( 'page-manga-archive.php' ) ) {
		return true;
	}
	$page = manga_library_page();
	return $page && is_page( $page->ID );
}

// Show the library on the Manga List page even if no page template is selected.
function manga_library_template( $template ) {
	if ( is_page() && manga_is_library_page() ) {
		$custom = locate_template( 'page-manga-archive.php' );
		if ( $custom ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'manga_library_template' );

// Send /manga/ (even when it 404s) to the Manga List page, keeping any filters.
function manga_redirect_archive_to_page() {
	global $wp;
	$page = manga_library_page();
	if ( ! $page ) {
		return;
	}
	$on_archive = is_post_type_archive( 'manga' ) || ( is_404() && 'manga' === trim( (string) $wp->request, '/' ) );
	if ( $on_archive ) {
		$url = get_permalink( $page );
		if ( ! empty( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$url = add_query_arg( urlencode_deep( wp_unslash( $_GET ) ), $url ); // phpcs:ignore WordPress.Security.NonceVerification
		}
		wp_safe_redirect( $url, 302 );
		exit;
	}
}
add_action( 'template_redirect', 'manga_redirect_archive_to_page', 1 );

/* ------------------------------------------------------------
 * Filters
 * ---------------------------------------------------------- */
function manga_sort_options() {
	return array(
		'az'       => __( 'A → Z', 'manga-theme' ),
		'za'       => __( 'Z → A', 'manga-theme' ),
		'updated'  => __( 'Recently updated', 'manga-theme' ),
		'newest'   => __( 'Newly added', 'manga-theme' ),
		'chapters' => __( 'Most chapters', 'manga-theme' ),
	);
}

function manga_csv_slugs( $value ) {
	if ( is_string( $value ) ) {
		$value = explode( ',', $value );
	}
	if ( ! is_array( $value ) ) {
		return array();
	}
	$slugs = array();
	foreach ( $value as $item ) {
		if ( is_scalar( $item ) ) {
			$slug = sanitize_title( (string) $item );
			if ( '' !== $slug ) {
				$slugs[] = $slug;
			}
		}
	}
	return array_values( array_unique( $slugs ) );
}

// Normalise filters from $_GET / $_POST (already unslashed).
function manga_parse_filters( $src ) {
	$src = is_array( $src ) ? $src : array();

	$letter = isset( $src['letter'] ) && is_string( $src['letter'] ) ? strtoupper( substr( $src['letter'], 0, 1 ) ) : '';
	if ( ! preg_match( '/^[A-Z#]$/', $letter ) ) {
		$letter = '';
	}

	$sort = isset( $src['sort'] ) && is_string( $src['sort'] ) ? sanitize_key( $src['sort'] ) : 'az';
	$opts = manga_sort_options();
	if ( ! isset( $opts[ $sort ] ) ) {
		$sort = 'az';
	}

	$g = manga_csv_slugs( isset( $src['g'] ) ? $src['g'] : array() );
	$x = array_values( array_diff( manga_csv_slugs( isset( $src['x'] ) ? $src['x'] : array() ), $g ) );

	return array(
		'q'      => isset( $src['q'] ) && is_string( $src['q'] ) ? sanitize_text_field( $src['q'] ) : '',
		'letter' => $letter,
		'g'      => $g,
		'x'      => $x,
		'st'     => manga_csv_slugs( isset( $src['st'] ) ? $src['st'] : array() ),
		'gm'     => ( isset( $src['gm'] ) && 'or' === $src['gm'] ) ? 'or' : 'and',
		'sort'   => $sort,
		'pg'     => max( 1, isset( $src['pg'] ) ? (int) $src['pg'] : 1 ),
	);
}

// Library URL for a set of filters (omits defaults).
function manga_library_url( $filters = array(), $base = '' ) {
	if ( ! $base ) {
		$base = manga_library_base_url();
	}

	$args = array();
	foreach ( array( 'g', 'x', 'st' ) as $key ) {
		if ( ! empty( $filters[ $key ] ) ) {
			$args[ $key ] = implode( ',', (array) $filters[ $key ] );
		}
	}
	if ( ! empty( $filters['q'] ) ) {
		$args['q'] = $filters['q'];
	}
	if ( ! empty( $filters['letter'] ) ) {
		$args['letter'] = $filters['letter'];
	}
	if ( isset( $filters['gm'] ) && 'or' === $filters['gm'] ) {
		$args['gm'] = 'or';
	}
	if ( ! empty( $filters['sort'] ) && 'az' !== $filters['sort'] ) {
		$args['sort'] = $filters['sort'];
	}
	if ( ! empty( $filters['pg'] ) && (int) $filters['pg'] > 1 ) {
		$args['pg'] = (int) $filters['pg'];
	}

	return $args ? add_query_arg( urlencode_deep( $args ), $base ) : $base;
}

function manga_library_query( $f, $per_page = 24 ) {
	manga_maybe_backfill(); // makes sure sort-by-meta never drops a series

	$args = array(
		'post_type'           => 'manga',
		'post_status'         => 'publish',
		'posts_per_page'      => $per_page,
		'paged'               => $f['pg'],
		'ignore_sticky_posts' => true,
	);

	switch ( $f['sort'] ) {
		case 'za':
			$args['orderby'] = 'title';
			$args['order']   = 'DESC';
			break;
		case 'newest':
			$args['orderby'] = 'date';
			$args['order']   = 'DESC';
			break;
		case 'updated':
			$args['meta_key'] = '_latest_chapter_date';
			$args['orderby']  = array( 'meta_value' => 'DESC', 'title' => 'ASC' );
			break;
		case 'chapters':
			$args['meta_key'] = '_chapter_count';
			$args['orderby']  = array( 'meta_value_num' => 'DESC', 'title' => 'ASC' );
			break;
		default:
			$args['orderby'] = 'title';
			$args['order']   = 'ASC';
	}

	$tax = array();
	if ( $f['g'] ) {
		$tax[] = array(
			'taxonomy'         => 'genre',
			'field'            => 'slug',
			'terms'            => $f['g'],
			'operator'         => ( 'and' === $f['gm'] ) ? 'AND' : 'IN',
			'include_children' => false,
		);
	}
	if ( $f['x'] ) {
		$tax[] = array(
			'taxonomy'         => 'genre',
			'field'            => 'slug',
			'terms'            => $f['x'],
			'operator'         => 'NOT IN',
			'include_children' => false,
		);
	}
	if ( $f['st'] ) {
		$tax[] = array(
			'taxonomy' => 'manga_status',
			'field'    => 'slug',
			'terms'    => $f['st'],
			'operator' => 'IN',
		);
	}
	if ( $tax ) {
		$args['tax_query'] = array_merge( array( 'relation' => 'AND' ), $tax );
	}

	if ( '' !== $f['q'] ) {
		$args['s']              = $f['q'];
		$args['search_columns'] = array( 'post_title' ); // WP 6.2+
	}

	$where = null;
	if ( '' !== $f['letter'] ) {
		$letter = $f['letter'];
		$where  = function ( $sql ) use ( $letter ) {
			global $wpdb;
			if ( '#' === $letter ) {
				return $sql . " AND {$wpdb->posts}.post_title NOT REGEXP '^[A-Za-z]'";
			}
			return $sql . $wpdb->prepare( " AND {$wpdb->posts}.post_title LIKE %s", $wpdb->esc_like( $letter ) . '%' );
		};
		add_filter( 'posts_where', $where );
	}

	$query = new WP_Query( $args );

	if ( $where ) {
		remove_filter( 'posts_where', $where );
	}
	return $query;
}

function manga_pagination_html( $f, $total, $base = '' ) {
	if ( $total < 2 ) {
		return '';
	}
	$cur   = (int) $f['pg'];
	$pages = array_unique( array_merge( array( 1, $total ), range( max( 1, $cur - 2 ), min( $total, $cur + 2 ) ) ) );
	sort( $pages );

	$link = function ( $n, $label, $class = '' ) use ( $f, $base ) {
		$f['pg'] = $n;
		return sprintf(
			'<a class="%s" href="%s" data-page="%d">%s</a>',
			esc_attr( $class ),
			esc_url( manga_library_url( $f, $base ) ),
			(int) $n,
			$label
		);
	};

	$out = '<nav class="pagination" aria-label="' . esc_attr__( 'Pagination', 'manga-theme' ) . '">';
	if ( $cur > 1 ) {
		$out .= $link( $cur - 1, esc_html__( '← Prev', 'manga-theme' ), 'prev' );
	}
	$last = 0;
	foreach ( $pages as $n ) {
		if ( $last && $n - $last > 1 ) {
			$out .= '<span class="dots">…</span>';
		}
		$out .= ( $n === $cur )
			? '<span class="current" aria-current="page">' . (int) $n . '</span>'
			: $link( $n, (int) $n );
		$last = $n;
	}
	if ( $cur < $total ) {
		$out .= $link( $cur + 1, esc_html__( 'Next →', 'manga-theme' ), 'next' );
	}
	return $out . '</nav>';
}

/*
 * Prints count + grid + pagination. Used for the first page load and by AJAX,
 * so both always produce identical markup.
 */
function manga_render_library( $f, $opts = array() ) {
	$opts = wp_parse_args( $opts, array(
		'per_page'   => 24,
		'base'       => '',
		'pagination' => true,
		'count'      => true,
	) );

	$q = manga_library_query( $f, $opts['per_page'] );
	if ( $q->max_num_pages && $f['pg'] > $q->max_num_pages ) {
		$f['pg'] = (int) $q->max_num_pages;
		$q       = manga_library_query( $f, $opts['per_page'] );
	}

	if ( ! $q->have_posts() ) {
		echo '<div class="no-results"><p>' . esc_html__( 'No manga match these filters.', 'manga-theme' ) . '</p>';
		if ( $opts['count'] ) {
			echo '<button type="button" class="btn btn-primary" data-action="reset">' . esc_html__( 'Reset filters', 'manga-theme' ) . '</button>';
		}
		echo '</div>';
		return;
	}

	if ( $opts['count'] ) {
		printf(
			'<p class="results-count">%s</p>',
			esc_html( sprintf(
				/* translators: %s: number of titles */
				_n( '%s title', '%s titles', $q->found_posts, 'manga-theme' ),
				number_format_i18n( $q->found_posts )
			) )
		);
	}

	output_manga_grid( $q->posts );

	if ( $opts['pagination'] ) {
		echo manga_pagination_html( $f, (int) $q->max_num_pages, $opts['base'] ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
}

/* ------------------------------------------------------------
 * AJAX (public, read-only, so no nonce: nonces go stale on cached pages)
 * ---------------------------------------------------------- */
function manga_ajax_library() {
	$f = manga_parse_filters( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification

	if ( ! empty( $_POST['compact'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		manga_render_library( $f, array(
			'pagination' => false,
			'count'      => false,
		) );
		wp_die();
	}

	$base = isset( $_POST['base'] ) && is_string( $_POST['base'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['base'] ) ), '' ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	manga_render_library( $f, array( 'base' => $base ) );
	wp_die();
}
add_action( 'wp_ajax_manga_library', 'manga_ajax_library' );
add_action( 'wp_ajax_nopriv_manga_library', 'manga_ajax_library' );

// "Random" button: /?manga_random=1 opens a random series.
function manga_random_redirect() {
	if ( empty( $_GET['manga_random'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}
	$ids = get_posts( array(
		'post_type'      => 'manga',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => 'rand',
		'fields'         => 'ids',
		'no_found_rows'  => true,
	) );
	nocache_headers();
	wp_safe_redirect( $ids ? get_permalink( $ids[0] ) : manga_library_base_url(), 302 );
	exit;
}
add_action( 'template_redirect', 'manga_random_redirect', 0 );
