<?php
/**
 * Sidebar — primary navigation. Green panel: logo, Start here CTA,
 * nav with hover dropdowns (Topics / Doubts / Fatwa), search, secondary links.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

$murtadd_start = get_page_by_path( 'start-here' );
?>
<aside class="murtadd-sidebar">
	<div class="murtadd-brand">
		<?php murtadd_the_logo( 'on-dark' ); ?>
		<span class="murtadd-tagline"><?php esc_html_e( 'before you decide', 'murtadd' ); ?></span>
	</div>

	<?php if ( $murtadd_start ) : ?>
		<a class="murtadd-start-cta" href="<?php echo esc_url( get_permalink( $murtadd_start ) ); ?>"><?php esc_html_e( 'Start here', 'murtadd' ); ?></a>
	<?php endif; ?>

	<button class="murtadd-nav-toggle" aria-expanded="false" aria-controls="murtadd-nav"><?php esc_html_e( 'Menu', 'murtadd' ); ?></button>

	<nav id="murtadd-nav" class="murtadd-nav" aria-label="<?php esc_attr_e( 'Primary', 'murtadd' ); ?>">
		<ul class="murtadd-nav-list">
			<li><a<?php murtadd_nav_state( 'home' ); ?> href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'murtadd' ); ?></a></li>
			<li><a<?php murtadd_nav_state( 'blog' ); ?> href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'murtadd' ); ?></a></li>

			<li class="has-sub">
				<a<?php murtadd_nav_state( 'topics' ); ?> href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_doubt' ) ); ?>"><?php esc_html_e( 'Topics', 'murtadd' ); ?> <span class="murtadd-caret" aria-hidden="true">▾</span></a>
				<ul class="murtadd-sub">
					<?php
					$murtadd_topics = get_terms( array( 'taxonomy' => 'murtadd_topic', 'hide_empty' => false ) );
					if ( ! is_wp_error( $murtadd_topics ) ) :
						foreach ( $murtadd_topics as $murtadd_topic ) :
							?>
							<li><a href="<?php echo esc_url( get_term_link( $murtadd_topic ) ); ?>"><?php echo esc_html( $murtadd_topic->name ); ?></a></li>
						<?php endforeach; endif; ?>
				</ul>
			</li>

			<li class="has-sub">
				<a<?php murtadd_nav_state( 'fatwa' ); ?> href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_fatwa' ) ); ?>"><?php esc_html_e( 'Fatwa', 'murtadd' ); ?> <span class="murtadd-caret" aria-hidden="true">▾</span></a>
				<ul class="murtadd-sub">
					<?php
					$murtadd_schools = get_terms( array( 'taxonomy' => 'murtadd_school', 'hide_empty' => false ) );
					if ( ! is_wp_error( $murtadd_schools ) ) :
						foreach ( $murtadd_schools as $murtadd_school ) :
							?>
							<li><a href="<?php echo esc_url( get_term_link( $murtadd_school ) ); ?>"><?php echo esc_html( $murtadd_school->name ); ?></a></li>
						<?php endforeach; endif; ?>
				</ul>
			</li>

			<li class="has-sub">
				<a<?php murtadd_nav_state( 'doubts' ); ?> href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_doubt' ) ); ?>"><?php esc_html_e( 'Doubts', 'murtadd' ); ?> <span class="murtadd-caret" aria-hidden="true">▾</span></a>
				<ul class="murtadd-sub">
					<?php
					$murtadd_cats = get_terms( array( 'taxonomy' => 'murtadd_doubt_category', 'hide_empty' => false ) );
					if ( ! is_wp_error( $murtadd_cats ) ) :
						foreach ( $murtadd_cats as $murtadd_cat ) :
							?>
							<li><a href="<?php echo esc_url( get_term_link( $murtadd_cat ) ); ?>"><?php echo esc_html( $murtadd_cat->name ); ?></a></li>
						<?php endforeach; endif; ?>
				</ul>
			</li>

			<li><a<?php murtadd_nav_state( 'rebuttals' ); ?> href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_rebuttal' ) ); ?>"><?php esc_html_e( 'Rebuttals', 'murtadd' ); ?></a></li>

			<li class="murtadd-nav-divider" role="separator"></li>

			<li><a<?php murtadd_nav_state( 'letters' ); ?> href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_letter' ) ); ?>"><?php esc_html_e( 'Letters', 'murtadd' ); ?></a></li>
		</ul>
	</nav>

	<div class="murtadd-sidebar-search">
		<?php get_search_form(); ?>
	</div>

</aside>
