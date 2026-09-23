<?php
/**
 * Front page — hero, doubt category grid, cross-link block, four-column footer.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>

<section class="murtadd-hero">
	<h1><?php esc_html_e( 'Doubts, taken seriously.', 'murtadd' ); ?></h1>
	<p><?php esc_html_e( 'Honest, sourced answers to the questions that shake faith — stated fairly, answered without dismissal, and linked to the scholarship behind them.', 'murtadd' ); ?></p>
</section>

<section class="murtadd-category-grid-wrap">
	<div class="murtadd-section-head">
		<h2 class="murtadd-kicker"><?php esc_html_e( 'Where does your doubt start?', 'murtadd' ); ?></h2>
		<a class="murtadd-more" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_doubt' ) ); ?>"><?php esc_html_e( 'All doubts →', 'murtadd' ); ?></a>
	</div>
	<div class="murtadd-category-grid">
		<?php
		$murtadd_cats = murtadd_doubt_categories_ordered();
		if ( $murtadd_cats ) :
			foreach ( $murtadd_cats as $murtadd_cat ) :
				get_template_part( 'template-parts/cards/card', 'doubt-category', array( 'term' => $murtadd_cat ) );
			endforeach;
		endif;
		?>
	</div>
</section>

<?php
$murtadd_ce      = murtadd_option( 'murtadd_cross_links', 'ce_base_url', '' );
$murtadd_bismika = murtadd_option( 'murtadd_cross_links', 'bismika_base_url', '' );
if ( $murtadd_ce || $murtadd_bismika ) :
	?>
	<section class="murtadd-crosslinks">
		<div class="murtadd-section-head murtadd-section-head--center">
			<h2 class="murtadd-heading-caps"><?php esc_html_e( 'This site gives the short answer', 'murtadd' ); ?></h2>
			<span class="murtadd-rule" aria-hidden="true"></span>
			<p><?php esc_html_e( 'This site answers what is said against Islam. These two carry the arguments it sits on top of.', 'murtadd' ); ?></p>
		</div>
		<div class="murtadd-crosslink-cards">
			<?php
			get_template_part( 'template-parts/cards/card', 'cross-link', array( 'url' => $murtadd_ce, 'title' => __( 'The questions beneath this one', 'murtadd' ), 'desc' => __( 'Does God exist, why is there suffering, can ethics stand without Him. The upstream arguments, at length.', 'murtadd' ) ) );
			get_template_part( 'template-parts/cards/card', 'cross-link', array( 'url' => $murtadd_bismika, 'title' => __( 'Missionary polemics, answered', 'murtadd' ), 'desc' => __( 'Responses to the standard arguments used against Islam, claim by claim.', 'murtadd' ) ) );
			?>
		</div>
	</section>
<?php endif; ?>

<footer class="murtadd-footer-columns">
	<div class="murtadd-footer-col murtadd-footer-about">
		<?php murtadd_the_logo( 'on-light', false ); ?>
		<p><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
	</div>
	<div class="murtadd-footer-col">
		<h3><?php esc_html_e( 'Browse', 'murtadd' ); ?></h3>
		<ul>
			<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Blog', 'murtadd' ); ?></a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_doubt' ) ); ?>"><?php esc_html_e( 'Doubts', 'murtadd' ); ?></a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_rebuttal' ) ); ?>"><?php esc_html_e( 'Rebuttals', 'murtadd' ); ?></a></li>
			<li><a href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_fatwa' ) ); ?>"><?php esc_html_e( 'Fatwa', 'murtadd' ); ?></a></li>
		</ul>
	</div>
	<div class="murtadd-footer-col">
		<h3><?php esc_html_e( 'Topics', 'murtadd' ); ?></h3>
		<ul>
			<?php
			$murtadd_topics = get_terms( array( 'taxonomy' => 'murtadd_topic', 'hide_empty' => false, 'number' => 6 ) );
			if ( ! is_wp_error( $murtadd_topics ) ) :
				foreach ( $murtadd_topics as $murtadd_topic ) :
					?>
					<li><a href="<?php echo esc_url( get_term_link( $murtadd_topic ) ); ?>"><?php echo esc_html( $murtadd_topic->name ); ?></a></li>
				<?php endforeach; endif; ?>
		</ul>
	</div>
	<?php if ( murtadd_has_secondary_links() ) : ?>
		<div class="murtadd-footer-col">
			<h3><?php esc_html_e( 'Site', 'murtadd' ); ?></h3>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer_site',
					'container'      => false,
					'fallback_cb'    => 'murtadd_secondary_fallback',
					'depth'          => 1,
				)
			);
			?>
		</div>
	<?php endif; ?>
</footer>

<?php get_footer(); ?>
