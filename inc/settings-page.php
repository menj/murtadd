<?php
/**
 * Settings page — Appearance → Theme Options. Settings API, tabbed.
 * One option array per tab: murtadd_colours, murtadd_cross_links, murtadd_contextual_links, murtadd_content_display, murtadd_security, murtadd_upkeep.
 * Taxonomies tab deep-links to native term screens (no duplicate CRUD).
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The Theme Options screen's hook suffix, as add_theme_page() returned it.
 * Admin assets compare against this, so they load on this screen alone.
 *
 * @param string|null $set Store the suffix (called once, from the menu hook).
 * @return string
 */
function murtadd_settings_hook( $set = null ) {
	static $hook = 'appearance_page_murtadd';
	if ( is_string( $set ) && '' !== $set ) {
		$hook = $set;
	}
	return $hook;
}

/*
 * The screen stays on manage_options rather than edit_theme_options. The
 * Security tab writes .htaccess, revokes application passwords and moves the
 * login screen, and options.php saves these groups under manage_options, so
 * the narrower capability is the one that matches what the screen does.
 */
function murtadd_add_settings_page() {
	$hook = add_theme_page( __( 'Theme Options', 'murtadd' ), __( 'Theme Options', 'murtadd' ), 'manage_options', 'murtadd', 'murtadd_render_settings_page' );
	if ( $hook ) {
		murtadd_settings_hook( $hook );
	}
}
add_action( 'admin_menu', 'murtadd_add_settings_page' );

function murtadd_register_settings() {
	register_setting( 'murtadd_colours', 'murtadd_colours', array( 'sanitize_callback' => 'murtadd_sanitize_colours' ) );
	register_setting( 'murtadd_cross_links', 'murtadd_cross_links', array( 'sanitize_callback' => 'murtadd_sanitize_cross_links' ) );
	register_setting( 'murtadd_contextual_links', 'murtadd_contextual_links', array( 'sanitize_callback' => 'murtadd_sanitize_contextual_links' ) );
	register_setting( 'murtadd_content_display', 'murtadd_content_display', array( 'sanitize_callback' => 'murtadd_sanitize_content_display' ) );
	register_setting( 'murtadd_content_display', 'murtadd_search', array( 'sanitize_callback' => 'murtadd_sanitize_search' ) );
	register_setting( 'murtadd_profiles', 'murtadd_profiles', array( 'sanitize_callback' => 'murtadd_sanitize_profiles' ) );
	register_setting( 'murtadd_profiles', 'murtadd_author_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
	register_setting( 'murtadd_security', 'murtadd_security', array( 'sanitize_callback' => 'murtadd_sanitize_security' ) );
	register_setting( 'murtadd_upkeep', 'murtadd_upkeep', array( 'sanitize_callback' => 'murtadd_sanitize_upkeep' ) );
}
add_action( 'admin_init', 'murtadd_register_settings' );

function murtadd_sanitize_colours( $input ) {
	return array(
		'accent'      => sanitize_hex_color( $input['accent'] ?? '' ) ?: '#0f6e56',
		'accent_tint' => sanitize_hex_color( $input['accent_tint'] ?? '' ) ?: '#eefaf7',
	);
}

function murtadd_sanitize_cross_links( $input ) {
	return array(
		'ce_base_url'      => esc_url_raw( $input['ce_base_url'] ?? '' ),
		'bismika_base_url' => esc_url_raw( $input['bismika_base_url'] ?? '' ),
		'show_counterpart' => empty( $input['show_counterpart'] ) ? '0' : '1',
		'show_quiz_card'   => empty( $input['show_quiz_card'] ) ? '0' : '1',
	);
}

function murtadd_sanitize_contextual_links( $input ) {
	$input = is_array( $input ) ? $input : array();
	$extra = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) ( $input['extra'] ?? '' ) ) as $line ) {
		// The phrase and the address are cleaned apart: sanitize_text_field()
		// strips every percent-encoded octet, which would break an encoded path.
		$parts = explode( '|', (string) $line, 2 );
		$line  = 2 === count( $parts ) ? sanitize_text_field( $parts[0] ) . ' | ' . esc_url_raw( trim( wp_check_invalid_utf8( $parts[1] ) ) ) : '';
		if ( '' !== $line && murtadd_ctx_parse_line( $line ) ) {
			$extra[] = $line;
		}
	}
	$excluded = array_filter( array_map( 'sanitize_title', preg_split( '/\r\n|\r|\n/', (string) ( $input['excluded'] ?? '' ) ) ) );
	return array(
		'own_enabled'    => empty( $input['own_enabled'] ) ? 0 : 1,
		'sister_enabled' => empty( $input['sister_enabled'] ) ? 0 : 1,
		'own_max'        => max( 0, min( 15, absint( $input['own_max'] ?? 4 ) ) ),
		'sister_max'     => max( 0, min( 10, absint( $input['sister_max'] ?? 3 ) ) ),
		'extra'          => implode( "\n", $extra ),
		'excluded'       => implode( "\n", array_unique( $excluded ) ),
	);
}

/**
 * Security tab. A login address that is reserved, malformed or already a
 * page's address is refused with a notice, and the saved one is kept, so a
 * typo can never move the login screen somewhere nobody knows.
 *
 * @param mixed $input Submitted values.
 * @return array
 */
function murtadd_sanitize_security( $input ) {
	$input = is_array( $input ) ? $input : array();
	$slug  = sanitize_title( (string) ( $input['login_slug'] ?? '' ) );
	if ( '' !== $slug && ( ! murtadd_login_slug_valid( $slug ) || get_page_by_path( $slug ) ) ) {
		add_settings_error( 'murtadd_security', 'murtadd-login-slug', __( 'That login address is reserved, malformed, or already the address of a page. It was not saved; the previous one is kept.', 'murtadd' ) );
		$slug = (string) murtadd_security( 'login_slug' );
	}
	$message = sanitize_textarea_field( (string) ( $input['login_message'] ?? '' ) );
	// PCRE in UTF-8 mode counts characters with or without mbstring.
	if ( preg_match( '/^.{0,500}/su', $message, $murtadd_cut ) ) {
		$message = $murtadd_cut[0];
	}
	$tagline = sanitize_text_field( (string) ( $input['login_tagline'] ?? '' ) );
	if ( preg_match( '/^.{0,160}/su', $tagline, $murtadd_cut ) ) {
		$tagline = $murtadd_cut[0];
	}
	return array(
		'hide_authors'  => empty( $input['hide_authors'] ) ? 0 : 1,
		'xmlrpc_off'    => empty( $input['xmlrpc_off'] ) ? 0 : 1,
		'app_passwords_off' => empty( $input['app_passwords_off'] ) ? 0 : 1,
		'rest_lock'     => empty( $input['rest_lock'] ) ? 0 : 1,
		'spam_gate'     => empty( $input['spam_gate'] ) ? 0 : 1,
		'spam_gone'     => empty( $input['spam_gone'] ) ? 0 : 1,
		'headers'       => empty( $input['headers'] ) ? 0 : 1,
		'login_slug'    => $slug,
		'login_design'  => empty( $input['login_design'] ) ? 0 : 1,
		'login_message' => $message,
		'login_tagline' => $tagline,
		'login_privacy' => empty( $input['login_privacy'] ) ? 0 : 1,
	);
}

/**
 * Upkeep tab.
 *
 * @param mixed $input Submitted values.
 * @return array
 */
function murtadd_sanitize_upkeep( $input ) {
	$input = is_array( $input ) ? $input : array();
	return array(
		'gone_paths'   => murtadd_gone_clean( (string) ( $input['gone_paths'] ?? '' ) ),
		'gone_presets' => empty( $input['gone_presets'] ) ? 0 : 1,
		'maintenance'  => empty( $input['maintenance'] ) ? 0 : 1,
		'error_pages'  => empty( $input['error_pages'] ) ? 0 : 1,
	);
}

function murtadd_sanitize_profiles( $input ) {
	$out = array();
	if ( ! is_array( $input ) ) {
		return $out;
	}
	foreach ( array_keys( murtadd_social_registry() ) as $slug ) {
		$url = isset( $input[ $slug ] ) ? esc_url_raw( trim( $input[ $slug ] ) ) : '';
		if ( $url ) {
			$out[ $slug ] = $url;
		}
	}
	return $out;
}

function murtadd_sanitize_content_display( $input ) {
	$sorts = array( 'scholar_az', 'era', 'recently_added' );
	$sort  = sanitize_key( $input['fatwa_index_default_sort'] ?? 'scholar_az' );
	$input = is_array( $input ) ? $input : array();
	return murtadd_sanitize_progress( $input ) + array(
		'short_response_word_cap'  => max( 50, absint( $input['short_response_word_cap'] ?? 400 ) ),
		'show_struggling_notice'   => ! empty( $input['show_struggling_notice'] ) ? 1 : 0,
		'show_breadcrumbs'         => ! empty( $input['show_breadcrumbs'] ) ? 1 : 0,
		'glossary_terms'           => empty( $input['glossary_terms'] ) ? 0 : 1,
		'fatwa_index_default_sort' => in_array( $sort, $sorts, true ) ? $sort : 'scholar_az',
		'footer_heritage_note'     => sanitize_text_field( $input['footer_heritage_note'] ?? 'online since 2004' ),
		'show_scripture'           => empty( $input['show_scripture'] ) ? 0 : 1,
		'scripture_text'           => sanitize_text_field( $input['scripture_text'] ?? '' ),
		'scripture_ref'            => sanitize_text_field( $input['scripture_ref'] ?? '' ),
		'contact_recipient'        => murtadd_contact_clean_recipient( $input['contact_recipient'] ?? '' ),
	);
}


/* -------------------------------------------------------------------------
 * Theme Options screen.
 *
 * Every tab is rendered into its own tab panel and the script switches
 * between them without a reload. Without the script the tab links still work:
 * each one is a plain ?tab= address and the server shows that panel alone.
 *
 * Each settings tab keeps its own form posting to options.php with its own
 * settings group, so the option keys, the sanitisers and the capability
 * options.php checks are exactly as before. Only the markup changed: cards
 * and field rows in place of form-table.
 * ---------------------------------------------------------------------- */

/**
 * The tabs, in the order the work is done. The KEYS are load-bearing: a
 * settings tab saves to the group 'murtadd_' . $key. Relabel and reorder
 * freely; never rename a key. 'setup', 'taxonomies' and 'docs' register no
 * group and render their own markup.
 *
 * @return array<string,array{label:string,icon:string}>
 */
function murtadd_settings_tabs() {
	return array(
		'setup'            => array( 'label' => __( 'Setup', 'murtadd' ), 'icon' => 'admin-tools' ),
		'content_display'  => array( 'label' => __( 'Content', 'murtadd' ), 'icon' => 'text-page' ),
		'taxonomies'       => array( 'label' => __( 'Topics & schools', 'murtadd' ), 'icon' => 'tag' ),
		'cross_links'      => array( 'label' => __( 'Linked sites', 'murtadd' ), 'icon' => 'admin-links' ),
		'contextual_links' => array( 'label' => __( 'Contextual links', 'murtadd' ), 'icon' => 'paperclip' ),
		'colours'          => array( 'label' => __( 'Colours', 'murtadd' ), 'icon' => 'art' ),
		'profiles'         => array( 'label' => __( 'Profiles', 'murtadd' ), 'icon' => 'id' ),
		'security'         => array( 'label' => __( 'Security', 'murtadd' ), 'icon' => 'shield' ),
		'upkeep'           => array( 'label' => __( 'Upkeep', 'murtadd' ), 'icon' => 'hammer' ),
		'docs'             => array( 'label' => __( 'Docs', 'murtadd' ), 'icon' => 'book' ),
	);
}

/**
 * The address of one tab.
 *
 * @param string $tab  Tab key.
 * @param array  $args Extra query arguments.
 * @return string
 */
function murtadd_settings_tab_url( $tab, $args = array() ) {
	return add_query_arg( array_merge( array( 'page' => 'murtadd', 'tab' => $tab ), $args ), admin_url( 'themes.php' ) );
}

/**
 * The hidden fields settings_fields() prints, for one tab's form.
 *
 * settings_fields() gives every form the ids "_wpnonce" and "_wp_http_referer"
 * and sends the browser back to the address the page was loaded at. With all
 * panels on one page that would repeat the ids and return a save to the wrong
 * tab, so the same four fields are printed here without ids, with the tab's
 * own address as the referer. The nonce action is the one options.php checks.
 *
 * @param string $tab Tab key; its group is 'murtadd_' . $tab.
 */
function murtadd_settings_form_fields( $tab ) {
	$group = 'murtadd_' . $tab;
	printf( '<input type="hidden" name="option_page" value="%s">', esc_attr( $group ) );
	echo '<input type="hidden" name="action" value="update">';
	printf( '<input type="hidden" name="_wpnonce" value="%s">', esc_attr( wp_create_nonce( $group . '-options' ) ) );
	printf( '<input type="hidden" name="_wp_http_referer" value="%s">', esc_attr( murtadd_settings_tab_url( $tab ) ) );
}

/**
 * Open a card.
 *
 * @param string $title       Heading.
 * @param string $description Optional line under the heading.
 * @param string $icon        Optional dashicon name.
 */
function murtadd_admin_card_open( $title, $description = '', $icon = '' ) {
	echo '<section class="murtadd-card">';
	echo '<header class="murtadd-card__head">';
	if ( $icon ) {
		printf( '<span class="murtadd-card__icon dashicons dashicons-%s" aria-hidden="true"></span>', esc_attr( $icon ) );
	}
	echo '<div class="murtadd-card__heading">';
	printf( '<h2 class="murtadd-card__title">%s</h2>', esc_html( $title ) );
	if ( '' !== $description ) {
		printf( '<p class="murtadd-card__desc">%s</p>', esc_html( $description ) );
	}
	echo '</div></header><div class="murtadd-card__body">';
}

/** Close a card. */
function murtadd_admin_card_close() {
	echo '</div></section>';
}

/**
 * Open a field row: the label on the left, the control on the right.
 *
 * @param string $label Row label.
 * @param string $for   Id of the control the label names.
 */
function murtadd_admin_row_open( $label, $for ) {
	echo '<div class="murtadd-field">';
	printf( '<div class="murtadd-field__label"><label for="%s">%s</label></div>', esc_attr( $for ), esc_html( $label ) );
	echo '<div class="murtadd-field__control">';
}

/** Close a field row. */
function murtadd_admin_row_close() {
	echo '</div></div>';
}

/**
 * A toggle switch for a boolean: a real checkbox with role="switch", so it
 * posts "1" as the old checkbox did and needs no script to work.
 *
 * @param string $id      Input id.
 * @param string $name    Input name, e.g. murtadd_security[headers].
 * @param bool   $checked Whether it is on.
 * @param string $text    What switching it on does.
 * @param string $desc    Optional help text, tied by aria-describedby.
 */
function murtadd_admin_toggle( $id, $name, $checked, $text, $desc = '' ) {
	$described = '' !== $desc ? ' aria-describedby="' . esc_attr( $id . '-desc' ) . '"' : '';
	echo '<div class="murtadd-toggle-row">';
	printf(
		'<span class="murtadd-toggle"><input type="checkbox" class="murtadd-toggle__input" role="switch" id="%1$s" name="%2$s" value="1"%3$s%4$s><span class="murtadd-toggle__track" aria-hidden="true"></span></span>',
		esc_attr( $id ),
		esc_attr( $name ),
		checked( (bool) $checked, true, false ),
		$described // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	);
	printf( '<label class="murtadd-toggle__text" for="%s">%s</label>', esc_attr( $id ), esc_html( $text ) );
	echo '</div>';
	if ( '' !== $desc ) {
		printf( '<p class="description" id="%s">%s</p>', esc_attr( $id . '-desc' ), esc_html( $desc ) );
	}
}

/**
 * A toggle in a row of its own.
 *
 * @param string $label   Row label.
 * @param string $id      Input id.
 * @param string $name    Input name.
 * @param bool   $checked Whether it is on.
 * @param string $text    What switching it on does.
 * @param string $desc    Optional help text.
 */
function murtadd_admin_toggle_row( $label, $id, $name, $checked, $text, $desc = '' ) {
	murtadd_admin_row_open( $label, $id );
	murtadd_admin_toggle( $id, $name, $checked, $text, $desc );
	murtadd_admin_row_close();
}

/**
 * A range slider that shows its value as it moves.
 *
 * @param string $id    Input id.
 * @param string $name  Input name.
 * @param int    $value Current value.
 * @param int    $min   Lowest value.
 * @param int    $max   Highest value.
 * @param string $unit  Words after the number, e.g. "a page".
 */
function murtadd_admin_range( $id, $name, $value, $min, $max, $unit ) {
	$value = max( $min, min( $max, (int) $value ) );
	printf(
		'<div class="murtadd-range"><input type="range" class="murtadd-range__input" id="%1$s" name="%2$s" value="%3$d" min="%4$d" max="%5$d" step="1" data-murtadd-range="%1$s-value"><output class="murtadd-range__value" id="%1$s-value" for="%1$s">%3$d</output> <span class="murtadd-range__unit">%6$s</span></div>',
		esc_attr( $id ),
		esc_attr( $name ),
		(int) $value,
		(int) $min,
		(int) $max,
		esc_html( $unit )
	);
}

/**
 * Render Appearance → Theme Options.
 */
function murtadd_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$tabs      = murtadd_settings_tabs();
	$requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- tab selection only.
	$active    = isset( $tabs[ $requested ] ) ? $requested : (string) array_key_first( $tabs );

	$murtadd_doubts    = (int) wp_count_posts( 'murtadd_doubt' )->publish;
	$murtadd_rebuttals = (int) wp_count_posts( 'murtadd_rebuttal' )->publish;
	$murtadd_gaps      = count( murtadd_content_gaps() );

	// The site icon: core's, if one is set; otherwise the theme's own mark.
	$murtadd_icon = get_site_icon_url( 96 );
	if ( ! $murtadd_icon ) {
		$murtadd_icon = get_stylesheet_directory_uri() . '/assets/icons/site-icon.svg';
	}
	?>
	<div class="wrap murtadd-admin">
		<header class="murtadd-admin-hero">
			<div class="murtadd-admin-hero-inner">
				<div class="murtadd-admin-brand">
					<img class="murtadd-admin-icon" src="<?php echo esc_url( $murtadd_icon ); ?>" alt="" width="52" height="52">
					<?php
					// The SAME component the site renders. Not a copy: a copy is how the
					// footer mark drifted, and how this hero drifted before it.
					murtadd_the_logo( 'on-dark', false );
					?>
					<span class="murtadd-admin-version">v<?php echo esc_html( MURTADD_VERSION ); ?></span>
				</div>
				<h1 class="murtadd-admin-title"><?php esc_html_e( 'Theme Options', 'murtadd' ); ?></h1>
				<p class="murtadd-admin-tagline"><?php esc_html_e( 'Doubts, taken seriously. Colours, cross-site links, content behaviour, security and upkeep.', 'murtadd' ); ?></p>
			</div>

			<dl class="murtadd-admin-stats">
				<div class="murtadd-admin-stat">
					<dt><?php esc_html_e( 'Doubts', 'murtadd' ); ?></dt>
					<dd><?php echo esc_html( number_format_i18n( $murtadd_doubts ) ); ?></dd>
				</div>
				<div class="murtadd-admin-stat">
					<dt><?php esc_html_e( 'Rebuttals', 'murtadd' ); ?></dt>
					<dd><?php echo esc_html( number_format_i18n( $murtadd_rebuttals ) ); ?></dd>
				</div>
				<div class="murtadd-admin-stat <?php echo $murtadd_gaps ? 'is-warning' : 'is-clear'; ?>">
					<dt><?php esc_html_e( 'Content gaps', 'murtadd' ); ?></dt>
					<dd><?php echo esc_html( $murtadd_gaps ? number_format_i18n( $murtadd_gaps ) : '—' ); ?></dd>
				</div>
			</dl>
		</header>
		<hr class="wp-header-end">
		<?php
		// A theme page gets no settings_errors() from core (only Settings
		// screens do), so "Settings saved" and a refused login address are
		// shown here.
		settings_errors();
		?>

		<nav class="murtadd-admin-tabs" aria-label="<?php esc_attr_e( 'Theme Options sections', 'murtadd' ); ?>">
			<div class="murtadd-admin-tablist" role="tablist" aria-label="<?php esc_attr_e( 'Theme Options sections', 'murtadd' ); ?>">
				<?php foreach ( $tabs as $slug => $tab ) : ?>
					<?php $is_active = $active === $slug; ?>
					<a
						href="<?php echo esc_url( murtadd_settings_tab_url( $slug ) ); ?>"
						class="murtadd-admin-tab<?php echo $is_active ? ' is-active' : ''; ?>"
						id="murtadd-tab-<?php echo esc_attr( $slug ); ?>"
						role="tab"
						aria-controls="murtadd-panel-<?php echo esc_attr( $slug ); ?>"
						aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
						tabindex="<?php echo $is_active ? '0' : '-1'; ?>"
						data-murtadd-tab="<?php echo esc_attr( $slug ); ?>"
					><span class="murtadd-admin-tab__icon dashicons dashicons-<?php echo esc_attr( $tab['icon'] ); ?>" aria-hidden="true"></span><span class="murtadd-admin-tab__label"><?php echo esc_html( $tab['label'] ); ?></span></a>
				<?php endforeach; ?>
			</div>
		</nav>

		<?php
		/*
		 * Only the active tab's panel is rendered here, so only its queries
		 * run (the Security tools and the Setup counts are the heavy ones).
		 * The other panels are empty shells: the script fetches a panel the
		 * first time its tab is chosen, and without the script each shell
		 * holds a link to its tab's own address.
		 */
		$murtadd_doc = isset( $_GET['doc'] ) ? sanitize_key( wp_unslash( $_GET['doc'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
		foreach ( $tabs as $slug => $tab ) {
			$is_active = $active === $slug;
			printf(
				'<section class="murtadd-admin-panel" id="murtadd-panel-%1$s" role="tabpanel" aria-labelledby="murtadd-tab-%1$s" tabindex="0"%2$s%3$s>',
				esc_attr( $slug ),
				$is_active ? '' : ' hidden',
				$is_active ? '' : ' data-murtadd-lazy="1"'
			);
			if ( $is_active ) {
				echo murtadd_settings_panel_html( $slug, $murtadd_doc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every part escaped where it was printed.
			} else {
				printf(
					'<p class="murtadd-admin-panel__loading"><a href="%s">%s</a></p>',
					esc_url( murtadd_settings_tab_url( $slug ) ),
					esc_html(
						sprintf(
							/* translators: %s: tab label */
							__( 'Open the %s tab', 'murtadd' ),
							$tab['label']
						)
					)
				);
			}
			echo '</section>';
		}
		?>

		<footer class="murtadd-admin-footer">
			<p>
				<a href="<?php echo esc_url( murtadd_settings_tab_url( 'docs' ) ); ?>" data-murtadd-tab-link="docs"><?php esc_html_e( 'Theme documentation', 'murtadd' ); ?></a>
				<span aria-hidden="true">·</span>
				<a href="<?php echo esc_url( murtadd_settings_tab_url( 'docs', array( 'doc' => 'settings' ) ) ); ?>"><?php esc_html_e( 'What each setting does', 'murtadd' ); ?></a>
				<span aria-hidden="true">·</span>
				<a href="<?php echo esc_url( murtadd_settings_tab_url( 'docs', array( 'doc' => 'setup' ) ) ); ?>"><?php esc_html_e( 'Setup guide', 'murtadd' ); ?></a>
			</p>
			<p class="murtadd-admin-footer__version">
				<?php
				/* translators: %s: theme version */
				printf( esc_html__( 'Murtadd %s', 'murtadd' ), esc_html( MURTADD_VERSION ) );
				?>
			</p>
		</footer>
	</div>
	<?php
}

/**
 * One panel's markup, as a string.
 *
 * The tool forms on the Setup, Security and Upkeep tabs print
 * wp_nonce_field() and submit_button(), which give every form the ids
 * "_wpnonce", "_wp_http_referer" and "submit". Panels loaded into the same
 * page would repeat them, and nothing refers to them, so they are dropped.
 * The fields keep their names, which is all the handlers read.
 *
 * @param string $slug Tab key.
 * @param string $doc  Docs tab: the document to show.
 * @return string
 */
function murtadd_settings_panel_html( $slug, $doc = '' ) {
	ob_start();
	murtadd_render_settings_panel( $slug, $doc );
	return (string) preg_replace( '/\sid="(?:_wpnonce|_wp_http_referer|submit)"/', '', (string) ob_get_clean() );
}

/**
 * AJAX: one inactive panel, the first time its tab is chosen.
 */
function murtadd_ajax_settings_panel() {
	check_ajax_referer( 'murtadd_settings_panel', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( null, 403 );
	}
	$slug = isset( $_POST['tab'] ) ? sanitize_key( wp_unslash( $_POST['tab'] ) ) : '';
	if ( ! isset( murtadd_settings_tabs()[ $slug ] ) ) {
		wp_send_json_error( null, 400 );
	}
	$doc = isset( $_POST['doc'] ) ? sanitize_key( wp_unslash( $_POST['doc'] ) ) : '';
	wp_send_json_success( array( 'html' => murtadd_settings_panel_html( $slug, $doc ) ) );
}
add_action( 'wp_ajax_murtadd_settings_panel', 'murtadd_ajax_settings_panel' );

/**
 * One tab's panel content.
 *
 * @param string $slug Tab key.
 * @param string $doc  Docs tab: the document to show.
 */
function murtadd_render_settings_panel( $slug, $doc = '' ) {
	switch ( $slug ) {
		case 'setup':
			murtadd_render_setup_tab();
			return;
		case 'taxonomies':
			murtadd_render_taxonomies_tab();
			return;
		case 'docs':
			murtadd_admin_card_open( __( 'Documentation', 'murtadd' ), __( 'The theme\'s own manual, rendered from the markdown that ships inside it. There is no second copy to fall out of date.', 'murtadd' ), 'book' );
			murtadd_docs_render_tab( sanitize_key( (string) $doc ) );
			murtadd_admin_card_close();
			return;
	}

	echo '<form method="post" action="' . esc_url( admin_url( 'options.php' ) ) . '" class="murtadd-admin-form">';
	murtadd_settings_form_fields( $slug );
	switch ( $slug ) {
		case 'content_display':
			murtadd_render_content_tab();
			break;
		case 'cross_links':
			murtadd_render_cross_links_tab();
			break;
		case 'contextual_links':
			murtadd_render_contextual_links_tab();
			break;
		case 'colours':
			murtadd_render_colours_tab();
			break;
		case 'profiles':
			murtadd_render_profiles_tab();
			break;
		case 'security':
			murtadd_render_security_tab();
			break;
		case 'upkeep':
			murtadd_render_upkeep_tab();
			break;
	}
	echo '<p class="murtadd-admin-actions"><button type="submit" class="button button-primary">' . esc_html__( 'Save Changes', 'murtadd' ) . '</button></p>';
	echo '</form>';

	if ( 'security' === $slug && function_exists( 'murtadd_sec_render_tools' ) ) {
		murtadd_sec_render_tools();
	}
	if ( 'upkeep' === $slug ) {
		murtadd_render_dropins_status();
	}
}

/**
 * The Setup tab: what the theme scaffolds, and a button to create anything missing.
 */
function murtadd_render_setup_tab() {
	if ( isset( $_GET['murtadd_done'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.
		?>
		<div class="notice notice-success is-dismissible inline"><p><?php esc_html_e( 'Setup complete.', 'murtadd' ); ?></p></div>
		<?php
	endif;

	murtadd_admin_card_open( __( 'Site scaffolding', 'murtadd' ), __( 'The theme creates these on activation. Upgrading the theme in place does not re-run that, so this button does it without switching themes away and back. It never overwrites: anything already present is left exactly as it is.', 'murtadd' ), 'admin-tools' );
	$murtadd_missing = murtadd_missing_pages();
	?>
	<table class="widefat striped murtadd-admin-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Page', 'murtadd' ); ?></th>
				<th scope="col"><?php esc_html_e( 'Status', 'murtadd' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( murtadd_required_pages() as $murtadd_slug => $murtadd_page ) : ?>
				<?php $murtadd_existing = get_page_by_path( $murtadd_slug ); ?>
				<tr>
					<td>
						<strong><?php echo esc_html( $murtadd_page['title'] ); ?></strong>
						<code><?php echo esc_html( $murtadd_slug ); ?></code>
					</td>
					<td>
						<?php if ( $murtadd_existing ) : ?>
							<a href="<?php echo esc_url( (string) get_edit_post_link( $murtadd_existing->ID ) ); ?>"><?php esc_html_e( 'Exists — edit', 'murtadd' ); ?></a>
						<?php else : ?>
							<span class="murtadd-status-missing"><?php esc_html_e( 'Missing', 'murtadd' ); ?></span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			<tr>
				<td><strong><?php esc_html_e( 'Footer menu', 'murtadd' ); ?></strong></td>
				<td>
					<?php if ( has_nav_menu( 'footer_site' ) ) : ?>
						<?php esc_html_e( 'Assigned', 'murtadd' ); ?>
					<?php else : ?>
						<span class="murtadd-status-missing"><?php esc_html_e( 'Not assigned', 'murtadd' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>
					<strong><?php esc_html_e( 'Starter content', 'murtadd' ); ?></strong>
					<code>
						<?php
						printf(
							/* translators: 1: doubt count, 2: rebuttal count */
							esc_html__( '%1$d doubts, %2$d rebuttals', 'murtadd' ),
							count( murtadd_seed_doubts() ),
							count( murtadd_seed_rebuttals() )
						);
						?>
					</code>
				</td>
				<td>
					<?php $murtadd_missing_content = murtadd_seed_missing_count(); ?>
					<?php if ( 0 === $murtadd_missing_content ) : ?>
						<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=murtadd_doubt' ) ); ?>"><?php esc_html_e( 'All present — view', 'murtadd' ); ?></a>
					<?php else : ?>
						<span class="murtadd-status-missing">
							<?php
							printf(
								/* translators: %d: number of missing entries */
								esc_html( _n( '%d entry missing', '%d entries missing', $murtadd_missing_content, 'murtadd' ) ),
								(int) $murtadd_missing_content
							);
							?>
						</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td><strong><?php esc_html_e( 'Reading settings', 'murtadd' ); ?></strong></td>
				<td>
					<?php if ( 'page' === get_option( 'show_on_front' ) && (int) get_option( 'page_for_posts' ) ) : ?>
						<?php esc_html_e( 'Front page and posts page are set', 'murtadd' ); ?>
					<?php else : ?>
						<span class="murtadd-status-missing"><?php esc_html_e( 'Not fully set', 'murtadd' ); ?></span>
					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>

	<?php if ( $murtadd_missing ) : ?>
		<p><?php esc_html_e( 'The "Start here" button in the sidebar renders only when the start-here page exists. That is why it is absent right now.', 'murtadd' ); ?></p>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="murtadd-admin-inline-form">
		<input type="hidden" name="action" value="murtadd_run_setup">
		<?php wp_nonce_field( 'murtadd_run_setup' ); ?>
		<?php submit_button( __( 'Create anything missing', 'murtadd' ), 'primary', 'submit', false ); ?>
	</form>

	<p class="description murtadd-admin-note">
		<?php esc_html_e( 'New pages are published immediately with their copy written. Before announcing the site, send a test message through the form on the Contact page (it goes to the address on the Content tab, or the administration email), and check that the Privacy page still describes the site if you add plugins that collect data. Starter content is published as written and can be edited freely: nothing here ever overwrites a post that already exists, so editing a doubt and running this again will not undo the edit.', 'murtadd' ); ?>
	</p>
	<?php
	murtadd_admin_card_close();
}

/**
 * The Topics & schools tab: links to the native term screens.
 */
function murtadd_render_taxonomies_tab() {
	murtadd_admin_card_open( __( 'Term management', 'murtadd' ), __( 'Terms are managed on the native WordPress screens — nothing is duplicated here.', 'murtadd' ), 'tag' );
	?>
	<ul class="murtadd-admin-links">
		<li><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=murtadd_topic&post_type=murtadd_doubt' ) ); ?>"><?php esc_html_e( 'Manage Topics →', 'murtadd' ); ?></a></li>
		<li><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=murtadd_school&post_type=murtadd_fatwa' ) ); ?>"><?php esc_html_e( 'Manage Schools →', 'murtadd' ); ?></a></li>
		<li><?php esc_html_e( 'Doubt categories are locked — four fixed terms power the homepage grid.', 'murtadd' ); ?></li>
	</ul>
	<?php
	murtadd_admin_card_close();
}

/**
 * The Content tab's fields.
 */
function murtadd_render_content_tab() {
	$o = get_option( 'murtadd_content_display', array() );
	$o = is_array( $o ) ? $o : array();

	murtadd_admin_card_open( __( 'Doubts', 'murtadd' ), '', 'editor-help' );
	murtadd_admin_row_open( __( 'Short response word cap', 'murtadd' ), 'murtadd_cap' );
	?>
	<input type="number" id="murtadd_cap" name="murtadd_content_display[short_response_word_cap]" value="<?php echo esc_attr( $o['short_response_word_cap'] ?? 400 ); ?>" min="50" step="10" class="small-text" aria-describedby="murtadd_cap-desc">
	<p class="description" id="murtadd_cap-desc"><?php esc_html_e( 'Soft cap. The doubt editor shows a live word count and warns past this — it never blocks saving.', 'murtadd' ); ?></p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_toggle_row(
		__( '"If you are struggling" notice', 'murtadd' ),
		'murtadd_struggling',
		'murtadd_content_display[show_struggling_notice]',
		! empty( $o['show_struggling_notice'] ?? 1 ),
		__( 'Show on Emotional and Identity doubt pages', 'murtadd' ),
		__( 'A quiet crisis-support block. No tracking, no preaching.', 'murtadd' )
	);
	murtadd_admin_card_close();

	murtadd_admin_card_open( __( 'Reading aids', 'murtadd' ), '', 'visibility' );
	murtadd_admin_toggle_row(
		__( 'Breadcrumbs', 'murtadd' ),
		'murtadd_breadcrumbs',
		'murtadd_content_display[show_breadcrumbs]',
		! empty( $o['show_breadcrumbs'] ?? 1 ),
		__( 'Show the trail above entries, pages and archives', 'murtadd' ),
		__( 'The same trail is given to search engines as a BreadcrumbList. With Rank Math\'s breadcrumbs switched on, Rank Math\'s trail is shown instead.', 'murtadd' )
	);
	murtadd_admin_toggle_row(
		__( 'Glossary of terms', 'murtadd' ),
		'murtadd_glossary',
		'murtadd_content_display[glossary_terms]',
		! isset( $o['glossary_terms'] ) || $o['glossary_terms'],
		__( 'Define Arabic and Malay terms where they first appear', 'murtadd' ),
		__( 'The first mention of a term on a page gets a short definition and its source, shown on hover, focus or tap. Never on Emotional or Identity doubts.', 'murtadd' )
	);
	murtadd_admin_row_open( __( 'Scripture box', 'murtadd' ), 'murtadd_scripture' );
	murtadd_admin_toggle(
		'murtadd_scripture',
		'murtadd_content_display[show_scripture]',
		! isset( $o['show_scripture'] ) || $o['show_scripture'],
		__( 'Show the scripture box in the rail on doubt and rebuttal pages', 'murtadd' )
	);
	?>
	<p class="murtadd-admin-spaced">
		<label class="murtadd-field__sublabel" for="murtadd_scripture_text"><?php esc_html_e( 'Verse', 'murtadd' ); ?></label>
		<input type="text" id="murtadd_scripture_text" name="murtadd_content_display[scripture_text]" value="<?php echo esc_attr( $o['scripture_text'] ?? __( 'Let there be no compulsion in religion.', 'murtadd' ) ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'The verse, in whichever rendering you prefer', 'murtadd' ); ?>">
	</p>
	<p>
		<label class="murtadd-field__sublabel" for="murtadd_scripture_ref"><?php esc_html_e( 'Reference', 'murtadd' ); ?></label>
		<input type="text" id="murtadd_scripture_ref" name="murtadd_content_display[scripture_ref]" value="<?php echo esc_attr( $o['scripture_ref'] ?? __( 'Qur’an 2:256', 'murtadd' ) ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Reference', 'murtadd' ); ?>">
	</p>
	<p class="description">
		<?php esc_html_e( 'Choose the rendering yourself. The wording of a translation is an editorial decision and belongs to you, not to the theme.', 'murtadd' ); ?>
	</p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_card_close();

	murtadd_render_progress_card();
	murtadd_render_search_card();

	murtadd_admin_card_open( __( 'Fatwa index default sort', 'murtadd' ), __( 'The order the fatwa index opens in. Readers can still re-sort it.', 'murtadd' ), 'sort' );
	$sort    = (string) ( $o['fatwa_index_default_sort'] ?? 'scholar_az' );
	$choices = array(
		'scholar_az'     => array( __( 'Scholar A–Z', 'murtadd' ), __( 'By the scholar or body, alphabetically.', 'murtadd' ), 'editor-textcolor' ),
		'era'            => array( __( 'Era', 'murtadd' ), __( 'Earliest positions first.', 'murtadd' ), 'backup' ),
		'recently_added' => array( __( 'Recently added', 'murtadd' ), __( 'Newest entries first.', 'murtadd' ), 'clock' ),
	);
	if ( ! isset( $choices[ $sort ] ) ) {
		$sort = 'scholar_az';
	}
	?>
	<fieldset class="murtadd-choices">
		<legend class="screen-reader-text"><?php esc_html_e( 'Fatwa index default sort', 'murtadd' ); ?></legend>
		<?php foreach ( $choices as $value => $choice ) : ?>
			<label class="murtadd-choice" for="murtadd_sort_<?php echo esc_attr( $value ); ?>">
				<input type="radio" class="murtadd-choice__input" id="murtadd_sort_<?php echo esc_attr( $value ); ?>" name="murtadd_content_display[fatwa_index_default_sort]" value="<?php echo esc_attr( $value ); ?>" <?php checked( $sort, $value ); ?>>
				<span class="murtadd-choice__body">
					<span class="murtadd-choice__icon dashicons dashicons-<?php echo esc_attr( $choice[2] ); ?>" aria-hidden="true"></span>
					<span class="murtadd-choice__title"><?php echo esc_html( $choice[0] ); ?></span>
					<span class="murtadd-choice__desc"><?php echo esc_html( $choice[1] ); ?></span>
				</span>
			</label>
		<?php endforeach; ?>
	</fieldset>
	<?php
	murtadd_admin_card_close();

	murtadd_admin_card_open( __( 'Contact and footer', 'murtadd' ), '', 'email' );
	murtadd_admin_row_open( __( 'Contact form recipient', 'murtadd' ), 'murtadd_contact_recipient' );
	?>
	<input type="email" id="murtadd_contact_recipient" name="murtadd_content_display[contact_recipient]" value="<?php echo esc_attr( $o['contact_recipient'] ?? '' ); ?>" class="regular-text" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" aria-describedby="murtadd_contact_recipient-desc">
	<p class="description" id="murtadd_contact_recipient-desc"><?php esc_html_e( 'Where messages from the contact form ([murtadd_contact]) are sent. Leave empty to use the administration email address under Settings, General. The address is never shown on the site.', 'murtadd' ); ?></p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_row_open( __( 'Footer heritage note', 'murtadd' ), 'murtadd_heritage' );
	?>
	<input type="text" id="murtadd_heritage" name="murtadd_content_display[footer_heritage_note]" value="<?php echo esc_attr( $o['footer_heritage_note'] ?? 'online since 2004' ); ?>" class="regular-text">
	<?php
	murtadd_admin_row_close();
	murtadd_admin_card_close();
}

/**
 * The Linked sites tab's fields.
 */
function murtadd_render_cross_links_tab() {
	$o = get_option( 'murtadd_cross_links', array() );
	$o = is_array( $o ) ? $o : array();

	murtadd_admin_card_open( __( 'Compelling Evidence', 'murtadd' ), __( 'The companion site that builds the positive case.', 'murtadd' ), 'admin-links' );
	murtadd_admin_row_open( __( 'Evidential case base URL', 'murtadd' ), 'murtadd_ce' );
	?>
	<input type="url" id="murtadd_ce" name="murtadd_cross_links[ce_base_url]" value="<?php echo esc_url( $o['ce_base_url'] ?? 'https://compelling-evidence.com' ); ?>" class="regular-text" aria-describedby="murtadd_ce-desc">
	<p class="description" id="murtadd_ce-desc"><?php esc_html_e( 'Used by the homepage "full argument" card and doubt pages\' outbound deep links. Cleared = links hidden.', 'murtadd' ); ?></p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_toggle_row(
		__( 'Counterpart link', 'murtadd' ),
		'murtadd_counterpart',
		'murtadd_cross_links[show_counterpart]',
		'0' !== (string) ( $o['show_counterpart'] ?? '1' ),
		__( 'Show the counterpart link', 'murtadd' ),
		__( 'On doubts and rebuttals that name a Compelling Evidence article, an aside links to it at the evidential case base URL above (/articles/slug/). On by default.', 'murtadd' )
	);
	murtadd_admin_toggle_row(
		__( 'Starting-point card', 'murtadd' ),
		'murtadd_quiz_card',
		'murtadd_cross_links[show_quiz_card]',
		'0' !== (string) ( $o['show_quiz_card'] ?? '1' ),
		__( 'Show the "Not sure where to start?" card', 'murtadd' ),
		__( 'On the home page and the Start Here page, a card links to the Compelling Evidence quiz at the evidential case base URL above (/quiz/?from=murtadd). It opens in the same tab. On by default.', 'murtadd' )
	);
	murtadd_admin_card_close();

	murtadd_admin_card_open( __( 'Polemics', 'murtadd' ), '', 'external' );
	murtadd_admin_row_open( __( 'Polemics base URL', 'murtadd' ), 'murtadd_bismika' );
	?>
	<input type="url" id="murtadd_bismika" name="murtadd_cross_links[bismika_base_url]" value="<?php echo esc_url( $o['bismika_base_url'] ?? 'https://bismikaallahuma.org' ); ?>" class="regular-text" aria-describedby="murtadd_bismika-desc">
	<p class="description" id="murtadd_bismika-desc"><?php esc_html_e( 'All outbound links open in a new tab with rel="noopener". No relationship is stated on the frontend.', 'murtadd' ); ?></p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_card_close();
}

/**
 * The Contextual links tab's fields.
 */
function murtadd_render_contextual_links_tab() {
	$o = murtadd_ctx_settings();

	murtadd_admin_card_open( __( 'Links in the running text', 'murtadd' ), __( 'In the running text of a single entry, the first mention of a known phrase links to the page that treats it, on this site or on one of the six sister sites. Links are added as the page renders; stored text is never changed. Headings, quotations, Qur’anic text, Arabic, sources and existing links are never touched, and Emotional and Identity doubts get no links at all.', 'murtadd' ), 'paperclip' );
	murtadd_admin_toggle_row(
		__( 'Links to this site', 'murtadd' ),
		'murtadd_ctx_own',
		'murtadd_contextual_links[own_enabled]',
		! empty( $o['own_enabled'] ),
		__( 'Link phrases to other doubts and rebuttals here', 'murtadd' )
	);
	murtadd_admin_row_open( __( 'At most, to this site', 'murtadd' ), 'murtadd_ctx_own_max' );
	murtadd_admin_range( 'murtadd_ctx_own_max', 'murtadd_contextual_links[own_max]', (int) $o['own_max'], 0, 15, __( 'a page (0 to 15)', 'murtadd' ) );
	murtadd_admin_row_close();
	murtadd_admin_toggle_row(
		__( 'Links to sister sites', 'murtadd' ),
		'murtadd_ctx_sister',
		'murtadd_contextual_links[sister_enabled]',
		! empty( $o['sister_enabled'] ),
		__( 'Link phrases to the page on a sister site that treats them', 'murtadd' )
	);
	murtadd_admin_row_open( __( 'At most, to sister sites', 'murtadd' ), 'murtadd_ctx_sister_max' );
	murtadd_admin_range( 'murtadd_ctx_sister_max', 'murtadd_contextual_links[sister_max]', (int) $o['sister_max'], 0, 10, __( 'a page (0 to 10)', 'murtadd' ) );
	murtadd_admin_row_close();
	murtadd_admin_card_close();

	murtadd_admin_card_open( __( 'Phrases', 'murtadd' ), '', 'editor-ul' );
	murtadd_admin_row_open( __( 'Extra phrases', 'murtadd' ), 'murtadd_ctx_extra' );
	?>
	<textarea id="murtadd_ctx_extra" name="murtadd_contextual_links[extra]" rows="8" class="large-text code" placeholder="<?php esc_attr_e( 'tudung | /rebuttals/the-head-cover-and-modesty/', 'murtadd' ); ?>" aria-describedby="murtadd_ctx_extra-desc"><?php echo esc_textarea( $o['extra'] ); ?></textarea>
	<p class="description" id="murtadd_ctx_extra-desc"><?php esc_html_e( 'One per line: phrase | address. A path such as /rebuttals/slug/ is this site; a full address must be on one of the sister sites. These are tried before the shared list, so they win. Lines in any other shape are dropped on save.', 'murtadd' ); ?></p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_row_open( __( 'Never link on', 'murtadd' ), 'murtadd_ctx_excluded' );
	?>
	<textarea id="murtadd_ctx_excluded" name="murtadd_contextual_links[excluded]" rows="4" class="large-text code" aria-describedby="murtadd_ctx_excluded-desc"><?php echo esc_textarea( $o['excluded'] ); ?></textarea>
	<p class="description" id="murtadd_ctx_excluded-desc"><?php esc_html_e( 'Slugs of entries or pages that get no contextual links, one per line.', 'murtadd' ); ?></p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_card_close();
}

/**
 * The Colours tab's fields: core's colour picker in a grid.
 */
function murtadd_render_colours_tab() {
	$o = get_option( 'murtadd_colours', array() );
	$o = is_array( $o ) ? $o : array();

	murtadd_admin_card_open( __( 'Accent colours', 'murtadd' ), __( 'Used for links, buttons, and the sidebar. Applied via wp_add_inline_style.', 'murtadd' ), 'art' );
	?>
	<div class="murtadd-color-grid">
		<div class="murtadd-color">
			<label class="murtadd-color__label" for="murtadd_accent"><?php esc_html_e( 'Accent colour', 'murtadd' ); ?></label>
			<input type="text" id="murtadd_accent" name="murtadd_colours[accent]" value="<?php echo esc_attr( $o['accent'] ?? '#0f6e56' ); ?>" class="murtadd-color-field" data-default-color="#0f6e56" data-murtadd-contrast>
		</div>
		<div class="murtadd-color">
			<label class="murtadd-color__label" for="murtadd_accent_tint"><?php esc_html_e( 'Accent tint', 'murtadd' ); ?></label>
			<input type="text" id="murtadd_accent_tint" name="murtadd_colours[accent_tint]" value="<?php echo esc_attr( $o['accent_tint'] ?? '#eefaf7' ); ?>" class="murtadd-color-field" data-default-color="#eefaf7">
		</div>
	</div>
	<div class="murtadd-field murtadd-field--plain">
		<div class="murtadd-field__label"><span class="murtadd-field__title" id="murtadd-contrast-label"><?php esc_html_e( 'Contrast check', 'murtadd' ); ?></span></div>
		<div class="murtadd-field__control"><span id="murtadd-contrast-result" class="murtadd-contrast" aria-live="polite" aria-labelledby="murtadd-contrast-label"></span></div>
	</div>
	<?php
	murtadd_admin_card_close();
}

/**
 * The Profiles tab's fields.
 */
function murtadd_render_profiles_tab() {
	$profiles = get_option( 'murtadd_profiles', array() );
	$profiles = is_array( $profiles ) ? $profiles : array();

	murtadd_admin_card_open( __( 'Author', 'murtadd' ), __( 'A link for any platform the author is on. Every URL entered here does two jobs at once: it appears as an icon in the site footer, and it is emitted as a sameAs entry on the author in the structured data, so search engines and AI systems can connect this site to the same person elsewhere. Leave a field blank and it appears in neither place.', 'murtadd' ), 'id' );
	murtadd_admin_row_open( __( 'Author name', 'murtadd' ), 'murtadd_author_name' );
	?>
	<input type="text" id="murtadd_author_name" name="murtadd_author_name" value="<?php echo esc_attr( get_option( 'murtadd_author_name', '' ) ); ?>" class="regular-text" aria-describedby="murtadd_author_name-desc">
	<p class="description" id="murtadd_author_name-desc"><?php esc_html_e( 'Kept for reference and no longer read. The structured data names Mohd Elfie Nieshaem Juferi, at https://menj.bio, as the author of every entry, with the links below ahead of https://menj.bio in sameAs.', 'murtadd' ); ?></p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_card_close();

	murtadd_admin_card_open( __( 'Profiles', 'murtadd' ), '', 'share' );
	echo '<div class="murtadd-profiles-grid">';
	foreach ( murtadd_social_registry() as $murtadd_slug => $murtadd_meta ) {
		$id = 'murtadd_profile_' . $murtadd_slug;
		?>
		<div class="murtadd-profile">
			<label class="murtadd-profile__label" for="<?php echo esc_attr( $id ); ?>">
				<span class="murtadd-profiles-icon"><?php echo murtadd_social_icon( $murtadd_slug, 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- registry SVG. ?></span>
				<?php echo esc_html( $murtadd_meta['label'] ); ?>
			</label>
			<input type="url" id="<?php echo esc_attr( $id ); ?>" name="murtadd_profiles[<?php echo esc_attr( $murtadd_slug ); ?>]" value="<?php echo esc_attr( $profiles[ $murtadd_slug ] ?? '' ); ?>" class="regular-text" placeholder="https://">
		</div>
		<?php
	}
	echo '</div>';
	murtadd_admin_card_close();
}

/**
 * The Security tab's fields (inside the tab's settings form).
 */
function murtadd_render_security_tab() {
	$slug      = (string) murtadd_security( 'login_slug' );
	$available = murtadd_hide_login_available();

	murtadd_admin_card_open( __( 'Hardening', 'murtadd' ), __( 'The site at this address was compromised in 2024. These switches close what WordPress leaves open by default. Each is independent.', 'murtadd' ), 'shield' );
	murtadd_admin_toggle_row(
		__( 'Author names', 'murtadd' ),
		'murtadd_sec_hide_authors',
		'murtadd_security[hide_authors]',
		(bool) murtadd_security( 'hide_authors' ),
		__( 'Keep account names private', 'murtadd' ),
		__( 'Closes the REST user list, ?author= addresses and author archives, and the users sitemap. Feeds and embeds name the author from the Profiles tab, or the site.', 'murtadd' )
	);
	murtadd_admin_row_open( __( 'XML-RPC', 'murtadd' ), 'murtadd_sec_xmlrpc_off' );
	murtadd_admin_toggle(
		'murtadd_sec_xmlrpc_off',
		'murtadd_security[xmlrpc_off]',
		(bool) murtadd_security( 'xmlrpc_off' ),
		__( 'Switch XML-RPC off', 'murtadd' ),
		__( 'The endpoint password-guessing scripts use: xmlrpc.php answers 403 and the pingback header goes. On Apache the theme also blocks xmlrpc.php in .htaccess, and removes that block when this is switched off. Leave XML-RPC on only if you publish from the WordPress mobile app or Jetpack.', 'murtadd' )
	);
	$murtadd_htaccess = (string) get_option( 'murtadd_security_htaccess', '' );
	if ( 'written' === $murtadd_htaccess ) {
		echo '<p class="murtadd-status-ok">' . esc_html__( 'The web server also refuses xmlrpc.php: the theme keeps a "Block xmlrpc.php" section in .htaccess and repairs it if it goes missing.', 'murtadd' ) . '</p>';
	} elseif ( 'unavailable' === $murtadd_htaccess ) {
		echo '<p class="murtadd-status-missing">' . esc_html__( 'The theme could not write .htaccess (the server is not Apache, or the file is read-only). WordPress still refuses xmlrpc.php; ask the host to deny it at the server as well.', 'murtadd' ) . '</p>';
	}
	murtadd_admin_row_close();
	murtadd_admin_toggle_row(
		__( 'Application passwords', 'murtadd' ),
		'murtadd_sec_app_passwords_off',
		'murtadd_security[app_passwords_off]',
		(bool) murtadd_security( 'app_passwords_off' ),
		__( 'Turn off application passwords', 'murtadd' ),
		__( 'Stored application passwords stop working at once. Revoke them as well, below, so they cannot return if this is switched off.', 'murtadd' )
	);
	murtadd_admin_toggle_row(
		__( 'REST API changes', 'murtadd' ),
		'murtadd_sec_rest_lock',
		'murtadd_security[rest_lock]',
		(bool) murtadd_security( 'rest_lock' ),
		__( 'Allow REST API changes only from a logged-in browser', 'murtadd' ),
		__( 'Any logged-in request that creates, changes or deletes content must come from a browser session on this site with a valid nonce. Basic authentication and passwords sent in a header are refused. Reading through the API, the contact form and anything a visitor sends are unchanged.', 'murtadd' )
	);
	murtadd_admin_toggle_row(
		__( 'Security headers', 'murtadd' ),
		'murtadd_sec_headers',
		'murtadd_security[headers]',
		(bool) murtadd_security( 'headers' ),
		__( 'Send security headers on every page', 'murtadd' ),
		__( 'X-Content-Type-Options, Referrer-Policy, X-Frame-Options and Permissions-Policy. A header the server already sends is left alone.', 'murtadd' )
	);
	murtadd_admin_card_close();

	murtadd_admin_card_open( __( 'Spam', 'murtadd' ), '', 'dismiss' );
	murtadd_admin_toggle_row(
		__( 'Spam gate', 'murtadd' ),
		'murtadd_sec_spam_gate',
		'murtadd_security[spam_gate]',
		(bool) murtadd_security( 'spam_gate' ),
		__( 'Hold suspected gambling spam as Pending', 'murtadd' ),
		__( 'A post, page, doubt, rebuttal, fatwa, letter or guide whose title, address or text reads as casino or betting spam is saved as Pending, never published, and logged below. So is anything published from outside the editor (XML-RPC, or REST without a browser session). Starter content is exempt.', 'murtadd' )
	);
	murtadd_admin_row_open( __( 'Removed spam', 'murtadd' ), 'murtadd_sec_spam_gone' );
	murtadd_admin_toggle(
		'murtadd_sec_spam_gone',
		'murtadd_security[spam_gone]',
		(bool) murtadd_security( 'spam_gone' ),
		__( 'Answer 410 Gone on the address of removed spam', 'murtadd' ),
		__( 'Search engines drop a 410 address faster than a 404 one. Only addresses of spam moved to the trash from this tab are affected.', 'murtadd' )
	);
	if ( defined( 'MURTADD_SECURITY_LOCK' ) && ! MURTADD_SECURITY_LOCK ) {
		echo '<p class="murtadd-status-missing">' . esc_html__( 'MURTADD_SECURITY_LOCK is false in wp-config.php, so the XML-RPC, application password and REST switches are off whatever they show.', 'murtadd' ) . '</p>';
	}
	murtadd_admin_row_close();
	murtadd_admin_card_close();

	murtadd_admin_card_open( __( 'Login', 'murtadd' ), '', 'lock' );
	murtadd_admin_row_open( __( 'Private login address', 'murtadd' ), 'murtadd_login_slug' );
	?>
	<div class="murtadd-input-prefix">
		<code><?php echo esc_html( home_url( '/' ) ); ?></code>
		<input type="text" id="murtadd_login_slug" name="murtadd_security[login_slug]" value="<?php echo esc_attr( $slug ); ?>" class="regular-text code" placeholder="<?php esc_attr_e( 'leave empty to keep wp-login.php', 'murtadd' ); ?>" aria-describedby="murtadd_login_slug-warning">
	</div>
	<div class="murtadd-admin-warning" id="murtadd_login_slug-warning">
		<p><strong><?php esc_html_e( 'Write the address down before saving.', 'murtadd' ); ?></strong> <?php esc_html_e( 'Once set, wp-login.php and wp-admin show a page not found to anyone not logged in. Off while the field is empty.', 'murtadd' ); ?></p>
		<p><?php esc_html_e( 'Locked out? Add define( \'MURTADD_HIDE_LOGIN\', false ); to wp-config.php, log in at wp-login.php, clear this field, then remove the line.', 'murtadd' ); ?></p>
	</div>
	<?php if ( ! $available ) : ?>
		<p class="murtadd-status-missing"><?php esc_html_e( 'Inactive: MURTADD_HIDE_LOGIN is false in wp-config.php, or the WPS Hide Login plugin is handling the login address.', 'murtadd' ); ?></p>
	<?php elseif ( '' !== murtadd_login_slug() ) : ?>
		<p class="murtadd-status-ok">
			<?php
			/* translators: %s: the private login address. */
			printf( esc_html__( 'Your login address: %s', 'murtadd' ), '<code>' . esc_html( murtadd_login_url() ) . '</code>' );
			?>
		</p>
	<?php endif; ?>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_toggle_row(
		__( 'Login page', 'murtadd' ),
		'murtadd_sec_login_design',
		'murtadd_security[login_design]',
		(bool) murtadd_security( 'login_design' ),
		__( 'Dress the login screen in the site\'s colours and type', 'murtadd' ),
		__( 'A green brand panel beside a rounded form card, on every login screen (log in, lost password, reset, register). The mark is the custom logo, then the Site Icon, then the theme\'s own mark, and links to the site. The accents follow the Colours tab. Works at the private login address too.', 'murtadd' )
	);
	murtadd_admin_row_open( __( 'Note above the form', 'murtadd' ), 'murtadd_login_message' );
	?>
	<textarea id="murtadd_login_message" name="murtadd_security[login_message]" rows="3" class="large-text" maxlength="500" aria-describedby="murtadd_login_message-desc"><?php echo esc_textarea( (string) murtadd_security( 'login_message' ) ); ?></textarea>
	<p class="description" id="murtadd_login_message-desc"><?php esc_html_e( 'Plain text, up to 500 characters, shown on the log-in screen only. Leave empty for none.', 'murtadd' ); ?></p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_row_open( __( 'Line on the brand panel', 'murtadd' ), 'murtadd_login_tagline' );
	?>
	<input type="text" id="murtadd_login_tagline" name="murtadd_security[login_tagline]" value="<?php echo esc_attr( (string) murtadd_security( 'login_tagline' ) ); ?>" class="large-text" maxlength="160" placeholder="<?php echo esc_attr( (string) get_bloginfo( 'description' ) ); ?>" aria-describedby="murtadd_login_tagline-desc">
	<p class="description" id="murtadd_login_tagline-desc"><?php esc_html_e( 'Plain text, up to 160 characters, beside the form on wide screens and above it on phones. Leave empty to use the site tagline.', 'murtadd' ); ?></p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_toggle_row(
		__( 'Privacy note', 'murtadd' ),
		'murtadd_sec_login_privacy',
		'murtadd_security[login_privacy]',
		(bool) murtadd_security( 'login_privacy' ),
		__( 'Show a privacy note under the log-in, lost-password and registration forms', 'murtadd' )
	);
	murtadd_admin_card_close();
}

/**
 * The Upkeep tab's fields (inside the tab's settings form).
 */
function murtadd_render_upkeep_tab() {
	murtadd_admin_card_open( __( 'Outages', 'murtadd' ), '', 'hammer' );
	murtadd_admin_toggle_row(
		__( 'Maintenance mode', 'murtadd' ),
		'murtadd_upkeep_maintenance',
		'murtadd_upkeep[maintenance]',
		(bool) murtadd_upkeep( 'maintenance' ),
		__( 'Show visitors the "Back shortly" page', 'murtadd' ),
		__( 'Visitors get the maintenance page with a 503, so search engines keep the pages and come back. Anyone who can change these options still sees the site, and the admin bar says maintenance is on. The login screen stays open.', 'murtadd' )
	);
	murtadd_admin_toggle_row(
		__( 'Error pages', 'murtadd' ),
		'murtadd_upkeep_error_pages',
		'murtadd_upkeep[error_pages]',
		(bool) murtadd_upkeep( 'error_pages' ),
		__( 'Use the branded pages for updates, PHP errors and database errors', 'murtadd' ),
		__( 'The theme writes maintenance.php, php-error.php and db-error.php into wp-content, where WordPress looks for them, and never overwrites a file it did not write. Switching this off, or switching theme, removes its copies.', 'murtadd' )
	);
	murtadd_admin_card_close();

	murtadd_admin_card_open( __( 'Removed for good (410)', 'murtadd' ), '', 'no' );
	murtadd_admin_toggle_row(
		__( 'Spam presets', 'murtadd' ),
		'murtadd_upkeep_gone_presets',
		'murtadd_upkeep[gone_presets]',
		(bool) murtadd_upkeep( 'gone_presets' ),
		__( 'Answer 410 for the spam addresses found in the November 2026 logs', 'murtadd' ),
		__( 'Covers /?items/*.sthml, /index.php/sitemap*, /sitemapimages*, /contents/* and /goods.php. WordPress answered most of them with the home page, so these answer 410 on the home page too. An address that leads to a real post, page or archive is never touched.', 'murtadd' )
	);
	murtadd_admin_row_open( __( 'Addresses', 'murtadd' ), 'murtadd_gone_paths' );
	?>
	<textarea id="murtadd_gone_paths" name="murtadd_upkeep[gone_paths]" rows="8" class="large-text code" placeholder="<?php echo esc_attr( "/cheap-pills/\n/shop/*" ); ?>" aria-describedby="murtadd_gone_paths-desc"><?php echo esc_textarea( (string) murtadd_upkeep( 'gone_paths' ) ); ?></textarea>
	<p class="description" id="murtadd_gone_paths-desc"><?php esc_html_e( 'Addresses that never belonged to this site, such as spam pages left by the 2024 compromise, answered 410 Gone so search engines drop them. One per line: an exact path (/cheap-pills/), a prefix ending in * (/shop/* covers /shop/ and everything under it), a * in the middle (/feed-*.xml), or a path with a query string (/?items/*.sthml). A path applies only where the site has no page: a live page, and any old address the legacy redirects recover, are never touched. A query string also applies on the home page, and may not start with a key WordPress or a tracking link uses (p, s, page_id, utm_source and the like). "/", "/*" and "/?*" alone are refused.', 'murtadd' ); ?></p>
	<?php
	murtadd_admin_row_close();
	murtadd_admin_card_close();
}

/**
 * The error pages' state in wp-content, and a button to write them now. Its
 * own form, outside the settings form.
 */
function murtadd_render_dropins_status() {
	$labels = array(
		'written' => __( 'Written by the theme', 'murtadd' ),
		'outdated' => __( 'Written by the theme, outdated: press the button to refresh it', 'murtadd' ),
		'kept'    => __( 'Another file is there; left alone', 'murtadd' ),
		'failed'  => __( 'Could not be written (wp-content not writable or source file unreadable)', 'murtadd' ),
	);
	murtadd_admin_card_open( __( 'Error pages in wp-content', 'murtadd' ), '', 'media-document' );
	?>
	<?php
	$murtadd_saved = (array) get_option( 'murtadd_dropins_status', array() );
	$murtadd_fail  = in_array( 'failed', $murtadd_saved, true );
	?>
	<?php $murtadd_subsite = ! murtadd_dropins_writer(); ?>
	<?php if ( ! $murtadd_subsite && isset( $_GET['murtadd_dropins'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
		<?php if ( $murtadd_fail ) : ?>
			<div class="notice notice-error is-dismissible inline"><p><?php esc_html_e( 'Some error pages could not be written: wp-content is not writable, or the source file is unreadable.', 'murtadd' ); ?></p></div>
		<?php else : ?>
			<div class="notice notice-success is-dismissible inline"><p><?php esc_html_e( 'The error pages were written again.', 'murtadd' ); ?></p></div>
		<?php endif; ?>
	<?php endif; ?>
	<table class="widefat striped murtadd-admin-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'File', 'murtadd' ); ?></th>
				<th scope="col"><?php esc_html_e( 'State', 'murtadd' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( array_keys( murtadd_dropin_files() ) as $murtadd_name ) : ?>
				<?php
				$murtadd_state = murtadd_dropin_state( $murtadd_name );
				// A failed write leaves the old state on disk; the saved result says why.
				// A sibling theme's file the theme may replace is not left alone.
				$murtadd_left = 'kept' === $murtadd_state && ! murtadd_dropin_is_replaceable( WP_CONTENT_DIR . '/' . $murtadd_name );
				if ( 'written' !== $murtadd_state && ! $murtadd_left && 'failed' === ( $murtadd_saved[ $murtadd_name ] ?? '' ) ) {
					$murtadd_state = 'failed';
				}
				?>
				<tr>
					<td><code><?php echo esc_html( $murtadd_name ); ?></code></td>
					<td class="<?php echo esc_attr( 'written' === $murtadd_state ? 'murtadd-status-ok' : 'murtadd-status-missing' ); ?>"><?php echo esc_html( $labels[ $murtadd_state ] ?? __( 'Not there', 'murtadd' ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php if ( $murtadd_subsite ) : ?>
		<p class="description"><?php esc_html_e( 'The whole network shares wp-content, so one site writes the error pages for all: the main site, or the first site that runs Murtadd when the main site runs another theme. Its Error pages switch applies to the whole network.', 'murtadd' ); ?></p>
	<?php elseif ( murtadd_upkeep( 'error_pages' ) ) : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="murtadd-admin-inline-form">
			<input type="hidden" name="action" value="murtadd_write_dropins">
			<?php wp_nonce_field( 'murtadd_write_dropins' ); ?>
			<?php submit_button( __( 'Write the error pages now', 'murtadd' ), 'secondary', 'submit', false ); ?>
		</form>
	<?php endif; ?>
	<?php if ( murtadd_upkeep( 'error_pages' ) ) : ?>
	<p class="description"><?php esc_html_e( 'For the errors the web server answers before WordPress starts, the theme writes static pages into wp-content/murtadd-errors, with this site\'s addresses. Add these lines to .htaccess to use them:', 'murtadd' ); ?></p>
	<textarea readonly rows="6" class="large-text code" aria-label="<?php esc_attr_e( '.htaccess lines', 'murtadd' ); ?>"><?php echo esc_textarea( murtadd_error_htaccess_snippet() ); ?></textarea>
	<?php else : ?>
	<p class="description"><?php esc_html_e( 'With Error pages off, the theme writes no static pages for the web server. Remove any ErrorDocument lines that point at wp-content/murtadd-errors from .htaccess.', 'murtadd' ); ?></p>
	<?php endif; ?>
	<?php
	murtadd_admin_card_close();
}
