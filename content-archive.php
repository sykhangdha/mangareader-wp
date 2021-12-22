<?php
/**
 * Displays content for the archive and search pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<div class="uk-grid uk-grid-small uk-margin-bottom">
		<div class="uk-width-small-1-6 uk-width-medium-3-10 chapter-thumb">
			<?php echo the_post_thumbnail( 'thumbnail', array( 'alt' => get_the_title()) ); ?>
		</div>
		
		<div class="uk-width-small-5-6 uk-width-medium-7-10 chapter-content">
			<div>
				<a href="<?php the_permalink(); ?>">
					<h3><?php the_title(); ?></h3>
				</a>
			</div>
      
      /**
 * Use wp_trim_words to shorten description of manga
 *
 * change amount of words shown by replacing $num_words = 15 with any number
 */
			
			<?php echo wp_trim_words( get_post_meta(get_the_ID(),'description',true), $num_words = 15, $more = '...' ); ?>

			
			<div>
				<small>
					<i class="uk-icon-comment"></i>
					<?php comments_popup_link( sprintf( __( '%s Comments', 'mangastarter' ), get_comments_number() ) ); ?>
				</small>
			</div>
			
			<div>
				<small><i class="uk-icon-clock-o"></i> <?php the_time( 'F d, Y' ); ?></small>
			</div>
		</div>
	</div>
</article><!-- end article -->
