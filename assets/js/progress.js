/**
 * Reading progress bar (inc/reading-progress.php). Measures the entry, the
 * rebuttal article where there is one and the main column otherwise. One
 * passive scroll listener asks for at most one update per animation frame.
 * Nothing here is needed to read the page.
 */
( function () {
	'use strict';

	var entry = document.querySelector( '.murtadd-rebuttal' ) || document.getElementById( 'murtadd-content' ) || document.body;
	var l10n = window.murtaddProgress || {};

	var bar = document.createElement( 'div' );
	bar.className = 'murtadd-progress' + ( 'bottom' === l10n.position ? ' murtadd-progress--bottom' : '' );
	bar.setAttribute( 'role', 'progressbar' );
	bar.setAttribute( 'aria-label', l10n.label || '' );
	bar.setAttribute( 'aria-valuemin', '0' );
	bar.setAttribute( 'aria-valuemax', '100' );
	bar.setAttribute( 'aria-valuenow', '0' );

	var fill = document.createElement( 'span' );
	fill.className = 'murtadd-progress-fill';
	bar.appendChild( fill );
	document.body.appendChild( bar );

	var ticking = false;

	function update() {
		var rect = entry.getBoundingClientRect();
		var start = rect.top + window.scrollY;
		var height = entry.offsetHeight - window.innerHeight;
		var ratio = height > 0 ? Math.min( 1, Math.max( 0, ( window.scrollY - start ) / height ) ) : 1;

		fill.style.transform = 'scaleX(' + ratio.toFixed( 4 ) + ')';
		bar.setAttribute( 'aria-valuenow', String( Math.round( ratio * 100 ) ) );
		ticking = false;
	}

	function schedule() {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( update );
		}
	}

	window.addEventListener( 'scroll', schedule, { passive: true } );
	window.addEventListener( 'resize', schedule, { passive: true } );

	// Images, embeds and late styles change the entry's height without a
	// scroll or a resize; measure again whenever it does.
	if ( 'ResizeObserver' in window ) {
		var observer = new window.ResizeObserver( schedule );
		observer.observe( document.body );
		if ( entry !== document.body ) {
			observer.observe( entry );
		}
	}
	update();
}() );
