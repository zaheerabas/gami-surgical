<?php
/**
 * Elementor integration: Theme Builder locations, widget category, widgets.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register header, footer, single and archive locations for Elementor Pro Theme Builder.
 */
function gami_elementor_locations( $elementor_theme_manager ) {
	$elementor_theme_manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'gami_elementor_locations' );

/**
 * Render an Elementor Pro location if one is assigned. Returns true when it rendered.
 */
function gami_elementor_location( $location ) {
	return function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $location );
}

/**
 * Widget category.
 */
function gami_elementor_category( $elements_manager ) {
	$elements_manager->add_category(
		'gami-surgical',
		array(
			'title' => __( 'Gami Surgical', 'gami-surgical' ),
			'icon'  => 'eicon-tools',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'gami_elementor_category' );

/**
 * Widgets.
 */
function gami_elementor_widgets( $widgets_manager ) {
	require_once GAMI_DIR . '/inc/elementor-widgets/class-instrument-grid.php';
	require_once GAMI_DIR . '/inc/elementor-widgets/class-quote-form.php';
	require_once GAMI_DIR . '/inc/elementor-widgets/class-spec-drawing.php';

	$widgets_manager->register( new \Gami_Widget_Instrument_Grid() );
	$widgets_manager->register( new \Gami_Widget_Quote_Form() );
	$widgets_manager->register( new \Gami_Widget_Spec_Drawing() );
}
add_action( 'elementor/widgets/register', 'gami_elementor_widgets' );

/**
 * Load theme styles inside the Elementor preview so widgets match the front end.
 */
function gami_elementor_preview_styles() {
	wp_enqueue_style( 'gami-main' );
	if ( wp_style_is( 'gami-woocommerce', 'registered' ) ) {
		wp_enqueue_style( 'gami-woocommerce' );
	}
}
add_action( 'elementor/preview/enqueue_styles', 'gami_elementor_preview_styles' );

/**
 * Enable Elementor for instruments by default on theme activation.
 */
function gami_elementor_cpt_support() {
	$cpts = get_option( 'elementor_cpt_support' );
	if ( ! is_array( $cpts ) ) {
		$cpts = array( 'page', 'post' );
	}
	if ( gami_use_instruments() && ! in_array( 'instrument', $cpts, true ) ) {
		$cpts[] = 'instrument';
	}
	if ( gami_wc_active() && ! in_array( 'product', $cpts, true ) ) {
		$cpts[] = 'product';
	}
	update_option( 'elementor_cpt_support', $cpts );
	// Let the theme's fonts and colours lead unless the user changes this.
	if ( false === get_option( 'elementor_disable_color_schemes' ) ) {
		update_option( 'elementor_disable_color_schemes', 'yes' );
	}
	if ( false === get_option( 'elementor_disable_typography_schemes' ) ) {
		update_option( 'elementor_disable_typography_schemes', 'yes' );
	}
}
add_action( 'after_switch_theme', 'gami_elementor_cpt_support' );

/**
 * Shared renderer for the instrument grid (used by the widget and the homepage).
 */
function gami_render_instrument_grid( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'count'    => 8,
			'category' => array(),
			'columns'  => 4,
			'orderby'  => 'menu_order',
			'featured' => false,
		)
	);

	// With WooCommerce, the catalogue is the product list.
	if ( function_exists( 'gami_render_product_grid' ) ) {
		gami_render_product_grid( $args );
		return;
	}

	$query_args = array(
		'post_type'           => 'instrument',
		'posts_per_page'      => absint( $args['count'] ),
		'ignore_sticky_posts' => true,
		'no_found_rows'       => true,
	);
	if ( 'menu_order' === $args['orderby'] ) {
		$query_args['orderby'] = array(
			'menu_order' => 'ASC',
			'date'       => 'DESC',
		);
	} else {
		$query_args['orderby'] = sanitize_key( $args['orderby'] );
	}
	if ( ! empty( $args['category'] ) ) {
		$query_args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'taxonomy' => 'instrument_category',
				'field'    => 'term_id',
				'terms'    => array_map( 'absint', (array) $args['category'] ),
			),
		);
	}

	$q = new WP_Query( $query_args );
	if ( ! $q->have_posts() ) {
		if ( current_user_can( 'edit_posts' ) ) {
			echo '<p class="empty-note">' . esc_html__( 'No instruments yet. Add them under Instruments in the dashboard.', 'gami-surgical' ) . '</p>';
		}
		return;
	}

	printf( '<ul class="instrument-grid" style="--cols:%d">', absint( $args['columns'] ) );
	while ( $q->have_posts() ) {
		$q->the_post();
		get_template_part( 'template-parts/content', 'instrument-card' );
	}
	echo '</ul>';
	wp_reset_postdata();
}
