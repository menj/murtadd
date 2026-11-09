/* Fatwa index filters (WCAG 3.2.2 On Input).
   Changing a select never submits the form: moving through the options with the
   arrow keys would reload the page at every step. The Filter button stays visible
   and is the one way to apply the choices, with or without JavaScript. The script
   only marks the form as changed so the button can say so to sighted users. */
( function () {
	'use strict';
	var form = document.querySelector( '.murtadd-fatwa-filters' );
	if ( ! form ) {
		return;
	}
	form.querySelectorAll( 'select' ).forEach( function ( select ) {
		select.addEventListener( 'change', function () {
			form.classList.add( 'is-changed' );
		} );
	} );
}() );
