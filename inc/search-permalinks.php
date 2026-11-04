<?php
/**
 * Readable search addresses.
 *
 * Turns /?s=apostasy into /search/apostasy/. WordPress already routes
 * /search/term/ and /search/term/page/2/; the theme adds the 301 from the
 * query-string form and a base word set in Settings, Permalinks (as the
 * plugin did) or on the Content tab of Theme Options. Both edit the same
 * option, murtadd_search.
 *
 * Adapted from the Pretty Search Permalinks plugin by Angel Costa (GPLv2 or
 * later). The plugin redirected an empty search to /search/ and dropped the
 * page number and every other query argument; this version sends no redirect
 * for an empty search and carries the page number and the arguments across.
 * The rewrite rules are rebuilt on the theme's version change, through
 * murtadd_maybe_flush_rewrites(), and when the base changes; never on an
 * ordinary load.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The saved search settings, with defaults. The plugin's base is imported
 * while the theme has none of its own.
 *
 * @return array{pretty:int,base:string}
 */
function murtadd_search_settings() {
	$saved = get_option( 'murtadd_search', null );

	if ( ! is_array( $saved ) ) {
		$saved = array( 'base' => (string) get_option( 'wpseosearch_base', 'search' ) );
	}

	$base = murtadd_search_clean_base( $saved['base'] ?? 'search' );

	return array(
		'pretty' => isset( $saved['pretty'] ) ? ( $saved['pretty'] ? 1 : 0 ) : 1,
		'base'   => $base,
	);
}

/**
 * A base word cleaned with sanitize_key (lowercase letters, numbers,
 * hyphens and underscores), 'search' when nothing is left.
 *
 * @param mixed $base Raw base.
 * @return string
 */
function murtadd_search_clean_base( $base ) {
	$base = sanitize_key( is_scalar( $base ) ? (string) $base : '' );

	return '' !== $base ? $base : 'search';
}

/**
 * Sanitises murtadd_search. Saved from the Content tab (pretty and base)
 * and from Settings, Permalinks (base only). A missing value keeps the saved
 * one. A changed base clears the rewrite stamp, so the rules are rebuilt once.
 *
 * @param mixed $input Raw input.
 * @return array{pretty:int,base:string}
 */
function murtadd_sanitize_search( $input ) {
	$current = murtadd_search_settings();

	if ( ! is_array( $input ) ) {
		return $current;
	}

	$out = array(
		'pretty' => array_key_exists( 'base', $input ) && ! array_key_exists( 'from_permalinks', $input ) ? ( empty( $input['pretty'] ) ? 0 : 1 ) : $current['pretty'],
		'base'   => isset( $input['base'] ) ? murtadd_search_clean_base( $input['base'] ) : $current['base'],
	);

	$problem = murtadd_search_base_problem( $out['base'] );

	if ( '' !== $problem ) {
		if ( function_exists( 'add_settings_error' ) ) {
			add_settings_error( 'murtadd_search', 'murtadd-search-base', $problem );
		}
		$out['base'] = $current['base'];
	}

	if ( $out !== $current ) {
		delete_option( 'murtadd_rewrite_version' );
	}

	return $out;
}

/**
 * Whether readable search addresses are on. They stand aside while the
 * plugin is active, so a search is never redirected twice.
 *
 * @return bool
 */
function murtadd_pretty_search_enabled() {
	return ! function_exists( 'wpseosearch_base' ) && 1 === murtadd_search_settings()['pretty'];
}

/**
 * Applies the base to the rewrite rules, before they are built.
 *
 * @return void
 */
function murtadd_apply_search_base() {
	global $wp_rewrite;

	if ( murtadd_pretty_search_enabled() && is_object( $wp_rewrite ) ) {
		$wp_rewrite->search_base = murtadd_search_settings()['base'];
	}
}
add_action( 'init', 'murtadd_apply_search_base', 1 );

/**
 * The readable address for a search.
 *
 * @param string $term  Search term.
 * @param int    $paged Page number.
 * @return string
 */
function murtadd_search_url( $term, $paged = 1 ) {
	$path = murtadd_search_settings()['base'] . '/' . rawurlencode( $term );

	if ( $paged > 1 ) {
		$path .= '/page/' . (int) $paged;
	}

	return home_url( user_trailingslashit( '/' . $path ) );
}

/**
 * Redirects /?s=term to /base/term/ with a 301, keeping the page number and
 * any other query argument. An empty search is left where it is.
 *
 * @return void
 */
function murtadd_redirect_search() {
	global $wp_rewrite;

	if ( ! murtadd_pretty_search_enabled() || is_admin() || ! is_search() || ! is_object( $wp_rewrite ) || ! $wp_rewrite->using_permalinks() ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only redirect of a public search.
	if ( ! isset( $_GET['s'] ) ) {
		return;
	}

	$term = (string) get_query_var( 's' );

	if ( '' === trim( $term ) ) {
		return;
	}

	$url = murtadd_search_url( $term, (int) get_query_var( 'paged' ) );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$args = wp_unslash( $_GET );
	unset( $args['s'], $args['paged'] );
	$args = murtadd_search_clean_args( $args );

	if ( $args ) {
		// Arrays such as post_type[] stay arrays.
		$url .= ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $args, '', '&', PHP_QUERY_RFC3986 );
	}

	wp_safe_redirect( $url, 301 );
	exit;
}
add_action( 'template_redirect', 'murtadd_redirect_search', 1 );

/**
 * The Search base field on Settings, Permalinks, saved when that screen is
 * saved. WordPress flushes the rewrite rules on that save, so the new base
 * is applied first.
 *
 * @return void
 */
function murtadd_search_permalink_screen() {
	if ( function_exists( 'wpseosearch_base' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['murtadd_search_base'] ) ) {
		check_admin_referer( 'update-permalink' );

		update_option(
			'murtadd_search',
			// Merged into what is saved: the switch keeps its value, so a site
			// with readable addresses off does not get them back.
			array(
				'pretty'          => murtadd_search_settings()['pretty'],
				'base'            => sanitize_key( wp_unslash( $_POST['murtadd_search_base'] ) ),
				'from_permalinks' => 1,
			)
		);
		murtadd_apply_search_base();
	}

	add_settings_field( 'murtadd_search_base', __( 'Search base', 'murtadd' ), 'murtadd_search_permalink_field', 'permalink', 'optional', array( 'label_for' => 'murtadd_search_base' ) );
}
add_action( 'load-options-permalink.php', 'murtadd_search_permalink_screen' );

/**
 * The field.
 *
 * @return void
 */
function murtadd_search_permalink_field() {
	printf(
		'<input type="text" name="murtadd_search_base" id="murtadd_search_base" class="regular-text code" value="%1$s"><p class="description">%2$s</p>',
		esc_attr( murtadd_search_settings()['base'] ),
		esc_html__( 'Lowercase letters, numbers, hyphens and underscores; anything else is removed. It cannot be an address the site already uses, such as doubts or topic. Searches open at this address, such as /search/apostasy/. Switch readable search addresses on or off in Theme Options, Content.', 'murtadd' )
	);
}

/**
 * The Search addresses card on the Content tab. It saves murtadd_search,
 * which is registered in the Content tab's group.
 *
 * @return void
 */
function murtadd_render_search_card() {
	murtadd_admin_card_open( __( 'Search addresses', 'murtadd' ), __( 'Readable addresses for search results, such as /search/apostasy/. The base word can also be set in Settings, Permalinks.', 'murtadd' ), 'search' );

	if ( function_exists( 'wpseosearch_base' ) ) {
		echo '<p class="description">' . esc_html__( 'The Pretty Search Permalinks plugin is active, so the theme leaves search addresses to it. Deactivate the plugin to set them here; its base word is carried over.', 'murtadd' ) . '</p>';
		murtadd_admin_card_close();
		return;
	}

	$s = murtadd_search_settings();

	murtadd_admin_toggle_row(
		__( 'Readable addresses', 'murtadd' ),
		'murtadd_search_pretty',
		'murtadd_search[pretty]',
		(bool) $s['pretty'],
		__( 'Send /?s= searches to the readable address with a permanent redirect', 'murtadd' ),
		__( 'An empty search is never redirected. Page numbers and any other part of the address are kept.', 'murtadd' )
	);
	murtadd_admin_row_open( __( 'Base word', 'murtadd' ), 'murtadd_search_base_tab' );
	printf(
		'<input type="text" id="murtadd_search_base_tab" name="murtadd_search[base]" value="%1$s" class="regular-text" aria-describedby="murtadd_search_base_tab-desc"><p class="description" id="murtadd_search_base_tab-desc">%2$s</p>',
		esc_attr( $s['base'] ),
		esc_html__( 'Lowercase letters, numbers, hyphens and underscores; anything else is removed. It cannot be an address the site already uses, such as doubts or topic.', 'murtadd' )
	);
	murtadd_admin_row_close();
	murtadd_admin_card_close();
}

/**
 * Query arguments safe to carry across the redirect: strings, and arrays of
 * strings (post_type[] and the like), nested as they came.
 *
 * @param array $args Raw arguments.
 * @return array
 */
function murtadd_search_clean_args( $args ) {
	$out = array();

	foreach ( (array) $args as $key => $value ) {
		if ( is_array( $value ) ) {
			$value = murtadd_search_clean_args( $value );
			if ( $value ) {
				$out[ $key ] = $value;
			}
		} elseif ( is_scalar( $value ) ) {
			$out[ $key ] = (string) $value;
		}
	}

	return $out;
}

/**
 * Words the base may not take: the theme's own rewrite slugs (its post
 * types, taxonomies, the doubt doors, the image sitemap, legacy paths) and
 * WordPress's.
 *
 * @return string[]
 */
function murtadd_search_reserved_bases() {
	$reserved = array( 'doubts', 'rebuttals', 'fatwa', 'letters', 'guides', 'journal', 'blog', 'topic', 'school', 'kind', 'category', 'tag', 'author', 'page', 'feed', 'comments', 'embed', 'attachment', 'type', 'wp-admin', 'wp-json', 'wp-content', 'wp-includes', 'wp-sitemap', 'murtadd-image-sitemap' );

	if ( function_exists( 'get_taxonomies' ) ) {
		foreach ( (array) get_taxonomies( array(), 'objects' ) as $taxonomy ) {
			if ( is_object( $taxonomy ) && ! empty( $taxonomy->rewrite['slug'] ) ) {
				$reserved[] = strtok( $taxonomy->rewrite['slug'], '/' );
			}
		}
	}

	foreach ( (array) get_post_types( array(), 'objects' ) as $type ) {
		if ( is_object( $type ) && ! empty( $type->rewrite['slug'] ) ) {
			$reserved[] = strtok( $type->rewrite['slug'], '/' );
		}
	}

	return array_values( array_unique( array_filter( $reserved ) ) );
}

/**
 * Why a base cannot be used, or '' when it can.
 *
 * @param string $base Cleaned base.
 * @return string
 */
function murtadd_search_base_problem( $base ) {
	if ( in_array( $base, murtadd_search_reserved_bases(), true ) ) {
		/* translators: %s: the refused base word. */
		return sprintf( __( 'The search base "%s" is already an address on this site, so the previous base is kept.', 'murtadd' ), $base );
	}

	return '';
}

/**
 * Whether the stored rewrite rules contain the search rule for the current
 * base. With readable addresses off there is nothing to look for.
 *
 * @return bool
 */
function murtadd_search_rules_ready() {
	if ( ! murtadd_pretty_search_enabled() ) {
		return true;
	}

	$rules = get_option( 'rewrite_rules' );

	if ( ! is_array( $rules ) ) {
		return false;
	}

	$prefix = murtadd_search_settings()['base'] . '/(.+)';

	foreach ( array_keys( $rules ) as $regex ) {
		if ( 0 === strpos( (string) $regex, $prefix ) ) {
			return true;
		}
	}

	return false;
}
