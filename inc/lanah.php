<?php
/**
 * [murtadd_lanah] The curse "laʿnat Allāh" (the curse of God) after the name
 * of Paul of Tarsus, as calligraphy (assets/images/lanat-allah.svg, drawn as
 * a mask in the strike red, --murtadd-accent-strike). The words stay inside,
 * visually hidden, for screen readers, search and a copy and paste, and show
 * as written where the mask cannot draw.
 *
 * It is never added on its own: it appears only where content places the
 * shortcode, by hand, and only where the text gives the site's own verdict on
 * Paul. The site is care-first; no seeded content uses it. Excerpts and
 * descriptions strip shortcodes, so it never reaches them.
 *
 * @package Murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The mark as markup.
 *
 * @return string
 */
function murtadd_lanah_shortcode() {
	$words = 'laʿnat Allāh, the curse of God';
	return '<span class="murtadd-lanah" lang="ar-Latn" title="' . esc_attr( $words ) . '"><span class="murtadd-lanah__text">' . esc_html( $words ) . '</span></span>';
}
add_shortcode( 'murtadd_lanah', 'murtadd_lanah_shortcode' );
