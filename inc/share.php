<?php
/**
 * Share row.
 *
 * Half of what this site is for is a person sending a page to someone who is
 * struggling, or a struggling person sending it to a friend. Until now there
 * was no way to do that.
 *
 * Plain anchor links and inline SVG throughout. No third-party scripts, no
 * SDKs, no pixels, nothing that phones home. The support notice on this site
 * promises "no preaching, no tracking", and a share widget that loaded
 * Facebook's SDK would make that a lie on the very page it is printed on.
 *
 * WhatsApp is first, deliberately: in Malaysia that is how a link actually
 * travels between two people who trust each other.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG icons.
 *
 * @param string $slug Icon slug.
 * @return string
 */
/*
 * The WhatsApp and Telegram marks are the Simple Icons glyphs (CC0), taken
 * from the minimalist social pack and matched to the same 24x24 viewBox as
 * the hand-drawn email and copy icons beside them. Email and copy are not
 * brand marks and stay hand-drawn. The full pack is not vendored: a private
 * share row with no social accounts has no use for eighty other logos.
 */
function murtadd_share_icon( $slug ) {
	$icons = array(
		'whatsapp' => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>',
		'telegram' => '<path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>',
		'email'    => '<path d="M3 5h18a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1zm9 7.2L4.4 7H19.6L12 12.2zM4 8.6V17h16V8.6l-7.4 5.1a1 1 0 0 1-1.2 0L4 8.6z"/>',
		'copy'     => '<path d="M9 2h9a2 2 0 0 1 2 2v11h-2V4H9V2zM5 6h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2zm0 2v12h10V8H5z"/>',
	);

	if ( ! isset( $icons[ $slug ] ) ) {
		return '';
	}

	return '<svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true" focusable="false" fill="currentColor">' . $icons[ $slug ] . '</svg>';
}

/**
 * Render the share row.
 *
 * @param int $post_id Post ID.
 */
function murtadd_share_row( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	$url = get_permalink( $post_id );

	// A Doubt's real title is its statement; that is what should arrive in the
	// message, in the reader's own words.
	$title = get_post_meta( $post_id, '_murtadd_doubt_statement', true );
	if ( ! $title ) {
		$title = get_the_title( $post_id );
	}

	$share = rawurlencode( $title . ' — ' . $url );

	$links = array(
		'whatsapp' => array(
			'label' => __( 'WhatsApp', 'murtadd' ),
			'href'  => 'https://wa.me/?text=' . $share,
		),
		'telegram' => array(
			'label' => __( 'Telegram', 'murtadd' ),
			'href'  => 'https://t.me/share/url?url=' . rawurlencode( $url ) . '&text=' . rawurlencode( $title ),
		),
		'email'    => array(
			'label' => __( 'Email', 'murtadd' ),
			'href'  => 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . $share,
		),
	);
	?>
	<div class="murtadd-share">
		<h2 class="murtadd-share-label"><?php esc_html_e( 'Send this to someone', 'murtadd' ); ?></h2>
		<ul class="murtadd-share-list">
			<?php foreach ( $links as $slug => $link ) : ?>
				<li>
					<a class="murtadd-share-link" href="<?php echo esc_url( $link['href'] ); ?>" target="_blank" rel="noopener">
						<?php echo murtadd_share_icon( $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG. ?>
						<span><?php echo esc_html( $link['label'] ); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
			<li>
				<button type="button" class="murtadd-share-link murtadd-share-copy" data-url="<?php echo esc_url( $url ); ?>">
					<?php echo murtadd_share_icon( 'copy' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG. ?>
					<span><?php esc_html_e( 'Copy link', 'murtadd' ); ?></span>
				</button>
			</li>
		</ul>
		<p class="murtadd-share-note"><?php esc_html_e( 'These are plain links. Nothing here tracks you, and nobody is told that you shared it.', 'murtadd' ); ?></p>
	</div>
	<?php
}
