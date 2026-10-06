<?php
/**
 * Contextual links — in-content links to other sites.
 *
 * Turns configured phrases found inside paragraphs of single posts into
 * ordinary links to approved domains. Nothing is labelled or styled
 * differently from the theme's normal links, and no domains are stored in
 * code: everything is configured under Appearance → Contextual Links.
 *
 * @package murtadd
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MUR_CL_OPTION', 'mur_cl' );
define( 'MUR_CL_META', '_' . 'mur_cl_phrases' );

/** Post types that receive links (filterable). */
function mur_cl_post_types() {
	return (array) apply_filters( 'mur_cl_post_types', array( 'murtadd_doubt', 'murtadd_fatwa', 'murtadd_letter', 'murtadd_rebuttal' ) );
}

function mur_cl_options() {
	$saved = get_option( MUR_CL_OPTION, array() );
	$saved = is_array( $saved ) ? $saved : array();
	return array_merge(
		array( 'enabled' => 1, 'domains' => '', 'phrases' => '', 'max' => 3, 'new_tab' => 0 ),
		$saved
	);
}

/** Normalised host: lowercase, no www. */
function mur_cl_host( $url ) {
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	return preg_replace( '/^www\./', '', $host );
}

/** Approved hosts from the Domains tab, minus this site. */
function mur_cl_allowed_hosts( $opts ) {
	$own   = mur_cl_host( home_url() );
	$hosts = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $opts['domains'] ) as $line ) {
		$h = mur_cl_host( false === strpos( $line, '//' ) ? 'https://' . trim( $line ) : trim( $line ) );
		if ( $h && $h !== $own ) {
			$hosts[ $h ] = true;
		}
	}
	return $hosts;
}

/** Parse "phrase | URL" lines; keep only URLs on approved hosts. */
function mur_cl_parse( $text, $hosts ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		if ( 2 !== count( $parts ) || '' === $parts[0] ) {
			continue;
		}
		$url = esc_url_raw( $parts[1], array( 'http', 'https' ) );
		if ( $url && isset( $hosts[ mur_cl_host( $url ) ] ) ) {
			$out[] = array( 'phrase' => $parts[0], 'url' => $url );
		}
	}
	return $out;
}

/**
 * Insert links into paragraph text. Each phrase links once per post, at most
 * one link per paragraph, never inside headings, links, code or captions.
 */
function mur_cl_filter( $content ) {
	if ( ! is_singular( mur_cl_post_types() ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$opts = mur_cl_options();
	if ( empty( $opts['enabled'] ) ) {
		return $content;
	}
	$hosts = mur_cl_allowed_hosts( $opts );
	$items = array_merge(
		mur_cl_parse( get_post_meta( get_the_ID(), MUR_CL_META, true ), $hosts ),
		mur_cl_parse( $opts['phrases'], $hosts )
	);
	if ( ! $hosts || ! $items ) {
		return $content;
	}
	$budget = max( 1, (int) $opts['max'] );
	$rel    = ! empty( $opts['new_tab'] ) ? ' target="_blank" rel="noopener"' : ' rel="noopener"';
	$skip   = array( 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'code', 'pre', 'script', 'style', 'button', 'figcaption', 'blockquote' );
	$used   = array();
	$tokens = preg_split( '/(<[^>]*>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
	$depth  = array_fill_keys( $skip, 0 );
	$in_p   = false;
	$done_p = false;
	foreach ( $tokens as $i => $tok ) {
		if ( '' !== $tok && '<' === $tok[0] ) {
			if ( preg_match( '#^<(/?)([a-z0-9]+)#i', $tok, $m ) ) {
				$tag = strtolower( $m[2] );
				$end = '/' === $m[1];
				if ( 'p' === $tag ) {
					$in_p   = ! $end;
					$done_p = false;
				} elseif ( isset( $depth[ $tag ] ) ) {
					$depth[ $tag ] = max( 0, $depth[ $tag ] + ( $end ? -1 : 1 ) );
				}
			}
			continue;
		}
		if ( ! $in_p || $done_p || $budget < 1 || array_sum( $depth ) ) {
			continue;
		}
		foreach ( $items as $n => $item ) {
			if ( isset( $used[ $n ] ) ) {
				continue;
			}
			$re = '/(?<![\p{L}\p{N}])(' . preg_quote( $item['phrase'], '/' ) . ')(?![\p{L}\p{N}])/iu';
			if ( preg_match( $re, $tok, $mm, PREG_OFFSET_CAPTURE ) ) {
				$link          = '<a href="' . esc_url( $item['url'] ) . '"' . $rel . '>' . $mm[1][0] . '</a>';
				$tokens[ $i ]  = $tok = substr_replace( $tok, $link, $mm[1][1], strlen( $mm[1][0] ) );
				$used[ $n ]    = true;
				$done_p        = true;
				--$budget;
				break;
			}
		}
	}
	return implode( '', $tokens );
}
add_filter( 'the_content', 'mur_cl_filter', 25 );

/* ── Per-post phrases ──────────────────────────────────────────────── */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'mur_cl', __( 'Contextual links', 'murtadd' ), function ( $post ) {
		wp_nonce_field( 'mur_cl_meta', 'mur_cl_nonce' );
		echo '<p class="description">' . esc_html__( 'One per line: phrase | URL. The phrase must appear in a paragraph; the URL must be on an approved domain.', 'murtadd' ) . '</p>';
		printf( '<textarea class="widefat" rows="5" name="mur_cl_phrases">%s</textarea>', esc_textarea( (string) get_post_meta( $post->ID, MUR_CL_META, true ) ) );
	}, mur_cl_post_types(), 'side' );
} );

add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['mur_cl_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['mur_cl_nonce'] ) ), 'mur_cl_meta' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_autosave( $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, MUR_CL_META, sanitize_textarea_field( wp_unslash( isset( $_POST['mur_cl_phrases'] ) ? $_POST['mur_cl_phrases'] : '' ) ) );
} );

/* ── Settings page (tabbed) ────────────────────────────────────────── */

add_action( 'admin_menu', function () {
	add_theme_page( __( 'Contextual Links', 'murtadd' ), __( 'Contextual Links', 'murtadd' ), 'manage_options', 'mur_cl', 'mur_cl_page' );
} );

add_action( 'admin_init', function () {
	register_setting( 'mur_cl', MUR_CL_OPTION, array( 'sanitize_callback' => 'mur_cl_sanitize' ) );
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'appearance_page_mur_cl' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'mur_cl-admin', get_theme_file_uri( 'assets/css/contextual-links-admin.css' ), array(), filemtime( get_theme_file_path( 'assets/css/contextual-links-admin.css' ) ) );
	wp_enqueue_script( 'mur_cl-admin', get_theme_file_uri( 'assets/js/contextual-links-admin.js' ), array(), filemtime( get_theme_file_path( 'assets/js/contextual-links-admin.js' ) ), true );
} );

function mur_cl_sanitize( $in ) {
	$in = is_array( $in ) ? $in : array();
	return array(
		'enabled' => empty( $in['enabled'] ) ? 0 : 1,
		'domains' => sanitize_textarea_field( isset( $in['domains'] ) ? $in['domains'] : '' ),
		'phrases' => sanitize_textarea_field( isset( $in['phrases'] ) ? $in['phrases'] : '' ),
		'max'     => isset( $in['max'] ) ? min( 12, max( 1, (int) $in['max'] ) ) : 3,
		'new_tab' => empty( $in['new_tab'] ) ? 0 : 1,
	);
}

function mur_cl_page() {
	$o = mur_cl_options();
	$n = MUR_CL_OPTION;
	?>
	<div class="wrap cl-admin">
		<h1><?php esc_html_e( 'Contextual Links', 'murtadd' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'mur_cl' ); ?>
			<nav class="cl-admin__tabs" role="tablist">
				<button type="button" class="cl-admin__tab" role="tab" data-tab="domains"><?php esc_html_e( 'Domains', 'murtadd' ); ?></button>
				<button type="button" class="cl-admin__tab" role="tab" data-tab="phrases"><?php esc_html_e( 'Phrases', 'murtadd' ); ?></button>
				<button type="button" class="cl-admin__tab" role="tab" data-tab="rules"><?php esc_html_e( 'Rules', 'murtadd' ); ?></button>
			</nav>

			<section class="cl-admin__panel" data-panel="domains">
				<p class="description"><?php esc_html_e( 'Approved domains, one per line. Links to any other domain, and to this site, are ignored.', 'murtadd' ); ?></p>
				<textarea class="large-text code" rows="8" name="<?php echo esc_attr( $n ); ?>[domains]"><?php echo esc_textarea( $o['domains'] ); ?></textarea>
			</section>

			<section class="cl-admin__panel" data-panel="phrases">
				<p class="description"><?php esc_html_e( 'Site-wide, one per line: phrase | URL. The phrase is linked where it already appears in a paragraph.', 'murtadd' ); ?></p>
				<textarea class="large-text code" rows="14" name="<?php echo esc_attr( $n ); ?>[phrases]"><?php echo esc_textarea( $o['phrases'] ); ?></textarea>
			</section>

			<section class="cl-admin__panel" data-panel="rules">
				<div class="cl-admin__row"><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enabled]" value="1" <?php checked( $o['enabled'] ); ?>> <?php esc_html_e( 'Insert links', 'murtadd' ); ?></label></div>
				<div class="cl-admin__row"><label><?php esc_html_e( 'Maximum links per post', 'murtadd' ); ?> <input type="number" min="1" max="12" name="<?php echo esc_attr( $n ); ?>[max]" value="<?php echo esc_attr( $o['max'] ); ?>"></label></div>
				<div class="cl-admin__row"><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[new_tab]" value="1" <?php checked( $o['new_tab'] ); ?>> <?php esc_html_e( 'Open in a new tab', 'murtadd' ); ?></label></div>
			</section>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
