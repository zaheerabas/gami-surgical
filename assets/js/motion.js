/**
 * Gami Surgical motion: content fades up as it scrolls into view, with a
 * short stagger for items in the same row (product cards, category cards).
 *
 * Nothing is hidden unless this script runs, and nothing moves when the
 * visitor's device asks for reduced motion.
 */
( function () {
	'use strict';

	if ( ! ( 'IntersectionObserver' in window ) || window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}
	var root = document.documentElement;
	root.classList.add( 'gs-motion' );

	var SELECTOR = [
		'.woocommerce-products-header',
		'.cat-card',
		'.subcat-slider',
		'.shop-toolbar',
		'ul.products li.product',
		'.side-card',
		'.no-results-help',
		'.woocommerce div.product div.images',
		'.woocommerce div.product div.summary',
		'.woocommerce-tabs',
		'.inquiry-band',
		'.gs-details__main',
		'.gs-features__item',
		'.gs-recent',
		'.related.products > h2',
		'.upsells.products > h2',
		'.inquiry-head',
		'.gs-inquiry__card',
		'.gs-inquiry__done',
		'.site-footer__grid > .footer-col',
		'.entry-header',
		'.entry-content > *:not(.elementor):not(script):not(style)',
		'.post-summary',
		'.search-result',
		'.error-404 > *',
		'[data-reveal]'
	].join( ',' );

	var io = new window.IntersectionObserver( function ( entries ) {
		// Stagger items that appear together in the same parent.
		var batches = new Map();
		entries.forEach( function ( en ) {
			if ( ! en.isIntersecting ) {
				return;
			}
			var p = en.target.parentElement;
			if ( ! batches.has( p ) ) {
				batches.set( p, [] );
			}
			batches.get( p ).push( en.target );
			io.unobserve( en.target );
		} );
		batches.forEach( function ( els ) {
			els.sort( function ( a, b ) {
				var ra = a.getBoundingClientRect();
				var rb = b.getBoundingClientRect();
				return ( ra.top - rb.top ) || ( ra.left - rb.left );
			} );
			els.forEach( function ( el, i ) {
				el.style.setProperty( '--gs-delay', Math.min( i, 7 ) * 70 + 'ms' );
				el.classList.add( 'is-visible' );
				// Clear inline delay afterwards so hover effects aren't delayed.
				setTimeout( function () {
					el.classList.add( 'is-revealed' );
					el.style.removeProperty( '--gs-delay' );
				}, 900 + i * 70 );
			} );
		} );
	}, { rootMargin: '0px 0px -6% 0px', threshold: 0.08 } );

	function watch( scope ) {
		( scope || document ).querySelectorAll( SELECTOR ).forEach( function ( el ) {
			if ( el.classList.contains( 'gs-reveal' ) || el.closest( '.mobile-inquiry-bar, .gs-search__panel, .main-navigation, .catalogue__filters .filters-head' ) ) {
				return;
			}
			el.classList.add( 'gs-reveal' );
			io.observe( el );
		} );
	}

	function start() {
		watch();
		// Content added later (e.g. by WooCommerce or page builders).
		if ( 'MutationObserver' in window ) {
			var mo = new window.MutationObserver( function ( list ) {
				list.forEach( function ( m ) {
					m.addedNodes.forEach( function ( n ) {
						if ( n.nodeType === 1 ) {
							watch( n.parentElement || n );
						}
					} );
				} );
			} );
			var main = document.getElementById( 'content' );
			if ( main ) {
				mo.observe( main, { childList: true, subtree: true } );
			}
		}
		// Safety net: after load and whenever scrolling stops (fast jumps,
		// "back" restoring the position), show anything left hidden in view.
		function sweep() {
			document.querySelectorAll( '.gs-reveal:not(.is-visible)' ).forEach( function ( el ) {
				var r = el.getBoundingClientRect();
				if ( r.top < window.innerHeight && r.bottom > 0 ) {
					el.classList.add( 'is-visible' );
					io.unobserve( el );
				}
			} );
		}
		setTimeout( sweep, 2500 );
		var t = null;
		window.addEventListener( 'scroll', function () {
			clearTimeout( t );
			t = setTimeout( sweep, 250 );
		}, { passive: true } );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
