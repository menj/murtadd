/* Fatwa index: progressive enhancement — submit filters on change,
   no button click needed. With JS disabled the form works as plain GET. */
( function () {
	'use strict';
	var form = document.querySelector( '.murtadd-fatwa-filters' );
	if ( ! form ) {
		return;
	}
	form.querySelectorAll( 'select' ).forEach( function ( select ) {
		select.addEventListener( 'change', function () {
			form.submit();
		} );
	} );
	var button = form.querySelector( 'button[type="submit"]' );
	if ( button ) {
		button.style.display = 'none';
	}
}() );
