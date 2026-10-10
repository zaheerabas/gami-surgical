<?php
/**
 * Gami Surgical theme bootstrap.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'GAMI_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );
define( 'GAMI_DIR', get_template_directory() );
define( 'GAMI_URI', get_template_directory_uri() );

$gami_includes = array(
	'setup',
	'enqueue',
	'template-tags',
	'customizer',
	'instruments',
	'inquiry',
	'woocommerce',
	'shop',
	'product',
	'search',
	'mega-menu',
	'quote-form',
	'elementor',
	'yoast',
	'admin-notices',
	'blog',
	'article-upload',
	'github-updater',
);

foreach ( $gami_includes as $gami_file ) {
	require_once GAMI_DIR . '/inc/' . $gami_file . '.php';
}
