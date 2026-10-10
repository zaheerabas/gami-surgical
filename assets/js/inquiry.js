/**
 * Gami Surgical inquiries.
 *
 * Everywhere:
 * - [data-inquiry-action="add"]  adds the product to the list and shows a toast.
 * - [data-inquiry-action="send"] adds the product, then opens the Inquiry
 *   page (the link also works without JavaScript).
 * - The header shows how many products are in the list.
 * On the Inquiry page ([data-inquiry-app]):
 * - renders the list (size and quantity per product, remove with undo),
 * - sends the form in place and shows the thank-you message.
 * The list lives in localStorage until the inquiry is sent.
 */
( function () {
	'use strict';

	var cfg = window.gamiInquiry;
	if ( ! cfg ) {
		return;
	}
	var t = cfg.i18n;
	var KEY = 'gamiInquiryList';
	var toast = document.querySelector( '.gs-toast' );
	var toastTimer = null;
	var app = document.querySelector( '[data-inquiry-app]' );
	var render = function () {};

	/* ---------- storage ---------- */

	function read() {
		try {
			var v = JSON.parse( window.localStorage.getItem( KEY ) );
			return Array.isArray( v ) ? v.filter( function ( x ) {
				return x && x.id;
			} ) : [];
		} catch ( e ) {
			return [];
		}
	}
	var items = read();

	function save() {
		try {
			window.localStorage.setItem( KEY, JSON.stringify( items ) );
		} catch ( e ) {}
		updateCount();
		document.dispatchEvent( new window.CustomEvent( 'gami:inquiry', { detail: { count: items.length } } ) );
	}

	function fromData( src ) {
		return {
			id: parseInt( src.id, 10 ),
			name: src.name || '',
			sku: src.sku || '',
			image: src.image || '',
			url: src.url || '',
			sizes: src.sizes || '',
			qty: 1,
			size: ''
		};
	}

	function fromButton( btn ) {
		var item = fromData( {
			id: btn.getAttribute( 'data-inquiry-id' ),
			name: btn.getAttribute( 'data-inquiry-name' ),
			sku: btn.getAttribute( 'data-inquiry-sku' ),
			image: btn.getAttribute( 'data-inquiry-image' ),
			url: btn.getAttribute( 'data-inquiry-url' ),
			sizes: btn.getAttribute( 'data-inquiry-sizes' )
		} );
		// Product page: use the size and quantity chosen there.
		var conf = document.querySelector( '[data-inquiry-config="' + item.id + '"]' );
		if ( conf ) {
			var r = conf.querySelector( 'input[type="radio"]:checked' );
			var q = conf.querySelector( '[data-config-qty]' );
			item.size = r ? r.value : '';
			item.qty = q ? Math.max( 1, parseInt( q.value, 10 ) || 1 ) : 1;
			item.fromConfig = true;
		}
		return item;
	}

	function same( a, b ) {
		return a.id === b.id && ( a.size || '' ) === ( b.size || '' );
	}

	function has( id ) {
		return items.some( function ( x ) {
			return x.id === id;
		} );
	}

	/**
	 * Add a product. The same product in another size is a separate line.
	 * Returns 'added', 'updated' (new quantity for an existing line) or false.
	 */
	function add( item ) {
		if ( ! item.id ) {
			return false;
		}
		var found = items.filter( function ( x ) {
			return same( x, item );
		} )[ 0 ];
		if ( found ) {
			if ( item.fromConfig && found.qty !== item.qty ) {
				found.qty = item.qty;
				save();
				return 'updated';
			}
			return false;
		}
		delete item.fromConfig;
		items.push( item );
		save();
		return 'added';
	}

	function countLabel( n ) {
		return n === 1 ? t.product : t.products.replace( '%d', n );
	}

	/* ---------- header count, button states ---------- */

	function updateCount() {
		var n = items.length;
		document.querySelectorAll( '.gs-inquiry-count' ).forEach( function ( b ) {
			b.textContent = n;
			b.hidden = n === 0;
		} );
		document.querySelectorAll( '.header-inquiry, .header-cta--bar' ).forEach( function ( a ) {
			var base = a.getAttribute( 'data-label' ) || a.getAttribute( 'title' ) || a.textContent.replace( /\d+/g, '' ).trim();
			a.setAttribute( 'data-label', base );
			a.setAttribute( 'aria-label', n ? base + ' (' + countLabel( n ) + ')' : base );
		} );
		document.querySelectorAll( '[data-inquiry-action="add"]' ).forEach( function ( b ) {
			var inList = has( parseInt( b.getAttribute( 'data-inquiry-id' ), 10 ) );
			b.classList.toggle( 'is-added', inList );
			b.setAttribute( 'aria-pressed', inList ? 'true' : 'false' );
		} );
	}

	/* ---------- toast ---------- */

	function hideToast() {
		if ( ! toast ) {
			return;
		}
		toast.classList.remove( 'is-visible' );
		setTimeout( function () {
			if ( ! toast.classList.contains( 'is-visible' ) ) {
				toast.hidden = true;
			}
		}, 220 );
	}

	function showToast( text, action ) {
		if ( ! toast ) {
			return;
		}
		toast.textContent = '';
		var span = document.createElement( 'span' );
		span.textContent = text;
		toast.appendChild( span );
		if ( action ) {
			var a = document.createElement( action.href ? 'a' : 'button' );
			if ( action.href ) {
				a.href = action.href;
			} else {
				a.type = 'button';
				a.addEventListener( 'click', function () {
					hideToast();
					action.onClick();
				} );
			}
			a.textContent = action.label;
			toast.appendChild( a );
		}
		toast.hidden = false;
		window.requestAnimationFrame( function () {
			toast.classList.add( 'is-visible' );
		} );
		clearTimeout( toastTimer );
		toastTimer = setTimeout( hideToast, 5000 );
	}

	/* ---------- clicks anywhere ---------- */

	document.addEventListener( 'click', function ( e ) {
		var btn = e.target.closest( '[data-inquiry-action]' );
		if ( ! btn ) {
			return;
		}
		var item = fromButton( btn );
		if ( btn.getAttribute( 'data-inquiry-action' ) === 'add' ) {
			e.preventDefault();
			var added = add( item );
			showToast( added === 'added' ? t.added : ( added === 'updated' ? t.updated : t.already ), { label: t.view, href: cfg.page } );
			return;
		}
		// "Send inquiry": save to the list, then open the inquiry page.
		add( item );
		if ( btn.tagName === 'A' && ! e.ctrlKey && ! e.metaKey && ! e.shiftKey ) {
			e.preventDefault();
			window.location.href = cfg.page;
		}
	} );

	window.addEventListener( 'storage', function ( e ) {
		if ( e.key === KEY ) {
			items = read();
			updateCount();
			render();
		}
	} );
	// Back/forward cache: refresh the count.
	window.addEventListener( 'pageshow', function ( e ) {
		if ( e.persisted ) {
			items = read();
			updateCount();
			render();
		}
	} );

	updateCount();

	/* ---------- product page: sticky bar on phones ---------- */

	var bar = document.querySelector( '.mobile-inquiry-bar' );
	var mainButtons = document.querySelector( '.inquiry-actions--single' );
	if ( bar && mainButtons && 'IntersectionObserver' in window ) {
		// The bar repeats the buttons above; hide it from assistive tech and the
		// tab order (the originals stay reachable), show it only when they scroll away.
		bar.querySelectorAll( 'a, button' ).forEach( function ( n ) {
			n.setAttribute( 'tabindex', '-1' );
		} );
		var band = document.querySelector( '.inquiry-actions--band' );
		var visible = {};
		var io = new window.IntersectionObserver( function ( entries ) {
			entries.forEach( function ( en ) {
				visible[ en.target === mainButtons ? 'main' : 'band' ] = en.isIntersecting;
			} );
			var past = mainButtons.getBoundingClientRect().bottom < 0;
			bar.classList.toggle( 'is-visible', past && ! visible.main && ! visible.band );
		} );
		io.observe( mainButtons );
		if ( band ) {
			io.observe( band );
		}
		window.addEventListener( 'scroll', function () {
			var past = mainButtons.getBoundingClientRect().bottom < 0;
			if ( ! past ) {
				bar.classList.remove( 'is-visible' );
			}
		}, { passive: true } );
	}

	/* ======================================================================
	 * Inquiry page
	 * ==================================================================== */

	if ( ! app ) {
		return;
	}
	var form = app.querySelector( '.gs-inq__form' );
	var listEl = app.querySelector( '.gs-inq__items' );
	var emptyEl = app.querySelector( '.gs-inq__empty' );
	var moreEl = app.querySelector( '.gs-inq__more' );
	var countEl = app.querySelector( '.gs-inq__count' );
	var errorBox = app.querySelector( '.gs-inq__error' );
	var submitBtn = app.querySelector( '.gs-inq__submit' );
	var layout = app.querySelector( '.gs-inquiry__layout' );
	var done = app.querySelector( '.gs-inquiry__done' );

	// A product passed in the address (?add=ID) joins the saved list.
	var preload = listEl.getAttribute( 'data-preload' );
	if ( preload ) {
		try {
			add( fromData( JSON.parse( preload ) ) );
		} catch ( e ) {}
	}
	// Drop ?add= from the address so a refresh doesn't re-add a removed item.
	if ( window.history && window.history.replaceState && /[?&]add=/.test( window.location.search ) ) {
		var u = new window.URL( window.location.href );
		u.searchParams.delete( 'add' );
		window.history.replaceState( null, '', u.toString() );
	}
	if ( app.classList.contains( 'is-sent' ) ) {
		items = [];
		save();
	}

	function el( tag, cls, text ) {
		var n = document.createElement( tag );
		if ( cls ) {
			n.className = cls;
		}
		if ( text !== undefined ) {
			n.textContent = text;
		}
		return n;
	}

	function row( item, i ) {
		var li = el( 'li', 'gs-inq__item' );
		li.setAttribute( 'data-id', item.id );

		var img = el( 'img', 'gs-inq__img' );
		img.alt = '';
		img.width = 72;
		img.height = 72;
		img.loading = 'lazy';
		if ( item.image ) {
			img.src = item.image;
		}
		li.appendChild( img );

		var info = el( 'div', 'gs-inq__info' );
		var name = el( 'a', 'gs-inq__name', item.name );
		name.href = item.url;
		info.appendChild( name );
		if ( item.sku ) {
			info.appendChild( el( 'span', 'gs-inq__sku', t.catNo + ' ' + item.sku ) );
		}
		var hid = el( 'input' );
		hid.type = 'hidden';
		hid.name = 'items[' + i + '][id]';
		hid.value = item.id;
		info.appendChild( hid );

		var opts = el( 'div', 'gs-inq__opts' );
		var sizes = ( item.sizes || '' ).split( ',' ).map( function ( s ) {
			return s.trim();
		} ).filter( Boolean );

		var sizeWrap = el( 'label', 'gs-inq__opt' );
		sizeWrap.appendChild( el( 'span', 'gs-inq__opt-label', sizes.length ? t.size : t.sizeText ) );
		var sizeInput;
		if ( sizes.length ) {
			sizeInput = el( 'select' );
			var any = el( 'option', '', t.anySize );
			any.value = '';
			sizeInput.appendChild( any );
			sizes.forEach( function ( s ) {
				var o = el( 'option', '', s );
				o.value = s;
				o.selected = s === item.size;
				sizeInput.appendChild( o );
			} );
		} else {
			sizeInput = el( 'input' );
			sizeInput.type = 'text';
			sizeInput.maxLength = 60;
			sizeInput.value = item.size || '';
		}
		sizeInput.name = 'items[' + i + '][size]';
		sizeInput.addEventListener( 'change', function () {
			item.size = sizeInput.value;
			save();
		} );
		sizeWrap.appendChild( sizeInput );
		opts.appendChild( sizeWrap );

		var qtyWrap = el( 'div', 'gs-inq__opt gs-inq__opt--qty' );
		var qtyId = 'gs-qty-' + i;
		var qtyLabel = el( 'label', 'gs-inq__opt-label', t.qty );
		qtyLabel.setAttribute( 'for', qtyId );
		qtyWrap.appendChild( qtyLabel );
		var stepper = el( 'span', 'gs-qty' );
		var minus = el( 'button', 'gs-qty__btn', '−' );
		minus.type = 'button';
		minus.setAttribute( 'aria-label', t.qty + ' −' );
		var qty = el( 'input' );
		qty.type = 'number';
		qty.min = '1';
		qty.step = '1';
		qty.inputMode = 'numeric';
		qty.id = qtyId;
		qty.name = 'items[' + i + '][qty]';
		qty.value = item.qty || 1;
		var plus = el( 'button', 'gs-qty__btn', '+' );
		plus.type = 'button';
		plus.setAttribute( 'aria-label', t.qty + ' +' );
		function setQty( v ) {
			v = Math.max( 1, Math.min( 1000000, parseInt( v, 10 ) || 1 ) );
			qty.value = v;
			item.qty = v;
			save();
		}
		qty.addEventListener( 'change', function () {
			setQty( qty.value );
		} );
		minus.addEventListener( 'click', function () {
			setQty( ( parseInt( qty.value, 10 ) || 1 ) - 1 );
		} );
		plus.addEventListener( 'click', function () {
			setQty( ( parseInt( qty.value, 10 ) || 0 ) + 1 );
		} );
		stepper.appendChild( minus );
		stepper.appendChild( qty );
		stepper.appendChild( plus );
		qtyWrap.appendChild( stepper );
		opts.appendChild( qtyWrap );
		info.appendChild( opts );
		li.appendChild( info );

		var rm = el( 'button', 'gs-inq__remove' );
		rm.type = 'button';
		rm.innerHTML = '<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>';
		rm.appendChild( el( 'span', 'gs-inq__remove-text', t.remove ) );
		rm.setAttribute( 'aria-label', t.remove + ': ' + item.name );
		rm.addEventListener( 'click', function () {
			var at = items.indexOf( item );
			items.splice( at, 1 );
			save();
			render();
			showToast( t.removed.replace( '%s', item.name ), {
				label: t.undo,
				onClick: function () {
					items.splice( at, 0, item );
					save();
					render();
				}
			} );
			var buttons = listEl.querySelectorAll( '.gs-inq__remove' );
			var next = buttons[ Math.min( at, buttons.length - 1 ) ] || form.querySelector( '[name="message"]' );
			if ( next ) {
				next.focus();
			}
		} );
		li.appendChild( rm );
		return li;
	}

	render = function () {
		listEl.textContent = '';
		var n = items.length;
		countEl.textContent = n ? countLabel( n ) : '';
		emptyEl.hidden = n > 0;
		moreEl.hidden = n === 0;
		items.forEach( function ( item, i ) {
			listEl.appendChild( row( item, i ) );
		} );
	};
	render();

	/* ---------- errors ---------- */

	function fieldError( name, text ) {
		var input = form.querySelector( '[name="' + name + '"]' );
		if ( ! input ) {
			return;
		}
		input.setAttribute( 'aria-invalid', 'true' );
		var msg = el( 'span', 'gs-field__error', text );
		msg.id = 'gs-err-' + name;
		input.setAttribute( 'aria-describedby', msg.id );
		input.closest( '.gs-field' ).appendChild( msg );
	}

	function clearErrors() {
		form.querySelectorAll( '.gs-field__error' ).forEach( function ( n ) {
			n.remove();
		} );
		form.querySelectorAll( '[aria-invalid]' ).forEach( function ( n ) {
			n.removeAttribute( 'aria-invalid' );
			n.removeAttribute( 'aria-describedby' );
		} );
		errorBox.hidden = true;
	}

	// A corrected field loses its error straight away.
	form.addEventListener( 'input', function ( e ) {
		var target = e.target;
		if ( target.getAttribute && target.getAttribute( 'aria-invalid' ) ) {
			target.removeAttribute( 'aria-invalid' );
			target.removeAttribute( 'aria-describedby' );
			var m = form.querySelector( '#gs-err-' + target.name );
			if ( m ) {
				m.remove();
			}
		}
	} );

	function focusFirstError() {
		var bad = form.querySelector( '[aria-invalid="true"]' );
		if ( bad ) {
			bad.scrollIntoView( { block: 'center', behavior: 'smooth' } );
			bad.focus( { preventScroll: true } );
		}
	}

	/* ---------- submit ---------- */

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		clearErrors();

		var f = form.elements;
		var local = {};
		if ( ! f.namedItem( 'name' ).value.trim() ) {
			local.name = f.namedItem( 'name' ).validationMessage || 'Required';
		}
		if ( ! f.namedItem( 'email' ).value.trim() || ! f.namedItem( 'email' ).checkValidity() ) {
			local.email = f.namedItem( 'email' ).validationMessage || 'Invalid email';
		}
		if ( ! items.length && f.namedItem( 'message' ).value.trim().length < 5 ) {
			local.message = emptyEl.querySelector( 'p:last-child' ).textContent;
		}
		if ( Object.keys( local ).length ) {
			Object.keys( local ).forEach( function ( k ) {
				fieldError( k, local[ k ] );
			} );
			focusFirstError();
			return;
		}

		var data = new window.FormData( form );
		data.set( 'source', window.location.href );

		var label = submitBtn.textContent;
		submitBtn.disabled = true;
		submitBtn.setAttribute( 'aria-busy', 'true' );
		submitBtn.textContent = t.sending;

		window.fetch( cfg.ajax, { method: 'POST', body: data, credentials: 'same-origin' } )
			.then( function ( r ) {
				return r.json().catch( function () {
					return { success: false };
				} );
			} )
			.then( function ( res ) {
				if ( res && res.success ) {
					items = [];
					save();
					form.reset();
					layout.hidden = true;
					done.hidden = false;
					app.classList.add( 'is-sent' );
					var ref = res.data.reference;
					var refEl = done.querySelector( '.gs-inquiry__ref' );
					refEl.hidden = ! ref;
					refEl.querySelector( 'strong' ).textContent = ref || '';
					if ( res.data.message ) {
						done.querySelector( '.gs-inquiry__done-text' ).textContent = res.data.message;
					}
					if ( res.data.url && window.history && window.history.replaceState ) {
						window.history.replaceState( null, '', res.data.url );
					}
					window.scrollTo( { top: 0, behavior: 'smooth' } );
					done.querySelector( '.gs-inquiry__done-title' ).focus( { preventScroll: true } );
					return;
				}
				var d = ( res && res.data ) || {};
				if ( d.fields ) {
					Object.keys( d.fields ).forEach( function ( k ) {
						fieldError( k, d.fields[ k ] );
					} );
				}
				errorBox.textContent = d.message || t.error;
				errorBox.hidden = false;
				if ( d.fields ) {
					focusFirstError();
				} else {
					errorBox.scrollIntoView( { block: 'center', behavior: 'smooth' } );
				}
			} )
			.catch( function () {
				errorBox.textContent = t.error;
				errorBox.hidden = false;
			} )
			.then( function () {
				submitBtn.disabled = false;
				submitBtn.removeAttribute( 'aria-busy' );
				submitBtn.textContent = label;
			} );
	} );
}() );
