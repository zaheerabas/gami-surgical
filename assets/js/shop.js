/**
 * Gami Surgical shop page: grid/list view, products per row, products per
 * page, and the inquiry-list card in the sidebar.
 *
 * View and columns switch instantly and are remembered in cookies (the
 * server reads them too). The toolbar links still work without JavaScript.
 */
( function () {
	'use strict';

	var list = document.querySelector( '.catalogue__results ul.products' );
	var viewBtns = document.querySelectorAll( '.view-btn[data-view]' );
	var colBtns = document.querySelectorAll( '.view-btn[data-cols]' );
	var mBtns = document.querySelectorAll( '.view-btn[data-mview]' );

	function setCookie( name, value ) {
		document.cookie = name + '=' + encodeURIComponent( value ) + ';path=/;max-age=31536000;SameSite=Lax';
	}

	function getCookie( name ) {
		var m = document.cookie.match( new RegExp( '(?:^|; )' + name + '=([^;]*)' ) );
		return m ? decodeURIComponent( m[ 1 ] ) : '';
	}

	function currentCols() {
		var m = list ? list.className.match( /columns-(\d)/ ) : null;
		return m ? parseInt( m[ 1 ], 10 ) : 3;
	}

	function apply( view, cols ) {
		if ( ! list ) {
			return;
		}
		list.classList.toggle( 'is-list', view === 'list' );
		list.classList.toggle( 'is-grid', view !== 'list' );
		if ( cols ) {
			list.className = list.className.replace( /\bcolumns-\d\b/, 'columns-' + cols );
		}
		var c = currentCols();
		viewBtns.forEach( function ( b ) {
			b.setAttribute( 'aria-pressed', b.getAttribute( 'data-view' ) === view ? 'true' : 'false' );
		} );
		colBtns.forEach( function ( b ) {
			b.setAttribute( 'aria-pressed', view !== 'list' && parseInt( b.getAttribute( 'data-cols' ), 10 ) === c ? 'true' : 'false' );
		} );
		document.documentElement.setAttribute( 'data-shop-view', view );
		syncMobile();
	}

	function currentMcols() {
		return list && list.classList.contains( 'm-cols-1' ) ? 1 : 2;
	}

	function setMcols( n ) {
		if ( ! list ) {
			return;
		}
		list.classList.remove( 'm-cols-1', 'm-cols-2' );
		list.classList.add( 'm-cols-' + n );
	}

	function syncMobile() {
		var isList = list && list.classList.contains( 'is-list' );
		var key = isList ? 'list' : 'grid' + currentMcols();
		mBtns.forEach( function ( b ) {
			b.setAttribute( 'aria-pressed', b.getAttribute( 'data-mview' ) === key ? 'true' : 'false' );
		} );
	}

	// A cached page may have been built for someone else's choice: use ours.
	var savedView = getCookie( 'gs_view' );
	var savedCols = parseInt( getCookie( 'gs_cols' ), 10 );
	var hasUrlChoice = /[?&](view|cols)=/.test( window.location.search );
	if ( list && ! hasUrlChoice && ( savedView || savedCols ) ) {
		apply( savedView === 'list' ? 'list' : ( savedView === 'grid' ? 'grid' : ( list.classList.contains( 'is-list' ) ? 'list' : 'grid' ) ), savedCols >= 2 && savedCols <= 4 ? savedCols : 0 );
	}
	// Choices made through the address are remembered as well.
	if ( hasUrlChoice && list ) {
		setCookie( 'gs_view', list.classList.contains( 'is-list' ) ? 'list' : 'grid' );
		setCookie( 'gs_cols', currentCols() );
		if ( window.history && window.history.replaceState ) {
			var u = new window.URL( window.location.href );
			u.searchParams.delete( 'view' );
			u.searchParams.delete( 'cols' );
			window.history.replaceState( null, '', u.toString() );
		}
	}

	// Phones: 1 per row, 2 per row or list.
	var savedM = parseInt( getCookie( 'gs_mcols' ), 10 );
	if ( list && ( savedM === 1 || savedM === 2 ) ) {
	setMcols( savedM );
	}
	syncMobile();
	mBtns.forEach( function ( b ) {
	b.addEventListener( 'click', function () {
		if ( ! list ) {
			return;
		}
		var key = b.getAttribute( 'data-mview' );
		if ( key === 'list' ) {
			apply( 'list', 0 );
			setCookie( 'gs_view', 'list' );
		} else {
			var n = key === 'grid1' ? 1 : 2;
			setMcols( n );
			setCookie( 'gs_mcols', n );
			apply( 'grid', 0 );
			setCookie( 'gs_view', 'grid' );
		}
	} );
	} );

	viewBtns.forEach( function ( b ) {
		b.addEventListener( 'click', function ( e ) {
			if ( ! list ) {
				return;
			}
			e.preventDefault();
			var view = b.getAttribute( 'data-view' );
			apply( view, 0 );
			setCookie( 'gs_view', view );
		} );
	} );

	colBtns.forEach( function ( b ) {
		b.addEventListener( 'click', function ( e ) {
			if ( ! list ) {
				return;
			}
			e.preventDefault();
			var cols = parseInt( b.getAttribute( 'data-cols' ), 10 );
			apply( 'grid', cols );
			setCookie( 'gs_view', 'grid' );
			setCookie( 'gs_cols', cols );
		} );
	} );

	// "Show 12 / 24 …" applies on change.
	document.querySelectorAll( 'select[data-autosubmit]' ).forEach( function ( s ) {
		s.addEventListener( 'change', function () {
			s.form.submit();
		} );
	} );

	/* ---------- sub-category photo row: arrows, drag, current in view ---------- */

	document.querySelectorAll( '[data-slider]' ).forEach( function ( slider ) {
		var view = slider.querySelector( '.subcat-slider__viewport' );
		var prev = slider.querySelector( '[data-slide="-1"]' );
		var next = slider.querySelector( '[data-slide="1"]' );
		if ( ! view ) {
			return;
		}
		var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

		function update() {
			var max = view.scrollWidth - view.clientWidth;
			var x = Math.round( view.scrollLeft );
			if ( prev ) {
				prev.hidden = x <= 2;
			}
			if ( next ) {
				next.hidden = x >= max - 2;
			}
			slider.classList.toggle( 'is-start', x <= 2 );
			slider.classList.toggle( 'is-end', x >= max - 2 );
			slider.classList.toggle( 'is-scrollable', max > 2 );
		}

		function go( dir ) {
			view.scrollBy( { left: dir * Math.max( 160, view.clientWidth * 0.8 ), behavior: reduce ? 'auto' : 'smooth' } );
		}
		if ( prev ) {
			prev.addEventListener( 'click', function () {
				go( -1 );
			} );
		}
		if ( next ) {
			next.addEventListener( 'click', function () {
				go( 1 );
			} );
		}
		view.addEventListener( 'scroll', update, { passive: true } );
		window.addEventListener( 'resize', update );

		// Show the current sub-category.
		var cur = view.querySelector( '.is-current' );
		if ( cur ) {
			var li = cur.parentElement;
			var left = li.offsetLeft - ( view.clientWidth - li.offsetWidth ) / 2;
			view.scrollLeft = Math.max( 0, left );
		}
		update();

		// Drag with the mouse (touch already swipes natively).
		var down = false;
		var startX = 0;
		var startLeft = 0;
		var moved = false;
		view.addEventListener( 'pointerdown', function ( e ) {
			if ( e.pointerType !== 'mouse' || e.button !== 0 ) {
				return;
			}
			down = true;
			moved = false;
			startX = e.clientX;
			startLeft = view.scrollLeft;
		} );
		window.addEventListener( 'pointermove', function ( e ) {
			if ( ! down ) {
				return;
			}
			var dx = e.clientX - startX;
			if ( Math.abs( dx ) > 5 ) {
				moved = true;
				slider.classList.add( 'is-dragging' );
			}
			if ( moved ) {
				view.scrollLeft = startLeft - dx;
			}
		} );
		window.addEventListener( 'pointerup', function () {
			down = false;
			setTimeout( function () {
				slider.classList.remove( 'is-dragging' );
			}, 0 );
		} );
		// A drag shouldn't open the card it ended on.
		view.addEventListener( 'click', function ( e ) {
			if ( moved ) {
				e.preventDefault();
				moved = false;
			}
		}, true );
	} );

	/* ---------- sidebar: inquiry list card ---------- */

	var card = document.querySelector( '[data-side-list]' );
	function syncCard() {
		if ( ! card ) {
			return;
		}
		var n = 0;
		try {
			var v = JSON.parse( window.localStorage.getItem( 'gamiInquiryList' ) );
			n = Array.isArray( v ) ? v.length : 0;
		} catch ( e ) {}
		card.querySelector( '.side-list__empty' ).hidden = n > 0;
		card.querySelector( '.side-list__filled' ).hidden = n === 0;
		card.querySelector( '.side-list__btn' ).hidden = n === 0;
		card.querySelector( '.side-list__num' ).textContent = n;
		card.classList.toggle( 'has-items', n > 0 );
	}
	syncCard();
	document.addEventListener( 'gami:inquiry', syncCard );
	window.addEventListener( 'storage', syncCard );
}() );
