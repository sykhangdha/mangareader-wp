<?php
/**
 * Fallback template: pages, posts and any list not covered by a specific template.
 */
get_header();
?>

<div class="page-content">
<?php if ( is_singular() ) : ?>
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'entry' ); ?>>
			<h1 class="entry-title"><?php the_title(); ?></h1>
			<?php if ( 'manga_news' === get_post_type() ) : ?>
				<p class="news-meta"><?php echo esc_html( sprintf( __( 'Posted %1$s by %2$s', 'manga-theme' ), get_the_date( 'M jS, Y' ), get_the_author() ) ); ?></p>
			<?php endif; ?>
			<div class="entry-content"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
<?php else : ?>
	<div class="section-header">
		<h1><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
	</div>

	<?php if ( have_posts() ) : ?>
		<?php if ( 'manga' === get_post_type() ) : ?>
			<?php output_manga_grid( $GLOBALS['wp_query']->posts ); ?>
		<?php else : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<article <?php post_class( 'entry entry-summary' ); ?>>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
		<?php endif; ?>
		<?php manga_main_pagination(); ?>
	<?php else : ?>
		<p class="no-results"><?php esc_html_e( 'Nothing found.', 'manga-theme' ); ?></p>
	<?php endif; ?>
<?php endif; ?>
</div>

<?php get_footer(); ?>
