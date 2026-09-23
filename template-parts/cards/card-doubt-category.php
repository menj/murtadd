<?php
/**
 * Card: doubt category tile (homepage grid).
 * Expects $args['term'] (WP_Term).
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

$murtadd_term = $args['term'] ?? null;
if ( ! $murtadd_term instanceof WP_Term ) {
	return;
}
?>
<a class="murtadd-category-card" href="<?php echo esc_url( get_term_link( $murtadd_term ) ); ?>">
	<h3><?php echo esc_html( $murtadd_term->name ); ?></h3>
	<p><?php echo esc_html( $murtadd_term->description ); ?></p>
	<span class="murtadd-category-count">
		<?php
		/* translators: %d: number of responses */
		printf( esc_html( _n( '%d response', '%d responses', (int) $murtadd_term->count, 'murtadd' ) ), (int) $murtadd_term->count );
		?>
	</span>
</a>
