/**
 * Contact form: stop a double click sending the message twice.
 *
 * The form works without this file; it only disables the button once the
 * browser has accepted the form for sending.
 */
( function () {
	'use strict';
	var form = document.querySelector( '.murtadd-contact__form' );
	if ( ! form ) {
		return;
	}
	form.addEventListener( 'submit', function () {
		var button = form.querySelector( '.murtadd-contact__submit' );
		if ( button ) {
			window.setTimeout( function () {
				button.disabled = true;
				button.setAttribute( 'aria-busy', 'true' );
			}, 0 );
		}
	} );
}() );
