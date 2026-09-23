<?php
/**
 * Single Fatwa — position summary on-site; primary source as secondary outbound button.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$murtadd_era      = get_post_meta( get_the_ID(), '_murtadd_era', true );
	$murtadd_summary  = get_post_meta( get_the_ID(), '_murtadd_position_summary', true );
	$murtadd_citation = get_post_meta( get_the_ID(), '_murtadd_citation_link', true );
	?>
	<article <?php post_class( 'murtadd-fatwa' ); ?>>
		<header class="murtadd-entry-header">
			<a class="murtadd-back" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_fatwa' ) ); ?>">← <?php esc_html_e( 'Back to Fatwa index', 'murtadd' ); ?></a>
			<div class="murtadd-chips">
				<?php murtadd_term_chip( 'murtadd_school', 'murtadd-chip murtadd-chip--school' ); ?>
				<?php if ( $murtadd_era ) : ?>
					<span class="murtadd-chip"><?php echo esc_html( 'classical' === $murtadd_era ? __( 'Classical', 'murtadd' ) : __( 'Modern', 'murtadd' ) ); ?></span>
				<?php endif; ?>
				<?php murtadd_term_chip( 'murtadd_topic', 'murtadd-chip murtadd-chip--outline' ); ?>
			</div>
			<h1><?php the_title(); ?></h1>
		</header>

		<h2 class="murtadd-kicker murtadd-kicker--accent"><?php esc_html_e( 'Position summary — in our own words', 'murtadd' ); ?></h2>
		<div class="murtadd-position-summary">
			<?php echo wp_kses_post( wpautop( $murtadd_summary ) ); ?>
			<p class="murtadd-disclaimer"><?php esc_html_e( 'This position is summarized in our own words, not quoted verbatim, and represents its reasoning on its own terms — not a verdict this site issues or endorses on any individual.', 'murtadd' ); ?></p>
		</div>

		<?php if ( $murtadd_citation ) : ?>
			<p class="murtadd-primary-source">
				<?php murtadd_external_link( $murtadd_citation, __( 'Read the primary source ↗', 'murtadd' ), 'murtadd-btn murtadd-btn--outline' ); ?>
				<span class="murtadd-note"><?php esc_html_e( 'opens in a new tab', 'murtadd' ); ?></span>
			</p>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
