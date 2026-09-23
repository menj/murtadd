/**
 * Long-form furniture: reading progress, mini table of contents, back to top.
 * Rebuttals only. Nothing here is required to read the page.
 */
( function () {
	'use strict';

	var article = document.querySelector( '.murtadd-rebuttal' );
	if ( ! article ) {
		return;
	}

	var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ---------- Progress ---------- */
	var bar = document.createElement( 'div' );
	bar.className = 'murtadd-progress';
	bar.setAttribute( 'role', 'progressbar' );
	bar.setAttribute( 'aria-label', 'Reading progress' );
	bar.innerHTML = '<span class="murtadd-progress-fill"></span>';
	document.body.appendChild( bar );
	var fill = bar.querySelector( '.murtadd-progress-fill' );

	/* ---------- Mini TOC ---------- */
	var headings = article.querySelectorAll( '.murtadd-rebuttal-body h3' );
	var toc = null;

	if ( headings.length >= 2 ) {
		toc = document.createElement( 'nav' );
		toc.className = 'murtadd-toc';
		toc.setAttribute( 'aria-label', 'On this page' );

		var list = document.createElement( 'ol' );
		headings.forEach( function ( heading, i ) {
			if ( ! heading.id ) {
				heading.id = 'section-' + ( i + 1 );
			}
			var li = document.createElement( 'li' );
			var a = document.createElement( 'a' );
			a.href = '#' + heading.id;
			a.textContent = heading.textContent;
			li.appendChild( a );
			list.appendChild( li );
		} );

		var label = document.createElement( 'h2' );
		label.className = 'murtadd-toc-label';
		label.textContent = 'On this page';
		toc.appendChild( label );
		toc.appendChild( list );

		var rail = document.querySelector( '.murtadd-rail' );
		if ( rail ) {
			rail.insertBefore( toc, rail.firstChild );
		}
	}

	/* ---------- Back to top ---------- */
	var top = document.createElement( 'button' );
	top.type = 'button';
	top.className = 'murtadd-top';
	top.setAttribute( 'aria-label', 'Back to top' );
	top.innerHTML = '<span aria-hidden="true">&uarr;</span>';
	document.body.appendChild( top );

	top.addEventListener( 'click', function () {
		window.scrollTo( { top: 0, behavior: reduced ? 'auto' : 'smooth' } );
	} );

	/* ---------- Scroll ---------- */
	var links = toc ? toc.querySelectorAll( 'a' ) : [];
	var ticking = false;

	function update() {
		var start = article.offsetTop;
		var height = article.offsetHeight - window.innerHeight;
		var scrolled = window.scrollY - start;
		var pct = height > 0 ? Math.min( 100, Math.max( 0, ( scrolled / height ) * 100 ) ) : 0;

		fill.style.width = pct + '%';
		bar.setAttribute( 'aria-valuenow', Math.round( pct ) );
		top.classList.toggle( 'is-visible', window.scrollY > 600 );

		var current = '';
		headings.forEach( function ( heading ) {
			if ( heading.getBoundingClientRect().top < 120 ) {
				current = heading.id;
			}
		} );
		links.forEach( function ( link ) {
			link.classList.toggle( 'is-current', link.getAttribute( 'href' ) === '#' + current );
		} );

		ticking = false;
	}

	window.addEventListener( 'scroll', function () {
		if ( ! ticking ) {
			window.requestAnimationFrame( update );
			ticking = true;
		}
	}, { passive: true } );

	update();
}() );
