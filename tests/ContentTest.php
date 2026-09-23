<?php
/**
 * Content invariants.
 *
 * Every assertion here corresponds to a defect that actually shipped on this
 * site and was found by a person rather than by the software.
 *
 * @package Murtadd\Tests
 */

use PHPUnit\Framework\TestCase;

final class ContentTest extends TestCase {

	/**
	 * Every doubt's related rebuttal must exist.
	 *
	 * Shipped broken once: "believe-without-knowing" pointed at a rebuttal
	 * slug that was never written, so its "go deeper" link led nowhere.
	 */
	public function test_every_doubt_links_to_a_real_rebuttal() {
		$slugs = array_column( murtadd_seed_rebuttals(), 'slug' );

		foreach ( murtadd_seed_doubts() as $doubt ) {
			if ( '' === $doubt['related'] ) {
				continue; // Deliberately unlinked; the fear doubt has no claim to defeat.
			}
			$this->assertContains(
				$doubt['related'],
				$slugs,
				"Doubt '{$doubt['slug']}' points at a rebuttal that does not exist: '{$doubt['related']}'"
			);
		}
	}

	/**
	 * All four doors must be populated.
	 *
	 * Shipped broken once: the Identity category was empty, so one of the four
	 * cards on the homepage read "0 responses" — a door opening onto nothing.
	 */
	public function test_all_four_doubt_categories_are_populated() {
		$used = array_unique( array_column( murtadd_seed_doubts(), 'category' ) );
		sort( $used );

		$this->assertSame(
			array( 'emotional', 'identity', 'intellectual', 'scriptural' ),
			$used,
			'Every doubt category must contain at least one doubt, or a homepage door opens onto nothing.'
		);
	}

	/** Slugs must be unique, or WordPress silently appends -2 and links break. */
	public function test_slugs_are_unique() {
		foreach ( array( 'murtadd_seed_doubts', 'murtadd_seed_rebuttals' ) as $fn ) {
			$slugs = array_column( $fn(), 'slug' );
			$this->assertSame( count( $slugs ), count( array_unique( $slugs ) ), "Duplicate slug in {$fn}()" );
		}
	}

	/** No doubt may take a reserved category slug: the term rule would shadow it. */
	public function test_no_doubt_takes_a_reserved_category_slug() {
		$reserved = array( 'intellectual', 'scriptural', 'emotional', 'identity' );

		foreach ( murtadd_seed_doubts() as $doubt ) {
			$this->assertNotContains(
				$doubt['slug'],
				$reserved,
				"Doubt slug '{$doubt['slug']}' collides with a category URL and would be unreachable."
			);
		}
	}

	/** The site's whole promise is checkable sources. A rebuttal without them breaks it. */
	public function test_every_rebuttal_has_at_least_one_source() {
		foreach ( murtadd_seed_rebuttals() as $rebuttal ) {
			$this->assertNotEmpty(
				$rebuttal['sources'],
				"Rebuttal '{$rebuttal['slug']}' has no sources. The site promises the reader can check for themselves."
			);
		}
	}

	/** Every rebuttal must state the claim it answers, fairly and in full. */
	public function test_every_rebuttal_states_its_claim() {
		foreach ( murtadd_seed_rebuttals() as $rebuttal ) {
			$this->assertNotEmpty( $rebuttal['claim'], "Rebuttal '{$rebuttal['slug']}' states no claim." );
		}
	}

	/** Doubts are short by design. The cap is the promise the reader is given. */
	public function test_doubt_responses_respect_the_word_cap() {
		foreach ( murtadd_seed_doubts() as $doubt ) {
			$words = str_word_count( wp_strip_all_tags( $doubt['response'] ) );
			$this->assertLessThanOrEqual(
				450,
				$words,
				"Doubt '{$doubt['slug']}' runs to {$words} words. Short answers must stay short."
			);
			$this->assertGreaterThan( 100, $words, "Doubt '{$doubt['slug']}' is too thin to be an answer." );
		}
	}
}
