<?php get_header(); ?>

<?php
while ( have_posts() ) :
	the_post();

	$manga_id = get_the_ID();
	$author   = get_post_meta( $manga_id, '_manga_author', true );
	$artist   = get_post_meta( $manga_id, '_manga_artist', true );
	$year     = get_post_meta( $manga_id, '_manga_year', true );

	$genres   = get_the_terms( $manga_id, 'genre' );
	$genres   = ( $genres && ! is_wp_error( $genres ) ) ? $genres : array();
	$statuses = get_the_terms( $manga_id, 'manga_status' );
	$status   = ( $statuses && ! is_wp_error( $statuses ) ) ? $statuses[0] : null;

	$chapters = manga_get_chapters( $manga_id ); // newest first
	$total    = count( $chapters );
	$latest   = $chapters ? $chapters[0] : null;
	$first    = $chapters ? $chapters[ $total - 1 ] : null;
	?>
	<div class="single-manga" data-manga-id="<?php echo (int) $manga_id; ?>" data-title="<?php echo esc_attr( get_the_title() ); ?>" data-url="<?php echo esc_url( get_permalink() ); ?>" data-cover="<?php echo esc_url( manga_cover_url( $manga_id, 'medium' ) ); ?>">
		<div class="manga-header">
			<div class="manga-cover-wrapper">
				<img src="<?php echo esc_url( manga_cover_url( $manga_id, 'large' ) ); ?>" class="manga-cover-large" alt="<?php echo esc_attr( get_the_title() ); ?>">
			</div>

			<div class="manga-details">
				<h1><?php the_title(); ?></h1>

				<div class="manga-stats">
					<div class="stat-box">
						<span class="stat-value"><?php echo (int) $total; ?></span>
						<span class="stat-label"><?php esc_html_e( 'Chapters', 'manga-theme' ); ?></span>
					</div>
					<?php if ( $status ) : ?>
						<div class="stat-box">
							<span class="stat-value status-<?php echo esc_attr( sanitize_html_class( $status->slug ) ); ?>"><?php echo esc_html( $status->name ); ?></span>
							<span class="stat-label"><?php esc_html_e( 'Status', 'manga-theme' ); ?></span>
						</div>
					<?php endif; ?>
					<?php if ( $year ) : ?>
						<div class="stat-box">
							<span class="stat-value"><?php echo esc_html( $year ); ?></span>
							<span class="stat-label"><?php esc_html_e( 'Year', 'manga-theme' ); ?></span>
						</div>
					<?php endif; ?>
				</div>

				<?php if ( $author || $artist ) : ?>
					<div class="manga-meta">
						<?php if ( $author ) : ?>
							<div class="meta-item"><span class="meta-label"><?php esc_html_e( 'Author:', 'manga-theme' ); ?></span> <span><?php echo esc_html( $author ); ?></span></div>
						<?php endif; ?>
						<?php if ( $artist ) : ?>
							<div class="meta-item"><span class="meta-label"><?php esc_html_e( 'Artist:', 'manga-theme' ); ?></span> <span><?php echo esc_html( $artist ); ?></span></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $genres ) : ?>
					<div class="genres">
						<?php foreach ( $genres as $genre ) : ?>
							<a href="<?php echo esc_url( manga_library_url( array( 'g' => array( $genre->slug ) ) ) ); ?>" class="genre-tag"><?php echo esc_html( $genre->name ); ?></a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>

				<div class="manga-description"><?php the_content(); ?></div>

				<div class="manga-actions">
					<a href="#" class="btn btn-primary btn-large" id="continueBtn" data-label="<?php esc_attr_e( 'Continue', 'manga-theme' ); ?>" hidden></a>
					<?php if ( $first ) : ?>
						<a href="<?php echo esc_url( get_permalink( $first['id'] ) ); ?>" class="btn btn-primary btn-large" id="readFirstBtn"><?php esc_html_e( 'Read first', 'manga-theme' ); ?></a>
					<?php endif; ?>
					<?php if ( $latest && $latest['id'] !== $first['id'] ) : ?>
						<a href="<?php echo esc_url( get_permalink( $latest['id'] ) ); ?>" class="btn btn-secondary btn-large"><?php echo esc_html( sprintf( __( 'Latest (%s)', 'manga-theme' ), $latest['label'] ) ); ?></a>
					<?php endif; ?>
					<button type="button" class="btn btn-secondary btn-large bookmark-btn" id="bookmarkBtn" aria-pressed="false" data-on="<?php esc_attr_e( 'Bookmarked', 'manga-theme' ); ?>" data-off="<?php esc_attr_e( 'Bookmark', 'manga-theme' ); ?>">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h12a1 1 0 0 1 1 1v17l-7-4-7 4V4a1 1 0 0 1 1-1z"/></svg>
						<span><?php esc_html_e( 'Bookmark', 'manga-theme' ); ?></span>
					</button>
				</div>
			</div>
		</div>

		<div class="chapters-section">
			<div class="chapters-header">
				<h2><?php esc_html_e( 'All chapters', 'manga-theme' ); ?></h2>
				<div class="chapters-controls">
					<button type="button" class="chapter-sort-btn active" data-sort="desc" aria-pressed="true"><?php esc_html_e( 'Newest first', 'manga-theme' ); ?></button>
					<button type="button" class="chapter-sort-btn" data-sort="asc" aria-pressed="false"><?php esc_html_e( 'Oldest first', 'manga-theme' ); ?></button>
				</div>
			</div>

			<?php if ( $chapters ) : ?>
				<div class="chapters-grid">
					<?php foreach ( $chapters as $i => $c ) : ?>
						<a href="<?php echo esc_url( get_permalink( $c['id'] ) ); ?>" class="chapter-card-link" data-chapter-id="<?php echo (int) $c['id']; ?>">
							<span class="chapter-title"><?php echo esc_html( $c['label'] ); ?><?php if ( $c['subtitle'] ) : ?><span class="chapter-subtitle"><?php echo esc_html( $c['subtitle'] ); ?></span><?php endif; ?></span>
							<span class="chapter-card-meta">
								<span class="chapter-date"><?php echo esc_html( get_the_date( 'M j, Y', $c['id'] ) ); ?></span>
								<?php if ( 0 === $i ) : ?>
									<span class="new-badge"><?php esc_html_e( 'Latest', 'manga-theme' ); ?></span>
								<?php endif; ?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="no-chapters-message"><p><?php esc_html_e( 'No chapters available for this manga yet.', 'manga-theme' ); ?></p></div>
			<?php endif; ?>
		</div>

		<?php
		$genre_ids = wp_list_pluck( $genres, 'term_id' );
		$related   = $genre_ids ? get_posts( array(
			'post_type'      => 'manga',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'post__not_in'   => array( $manga_id ),
			'tax_query'      => array(
				array(
					'taxonomy' => 'genre',
					'field'    => 'term_id',
					'terms'    => $genre_ids,
				),
			),
			'meta_key'       => '_latest_chapter_date',
			'orderby'        => 'meta_value',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) ) : array();
		?>
		<?php if ( $related ) : ?>
			<section class="related-manga">
				<div class="section-header"><h2><?php esc_html_e( 'You might also like', 'manga-theme' ); ?></h2></div>
				<?php output_manga_grid( $related ); ?>
			</section>
		<?php endif; ?>
	</div>
<?php endwhile; ?>

<?php get_footer(); ?>
