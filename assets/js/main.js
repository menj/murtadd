/* Murtadd — frontend. Progressive enhancement only; everything works without JS. */
( function () {
	'use strict';

	// Mobile nav toggle. Escape closes the open menu and returns focus to the toggle.
	var toggle = document.querySelector( '.murtadd-nav-toggle' );
	var sidebar = document.querySelector( '.murtadd-sidebar' );
	if ( toggle && sidebar ) {
		toggle.addEventListener( 'click', function () {
			var open = sidebar.classList.toggle( 'is-open' );
			toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
		sidebar.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && sidebar.classList.contains( 'is-open' ) && ! e.defaultPrevented ) {
				sidebar.classList.remove( 'is-open' );
				toggle.setAttribute( 'aria-expanded', 'false' );
				toggle.focus();
			}
		} );
	}

	// Submenus open on hover and focus (CSS). The script reports the state through
	// aria-expanded, lets Escape dismiss an open one, and keeps ArrowDown.
	document.querySelectorAll( '.has-sub > a' ).forEach( function ( link ) {
		var item = link.parentElement;
		var sub = item.querySelector( '.murtadd-sub' );
		if ( ! sub ) {
			return;
		}
		var sync = function () {
			var shown = ! item.classList.contains( 'is-dismissed' ) && ( item.matches( ':hover' ) || item.contains( document.activeElement ) );
			link.setAttribute( 'aria-expanded', shown ? 'true' : 'false' );
		};
		var reset = function () {
			// Wait for the focus move to finish before reading it.
			setTimeout( function () {
				if ( ! item.contains( document.activeElement ) && ! item.matches( ':hover' ) ) {
					item.classList.remove( 'is-dismissed' );
				}
				sync();
			}, 0 );
		};
		link.setAttribute( 'aria-expanded', 'false' );
		item.addEventListener( 'focusin', function ( e ) {
			// Focus moving into a dismissed submenu reopens it, so focus is never hidden.
			if ( sub.contains( e.target ) ) {
				item.classList.remove( 'is-dismissed' );
			}
			sync();
		} );
		item.addEventListener( 'mouseenter', sync );
		item.addEventListener( 'focusout', reset );
		item.addEventListener( 'mouseleave', reset );
		item.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && ! item.classList.contains( 'is-dismissed' ) && 'true' === link.getAttribute( 'aria-expanded' ) ) {
				e.preventDefault();
				item.classList.add( 'is-dismissed' );
				link.focus();
				sync();
			}
		} );
		link.addEventListener( 'keydown', function ( e ) {
			if ( 'ArrowDown' === e.key ) {
				e.preventDefault();
				item.classList.remove( 'is-dismissed' );
				var first = sub.querySelector( 'a' );
				if ( first ) {
					first.focus();
				}
				sync();
			}
		} );
	} );
}() );
