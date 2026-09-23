<?php
/**
 * 404 — not found.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<header class="murtadd-archive-header">
	<h1><?php esc_html_e( 'That page is not here.', 'murtadd' ); ?></h1>
	<p class="murtadd-subtitle"><?php esc_html_e( 'The link may be old, or the page may have moved. Search, or pick a door below.', 'murtadd' ); ?></p>
	<div class="murtadd-search-inline"><?php get_search_form(); ?></div>
</header>

<div class="murtadd-no-results">
	<div class="murtadd-suggest-routes">
		<?php $murtadd_start = get_page_by_path( 'start-here' ); ?>
		<?php if ( $murtadd_start ) : ?>
			<a class="murtadd-btn murtadd-btn--outline" href="<?php echo esc_url( get_permalink( $murtadd_start ) ); ?>"><?php esc_html_e( 'Start here', 'murtadd' ); ?></a>
		<?php endif; ?>
		<a class="murtadd-btn murtadd-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_doubt' ) ); ?>"><?php esc_html_e( 'Browse doubts', 'murtadd' ); ?></a>
		<a class="murtadd-btn murtadd-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_rebuttal' ) ); ?>"><?php esc_html_e( 'Browse rebuttals', 'murtadd' ); ?></a>
		<a class="murtadd-btn murtadd-btn--outline" href="<?php echo esc_url( get_post_type_archive_link( 'murtadd_fatwa' ) ); ?>"><?php esc_html_e( 'Fatwa index', 'murtadd' ); ?></a>
	</div>
</div>
<?php get_footer(); ?>
