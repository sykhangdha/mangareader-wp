<?php
/**
 * The manga library: search, sort, genre include/exclude, status and letter filters.
 */
$filters = manga_parse_filters( wp_unslash( $_GET ) ); // phpcs:ignore WordPress.Security.NonceVerification
$base    = manga_library_base_url();
$genres  = get_terms( array( 'taxonomy' => 'genre', 'hide_empty' => true, 'orderby' => 'name' ) );
$genres  = is_wp_error( $genres ) ? array() : $genres;
$status  = get_terms( array( 'taxonomy' => 'manga_status', 'hide_empty' => false, 'orderby' => 'name' ) );
$status  = is_wp_error( $status ) ? array() : $status;

$active = count( $filters['g'] ) + count( $filters['x'] ) + count( $filters['st'] ) + ( $filters['letter'] ? 1 : 0 );
$open   = $active > 0;

$letters = array_merge( array( '' => __( 'All', 'manga-theme' ), '#' => '#' ), array_combine( range( 'A', 'Z' ), range( 'A', 'Z' ) ) );
?>
<div class="manga-archive">
	<div class="archive-header">
		<h1><?php esc_html_e( 'Manga Library', 'manga-theme' ); ?></h1>
		<p><?php esc_html_e( 'Browse the collection and narrow it down by genre, status or title.', 'manga-theme' ); ?></p>
	</div>

	<form class="library-filters" id="libraryFilters" method="get" action="<?php echo esc_url( $base ); ?>" role="search">
		<div class="library-toolbar">
			<label class="screen-reader-text" for="libraryQuery"><?php esc_html_e( 'Search the library', 'manga-theme' ); ?></label>
			<input type="search" id="libraryQuery" name="q" class="library-search" placeholder="<?php esc_attr_e( 'Search by title…', 'manga-theme' ); ?>" value="<?php echo esc_attr( $filters['q'] ); ?>" autocomplete="off">

			<label class="screen-reader-text" for="librarySort"><?php esc_html_e( 'Sort by', 'manga-theme' ); ?></label>
			<select id="librarySort" name="sort" class="library-select">
				<?php foreach ( manga_sort_options() as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['sort'], $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>

			<button type="button" class="btn btn-secondary filter-toggle" id="filterToggle" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="filterPanel">
				<?php esc_html_e( 'Filters', 'manga-theme' ); ?>
				<span class="filter-count" id="filterCount" <?php echo $active ? '' : 'hidden'; ?>><?php echo (int) $active; ?></span>
			</button>
			<a class="btn btn-secondary random-btn" href="<?php echo esc_url( add_query_arg( 'manga_random', '1', home_url( '/' ) ) ); ?>" rel="nofollow"><?php esc_html_e( 'Random', 'manga-theme' ); ?></a>
		</div>

		<div class="filter-panel" id="filterPanel" <?php echo $open ? '' : 'hidden'; ?>>
			<?php if ( $genres ) : ?>
				<fieldset class="filter-group">
					<legend><?php esc_html_e( 'Genres', 'manga-theme' ); ?></legend>
					<div class="filter-group-head">
						<span class="filter-hint"><?php esc_html_e( 'Click once to include, twice to exclude.', 'manga-theme' ); ?></span>
						<label class="screen-reader-text" for="libraryMode"><?php esc_html_e( 'Genre matching', 'manga-theme' ); ?></label>
						<select id="libraryMode" name="gm" class="library-select">
							<option value="and" <?php selected( $filters['gm'], 'and' ); ?>><?php esc_html_e( 'Match all included', 'manga-theme' ); ?></option>
							<option value="or" <?php selected( $filters['gm'], 'or' ); ?>><?php esc_html_e( 'Match any included', 'manga-theme' ); ?></option>
						</select>
					</div>
					<div class="chip-list">
						<?php foreach ( $genres as $genre ) :
							$state = in_array( $genre->slug, $filters['g'], true ) ? 'include' : ( in_array( $genre->slug, $filters['x'], true ) ? 'exclude' : '' );
							?>
							<button type="button" class="chip" data-kind="genre" data-slug="<?php echo esc_attr( $genre->slug ); ?>" data-state="<?php echo esc_attr( $state ); ?>" aria-pressed="<?php echo 'include' === $state ? 'true' : ( 'exclude' === $state ? 'mixed' : 'false' ); ?>"><?php echo esc_html( $genre->name ); ?></button>
						<?php endforeach; ?>
					</div>
				</fieldset>
			<?php endif; ?>

			<?php if ( $status ) : ?>
				<fieldset class="filter-group">
					<legend><?php esc_html_e( 'Status', 'manga-theme' ); ?></legend>
					<div class="chip-list">
						<?php foreach ( $status as $term ) :
							$on = in_array( $term->slug, $filters['st'], true );
							?>
							<button type="button" class="chip" data-kind="status" data-slug="<?php echo esc_attr( $term->slug ); ?>" data-state="<?php echo $on ? 'include' : ''; ?>" aria-pressed="<?php echo $on ? 'true' : 'false'; ?>"><?php echo esc_html( $term->name ); ?></button>
						<?php endforeach; ?>
					</div>
				</fieldset>
			<?php endif; ?>

			<fieldset class="filter-group">
				<legend><?php esc_html_e( 'Starts with', 'manga-theme' ); ?></legend>
				<div class="chip-list chip-list-letters">
					<?php foreach ( $letters as $key => $label ) :
						$on = ( (string) $key === $filters['letter'] );
						?>
						<button type="button" class="chip chip-small" data-kind="letter" data-slug="<?php echo esc_attr( $key ); ?>" data-state="<?php echo $on ? 'include' : ''; ?>" aria-pressed="<?php echo $on ? 'true' : 'false'; ?>"><?php echo esc_html( $label ); ?></button>
					<?php endforeach; ?>
				</div>
			</fieldset>

			<div class="filter-actions">
				<button type="button" class="btn btn-secondary" data-action="reset"><?php esc_html_e( 'Reset filters', 'manga-theme' ); ?></button>
			</div>
		</div>
	</form>

	<div id="manga-container" data-base="<?php echo esc_url( $base ); ?>" aria-live="polite">
		<?php manga_render_library( $filters, array( 'base' => $base ) ); ?>
	</div>
</div>
