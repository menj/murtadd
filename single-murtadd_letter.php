<?php
/**
 * Single letter.
 *
 * The dateline and the archival frame come before the text, deliberately. A
 * reader must know what they are looking at before they read a word of it.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$murtadd_note     = get_post_meta( get_the_ID(), '_murtadd_letter_note', true );
	$murtadd_url      = get_post_meta( get_the_ID(), '_murtadd_letter_url', true );
	$murtadd_replying = get_post_meta( get_the_ID(), '_murtadd_letter_replying_to', true );
	?>
	<article <?php post_class( 'murtadd-letter' ); ?>>
		<a class="murtadd-back" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_letter' ) ); ?>">← <?php esc_html_e( 'All letters', 'murtadd' ); ?></a>

		<header class="murtadd-entry-header">
			<?php murtadd_letter_dateline(); ?>
			<h1><?php the_title(); ?></h1>
			<?php if ( $murtadd_replying ) : ?>
				<p class="murtadd-letter-replying">
					<?php
					/* translators: %s: title of the piece being answered */
					printf( esc_html__( 'In reply to: %s', 'murtadd' ), esc_html( $murtadd_replying ) );
					?>
				</p>
			<?php endif; ?>
		</header>

		<div class="murtadd-letter-frame">
			<p>
				<?php esc_html_e( 'This is an archived letter, reproduced as it was published. It records a position taken at the date above and is not a page written to answer a reader’s doubt.', 'murtadd' ); ?>
				<a href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_doubt' ) ); ?>"><?php esc_html_e( 'Start with the doubts instead →', 'murtadd' ); ?></a>
			</p>
		</div>

		<?php if ( $murtadd_note ) : ?>
			<aside class="murtadd-letter-note">
				<h2 class="murtadd-kicker murtadd-kicker--accent"><?php esc_html_e( 'Editor’s note', 'murtadd' ); ?></h2>
				<?php echo wp_kses_post( wpautop( $murtadd_note ) ); ?>
			</aside>
		<?php endif; ?>

		<div class="murtadd-letter-body">
			<?php the_content(); ?>
		</div>

		<footer class="murtadd-entry-footer">
			<?php murtadd_external_link( $murtadd_url, __( 'Read at the original publication ↗', 'murtadd' ), 'murtadd-btn murtadd-btn--outline' ); ?>
			<?php murtadd_the_reviewed_date(); ?>
		</footer>
	</article>
	<?php
endwhile;

get_footer();
