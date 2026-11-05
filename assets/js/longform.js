/**
 * Long-form furniture: mini table of contents, back to top. Rebuttals and
 * Journal posts. Nothing here is required to read the page. The reading
 * progress bar moved to progress.js (inc/reading-progress.php), which Theme
 * Options sets.
 *
 * On a rebuttal the script builds the contents in the rail from the h3
 * headings. On a Journal post the server has already printed it
 * (inc/article.php) from the h2 headings; the script only marks the
 * section being read.
 */
( function () {
	'use strict';

	var article = document.querySelector( '.murtadd-rebuttal, .murtadd-blog-post' );
	if ( ! article ) {
		return;
	}
	var journal = article.classList.contains( 'murtadd-blog-post' );

	var l10n = window.murtaddLongform || {};
	var text = {
		progress: l10n.progress || 'Reading progress',
		toc: l10n.toc || 'On this page',
		top: l10n.top || 'Back to top'
	};

	var reduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ---------- Mini TOC ---------- */
	var headings = article.querySelectorAll( journal ? '.murtadd-blog-body h2[id]' : '.murtadd-rebuttal-body h3' );
	var toc = journal ? article.querySelector( '.murtadd-toc' ) : null;

	if ( ! journal && headings.length >= 2 ) {
		toc = document.createElement( 'nav' );
		toc.className = 'murtadd-toc';
		toc.setAttribute( 'aria-label', text.toc );

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
		label.textContent = text.toc;
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
	top.setAttribute( 'aria-label', text.top );
	top.innerHTML = '<span aria-hidden="true">&uarr;</span>';
	document.body.appendChild( top );

	top.addEventListener( 'click', function () {
		window.scrollTo( { top: 0, behavior: reduced ? 'auto' : 'smooth' } );
	} );

	/* ---------- Scroll ---------- */
	var links = toc ? toc.querySelectorAll( 'a' ) : [];
	var ticking = false;

	function update() {
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
