<?php
/**
 * Single Doubt — statement, short response, go deeper, support notice.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$murtadd_rebuttal_id = murtadd_related_link( '_murtadd_related_rebuttal' );
	$murtadd_deep_link   = get_post_meta( get_the_ID(), '_murtadd_external_deep_link', true );
	$murtadd_response    = get_post_meta( get_the_ID(), '_murtadd_short_response', true );
	?>
	<article <?php post_class( 'murtadd-doubt' ); ?>
	<div class="murtadd-single-layout">
	<div class="murtadd-single-main">>
		<header class="murtadd-entry-header">
			<div class="murtadd-chips">
				<?php murtadd_term_chip( 'murtadd_doubt_category', 'murtadd-chip murtadd-chip--category' ); ?>
				<?php murtadd_term_chip( 'murtadd_topic' ); ?>
			</div>
			<h1 class="murtadd-doubt-statement">“<?php murtadd_the_doubt_statement(); ?>”</h1>
			<div class="murtadd-meta-row">
				<span><?php echo esc_html( murtadd_read_time( $murtadd_response ) ); ?></span>
			</div>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="murtadd-entry-image">
				<?php the_post_thumbnail( 'large' ); ?>
				<?php if ( wp_get_attachment_caption( get_post_thumbnail_id() ) ) : ?>
					<figcaption><?php echo esc_html( wp_get_attachment_caption( get_post_thumbnail_id() ) ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>

		<div class="murtadd-short-response murtadd-dropcap">
			<?php murtadd_the_short_response(); ?>
		</div>

		<div class="murtadd-ornament" aria-hidden="true">◆</div>

		<?php if ( $murtadd_rebuttal_id || $murtadd_deep_link ) : ?>
			<section class="murtadd-go-deeper">
				<h2 class="murtadd-kicker"><?php esc_html_e( 'Go deeper', 'murtadd' ); ?></h2>
				<?php if ( $murtadd_rebuttal_id ) : ?>
					<a class="murtadd-go-deeper-primary" href="<?php echo esc_url( get_permalink( $murtadd_rebuttal_id ) ); ?>">
						<span class="murtadd-kicker-small"><?php esc_html_e( 'The full rebuttal', 'murtadd' ); ?></span>
						<span><?php echo esc_html( get_the_title( $murtadd_rebuttal_id ) ); ?> →</span>
					</a>
				<?php endif; ?>
				<?php murtadd_external_link( $murtadd_deep_link, __( 'Read the full case ↗', 'murtadd' ), 'murtadd-go-deeper-secondary' ); ?>
			</section>
		<?php endif; ?>

		<?php if ( murtadd_doubt_needs_notice() ) : ?>
			<?php murtadd_struggling_notice(); ?>
		<?php endif; ?>

		<?php murtadd_share_row(); ?>

		<footer class="murtadd-entry-footer">
			<?php murtadd_the_reading_time(); ?>
			<?php murtadd_the_reviewed_date(); ?>
		</footer>

		<?php murtadd_related_doubts(); ?>
	</div><!-- .murtadd-single-main -->

	<?php get_template_part( 'template-parts/counterpart' ); ?>

	<?php get_template_part( 'template-parts/rail/rail', 'single' ); ?>
	</div><!-- .murtadd-single-layout -->
	</article>
	<?php
endwhile;

get_footer();
