<?php
/**
 * Template Name: Start Here
 * Guided entry — three routes by where the visitor is.
 * Create a page with slug "start-here" and assign this template.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'murtadd-start-here' ); ?>>
		<header class="murtadd-entry-header">
			<h1><?php esc_html_e( "You're here because something is bothering you.", 'murtadd' ); ?></h1>
			<p class="murtadd-start-lede"><?php esc_html_e( 'Good. This site was built for exactly that. A doubt is not a verdict, and it is not apostasy — it is a question that deserves a serious answer. Pick the door that matches where you are.', 'murtadd' ); ?></p>
		</header>

		<div class="murtadd-ornament" aria-hidden="true">◆</div>

		<div class="murtadd-start-routes">
			<a class="murtadd-start-route" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_doubt' ) ); ?>">
				<span class="murtadd-route-num">01</span>
				<span class="murtadd-route-body">
					<span class="murtadd-route-title"><?php esc_html_e( '“I have a doubt I can’t shake.”', 'murtadd' ); ?></span>
					<span class="murtadd-route-desc"><?php esc_html_e( 'Start with the short answers — organized by where the doubt comes from: reason, scripture, feeling, or belonging.', 'murtadd' ); ?></span>
				</span>
				<span class="murtadd-route-arrow" aria-hidden="true">→</span>
			</a>
			<?php $murtadd_irtidad = get_page_by_path( 'irtidad' ); ?>
			<?php if ( $murtadd_irtidad ) : ?>
				<a class="murtadd-start-route" href="<?php echo esc_url( get_permalink( $murtadd_irtidad ) ); ?>">
					<span class="murtadd-route-num">02</span>
					<span class="murtadd-route-body">
						<span class="murtadd-route-title"><?php esc_html_e( '“I’m afraid I’ve already crossed the line.”', 'murtadd' ); ?></span>
						<span class="murtadd-route-desc"><?php esc_html_e( 'Read what irtidad actually is — and what does not take you out of Islam. The fear itself is evidence you haven’t left.', 'murtadd' ); ?></span>
					</span>
					<span class="murtadd-route-arrow" aria-hidden="true">→</span>
				</a>
			<?php endif; ?>
			<a class="murtadd-start-route" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_rebuttal' ) ); ?>">
				<span class="murtadd-route-num">03</span>
				<span class="murtadd-route-body">
					<span class="murtadd-route-title"><?php esc_html_e( '“Someone sent me arguments against Islam.”', 'murtadd' ); ?></span>
					<span class="murtadd-route-desc"><?php esc_html_e( 'The claims, stated as fairly as they circulate — then answered with sources you can check yourself.', 'murtadd' ); ?></span>
				</span>
				<span class="murtadd-route-arrow" aria-hidden="true">→</span>
			</a>
		</div>

		<?php murtadd_struggling_notice(); ?>
	</article>
	<?php
endwhile;

get_footer();
