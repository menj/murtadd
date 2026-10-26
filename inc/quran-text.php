<?php
/**
 * Qur'anic verse text for the KFGQPC HAFS Uthmanic Script face.
 *
 * Most Uthmani text in circulation follows the Tanzil encoding: sukun is
 * U+0652 and the small rounded zero over a silent letter is U+06DF. The KFGQPC
 * face follows the Complex's own encoding, where the Madani sukun is U+06E1
 * and the rounded zero is U+0652; it draws U+06DF as a spacing ornament, so a
 * Tanzil verse shows a round sukun everywhere and a stray circle on every
 * silent letter. The face also draws Arabic-Indic digits as the ayah
 * medallion, so ornate brackets written round a verse number would draw a
 * second frame.
 *
 * Verse text is mapped as it renders; the stored text stays standard. Only an
 * element with class murtadd-ayah, or the Arabic verse in the rail, is
 * touched: never a hadith or any other Arabic.
 *
 * @package Murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map Tanzil-encoded Uthmani text to the KFGQPC encoding.
 *
 * Text that already carries U+06E1 is taken to be in the Complex's encoding
 * and left alone. Callers must not map the same text twice: a verse with a
 * rounded zero and no sukun carries no U+06E1 after mapping, so a second pass
 * would turn its rounded zeros into sukun. murtadd_quran_verses() marks what it
 * has mapped for that reason.
 *
 * @param string $text Verse text or markup.
 * @return string
 */
function murtadd_quran_text_for_font( $text ) {
	$text = (string) preg_replace( '/[\x{FD3E}\x{FD3F}]\s*([\x{0660}-\x{0669}]+)\s*[\x{FD3E}\x{FD3F}]/u', '$1', (string) $text );
	if ( false !== strpos( $text, "\u{06E1}" ) ) {
		return $text;
	}
	return strtr(
		$text,
		array(
			"\u{0652}" => "\u{06E1}",
			"\u{06DF}" => "\u{0652}",
		)
	);
}

/**
 * Map every Qur'anic verse element (class murtadd-ayah) in a block of markup.
 *
 * A mapped element carries data-quran-encoding="kfgqpc" and is skipped on any
 * later pass.
 *
 * @param string $html Markup.
 * @return string
 */
function murtadd_quran_verses( $html ) {
	$html = (string) $html;
	if ( false === strpos( $html, 'murtadd-ayah' ) ) {
		return $html;
	}
	return (string) preg_replace_callback(
		'#(<(p|span|div|blockquote)\b[^>]*\bclass=(["\'])(?:(?!\3).)*\bmurtadd-ayah\b(?!-)(?:(?!\3).)*\3[^>]*)(>)(.*?)(</\2>)#su',
		static function ( $m ) {
			if ( false !== strpos( $m[1], 'data-quran-encoding=' ) ) {
				return $m[0];
			}
			return $m[1] . ' data-quran-encoding="kfgqpc"' . $m[4] . murtadd_quran_text_for_font( $m[5] ) . $m[6];
		},
		$html
	);
}

/**
 * Whether a string is Arabic script (more Arabic letters than Latin ones).
 *
 * @param string $text Text.
 * @return bool
 */
function murtadd_is_arabic( $text ) {
	$arabic = preg_match_all( '/\p{Arabic}/u', (string) $text );
	$latin  = preg_match_all( '/\p{Latin}/u', (string) $text );
	return $arabic > 0 && $arabic > $latin;
}

add_filter( 'the_content', 'murtadd_quran_verses', 6 );
