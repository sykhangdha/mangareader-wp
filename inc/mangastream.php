<?php
/**
 * MangaStreamUI extras: News Updates post type, customizer options and the
 * front page blocks (featured tiles, news, "New & Noteworthy").
 *
 * News shows in every theme. The featured tiles, support box and
 * "New & Noteworthy" are printed too, but CSS only shows them while the
 * MangaStreamUI theme is selected (see the .ms-only / .ms-hide classes).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ------------------------------------------------------------
 * News Updates post type
 * ---------------------------------------------------------- */
function manga_register_news_type() {
	register_post_type( 'manga_news', array(
		'labels'             => manga_post_type_labels( __( 'News Update', 'manga-theme' ), __( 'News Updates', 'manga-theme' ) ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'has_archive'        => 'news',
		'rewrite'            => array( 'slug' => 'news', 'with_front' => false ),
		'menu_position'      => 7,
		'menu_icon'          => 'dashicons-megaphone',
		'supports'           => array( 'title', 'editor', 'author', 'excerpt' ),
		'show_in_rest'       => true,
	) );
}
add_action( 'init', 'manga_register_news_type' );

// New post types need the rewrite rules refreshed once, so News pages don't 404.
function manga_maybe_flush_rewrites() {
	if ( get_option( 'manga_rewrite_version' ) !== MANGA_THEME_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'manga_rewrite_version', MANGA_THEME_VERSION, false );
	}
}
add_action( 'init', 'manga_maybe_flush_rewrites', 99 );

function manga_news_admin_notice() {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( $screen && 'manga_news' === $screen->post_type ) {
		echo '<div class="notice notice-info"><p>' . esc_html__( 'News updates appear on the front page: as a short headline list in the sidebar, and as full posts in the MangaStreamUI theme.', 'manga-theme' ) . '</p></div>';
	}
}
add_action( 'admin_notices', 'manga_news_admin_notice' );

/* ------------------------------------------------------------
 * Customizer: Appearance → Customize → MangaStreamUI
 * ---------------------------------------------------------- */
function manga_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'manga_mangastream', array(
		'title'    => 'MangaStreamUI',
		'priority' => 160,
	) );

	$fields = array(
		'manga_news_count'   => array( __( 'MangaStreamUI: full news posts on the front page', 'manga-theme' ), 'number', 5, 'absint' ),
		'manga_news_sidebar_count' => array( __( 'Other themes: news headlines in the sidebar', 'manga-theme' ), 'number', 3, 'absint' ),
		'manga_support_text' => array( __( 'Support box text', 'manga-theme' ), 'text', __( 'Help keep the site running!', 'manga-theme' ), 'sanitize_text_field' ),
		'manga_support_label' => array( __( 'Support button label', 'manga-theme' ), 'text', __( 'Support us', 'manga-theme' ), 'sanitize_text_field' ),
		'manga_support_url'  => array( __( 'Support button link (leave empty to hide the box)', 'manga-theme' ), 'url', '', 'esc_url_raw' ),
	);

	foreach ( $fields as $id => $def ) {
		$wp_customize->add_setting( $id, array(
			'default'           => $def[2],
			'sanitize_callback' => $def[3],
		) );
		$wp_customize->add_control( $id, array(
			'section' => 'manga_mangastream',
			'label'   => $def[0],
			'type'    => $def[1],
		) );
	}
}
add_action( 'customize_register', 'manga_customize_register' );

/* ------------------------------------------------------------
 * Helpers
 * ---------------------------------------------------------- */

// "1082" or "Vol. 2 Ch. 5": the bold part of a title in the lists.
function manga_short_number( $info ) {
	return $info['volume']
		? sprintf( 'Vol. %s Ch. %s', manga_fmt_num( $info['volume'] ), manga_fmt_num( $info['chapter'] ) )
		: manga_fmt_num( $info['chapter'] );
}

/* ------------------------------------------------------------
 * Front page blocks
 * ---------------------------------------------------------- */

// Three tiles for the most recently updated manga.
function manga_render_ms_featured() {
	manga_maybe_backfill();

	$list = get_posts( array(
		'post_type'      => 'manga',
		'post_status'    => 'publish',
		'posts_per_page' => 3,
		'meta_key'       => '_latest_chapter_date',
		'orderby'        => 'meta_value',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	) );
	if ( ! $list ) {
		return;
	}
	manga_prime_chapters( wp_list_pluck( $list, 'ID' ) );

	echo '<div class="ms-featured ms-only">';
	foreach ( $list as $manga ) {
		$chapters = manga_get_chapters( $manga->ID );
		$latest   = $chapters ? $chapters[0] : null;
		$url      = get_permalink( $latest ? $latest['id'] : $manga->ID );
		?>
		<a class="ms-tile" href="<?php echo esc_url( $url ); ?>">
			<span class="ms-tile-cover">
				<img src="<?php echo esc_url( manga_cover_url( $manga->ID, 'medium' ) ); ?>" alt="" loading="lazy">
			</span>
			<span class="ms-tile-info">
				<span class="ms-tile-title"><?php echo esc_html( get_the_title( $manga->ID ) ); ?></span>
				<?php if ( $latest ) : ?>
					<span class="ms-tile-chapter"><?php echo esc_html( $latest['label'] ); ?></span>
					<?php if ( $latest['subtitle'] ) : ?>
						<span class="ms-tile-sub"><?php echo esc_html( $latest['subtitle'] ); ?></span>
					<?php endif; ?>
					<span class="ms-tile-date"><?php echo esc_html( get_the_date( 'M jS', $latest['id'] ) ); ?></span>
				<?php endif; ?>
			</span>
		</a>
		<?php
	}
	echo '</div>';
}

// Latest news posts. Shown on the front page in every theme.
function manga_render_news() {
	$count = max( 1, min( 20, (int) get_theme_mod( 'manga_news_count', 5 ) ) );
	$news  = new WP_Query( array(
		'post_type'           => 'manga_news',
		'post_status'         => 'publish',
		'posts_per_page'      => $count,
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	) );

	if ( ! $news->have_posts() ) {
		// Visitors see nothing; editors get a shortcut to add the first post.
		if ( current_user_can( 'edit_posts' ) ) {
			echo '<section class="news news-full"><div class="news-item news-empty"><p>' . esc_html__( 'No news yet.', 'manga-theme' ) . ' <a href="' . esc_url( admin_url( 'post-new.php?post_type=manga_news' ) ) . '">' . esc_html__( 'Add your first news update', 'manga-theme' ) . '</a></p></div></section>';
		}
		return;
	}

	echo '<section class="news news-full" aria-labelledby="newsHeading">';
	echo '<div class="section-header"><h2 id="newsHeading">' . esc_html__( 'Latest news', 'manga-theme' ) . '</h2></div>';

	while ( $news->have_posts() ) {
		$news->the_post();
		?>
		<article class="news-item">
			<h3 class="news-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			<p class="news-meta"><?php echo esc_html( sprintf( __( 'Posted %1$s by %2$s', 'manga-theme' ), get_the_date( 'M jS, Y' ), get_the_author() ) ); ?></p>
			<div class="news-content"><?php the_content( __( 'Continue reading →', 'manga-theme' ) ); ?></div>
		</article>
		<?php
	}
	wp_reset_postdata();

	$archive = get_post_type_archive_link( 'manga_news' );
	if ( $archive ) {
		echo '<p class="news-more"><a href="' . esc_url( $archive ) . '">' . esc_html__( 'Older news →', 'manga-theme' ) . '</a></p>';
	}
	echo '</section>';
}

// Compact headline list for the sidebar (every theme except MangaStreamUI, which shows full posts).
function manga_render_news_compact() {
	$count = max( 1, min( 10, (int) get_theme_mod( 'manga_news_sidebar_count', 3 ) ) );
	$news  = get_posts( array(
		'post_type'      => 'manga_news',
		'post_status'    => 'publish',
		'posts_per_page' => $count,
		'no_found_rows'  => true,
	) );

	if ( ! $news ) {
		if ( current_user_can( 'edit_posts' ) ) {
			echo '<div class="widget news-compact"><h3>' . esc_html__( 'Latest news', 'manga-theme' ) . '</h3><p class="description"><a href="' . esc_url( admin_url( 'post-new.php?post_type=manga_news' ) ) . '">' . esc_html__( 'Add your first news update', 'manga-theme' ) . '</a></p></div>';
		}
		return;
	}
	?>
	<div class="widget news-compact">
		<h3><?php esc_html_e( 'Latest news', 'manga-theme' ); ?></h3>
		<ul class="news-list">
			<?php foreach ( $news as $post_item ) : ?>
				<li>
					<a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>">
						<span class="news-list-title"><?php echo esc_html( get_the_title( $post_item ) ); ?></span>
						<span class="news-list-date"><?php echo esc_html( get_the_date( 'M jS', $post_item ) ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php $archive = get_post_type_archive_link( 'manga_news' ); ?>
		<?php if ( $archive ) : ?>
			<a class="news-list-all" href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'All news →', 'manga-theme' ); ?></a>
		<?php endif; ?>
	</div>
	<?php
}

// Optional "Love <site>? Support us" box (only shown if a link is set in the Customizer).
function manga_render_support_box() {
	$url = get_theme_mod( 'manga_support_url', '' );
	if ( ! $url ) {
		return;
	}
	$text  = get_theme_mod( 'manga_support_text', __( 'Help keep the site running!', 'manga-theme' ) );
	$label = get_theme_mod( 'manga_support_label', __( 'Support us', 'manga-theme' ) );
	?>
	<div class="ms-support ms-only">
		<div>
			<strong><?php echo esc_html( sprintf( __( 'Love %s?', 'manga-theme' ), get_bloginfo( 'name' ) ) ); ?></strong>
			<span><?php echo esc_html( $text ); ?></span>
		</div>
		<a class="ms-support-btn" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a>
	</div>
	<?php
}

// "New & Noteworthy": the newest chapters across the whole site.
function manga_render_noteworthy() {
	$chapters = get_posts( array(
		'post_type'      => 'chapter',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'no_found_rows'  => true,
	) );
	if ( ! $chapters ) {
		return;
	}

	// One query loads every manga title we are about to print.
	$manga_ids = array();
	foreach ( $chapters as $chapter ) {
		$mid = (int) get_post_meta( $chapter->ID, 'connected_manga_id', true );
		if ( $mid ) {
			$manga_ids[] = $mid;
		}
	}
	if ( $manga_ids ) {
		get_posts( array(
			'post_type'      => 'manga',
			'post__in'       => array_unique( $manga_ids ),
			'posts_per_page' => -1,
			'no_found_rows'  => true,
		) );
	}

	echo '<section class="ms-noteworthy ms-only"><h2>' . esc_html__( 'New & Noteworthy', 'manga-theme' ) . '</h2><div class="ms-rows">';
	foreach ( $chapters as $chapter ) {
		$mid = (int) get_post_meta( $chapter->ID, 'connected_manga_id', true );
		if ( ! $mid ) {
			continue;
		}
		$info   = manga_chapter_info( $chapter->ID );
		$is_new = ( time() - (int) get_post_time( 'U', true, $chapter ) ) < 3 * DAY_IN_SECONDS;
		?>
		<a class="ms-row<?php echo $is_new ? ' is-new' : ''; ?>" href="<?php echo esc_url( get_permalink( $chapter->ID ) ); ?>">
			<span class="ms-row-main">
				<span class="ms-row-title">
					<?php if ( $is_new ) : ?>
						<svg class="ms-flame" width="12" height="14" viewBox="0 0 24 28" aria-hidden="true"><path fill="currentColor" d="M13 0c1 6-6 8-6 15 0 2 .8 3.700 2 4.800C8.500 17 9.500 14 12 12c.5 3-1.500 4.500-1.500 6.500 0 1.200.7 2.200 1.800 2.500 2.800-.7 4.700-3 4.700-6.200C17 10 14 6 13 0zM4 17c-1 5 2 10 8 11-5-2-6-6-4-11z"/></svg>
					<?php endif; ?>
					<?php echo esc_html( get_the_title( $mid ) ); ?> <strong><?php echo esc_html( $info['label'] ); ?></strong>
				</span>
				<?php if ( $info['subtitle'] ) : ?>
					<span class="ms-row-sub"><?php echo esc_html( $info['subtitle'] ); ?></span>
				<?php endif; ?>
			</span>
			<span class="ms-row-date"><?php echo esc_html( get_the_date( 'M jS', $chapter ) ); ?></span>
		</a>
		<?php
	}
	echo '</div></section>';
}
