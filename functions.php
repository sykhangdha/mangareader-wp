<?php
/**
 * Manga Reader Theme – bootstrap.
 *
 * @package manga-theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MANGA_THEME_VERSION', '7.2' );

$manga_inc = get_template_directory() . '/inc/';
require_once $manga_inc . 'helpers.php';
require_once $manga_inc . 'post-types.php';
require_once $manga_inc . 'meta-boxes.php';
require_once $manga_inc . 'library.php';
require_once $manga_inc . 'mangastream.php';
require_once $manga_inc . 'music.php';
require_once $manga_inc . 'slider.php';
if ( is_admin() ) {
	require_once $manga_inc . 'importer.php';
}
unset( $manga_inc );

/* ------------------------------------------------------------
 * Setup
 * ---------------------------------------------------------- */
function manga_theme_setup() {
	load_theme_textdomain( 'manga-theme', get_template_directory() . '/languages' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'manga-theme' ),
		'footer'  => __( 'Footer Menu: Quick Links', 'manga-theme' ),
		'footer_browse' => __( 'Footer Menu: Browse', 'manga-theme' ),
	) );

	add_image_size( 'manga-cover', 300, 420, true );
	add_image_size( 'manga-cover-small', 180, 252, true );
}
add_action( 'after_setup_theme', 'manga_theme_setup' );

function manga_theme_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'manga-theme' ),
		'id'            => 'sidebar-1',
		'description'   => __( 'Shown under the genre and status widgets on the homepage.', 'manga-theme' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'manga_theme_widgets_init' );

/* ------------------------------------------------------------
 * Themes available in the switcher
 * ---------------------------------------------------------- */
function manga_themes() {
	return array(
		'fluent'      => 'FluentUI',
		'metro'       => 'MetroUI',
		'mangastream' => 'MangaStreamUI',
	);
}

// FluentUI comes in three colour modes.
function manga_fluent_modes() {
	return array(
		'light' => __( 'Light', 'manga-theme' ),
		'dark'  => __( 'Dark', 'manga-theme' ),
		'sepia' => __( 'Sepia', 'manga-theme' ),
	);
}

// Windows Phone accent colours used by the MetroUI theme.
function manga_metro_accents() {
	return array(
		'Lime'    => '#a4c400',
		'Green'   => '#60a917',
		'Teal'    => '#00aba9',
		'Cyan'    => '#1ba1e2',
		'Cobalt'  => '#0050ef',
		'Indigo'  => '#6a00ff',
		'Violet'  => '#aa00ff',
		'Magenta' => '#d80073',
		'Crimson' => '#a20025',
		'Red'     => '#e51400',
		'Orange'  => '#fa6800',
		'Amber'   => '#f0a30a',
	);
}

// Menu shown until the admin assigns one to the "Primary" location.
function manga_default_menu() {
	echo '<ul class="primary-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'manga-theme' ) . '</a></li>';
	$archive = manga_library_base_url();
	if ( $archive ) {
		echo '<li><a href="' . esc_url( $archive ) . '">' . esc_html__( 'Library', 'manga-theme' ) . '</a></li>';
	}
	echo '</ul>';
}

// Footer menus shown until the admin assigns a menu to each footer location.
function manga_footer_quick_fallback() {
	echo '<ul class="footer-menu">';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'manga-theme' ) . '</a></li>';
	echo '<li><a href="' . esc_url( manga_library_base_url() ) . '">' . esc_html__( 'Manga List', 'manga-theme' ) . '</a></li>';
	echo '</ul>';
}

function manga_footer_browse_fallback() {
	echo '<ul class="footer-menu">';
	echo '<li><a href="' . esc_url( manga_library_base_url() ) . '">' . esc_html__( 'All Manga', 'manga-theme' ) . '</a></li>';
	echo '<li><a href="' . esc_url( manga_library_url( array( 'sort' => 'updated' ) ) ) . '">' . esc_html__( 'Recently updated', 'manga-theme' ) . '</a></li>';
	echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Latest Chapters', 'manga-theme' ) . '</a></li>';
	echo '<li><a href="' . esc_url( add_query_arg( 'manga_random', '1', home_url( '/' ) ) ) . '" rel="nofollow">' . esc_html__( 'Random manga', 'manga-theme' ) . '</a></li>';
	echo '</ul>';
}

// Version string for CSS and JavaScript links. It changes whenever ANY theme file changes,
// so browsers and cache plugins never keep serving an old script after an update.
function manga_asset_version() {
	static $ver = null;
	if ( null === $ver ) {
		$dir   = get_template_directory();
		$files = array_merge(
			array( $dir . '/style.css' ),
			(array) glob( $dir . '/assets/js/*.js' ),
			(array) glob( $dir . '/assets/css/*.css' )
		);
		$time = 0;
		foreach ( $files as $file ) {
			$time = max( $time, (int) filemtime( $file ) );
		}
		$ver = MANGA_THEME_VERSION . '.' . $time;
	}
	return $ver;
}

/* ------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------- */
function manga_theme_scripts() {
	$uri = get_template_directory_uri();
	$ver = manga_asset_version();

	wp_enqueue_style( 'manga-theme-style', get_stylesheet_uri(), array(), $ver );
	wp_enqueue_script( 'manga-main', $uri . '/assets/js/main.js', array(), $ver, true );
	wp_localize_script( 'manga-main', 'mangaTheme', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'i18n'    => array(
			'error' => __( 'Something went wrong. Please try again.', 'manga-theme' ),
		),
	) );

	if ( is_front_page() ) {
		wp_enqueue_script( 'manga-home', $uri . '/assets/js/home.js', array( 'manga-main' ), $ver, true );
	}

	if ( manga_is_library_page() ) {
		wp_enqueue_script( 'manga-archive', $uri . '/assets/js/archive-manga.js', array( 'manga-main' ), $ver, true );
	}

	if ( is_singular( 'manga' ) ) {
		wp_enqueue_script( 'manga-single', $uri . '/assets/js/single-manga.js', array( 'manga-main' ), $ver, true );
	}

	if ( is_singular( array( 'manga', 'chapter' ) ) ) {
		wp_enqueue_script( 'manga-views', $uri . '/assets/js/views.js', array(), $ver, true );
		wp_localize_script( 'manga-views', 'mangaView', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'id'      => get_queried_object_id(),
		) );
	}

	if ( is_singular( 'chapter' ) ) {
		wp_enqueue_style( 'manga-reader', $uri . '/assets/css/reader.css', array( 'manga-theme-style' ), $ver );
		wp_enqueue_script( 'manga-reader', $uri . '/assets/js/reader.js', array( 'manga-main' ), $ver, true );
		if ( manga_music_enabled() && manga_music_tracks() ) {
			wp_enqueue_script( 'manga-music', $uri . '/assets/js/music-player.js', array(), $ver, true );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'manga_theme_scripts' );

/* ------------------------------------------------------------
 * Query tweaks
 * ---------------------------------------------------------- */
function manga_pre_get_posts( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_search() ) {
		$query->set( 'post_type', 'manga' );
		$query->set( 'posts_per_page', 24 );
		$query->set( 'search_columns', array( 'post_title' ) ); // WP 6.2+
	}

	// The library renders its own filtered query, so keep the unused main query tiny.
	if ( $query->is_post_type_archive( 'manga' ) ) {
		$query->set( 'posts_per_page', 1 );
		$query->set( 'no_found_rows', true );
	}
}
add_action( 'pre_get_posts', 'manga_pre_get_posts' );

/*
 * Genre and status term pages become filters on the library page.
 * Disable with: add_filter( 'manga_redirect_taxonomies', '__return_false' );
 */
function manga_redirect_taxonomies() {
	if ( ! apply_filters( 'manga_redirect_taxonomies', true ) ) {
		return;
	}
	if ( is_tax( 'genre' ) || is_tax( 'manga_status' ) ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			$key = ( 'genre' === $term->taxonomy ) ? 'g' : 'st';
			wp_safe_redirect( manga_library_url( array( $key => array( $term->slug ) ) ), 302 );
			exit;
		}
	}
}
add_action( 'template_redirect', 'manga_redirect_taxonomies' );

/* ------------------------------------------------------------
 * Footer "About" section: Appearance → Customize → Footer
 * ---------------------------------------------------------- */
function manga_footer_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'manga_footer', array(
		'title'    => __( 'Footer', 'manga-theme' ),
		'priority' => 162,
	) );

	$wp_customize->add_setting( 'manga_footer_about_show', array(
		'default'           => true,
		'sanitize_callback' => 'rest_sanitize_boolean',
	) );
	$wp_customize->add_control( 'manga_footer_about_show', array(
		'section' => 'manga_footer',
		'label'   => __( 'Show the About section', 'manga-theme' ),
		'type'    => 'checkbox',
	) );

	$wp_customize->add_setting( 'manga_footer_about_title', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'manga_footer_about_title', array(
		'section'     => 'manga_footer',
		'label'       => __( 'About heading', 'manga-theme' ),
		'description' => __( 'Leave empty to use "About".', 'manga-theme' ),
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'manga_footer_about_text', array(
		'default'           => '',
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'manga_footer_about_text', array(
		'section'     => 'manga_footer',
		'label'       => __( 'About text', 'manga-theme' ),
		'description' => __( 'Leave empty to show your site tagline. Links and basic formatting (bold, italic) are allowed; a blank line starts a new paragraph.', 'manga-theme' ),
		'type'        => 'textarea',
		'input_attrs' => array( 'rows' => 6 ),
	) );
}
add_action( 'customize_register', 'manga_footer_customize_register' );
