<?php
/**
 * Card: outbound cross-link (homepage). No relationship stated.
 * Expects $args: url, title, desc.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

if ( empty( $args['url'] ) ) {
	return;
}
?>
<a class="murtadd-crosslink-card" href="<?php echo esc_url( $args['url'] ); ?>" target="_blank" rel="noopener">
	<span><?php echo esc_html( $args['title'] ); ?> ↗</span>
	<span class="murtadd-kicker-small"><?php echo esc_html( $args['desc'] ); ?></span>
</a>
