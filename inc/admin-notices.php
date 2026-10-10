<?php
/**
 * Recommend Elementor and Yoast SEO after activation.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gami_recommended_plugins_notice() {
	if ( ! current_user_can( 'install_plugins' ) || get_user_meta( get_current_user_id(), 'gami_dismiss_plugins', true ) ) {
		return;
	}

	$missing = array();
	if ( ! did_action( 'elementor/loaded' ) ) {
		$missing['elementor'] = 'Elementor';
	}
	if ( ! class_exists( 'WooCommerce' ) ) {
		$missing['woocommerce'] = 'WooCommerce';
	}
	if ( ! defined( 'WPSEO_VERSION' ) ) {
		$missing['wordpress-seo'] = 'Yoast SEO';
	}
	if ( ! $missing ) {
		return;
	}

	$links = array();
	foreach ( $missing as $slug => $name ) {
		$url     = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $slug ), 'install-plugin_' . $slug );
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( sprintf( /* translators: %s plugin */ __( 'Install %s', 'gami-surgical' ), $name ) ) );
	}
	$dismiss = wp_nonce_url( add_query_arg( 'gami_dismiss_plugins', '1' ), 'gami_dismiss' );

	printf(
		'<div class="notice notice-info"><p><strong>%1$s</strong> %2$s</p><p>%3$s | <a href="%4$s">%5$s</a></p></div>',
		esc_html__( 'Gami Surgical works best with these plugins.', 'gami-surgical' ),
		esc_html__( 'Elementor powers the page builder widgets, WooCommerce runs the product catalogue, quotes and orders, and Yoast SEO adds breadcrumbs, sitemaps and schema.', 'gami-surgical' ),
		implode( ' | ', $links ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_url( $dismiss ),
		esc_html__( 'Dismiss', 'gami-surgical' )
	);
}
add_action( 'admin_notices', 'gami_recommended_plugins_notice' );

function gami_dismiss_plugins_notice() {
	if ( isset( $_GET['gami_dismiss_plugins'] ) && check_admin_referer( 'gami_dismiss' ) ) {
		update_user_meta( get_current_user_id(), 'gami_dismiss_plugins', 1 );
	}
}
add_action( 'admin_init', 'gami_dismiss_plugins_notice' );
