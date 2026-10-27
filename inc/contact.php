<?php
/**
 * Contact form: the [murtadd_contact] shortcode.
 *
 * The owner publishes no address, so the Contact page carries a form and the
 * message goes by wp_mail() to the administration email address, or to the
 * address set on the Content tab. Nothing a reader sends is stored on the
 * site: the message exists only in the email.
 *
 * Protection, each step pinned in tests/ContactTest.php:
 *   1. a nonce, so the form cannot be posted from elsewhere;
 *   2. a honeypot field, hidden from people and filled by bots; a filled one
 *      is answered as though it were sent, and nothing is mailed;
 *   3. a per-address rate limit kept in a transient, keyed by a keyed hash of
 *      the IP address, so the address itself is never written anywhere;
 *   4. every field sanitised and capped before it reaches the email.
 *
 * Works without JavaScript: the form posts to the page itself, is handled on
 * template_redirect, and a sent message redirects back with a notice, so a
 * reload cannot send it twice. On an error the page renders again with the
 * reader's words still in the fields.
 *
 * @package murtadd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Limits. Filterable: murtadd_contact_limits.
 *
 * @return array{per_hour:int,name:int,message:int}
 */
function murtadd_contact_limits() {
	$limits = array(
		'per_hour' => 3,    // Messages one address may send in an hour.
		'name'     => 100,  // Characters.
		'message'  => 5000, // Characters.
	);
	return array_map( 'absint', (array) apply_filters( 'murtadd_contact_limits', $limits ) ) + $limits;
}

/**
 * Where messages go: the Content tab's address when it is a valid one, the
 * site's administration email otherwise.
 *
 * @return string
 */
function murtadd_contact_recipient() {
	$o  = get_option( 'murtadd_content_display', array() );
	$to = is_array( $o ) && ! empty( $o['contact_recipient'] ) ? (string) $o['contact_recipient'] : '';
	if ( '' === $to || ! is_email( $to ) ) {
		$to = (string) get_option( 'admin_email' );
	}
	return $to;
}

/**
 * Clean the recipient override for saving: a valid address, or empty.
 *
 * @param mixed $value Submitted value.
 * @return string
 */
function murtadd_contact_clean_recipient( $value ) {
	$email = sanitize_email( trim( (string) $value ) );
	return ( '' !== $email && is_email( $email ) ) ? $email : '';
}

/**
 * A keyed hash of the visitor's IP address. Only this is ever stored.
 *
 * @param string|null $ip Address; the request's own when null.
 * @return string 64 hex characters.
 */
function murtadd_contact_ip_hash( $ip = null ) {
	if ( null === $ip ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}
	return hash_hmac( 'sha256', 'murtadd-contact|' . (string) $ip, wp_salt( 'nonce' ) );
}

/**
 * The transient that counts one address's messages.
 *
 * @param string $hash From murtadd_contact_ip_hash().
 * @return string
 */
function murtadd_contact_rate_key( $hash ) {
	return 'murtadd_contact_' . substr( (string) $hash, 0, 32 );
}

/**
 * Whether the address has used up its allowance this hour.
 *
 * @param string $hash From murtadd_contact_ip_hash().
 * @return bool
 */
function murtadd_contact_is_limited( $hash ) {
	return (int) get_transient( murtadd_contact_rate_key( $hash ) ) >= murtadd_contact_limits()['per_hour'];
}

/**
 * Count one message against the address.
 *
 * @param string $hash From murtadd_contact_ip_hash().
 */
function murtadd_contact_count( $hash ) {
	$key = murtadd_contact_rate_key( $hash );
	set_transient( $key, (int) get_transient( $key ) + 1, HOUR_IN_SECONDS );
}

/**
 * Sanitise the submitted fields.
 *
 * @param array $raw Unslashed $_POST.
 * @return array{name:string,email:string,email_given:bool,message:string}
 */
function murtadd_contact_sanitise( $raw ) {
	$limits  = murtadd_contact_limits();
	$name    = sanitize_text_field( (string) ( $raw['murtadd_contact_name'] ?? '' ) );
	$given   = trim( (string) ( $raw['murtadd_contact_email'] ?? '' ) );
	$email   = sanitize_email( $given );
	$message = sanitize_textarea_field( (string) ( $raw['murtadd_contact_message'] ?? '' ) );

	return array(
		'name'        => trim( mb_substr( $name, 0, $limits['name'] ) ),
		'email'       => ( '' !== $email && is_email( $email ) ) ? $email : '',
		'email_given' => '' !== $given,
		'message'     => trim( mb_substr( $message, 0, $limits['message'] ) ),
	);
}

/**
 * Handle one submission. Pure of the request: everything comes in $raw.
 *
 * @param array       $raw  Unslashed $_POST.
 * @param string|null $hash IP hash; the request's own when null.
 * @return array{status:string,errors:string[],fields:array}
 *   status: sent, nonce, limited, invalid or failed.
 */
function murtadd_contact_process( $raw, $hash = null ) {
	$raw    = is_array( $raw ) ? $raw : array();
	$fields = murtadd_contact_sanitise( $raw );
	$result = array(
		'status' => 'invalid',
		'errors' => array(),
		'fields' => $fields,
	);

	$nonce = sanitize_text_field( (string) ( $raw['murtadd_contact_nonce'] ?? '' ) );
	if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'murtadd_contact' ) ) {
		$result['status'] = 'nonce';
		return $result;
	}

	$hash = null === $hash ? murtadd_contact_ip_hash() : (string) $hash;
	if ( murtadd_contact_is_limited( $hash ) ) {
		$result['status'] = 'limited';
		return $result;
	}

	// A filled honeypot is a bot. Tell it the message went; send nothing.
	if ( '' !== trim( (string) ( $raw['murtadd_contact_url'] ?? '' ) ) ) {
		murtadd_contact_count( $hash );
		$result['status'] = 'sent';
		return $result;
	}

	if ( '' === $fields['message'] ) {
		$result['errors'][] = 'message';
	}
	if ( $fields['email_given'] && '' === $fields['email'] ) {
		$result['errors'][] = 'email';
	}
	if ( $result['errors'] ) {
		return $result;
	}

	murtadd_contact_count( $hash );

	$site    = wp_specialchars_decode( (string) get_bloginfo( 'name' ), ENT_QUOTES );
	$subject = sprintf(
		/* translators: %s: site name */
		__( '[%s] A message from the contact form', 'murtadd' ),
		$site
	);
	$body = implode(
		"\n",
		array(
			/* translators: %s: the sender's name, or "not given" */
			sprintf( __( 'Name: %s', 'murtadd' ), '' !== $fields['name'] ? $fields['name'] : __( 'not given', 'murtadd' ) ),
			/* translators: %s: the sender's email address, or "not given" */
			sprintf( __( 'Email: %s', 'murtadd' ), '' !== $fields['email'] ? $fields['email'] : __( 'not given', 'murtadd' ) ),
			'',
			$fields['message'],
			'',
			'-- ',
			/* translators: %s: site address */
			sprintf( __( 'Sent from the contact form at %s. The site keeps no copy of this message.', 'murtadd' ), home_url( '/' ) ),
		)
	);
	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	if ( '' !== $fields['email'] ) {
		$headers[] = 'Reply-To: ' . $fields['email'];
	}

	$result['status'] = wp_mail( murtadd_contact_recipient(), $subject, $body, $headers ) ? 'sent' : 'failed';
	return $result;
}

/**
 * The result of this request's submission, if there was one.
 *
 * @param array|null $set Store a result.
 * @return array|null
 */
function murtadd_contact_result( $set = null ) {
	static $result = null;
	if ( null !== $set ) {
		$result = $set;
	}
	return $result;
}

/**
 * Handle a posted form before the page renders.
 */
function murtadd_contact_handle_post() {
	if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! isset( $_POST['murtadd_contact_submit'] ) || ! is_singular() ) {
		return;
	}
	$result = murtadd_contact_process( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified inside, every field sanitised there.
	if ( 'sent' === $result['status'] ) {
		wp_safe_redirect( add_query_arg( 'murtadd_contact', 'sent', get_permalink() ) . '#murtadd-contact' );
		exit;
	}
	murtadd_contact_result( $result );
}
add_action( 'template_redirect', 'murtadd_contact_handle_post' );

/**
 * The notice text for a status or field error.
 *
 * @param string $code Status or error code.
 * @return string
 */
function murtadd_contact_message( $code ) {
	$messages = array(
		'sent'    => __( 'Thank you. Your message has been sent. If you gave an email address, any reply will come to it.', 'murtadd' ),
		'nonce'   => __( 'The form had expired, so nothing was sent. Please send your message again.', 'murtadd' ),
		'limited' => __( 'Several messages have already come from your connection in the last hour, so this one was not sent. Please try again later.', 'murtadd' ),
		'failed'  => __( 'Your message could not be sent just now. Nothing was lost on your side: your words are still below, and you can try again in a while.', 'murtadd' ),
		'message' => __( 'Please write a message before sending.', 'murtadd' ),
		'email'   => __( 'That email address does not look complete. Correct it, or leave the field empty.', 'murtadd' ),
	);
	return $messages[ $code ] ?? '';
}

/**
 * Render the form.
 *
 * @return string
 */
function murtadd_contact_shortcode() {
	$limits = murtadd_contact_limits();
	$result = murtadd_contact_result();
	$fields = $result ? $result['fields'] : array(
		'name'    => '',
		'email'   => '',
		'message' => '',
	);
	if ( $result && in_array( 'email', $result['errors'], true ) ) {
		$fields['email'] = sanitize_text_field( wp_unslash( $_POST['murtadd_contact_email'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- shown back to the reader, escaped below.
	}
	$sent = ! $result && isset( $_GET['murtadd_contact'] ) && 'sent' === sanitize_key( wp_unslash( $_GET['murtadd_contact'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only.

	$notices = array();
	if ( $sent ) {
		$notices[] = array( 'success', murtadd_contact_message( 'sent' ) );
	} elseif ( $result ) {
		$codes = 'invalid' === $result['status'] ? $result['errors'] : array( $result['status'] );
		foreach ( $codes as $code ) {
			$notices[] = array( 'error', murtadd_contact_message( $code ) );
		}
	}

	ob_start();
	?>
	<div class="murtadd-contact" id="murtadd-contact">
		<?php foreach ( $notices as $notice ) : ?>
			<p class="murtadd-contact__notice murtadd-contact__notice--<?php echo esc_attr( $notice[0] ); ?>" role="<?php echo 'error' === $notice[0] ? 'alert' : 'status'; ?>"><?php echo esc_html( $notice[1] ); ?></p>
		<?php endforeach; ?>

		<form class="murtadd-contact__form" method="post" action="<?php echo esc_url( get_permalink() . '#murtadd-contact' ); ?>">
			<?php wp_nonce_field( 'murtadd_contact', 'murtadd_contact_nonce', false ); ?>

			<p class="murtadd-contact__field">
				<label for="murtadd-contact-name"><?php esc_html_e( 'Your name', 'murtadd' ); ?> <span class="murtadd-contact__hint"><?php esc_html_e( '(optional)', 'murtadd' ); ?></span></label>
				<input type="text" id="murtadd-contact-name" name="murtadd_contact_name" value="<?php echo esc_attr( $fields['name'] ); ?>" maxlength="<?php echo (int) $limits['name']; ?>" autocomplete="name">
			</p>

			<p class="murtadd-contact__field">
				<label for="murtadd-contact-email"><?php esc_html_e( 'Your email', 'murtadd' ); ?> <span class="murtadd-contact__hint"><?php esc_html_e( '(optional, only if you would like a reply)', 'murtadd' ); ?></span></label>
				<input type="email" id="murtadd-contact-email" name="murtadd_contact_email" value="<?php echo esc_attr( $fields['email'] ); ?>" maxlength="254" autocomplete="email">
			</p>

			<p class="murtadd-contact__field">
				<label for="murtadd-contact-message"><?php esc_html_e( 'Your message', 'murtadd' ); ?></label>
				<textarea id="murtadd-contact-message" name="murtadd_contact_message" rows="8" maxlength="<?php echo (int) $limits['message']; ?>" required><?php echo esc_textarea( $fields['message'] ); ?></textarea>
			</p>

			<div class="murtadd-contact__trap" aria-hidden="true">
				<label for="murtadd-contact-url"><?php esc_html_e( 'Leave this field empty', 'murtadd' ); ?></label>
				<input type="text" id="murtadd-contact-url" name="murtadd_contact_url" value="" tabindex="-1" autocomplete="off">
			</div>

			<p class="murtadd-contact__actions">
				<button type="submit" class="murtadd-contact__submit" name="murtadd_contact_submit" value="1"><?php esc_html_e( 'Send message', 'murtadd' ); ?></button>
			</p>
			<p class="murtadd-contact__privacy">
				<?php esc_html_e( 'Your message is sent by email to the people who run this site and is not stored here.', 'murtadd' ); ?>
				<a href="<?php echo esc_url( home_url( '/privacy/' ) ); ?>"><?php esc_html_e( 'Privacy', 'murtadd' ); ?></a>
			</p>
		</form>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'murtadd_contact', 'murtadd_contact_shortcode' );

/**
 * Whether the queried page carries the form.
 *
 * @return bool
 */
function murtadd_contact_on_page() {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	return isset( $post->post_content ) && has_shortcode( (string) $post->post_content, 'murtadd_contact' );
}

/**
 * Load the form's stylesheet and script only where the form is.
 */
function murtadd_contact_assets() {
	if ( ! murtadd_contact_on_page() ) {
		return;
	}
	wp_enqueue_style( 'murtadd-contact', get_stylesheet_directory_uri() . '/assets/css/contact' . MURTADD_ASSET_SUFFIX . '.css', array( 'murtadd-main' ), MURTADD_VERSION );
	wp_enqueue_script( 'murtadd-contact', get_stylesheet_directory_uri() . '/assets/js/contact' . MURTADD_ASSET_SUFFIX . '.js', array(), MURTADD_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'murtadd_contact_assets', 20 );

/**
 * A page holding a nonce must not be served from a full-page cache.
 */
function murtadd_contact_nocache() {
	if ( murtadd_contact_on_page() ) {
		nocache_headers();
	}
}
add_action( 'template_redirect', 'murtadd_contact_nocache', 5 );
