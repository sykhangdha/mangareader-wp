<?php get_header(); ?>

<div class="search-results">
	<div class="section-header">
		<h1><?php echo esc_html( sprintf( __( 'Search results for: %s', 'manga-theme' ), get_search_query( false ) ) ); ?></h1>
	</div>

	<?php if ( have_posts() ) : ?>
		<?php output_manga_grid( $GLOBALS['wp_query']->posts ); ?>
		<?php manga_main_pagination(); ?>
	<?php else : ?>
		<div class="no-results">
			<p><?php echo esc_html( sprintf( __( 'No results found for "%s".', 'manga-theme' ), get_search_query( false ) ) ); ?></p>
			<a class="btn btn-primary" href="<?php echo esc_url( manga_library_url() ); ?>"><?php esc_html_e( 'Browse the full library', 'manga-theme' ); ?></a>
		</div>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
