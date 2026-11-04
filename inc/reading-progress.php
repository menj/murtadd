<?php
/**
 * Reading progress bar.
 *
 * Rebuttals have carried a progress bar since the long-form furniture
 * arrived (assets/js/longform.js). It now lives here, on its own, and the
 * Content tab of Theme Options sets it: on or off, the colour, the height,
 * the top or bottom edge, and the post types it runs on.
 *
 * Doubts never get it. They are capped at a few hundred words by design, and
 * a progress bar would advertise a length they must never have, so the type
 * is not offered and is refused if it is ever saved.
 *
 * The bar keeps the long-form bar's role="progressbar", labelled, with
 * aria-valuemin, aria-valuemax and aria-valuenow. It cannot be focused, so
 * it is never announced while someone scrolls, and a screen-reader user who
 * reaches it by reading hears how far through the entry they are.
 *
 * Adapted, for the options, from the Reading Progress Bar plugin by
 * Jean-Baptiste Audras (GPLv2). The theme stands aside while that plugin is
 * active, so a page never shows two bars.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Post types the bar may run on: the public types, Doubts excepted.
 *
 * @return string[]
 */
function murtadd_progress_allowed_types() {
	$types = array_values( get_post_types( array( 'public' => true ), 'names' ) );

	return array_values( array_diff( $types, array( 'murtadd_doubt', 'attachment' ) ) );
}

/**
 * The bar's settings, from murtadd_content_display, with defaults.
 *
 * @return array{on:int,colour:string,height:int,position:string,types:string[]}
 */
function murtadd_progress_settings() {
	$o = get_option( 'murtadd_content_display', array() );
	$o = is_array( $o ) ? $o : array();

	$types = isset( $o['progress_types'] ) && is_array( $o['progress_types'] )
		? $o['progress_types']
		: array( 'murtadd_rebuttal', 'murtadd_guide', 'post', 'page' );

	return array(
		'on'       => isset( $o['progress_bar'] ) ? ( $o['progress_bar'] ? 1 : 0 ) : 1,
		'colour'   => (string) ( sanitize_hex_color( $o['progress_colour'] ?? '' ) ?: '' ),
		'height'   => max( 2, min( 12, (int) ( $o['progress_height'] ?? 3 ) ) ),
		'position' => 'bottom' === ( $o['progress_position'] ?? 'top' ) ? 'bottom' : 'top',
		'types'    => array_values( array_diff( array_map( 'sanitize_key', $types ), array( 'murtadd_doubt' ) ) ),
	);
}

/**
 * Whether the current view shows the bar.
 *
 * @return bool
 */
function murtadd_progress_applies() {
	if ( function_exists( 'rp_show_it' ) || is_admin() || is_front_page() || ! is_singular() ) {
		return false;
	}

	$settings = murtadd_progress_settings();
	$type     = get_post_type( get_queried_object_id() );

	return $settings['on'] && 'murtadd_doubt' !== $type && in_array( $type, $settings['types'], true );
}

/**
 * Loads the bar's stylesheet and script on the views that show it.
 *
 * @return void
 */
function murtadd_progress_assets() {
	if ( ! murtadd_progress_applies() ) {
		return;
	}

	$settings = murtadd_progress_settings();
	$uri      = get_stylesheet_directory_uri();

	wp_enqueue_style( 'murtadd-progress', $uri . '/assets/css/progress' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );

	$css = '--murtadd-progress-height:' . $settings['height'] . 'px;';

	if ( '' !== $settings['colour'] ) {
		$css .= '--murtadd-progress-colour:' . $settings['colour'] . ';';
	}

	wp_add_inline_style( 'murtadd-progress', '.murtadd-progress{' . $css . '}' );

	wp_enqueue_script( 'murtadd-progress', $uri . '/assets/js/progress' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );
	wp_localize_script(
		'murtadd-progress',
		'murtaddProgress',
		array(
			'label'    => __( 'Reading progress', 'murtadd' ),
			'position' => $settings['position'],
		)
	);
}
add_action( 'wp_enqueue_scripts', 'murtadd_progress_assets', 20 );

/**
 * Sanitises the bar's fields from the Content tab into the content display
 * array.
 *
 * @param array $input Raw input.
 * @return array
 */
function murtadd_sanitize_progress( $input ) {
	$types = isset( $input['progress_types'] ) ? array_map( 'sanitize_key', (array) $input['progress_types'] ) : array();

	return array(
		'progress_bar'      => empty( $input['progress_bar'] ) ? 0 : 1,
		'progress_colour'   => (string) ( sanitize_hex_color( $input['progress_colour'] ?? '' ) ?: '' ),
		'progress_height'   => max( 2, min( 12, (int) ( $input['progress_height'] ?? 3 ) ) ),
		'progress_position' => 'bottom' === ( $input['progress_position'] ?? 'top' ) ? 'bottom' : 'top',
		'progress_types'    => array_values( array_intersect( $types, murtadd_progress_allowed_types() ) ),
	);
}

/**
 * The Reading progress card on the Content tab.
 *
 * @return void
 */
function murtadd_render_progress_card() {
	murtadd_admin_card_open( __( 'Reading progress bar', 'murtadd' ), __( 'A thin bar that fills as the reader moves through an entry. Never on doubts: they are short by design.', 'murtadd' ), 'chart-bar' );

	if ( function_exists( 'rp_show_it' ) ) {
		echo '<p class="description">' . esc_html__( 'The Reading Progress Bar plugin is active, so the theme leaves the bar to it. Deactivate the plugin to set the bar here.', 'murtadd' ) . '</p>';
		murtadd_admin_card_close();
		return;
	}

	$s = murtadd_progress_settings();

	murtadd_admin_toggle_row(
		__( 'Progress bar', 'murtadd' ),
		'murtadd_progress_bar',
		'murtadd_content_display[progress_bar]',
		(bool) $s['on'],
		__( 'Show the bar on the entry types ticked below', 'murtadd' )
	);

	murtadd_admin_row_open( __( 'Height', 'murtadd' ), 'murtadd_progress_height' );
	murtadd_admin_range( 'murtadd_progress_height', 'murtadd_content_display[progress_height]', $s['height'], 2, 12, __( 'pixels', 'murtadd' ) );
	murtadd_admin_row_close();
	?>
	<div class="murtadd-color-grid">
		<div class="murtadd-color">
			<label class="murtadd-color__label" for="murtadd_progress_colour"><?php esc_html_e( 'Bar colour', 'murtadd' ); ?></label>
			<input type="text" id="murtadd_progress_colour" name="murtadd_content_display[progress_colour]" value="<?php echo esc_attr( $s['colour'] ); ?>" class="murtadd-color-field" data-default-color="" aria-describedby="murtadd_progress_colour-desc">
			<p class="description" id="murtadd_progress_colour-desc"><?php esc_html_e( 'Leave empty to use the accent colour from the Colours tab.', 'murtadd' ); ?></p>
		</div>
	</div>
	<fieldset class="murtadd-choices">
		<legend class="screen-reader-text"><?php esc_html_e( 'Edge of the window', 'murtadd' ); ?></legend>
		<?php
		$edges = array(
			'top'    => array( __( 'Top', 'murtadd' ), __( 'Along the top, below the admin bar.', 'murtadd' ), 'arrow-up-alt' ),
			'bottom' => array( __( 'Bottom', 'murtadd' ), __( 'Along the bottom of the window.', 'murtadd' ), 'arrow-down-alt' ),
		);
		foreach ( $edges as $value => $edge ) :
			?>
			<label class="murtadd-choice" for="murtadd_progress_<?php echo esc_attr( $value ); ?>">
				<input type="radio" class="murtadd-choice__input" id="murtadd_progress_<?php echo esc_attr( $value ); ?>" name="murtadd_content_display[progress_position]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $s['position'], $value ); ?>>
				<span class="murtadd-choice__body">
					<span class="murtadd-choice__icon dashicons dashicons-<?php echo esc_attr( $edge[2] ); ?>" aria-hidden="true"></span>
					<span class="murtadd-choice__title"><?php echo esc_html( $edge[0] ); ?></span>
					<span class="murtadd-choice__desc"><?php echo esc_html( $edge[1] ); ?></span>
				</span>
			</label>
		<?php endforeach; ?>
	</fieldset>
	<?php
	murtadd_admin_row_open( __( 'Entry types', 'murtadd' ), 'murtadd_progress_type_first' );
	$first = true;
	foreach ( murtadd_progress_allowed_types() as $type ) {
		$object = get_post_type_object( $type );
		printf(
			'<p><label><input type="checkbox"%1$s name="murtadd_content_display[progress_types][]" value="%2$s"%3$s> %4$s</label></p>',
			$first ? ' id="murtadd_progress_type_first"' : '',
			esc_attr( $type ),
			checked( in_array( $type, $s['types'], true ), true, false ),
			esc_html( $object ? $object->labels->name : $type )
		);
		$first = false;
	}
	murtadd_admin_row_close();
	murtadd_admin_card_close();
}
