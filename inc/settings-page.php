<?php
/**
 * Settings page — Appearance → Murtadd. Settings API, tabbed.
 * One option array per tab: murtadd_colours, murtadd_cross_links, murtadd_content_display.
 * Taxonomies tab deep-links to native term screens (no duplicate CRUD).
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

function murtadd_add_settings_page() {
	add_theme_page( __( 'Murtadd', 'murtadd' ), __( 'Murtadd', 'murtadd' ), 'manage_options', 'murtadd', 'murtadd_render_settings_page' );
}
add_action( 'admin_menu', 'murtadd_add_settings_page' );

function murtadd_register_settings() {
	register_setting( 'murtadd_colours', 'murtadd_colours', array( 'sanitize_callback' => 'murtadd_sanitize_colours' ) );
	register_setting( 'murtadd_cross_links', 'murtadd_cross_links', array( 'sanitize_callback' => 'murtadd_sanitize_cross_links' ) );
	register_setting( 'murtadd_content_display', 'murtadd_content_display', array( 'sanitize_callback' => 'murtadd_sanitize_content_display' ) );
	register_setting( 'murtadd_profiles', 'murtadd_profiles', array( 'sanitize_callback' => 'murtadd_sanitize_profiles' ) );
	register_setting( 'murtadd_profiles', 'murtadd_author_name', array( 'sanitize_callback' => 'sanitize_text_field' ) );
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
	return array(
		'short_response_word_cap'  => max( 50, absint( $input['short_response_word_cap'] ?? 400 ) ),
		'show_struggling_notice'   => ! empty( $input['show_struggling_notice'] ) ? 1 : 0,
		'fatwa_index_default_sort' => in_array( $sort, $sorts, true ) ? $sort : 'scholar_az',
		'footer_heritage_note'     => sanitize_text_field( $input['footer_heritage_note'] ?? 'online since 2004' ),
		'show_scripture'           => empty( $input['show_scripture'] ) ? 0 : 1,
		'scripture_text'           => sanitize_text_field( $input['scripture_text'] ?? '' ),
		'scripture_ref'            => sanitize_text_field( $input['scripture_ref'] ?? '' ),
	);
}

function murtadd_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	/*
	 * Tabs in the order the work is actually done, not the order the code was
	 * written. Setup scaffolds the site and comes first; colour is the last
	 * thing anyone touches and comes last.
	 *
	 * The KEYS are load-bearing: settings_fields( 'murtadd_' . $active ) maps
	 * each one to its registered setting group. Relabel and reorder freely;
	 * renaming a key silently detaches the tab from its settings. 'setup',
	 * 'taxonomies', and 'docs' are the exceptions: they render their own
	 * markup and register no settings group.
	 */
	$tabs = array(
		'setup'           => __( 'Setup', 'murtadd' ),           // Build the site.
		'content_display' => __( 'Content', 'murtadd' ),         // Decide what it shows.
		'taxonomies'      => __( 'Topics & schools', 'murtadd' ), // File the content.
		'cross_links'     => __( 'Linked sites', 'murtadd' ),    // Point outward.
		'colours'         => __( 'Colours', 'murtadd' ),         // Make it yours.
		'profiles'        => __( 'Profiles', 'murtadd' ),        // The author's links, shown and structured.
		'docs'            => __( 'Docs', 'murtadd' ),            // Look something up.
	);
	$requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
	$active    = isset( $tabs[ $requested ] ) ? $requested : (string) array_key_first( $tabs );
	?>
	<div class="wrap murtadd-admin">

		<?php
		$murtadd_doubts    = (int) wp_count_posts( 'murtadd_doubt' )->publish;
		$murtadd_rebuttals = (int) wp_count_posts( 'murtadd_rebuttal' )->publish;
		$murtadd_gaps      = count( murtadd_content_gaps() );
		?>

		<header class="murtadd-admin-hero">
			<div class="murtadd-admin-hero-inner">
				<div class="murtadd-admin-brand">
					<?php
					// The site icon: core's, if one is set; otherwise the theme's own mark.
					$murtadd_icon = get_site_icon_url( 96 );
					if ( ! $murtadd_icon ) {
						$murtadd_icon = get_stylesheet_directory_uri() . '/assets/icons/site-icon.svg';
					}
					?>
					<img class="murtadd-admin-icon" src="<?php echo esc_url( $murtadd_icon ); ?>" alt="" width="52" height="52">

					<?php
					// The SAME component the site renders. Not a copy: a copy is how the
					// footer mark drifted, and how this hero drifted before it.
					murtadd_the_logo( 'on-dark', false );
					?>

					<span class="murtadd-admin-version">v<?php echo esc_html( MURTADD_VERSION ); ?></span>
				</div>
				<p class="murtadd-admin-tagline"><?php esc_html_e( 'Doubts, taken seriously. Theme options.', 'murtadd' ); ?></p>
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
		<p class="description"><?php esc_html_e( 'Colours, cross-site links, content behavior, and taxonomy management.', 'murtadd' ); ?></p>
		<nav class="nav-tab-wrapper murtadd-admin-tabs">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'murtadd', 'tab' => $slug ), admin_url( 'themes.php' ) ) ); ?>" class="nav-tab <?php echo $active === $slug ? 'nav-tab-active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>

		<div class="murtadd-admin-panel">
		<?php if ( 'setup' === $active ) : ?>
			<?php if ( isset( $_GET['murtadd_done'] ) ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Setup complete.', 'murtadd' ); ?></p></div>
			<?php endif; ?>

			<h2><?php esc_html_e( 'Site scaffolding', 'murtadd' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'The theme creates these on activation. Upgrading the theme in place does not re-run that, so this button does it without switching themes away and back. It never overwrites: anything already present is left exactly as it is.', 'murtadd' ); ?>
			</p>

			<?php $murtadd_missing = murtadd_missing_pages(); ?>

			<table class="widefat striped" style="max-width:640px;margin:16px 0;">
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
									<a href="<?php echo esc_url( get_edit_post_link( $murtadd_existing->ID ) ); ?>"><?php esc_html_e( 'Exists — edit', 'murtadd' ); ?></a>
								<?php else : ?>
									<span style="color:#8a4b00;font-weight:600;"><?php esc_html_e( 'Missing', 'murtadd' ); ?></span>
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
								<span style="color:#8a4b00;font-weight:600;"><?php esc_html_e( 'Not assigned', 'murtadd' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td>
							<strong><?php esc_html_e( 'Starter content', 'murtadd' ); ?></strong>
							<code>
								<?php
								printf(
									/* translators: 1: doubt count, 2: rebuttal count, 3: letter count */
									esc_html__( '%1$d doubts, %2$d rebuttals, %3$d placeholder letters', 'murtadd' ),
									count( murtadd_seed_doubts() ),
									count( murtadd_seed_rebuttals() ),
									count( murtadd_seed_letters() )
								);
								?>
							</code>
						</td>
						<td>
							<?php $murtadd_missing_content = murtadd_seed_missing_count(); ?>
							<?php if ( 0 === $murtadd_missing_content ) : ?>
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=murtadd_doubt' ) ); ?>"><?php esc_html_e( 'All present — view', 'murtadd' ); ?></a>
							<?php else : ?>
								<span style="color:#8a4b00;font-weight:600;">
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
								<span style="color:#8a4b00;font-weight:600;"><?php esc_html_e( 'Not fully set', 'murtadd' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				</tbody>
			</table>

			<?php if ( $murtadd_missing ) : ?>
				<p><?php esc_html_e( 'The "Start here" button in the sidebar renders only when the start-here page exists. That is why it is absent right now.', 'murtadd' ); ?></p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="murtadd_run_setup">
				<?php wp_nonce_field( 'murtadd_run_setup' ); ?>
				<?php submit_button( __( 'Create anything missing', 'murtadd' ), 'primary', 'submit', false ); ?>
			</form>

			<p class="description" style="margin-top:12px;">
				<?php esc_html_e( 'New pages are published immediately and carry placeholder text. Replace the copy before announcing the site. Starter content is published as written and can be edited freely: nothing here ever overwrites a post that already exists, so editing a doubt and running this again will not undo the edit.', 'murtadd' ); ?>
			</p>

		<?php elseif ( 'taxonomies' === $active ) : ?>
			<h2><?php esc_html_e( 'Term management', 'murtadd' ); ?></h2>
			<p><?php esc_html_e( 'Terms are managed on the native WordPress screens — nothing is duplicated here.', 'murtadd' ); ?></p>
			<ul>
				<li><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=murtadd_topic&post_type=murtadd_doubt' ) ); ?>"><?php esc_html_e( 'Manage Topics →', 'murtadd' ); ?></a></li>
				<li><a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=murtadd_school&post_type=murtadd_fatwa' ) ); ?>"><?php esc_html_e( 'Manage Schools →', 'murtadd' ); ?></a></li>
				<li><?php esc_html_e( 'Doubt categories are locked — four fixed terms power the homepage grid.', 'murtadd' ); ?></li>
			</ul>
		<?php elseif ( 'profiles' === $active ) : ?>
			<h2><?php esc_html_e( 'Profiles', 'murtadd' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'A link for any platform the author is on. Every URL entered here does two jobs at once: it appears as an icon in the site footer, and it is emitted as a sameAs entry on the author in the structured data, so search engines and AI systems can connect this site to the same person elsewhere. Leave a field blank and it appears in neither place.', 'murtadd' ); ?>
			</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'murtadd_profiles' ); ?>
				<?php $murtadd_profiles = get_option( 'murtadd_profiles', array() ); ?>
				<table class="form-table" role="presentation"><tbody><tr>
					<th scope="row"><label for="murtadd_author_name"><?php esc_html_e( 'Author name', 'murtadd' ); ?></label></th>
					<td><input type="text" id="murtadd_author_name" name="murtadd_author_name" value="<?php echo esc_attr( get_option( 'murtadd_author_name', '' ) ); ?>" class="regular-text">
					<br><span class="description"><?php esc_html_e( 'The person these profiles belong to. Named as the author in the structured data, with the links below as their sameAs set. Leave blank to attribute everything to the site instead.', 'murtadd' ); ?></span></td>
				</tr></tbody></table>
				<table class="form-table murtadd-profiles-table" role="presentation">
					<tbody>
					<?php foreach ( murtadd_social_registry() as $murtadd_slug => $murtadd_meta ) : ?>
						<tr>
							<th scope="row">
								<span class="murtadd-profiles-icon"><?php echo murtadd_social_icon( $murtadd_slug, 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- registry SVG. ?></span>
								<label for="murtadd_profile_<?php echo esc_attr( $murtadd_slug ); ?>"><?php echo esc_html( $murtadd_meta['label'] ); ?></label>
							</th>
							<td>
								<input
									type="url"
									id="murtadd_profile_<?php echo esc_attr( $murtadd_slug ); ?>"
									name="murtadd_profiles[<?php echo esc_attr( $murtadd_slug ); ?>]"
									value="<?php echo esc_attr( $murtadd_profiles[ $murtadd_slug ] ?? '' ); ?>"
									class="regular-text"
									placeholder="https://">
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>

		<?php elseif ( 'docs' === $active ) : ?>
			<h2><?php esc_html_e( 'Documentation', 'murtadd' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'The theme\'s own manual, rendered from the markdown that ships inside it. There is no second copy to fall out of date.', 'murtadd' ); ?>
			</p>
			<?php
			$murtadd_doc = isset( $_GET['doc'] ) ? sanitize_key( wp_unslash( $_GET['doc'] ) ) : '';
			murtadd_docs_render_tab( $murtadd_doc );
			?>

		<?php else : ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'murtadd_' . $active );
				if ( 'colours' === $active ) {
					$o = get_option( 'murtadd_colours', array() );
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="murtadd_accent"><?php esc_html_e( 'Accent colour', 'murtadd' ); ?></label></th>
							<td>
								<input type="text" id="murtadd_accent" name="murtadd_colours[accent]" value="<?php echo esc_attr( $o['accent'] ?? '#0f6e56' ); ?>" class="regular-text" data-murtadd-contrast>
								<p class="description"><?php esc_html_e( 'Used for links, buttons, and the sidebar. Applied via wp_add_inline_style.', 'murtadd' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="murtadd_accent_tint"><?php esc_html_e( 'Accent tint', 'murtadd' ); ?></label></th>
							<td><input type="text" id="murtadd_accent_tint" name="murtadd_colours[accent_tint]" value="<?php echo esc_attr( $o['accent_tint'] ?? '#eefaf7' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Contrast check', 'murtadd' ); ?></th>
							<td><span id="murtadd-contrast-result" aria-live="polite"></span></td>
						</tr>
					</table>
					<?php
				} elseif ( 'cross_links' === $active ) {
					$o = get_option( 'murtadd_cross_links', array() );
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="murtadd_ce"><?php esc_html_e( 'Evidential case base URL', 'murtadd' ); ?></label></th>
							<td>
								<input type="url" id="murtadd_ce" name="murtadd_cross_links[ce_base_url]" value="<?php echo esc_url( $o['ce_base_url'] ?? 'https://compelling-evidence.com' ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'Used by the homepage "full argument" card and doubt pages\' outbound deep links. Cleared = links hidden.', 'murtadd' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="murtadd_bismika"><?php esc_html_e( 'Polemics base URL', 'murtadd' ); ?></label></th>
							<td>
								<input type="url" id="murtadd_bismika" name="murtadd_cross_links[bismika_base_url]" value="<?php echo esc_url( $o['bismika_base_url'] ?? 'https://bismikaallahuma.org' ); ?>" class="regular-text">
								<p class="description"><?php esc_html_e( 'All outbound links open in a new tab with rel="noopener". No relationship is stated on the frontend.', 'murtadd' ); ?></p>
							</td>
						</tr>
					</table>
					<?php
				} else {
					$o = get_option( 'murtadd_content_display', array() );
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="murtadd_cap"><?php esc_html_e( 'Short response word cap', 'murtadd' ); ?></label></th>
							<td>
								<input type="number" id="murtadd_cap" name="murtadd_content_display[short_response_word_cap]" value="<?php echo esc_attr( $o['short_response_word_cap'] ?? 400 ); ?>" min="50" step="10" class="small-text">
								<p class="description"><?php esc_html_e( 'Soft cap. The doubt editor shows a live word count and warns past this — it never blocks saving.', 'murtadd' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( '"If you are struggling" notice', 'murtadd' ); ?></th>
							<td>
								<label><input type="checkbox" name="murtadd_content_display[show_struggling_notice]" value="1" <?php checked( ! empty( $o['show_struggling_notice'] ?? 1 ) ); ?>> <?php esc_html_e( 'Show on Emotional and Identity doubt pages', 'murtadd' ); ?></label>
								<p class="description"><?php esc_html_e( 'A quiet crisis-support block. No tracking, no preaching.', 'murtadd' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="murtadd_sort"><?php esc_html_e( 'Fatwa index default sort', 'murtadd' ); ?></label></th>
							<td>
								<select id="murtadd_sort" name="murtadd_content_display[fatwa_index_default_sort]">
									<option value="scholar_az" <?php selected( $o['fatwa_index_default_sort'] ?? '', 'scholar_az' ); ?>><?php esc_html_e( 'Scholar A–Z', 'murtadd' ); ?></option>
									<option value="era" <?php selected( $o['fatwa_index_default_sort'] ?? '', 'era' ); ?>><?php esc_html_e( 'Era', 'murtadd' ); ?></option>
									<option value="recently_added" <?php selected( $o['fatwa_index_default_sort'] ?? '', 'recently_added' ); ?>><?php esc_html_e( 'Recently added', 'murtadd' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="murtadd_heritage"><?php esc_html_e( 'Footer heritage note', 'murtadd' ); ?></label></th>
							<td><input type="text" id="murtadd_heritage" name="murtadd_content_display[footer_heritage_note]" value="<?php echo esc_attr( $o['footer_heritage_note'] ?? 'online since 2004' ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Scripture box', 'murtadd' ); ?></th>
							<td>
								<label>
									<input type="checkbox" name="murtadd_content_display[show_scripture]" value="1" <?php checked( ! isset( $o['show_scripture'] ) || $o['show_scripture'] ); ?>>
									<?php esc_html_e( 'Show the scripture box in the rail on doubt and rebuttal pages', 'murtadd' ); ?>
								</label>
								<p style="margin-top:10px;">
									<input type="text" name="murtadd_content_display[scripture_text]" value="<?php echo esc_attr( $o['scripture_text'] ?? __( 'Let there be no compulsion in religion.', 'murtadd' ) ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'The verse, in whichever rendering you prefer', 'murtadd' ); ?>">
								</p>
								<p>
									<input type="text" name="murtadd_content_display[scripture_ref]" value="<?php echo esc_attr( $o['scripture_ref'] ?? __( 'Qur’an 2:256', 'murtadd' ) ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Reference', 'murtadd' ); ?>">
								</p>
								<p class="description">
									<?php esc_html_e( 'Choose the rendering yourself. The wording of a translation is an editorial decision and belongs to you, not to the theme.', 'murtadd' ); ?>
								</p>
							</td>
						</tr>
					</table>
					<?php
				}
				submit_button();
				?>
			</form>
		<?php endif; ?>
		</div><!-- .murtadd-admin-panel -->
	</div>
	<?php
}
