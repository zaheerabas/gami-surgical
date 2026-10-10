/**
 * Gami Surgical live search.
 *
 * Combobox pattern: the input keeps focus, arrow keys move through the
 * results (aria-activedescendant), Enter opens the highlighted result or
 * submits the search, Escape closes. Results are built with DOM methods and
 * textContent only, so nothing from the server is injected as HTML.
 */
( function () {
	'use strict';

	var cfg = window.gamiSearch;
	if ( ! cfg ) {
		return;
	}
	var t = cfg.i18n;
	var RECENT_KEY = 'gamiRecentSearches';
	var cache = {};

	function fmt( str, val ) {
		return str.replace( '%s', val ).replace( '%d', val );
	}

	function el( tag, cls, text ) {
		var n = document.createElement( tag );
		if ( cls ) {
			n.className = cls;
		}
		if ( text !== undefined && text !== null ) {
			n.textContent = text;
		}
		return n;
	}

	/* recent searches live only in this browser */
	function getRecent() {
		try {
			return JSON.parse( window.localStorage.getItem( RECENT_KEY ) ) || [];
		} catch ( e ) {
			return [];
		}
	}
	function addRecent( q ) {
		q = q.trim();
		if ( q.length < 2 ) {
			return;
		}
		var list = getRecent().filter( function ( x ) {
			return x.toLowerCase() !== q.toLowerCase();
		} );
		list.unshift( q );
		try {
			window.localStorage.setItem( RECENT_KEY, JSON.stringify( list.slice( 0, 6 ) ) );
		} catch ( e ) {}
	}
	function clearRecent() {
		try {
			window.localStorage.removeItem( RECENT_KEY );
		} catch ( e ) {}
	}

	/* wrap matches of the query in <mark>, safely */
	function highlight( text, q ) {
		var frag = document.createDocumentFragment();
		var words = q.trim().split( /\s+/ ).filter( function ( w ) {
			return w.length > 1;
		} ).map( function ( w ) {
			return w.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' );
		} );
		if ( ! words.length ) {
			frag.appendChild( document.createTextNode( text ) );
			return frag;
		}
		var re = new RegExp( '(' + words.join( '|' ) + ')', 'ig' );
		var last = 0;
		text.replace( re, function ( m, g, idx ) {
			if ( idx > last ) {
				frag.appendChild( document.createTextNode( text.slice( last, idx ) ) );
			}
			frag.appendChild( el( 'mark', '', m ) );
			last = idx + m.length;
		} );
		if ( last < text.length ) {
			frag.appendChild( document.createTextNode( text.slice( last ) ) );
		}
		return frag;
	}

	function setup( form ) {
		var input = form.querySelector( '.gs-search__input' );
		var panel = form.querySelector( '.gs-search__panel' );
		var cat = form.querySelector( '.gs-search__cat' );
		var clear = form.querySelector( '.gs-search__clear' );
		var status = form.querySelector( '.gs-search__status' );
		var live = form.getAttribute( 'data-live' ) === '1';
		var uid = form.id || 'gs-search';
		var options = [];
		var active = -1;
		var timer = null;
		var controller = null;
		var lastQuery = '';

		if ( ! input || ! panel ) {
			return;
		}

		function open() {
			panel.hidden = false;
			input.setAttribute( 'aria-expanded', 'true' );
		}
		function close() {
			panel.hidden = true;
			input.setAttribute( 'aria-expanded', 'false' );
			input.removeAttribute( 'aria-activedescendant' );
			active = -1;
		}
		function setLoading( on ) {
			form.classList.toggle( 'is-loading', on );
		}
		function syncValue() {
			var has = input.value.length > 0;
			form.classList.toggle( 'has-value', has );
			if ( clear ) {
				clear.hidden = ! has;
			}
		}

		function register( node ) {
			node.id = uid + '-opt-' + options.length;
			node.setAttribute( 'role', 'option' );
			node.setAttribute( 'aria-selected', 'false' );
			node.addEventListener( 'mousemove', function () {
				move( options.indexOf( node ), true );
			} );
			options.push( node );
			return node;
		}

		function move( i, fromMouse ) {
			if ( ! options.length ) {
				return;
			}
			if ( active > -1 && options[ active ] ) {
				options[ active ].setAttribute( 'aria-selected', 'false' );
			}
			active = ( i + options.length ) % options.length;
			var node = options[ active ];
			node.setAttribute( 'aria-selected', 'true' );
			input.setAttribute( 'aria-activedescendant', node.id );
			if ( ! fromMouse ) {
				node.scrollIntoView( { block: 'nearest' } );
			}
		}

		function reset() {
			panel.textContent = '';
			options = [];
			active = -1;
			panel.setAttribute( 'role', 'listbox' );
			panel.setAttribute( 'aria-label', t.products );
		}

		/* empty box: recent + popular */
		function renderSuggestions() {
			reset();
			var recent = getRecent();
			var popular = cfg.popular || [];
			if ( ! recent.length && ! popular.length ) {
				close();
				return;
			}
			if ( recent.length ) {
				var head = el( 'p', 'gs-s-head' );
				head.appendChild( el( 'span', '', t.recent ) );
				var clr = el( 'button', '', t.clear );
				clr.type = 'button';
				clr.addEventListener( 'mousedown', function ( e ) {
					e.preventDefault();
				} );
				clr.addEventListener( 'click', function () {
					clearRecent();
					renderSuggestions();
					input.focus();
				} );
				head.appendChild( clr );
				panel.appendChild( head );
				panel.appendChild( chipList( recent ) );
			}
			if ( popular.length ) {
				panel.appendChild( el( 'p', 'gs-s-head', t.popular ) );
				panel.appendChild( chipList( popular ) );
			}
			open();
		}

		function chipList( terms ) {
			var ul = el( 'ul', 'gs-s-chips' );
			ul.setAttribute( 'role', 'presentation' );
			terms.forEach( function ( term ) {
				var li = el( 'li' );
				li.setAttribute( 'role', 'presentation' );
				var chip = register( el( 'span', 'gs-s-chip', term ) );
				chip.addEventListener( 'mousedown', function ( e ) {
					e.preventDefault();
				} );
				chip.addEventListener( 'click', function () {
					input.value = term;
					syncValue();
					search( true );
				} );
				chip.dataset.term = term;
				li.appendChild( chip );
				ul.appendChild( li );
			} );
			return ul;
		}

		function productRow( p, q ) {
			var a = register( el( 'a', 'gs-s-item' ) );
			a.href = p.url;
			if ( p.image ) {
				var img = el( 'img', 'gs-s-thumb' );
				img.src = p.image;
				img.alt = '';
				img.loading = 'lazy';
				img.width = 44;
				img.height = 44;
				a.appendChild( img );
			} else {
				a.appendChild( el( 'span', 'gs-s-thumb gs-s-thumb--empty' ) );
			}
			var body = el( 'span', 'gs-s-body' );
			var title = el( 'span', 'gs-s-title' );
			title.appendChild( highlight( p.title, q ) );
			body.appendChild( title );
			var metaParts = [];
			if ( p.cat ) {
				metaParts.push( p.cat );
			}
			var meta = el( 'span', 'gs-s-meta' );
			if ( metaParts.length ) {
				meta.appendChild( document.createTextNode( metaParts.join( ' · ' ) + ( p.sku ? ' · ' : '' ) ) );
			}
			if ( p.sku ) {
				meta.appendChild( document.createTextNode( t.catNo + ' ' ) );
				meta.appendChild( highlight( p.sku, q ) );
			}
			if ( meta.childNodes.length ) {
				body.appendChild( meta );
			}
			if ( p.stock ) {
				body.appendChild( el( 'span', 'gs-s-stock', p.stock ) );
			}
			a.appendChild( body );
			if ( p.quote ) {
				a.appendChild( el( 'span', 'gs-s-quote', t.inquire ) );
			} else if ( p.price ) {
				a.appendChild( el( 'span', 'gs-s-price', p.price ) );
			}
			return a;
		}

		function plainRow( item, q, extra ) {
			var a = register( el( 'a', 'gs-s-item gs-s-item--plain' ) );
			a.href = item.url;
			var title = el( 'span', 'gs-s-title' );
			title.appendChild( highlight( item.title, q ) );
			a.appendChild( title );
			if ( extra ) {
				a.appendChild( el( 'span', 'gs-s-meta', extra ) );
			}
			return a;
		}

		function renderResults( data, q ) {
			reset();
			var hasSide = data.categories.length || data.pages.length;
			var wrap = el( 'div', 'gs-s-cols' + ( hasSide && data.products.length ? ' gs-s-cols--split' : '' ) );

			if ( data.products.length ) {
				var main = el( 'div', 'gs-s-main' );
				main.appendChild( el( 'p', 'gs-s-head', t.products ) );
				var ul = el( 'ul', 'gs-s-list' );
				ul.setAttribute( 'role', 'presentation' );
				data.products.forEach( function ( p ) {
					var li = el( 'li' );
					li.setAttribute( 'role', 'presentation' );
					li.appendChild( productRow( p, q ) );
					ul.appendChild( li );
				} );
				main.appendChild( ul );
				wrap.appendChild( main );
			}

			if ( hasSide ) {
				var side = el( 'div', 'gs-s-side' );
				if ( data.categories.length ) {
					side.appendChild( el( 'p', 'gs-s-head', t.categories ) );
					data.categories.forEach( function ( c ) {
						side.appendChild( plainRow( c, q, fmt( t.items, c.count ) ) );
					} );
				}
				if ( data.pages.length ) {
					side.appendChild( el( 'p', 'gs-s-head', t.pages ) );
					data.pages.forEach( function ( pg ) {
						side.appendChild( plainRow( pg, q ) );
					} );
				}
				wrap.appendChild( side );
			}

			if ( ! data.products.length && ! hasSide ) {
				var empty = el( 'div', 'gs-s-empty' );
				empty.appendChild( el( 'p', 'gs-s-empty__title', fmt( t.noResults, q ) ) );
				empty.appendChild( el( 'p', 'gs-s-empty__hint', t.noHint ) );
				var btn = register( el( 'a', 'button button--primary', t.askQuote ) );
				btn.href = cfg.quoteUrl + ( cfg.quoteUrl.indexOf( '?' ) > -1 ? '&' : '?' ) + 'message=' + encodeURIComponent( fmt( t.lookingFor, q ) );
				empty.appendChild( btn );
				panel.appendChild( empty );
				if ( ( cfg.popular || [] ).length ) {
					panel.appendChild( el( 'p', 'gs-s-head', t.popular ) );
					panel.appendChild( chipList( cfg.popular ) );
				}
				status.textContent = fmt( t.noResults, q );
				open();
				return;
			}

			panel.appendChild( wrap );

			var all = register( el( 'a', 'gs-s-all' ) );
			all.href = data.url;
			all.appendChild( el( 'span', '', fmt( t.viewAll, q ) + ( data.total ? ' (' + data.total + ')' : '' ) ) );
			all.appendChild( el( 'kbd', '', 'Enter ↵' ) );
			panel.appendChild( all );

			status.textContent = fmt( t.found, options.length );
			open();
		}

		function search( immediate ) {
			var q = input.value.trim();
			syncValue();
			clearTimeout( timer );
			if ( q.length < cfg.minChars ) {
				if ( controller ) {
					controller.abort();
				}
				setLoading( false );
				lastQuery = '';
				renderSuggestions();
				return;
			}
			if ( ! live ) {
				return;
			}
			timer = setTimeout( function () {
				fetchResults( q );
			}, immediate ? 0 : 200 );
		}

		function fetchResults( q ) {
			var c = cat ? cat.value : '';
			var key = q.toLowerCase() + '|' + c;
			lastQuery = key;
			if ( cache[ key ] ) {
				renderResults( cache[ key ], q );
				return;
			}
			if ( controller ) {
				controller.abort();
			}
			controller = window.AbortController ? new window.AbortController() : null;
			setLoading( true );
			var url = cfg.endpoint + ( cfg.endpoint.indexOf( '?' ) > -1 ? '&' : '?' ) + 'q=' + encodeURIComponent( q ) + '&cat=' + encodeURIComponent( c );
			window.fetch( url, { signal: controller ? controller.signal : undefined, headers: { Accept: 'application/json' } } )
				.then( function ( r ) {
					if ( ! r.ok ) {
						throw new Error( r.status );
					}
					return r.json();
				} )
				.then( function ( data ) {
					cache[ key ] = data;
					if ( lastQuery === key && document.activeElement === input ) {
						renderResults( data, q );
					}
				} )
				.catch( function ( err ) {
					if ( err && err.name === 'AbortError' ) {
						return;
					}
					reset();
					panel.appendChild( el( 'p', 'gs-s-empty', t.error ) );
					open();
				} )
				.then( function () {
					setLoading( false );
				} );
		}

		input.addEventListener( 'input', function () {
			search( false );
		} );
		input.addEventListener( 'focus', function () {
			search( true );
		} );
		if ( cat ) {
			cat.addEventListener( 'change', function () {
				if ( input.value.trim().length >= cfg.minChars ) {
					input.focus();
					search( true );
				}
			} );
		}
		if ( clear ) {
			clear.addEventListener( 'click', function () {
				input.value = '';
				syncValue();
				input.focus();
				renderSuggestions();
			} );
		}

		input.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'ArrowDown' ) {
				e.preventDefault();
				if ( panel.hidden ) {
					search( true );
				} else {
					move( active + 1 );
				}
			} else if ( e.key === 'ArrowUp' ) {
				e.preventDefault();
				move( active - 1 );
			} else if ( e.key === 'Enter' && ! panel.hidden && active > -1 && options[ active ] ) {
				e.preventDefault();
				var opt = options[ active ];
				if ( opt.dataset.term ) {
					opt.click();
				} else {
					addRecent( input.value );
					window.location.href = opt.href;
				}
			} else if ( e.key === 'Escape' ) {
				if ( ! panel.hidden ) {
					e.stopPropagation();
					close();
				} else if ( input.value ) {
					input.value = '';
					syncValue();
				}
			}
		} );

		// Remember what people search for (in their own browser only).
		form.addEventListener( 'submit', function ( e ) {
			if ( ! input.value.trim() ) {
				e.preventDefault();
				input.focus();
				return;
			}
			addRecent( input.value );
		} );
		panel.addEventListener( 'click', function ( e ) {
			var a = e.target.closest( 'a' );
			if ( a ) {
				addRecent( input.value );
			}
		} );
		// Keep focus in the input when clicking inside the panel.
		panel.addEventListener( 'mousedown', function ( e ) {
			if ( ! e.target.closest( 'a' ) ) {
				e.preventDefault();
			}
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( ! form.contains( e.target ) ) {
				close();
			}
		} );
		form.addEventListener( 'focusout', function ( e ) {
			if ( e.relatedTarget && ! form.contains( e.relatedTarget ) ) {
				close();
			}
		} );

		syncValue();
	}

	var forms = Array.prototype.slice.call( document.querySelectorAll( '.gs-search' ) );
	forms.forEach( setup );

	// Press "/" anywhere to jump to the search box.
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey ) {
			return;
		}
		var tag = ( document.activeElement && document.activeElement.tagName ) || '';
		if ( /INPUT|TEXTAREA|SELECT/.test( tag ) || ( document.activeElement && document.activeElement.isContentEditable ) ) {
			return;
		}
		var visible = forms.filter( function ( f ) {
			return f.offsetParent !== null;
		} )[ 0 ];
		if ( visible ) {
			e.preventDefault();
			visible.querySelector( '.gs-search__input' ).focus();
		} else {
			var toggle = document.querySelector( '.header-search-toggle' );
			if ( toggle ) {
				e.preventDefault();
				toggle.click();
			}
		}
	} );
}() );
