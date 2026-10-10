<?php
/**
 * Yoast SEO integration: breadcrumbs, Product schema for instruments, sitemap tweaks.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Breadcrumbs. Uses Yoast when active (enable under Yoast SEO > Settings > Breadcrumbs),
 * otherwise prints a simple trail.
 */
/**
 * Breadcrumb separator: a small chevron drawn in CSS.
 */
function gami_breadcrumb_sep() {
	return '<span class="breadcrumbs__sep" aria-hidden="true"></span>';
}

/**
 * Yoast breadcrumbs: same chevron separator as everywhere else.
 */
add_filter(
	'wpseo_breadcrumb_separator',
	function () {
		return gami_breadcrumb_sep();
	}
);

/**
 * Yoast breadcrumbs: add the catalogue (Products) after Home on product
 * category and product pages, as WooCommerce's own breadcrumbs do here.
 */
function gami_yoast_breadcrumb_catalogue( $links ) {
	if ( ! function_exists( 'wc_get_page_id' ) || ! ( is_product() || is_product_taxonomy() ) || count( $links ) < 2 ) {
		return $links;
	}
	$shop_id = wc_get_page_id( 'shop' );
	if ( $shop_id <= 0 ) {
		return $links;
	}
	$shop_url = get_permalink( $shop_id );
	foreach ( $links as $link ) {
		if ( ( isset( $link['url'] ) && untrailingslashit( $link['url'] ) === untrailingslashit( $shop_url ) ) || ( isset( $link['ptarchive'] ) && 'product' === $link['ptarchive'] ) ) {
			return $links;
		}
	}
	array_splice(
		$links,
		1,
		0,
		array(
			array(
				'url'  => $shop_url,
				'text' => get_the_title( $shop_id ),
			),
		)
	);
	return $links;
}
add_filter( 'wpseo_breadcrumb_links', 'gami_yoast_breadcrumb_catalogue' );

function gami_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	if ( function_exists( 'yoast_breadcrumb' ) ) {
		$options = get_option( 'wpseo_titles' );
		if ( ! empty( $options['breadcrumbs-enable'] ) ) {
			yoast_breadcrumb( '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'gami-surgical' ) . '"><p>', '</p></nav>' );
			return;
		}
	}

	// WooCommerce pages: use WooCommerce's own trail, styled like ours.
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		woocommerce_breadcrumb(
			array(
				'delimiter'   => gami_breadcrumb_sep(),
				'wrap_before' => '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'gami-surgical' ) . '"><p>',
				'wrap_after'  => '</p></nav>',
				'home'        => __( 'Home', 'gami-surgical' ),
			)
		);
		return;
	}

	$crumbs   = array();
	$crumbs[] = array( home_url( '/' ), __( 'Home', 'gami-surgical' ) );

	if ( is_singular( 'instrument' ) || is_tax( 'instrument_category' ) || is_post_type_archive( 'instrument' ) ) {
		$crumbs[] = array( get_post_type_archive_link( 'instrument' ), __( 'Instruments', 'gami-surgical' ) );
	}
	if ( is_singular( 'instrument' ) ) {
		$terms = get_the_terms( get_the_ID(), 'instrument_category' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$crumbs[] = array( get_term_link( $terms[0] ), $terms[0]->name );
		}
		$crumbs[] = array( '', get_the_title() );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$crumbs[] = array( '', single_term_title( '', false ) );
	} elseif ( is_singular( 'post' ) ) {
		$page_for_posts = (int) get_option( 'page_for_posts' );
		if ( $page_for_posts ) {
			$crumbs[] = array( get_permalink( $page_for_posts ), get_the_title( $page_for_posts ) );
		}
		$crumbs[] = array( '', get_the_title() );
	} elseif ( is_page() ) {
		foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $ancestor ) {
			$crumbs[] = array( get_permalink( $ancestor ), get_the_title( $ancestor ) );
		}
		$crumbs[] = array( '', get_the_title() );
	} elseif ( is_search() ) {
		$crumbs[] = array( '', __( 'Search results', 'gami-surgical' ) );
	} elseif ( is_404() ) {
		$crumbs[] = array( '', __( 'Page not found', 'gami-surgical' ) );
	} elseif ( is_home() ) {
		$crumbs[] = array( '', single_post_title( '', false ) );
	}

	echo '<nav class="breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'gami-surgical' ) . '"><ol>';
	$last = count( $crumbs ) - 1;
	foreach ( $crumbs as $i => $crumb ) {
		if ( $crumb[0] && $i !== $last ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $crumb[0] ), esc_html( $crumb[1] ) );
		} else {
			printf( '<li aria-current="page">%s</li>', esc_html( $crumb[1] ) );
		}
	}
	echo '</ol></nav>';
}

/**
 * Add a Product node to Yoast's schema graph on single instruments.
 */
function gami_yoast_schema_pieces( $pieces, $context ) {
	if ( class_exists( '\Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece' ) ) {
		require_once GAMI_DIR . '/inc/schema/class-instrument-schema.php';
		$pieces[] = new Gami_Instrument_Schema( $context );
	}
	return $pieces;
}
add_filter( 'wpseo_schema_graph_pieces', 'gami_yoast_schema_pieces', 11, 2 );

/**
 * Mark instrument and product pages as ItemPage in Yoast's WebPage node.
 */
function gami_yoast_webpage_type( $type ) {
	if ( is_singular( array( 'instrument', 'product' ) ) ) {
		return 'ItemPage';
	}
	if ( is_post_type_archive( array( 'instrument', 'product' ) ) || is_tax( array( 'instrument_category', 'product_cat', 'product_tag' ) ) ) {
		return 'CollectionPage';
	}
	return $type;
}
add_filter( 'wpseo_schema_webpage_type', 'gami_yoast_webpage_type' );

/**
 * Make sure catalogue images are included in the instrument sitemap.
 */
function gami_yoast_sitemap_images( $images, $post_id ) {
	if ( 'instrument' === get_post_type( $post_id ) && has_post_thumbnail( $post_id ) ) {
		$src = get_the_post_thumbnail_url( $post_id, 'full' );
		if ( $src ) {
			$images[] = array( 'src' => $src );
		}
	}
	return $images;
}
add_filter( 'wpseo_sitemap_urlimages', 'gami_yoast_sitemap_images', 10, 2 );

/**
 * Let Yoast analyse the spec fields along with the content.
 */
function gami_yoast_admin_scripts( $hook ) {
	if ( ! defined( 'WPSEO_VERSION' ) || ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		return;
	}
	if ( ! in_array( get_post_type(), array( 'instrument', 'product' ), true ) ) {
		return;
	}
	wp_enqueue_script( 'gami-yoast-specs', GAMI_URI . '/assets/js/yoast-specs.js', array(), GAMI_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'gami_yoast_admin_scripts' );
