<?php
/**
 * CPT: Letter (murtadd_letter).
 *
 * Editorial correspondence — letters and opinion pieces published in outlets
 * elsewhere, archived here in their original form.
 *
 * This is deliberately walled off from the triage architecture. Doubts,
 * Rebuttals, and Fatwa entries exist to meet a reader who arrived carrying a
 * question; a letter is the author addressing an editor, at a fixed moment,
 * often years ago. The two do different jobs and must not be presented as
 * though they did the same one. Concretely, Letters:
 *
 *   - do not appear on the homepage triage grid;
 *   - do not carry a Doubt Category, so they cannot enter the four doors;
 *   - are not offered as an answer to anybody's doubt;
 *   - carry a mandatory dateline, because a position taken in 2004 is a
 *     historical artefact and must read as one.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the post type.
 */
function murtadd_register_cpt_letter() {
	register_post_type(
		'murtadd_letter',
		array(
			'labels'       => array(
				'name'          => __( 'Letters', 'murtadd' ),
				'singular_name' => __( 'Letter', 'murtadd' ),
				'add_new_item'  => __( 'Add New Letter', 'murtadd' ),
				'edit_item'     => __( 'Edit Letter', 'murtadd' ),
			),
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'letters' ),
			'menu_icon'    => 'dashicons-email-alt',
			'supports'     => array( 'title', 'editor', 'revisions' ),
			'show_in_rest' => true,
			'taxonomies'   => array( 'murtadd_topic' ),
		)
	);
}
add_action( 'init', 'murtadd_register_cpt_letter' );

/**
 * Meta box: where and when it ran, and how it reads now.
 */
function murtadd_letter_meta_box() {
	add_meta_box(
		'murtadd_letter_fields',
		__( 'Letter details', 'murtadd' ),
		'murtadd_letter_meta_box_render',
		'murtadd_letter',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'murtadd_letter_meta_box' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post Post object.
 */
function murtadd_letter_meta_box_render( $post ) {
	wp_nonce_field( 'murtadd_letter_save', 'murtadd_letter_nonce' );

	$outlet   = get_post_meta( $post->ID, '_murtadd_letter_outlet', true );
	$date     = get_post_meta( $post->ID, '_murtadd_letter_date', true );
	$url      = get_post_meta( $post->ID, '_murtadd_letter_url', true );
	$note     = get_post_meta( $post->ID, '_murtadd_letter_note', true );
	$replying = get_post_meta( $post->ID, '_murtadd_letter_replying_to', true );
	?>
	<p>
		<label for="murtadd_letter_outlet"><strong><?php esc_html_e( 'Published in', 'murtadd' ); ?></strong></label><br>
		<input type="text" class="widefat" id="murtadd_letter_outlet" name="murtadd_letter_outlet" value="<?php echo esc_attr( $outlet ); ?>" placeholder="<?php esc_attr_e( 'e.g. Malaysiakini', 'murtadd' ); ?>">
	</p>
	<p>
		<label for="murtadd_letter_date"><strong><?php esc_html_e( 'Date of original publication', 'murtadd' ); ?></strong></label><br>
		<input type="date" id="murtadd_letter_date" name="murtadd_letter_date" value="<?php echo esc_attr( $date ); ?>">
		<span class="description"><?php esc_html_e( 'Required. The dateline is printed at the head of the letter: an archived position must read as one.', 'murtadd' ); ?></span>
	</p>
	<p>
		<label for="murtadd_letter_replying_to"><strong><?php esc_html_e( 'Replying to', 'murtadd' ); ?></strong></label><br>
		<input type="text" class="widefat" id="murtadd_letter_replying_to" name="murtadd_letter_replying_to" value="<?php echo esc_attr( $replying ); ?>" placeholder="<?php esc_attr_e( 'Title of the letter or article being answered', 'murtadd' ); ?>">
	</p>
	<p>
		<label for="murtadd_letter_url"><strong><?php esc_html_e( 'Link to the original', 'murtadd' ); ?></strong></label><br>
		<input type="url" class="widefat" id="murtadd_letter_url" name="murtadd_letter_url" value="<?php echo esc_url( $url ); ?>" placeholder="https://">
	</p>
	<p>
		<label for="murtadd_letter_note"><strong><?php esc_html_e( 'Editor’s note (optional)', 'murtadd' ); ?></strong></label><br>
		<textarea class="widefat" rows="4" id="murtadd_letter_note" name="murtadd_letter_note" placeholder="<?php esc_attr_e( 'Standing context, or where the position has since developed. Rendered above the letter, in the site’s present voice.', 'murtadd' ); ?>"><?php echo esc_textarea( $note ); ?></textarea>
		<span class="description"><?php esc_html_e( 'Use this where an archived position needs framing for a reader who arrives at it today.', 'murtadd' ); ?></span>
	</p>
	<?php
}

/**
 * Save the meta.
 *
 * @param int $post_id Post ID.
 */
function murtadd_letter_save( $post_id ) {
	if ( ! isset( $_POST['murtadd_letter_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['murtadd_letter_nonce'] ) ), 'murtadd_letter_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_murtadd_letter_outlet'      => 'sanitize_text_field',
		'_murtadd_letter_date'        => 'sanitize_text_field',
		'_murtadd_letter_replying_to' => 'sanitize_text_field',
		'_murtadd_letter_url'         => 'esc_url_raw',
		'_murtadd_letter_note'        => 'wp_kses_post',
	);

	foreach ( $fields as $key => $sanitizer ) {
		$field = str_replace( '_murtadd_letter_', 'murtadd_letter_', $key );
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $key, call_user_func( $sanitizer, wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}
add_action( 'save_post_murtadd_letter', 'murtadd_letter_save' );

/**
 * Keep Letters out of the reader-in-distress path.
 *
 * The site search exists so that a person carrying a doubt finds the page
 * written to meet it. An archived letter to an editor is not that page, and
 * surfacing it there would answer a question nobody asked.
 *
 * Letters remain fully public, indexable, and reachable from their own archive.
 *
 * @param WP_Query $query The query.
 */
function murtadd_letters_out_of_search( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$types = get_post_types( array( 'public' => true ) );
	unset( $types['murtadd_letter'] );
	$query->set( 'post_type', array_values( $types ) );
}
add_action( 'pre_get_posts', 'murtadd_letters_out_of_search' );
