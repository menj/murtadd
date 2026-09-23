<?php
/**
 * Single Rebuttal — claim box, rebuttal, sources panel, cross-link row.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$murtadd_claim    = get_post_meta( get_the_ID(), '_murtadd_claim', true );
	$murtadd_type     = get_post_meta( get_the_ID(), '_murtadd_claim_source_type', true );
	$murtadd_sources  = get_post_meta( get_the_ID(), '_murtadd_sources', true );
	$murtadd_fatwa_id = murtadd_related_link( '_murtadd_related_fatwa' );
	$murtadd_doubt_id = murtadd_related_link( '_murtadd_related_doubt' );
	$murtadd_types    = array(
		'online-commentary' => __( 'Online commentary', 'murtadd' ),
		'academic'          => __( 'Academic', 'murtadd' ),
		'forum'             => __( 'Forum', 'murtadd' ),
		'pamphlet'          => __( 'Pamphlet', 'murtadd' ),
		'other'             => __( 'Other', 'murtadd' ),
	);
	?>
	<article <?php post_class( 'murtadd-rebuttal' ); ?>
	<div class="murtadd-single-layout">
	<div class="murtadd-single-main">>
		<header class="murtadd-entry-header">
			<div class="murtadd-chips">
				<?php murtadd_term_chip( 'murtadd_topic' ); ?>
				<?php if ( $murtadd_type && isset( $murtadd_types[ $murtadd_type ] ) ) : ?>
					<span class="murtadd-chip murtadd-chip--outline"><?php echo esc_html( $murtadd_types[ $murtadd_type ] ); ?></span>
				<?php endif; ?>
			</div>
			<h1><?php the_title(); ?></h1>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="murtadd-entry-image">
				<?php the_post_thumbnail( 'large' ); ?>
				<?php if ( wp_get_attachment_caption( get_post_thumbnail_id() ) ) : ?>
					<figcaption><?php echo esc_html( wp_get_attachment_caption( get_post_thumbnail_id() ) ); ?></figcaption>
				<?php endif; ?>
			</figure>
		<?php endif; ?>

		<?php if ( $murtadd_claim ) : ?>
			<section class="murtadd-claim-box">
				<h2 class="murtadd-claim-label"><?php esc_html_e( 'The claim — as circulated', 'murtadd' ); ?></h2>
				<blockquote><?php echo esc_html( $murtadd_claim ); ?></blockquote>
			</section>
		<?php endif; ?>

		<h2 class="murtadd-kicker murtadd-kicker--accent"><?php esc_html_e( 'The rebuttal', 'murtadd' ); ?></h2>
		<div class="murtadd-rebuttal-body murtadd-dropcap">
			<?php the_content(); ?>
		</div>

		<div class="murtadd-ornament" aria-hidden="true">◆</div>

		<?php if ( is_array( $murtadd_sources ) && $murtadd_sources ) : ?>
			<section class="murtadd-sources-panel">
				<h2 class="murtadd-kicker"><?php esc_html_e( 'Sources', 'murtadd' ); ?></h2>
				<ol class="murtadd-sources-list">
					<?php foreach ( $murtadd_sources as $murtadd_src ) : ?>
						<li>
							<?php echo esc_html( $murtadd_src['citation_text'] ); ?>
							<?php murtadd_external_link( $murtadd_src['url'] ?? '', __( 'source ↗', 'murtadd' ), 'murtadd-source-link' ); ?>
						</li>
					<?php endforeach; ?>
				</ol>
			</section>
		<?php endif; ?>

		<?php if ( $murtadd_fatwa_id || $murtadd_doubt_id ) : ?>
			<section class="murtadd-crosslink-row">
				<?php if ( $murtadd_fatwa_id ) : ?>
					<a class="murtadd-crosslink-card" href="<?php echo esc_url( get_permalink( $murtadd_fatwa_id ) ); ?>">
						<span class="murtadd-kicker-small"><?php esc_html_e( 'Scholarly positions', 'murtadd' ); ?></span>
						<span><?php echo esc_html( get_the_title( $murtadd_fatwa_id ) ); ?> →</span>
					</a>
				<?php endif; ?>
				<?php if ( $murtadd_doubt_id ) : ?>
					<a class="murtadd-crosslink-card" href="<?php echo esc_url( get_permalink( $murtadd_doubt_id ) ); ?>">
						<span class="murtadd-kicker-small"><?php esc_html_e( 'The short answer', 'murtadd' ); ?></span>
						<span><?php echo esc_html( get_the_title( $murtadd_doubt_id ) ); ?> →</span>
					</a>
				<?php endif; ?>
			</section>
		<?php endif; ?>

		<?php murtadd_share_row(); ?>

		<footer class="murtadd-entry-footer">
			<?php murtadd_the_reading_time(); ?>
			<?php murtadd_the_reviewed_date(); ?>
		</footer>
	</div><!-- .murtadd-single-main -->

	<?php get_template_part( 'template-parts/counterpart' ); ?>

	<?php get_template_part( 'template-parts/rail/rail', 'single' ); ?>
	</div><!-- .murtadd-single-layout -->
	</article>
	<?php
endwhile;

get_footer();
