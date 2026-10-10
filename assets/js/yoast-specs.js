/**
 * Feed instrument spec fields into Yoast SEO's content analysis.
 */
( function () {
	'use strict';

	function boot() {
		if ( typeof window.YoastSEO === 'undefined' || ! window.YoastSEO.app || ! window.YoastSEO.app.registerPlugin ) {
			return;
		}
		var name = 'gamiSpecs';
		window.YoastSEO.app.registerPlugin( name, { status: 'ready' } );
		window.YoastSEO.app.registerModification( 'content', function ( content ) {
			var inputs = document.querySelectorAll( '#gami_instrument_specs input[type="text"], #gami_product_specs input[type="text"]' );
			var extra = [];
			inputs.forEach( function ( input ) {
				if ( input.value ) {
					var label = document.querySelector( 'label[for="' + input.id + '"]' );
					extra.push( '<p>' + ( label ? label.textContent + ': ' : '' ) + input.value + '</p>' );
				}
			} );
			return content + extra.join( '' );
		}, name, 5 );

		document.querySelectorAll( '#gami_instrument_specs input, #gami_product_specs input' ).forEach( function ( input ) {
			input.addEventListener( 'change', function () {
				window.YoastSEO.app.pluginReloaded( name );
			} );
		} );
	}

	if ( document.readyState === 'complete' ) {
		boot();
	} else {
		window.addEventListener( 'load', boot );
	}
}() );
