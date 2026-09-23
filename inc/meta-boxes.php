<?php
/**
 * Meta boxes — nonce-protected, sanitized on save.
 * Doubt: doubt_statement, short_response, related_rebuttal, external_deep_link.
 * Rebuttal: claim, claim_source_type, sources (repeater), related_fatwa, related_doubt.
 * Fatwa: scholar_or_body, era, position_summary, citation_link (required).
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

function murtadd_add_meta_boxes() {
	add_meta_box( 'murtadd_doubt_fields', __( 'Doubt fields', 'murtadd' ), 'murtadd_doubt_meta_box', 'murtadd_doubt', 'normal', 'high' );
	add_meta_box( 'murtadd_rebuttal_fields', __( 'Rebuttal fields', 'murtadd' ), 'murtadd_rebuttal_meta_box', 'murtadd_rebuttal', 'normal', 'high' );
	add_meta_box( 'murtadd_fatwa_fields', __( 'Fatwa fields', 'murtadd' ), 'murtadd_fatwa_meta_box', 'murtadd_fatwa', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'murtadd_add_meta_boxes' );

/** Small helper: post dropdown. */
function murtadd_post_select( $name, $post_type, $selected, $none_label ) {
	$posts = get_posts( array( 'post_type' => $post_type, 'numberposts' => 200, 'orderby' => 'title', 'order' => 'ASC' ) );
	echo '<select name="' . esc_attr( $name ) . '" id="' . esc_attr( $name ) . '">';
	echo '<option value="">' . esc_html( $none_label ) . '</option>';
	foreach ( $posts as $p ) {
		printf( '<option value="%d" %s>%s</option>', (int) $p->ID, selected( $selected, $p->ID, false ), esc_html( get_the_title( $p ) ) );
	}
	echo '</select>';
}

function murtadd_doubt_meta_box( $post ) {
	wp_nonce_field( 'murtadd_save_meta', 'murtadd_meta_nonce' );
	$statement = get_post_meta( $post->ID, '_murtadd_doubt_statement', true );
	$response  = get_post_meta( $post->ID, '_murtadd_short_response', true );
	$rebuttal  = (int) get_post_meta( $post->ID, '_murtadd_related_rebuttal', true );
	$deep_link = get_post_meta( $post->ID, '_murtadd_external_deep_link', true );
	?>
	<p><label for="murtadd_doubt_statement"><strong><?php esc_html_e( 'Doubt statement', 'murtadd' ); ?></strong> — <?php esc_html_e( 'shown as the page title block', 'murtadd' ); ?></label><br>
	<textarea name="murtadd_doubt_statement" id="murtadd_doubt_statement" rows="2" class="large-text"><?php echo esc_textarea( $statement ); ?></textarea></p>

	<p><label for="murtadd_short_response"><strong><?php esc_html_e( 'Short response', 'murtadd' ); ?></strong> — <?php esc_html_e( '150–400 words; live word count warns past the cap, never blocks', 'murtadd' ); ?></label><br>
	<textarea name="murtadd_short_response" id="murtadd_short_response" rows="10" class="large-text" data-murtadd-wordcount><?php echo esc_textarea( $response ); ?></textarea>
	<span class="murtadd-wordcount description" aria-live="polite"></span></p>

	<p><label for="murtadd_related_rebuttal"><strong><?php esc_html_e( 'Related rebuttal (primary "go deeper" target)', 'murtadd' ); ?></strong></label><br>
	<?php murtadd_post_select( 'murtadd_related_rebuttal', 'murtadd_rebuttal', $rebuttal, __( '— None —', 'murtadd' ) ); ?></p>

	<p><label for="murtadd_external_deep_link"><strong><?php esc_html_e( 'External deep link (optional, secondary)', 'murtadd' ); ?></strong></label><br>
	<input type="url" name="murtadd_external_deep_link" id="murtadd_external_deep_link" class="large-text" value="<?php echo esc_url( $deep_link ); ?>" placeholder="https://"></p>
	<?php
}

function murtadd_rebuttal_meta_box( $post ) {
	wp_nonce_field( 'murtadd_save_meta', 'murtadd_meta_nonce' );
	$claim       = get_post_meta( $post->ID, '_murtadd_claim', true );
	$source_type = get_post_meta( $post->ID, '_murtadd_claim_source_type', true );
	$sources     = get_post_meta( $post->ID, '_murtadd_sources', true );
	$ce_slug     = get_post_meta( $post->ID, '_murtadd_ce_slug', true );
	$fatwa       = (int) get_post_meta( $post->ID, '_murtadd_related_fatwa', true );
	$doubt       = (int) get_post_meta( $post->ID, '_murtadd_related_doubt', true );
	$types       = array(
		'online-commentary' => __( 'Online commentary', 'murtadd' ),
		'academic'          => __( 'Academic', 'murtadd' ),
		'forum'             => __( 'Forum', 'murtadd' ),
		'pamphlet'          => __( 'Pamphlet', 'murtadd' ),
		'other'             => __( 'Other', 'murtadd' ),
	);
	if ( ! is_array( $sources ) || ! $sources ) {
		$sources = array( array( 'citation_text' => '', 'url' => '' ) );
	}
	?>
	<p><label for="murtadd_claim"><strong><?php esc_html_e( 'The claim — stated fairly, as circulated. No named individuals.', 'murtadd' ); ?></strong></label><br>
	<textarea name="murtadd_claim" id="murtadd_claim" rows="3" class="large-text"><?php echo esc_textarea( $claim ); ?></textarea></p>

	<p><label for="murtadd_ce_slug"><strong><?php esc_html_e( 'Counterpart on the linked long-form site', 'murtadd' ); ?></strong></label><br>
	<input type="text" name="murtadd_ce_slug" id="murtadd_ce_slug" value="<?php echo esc_attr( $ce_slug ); ?>" class="regular-text" placeholder="aisha-age-marriage">
	<br><span class="description"><?php esc_html_e( 'Article slug only, no URL. Sharing a topic with the companion site is fine; sharing an argument is not. If the counterpart already makes a point, compress it to a sentence and spend this entry on what only this site can say: the specific charge, its primary evidence, the law across Muslim-majority states, the verdict. Leave blank if there is no counterpart.', 'murtadd' ); ?></span></p>

	<p><label for="murtadd_claim_source_type"><strong><?php esc_html_e( 'Claim source type', 'murtadd' ); ?></strong></label><br>
	<select name="murtadd_claim_source_type" id="murtadd_claim_source_type">
		<?php foreach ( $types as $val => $label ) : ?>
			<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $source_type, $val ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select></p>

	<p><strong><?php esc_html_e( 'Sources', 'murtadd' ); ?></strong> — <?php esc_html_e( 'citation text required per row; URL optional', 'murtadd' ); ?></p>
	<div id="murtadd-sources-repeater">
		<?php foreach ( $sources as $i => $row ) : ?>
			<p class="murtadd-source-row">
				<input type="text" name="murtadd_sources_citation[]" value="<?php echo esc_attr( $row['citation_text'] ?? '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Citation text', 'murtadd' ); ?>">
				<input type="url" name="murtadd_sources_url[]" value="<?php echo esc_url( $row['url'] ?? '' ); ?>" class="regular-text" placeholder="https:// (optional)">
				<button type="button" class="button murtadd-remove-source" aria-label="<?php esc_attr_e( 'Remove source', 'murtadd' ); ?>">&times;</button>
			</p>
		<?php endforeach; ?>
	</div>
	<p><button type="button" class="button" id="murtadd-add-source"><?php esc_html_e( 'Add source', 'murtadd' ); ?></button></p>

	<p><label for="murtadd_related_fatwa"><strong><?php esc_html_e( 'Related fatwa (optional)', 'murtadd' ); ?></strong></label><br>
	<?php murtadd_post_select( 'murtadd_related_fatwa', 'murtadd_fatwa', $fatwa, __( '— None —', 'murtadd' ) ); ?></p>

	<p><label for="murtadd_related_doubt"><strong><?php esc_html_e( 'Related doubt (back-link, optional)', 'murtadd' ); ?></strong></label><br>
	<?php murtadd_post_select( 'murtadd_related_doubt', 'murtadd_doubt', $doubt, __( '— None —', 'murtadd' ) ); ?></p>
	<?php
}

function murtadd_fatwa_meta_box( $post ) {
	wp_nonce_field( 'murtadd_save_meta', 'murtadd_meta_nonce' );
	$scholar  = get_post_meta( $post->ID, '_murtadd_scholar_or_body', true );
	$era      = get_post_meta( $post->ID, '_murtadd_era', true );
	$summary  = get_post_meta( $post->ID, '_murtadd_position_summary', true );
	$citation = get_post_meta( $post->ID, '_murtadd_citation_link', true );
	?>
	<p><label for="murtadd_scholar_or_body"><strong><?php esc_html_e( 'Scholar or body', 'murtadd' ); ?></strong></label><br>
	<input type="text" name="murtadd_scholar_or_body" id="murtadd_scholar_or_body" class="large-text" value="<?php echo esc_attr( $scholar ); ?>"></p>

	<p><label for="murtadd_era"><strong><?php esc_html_e( 'Era', 'murtadd' ); ?></strong></label><br>
	<select name="murtadd_era" id="murtadd_era">
		<option value="classical" <?php selected( $era, 'classical' ); ?>><?php esc_html_e( 'Classical', 'murtadd' ); ?></option>
		<option value="modern" <?php selected( $era, 'modern' ); ?>><?php esc_html_e( 'Modern', 'murtadd' ); ?></option>
	</select></p>

	<p><label for="murtadd_position_summary"><strong><?php esc_html_e( 'Position summary — own words, never verbatim; ~80 word soft cap', 'murtadd' ); ?></strong></label><br>
	<textarea name="murtadd_position_summary" id="murtadd_position_summary" rows="5" class="large-text"><?php echo esc_textarea( $summary ); ?></textarea></p>

	<p><label for="murtadd_citation_link"><strong><?php esc_html_e( 'Citation link to primary source (required)', 'murtadd' ); ?></strong></label><br>
	<input type="url" name="murtadd_citation_link" id="murtadd_citation_link" class="large-text" value="<?php echo esc_url( $citation ); ?>" placeholder="https://" required></p>
	<?php
}

/**
 * Save handler — one for all three CPTs.
 */
function murtadd_save_meta( $post_id ) {
	if ( ! isset( $_POST['murtadd_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['murtadd_meta_nonce'] ), 'murtadd_save_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$text_fields = array(
		'murtadd_doubt_statement'   => array( '_murtadd_doubt_statement', 'sanitize_textarea_field' ),
		'murtadd_short_response'    => array( '_murtadd_short_response', 'sanitize_textarea_field' ),
		'murtadd_claim'             => array( '_murtadd_claim', 'sanitize_textarea_field' ),
		'murtadd_ce_slug'           => array( '_murtadd_ce_slug', 'sanitize_title' ),
		'murtadd_position_summary'  => array( '_murtadd_position_summary', 'sanitize_textarea_field' ),
		'murtadd_scholar_or_body'   => array( '_murtadd_scholar_or_body', 'sanitize_text_field' ),
		'murtadd_external_deep_link'=> array( '_murtadd_external_deep_link', 'esc_url_raw' ),
		'murtadd_citation_link'     => array( '_murtadd_citation_link', 'esc_url_raw' ),
	);
	foreach ( $text_fields as $field => $conf ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $conf[0], call_user_func( $conf[1], wp_unslash( $_POST[ $field ] ) ) );
		}
	}

	if ( isset( $_POST['murtadd_claim_source_type'] ) ) {
		$allowed = array( 'online-commentary', 'academic', 'forum', 'pamphlet', 'other' );
		$val     = sanitize_key( wp_unslash( $_POST['murtadd_claim_source_type'] ) );
		update_post_meta( $post_id, '_murtadd_claim_source_type', in_array( $val, $allowed, true ) ? $val : 'other' );
	}
	if ( isset( $_POST['murtadd_era'] ) ) {
		$val = sanitize_key( wp_unslash( $_POST['murtadd_era'] ) );
		update_post_meta( $post_id, '_murtadd_era', in_array( $val, array( 'classical', 'modern' ), true ) ? $val : 'classical' );
	}
	foreach ( array( 'murtadd_related_rebuttal' => '_murtadd_related_rebuttal', 'murtadd_related_fatwa' => '_murtadd_related_fatwa', 'murtadd_related_doubt' => '_murtadd_related_doubt' ) as $field => $key ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $key, absint( $_POST[ $field ] ) );
		}
	}

	if ( isset( $_POST['murtadd_sources_citation'] ) && is_array( $_POST['murtadd_sources_citation'] ) ) {
		$citations = array_map( 'sanitize_text_field', wp_unslash( $_POST['murtadd_sources_citation'] ) );
		$urls      = isset( $_POST['murtadd_sources_url'] ) ? array_map( 'esc_url_raw', wp_unslash( (array) $_POST['murtadd_sources_url'] ) ) : array();
		$rows      = array();
		foreach ( $citations as $i => $citation ) {
			if ( '' === trim( $citation ) ) {
				continue;
			}
			$rows[] = array(
				'citation_text' => $citation,
				'url'           => $urls[ $i ] ?? '',
			);
		}
		update_post_meta( $post_id, '_murtadd_sources', $rows );
	}
}
add_action( 'save_post', 'murtadd_save_meta' );
