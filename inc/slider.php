<?php
/**
 * Homepage slider: source, heading, view counter and Recommendations.
 * Options: Appearance → Customize → Homepage Slider.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------
 * Settings
 * ---------------------------------------------------------- */
function manga_slider_sources() {
	return array(
		'new'         => __( 'New titles (newest added)', 'manga-theme' ),
		'viewed'      => __( 'Most viewed (all time)', 'manga-theme' ),
		'popular'     => __( 'Popular (most views this week)', 'manga-theme' ),
		'recommended' => __( 'Recommendations (hand-picked)', 'manga-theme' ),
	);
}

function manga_slider_source() {
	$source = get_theme_mod( 'manga_slider_source', 'new' );
	return isset( manga_slider_sources()[ $source ] ) ? $source : 'new';
}

function manga_sanitize_slider_source( $value ) {
	return isset( manga_slider_sources()[ $value ] ) ? $value : 'new';
}

function manga_slider_heading() {
	$custom = trim( (string) get_theme_mod( 'manga_slider_heading', '' ) );
	if ( '' !== $custom ) {
		return $custom;
	}
	switch ( manga_slider_source() ) {
		case 'viewed':
			return __( 'Most viewed', 'manga-theme' );
		case 'popular':
			return __( 'Popular this week', 'manga-theme' );
		case 'recommended':
			return __( 'Recommendations', 'manga-theme' );
		default:
			return __( 'New titles', 'manga-theme' );
	}
}

function manga_slider_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'manga_slider', array(
		'title'       => __( 'Homepage Slider', 'manga-theme' ),
		'priority'    => 158,
		'description' => __( 'Choose what the slider at the top of the homepage shows. For Recommendations, tick "Recommend this manga" on each manga you want to feature (Manga → edit).', 'manga-theme' ),
	) );

	$wp_customize->add_setting( 'manga_slider_source', array(
		'default'           => 'new',
		'sanitize_callback' => 'manga_sanitize_slider_source',
	) );
	$wp_customize->add_control( 'manga_slider_source', array(
		'section' => 'manga_slider',
		'label'   => __( 'Show', 'manga-theme' ),
		'type'    => 'select',
		'choices' => manga_slider_sources(),
	) );

	$wp_customize->add_setting( 'manga_slider_heading', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'manga_slider_heading', array(
		'section'     => 'manga_slider',
		'label'       => __( 'Heading', 'manga-theme' ),
		'description' => __( 'Leave empty to use the default name for the chosen list.', 'manga-theme' ),
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'manga_slider_count', array(
		'default'           => 5,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'manga_slider_count', array(
		'section'     => 'manga_slider',
		'label'       => __( 'Number of slides (1-10)', 'manga-theme' ),
		'type'        => 'number',
		'input_attrs' => array( 'min' => 1, 'max' => 10 ),
	) );
}
add_action( 'customize_register', 'manga_slider_customize_register' );

/* ------------------------------------------------------------
 * Slides
 * ---------------------------------------------------------- */
function manga_slider_newest( $limit, $exclude = array() ) {
	return get_posts( array(
		'post_type'      => 'manga',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'post__not_in'   => $exclude,
		'no_found_rows'  => true,
	) );
}

function manga_slider_items() {
	$limit  = max( 1, min( 10, (int) get_theme_mod( 'manga_slider_count', 5 ) ) );
	$source = manga_slider_source();
	$base   = array(
		'post_type'      => 'manga',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'no_found_rows'  => true,
	);
	$posts = array();

	if ( 'viewed' === $source ) {
		$posts = get_posts( $base + array(
			'meta_key' => '_view_count',
			'orderby'  => array( 'meta_value_num' => 'DESC', 'title' => 'ASC' ),
		) );
	} elseif ( 'popular' === $source ) {
		$posts = get_posts( $base + array(
			'meta_query' => array(
				'relation'   => 'AND',
				'week_start' => array(
					'key'     => '_view_week_start',
					'value'   => time() - WEEK_IN_SECONDS,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
				'week_views' => array(
					'key'     => '_view_week',
					'compare' => 'EXISTS',
					'type'    => 'NUMERIC',
				),
			),
			'orderby'    => array( 'week_views' => 'DESC', 'title' => 'ASC' ),
		) );
	} elseif ( 'recommended' === $source ) {
		$posts = get_posts( array(
			'post_type'      => 'manga',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_key'       => '_manga_recommended',
			'meta_value'     => '1',
			'no_found_rows'  => true,
		) );
		usort( $posts, function ( $a, $b ) {
			$oa = (int) get_post_meta( $a->ID, '_manga_rec_order', true );
			$ob = (int) get_post_meta( $b->ID, '_manga_rec_order', true );
			return array( $oa, strtolower( $a->post_title ) ) <=> array( $ob, strtolower( $b->post_title ) );
		} );
		$posts = array_slice( $posts, 0, $limit );
	}

	if ( 'new' === $source ) {
		return manga_slider_newest( $limit );
	}

	// Nothing picked yet: show the newest titles so the slider is never empty.
	if ( 'recommended' === $source ) {
		return $posts ? $posts : manga_slider_newest( $limit );
	}

	// Views start at zero on a new install, so top up the list with the newest titles.
	if ( count( $posts ) < $limit ) {
		$posts = array_merge( $posts, manga_slider_newest( $limit - count( $posts ), wp_list_pluck( $posts, 'ID' ) ) );
	}
	return $posts;
}

function manga_slider_description( $manga ) {
	if ( 'recommended' === manga_slider_source() ) {
		$note = trim( (string) get_post_meta( $manga->ID, '_manga_rec_note', true ) );
		if ( '' !== $note ) {
			return $note;
		}
	}
	return $manga->post_excerpt ? $manga->post_excerpt : wp_trim_words( $manga->post_content, 60, '…' );
}

// Small extra line under the title, e.g. "1,234 views".
function manga_slider_extra( $manga_id ) {
	$source = manga_slider_source();
	if ( 'viewed' === $source ) {
		$n = (int) get_post_meta( $manga_id, '_view_count', true );
		return $n ? sprintf( _n( '%s view', '%s views', $n, 'manga-theme' ), number_format_i18n( $n ) ) : '';
	}
	if ( 'popular' === $source ) {
		$start = (int) get_post_meta( $manga_id, '_view_week_start', true );
		$n     = (int) get_post_meta( $manga_id, '_view_week', true );
		if ( $n && $start >= time() - WEEK_IN_SECONDS ) {
			return sprintf( _n( '%s view this week', '%s views this week', $n, 'manga-theme' ), number_format_i18n( $n ) );
		}
	}
	return '';
}

/* ------------------------------------------------------------
 * View counter (AJAX, so it also works behind page caching)
 * ---------------------------------------------------------- */
function manga_ajax_count_view() {
	$id = isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
	if ( $id && 'chapter' === get_post_type( $id ) ) {
		$id = (int) get_post_meta( $id, 'connected_manga_id', true );
	}
	if ( ! $id || 'manga' !== get_post_type( $id ) || 'publish' !== get_post_status( $id ) ) {
		wp_die();
	}

	// Ignore bots and the people who edit the site.
	$agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( (string) wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	if ( '' === $agent || preg_match( '/bot|crawl|spider|slurp|headless|preview|facebookexternalhit/', $agent ) ) {
		wp_die();
	}
	if ( is_user_logged_in() && current_user_can( 'edit_posts' ) ) {
		wp_die();
	}

	// One count per visitor per series every 6 hours.
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? (string) $_SERVER['REMOTE_ADDR'] : '';
	$key = 'manga_v_' . md5( $ip . '|' . $id );
	if ( get_transient( $key ) ) {
		wp_die();
	}
	set_transient( $key, 1, 6 * HOUR_IN_SECONDS );

	update_post_meta( $id, '_view_count', (int) get_post_meta( $id, '_view_count', true ) + 1 );

	$start = (int) get_post_meta( $id, '_view_week_start', true );
	if ( ! $start || time() - $start > WEEK_IN_SECONDS ) {
		update_post_meta( $id, '_view_week_start', time() );
		update_post_meta( $id, '_view_week', 1 );
	} else {
		update_post_meta( $id, '_view_week', (int) get_post_meta( $id, '_view_week', true ) + 1 );
	}
	wp_die();
}
add_action( 'wp_ajax_manga_count_view', 'manga_ajax_count_view' );
add_action( 'wp_ajax_nopriv_manga_count_view', 'manga_ajax_count_view' );

// "Views" column in the manga list.
function manga_views_column( $cols ) {
	$cols['manga_views'] = __( 'Views', 'manga-theme' );
	return $cols;
}
add_filter( 'manage_manga_posts_columns', 'manga_views_column' );

function manga_views_column_content( $column, $post_id ) {
	if ( 'manga_views' === $column ) {
		echo (int) get_post_meta( $post_id, '_view_count', true );
	}
}
add_action( 'manage_manga_posts_custom_column', 'manga_views_column_content', 10, 2 );

/* ------------------------------------------------------------
 * Recommendations: box on the manga edit screen
 * ---------------------------------------------------------- */
function manga_add_recommend_box() {
	add_meta_box( 'manga_recommend', __( 'Recommendation', 'manga-theme' ), 'manga_render_recommend_box', 'manga', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'manga_add_recommend_box' );

function manga_render_recommend_box( $post ) {
	wp_nonce_field( 'manga_recommend_save', 'manga_recommend_nonce' );
	$on    = '1' === get_post_meta( $post->ID, '_manga_recommended', true );
	$note  = get_post_meta( $post->ID, '_manga_rec_note', true );
	$order = (int) get_post_meta( $post->ID, '_manga_rec_order', true );
	?>
	<p><label><input type="checkbox" name="manga_recommended" value="1" <?php checked( $on ); ?>> <?php esc_html_e( 'Recommend this manga', 'manga-theme' ); ?></label></p>
	<p class="manga-mb-row">
		<label for="manga_rec_note"><?php esc_html_e( 'Why we recommend it (optional)', 'manga-theme' ); ?></label>
		<textarea name="manga_rec_note" id="manga_rec_note" rows="3"><?php echo esc_textarea( $note ); ?></textarea>
	</p>
	<p class="manga-mb-row">
		<label for="manga_rec_order"><?php esc_html_e( 'Order (lowest first)', 'manga-theme' ); ?></label>
		<input type="number" name="manga_rec_order" id="manga_rec_order" value="<?php echo (int) $order; ?>" step="1">
	</p>
	<p class="description"><?php esc_html_e( 'Shown when Appearance → Customize → Homepage Slider is set to Recommendations.', 'manga-theme' ); ?></p>
	<?php
}

function manga_save_recommend_box( $post_id ) {
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['manga_recommend_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['manga_recommend_nonce'] ) ), 'manga_recommend_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( ! empty( $_POST['manga_recommended'] ) ) {
		update_post_meta( $post_id, '_manga_recommended', '1' );
	} else {
		delete_post_meta( $post_id, '_manga_recommended' );
	}

	$note = isset( $_POST['manga_rec_note'] ) && is_string( $_POST['manga_rec_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['manga_rec_note'] ) ) : '';
	if ( '' !== $note ) {
		update_post_meta( $post_id, '_manga_rec_note', $note );
	} else {
		delete_post_meta( $post_id, '_manga_rec_note' );
	}

	update_post_meta( $post_id, '_manga_rec_order', isset( $_POST['manga_rec_order'] ) ? (int) $_POST['manga_rec_order'] : 0 );
}
add_action( 'save_post_manga', 'manga_save_recommend_box' );
