<?php get_header(); ?>

<?php
while ( have_posts() ) :
	the_post();

	$chapter_id  = get_the_ID();
	$manga_id    = (int) get_post_meta( $chapter_id, 'connected_manga_id', true );
	$manga_title = $manga_id ? get_the_title( $manga_id ) : __( 'Unknown Manga', 'manga-theme' );
	$manga_url   = $manga_id ? get_permalink( $manga_id ) : home_url( '/' );
	$label       = manga_chapter_label( $chapter_id );

	$raw    = (string) get_post_meta( $chapter_id, 'image_links', true );
	$images = array_values( array_filter( array_map( 'trim', explode( "\n", $raw ) ) ) );
	$total  = count( $images );

	// Newest first. "Prev" = older chapter, "Next" = newer chapter.
	$chapters = $manga_id ? manga_get_chapters( $manga_id ) : array();
	$index    = false;
	foreach ( $chapters as $i => $c ) {
		if ( (int) $c['id'] === (int) $chapter_id ) {
			$index = $i;
			break;
		}
	}
	$newer = ( false !== $index && $index > 0 ) ? $chapters[ $index - 1 ] : null;
	$older = ( false !== $index && isset( $chapters[ $index + 1 ] ) ) ? $chapters[ $index + 1 ] : null;

	$arrow_l = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>';
	$arrow_r = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>';
	?>

	<div class="manga-reader-container" id="mangaReaderContainer" data-current-bg="dark" data-chapter-id="<?php echo (int) $chapter_id; ?>"
		data-manga-id="<?php echo (int) $manga_id; ?>" data-manga-title="<?php echo esc_attr( $manga_title ); ?>" data-manga-url="<?php echo esc_url( $manga_url ); ?>" data-manga-cover="<?php echo esc_url( $manga_id ? manga_cover_url( $manga_id, 'medium' ) : '' ); ?>"
		data-chapter-label="<?php echo esc_attr( $label ); ?>" data-chapter-url="<?php echo esc_url( get_permalink( $chapter_id ) ); ?>"
		data-next-url="<?php echo esc_url( $newer ? get_permalink( $newer['id'] ) : '' ); ?>">
		<script>
		try {
			var d = document.documentElement, t = d.getAttribute('data-theme'), m = d.getAttribute('data-mode');
			var b = localStorage.getItem('readerBgMode');
			if (!/^(dark|light|sepia)$/.test(b)) {
				b = t === 'metro' ? 'dark' : (t === 'mangastream' ? 'light' : (/^(dark|light|sepia)$/.test(m) ? m : 'dark'));
			}
			document.getElementById('mangaReaderContainer').setAttribute('data-current-bg', b);
		} catch (e) {}
		</script>

		<div class="reader-top-bar" id="readerTopBar">
			<div class="reader-top-bar-left">
				<a href="<?php echo esc_url( $manga_url ); ?>" class="reader-nav-btn reader-back">
					<?php echo $arrow_l; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span><?php esc_html_e( 'Back', 'manga-theme' ); ?></span>
				</a>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="reader-nav-btn reader-home" aria-label="<?php esc_attr_e( 'Home', 'manga-theme' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h5v-6h4v6h5V10"/></svg>
					<span><?php esc_html_e( 'Home', 'manga-theme' ); ?></span>
				</a>
				<div class="chapter-info-compact">
					<span class="compact-title"><?php echo esc_html( $manga_title ); ?></span>
					<span class="compact-chapter"><?php echo esc_html( $label ); ?></span>
				</div>
			</div>

			<div class="reader-top-bar-center">
				<div class="chapter-nav">
					<?php if ( $older ) : ?>
						<a href="<?php echo esc_url( get_permalink( $older['id'] ) ); ?>" class="reader-nav-btn" rel="prev"><?php echo $arrow_l; // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Prev', 'manga-theme' ); ?></span></a>
					<?php else : ?>
						<span class="reader-nav-btn is-disabled" aria-disabled="true"><?php echo $arrow_l; // phpcs:ignore WordPress.Security.EscapeOutput ?><span><?php esc_html_e( 'Prev', 'manga-theme' ); ?></span></span>
					<?php endif; ?>

					<select id="chapterSelector" class="chapter-selector" aria-label="<?php esc_attr_e( 'Select chapter', 'manga-theme' ); ?>">
						<?php if ( $chapters ) : ?>
							<?php foreach ( array_reverse( $chapters ) as $c ) : ?>
								<option value="<?php echo esc_url( get_permalink( $c['id'] ) ); ?>" <?php selected( (int) $c['id'], (int) $chapter_id ); ?>><?php echo esc_html( $c['label'] ); ?></option>
							<?php endforeach; ?>
						<?php else : ?>
							<option value=""><?php echo esc_html( $label ); ?></option>
						<?php endif; ?>
					</select>

					<?php if ( $newer ) : ?>
						<a href="<?php echo esc_url( get_permalink( $newer['id'] ) ); ?>" class="reader-nav-btn" rel="next"><span><?php esc_html_e( 'Next', 'manga-theme' ); ?></span><?php echo $arrow_r; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
					<?php else : ?>
						<span class="reader-nav-btn is-disabled" aria-disabled="true"><span><?php esc_html_e( 'Next', 'manga-theme' ); ?></span><?php echo $arrow_r; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<?php endif; ?>
				</div>
			</div>

			<div class="reader-top-bar-right">
				<button type="button" class="reader-settings-btn" id="settingsToggleBtn" aria-label="<?php esc_attr_e( 'Reader settings', 'manga-theme' ); ?>" aria-expanded="false" aria-controls="readerSettingsPanel">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
				</button>
				<button type="button" class="reader-fullscreen-btn" id="fullscreenToggleBtn" aria-label="<?php esc_attr_e( 'Toggle fullscreen', 'manga-theme' ); ?>">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
				</button>
			</div>
		</div>

		<div class="reader-progress-bar"><div class="reader-progress-fill" id="readerProgressFill"></div></div>

		<div class="reader-main-content" id="readerMainContent">
			<h1 class="screen-reader-text"><?php echo esc_html( $manga_title . ' – ' . $label ); ?></h1>
			<div class="reader-viewer" id="readerViewer">
				<?php if ( $images ) : ?>
					<?php foreach ( $images as $i => $image ) : ?>
						<div class="reader-page" data-page="<?php echo (int) $i; ?>">
							<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( sprintf( __( 'Page %d', 'manga-theme' ), $i + 1 ) ); ?>" loading="<?php echo $i < 2 ? 'eager' : 'lazy'; ?>" decoding="async">
						</div>
					<?php endforeach; ?>
				<?php else : ?>
					<div class="no-images-message">
						<p><?php esc_html_e( 'No images found for this chapter.', 'manga-theme' ); ?></p>
						<?php if ( current_user_can( 'edit_post', $chapter_id ) ) : ?>
							<a href="<?php echo esc_url( get_edit_post_link( $chapter_id ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Edit chapter', 'manga-theme' ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="reader-end">
					<p class="reader-end-title"><?php echo esc_html( sprintf( __( 'End of %s', 'manga-theme' ), $label ) ); ?></p>
					<div class="reader-end-actions">
						<?php if ( $newer ) : ?>
							<a class="btn btn-primary" rel="next" href="<?php echo esc_url( get_permalink( $newer['id'] ) ); ?>"><?php echo esc_html( sprintf( __( 'Next: %s →', 'manga-theme' ), $newer['label'] ) ); ?></a>
						<?php endif; ?>
						<a class="btn" href="<?php echo esc_url( $manga_url ); ?>"><?php esc_html_e( 'Back to series', 'manga-theme' ); ?></a>
					</div>
				</div>
			</div>
		</div>

		<div class="reader-footer" id="readerFooter">
			<?php manga_render_music_player(); ?>
			<div class="page-indicator" id="pageIndicator" hidden>
				<span id="currentPageDisplay">1</span> / <span id="totalPagesDisplay"><?php echo (int) $total; ?></span>
			</div>
		</div>

		<button type="button" class="nav-arrow nav-arrow-left" id="navArrowLeft" aria-label="<?php esc_attr_e( 'Previous page', 'manga-theme' ); ?>"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
		<button type="button" class="nav-arrow nav-arrow-right" id="navArrowRight" aria-label="<?php esc_attr_e( 'Next page', 'manga-theme' ); ?>"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></button>


		<div class="reader-settings-panel" id="readerSettingsPanel" aria-hidden="true">
			<div class="settings-panel-header">
				<h3><?php esc_html_e( 'Reader settings', 'manga-theme' ); ?></h3>
				<button type="button" class="close-settings-btn" id="closeSettingsPanelBtn" aria-label="<?php esc_attr_e( 'Close settings', 'manga-theme' ); ?>">&times;</button>
			</div>
			<div class="settings-panel-content">
				<div class="settings-section">
					<span class="settings-label"><?php esc_html_e( 'Reading mode', 'manga-theme' ); ?></span>
					<div class="settings-buttons-group">
						<button type="button" class="settings-btn" data-setting="mode" data-value="paged"><?php esc_html_e( 'Paged', 'manga-theme' ); ?></button>
						<button type="button" class="settings-btn" data-setting="mode" data-value="longstrip"><?php esc_html_e( 'Long strip', 'manga-theme' ); ?></button>
					</div>
				</div>
				<div class="settings-section">
					<span class="settings-label"><?php esc_html_e( 'Image fit', 'manga-theme' ); ?></span>
					<div class="settings-buttons-group">
						<button type="button" class="settings-btn" data-setting="fit" data-value="width"><?php esc_html_e( 'Fit width', 'manga-theme' ); ?></button>
						<button type="button" class="settings-btn" data-setting="fit" data-value="height"><?php esc_html_e( 'Fit height', 'manga-theme' ); ?></button>
						<button type="button" class="settings-btn" data-setting="fit" data-value="original"><?php esc_html_e( 'Original', 'manga-theme' ); ?></button>
					</div>
				</div>
				<div class="settings-section">
					<span class="settings-label"><?php esc_html_e( 'Zoom', 'manga-theme' ); ?></span>
					<div class="reader-zoom-controls" id="readerZoomControls">
						<button type="button" class="zoom-btn" id="zoomOutBtn" aria-label="<?php esc_attr_e( 'Zoom out', 'manga-theme' ); ?>">−</button>
						<span class="zoom-level" id="zoomLevelDisplay">100%</span>
						<button type="button" class="zoom-btn" id="zoomInBtn" aria-label="<?php esc_attr_e( 'Zoom in', 'manga-theme' ); ?>">+</button>
						<button type="button" class="zoom-btn" id="resetZoomBtn" aria-label="<?php esc_attr_e( 'Reset zoom', 'manga-theme' ); ?>">↺</button>
					</div>
				</div>
				<div class="settings-section">
					<span class="settings-label"><?php esc_html_e( 'Background', 'manga-theme' ); ?></span>
					<div class="settings-buttons-group">
						<button type="button" class="settings-btn" data-setting="bg" data-value="dark"><?php esc_html_e( 'Dark', 'manga-theme' ); ?></button>
						<button type="button" class="settings-btn" data-setting="bg" data-value="light"><?php esc_html_e( 'Light', 'manga-theme' ); ?></button>
						<button type="button" class="settings-btn" data-setting="bg" data-value="sepia"><?php esc_html_e( 'Sepia', 'manga-theme' ); ?></button>
					</div>
				</div>
				<div class="settings-section">
					<span class="settings-label"><?php esc_html_e( 'Reading direction', 'manga-theme' ); ?></span>
					<div class="settings-buttons-group">
						<button type="button" class="settings-btn" data-setting="dir" data-value="ltr"><?php esc_html_e( 'Left to right', 'manga-theme' ); ?></button>
						<button type="button" class="settings-btn" data-setting="dir" data-value="rtl"><?php esc_html_e( 'Right to left (manga)', 'manga-theme' ); ?></button>
					</div>
				</div>
				<div class="settings-section">
					<label class="settings-label" for="readerBrightness"><?php esc_html_e( 'Brightness', 'manga-theme' ); ?></label>
					<input type="range" id="readerBrightness" class="reader-range" min="40" max="100" step="5" value="100">
				</div>
				<div class="settings-section">
					<span class="settings-label"><?php esc_html_e( 'Toolbar', 'manga-theme' ); ?></span>
					<div class="settings-buttons-group">
						<button type="button" class="settings-btn" data-setting="bar" data-value="show"><?php esc_html_e( 'Always show', 'manga-theme' ); ?></button>
						<button type="button" class="settings-btn" data-setting="bar" data-value="auto"><?php esc_html_e( 'Auto-hide', 'manga-theme' ); ?></button>
					</div>
				</div>
				<div class="settings-section">
					<span class="settings-label"><?php esc_html_e( 'Navigation', 'manga-theme' ); ?></span>
					<div class="settings-buttons-group">
						<button type="button" class="settings-btn" data-setting="nav" data-value="click"><?php esc_html_e( 'Click to advance', 'manga-theme' ); ?></button>
						<button type="button" class="settings-btn" data-setting="nav" data-value="buttons"><?php esc_html_e( 'Buttons only', 'manga-theme' ); ?></button>
					</div>
				</div>
			</div>
		</div>

	</div>
<?php endwhile; ?>

<?php get_footer(); ?>
