<?php
/**
 * Search form — used in the sidebar and on the search results page.
 * Replaces the WordPress default markup so both instances share one shape.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;
?>
<form role="search" method="get" class="murtadd-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="murtadd-search-<?php echo esc_attr( wp_unique_id() ); ?>"><?php esc_html_e( 'Search', 'murtadd' ); ?></label>
	<input type="search" class="murtadd-search-field" placeholder="<?php esc_attr_e( 'Search the site…', 'murtadd' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	<button type="submit" class="murtadd-search-submit"><?php esc_html_e( 'Search', 'murtadd' ); ?></button>
</form>
