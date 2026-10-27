<?php
/**
 * Post types, taxonomies, admin columns.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function manga_post_type_labels( $singular, $plural ) {
	return array(
		'name'               => $plural,
		'singular_name'      => $singular,
		'menu_name'          => $plural,
		'add_new'            => sprintf( __( 'Add New %s', 'manga-theme' ), $singular ),
		'add_new_item'       => sprintf( __( 'Add New %s', 'manga-theme' ), $singular ),
		'edit_item'          => sprintf( __( 'Edit %s', 'manga-theme' ), $singular ),
		'new_item'           => sprintf( __( 'New %s', 'manga-theme' ), $singular ),
		'view_item'          => sprintf( __( 'View %s', 'manga-theme' ), $singular ),
		'search_items'       => sprintf( __( 'Search %s', 'manga-theme' ), $plural ),
		'not_found'          => sprintf( __( 'No %s found', 'manga-theme' ), strtolower( $plural ) ),
		'not_found_in_trash' => sprintf( __( 'No %s found in trash', 'manga-theme' ), strtolower( $plural ) ),
		'all_items'          => sprintf( __( 'All %s', 'manga-theme' ), $plural ),
	);
}

function manga_register_content_types() {
	register_post_type( 'manga', array(
		'labels'             => manga_post_type_labels( __( 'Manga', 'manga-theme' ), __( 'Manga', 'manga-theme' ) ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'manga', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'menu_icon'          => 'dashicons-book',
		'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'show_in_rest'       => true,
	) );

	// Classic editor for chapters so the title regenerates visibly after saving.
	register_post_type( 'chapter', array(
		'labels'             => manga_post_type_labels( __( 'Chapter', 'manga-theme' ), __( 'Chapters', 'manga-theme' ) ),
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'chapter', 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 6,
		'menu_icon'          => 'dashicons-media-text',
		'supports'           => array( 'title', 'editor' ),
		'show_in_rest'       => false,
	) );

	register_taxonomy( 'genre', array( 'manga' ), array(
		'hierarchical'      => true,
		'labels'            => array(
			'name'          => __( 'Genres', 'manga-theme' ),
			'singular_name' => __( 'Genre', 'manga-theme' ),
			'search_items'  => __( 'Search Genres', 'manga-theme' ),
			'all_items'     => __( 'All Genres', 'manga-theme' ),
			'edit_item'     => __( 'Edit Genre', 'manga-theme' ),
			'update_item'   => __( 'Update Genre', 'manga-theme' ),
			'add_new_item'  => __( 'Add New Genre', 'manga-theme' ),
			'new_item_name' => __( 'New Genre Name', 'manga-theme' ),
			'menu_name'     => __( 'Genres', 'manga-theme' ),
		),
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'genre' ),
		'show_in_rest'      => true,
	) );

	register_taxonomy( 'manga_status', array( 'manga' ), array(
		'hierarchical'      => true,
		'labels'            => array(
			'name'          => __( 'Status', 'manga-theme' ),
			'singular_name' => __( 'Status', 'manga-theme' ),
			'search_items'  => __( 'Search Status', 'manga-theme' ),
			'all_items'     => __( 'All Statuses', 'manga-theme' ),
			'edit_item'     => __( 'Edit Status', 'manga-theme' ),
			'update_item'   => __( 'Update Status', 'manga-theme' ),
			'add_new_item'  => __( 'Add New Status', 'manga-theme' ),
			'new_item_name' => __( 'New Status Name', 'manga-theme' ),
			'menu_name'     => __( 'Status', 'manga-theme' ),
		),
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => 'status' ),
		'show_in_rest'      => true,
	) );
}
add_action( 'init', 'manga_register_content_types' );

// Default statuses, created once (not on every page load).
function manga_seed_statuses() {
	if ( get_option( 'manga_statuses_seeded' ) || ! taxonomy_exists( 'manga_status' ) ) {
		return;
	}
	foreach ( array( 'Ongoing', 'Completed', 'Hiatus', 'Cancelled' ) as $status ) {
		if ( ! term_exists( $status, 'manga_status' ) ) {
			wp_insert_term( $status, 'manga_status' );
		}
	}
	update_option( 'manga_statuses_seeded', 1, false );
}
add_action( 'admin_init', 'manga_seed_statuses' );

function manga_on_theme_switch() {
	manga_register_content_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'manga_on_theme_switch' );

/* ------------------------------------------------------------
 * Chapter list columns
 * ---------------------------------------------------------- */
function manga_chapter_columns( $cols ) {
	$new = array();
	foreach ( $cols as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['chapter_manga'] = __( 'Manga', 'manga-theme' );
			$new['chapter_label'] = __( 'Chapter', 'manga-theme' );
			$new['chapter_pages'] = __( 'Pages', 'manga-theme' );
		}
	}
	return $new;
}
add_filter( 'manage_chapter_posts_columns', 'manga_chapter_columns' );

function manga_chapter_column_content( $column, $post_id ) {
	if ( 'chapter_manga' === $column ) {
		$mid = (int) get_post_meta( $post_id, 'connected_manga_id', true );
		echo $mid ? esc_html( get_the_title( $mid ) ) : '—';
	} elseif ( 'chapter_label' === $column ) {
		echo esc_html( manga_chapter_label( $post_id ) );
	} elseif ( 'chapter_pages' === $column ) {
		$raw = (string) get_post_meta( $post_id, 'image_links', true );
		echo (int) count( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) );
	}
}
add_action( 'manage_chapter_posts_custom_column', 'manga_chapter_column_content', 10, 2 );
