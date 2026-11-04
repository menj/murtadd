/* Murtadd: Appearance → Theme Options.
   Enqueued only on the screen's own hook suffix.

   - Tabs: the server renders the active panel only. Click, or the arrow
     keys, Home and End on the tab list, switch panels without a reload; a
     panel not yet in the page is fetched once over admin-ajax and then kept.
     The address follows the tab, so a reload or a save comes back to it, and
     a Docs document (?doc=) is kept. Without this script the tabs are plain
     links and the server shows one panel.
   - Range sliders show their value as they move.
   - Core's colour picker on the colour fields, with the contrast check.

   Nothing is put on window. */
( function ( $ ) {
	'use strict';

	var l10n = window.murtaddSettings || {};

	/* ---------- Tabs ---------- */

	var list = document.querySelector( '.murtadd-admin-tablist' );
	var tabs = list ? Array.prototype.slice.call( list.querySelectorAll( '[role="tab"]' ) ) : [];

	function panelFor( tab ) {
		return document.getElementById( tab.getAttribute( 'aria-controls' ) );
	}

	function select( tab, focus ) {
		tabs.forEach( function ( other ) {
			var on = other === tab;
			var panel = panelFor( other );
			other.setAttribute( 'aria-selected', on ? 'true' : 'false' );
			other.setAttribute( 'tabindex', on ? '0' : '-1' );
			other.classList.toggle( 'is-active', on );
			if ( panel ) {
				panel.hidden = ! on;
			}
		} );
		if ( focus ) {
			tab.focus();
		}
		var target = panelFor( tab );
		if ( target && target.hasAttribute( 'data-murtadd-lazy' ) ) {
			load( tab, target );
		}
		tab.scrollIntoView( { block: 'nearest', inline: 'nearest' } );
		if ( window.history && window.history.replaceState ) {
			var url = new URL( window.location.href );
			url.searchParams.set( 'tab', tab.getAttribute( 'data-murtadd-tab' ) );
			// ?doc= stays: it names the document the Docs panel shows.
			[ 'settings-updated', 'murtadd_done', 'murtadd_dropins', 'murtadd_sec', 'murtadd_count' ].forEach( function ( key ) {
				url.searchParams.delete( key );
			} );
			window.history.replaceState( null, '', url.toString() );
		}
	}

	/*
	 * Fetch a panel the first time its tab is chosen. If the request fails,
	 * open the tab's own address instead, which the server renders in full.
	 */
	function load( tab, panel ) {
		panel.removeAttribute( 'data-murtadd-lazy' );
		panel.setAttribute( 'aria-busy', 'true' );
		var body = new FormData();
		body.append( 'action', 'murtadd_settings_panel' );
		body.append( 'nonce', l10n.nonce || '' );
		body.append( 'tab', tab.getAttribute( 'data-murtadd-tab' ) );
		body.append( 'doc', new URL( window.location.href ).searchParams.get( 'doc' ) || '' );
		window.fetch( l10n.ajax, { method: 'POST', credentials: 'same-origin', body: body } )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {
				if ( ! json || ! json.success || ! json.data || 'string' !== typeof json.data.html ) {
					throw new Error( 'panel' );
				}
				panel.innerHTML = json.data.html;
				panel.removeAttribute( 'aria-busy' );
				initPanel( panel );
				document.dispatchEvent( new CustomEvent( 'murtadd:panel', { detail: { tab: tab.getAttribute( 'data-murtadd-tab' ) } } ) );
			} )
			.catch( function () {
				panel.removeAttribute( 'aria-busy' );
				window.location.assign( tab.href );
			} );
	}

	if ( tabs.length ) {
		list.addEventListener( 'click', function ( event ) {
			var tab = event.target.closest( '[role="tab"]' );
			if ( ! tab || event.metaKey || event.ctrlKey || event.shiftKey || 0 !== event.button ) {
				return;
			}
			event.preventDefault();
			select( tab, false );
		} );

		list.addEventListener( 'keydown', function ( event ) {
			var index = tabs.indexOf( document.activeElement );
			var next = -1;
			if ( index < 0 ) {
				return;
			}
			switch ( event.key ) {
				case 'ArrowRight':
				case 'ArrowDown':
					next = ( index + 1 ) % tabs.length;
					break;
				case 'ArrowLeft':
				case 'ArrowUp':
					next = ( index - 1 + tabs.length ) % tabs.length;
					break;
				case 'Home':
					next = 0;
					break;
				case 'End':
					next = tabs.length - 1;
					break;
				case ' ':
				case 'Enter':
					event.preventDefault();
					select( tabs[ index ], true );
					return;
				default:
					return;
			}
			event.preventDefault();
			select( tabs[ next ], true );
		} );

		// The footer link to the docs opens the Docs tab in place.
		Array.prototype.forEach.call( document.querySelectorAll( '[data-murtadd-tab-link]' ), function ( link ) {
			link.addEventListener( 'click', function ( event ) {
				var target = document.getElementById( 'murtadd-tab-' + link.getAttribute( 'data-murtadd-tab-link' ) );
				if ( target ) {
					event.preventDefault();
					select( target, true );
				}
			} );
		} );
	}

	/* ---------- Range sliders ---------- */

	function initRanges( root ) {
	Array.prototype.forEach.call( root.querySelectorAll( '[data-murtadd-range]' ), function ( input ) {
		var output = document.getElementById( input.getAttribute( 'data-murtadd-range' ) );
		if ( ! output ) {
			return;
		}
		input.addEventListener( 'input', function () {
			output.value = input.value;
			output.textContent = input.value;
		} );
	} );
	}

	/* ---------- Contrast check ---------- */

	var contrastInput = null;
	var contrastResult = null;

	function luminance( hex ) {
		hex = String( hex ).replace( '#', '' );
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

	function checkContrast( value ) {
		if ( ! contrastInput || ! contrastResult ) {
			return;
		}
		var lum = luminance( String( undefined === value ? contrastInput.value : value ).trim() );
		contrastResult.classList.remove( 'is-pass', 'is-fail' );
		if ( null === lum ) {
			contrastResult.textContent = '';
			return;
		}
		var ratio = ( 1 + 0.05 ) / ( lum + 0.05 ); // Against white.
		var rounded = String( Math.round( ratio * 10 ) / 10 );
		var pass = ratio >= 4.5;
		contrastResult.classList.add( pass ? 'is-pass' : 'is-fail' );
		contrastResult.textContent = String( pass ? l10n.contrastPass : l10n.contrastFail ).replace( '%s', rounded );
	}

	function initContrast( root ) {
		var input = root.querySelector( '[data-murtadd-contrast]' );
		if ( ! input ) {
			return;
		}
		contrastInput = input;
		contrastResult = document.getElementById( 'murtadd-contrast-result' );
		contrastInput.addEventListener( 'input', function () {
			checkContrast();
		} );
		checkContrast();
	}

	/* ---------- Colour pickers ---------- */

	function initPickers( root ) {
	if ( $ && $.fn && $.fn.wpColorPicker ) {
		$( root ).find( '.murtadd-color-field' ).each( function () {
			var field = this;
			$( field ).wpColorPicker( {
				change: function ( event, ui ) {
					if ( field === contrastInput ) {
						checkContrast( ui.color.toString() );
					}
				},
				clear: function () {
					if ( field === contrastInput ) {
						checkContrast( '' );
					}
				}
			} );
		} );
	}
	}

	function initPanel( root ) {
		initRanges( root );
		initContrast( root );
		initPickers( root );
	}

	initPanel( document );
}( window.jQuery ) );
