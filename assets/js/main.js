/* Murtadd — frontend. Progressive enhancement only; everything works without JS. */
( function () {
	'use strict';

	// Mobile nav toggle.
	var toggle = document.querySelector( '.murtadd-nav-toggle' );
	var sidebar = document.querySelector( '.murtadd-sidebar' );
	if ( toggle && sidebar ) {
		toggle.addEventListener( 'click', function () {
			var open = sidebar.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
	}

	// Keyboard support for hover submenus: toggle on caret-parent link second tap.
	document.querySelectorAll( '.has-sub > a' ).forEach( function ( link ) {
		link.addEventListener( 'keydown', function ( e ) {
			if ( 'ArrowDown' === e.key ) {
				e.preventDefault();
				var first = link.parentElement.querySelector( '.murtadd-sub a' );
				if ( first ) {
					first.focus();
				}
			}
		} );
	} );
}() );
