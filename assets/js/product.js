/**
 * Gami Surgical product page:
 * - copy catalogue number / link,
 * - size chips and quantity, mirrored in the sticky inquiry card,
 * - recently viewed products (kept in this browser only).
 */
( function () {
	'use strict';

	var cfg = window.gamiProduct || {};

	/* ---------- copy buttons ---------- */

	function copyText( text ) {
		if ( navigator.clipboard && window.isSecureContext ) {
			return navigator.clipboard.writeText( text );
		}
		return new Promise( function ( resolve ) {
			var ta = document.createElement( 'textarea' );
			ta.value = text;
			ta.setAttribute( 'readonly', '' );
			ta.style.position = 'fixed';
			ta.style.opacity = '0';
			document.body.appendChild( ta );
			ta.select();
			try {
				document.execCommand( 'copy' );
			} catch ( e ) {}
			ta.remove();
			resolve();
		} );
	}

	document.querySelectorAll( '[data-copy]' ).forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			copyText( btn.getAttribute( 'data-copy' ) ).then( function () {
				btn.classList.add( 'is-copied' );
				var label = btn.getAttribute( 'data-copy-done' ) || cfg.copied || 'Copied';
				var tip = document.createElement( 'span' );
				tip.className = 'gs-copied-tip';
				tip.setAttribute( 'role', 'status' );
				tip.textContent = label;
				btn.appendChild( tip );
				setTimeout( function () {
					btn.classList.remove( 'is-copied' );
					tip.remove();
				}, 1600 );
			} );
		} );
	} );

	/* ---------- full-screen button on the photo ----------
	 * WooCommerce only adds its button when hover zoom is on. With zoom off,
	 * add one that opens the photo currently shown (clicking the photo works too). */

	var gallery = document.querySelector( '.gs-product .woocommerce-product-gallery' );
	if ( gallery && ! gallery.querySelector( '.woocommerce-product-gallery__trigger' ) && gallery.querySelector( '.woocommerce-product-gallery__image a' ) ) {
		var full = document.createElement( 'button' );
		full.type = 'button';
		full.className = 'woocommerce-product-gallery__trigger gs-gallery-full';
		full.setAttribute( 'aria-label', cfg.fullscreen || 'View full screen' );
		full.title = cfg.fullscreen || 'View full screen';
		full.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var link = gallery.querySelector( '.flex-active-slide a' ) || gallery.querySelector( '.woocommerce-product-gallery__image a' );
			if ( link ) {
				link.click();
			}
		} );
		gallery.insertBefore( full, gallery.firstChild );
	}

	/* ---------- photo counter (1 / 5) and keyboard arrows ---------- */

	function setupGalleryNav( g ) {
		var real = g.querySelectorAll( '.woocommerce-product-gallery__image:not(.clone)' );
		if ( real.length < 2 ) {
			return;
		}
		var count = g.querySelector( '.gs-gallery-count' );
		if ( ! count ) {
			count = document.createElement( 'span' );
			count.className = 'gs-gallery-count';
			count.setAttribute( 'aria-live', 'polite' );
			g.appendChild( count );
		}
		function update() {
			var thumbs = g.querySelectorAll( '.flex-control-thumbs img' );
			var idx = 0;
			thumbs.forEach( function ( t, i ) {
				if ( t.classList.contains( 'flex-active' ) ) {
					idx = i;
				}
			} );
			count.textContent = ( idx + 1 ) + ' / ' + real.length;
			count.setAttribute( 'aria-label', ( cfg.photoOf || 'Photo %1$d of %2$d' ).replace( '%1$d', idx + 1 ).replace( '%2$d', real.length ) );
		}
		update();
		var thumbsList = g.querySelector( '.flex-control-thumbs' );
		if ( thumbsList && 'MutationObserver' in window ) {
			new window.MutationObserver( update ).observe( thumbsList, { attributes: true, subtree: true, attributeFilter: [ 'class' ] } );
		}
		// (The slider itself already moves with the ← → keys.)
		g.setAttribute( 'role', 'region' );
		g.setAttribute( 'aria-label', cfg.gallery || 'Product photos' );
	}

	/* ---------- frame colour matches each photo's own background ----------
	 * Reads the colour at the photo's edges; if the edges are one plain colour,
	 * the frame (and that photo's thumbnail) uses it, so there is no visible
	 * "box inside a box". Busy edges keep the Customizer colour. */

	function edgeColour( img ) {
		try {
			var w = img.naturalWidth;
			var h = img.naturalHeight;
			if ( ! w || ! h ) {
				return null;
			}
			var size = 64;
			var c = document.createElement( 'canvas' );
			c.width = size;
			c.height = size;
			var ctx = c.getContext( '2d', { willReadFrequently: true } );
			ctx.drawImage( img, 0, 0, size, size );
			var pts = [ [ 1, 1 ], [ size - 2, 1 ], [ 1, size - 2 ], [ size - 2, size - 2 ], [ size / 2, 1 ], [ size / 2, size - 2 ], [ 1, size / 2 ], [ size - 2, size / 2 ] ];
			var cols = pts.map( function ( pt ) {
				var d = ctx.getImageData( pt[ 0 ], pt[ 1 ], 1, 1 ).data;
				// Transparent edges: treat as white.
				if ( d[ 3 ] < 20 ) {
					return [ 255, 255, 255 ];
				}
				return [ d[ 0 ], d[ 1 ], d[ 2 ] ];
			} );
			var avg = [ 0, 1, 2 ].map( function ( k ) {
				return Math.round( cols.reduce( function ( sum, col ) {
					return sum + col[ k ];
				}, 0 ) / cols.length );
			} );
			var spread = Math.max.apply( null, cols.map( function ( col ) {
				return Math.abs( col[ 0 ] - avg[ 0 ] ) + Math.abs( col[ 1 ] - avg[ 1 ] ) + Math.abs( col[ 2 ] - avg[ 2 ] );
			} ) );
			// Only plain, light backgrounds (studio shots).
			if ( spread > 24 || ( avg[ 0 ] + avg[ 1 ] + avg[ 2 ] ) / 3 < 150 ) {
				return null;
			}
			return 'rgb(' + avg.join( ',' ) + ')';
		} catch ( e ) {
			return null; // e.g. images served from another domain
		}
	}

	function whenLoaded( img, fn ) {
		if ( img.complete && img.naturalWidth ) {
			fn();
		} else {
			img.addEventListener( 'load', fn, { once: true } );
		}
	}

	function matchBackgrounds( g ) {
		var slides = g.querySelectorAll( '.woocommerce-product-gallery__image' );
		var thumbs = g.querySelectorAll( '.flex-control-thumbs img' );
		slides.forEach( function ( slide, i ) {
			var img = slide.querySelector( 'img' );
			if ( ! img ) {
				return;
			}
			whenLoaded( img, function () {
				var col = edgeColour( img );
				if ( ! col ) {
					return;
				}
				slide.style.background = col;
				slide.classList.add( 'gs-bg-matched' );
				if ( i === 0 ) {
					g.style.setProperty( '--gs-photo-bg', col );
				}
				if ( thumbs[ i ] ) {
					thumbs[ i ].style.background = col;
				}
			} );
		} );
	}

	/* ---------- arrows and counter live inside the main photo frame ---------- */

	function placeInFrame( g ) {
		var frame = g.querySelector( '.flex-viewport' );
		if ( ! frame ) {
			return;
		}
		var nav = g.querySelector( '.flex-direction-nav' );
		if ( nav && nav.parentElement !== frame ) {
			frame.appendChild( nav );
		}
		var count = g.querySelector( '.gs-gallery-count' );
		if ( count && count.parentElement !== frame ) {
			frame.appendChild( count );
		}
	}

	if ( gallery && gallery.querySelectorAll( '.woocommerce-product-gallery__image' ).length < 2 ) {
		// One photo: no slider to wait for.
		matchBackgrounds( gallery );
	} else if ( gallery ) {
		// WooCommerce builds the slider after the page loads; wait for it.
		var tries = 0;
		var waitSlider = setInterval( function () {
			tries++;
			if ( gallery.querySelector( '.flex-control-thumbs' ) || tries > 40 ) {
				clearInterval( waitSlider );
				setupGalleryNav( gallery );
				placeInFrame( gallery );
				matchBackgrounds( gallery );
			}
		}, 100 );
	}

	/* ---------- "Read full description": open that tab and go there ---------- */

	document.querySelectorAll( '[data-open-tab]' ).forEach( function ( link ) {
		link.addEventListener( 'click', function ( e ) {
			var tab = document.querySelector( '#tab-title-' + link.getAttribute( 'data-open-tab' ) + ' a' );
			if ( ! tab ) {
				return;
			}
			e.preventDefault();
			tab.click();
			var target = document.querySelector( '.gs-details' ) || tab;
			var y = target.getBoundingClientRect().top + window.scrollY - 120;
			window.scrollTo( { top: y, behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth' } );
		} );
	} );

	/* ---------- size and quantity ---------- */

	document.querySelectorAll( '[data-inquiry-config]' ).forEach( function ( conf ) {
		var qty = conf.querySelector( '[data-config-qty]' );
		var min = qty ? Math.max( 1, parseInt( qty.value, 10 ) || 1 ) : 1;

		function sync() {
			var r = conf.querySelector( 'input[type="radio"]:checked' );
			var size = r && r.value ? r.value : ( cfg.anySize || 'Any / all sizes' );
			document.querySelectorAll( '[data-chosen-size]' ).forEach( function ( el ) {
				el.textContent = size;
			} );
			if ( qty ) {
				document.querySelectorAll( '[data-chosen-qty]' ).forEach( function ( el ) {
					el.textContent = qty.value;
				} );
			}
		}

		conf.addEventListener( 'change', function ( e ) {
			if ( e.target === qty ) {
				qty.value = Math.max( 1, Math.min( 1000000, parseInt( qty.value, 10 ) || min ) );
			}
			sync();
		} );
		conf.addEventListener( 'input', sync );
		conf.querySelectorAll( '[data-qty-step]' ).forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				var step = parseInt( b.getAttribute( 'data-qty-step' ), 10 );
				// Steps go by the minimum order (e.g. 50, 100, 150) when there is one.
				var unit = min > 1 ? min : 1;
				var v = ( parseInt( qty.value, 10 ) || 0 ) + step * unit;
				qty.value = Math.max( min > 1 ? min : 1, v );
				sync();
			} );
		} );
		sync();
	} );

	/* ---------- recently viewed ---------- */

	var box = document.querySelector( '[data-recent]' );
	if ( box ) {
		var KEY = 'gamiRecentlyViewed';
		var current = parseInt( box.getAttribute( 'data-current' ), 10 );
		var list = [];
		try {
			list = JSON.parse( window.localStorage.getItem( KEY ) ) || [];
		} catch ( e ) {}
		if ( ! Array.isArray( list ) ) {
			list = [];
		}
		var shown = list.filter( function ( x ) {
			return x && x.id && x.id !== current;
		} ).slice( 0, 6 );

		if ( shown.length ) {
			var ul = box.querySelector( '.gs-recent__list' );
			shown.forEach( function ( p ) {
				var li = document.createElement( 'li' );
				var a = document.createElement( 'a' );
				a.className = 'gs-recent__card';
				a.href = p.url;
				var media = document.createElement( 'span' );
				media.className = 'gs-recent__media';
				if ( p.image ) {
					var img = document.createElement( 'img' );
					img.src = p.image;
					img.alt = '';
					img.loading = 'lazy';
					img.width = 120;
					img.height = 120;
					media.appendChild( img );
				}
				a.appendChild( media );
				var name = document.createElement( 'span' );
				name.className = 'gs-recent__name';
				name.textContent = p.name;
				a.appendChild( name );
				if ( p.sku ) {
					var sku = document.createElement( 'span' );
					sku.className = 'gs-recent__sku';
					sku.textContent = ( cfg.catNo || 'Cat. no.' ) + ' ' + p.sku;
					a.appendChild( sku );
				}
				li.appendChild( a );
				ul.appendChild( li );
			} );
			box.hidden = false;
		}

		// Remember this product (newest first, 12 max).
		try {
			var me = JSON.parse( box.getAttribute( 'data-item' ) );
			list = list.filter( function ( x ) {
				return x && x.id !== me.id;
			} );
			list.unshift( { id: me.id, name: me.name, url: me.url, image: me.image, sku: me.sku } );
			window.localStorage.setItem( KEY, JSON.stringify( list.slice( 0, 12 ) ) );
		} catch ( e ) {}
	}
}() );
