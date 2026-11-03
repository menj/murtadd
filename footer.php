<?php
/**
 * Footer — slim bar on inner pages. front-page.php prints its own
 * four-column footer section before including this.
 *
 * The Journal link and the secondary links (About / FAQ / Contact / Privacy)
 * live here. The secondary links stay out of the sidebar, which is the triage
 * navigation: utility links there competed with it and duplicated the front
 * page's footer column. The Journal is in both places.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;
?>
	<footer class="murtadd-footer-bar">
		<span class="murtadd-footer-copy"><?php echo esc_html( '© ' . wp_parse_url( home_url(), PHP_URL_HOST ) ); ?> · <?php esc_html_e( 'share with attribution', 'murtadd' ); ?></span>

		<?php if ( ! is_front_page() ) : ?>
			<nav class="murtadd-footer-links" aria-label="<?php esc_attr_e( 'Secondary', 'murtadd' ); ?>">
				<ul class="murtadd-secondary-list murtadd-footer-journal">
					<li><a href="<?php echo esc_url( murtadd_journal_url() ); ?>"><?php esc_html_e( 'Journal', 'murtadd' ); ?></a></li>
				</ul>
				<?php if ( murtadd_has_secondary_links() ) : ?>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer_site',
						'container'      => false,
						'menu_class'     => 'murtadd-secondary-list',
						'fallback_cb'    => 'murtadd_secondary_fallback',
						'depth'          => 1,
					)
				);
				?>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

		<?php $murtadd_profiles = murtadd_social_profiles(); ?>
		<?php if ( $murtadd_profiles ) : ?>
			<nav class="murtadd-footer-social" aria-label="<?php esc_attr_e( 'Author profiles', 'murtadd' ); ?>">
				<?php foreach ( $murtadd_profiles as $murtadd_slug => $murtadd_url ) : ?>
					<?php $murtadd_meta = murtadd_social_registry()[ $murtadd_slug ]; ?>
					<a class="murtadd-social-link" href="<?php echo esc_url( $murtadd_url ); ?>" rel="me noopener" target="_blank" title="<?php echo esc_attr( $murtadd_meta['label'] ); ?>">
						<?php echo murtadd_social_icon( $murtadd_slug, 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- registry SVG. ?>
						<span class="screen-reader-text"><?php echo esc_html( $murtadd_meta['label'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<span class="murtadd-footer-heritage"><?php murtadd_the_heritage_note(); ?></span>
	</footer>
	</main><!-- .murtadd-main -->
</div><!-- .murtadd-shell -->
<?php wp_footer(); ?>
</body>
</html>
