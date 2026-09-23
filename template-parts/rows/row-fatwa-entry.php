<?php
/**
 * Row: fatwa index entry. Links inward to the single fatwa page.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

$murtadd_scholar = get_post_meta( get_the_ID(), '_murtadd_scholar_or_body', true ) ?: get_the_title();
$murtadd_era     = get_post_meta( get_the_ID(), '_murtadd_era', true );
$murtadd_summary = get_post_meta( get_the_ID(), '_murtadd_position_summary', true );
$murtadd_school  = get_the_terms( get_the_ID(), 'murtadd_school' );
?>
<div class="murtadd-fatwa-row" role="row">
	<span class="murtadd-fatwa-scholar" role="cell"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $murtadd_scholar ); ?></a></span>
	<span role="cell"><?php echo ( $murtadd_school && ! is_wp_error( $murtadd_school ) ) ? esc_html( $murtadd_school[0]->name ) : ''; ?></span>
	<span role="cell"><?php echo esc_html( 'classical' === $murtadd_era ? __( 'Classical', 'murtadd' ) : __( 'Modern', 'murtadd' ) ); ?></span>
	<span role="cell"><?php echo esc_html( wp_trim_words( $murtadd_summary, 16 ) ); ?></span>
	<span role="cell"><a class="murtadd-fatwa-view" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View →', 'murtadd' ); ?></a></span>
</div>
