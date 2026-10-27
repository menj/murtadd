<?php
/**
 * Starter content data loaders.
 *
 * Doubts and Rebuttals covering every substantive chapter of the author's 2006
 * book, recast for this site, plus the second-wave entries: the echo-chamber
 * method, the scientific-miracles audit, and the classical apostasy law as
 * history. Data only; inc/seed-content.php and inc/activation.php insert.
 *
 * The data itself is JSON in inc/data/seed/ (topics, rebuttals, doubts,
 * pages), not PHP: content-population data is read and decoded, never
 * executed. A file that is missing, unreadable, not valid JSON or not the
 * expected shape loads as an empty array, so the seeder creates nothing from
 * it instead of failing. Each file is decoded once per request.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Decode one seed data file.
 *
 * @param string $name File name without extension, in inc/data/seed/.
 * @return array Decoded data, or an empty array when it cannot be read.
 */
function murtadd_seed_read_json( $name ) {
	$file = __DIR__ . '/data/seed/' . $name . '.json';
	if ( ! is_readable( $file ) ) {
		return array(); // While an update is unpacking the file can be briefly absent.
	}
	$json = file_get_contents( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- a bundled file.
	if ( ! is_string( $json ) ) {
		return array();
	}
	$data = json_decode( $json, true );
	return ( is_array( $data ) && JSON_ERROR_NONE === json_last_error() ) ? $data : array();
}

/**
 * Whether a value has the given type: 'string', 'array', 'strings' (a list of
 * strings) or 'sources' (a list of citation rows, each a citation_text and a
 * url, both strings).
 *
 * @param mixed  $value Candidate value.
 * @param string $type  Type name.
 * @return bool
 */
function murtadd_seed_value_valid( $value, $type ) {
	if ( 'strings' === $type || 'sources' === $type ) {
		if ( ! is_array( $value ) || array_values( $value ) !== $value ) {
			return false;
		}
		foreach ( $value as $item ) {
			$ok = 'strings' === $type
				? is_string( $item )
				: is_array( $item ) && isset( $item['citation_text'], $item['url'] ) && is_string( $item['citation_text'] ) && is_string( $item['url'] ) && 2 === count( $item );
			if ( ! $ok ) {
				return false;
			}
		}
		return true;
	}
	return gettype( $value ) === $type;
}

/**
 * Whether every listed key of $entry holds a value of the given type.
 *
 * @param mixed                $entry    Candidate entry.
 * @param array<string,string> $required Key => type (see murtadd_seed_value_valid()).
 * @param array<string,string> $optional Key => type, checked only when present.
 * @return bool
 */
function murtadd_seed_entry_valid( $entry, $required, $optional = array() ) {
	if ( ! is_array( $entry ) ) {
		return false;
	}
	foreach ( $required as $key => $type ) {
		if ( ! array_key_exists( $key, $entry ) || ! murtadd_seed_value_valid( $entry[ $key ], $type ) ) {
			return false;
		}
	}
	foreach ( $optional as $key => $type ) {
		if ( array_key_exists( $key, $entry ) && ! murtadd_seed_value_valid( $entry[ $key ], $type ) ) {
			return false;
		}
	}
	return true;
}

/**
 * Load a list of entries, all or nothing: one malformed entry rejects the file.
 *
 * @param string               $name     File name without extension.
 * @param array<string,string> $required Key => type every entry must carry.
 * @param array<string,string> $optional Key => type, checked only when present.
 * @return array<int,array>
 */
function murtadd_seed_load_entries( $name, $required, $optional = array() ) {
	$data = murtadd_seed_read_json( $name );
	if ( array_values( $data ) !== $data ) {
		return array();
	}
	foreach ( $data as $entry ) {
		if ( ! murtadd_seed_entry_valid( $entry, $required, $optional ) ) {
			return array();
		}
	}
	return $data;
}

/**
 * Load a string => string map, all or nothing.
 *
 * @param string $name File name without extension.
 * @return array<string,string>
 */
function murtadd_seed_load_map( $name ) {
	$data = murtadd_seed_read_json( $name );
	foreach ( $data as $key => $value ) {
		if ( ! is_string( $key ) || ! is_string( $value ) ) {
			return array();
		}
	}
	return $data;
}

/**
 * Entries whose copy is blank are dropped: blank copy is no copy, and must
 * never create a page or replace a placeholder with nothing.
 *
 * @param array<string,string> $map Slug => copy.
 * @return array<string,string>
 */
function murtadd_seed_drop_blank( $map ) {
	return array_filter(
		$map,
		static function ( $copy ) {
			return '' !== trim( $copy );
		}
	);
}

/**
 * Topic terms the starter content needs.
 *
 * NOT murtadd_seed_topics(): that name belongs to inc/taxonomy-topic.php and
 * redeclaring it is fatal.
 *
 * @return array<string,string> Slug => name.
 */
function murtadd_starter_topics() {
	static $topics = null;
	if ( null === $topics ) {
		$topics = murtadd_seed_load_map( 'topics' );
	}
	return $topics;
}

/**
 * Rebuttals. Inserted before Doubts, since each Doubt points at one by ID.
 *
 * @return array<int,array>
 */
function murtadd_seed_rebuttals() {
	static $rebuttals = null;
	if ( null === $rebuttals ) {
		$rebuttals = murtadd_seed_load_entries(
			'rebuttals',
			array(
				'slug'        => 'string',
				'title'       => 'string',
				'claim'       => 'string',
				'source_type' => 'string',
				'topics'      => 'strings',
				'sources'     => 'sources',
				'body'        => 'string',
			),
			array( 'ce_slug' => 'string' )
		);
	}
	return $rebuttals;
}

/**
 * Doubts. 'related' names a rebuttal slug ('' for none).
 *
 * @return array<int,array>
 */
function murtadd_seed_doubts() {
	static $doubts = null;
	if ( null === $doubts ) {
		$doubts = murtadd_seed_load_entries(
			'doubts',
			array(
				'slug'      => 'string',
				'title'     => 'string',
				'statement' => 'string',
				'category'  => 'string',
				'topics'    => 'strings',
				'related'   => 'string',
				'response'  => 'string',
			),
			array( 'ce_slug' => 'string' )
		);
	}
	return $doubts;
}

/**
 * Resolve the {{url:/path/}} tokens in seed copy to escaped site addresses.
 *
 * @param string $html Copy with tokens.
 * @return string
 */
function murtadd_seed_resolve_urls( $html ) {
	return preg_replace_callback(
		'/\{\{url:([^{}"\s]*)\}\}/',
		static function ( $m ) {
			return esc_url( home_url( $m[1] ) );
		},
		$html
	);
}

/**
 * The copy for the pages activation creates.
 *
 * Only pages whose template renders the_content() are here: irtidad, about,
 * faq, contact and privacy. Links are stored as {{url:/path/}} tokens and
 * built with home_url() when the copy is loaded, so a site installed in a
 * subdirectory links correctly, and every path named here exists in the
 * starter content (tests/PageCopyTest.php checks each one).
 *
 * Contact carries the [murtadd_contact] form (inc/contact.php), and Privacy
 * describes what the theme itself does; neither names an operator or
 * publishes an address.
 *
 * @return array<string,string> Slug => HTML.
 */
function murtadd_seed_page_bodies() {
	static $raw = null;
	if ( null === $raw ) {
		$raw = murtadd_seed_load_map( 'pages' );
	}
	// Tokens resolve on every call: home_url() can differ between calls (multisite switches, filters).
	return array_map( 'murtadd_seed_resolve_urls', murtadd_seed_drop_blank( $raw ) );
}
