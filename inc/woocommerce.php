<?php
/**
 * WooCommerce integration: an inquiry catalogue, not a shop.
 *
 * Gami Surgical manufactures surgical, dental and beauty instruments and sells
 * by inquiry. So the theme turns off everything shop-like:
 * - no prices (unless "Show prices" is on), no add to cart, cart or checkout;
 * - no customer accounts or registration;
 * - cart, checkout and My account pages redirect to the catalogue and are left
 *   out of menus and the Yoast sitemap.
 * "Send inquiry" and "Add to inquiry list" replace the cart (inc/inquiry.php).
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/* -------------------------------------------------------------------------
 * Helpers (kept for compatibility with other theme files)
 * ---------------------------------------------------------------------- */

function gami_show_prices() {
	return (bool) gami_option( 'show_prices' );
}

function gami_quotes_enabled() {
	return true;
}

function gami_quote_only() {
	return true;
}

/**
 * True on the front end (including AJAX), false in wp-admin screens.
 */
function gami_is_storefront_request() {
	return ! is_admin() || wp_doing_ajax();
}

function gami_wc_needs_quote( $product ) {
	return (bool) $product;
}

/* -------------------------------------------------------------------------
 * Setup
 * ---------------------------------------------------------------------- */

function gami_wc_setup() {
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 480,
			'single_image_width'    => 800,
			'product_grid'          => array(
				'default_columns' => 3,
				'default_rows'    => 8,
				'min_columns'     => 2,
				'max_columns'     => 5,
			),
		)
	);
	// Hover zoom on the product photo is off unless switched on in the Customizer.
	if ( gami_option( 'gallery_zoom' ) ) {
		add_theme_support( 'wc-product-gallery-zoom' );
	}
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'gami_wc_setup' );

function gami_wc_widgets() {
	register_sidebar(
		array(
			'name'          => __( 'Shop sidebar', 'gami-surgical' ),
			'id'            => 'sidebar-shop',
			'description'   => __( 'Shown below the category list on the shop and category pages. Good for price, attribute and rating filters.', 'gami-surgical' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'gami_wc_widgets' );

function gami_wc_scripts() {
	wp_enqueue_style( 'gami-woocommerce', GAMI_URI . '/assets/css/woocommerce.css', array( 'gami-main' ), GAMI_VERSION );

	// No cart: drop WooCommerce's cart scripts.
	foreach ( array( 'wc-cart-fragments', 'wc-add-to-cart', 'wc-add-to-cart-variation', 'wc-cart', 'wc-checkout' ) as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'gami_wc_scripts', 20 );

// The theme prints its own breadcrumbs (Yoast when enabled).
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// Columns and products per page: inc/shop.php (toolbar + Customizer defaults).

add_filter(
	'woocommerce_output_related_products_args',
	function ( $args ) {
		$args['posts_per_page'] = 4;
		$args['columns']        = 4;
		return $args;
	}
);

add_filter(
	'woocommerce_upsell_display_args',
	function ( $args ) {
		$args['posts_per_page'] = 4;
		$args['columns']        = 4;
		return $args;
	}
);

/**
 * Show the SKU (catalogue number) above product names in grids.
 */
function gami_wc_loop_sku() {
	global $product;
	if ( $product && $product->get_sku() ) {
		printf( '<p class="product-catno">%s</p>', esc_html( $product->get_sku() ) );
	}
}
add_action( 'woocommerce_shop_loop_item_title', 'gami_wc_loop_sku', 5 );

/**
 * Sizes line under the product name in grids.
 */
function gami_wc_loop_sizes() {
	$sizes = implode( ', ', gami_product_sizes( get_the_ID() ) );
	if ( $sizes ) {
		printf( '<p class="product-sizes">%s</p>', esc_html( $sizes ) );
	}
}
add_action( 'woocommerce_after_shop_loop_item_title', 'gami_wc_loop_sizes', 4 );

/* -------------------------------------------------------------------------
 * No shop: prices, purchasing, cart, checkout, accounts
 * ---------------------------------------------------------------------- */

function gami_wc_hide_price( $html ) {
	return ( ! gami_show_prices() && gami_is_storefront_request() ) ? '' : $html;
}
add_filter( 'woocommerce_get_price_html', 'gami_wc_hide_price', 100 );

function gami_wc_not_purchasable( $purchasable ) {
	return gami_is_storefront_request() ? false : $purchasable;
}
add_filter( 'woocommerce_is_purchasable', 'gami_wc_not_purchasable', 100 );
add_filter( 'woocommerce_variation_is_purchasable', 'gami_wc_not_purchasable', 100 );

// Customer accounts and registration are off.
foreach ( array(
	'woocommerce_enable_myaccount_registration',
	'woocommerce_enable_signup_and_login_from_checkout',
	'woocommerce_enable_checkout_login_reminder',
	'woocommerce_enable_guest_checkout',
) as $gami_wc_option ) {
	add_filter(
		'pre_option_' . $gami_wc_option,
		function () {
			return 'no';
		}
	);
}
add_filter( 'pre_option_users_can_register', '__return_zero' );

/**
 * IDs of the shop-only pages (cart, checkout, my account).
 */
function gami_wc_shop_page_ids() {
	$ids = array();
	foreach ( array( 'cart', 'checkout', 'myaccount' ) as $page ) {
		$id = (int) wc_get_page_id( $page );
		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}
	return $ids;
}

/**
 * Cart, checkout and My account send visitors to the catalogue.
 * Logged-in staff can still open My account (for example to log out).
 */
function gami_wc_redirect_shop_pages() {
	if ( is_cart() || is_checkout() || ( is_account_page() && ! current_user_can( 'edit_posts' ) ) ) {
		wp_safe_redirect( gami_catalog_url(), 302 );
		exit;
	}
}
add_action( 'template_redirect', 'gami_wc_redirect_shop_pages' );

/**
 * Keep those pages out of menus, page lists and the Yoast sitemap.
 */
function gami_wc_hide_shop_pages_from_menus( $items ) {
	$ids = gami_wc_shop_page_ids();
	foreach ( $items as $key => $item ) {
		if ( 'page' === $item->object && in_array( (int) $item->object_id, $ids, true ) ) {
			unset( $items[ $key ] );
		}
	}
	return $items;
}
add_filter( 'wp_nav_menu_objects', 'gami_wc_hide_shop_pages_from_menus', 5 );

add_filter(
	'wp_list_pages_excludes',
	function ( $exclude ) {
		return array_merge( (array) $exclude, gami_wc_shop_page_ids() );
	}
);

add_filter(
	'wpseo_exclude_from_sitemap_by_post_ids',
	function ( $ids ) {
		return array_merge( (array) $ids, gami_wc_shop_page_ids() );
	}
);

/* -------------------------------------------------------------------------
 * Catalogue clean-up: nothing that only makes sense in a shop
 * ---------------------------------------------------------------------- */

/**
 * Sorting: no price sorting without prices, no rating sorting without reviews.
 */
function gami_wc_sorting( $options ) {
	if ( ! gami_show_prices() ) {
		unset( $options['price'], $options['price-desc'] );
	}
	if ( ! gami_option( 'show_reviews' ) ) {
		unset( $options['rating'] );
	}
	return $options;
}
add_filter( 'woocommerce_catalog_orderby', 'gami_wc_sorting' );
add_filter( 'woocommerce_default_catalog_orderby_options', 'gami_wc_sorting' );

/**
 * A price sort left in the address or settings falls back to the default order.
 */
function gami_wc_sorting_fallback( $args ) {
	if ( ! gami_show_prices() && isset( $args['orderby'] ) && false !== strpos( (string) $args['orderby'], 'price' ) ) {
		$args['orderby'] = 'menu_order title';
		$args['order']   = 'ASC';
	}
	return $args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'gami_wc_sorting_fallback' );

/**
 * Sorting options with clear names, plus name (A–Z, Z–A) and catalogue number.
 */
function gami_wc_sorting_labels( $options ) {
	$labels = array(
		'menu_order' => __( 'Recommended', 'gami-surgical' ),
		'popularity' => __( 'Most popular', 'gami-surgical' ),
		'date'       => __( 'Newest first', 'gami-surgical' ),
		'title'      => __( 'Name: A to Z', 'gami-surgical' ),
		'title-desc' => __( 'Name: Z to A', 'gami-surgical' ),
		'sku'        => __( 'Catalogue no.', 'gami-surgical' ),
		'price'      => __( 'Price: low to high', 'gami-surgical' ),
		'price-desc' => __( 'Price: high to low', 'gami-surgical' ),
		'rating'     => __( 'Best rated', 'gami-surgical' ),
	);
	$out = array();
	foreach ( $labels as $key => $label ) {
		if ( in_array( $key, array( 'title', 'title-desc', 'sku' ), true ) || array_key_exists( $key, $options ) ) {
			$out[ $key ] = $label;
		}
	}
	return $out;
}
add_filter( 'woocommerce_catalog_orderby', 'gami_wc_sorting_labels', 20 );
add_filter( 'woocommerce_default_catalog_orderby_options', 'gami_wc_sorting_labels', 20 );

/**
 * Catalogue number sorting (WooCommerce has no SKU sort of its own).
 */
function gami_wc_sku_ordering( $args, $orderby = '', $order = '' ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$value = isset( $_GET['orderby'] ) ? wc_clean( wp_unslash( $_GET['orderby'] ) ) : $orderby;
	if ( 'sku' === $value || 'sku' === $orderby ) {
		$args['orderby']  = 'meta_value';
		$args['meta_key'] = '_sku'; // phpcs:ignore WordPress.DB.SlowDBQuery
		$args['order']    = 'ASC';
	}
	return $args;
}
add_filter( 'woocommerce_get_catalog_ordering_args', 'gami_wc_sku_ordering', 20, 3 );

/**
 * Pagination: Previous / Next with arrows, fewer numbers, "Page 2 of 39".
 */
function gami_wc_pagination_args( $args ) {
	$args['prev_text'] = '<span aria-hidden="true">←</span><span class="page-numbers__label">' . esc_html__( 'Previous', 'gami-surgical' ) . '</span>';
	$args['next_text'] = '<span class="page-numbers__label">' . esc_html__( 'Next', 'gami-surgical' ) . '</span><span aria-hidden="true">→</span>';
	$args['end_size']  = 1;
	$args['mid_size']  = 1;
	return $args;
}
add_filter( 'woocommerce_pagination_args', 'gami_wc_pagination_args' );

function gami_wc_page_of() {
	$total   = (int) wc_get_loop_prop( 'total_pages' );
	$current = max( 1, (int) wc_get_loop_prop( 'current_page' ) );
	if ( $total > 1 ) {
		/* translators: 1: current page, 2: total pages */
		printf( '<p class="shop-pageof">%s</p>', esc_html( sprintf( __( 'Page %1$d of %2$d', 'gami-surgical' ), $current, $total ) ) );
	}
}
add_action( 'woocommerce_after_shop_loop', 'gami_wc_page_of', 11 );

// "Sale!" badges mean nothing without prices.
add_filter(
	'woocommerce_sale_flash',
	function ( $html ) {
		return gami_show_prices() ? $html : '';
	}
);

/**
 * Reviews: off unless turned on (Customize > Gami Surgical > Inquiries).
 */
function gami_wc_reviews_off() {
	if ( gami_option( 'show_reviews' ) ) {
		return;
	}
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
}
add_action( 'wp', 'gami_wc_reviews_off' );
add_filter(
	'woocommerce_product_tabs',
	function ( $tabs ) {
		if ( ! gami_option( 'show_reviews' ) ) {
			unset( $tabs['reviews'] );
		}
		return $tabs;
	},
	98
);

/**
 * Stock: "In stock" reads oddly for made-to-order instruments; keep only
 * "Out of stock" and backorder notices.
 */
add_filter(
	'woocommerce_get_stock_html',
	function ( $html, $product ) {
		return ( $product && 'instock' === $product->get_stock_status() ) ? '' : $html;
	},
	10,
	2
);

/**
 * Call the SKU what the rest of the site calls it.
 */
function gami_wc_sku_label( $translation, $text, $domain ) {
	if ( 'woocommerce' === $domain && 'SKU:' === $text ) {
		return __( 'Catalogue no.:', 'gami-surgical' );
	}
	return $translation;
}
add_filter( 'gettext', 'gami_wc_sku_label', 10, 3 );

/**
 * Breadcrumbs: Home / Products / Category / Product, so buyers can always get
 * back to the full catalogue (WooCommerce leaves the catalogue level out).
 */
function gami_wc_breadcrumb_catalogue( $crumbs ) {
	$shop_id = wc_get_page_id( 'shop' );
	if ( $shop_id <= 0 || ! ( is_product() || is_product_taxonomy() ) || count( $crumbs ) < 2 ) {
		return $crumbs;
	}
	$shop_url = get_permalink( $shop_id );
	foreach ( $crumbs as $crumb ) {
		if ( isset( $crumb[1] ) && untrailingslashit( $crumb[1] ) === untrailingslashit( $shop_url ) ) {
			return $crumbs;
		}
	}
	array_splice( $crumbs, 1, 0, array( array( get_the_title( $shop_id ), $shop_url ) ) );
	return $crumbs;
}
add_filter( 'woocommerce_get_breadcrumb', 'gami_wc_breadcrumb_catalogue' );

/**
 * Empty search or category: a helpful block instead of WooCommerce's notice.
 */
remove_action( 'woocommerce_no_products_found', 'wc_no_products_found' );
add_action( 'woocommerce_no_products_found', 'gami_no_results_help' );

/**
 * On activation, call WooCommerce's "Shop" page "Products" (it can be renamed).
 */
function gami_wc_rename_shop_page() {
	$id = wc_get_page_id( 'shop' );
	if ( $id > 0 && 'Shop' === get_the_title( $id ) ) {
		wp_update_post(
			array(
				'ID'         => $id,
				'post_title' => __( 'Products', 'gami-surgical' ),
			)
		);
	}
}
add_action( 'after_switch_theme', 'gami_wc_rename_shop_page' );

/**
 * Grid button: "Send inquiry" + "Add to inquiry list" instead of add to cart.
 */
function gami_wc_loop_button( $html, $product ) {
	return gami_inquiry_buttons( $product->get_id(), 'loop' );
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'gami_wc_loop_button', 20, 2 );

// Product page layout: inc/product.php.

/* -------------------------------------------------------------------------
 * Specifications tab (front end)
 * ---------------------------------------------------------------------- */

function gami_wc_product_tabs( $tabs ) {
	$specs = gami_instrument_specs( get_the_ID() );
	if ( $specs ) {
		$tabs['gami_specs'] = array(
			'title'    => __( 'Specifications', 'gami-surgical' ),
			'priority' => 5,
			'callback' => function () {
				gami_spec_table( get_the_ID() );
			},
		);
	}
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'gami_wc_product_tabs' );

/* -------------------------------------------------------------------------
 * Specifications panel (product editor)
 * ---------------------------------------------------------------------- */

function gami_wc_admin_tab( $tabs ) {
	$tabs['gami_specs'] = array(
		'label'    => __( 'Specifications', 'gami-surgical' ),
		'target'   => 'gami_product_specs',
		'class'    => array(),
		'priority' => 65,
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'gami_wc_admin_tab' );

function gami_wc_admin_panel() {
	global $post;
	$hints = gami_spec_hints();
	echo '<div id="gami_product_specs" class="panel woocommerce_options_panel hidden"><div class="options_group">';
	echo '<p class="form-field"><em>' . esc_html__( 'Shown in the Specifications tab and in search results data. The catalogue number is the SKU from the Inventory tab.', 'gami-surgical' ) . '</em></p>';
	foreach ( gami_editable_spec_fields( 'product' ) as $key => $label ) {
		woocommerce_wp_text_input(
			array(
				'id'          => '_gami_' . $key,
				'label'       => $label,
				'placeholder' => isset( $hints[ $key ] ) ? $hints[ $key ] : '',
				'value'       => get_post_meta( $post->ID, '_gami_' . $key, true ),
			)
		);
	}
	echo '</div></div>';
}
add_action( 'woocommerce_product_data_panels', 'gami_wc_admin_panel' );

/**
 * Save spec fields. WooCommerce has already verified its nonce at this point.
 */
function gami_wc_admin_save( $product ) {
	foreach ( array_keys( gami_editable_spec_fields( 'product' ) ) as $key ) {
		$field = '_gami_' . $key;
		if ( ! isset( $_POST[ $field ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			continue;
		}
		$value = sanitize_text_field( wp_unslash( $_POST[ $field ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( '' === $value ) {
			$product->delete_meta_data( $field );
		} else {
			$product->update_meta_data( $field, $value );
		}
	}
}
add_action( 'woocommerce_admin_process_product_object', 'gami_wc_admin_save' );

/* -------------------------------------------------------------------------
 * Structured data: enrich WooCommerce's Product JSON-LD
 * ---------------------------------------------------------------------- */

function gami_wc_structured_data( $markup, $product ) {
	$id = $product->get_id();

	if ( empty( $markup['brand'] ) ) {
		$markup['brand'] = array(
			'@type' => 'Brand',
			'name'  => get_bloginfo( 'name' ),
		);
	}
	if ( $product->get_sku() && empty( $markup['mpn'] ) ) {
		$markup['mpn'] = $product->get_sku();
	}

	$material = get_post_meta( $id, '_gami_material', true );
	if ( $material ) {
		$markup['material'] = $material;
	}

	$fields = gami_instrument_fields( 'product' );
	$props  = array();
	foreach ( array( 'sizes', 'finish', 'usage' ) as $key ) {
		$value = get_post_meta( $id, '_gami_' . $key, true );
		if ( $value && isset( $fields[ $key ] ) ) {
			$props[] = array(
				'@type' => 'PropertyValue',
				'name'  => $fields[ $key ],
				'value' => $value,
			);
		}
	}
	if ( $props ) {
		$markup['additionalProperty'] = $props;
	}

	// No public price: don't advertise an offer.
	if ( ! gami_show_prices() ) {
		unset( $markup['offers'] );
	}

	return apply_filters( 'gami_product_schema', $markup, $product );
}
add_filter( 'woocommerce_structured_data_product', 'gami_wc_structured_data', 10, 2 );

/* -------------------------------------------------------------------------
 * Header: no cart or account icons (the inquiry list replaces them)
 * ---------------------------------------------------------------------- */

function gami_header_cart() {}
function gami_header_account() {}

/* -------------------------------------------------------------------------
 * Product grid renderer (homepage and Elementor widget)
 * ---------------------------------------------------------------------- */

function gami_render_product_grid( $args ) {
	$atts = array(
		'limit'   => absint( $args['count'] ),
		'columns' => absint( $args['columns'] ),
	);

	switch ( $args['orderby'] ) {
		case 'date':
			$atts['orderby'] = 'date';
			$atts['order']   = 'DESC';
			break;
		case 'title':
			$atts['orderby'] = 'title';
			$atts['order']   = 'ASC';
			break;
		case 'rand':
			$atts['orderby'] = 'rand';
			break;
		case 'popularity':
			$atts['orderby'] = 'popularity';
			break;
		default:
			$atts['orderby'] = 'menu_order';
			$atts['order']   = 'ASC';
	}

	if ( ! empty( $args['category'] ) ) {
		$slugs = array();
		foreach ( (array) $args['category'] as $term_id ) {
			$term = get_term( absint( $term_id ), 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				$slugs[] = $term->slug;
			}
		}
		if ( $slugs ) {
			$atts['category'] = implode( ',', $slugs );
		}
	}

	if ( ! empty( $args['featured'] ) ) {
		$atts['visibility'] = 'featured';
	}

	$shortcode = new WC_Shortcode_Products( $atts, 'products' );
	$html      = $shortcode->get_content();

	if ( false === strpos( $html, 'product' ) || false === strpos( $html, '<li' ) ) {
		if ( current_user_can( 'edit_products' ) ) {
			echo '<p class="empty-note">' . esc_html__( 'No products yet. Add them under Products in the dashboard.', 'gami-surgical' ) . '</p>';
		}
		return;
	}
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WooCommerce template output.
}

/* -------------------------------------------------------------------------
 * Small conveniences
 * ---------------------------------------------------------------------- */

/**
 * Let Elementor edit product descriptions on activation.
 */
function gami_wc_elementor_support() {
	$cpts = get_option( 'elementor_cpt_support' );
	if ( is_array( $cpts ) && ! in_array( 'product', $cpts, true ) ) {
		$cpts[] = 'product';
		update_option( 'elementor_cpt_support', $cpts );
	}
}
add_action( 'after_switch_theme', 'gami_wc_elementor_support', 20 );

