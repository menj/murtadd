/* Murtadd settings screen: contrast check on the Colours tab.
   Enqueued only on appearance_page_murtadd. Tab switching itself is
   server-side links (Settings API sections) — nothing to do here for tabs. */
( function () {
	'use strict';

	var input = document.querySelector( '[data-murtadd-contrast]' );
	var result = document.getElementById( 'murtadd-contrast-result' );
	if ( ! input || ! result ) {
		return;
	}

	function luminance( hex ) {
		hex = hex.replace( '#', '' );
		if ( 3 === hex.length ) {
			hex = hex.replace( /./g, '$&$&' );
		}
		if ( 6 !== hex.length || /[^0-9a-f]/i.test( hex ) ) {
			return null;
		}
		var rgb = [ 0, 2, 4 ].map( function ( i ) {
			var c = parseInt( hex.substr( i, 2 ), 16 ) / 255;
			return c <= 0.03928 ? c / 12.92 : Math.pow( ( c + 0.055 ) / 1.055, 2.4 );
		} );
		return 0.2126 * rgb[ 0 ] + 0.7152 * rgb[ 1 ] + 0.0722 * rgb[ 2 ];
	}

	function check() {
		var lum = luminance( input.value.trim() );
		if ( null === lum ) {
			result.textContent = '';
			return;
		}
		var ratio = ( 1 + 0.05 ) / ( lum + 0.05 ); // vs white
		if ( ratio < 1 ) {
			ratio = 1 / ratio;
		}
		var rounded = Math.round( ratio * 10 ) / 10;
		if ( ratio >= 4.5 ) {
			result.textContent = '✓ ' + rounded + ':1 — passes WCAG AA against white.';
			result.style.color = '#00450c';
		} else {
			result.textContent = '⚠ ' + rounded + ':1 — below WCAG AA (4.5:1) against white. Saving is not blocked.';
			result.style.color = '#8a4b00';
		}
	}

	input.addEventListener( 'input', check );
	check();
}() );
