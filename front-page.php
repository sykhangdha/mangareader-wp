<?php get_header(); ?>

<div class="homepage-content">
	<?php
	$featured = manga_slider_items();
	manga_prime_chapters( wp_list_pluck( $featured, 'ID' ) );
	?>

	<?php if ( $featured ) : ?>
	<section class="featured-section ms-hide">
		<div class="featured-header">
			<h2><?php echo esc_html( manga_slider_heading() ); ?></h2>
			<div class="featured-nav">
				<button type="button" class="featured-prev" id="featuredPrev" aria-label="<?php esc_attr_e( 'Previous', 'manga-theme' ); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
				<button type="button" class="featured-next" id="featuredNext" aria-label="<?php esc_attr_e( 'Next', 'manga-theme' ); ?>"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></button>
			</div>
		</div>

		<div class="featured-container">
			<div class="featured-slider" id="featuredSlider">
				<?php foreach ( $featured as $manga ) :
					$manga_id = $manga->ID;
					$title    = get_the_title( $manga_id );
					$genres   = wp_get_post_terms( $manga_id, 'genre', array( 'fields' => 'names' ) );
					$genres   = is_wp_error( $genres ) ? array() : $genres;
					$desc     = manga_slider_description( $manga );
					$chapters = manga_get_chapters( $manga_id );
					$latest   = $chapters ? $chapters[0] : null;
					?>
					<div class="featured-item">
						<div class="featured-cover">
							<img src="<?php echo esc_url( manga_cover_url( $manga_id, 'medium' ) ); ?>" alt="<?php echo esc_attr( $title ); ?>">
							<div class="featured-overlay">
								<a href="<?php echo esc_url( get_permalink( $manga_id ) ); ?>" class="featured-btn"><?php esc_html_e( 'View Details', 'manga-theme' ); ?></a>
							</div>
						</div>
						<div class="featured-info">
							<h3 class="featured-title"><a href="<?php echo esc_url( get_permalink( $manga_id ) ); ?>"><?php echo esc_html( $title ); ?></a></h3>
							<div class="featured-genre"><?php echo esc_html( $genres ? $genres[0] : __( 'Manga', 'manga-theme' ) ); ?></div>
							<div class="featured-stats">
								<span><?php echo esc_html( sprintf( _n( '%d chapter', '%d chapters', count( $chapters ), 'manga-theme' ), count( $chapters ) ) ); ?></span>
								<?php $extra = manga_slider_extra( $manga_id ); ?>
								<?php if ( $extra ) : ?><span><?php echo esc_html( $extra ); ?></span><?php endif; ?>
							</div>
							<p class="featured-description"><?php echo esc_html( $desc ); ?></p>
							<?php if ( $genres ) : ?>
								<div class="featured-tags">
									<?php foreach ( array_slice( $genres, 0, 3 ) as $genre_name ) : ?>
										<span class="featured-tag"><?php echo esc_html( $genre_name ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<div class="featured-footer">
								<span class="featured-chapter"><?php echo esc_html( $latest ? $latest['label'] : __( 'Coming soon', 'manga-theme' ) ); ?></span>
								<a href="<?php echo esc_url( get_permalink( $latest ? $latest['id'] : $manga_id ) ); ?>" class="featured-read"><?php echo esc_html( $latest ? __( 'Read latest', 'manga-theme' ) : __( 'View', 'manga-theme' ) ); ?></a>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="featured-dots" id="featuredDots"></div>
	</section>
	<?php endif; ?>

	<?php manga_render_ms_featured(); ?>

	<div class="content-wrapper">
		<div class="main-content">
			<div class="my-rails">
				<section class="rail" id="railContinue" hidden>
					<div class="section-header"><h2><?php esc_html_e( 'Continue reading', 'manga-theme' ); ?></h2><button type="button" class="rail-clear" id="railClear"><?php esc_html_e( 'Clear', 'manga-theme' ); ?></button></div>
					<div class="rail-row" id="railContinueRow"></div>
				</section>
				<section class="rail" id="railBookmarks" hidden>
					<div class="section-header"><h2><?php esc_html_e( 'Your bookmarks', 'manga-theme' ); ?></h2></div>
					<div class="rail-row" id="railBookmarksRow"></div>
				</section>
			</div>

			<?php manga_render_news(); ?>

			<div class="ms-hide">
			<div class="section-header">
				<h2><?php esc_html_e( 'Latest updates', 'manga-theme' ); ?></h2>
				<div class="sort-options">
					<button type="button" class="sort-btn active" data-sort="updated" aria-pressed="true"><?php esc_html_e( 'Recently updated', 'manga-theme' ); ?></button>
					<button type="button" class="sort-btn" data-sort="az" aria-pressed="false"><?php esc_html_e( 'A–Z', 'manga-theme' ); ?></button>
				</div>
			</div>

			<div id="manga-container" aria-live="polite">
				<?php
				manga_render_library(
					manga_parse_filters( array( 'sort' => 'updated' ) ),
					array(
						'pagination' => false,
						'count'      => false,
					)
				);
				?>
			</div>

			<p class="view-all-link">
				<a class="btn btn-outline-primary" href="<?php echo esc_url( manga_library_url( array( 'sort' => 'updated' ) ) ); ?>"><?php esc_html_e( 'Browse the full library', 'manga-theme' ); ?></a>
			</p>
			</div>
		</div>

		<aside class="sidebar">
			<?php manga_render_support_box(); ?>
			<?php manga_render_news_compact(); ?>
			<?php manga_render_noteworthy(); ?>
			<div class="widget">
				<h3><?php esc_html_e( 'Popular genres', 'manga-theme' ); ?></h3>
				<div class="genre-list">
					<?php
					$genre_terms = get_terms( array(
						'taxonomy'   => 'genre',
						'hide_empty' => true,
						'number'     => 12,
						'orderby'    => 'count',
						'order'      => 'DESC',
					) );
					if ( ! empty( $genre_terms ) && ! is_wp_error( $genre_terms ) ) :
						foreach ( $genre_terms as $genre ) :
							?>
							<a href="<?php echo esc_url( manga_library_url( array( 'g' => array( $genre->slug ) ) ) ); ?>" class="genre-item"><?php echo esc_html( $genre->name ); ?></a>
						<?php endforeach;
					else :
						?>
						<p class="description"><?php esc_html_e( 'No genres yet.', 'manga-theme' ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<div class="widget">
				<h3><?php esc_html_e( 'Reading status', 'manga-theme' ); ?></h3>
				<div class="status-list">
					<?php
					$statuses = get_terms( array( 'taxonomy' => 'manga_status', 'hide_empty' => false ) );
					if ( ! empty( $statuses ) && ! is_wp_error( $statuses ) ) :
						?>
						<a class="status-filter" href="<?php echo esc_url( manga_library_url() ); ?>">
							<span class="status-name"><?php esc_html_e( 'All', 'manga-theme' ); ?></span>
							<span class="status-count"><?php echo (int) wp_count_posts( 'manga' )->publish; ?></span>
						</a>
						<?php foreach ( $statuses as $term ) : ?>
							<a class="status-filter status-<?php echo esc_attr( sanitize_html_class( $term->slug ) ); ?>" href="<?php echo esc_url( manga_library_url( array( 'st' => array( $term->slug ) ) ) ); ?>">
								<span class="status-name"><?php echo esc_html( $term->name ); ?></span>
								<span class="status-count"><?php echo (int) $term->count; ?></span>
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
				<?php dynamic_sidebar( 'sidebar-1' ); ?>
			<?php endif; ?>
		</aside>
	</div>
</div>

<?php get_footer(); ?>
