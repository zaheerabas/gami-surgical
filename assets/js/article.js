/**
 * Article page: reading progress bar, current section in the contents list,
 * and the "Copy link" share button.
 */
( function () {
	'use strict';

	var content = document.querySelector( '.article-content' );
	var bar = document.querySelector( '.read-progress span' );

	// Highlight the section being read in the sidebar contents.
	var links = Array.prototype.slice.call( document.querySelectorAll( '.toc--side a[href^="#"]' ) );
	var headings = [];
	links.forEach( function ( a ) {
		var h = document.getElementById( decodeURIComponent( a.getAttribute( 'href' ).slice( 1 ) ) );
		if ( h ) {
			headings.push( { el: h, link: a } );
		}
	} );
	var active = null;

	function updateToc() {
		// The last heading above the top third of the screen is the current section.
		var current = null;
		headings.forEach( function ( item ) {
			if ( item.el.getBoundingClientRect().top < window.innerHeight * 0.33 ) {
				current = item;
			}
		} );
		if ( current === active ) {
			return;
		}
		if ( active ) {
			active.link.classList.remove( 'is-active' );
			active.link.removeAttribute( 'aria-current' );
		}
		if ( current ) {
			current.link.classList.add( 'is-active' );
			current.link.setAttribute( 'aria-current', 'true' );
		}
		active = current;
	}

	// Reading progress: 0 at the top of the text, 100% at its end.
	function updateBar() {
		if ( ! content || ! bar ) {
			return;
		}
		var rect = content.getBoundingClientRect();
		var total = rect.height - window.innerHeight * 0.6;
		var done = total > 0 ? Math.min( 1, Math.max( 0, -rect.top / total ) ) : 1;
		bar.style.transform = 'scaleX(' + done.toFixed( 4 ) + ')';
	}

	var ticking = false;
	function update() {
		updateBar();
		updateToc();
		ticking = false;
	}
	if ( ( content && bar ) || headings.length ) {
		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				ticking = true;
				window.requestAnimationFrame( update );
			}
		}, { passive: true } );
		window.addEventListener( 'resize', update );
		update();
	}

	// Close the inline contents after choosing a section (phones).
	var inline = document.querySelector( '.toc--inline' );
	if ( inline ) {
		inline.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( 'a' ) ) {
				inline.removeAttribute( 'open' );
			}
		} );
	}

	// Copy link.
	var copy = document.querySelector( '.share-links__copy' );
	if ( copy && navigator.clipboard ) {
		var label = copy.textContent;
		copy.addEventListener( 'click', function () {
			navigator.clipboard.writeText( copy.getAttribute( 'data-copy' ) ).then( function () {
				copy.textContent = copy.getAttribute( 'data-done' );
				copy.classList.add( 'is-done' );
				window.setTimeout( function () {
					copy.textContent = label;
					copy.classList.remove( 'is-done' );
				}, 2000 );
			} );
		} );
	} else if ( copy ) {
		copy.parentNode.hidden = true;
	}
}() );
