<?php
/**
 * One manga card. Works in the main loop, or with
 * get_template_part( 'template-parts/manga-card', null, array( 'manga_id' => 123 ) ).
 */
$manga_id  = isset( $args['manga_id'] ) ? (int) $args['manga_id'] : get_the_ID();
$title     = get_the_title( $manga_id );
$permalink = get_permalink( $manga_id );
$recent    = array_slice( manga_get_chapters( $manga_id ), 0, 3 ); // newest first
?>
<div class="manga-item">
	<div class="manga-item-cover">
		<a href="<?php echo esc_url( $permalink ); ?>">
			<img src="<?php echo esc_url( manga_cover_url( $manga_id ) ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
			<div class="manga-item-overlay">
				<span class="view-details"><?php esc_html_e( 'View Details', 'manga-theme' ); ?></span>
			</div>
		</a>
		<?php if ( $recent ) : ?>
			<div class="latest-chapter-badge"><?php echo esc_html( $recent[0]['label'] ); ?></div>
			<?php if ( time() - (int) get_post_time( 'U', true, $recent[0]['post'] ) < 3 * DAY_IN_SECONDS ) : ?>
				<span class="new-flag"><?php esc_html_e( 'NEW', 'manga-theme' ); ?></span>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<div class="manga-item-info">
		<h3 class="manga-item-title"><a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a></h3>
		<?php if ( $recent ) : ?>
			<div class="manga-item-chapters">
				<?php foreach ( $recent as $c ) : ?>
					<a href="<?php echo esc_url( get_permalink( $c['id'] ) ); ?>" class="chapter-link">
						<span class="chapter-num"><?php echo esc_html( $c['label'] ); ?></span>
						<span class="chapter-date"><?php echo esc_html( get_the_date( 'M j, Y', $c['id'] ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="no-chapters"><?php esc_html_e( 'No chapters yet', 'manga-theme' ); ?></div>
		<?php endif; ?>
	</div>
</div>
