<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
	<script>
	/* Apply the saved theme before first paint (no flash). */
	(function () {
		var d = document.documentElement, t = null, m = null, a = null;
		try {
			t = localStorage.getItem('mangaTheme');
			m = localStorage.getItem('mangaMode');
			a = localStorage.getItem('mangaAccent');
			if (!t) { var o = localStorage.getItem('theme'); if (o === 'dark' || o === 'light') { t = o; } }
		} catch (e) {}
		// Older versions saved Light, Dark and Sepia as separate themes: they are now FluentUI modes.
		if (t === 'light' || t === 'dark' || t === 'sepia') { m = t; t = 'fluent'; }
		if (['fluent', 'metro', 'mangastream'].indexOf(t) < 0) { t = 'fluent'; }
		if (['light', 'dark', 'sepia'].indexOf(m) < 0) {
			m = (window.matchMedia && matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
		}
		d.setAttribute('data-theme', t);
		d.setAttribute('data-mode', m);
		if (a && /^#[0-9a-f]{6}$/i.test(a)) {
			var n = parseInt(a.slice(1), 16);
			d.style.setProperty('--metro-accent', a);
			d.style.setProperty('--metro-on-accent', ((((n >> 16) & 255) * 299 + ((n >> 8) & 255) * 587 + (n & 255) * 114) / 1000) > 150 ? '#000' : '#fff');
		}
	})();
	</script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'manga-theme' ); ?></a>

<div id="page" class="site">
	<header class="site-header" id="siteHeader">
		<div class="container">
			<div class="header-wrapper">
				<div class="site-branding">
					<?php $title_tag = is_front_page() ? 'h1' : 'p'; ?>
					<<?php echo esc_attr( $title_tag ); ?> class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></<?php echo esc_attr( $title_tag ); ?>>
					<?php if ( get_bloginfo( 'description' ) ) : ?>
						<p class="site-description"><?php bloginfo( 'description' ); ?></p>
					<?php endif; ?>
				</div>

				<nav class="main-nav" id="mainNav" aria-label="<?php esc_attr_e( 'Primary', 'manga-theme' ); ?>">
					<?php
					wp_nav_menu( array(
						'theme_location' => 'primary',
						'menu_class'     => 'primary-menu',
						'container'      => false,
						'fallback_cb'    => 'manga_default_menu',
						'depth'          => 2,
					) );
					?>
				</nav>

				<div class="header-actions">
					<div class="theme-switcher">
						<button type="button" class="icon-btn" id="themeToggle" aria-haspopup="true" aria-expanded="false" aria-controls="themeMenu" aria-label="<?php esc_attr_e( 'Change theme', 'manga-theme' ); ?>">
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 3v18" /><path d="M12 3a9 9 0 0 1 0 18z" fill="currentColor"/></svg>
						</button>
						<div class="theme-menu" id="themeMenu" role="group" aria-label="<?php esc_attr_e( 'Theme', 'manga-theme' ); ?>" hidden>
							<p class="theme-menu-title"><?php esc_html_e( 'Theme', 'manga-theme' ); ?></p>
							<?php foreach ( manga_themes() as $slug => $label ) : ?>
								<button type="button" class="theme-option" data-theme-value="<?php echo esc_attr( $slug ); ?>" aria-pressed="false">
									<span class="theme-swatch theme-swatch-<?php echo esc_attr( $slug ); ?>" aria-hidden="true"></span>
									<?php echo esc_html( $label ); ?>
								</button>
							<?php endforeach; ?>
							<div class="mode-picker" id="modePicker" hidden>
								<p class="theme-menu-title"><?php esc_html_e( 'Mode', 'manga-theme' ); ?></p>
								<?php foreach ( manga_fluent_modes() as $slug => $label ) : ?>
									<button type="button" class="theme-option" data-mode-value="<?php echo esc_attr( $slug ); ?>" aria-pressed="false">
										<span class="theme-swatch theme-swatch-<?php echo esc_attr( $slug ); ?>" aria-hidden="true"></span>
										<?php echo esc_html( $label ); ?>
									</button>
								<?php endforeach; ?>
							</div>
							<div class="accent-picker" id="accentPicker" hidden>
								<p class="theme-menu-title"><?php esc_html_e( 'Accent colour', 'manga-theme' ); ?></p>
								<div class="accent-grid">
									<?php foreach ( manga_metro_accents() as $name => $hex ) : ?>
										<button type="button" class="accent-swatch" data-accent="<?php echo esc_attr( $hex ); ?>" style="background:<?php echo esc_attr( $hex ); ?>" aria-label="<?php echo esc_attr( $name ); ?>" aria-pressed="false"></button>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					</div>

					<button type="button" class="icon-btn" id="searchToggle" data-open-search aria-label="<?php esc_attr_e( 'Search', 'manga-theme' ); ?>">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
					</button>

					<button type="button" class="icon-btn mobile-menu-toggle" id="mobileMenuToggle" aria-label="<?php esc_attr_e( 'Menu', 'manga-theme' ); ?>" aria-expanded="false" aria-controls="mainNav">
						<span></span><span></span><span></span>
					</button>
				</div>
			</div>
		</div>
	</header>

	<div class="search-modal" id="searchModal" role="dialog" aria-modal="true" aria-labelledby="searchModalTitle" aria-hidden="true">
		<div class="search-modal-overlay"></div>
		<div class="search-modal-content">
			<button type="button" class="search-modal-close" id="searchModalClose" aria-label="<?php esc_attr_e( 'Close search', 'manga-theme' ); ?>">&times;</button>
			<h3 id="searchModalTitle"><?php esc_html_e( 'Search manga', 'manga-theme' ); ?></h3>
			<form role="search" method="get" class="search-form-modal" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<input type="search" class="search-input-modal" placeholder="<?php esc_attr_e( 'Search by title…', 'manga-theme' ); ?>" value="<?php echo esc_attr( get_search_query( false ) ); ?>" name="s">
				<input type="hidden" name="post_type" value="manga">
				<button type="submit" class="search-button-modal"><?php esc_html_e( 'Search', 'manga-theme' ); ?></button>
			</form>
			<?php $popular = manga_popular_ids( 6 ); ?>
			<?php if ( $popular ) : ?>
				<div class="search-suggestions">
					<p><?php esc_html_e( 'Top series', 'manga-theme' ); ?></p>
					<div class="popular-manga-list">
						<?php foreach ( $popular as $popular_id ) : ?>
							<a href="<?php echo esc_url( get_permalink( $popular_id ) ); ?>"><?php echo esc_html( get_the_title( $popular_id ) ); ?></a>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<main id="main" class="site-main">
		<div class="container">
