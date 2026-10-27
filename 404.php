<?php get_header(); ?>

<div class="error-404">
	<h1 class="error-404-code">404</h1>
	<h2><?php esc_html_e( 'Page not found', 'manga-theme' ); ?></h2>
	<p><?php esc_html_e( 'The page you are looking for does not exist or has moved.', 'manga-theme' ); ?></p>
	<div class="error-404-actions">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-large"><?php esc_html_e( 'Go home', 'manga-theme' ); ?></a>
		<button type="button" class="btn btn-secondary btn-large" data-open-search><?php esc_html_e( 'Search manga', 'manga-theme' ); ?></button>
	</div>
</div>

<?php get_footer(); ?>
