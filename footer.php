		</div>
	</main>

	<footer class="site-footer">
		<div class="container">
			<div class="footer-content">
				<?php if ( get_theme_mod( 'manga_footer_about_show', true ) ) : ?>
					<?php
					$about_title = trim( (string) get_theme_mod( 'manga_footer_about_title', '' ) );
					$about_text  = trim( (string) get_theme_mod( 'manga_footer_about_text', '' ) );
					?>
					<div class="footer-section">
						<h3><?php echo esc_html( '' !== $about_title ? $about_title : __( 'About', 'manga-theme' ) ); ?></h3>
						<?php if ( '' !== $about_text ) : ?>
							<div class="footer-about"><?php echo wp_kses_post( wpautop( $about_text ) ); ?></div>
						<?php else : ?>
							<p><?php echo esc_html( get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : get_bloginfo( 'name' ) ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div class="footer-section">
					<h3><?php esc_html_e( 'Quick Links', 'manga-theme' ); ?></h3>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer',
						'menu_class'     => 'footer-menu',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => 'manga_footer_quick_fallback',
					) );
					?>
				</div>

				<div class="footer-section">
					<h3><?php esc_html_e( 'Browse', 'manga-theme' ); ?></h3>
					<?php
					wp_nav_menu( array(
						'theme_location' => 'footer_browse',
						'menu_class'     => 'footer-menu',
						'container'      => false,
						'depth'          => 1,
						'fallback_cb'    => 'manga_footer_browse_fallback',
					) );
					?>
				</div>
			</div>

			<div class="footer-bottom">
				<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'manga-theme' ); ?></p>
			</div>
		</div>
	</footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
