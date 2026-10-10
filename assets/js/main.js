/**
 * Gami Surgical front end.
 *
 * - Menu switches to the off-canvas drawer whenever the links don't fit
 *   (not at a fixed width), so any menu length works on any screen.
 * - Drawer: overlay, Escape, focus trap, scroll lock, sub-menu toggles.
 * - Header search, slimmer sticky header after scrolling, back-to-top.
 * - Footer columns become accordions on phones.
 * - Shop filters open as a drawer on small screens.
 */
( function () {
	'use strict';

	var root = document.documentElement;
	var header = document.getElementById( 'masthead' );
	var nav = document.getElementById( 'site-navigation' );
	var toggle = document.querySelector( '.menu-toggle' );
	var overlay = document.querySelector( '.nav-overlay' );
	var lastFocus = null;
	var MOBILE_MAX = 767;

	function qsa( sel, ctx ) {
		return Array.prototype.slice.call( ( ctx || document ).querySelectorAll( sel ) );
	}

	function debounce( fn, ms ) {
		var t;
		return function () {
			clearTimeout( t );
			t = setTimeout( fn, ms );
		};
	}

	/* ---------- Focus trap + scroll lock shared by drawers ---------- */

	function focusables( el ) {
		return qsa( 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select, textarea, [tabindex]:not([tabindex="-1"])', el )
			.filter( function ( n ) {
				return n.offsetParent !== null;
			} );
	}

	function trap( el, e ) {
		if ( e.key !== 'Tab' ) {
			return;
		}
		var items = focusables( el );
		if ( ! items.length ) {
			return;
		}
		var first = items[ 0 ];
		var last = items[ items.length - 1 ];
		if ( e.shiftKey && document.activeElement === first ) {
			e.preventDefault();
			last.focus();
		} else if ( ! e.shiftKey && document.activeElement === last ) {
			e.preventDefault();
			first.focus();
		}
	}

	function lockScroll( on ) {
		root.classList.toggle( 'gs-scroll-locked', on );
	}

	/* ---------- Mobile breakpoint and the "More" menu ---------- */

	var BP = parseInt( root.getAttribute( 'data-gs-bp' ), 10 ) || 992;
	var primary = nav ? nav.querySelector( '.primary-menu' ) : null;
	var more = null;

	function isMobile() {
		return window.innerWidth < BP;
	}

	function buildMore() {
		if ( ! primary || more ) {
			return;
		}
		more = document.createElement( 'li' );
		more.className = 'menu-item menu-item-more menu-item-has-children';
		more.hidden = true;
		more.innerHTML = '<button type="button" class="more-toggle" aria-expanded="false" aria-controls="gs-more-menu">' +
			( ( window.gamiNav && window.gamiNav.more ) || 'More' ) +
			'<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></button>' +
			'<ul class="sub-menu" id="gs-more-menu"></ul>';
		primary.appendChild( more );
		var btn = more.querySelector( '.more-toggle' );
		btn.addEventListener( 'click', function () {
			var open = btn.getAttribute( 'aria-expanded' ) !== 'true';
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			more.classList.toggle( 'is-sub-open', open );
		} );
		more.addEventListener( 'focusout', function ( e ) {
			if ( ! more.contains( e.relatedTarget ) ) {
				btn.setAttribute( 'aria-expanded', 'false' );
				more.classList.remove( 'is-sub-open' );
			}
		} );
	}

	// Put every item back in the menu bar, in its original order.
	function restoreMore() {
		if ( ! more ) {
			return;
		}
		var list = more.querySelector( '.sub-menu' );
		while ( list.firstElementChild ) {
			primary.insertBefore( list.firstElementChild, more );
		}
		more.hidden = true;
	}

	function overflowing() {
		var bar = primary.closest( '.site-header__nav-inner' ) || primary.closest( '.site-header__inner' );
		var navBox = nav.getBoundingClientRect();
		var last = more && ! more.hidden ? more : primary.lastElementChild;
		if ( ! last ) {
			return false;
		}
		if ( bar && bar.scrollWidth > bar.clientWidth + 1 ) {
			return true;
		}
		return last.getBoundingClientRect().right > navBox.right + 1;
	}

	function fitMenu() {
		if ( ! primary ) {
			return;
		}
		buildMore();
		restoreMore();
		if ( isMobile() ) {
			return;
		}
		var list = more.querySelector( '.sub-menu' );
		var guard = 0;
		while ( overflowing() && guard < 30 ) {
			var items = Array.prototype.filter.call( primary.children, function ( li ) {
				return li !== more;
			} );
			if ( items.length <= 1 ) {
				break;
			}
			more.hidden = false;
			list.insertBefore( items[ items.length - 1 ], list.firstChild );
			guard++;
		}
	}

	function applyBreakpoint() {
		var mobile = isMobile();
		root.classList.toggle( 'gs-nav-collapsed', mobile );
		if ( ! mobile && nav && nav.classList.contains( 'is-open' ) ) {
			setMenu( false, true );
		}
		fitMenu();
	}

	function setMenu( open, silent ) {
		if ( ! nav || ! toggle ) {
			return;
		}
		if ( open ) {
			lastFocus = document.activeElement;
		}
		toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		nav.classList.toggle( 'is-open', open );
		root.classList.toggle( 'gs-menu-open', open );
		if ( overlay ) {
			overlay.hidden = ! open;
		}
		lockScroll( open );
		if ( open ) {
			var close = nav.querySelector( '.drawer-close' );
			if ( close ) {
				setTimeout( function () {
					close.focus();
				}, 50 );
			}
		} else if ( ! silent && lastFocus ) {
			lastFocus.focus();
		}
	}

	if ( toggle && nav ) {
		toggle.addEventListener( 'click', function () {
			setMenu( ! nav.classList.contains( 'is-open' ) );
			setSearch( false );
		} );
		qsa( '[data-close-menu]' ).forEach( function ( el ) {
			el.addEventListener( 'click', function () {
				setMenu( false );
			} );
		} );
		nav.addEventListener( 'keydown', function ( e ) {
			if ( nav.classList.contains( 'is-open' ) ) {
				trap( nav, e );
			}
		} );
		// Following an in-page link closes the drawer.
		nav.addEventListener( 'click', function ( e ) {
			var a = e.target.closest( 'a' );
			if ( a && nav.classList.contains( 'is-open' ) && a.getAttribute( 'href' ) && a.getAttribute( 'href' ).charAt( 0 ) === '#' ) {
				setMenu( false, true );
			}
		} );

		window.addEventListener( 'resize', function () {
			root.classList.remove( 'gs-ready' );
		} );
		window.addEventListener( 'resize', debounce( function () {
			applyBreakpoint();
			window.requestAnimationFrame( function () {
				root.classList.add( 'gs-ready' );
			} );
		}, 120 ) );
		window.addEventListener( 'load', fitMenu );
		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( fitMenu );
		}
	}

	/* ---------- Sub-menu toggles ---------- */

	qsa( '.main-navigation .menu-item-has-children, .main-navigation .page_item_has_children' ).forEach( function ( li, i ) {
		var link = li.querySelector( ':scope > a' );
		var sub = li.querySelector( ':scope > ul' );
		if ( ! link || ! sub || li.classList.contains( 'menu-item-more' ) ) {
			return;
		}
		// Columns inside a mega menu are always open.
		var parentList = li.parentElement;
		if ( parentList && parentList.parentElement && parentList.parentElement.classList.contains( 'mega-menu' ) ) {
			return;
		}
		sub.id = sub.id || 'gs-submenu-' + i;
		var btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = 'submenu-toggle';
		btn.setAttribute( 'aria-expanded', 'false' );
		btn.setAttribute( 'aria-controls', sub.id );
		btn.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg><span class="screen-reader-text">' + ( link.textContent.trim() ) + ' submenu</span>';
		link.insertAdjacentElement( 'afterend', btn );
		btn.addEventListener( 'click', function () {
			var open = btn.getAttribute( 'aria-expanded' ) !== 'true';
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			li.classList.toggle( 'is-sub-open', open );
		} );
		// Desktop: close a keyboard-opened dropdown when focus leaves it.
		li.addEventListener( 'focusout', function ( e ) {
			if ( ! root.classList.contains( 'gs-nav-collapsed' ) && ! li.contains( e.relatedTarget ) ) {
				btn.setAttribute( 'aria-expanded', 'false' );
				li.classList.remove( 'is-sub-open' );
			}
		} );
	} );

	/* ---------- Header search ---------- */

	var searchToggle = document.querySelector( '.header-search-toggle' );
	var search = document.getElementById( 'header-search' );

	function setSearch( open ) {
		if ( ! searchToggle || ! search ) {
			return;
		}
		searchToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		search.hidden = ! open;
		if ( open ) {
			var field = search.querySelector( 'input[type="search"]' );
			if ( field ) {
				field.focus();
			}
		}
	}

	if ( searchToggle && search ) {
		searchToggle.addEventListener( 'click', function () {
			setSearch( search.hidden );
			setMenu( false, true );
		} );
		qsa( '[data-close-search]' ).forEach( function ( el ) {
			el.addEventListener( 'click', function () {
				setSearch( false );
				searchToggle.focus();
			} );
		} );
	}

	/* ---------- Shop filters drawer ---------- */

	var filters = document.getElementById( 'shop-filters' );
	var filterBtns = qsa( '[data-open-filters]' );
	var filterBtn = filterBtns[ 0 ] || null;
	var filterLast = null;

	function setFilters( open ) {
		if ( ! filters ) {
			return;
		}
		if ( open ) {
			filterLast = document.activeElement;
		}
		filters.classList.toggle( 'is-open', open );
		root.classList.toggle( 'gs-filters-open', open );
		lockScroll( open );
		filterBtns.forEach( function ( b ) {
			b.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		} );
		if ( open ) {
			var first = focusables( filters )[ 0 ];
			if ( first ) {
				first.focus();
			}
		} else if ( filterLast ) {
			filterLast.focus();
		}
	}

	if ( filters && filterBtn ) {
		filterBtns.forEach( function ( b ) {
			b.addEventListener( 'click', function () {
				setFilters( true );
			} );
		} );
		qsa( '[data-close-filters]' ).forEach( function ( el ) {
			el.addEventListener( 'click', function () {
				setFilters( false );
			} );
		} );
		filters.addEventListener( 'keydown', function ( e ) {
			if ( filters.classList.contains( 'is-open' ) ) {
				trap( filters, e );
			}
		} );
	}

	/* ---------- Escape closes whatever is open ---------- */

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== 'Escape' ) {
			return;
		}
		if ( nav && nav.classList.contains( 'is-open' ) ) {
			setMenu( false );
		} else if ( filters && filters.classList.contains( 'is-open' ) ) {
			setFilters( false );
		} else if ( search && ! search.hidden ) {
			setSearch( false );
			searchToggle.focus();
		}
	} );

	/* ---------- Scroll: slimmer header, back-to-top ---------- */

	var backTop = document.querySelector( '.back-to-top' );
	var ticking = false;

	// Phones: scrolling down leaves only the search bar; scrolling up brings
	// the whole header back. The header slides (transform), so the page
	// never jumps.
	var condenseOn = header && header.classList.contains( 'site-header--condense' );
	var searchRow = header ? header.querySelector( '.site-header__search' ) : null;
	var lastY = window.scrollY || 0;
	var condensed = false;

	function setCondensed( on ) {
		if ( on === condensed || ! header ) {
			return;
		}
		condensed = on;
		if ( on && searchRow ) {
			header.style.setProperty( '--gs-condense', ( -searchRow.offsetTop + 8 ) + 'px' );
		}
		header.classList.toggle( 'is-condensed', on );
	}

	function condense( y ) {
		if ( ! condenseOn || ! searchRow ) {
			return;
		}
		var mobile = root.classList.contains( 'gs-nav-collapsed' );
		var busy = root.classList.contains( 'gs-menu-open' ) || root.classList.contains( 'gs-filters-open' ) || ( document.activeElement && searchRow.contains( document.activeElement ) );
		var delta = y - lastY;
		if ( ! mobile || y < header.offsetHeight + 60 ) {
			setCondensed( false );
		} else if ( ! busy && delta > 6 ) {
			setCondensed( true );
		} else if ( delta < -6 ) {
			setCondensed( false );
		}
		if ( Math.abs( delta ) > 6 ) {
			lastY = y;
		}
	}

	function onScroll() {
		var y = window.scrollY || window.pageYOffset;
		if ( header ) {
			if ( y > 60 ) { header.classList.add( "is-scrolled" ); } else if ( y < 10 ) { header.classList.remove( "is-scrolled" ); }
		}
		if ( backTop ) {
			backTop.hidden = y < 600;
		}
		condense( y );
		ticking = false;
	}

	window.addEventListener( 'scroll', function () {
		if ( ! ticking ) {
			window.requestAnimationFrame( onScroll );
			ticking = true;
		}
	}, { passive: true } );
	onScroll();

	if ( backTop ) {
		backTop.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
			window.scrollTo( { top: 0, behavior: reduce ? 'auto' : 'smooth' } );
			var skip = document.querySelector( '.skip-link' );
			if ( skip ) {
				skip.focus( { preventScroll: true } );
			}
		} );
	}

	// Initial layout: mobile header or desktop menu bar with "More".
	if ( nav ) {
		applyBreakpoint();
	}

	// Turn on drawer animations once the first layout is settled.
	window.requestAnimationFrame( function () {
		window.requestAnimationFrame( function () {
			root.classList.add( 'gs-ready' );
		} );
	} );

	document.querySelectorAll( '.footer-top-link' ).forEach( function ( link ) {
		link.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			window.scrollTo( { top: 0, behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth' } );
		} );
	} );

	/* ---------- Footer accordions on phones ---------- */

	var phone = window.matchMedia( '(max-width: 639px)' );

	qsa( '.site-footer__grid .footer-col:not(.footer-col--1)' ).forEach( function ( col, i ) {
		var title = col.querySelector( '.widget-title' );
		if ( ! title ) {
			return;
		}
		var body = document.createElement( 'div' );
		body.className = 'footer-col__body';
		body.id = 'gs-footer-col-' + i;
		while ( title.nextSibling ) {
			body.appendChild( title.nextSibling );
		}
		col.appendChild( body );

		var btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = 'footer-col__toggle';
		btn.setAttribute( 'aria-controls', body.id );
		btn.innerHTML = '<span>' + title.innerHTML + '</span><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		title.innerHTML = '';
		title.appendChild( btn );
		col.classList.add( 'is-accordion' );

		function sync() {
			var open = ! phone.matches || col.classList.contains( 'is-open' );
			btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			btn.disabled = ! phone.matches;
		}
		btn.addEventListener( 'click', function () {
			col.classList.toggle( 'is-open' );
			sync();
		} );
		phone.addEventListener( 'change', sync );
		sync();
	} );
}() );
