<?php
/**
 * The counterpart link: same topic, other site, different job.
 *
 * Both sites cover a good deal of the same ground on purpose. This one answers
 * the charge in a forensic register; the linked site takes the reader through
 * the question at length in a pastoral one. A reader who wants the second after
 * finishing the first should not have to go looking, and a reader who arrives
 * here from a search should be told the other treatment exists.
 *
 * Renders only when the entry names a counterpart AND the base URL is set in
 * Theme Options → Linked sites. Either missing means nothing renders, which is
 * the correct behaviour for a site whose owner has cleared the link.
 *
 * @package Murtadd
 */

defined( 'ABSPATH' ) || exit;

$murtadd_ce_slug = get_post_meta( get_the_ID(), '_murtadd_ce_slug', true );
$murtadd_ce_base = murtadd_option( 'murtadd_cross_links', 'ce_base_url', '' );

if ( ! $murtadd_ce_slug || ! $murtadd_ce_base ) {
	return;
}

$murtadd_ce_url = trailingslashit( $murtadd_ce_base ) . 'articles/' . sanitize_title( $murtadd_ce_slug ) . '/';
?>
<aside class="murtadd-counterpart">
	<p class="murtadd-counterpart-kicker"><?php esc_html_e( 'The same question, at length', 'murtadd' ); ?></p>
	<p class="murtadd-counterpart-body">
		<?php esc_html_e( 'This page answers the charge. If you want the question itself worked through slowly, rather than the objection answered, the companion site takes it up there.', 'murtadd' ); ?>
	</p>
	<a class="murtadd-counterpart-link" href="<?php echo esc_url( $murtadd_ce_url ); ?>" target="_blank" rel="noopener">
		<?php esc_html_e( 'Read it on Compelling Evidence', 'murtadd' ); ?> <span aria-hidden="true">↗</span>
	</a>
</aside>
