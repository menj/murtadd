/**
 * Glossary of terms. Hover and keyboard focus show a definition through CSS
 * alone; this script adds tap to open and close, Escape to close, closing on
 * a tap elsewhere, and keeps the definition inside the viewport.
 */
( function () {
	'use strict';

	var terms = document.querySelectorAll( '.murtadd-term' );
	if ( ! terms.length ) {
		return;
	}

	function place( term ) {
		var tip = term.querySelector( '.murtadd-term__tip' );
		if ( ! tip ) {
			return;
		}
		tip.classList.remove( 'is-start', 'is-end', 'is-below' );
		var rect = tip.getBoundingClientRect();
		var width = document.documentElement.clientWidth;
		if ( rect.left < 8 ) {
			tip.classList.add( 'is-start' );
		} else if ( rect.right > width - 8 ) {
			tip.classList.add( 'is-end' );
		}
		if ( tip.getBoundingClientRect().top < 8 ) {
			tip.classList.add( 'is-below' );
		}
	}

	function close( term ) {
		term.classList.remove( 'is-open' );
		term.classList.add( 'is-dismissed' );
		var word = term.querySelector( '.murtadd-term__word' );
		if ( word ) {
			word.setAttribute( 'aria-expanded', 'false' );
		}
	}

	function closeAll( except ) {
		terms.forEach( function ( term ) {
			if ( term !== except ) {
				close( term );
			}
		} );
	}

	terms.forEach( function ( term ) {
		var word = term.querySelector( '.murtadd-term__word' );
		if ( ! word ) {
			return;
		}
		term.addEventListener( 'mouseleave', function () {
			term.classList.remove( 'is-dismissed' );
		} );
		word.addEventListener( 'blur', function () {
			term.classList.remove( 'is-dismissed' );
		} );
		word.addEventListener( 'click', function () {
			var open = ! term.classList.contains( 'is-open' );
			term.classList.remove( 'is-dismissed' );
			closeAll( term );
			term.classList.toggle( 'is-open', open );
			word.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			if ( open ) {
				place( term );
			} else {
				// Closed by a second tap: stay closed under a sticky touch hover.
				term.classList.add( 'is-dismissed' );
			}
		} );
		term.addEventListener( 'mouseenter', function () {
			term.classList.remove( 'is-dismissed' );
			place( term );
		} );
		word.addEventListener( 'focus', function () {
			term.classList.remove( 'is-dismissed' );
			place( term );
		} );
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key ) {
			closeAll( null );
		}
	} );

	document.addEventListener( 'click', function ( event ) {
		if ( ! event.target.closest( '.murtadd-term' ) ) {
			closeAll( null );
		}
	} );
}() );
