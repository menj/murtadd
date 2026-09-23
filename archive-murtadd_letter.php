<?php
/**
 * Letters archive.
 *
 * Framed as an archive, plainly. These are positions taken at a moment, in
 * another publication, and the page says so before the reader clicks anything.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="murtadd-archive-header">
	<h1><?php esc_html_e( 'Letters', 'murtadd' ); ?></h1>
	<p class="murtadd-subtitle">
		<?php esc_html_e( 'Correspondence and opinion pieces published in other outlets, archived here in their original form. Each is dated, and each is a position taken at a particular moment. They are kept as a record rather than offered as an answer to anyone’s doubt — for that, start with the doubts.', 'murtadd' ); ?>
	</p>
</header>

<div class="murtadd-article-list">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class( 'murtadd-list-row murtadd-letter-row' ); ?>>
				<?php murtadd_letter_dateline(); ?>
				<h2 class="murtadd-list-title">
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</h2>
				<?php $murtadd_replying = get_post_meta( get_the_ID(), '_murtadd_letter_replying_to', true ); ?>
				<?php if ( $murtadd_replying ) : ?>
					<div class="murtadd-list-excerpt">
						<?php
						/* translators: %s: title of the piece being answered */
						printf( esc_html__( 'In reply to: %s', 'murtadd' ), esc_html( $murtadd_replying ) );
						?>
					</div>
				<?php endif; ?>
				<div class="murtadd-list-meta">
					<?php murtadd_term_chip( 'murtadd_topic', 'murtadd-chip-small' ); ?>
				</div>
			</article>
			<?php
		endwhile;
		?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p class="murtadd-empty"><?php esc_html_e( 'No letters archived yet.', 'murtadd' ); ?></p>
	<?php endif; ?>
</div>
<?php get_footer(); ?>
