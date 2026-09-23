<?php
/**
 * Row: mixed-type list entry. Used by search, the topic archive, and the
 * generic date archive so every list in the theme reads the same way.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

$murtadd_type = get_post_type();

if ( 'murtadd_doubt' === $murtadd_type ) {
	$murtadd_summary = get_post_meta( get_the_ID(), '_murtadd_short_response', true );
} elseif ( 'murtadd_rebuttal' === $murtadd_type ) {
	$murtadd_summary = get_post_meta( get_the_ID(), '_murtadd_claim', true );
} elseif ( 'murtadd_fatwa' === $murtadd_type ) {
	$murtadd_summary = get_post_meta( get_the_ID(), '_murtadd_position_summary', true );
} else {
	$murtadd_summary = get_the_excerpt();
}
?>
<article <?php post_class( 'murtadd-list-row' ); ?>>
	<span class="murtadd-chip-small murtadd-chip--outline"><?php echo esc_html( murtadd_type_label( $murtadd_type ) ); ?></span>
	<h2 class="murtadd-list-title">
		<a href="<?php the_permalink(); ?>">
			<?php if ( 'murtadd_doubt' === $murtadd_type ) : ?>
				“<?php murtadd_the_doubt_statement(); ?>”
			<?php else : ?>
				<?php the_title(); ?>
			<?php endif; ?>
		</a>
	</h2>
	<?php if ( $murtadd_summary ) : ?>
		<div class="murtadd-list-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $murtadd_summary ), 24 ) ); ?></div>
	<?php endif; ?>
	<div class="murtadd-list-meta">
		<?php murtadd_term_chip( 'murtadd_topic', 'murtadd-chip-small' ); ?>
		<?php if ( 'post' === $murtadd_type ) : ?>
			<span><?php echo esc_html( get_the_date() ); ?></span>
		<?php endif; ?>
	</div>
</article>
