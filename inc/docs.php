<?php
/**
 * Documentation viewer — Appearance → Murtadd → Docs.
 *
 * Renders the theme's own /docs/*.md, so the manual and the code ship in one
 * artefact and cannot drift apart. There is no second copy to update: edit the
 * markdown, and the admin screen changes with it.
 *
 * Markdown is parsed by the vendored Parsedown (MIT, erusev), run in safe mode
 * and then passed through wp_kses(). Safe mode alone strips scripts and
 * javascript: URLs; kses is the second belt, because the docs are files on
 * disk and anything with write access to them can already do worse — but a
 * viewer that renders arbitrary disk content into an admin page should not be
 * the weakest link in that chain.
 *
 * @package Murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * The documents, in reading order, keyed by slug.
 *
 * Reading order, not alphabetical: someone opening this tab is either setting
 * the site up or looking something up, and setup comes first for the former
 * while the reference sits mid-list for the latter. Changelog and upgrading
 * live at the end, where you go deliberately.
 *
 * @return array<string,array{file:string,label:string,blurb:string}>
 */
function murtadd_docs_manifest() {
	return array(
		'readme'        => array(
			'file'  => 'readme.md',
			'label' => __( 'Overview', 'murtadd' ),
			'blurb' => __( 'What the theme is and what it assumes.', 'murtadd' ),
		),
		'setup'         => array(
			'file'  => 'setup.md',
			'label' => __( 'Setup', 'murtadd' ),
			'blurb' => __( 'Getting a site standing from a fresh activation.', 'murtadd' ),
		),
		'content-model' => array(
			'file'  => 'content-model.md',
			'label' => __( 'Content model', 'murtadd' ),
			'blurb' => __( 'The post types, their fields, and how they link.', 'murtadd' ),
		),
		'settings'      => array(
			'file'  => 'settings.md',
			'label' => __( 'Settings', 'murtadd' ),
			'blurb' => __( 'Every option on this screen, explained.', 'murtadd' ),
		),
		'ssot'          => array(
			'file'  => 'ssot.md',
			'label' => __( 'Reference', 'murtadd' ),
			'blurb' => __( 'The single source of truth: hooks, options, templates.', 'murtadd' ),
		),
		'upgrading'     => array(
			'file'  => 'upgrading.md',
			'label' => __( 'Upgrading', 'murtadd' ),
			'blurb' => __( 'Version-to-version steps, including manual ones.', 'murtadd' ),
		),
		'changelog'     => array(
			'file'  => 'changelog.md',
			'label' => __( 'Changelog', 'murtadd' ),
			'blurb' => __( 'What changed, when, and why.', 'murtadd' ),
		),
	);
}

/**
 * Absolute path to a document, or '' if the slug is unknown or the file is gone.
 *
 * realpath() plus a prefix check, so a manifest edit can never point outside
 * the theme's own docs directory.
 *
 * @param string $slug Document slug.
 * @return string
 */
function murtadd_docs_path( $slug ) {
	$manifest = murtadd_docs_manifest();
	if ( ! isset( $manifest[ $slug ] ) ) {
		return '';
	}

	$dir  = realpath( get_stylesheet_directory() . '/docs' );
	$path = realpath( get_stylesheet_directory() . '/docs/' . $manifest[ $slug ]['file'] );

	if ( ! $dir || ! $path || 0 !== strpos( $path, $dir . DIRECTORY_SEPARATOR ) ) {
		return '';
	}

	return $path;
}

/**
 * Render one document to sanitised HTML.
 *
 * @param string $slug Document slug.
 * @return string HTML, or '' if the document cannot be read.
 */
function murtadd_docs_render( $slug ) {
	$path = murtadd_docs_path( $slug );
	if ( ! $path || ! is_readable( $path ) ) {
		return '';
	}

	$markdown = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Local theme file, not a remote fetch.
	if ( false === $markdown ) {
		return '';
	}

	if ( ! class_exists( 'Parsedown' ) ) {
		require_once get_stylesheet_directory() . '/vendor/parsedown/Parsedown.php';
	}

	$parser = new Parsedown();
	$parser->setSafeMode( true );
	$parser->setBreaksEnabled( false );

	$allowed = array(
		'h1'     => array( 'id' => true ),
		'h2'     => array( 'id' => true ),
		'h3'     => array( 'id' => true ),
		'h4'     => array( 'id' => true ),
		'h5'     => array( 'id' => true ),
		'h6'     => array( 'id' => true ),
		'p'      => array(),
		'a'      => array(
			'href'   => true,
			'title'  => true,
			'rel'    => true,
			'target' => true,
		),
		'ul'     => array(),
		'ol'     => array( 'start' => true ),
		'li'     => array(),
		'strong' => array(),
		'em'     => array(),
		'code'   => array( 'class' => true ),
		'pre'    => array( 'class' => true ),
		'blockquote' => array(),
		'hr'     => array(),
		'br'     => array(),
		'table'  => array(),
		'thead'  => array(),
		'tbody'  => array(),
		'tr'     => array(),
		'th'     => array( 'align' => true ),
		'td'     => array( 'align' => true ),
		'del'    => array(),
		'img'    => array(
			'src' => true,
			'alt' => true,
		),
	);

	return wp_kses( $parser->text( $markdown ), $allowed );
}

/**
 * Render the Docs tab.
 *
 * @param string $active_doc Slug of the document to show.
 * @return void
 */
function murtadd_docs_render_tab( $active_doc ) {
	$manifest = murtadd_docs_manifest();
	if ( ! isset( $manifest[ $active_doc ] ) ) {
		$active_doc = (string) array_key_first( $manifest );
	}

	$html = murtadd_docs_render( $active_doc );
	?>
	<div class="murtadd-docs">
		<nav class="murtadd-docs-nav" aria-label="<?php esc_attr_e( 'Documentation', 'murtadd' ); ?>">
			<ul>
				<?php foreach ( $manifest as $slug => $doc ) : ?>
					<?php $missing = '' === murtadd_docs_path( $slug ); ?>
					<li>
						<a
							href="<?php echo esc_url( add_query_arg( array( 'page' => 'murtadd', 'tab' => 'docs', 'doc' => $slug ), admin_url( 'themes.php' ) ) ); ?>"
							class="<?php echo $active_doc === $slug ? 'is-active' : ''; ?>"
							<?php echo $active_doc === $slug ? 'aria-current="page"' : ''; ?>
						>
							<span class="murtadd-docs-nav-label"><?php echo esc_html( $doc['label'] ); ?></span>
							<span class="murtadd-docs-nav-blurb"><?php echo esc_html( $missing ? __( 'File missing from this build.', 'murtadd' ) : $doc['blurb'] ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>

		<article class="murtadd-docs-body">
			<?php if ( $html ) : ?>
				<?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Parsedown safe mode + wp_kses in murtadd_docs_render(). ?>
			<?php else : ?>
				<div class="notice notice-warning inline">
					<p>
						<?php
						printf(
							/* translators: %s: file name, e.g. setup.md */
							esc_html__( 'Could not read %s. The docs directory ships with the theme; if it was removed to save space, restore it from the original archive.', 'murtadd' ),
							'<code>' . esc_html( $manifest[ $active_doc ]['file'] ) . '</code>'
						);
						?>
					</p>
				</div>
			<?php endif; ?>
		</article>
	</div>
	<?php
}
