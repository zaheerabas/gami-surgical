<?php
/**
 * Scripts and styles.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gami_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wdth,wght@0,75..100,400..700;1,75..100,400..700&display=swap';
}

function gami_scripts() {
	wp_enqueue_style( 'gami-fonts', gami_fonts_url(), array(), null );
	wp_enqueue_style( 'gami-main', GAMI_URI . '/assets/css/main.css', array( 'gami-fonts' ), GAMI_VERSION );
	wp_enqueue_style( 'gami-header', GAMI_URI . '/assets/css/header.css', array( 'gami-main' ), GAMI_VERSION );
	if ( gami_option( 'animations' ) ) {
		wp_enqueue_style( 'gami-motion', GAMI_URI . '/assets/css/motion.css', array( 'gami-main' ), GAMI_VERSION );
		wp_enqueue_script(
			'gami-motion',
			GAMI_URI . '/assets/js/motion.js',
			array(),
			GAMI_VERSION,
			array(
				'strategy'  => 'defer',
				'in_footer' => false,
			)
		);
	}
	wp_add_inline_style( 'gami-main', gami_customizer_css() );
	wp_add_inline_style( 'gami-header', gami_breakpoint_css() );

	wp_enqueue_script(
		'gami-main',
		GAMI_URI . '/assets/js/main.js',
		array(),
		GAMI_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	wp_localize_script( 'gami-main', 'gamiNav', array( 'more' => __( 'More', 'gami-surgical' ) ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'gami_scripts' );

function gami_resource_hints( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href' => 'https://fonts.gstatic.com',
			'crossorigin',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'gami_resource_hints', 10, 2 );

function gami_editor_assets() {
	wp_enqueue_style( 'gami-editor-fonts', gami_fonts_url(), array(), null );
}
add_action( 'enqueue_block_editor_assets', 'gami_editor_assets' );

/**
 * Before the page paints: mark JS as available and switch to the mobile
 * header below the breakpoint set in the Customizer.
 */
function gami_head_js() {
	$bp = max( 600, min( 1400, absint( gami_option( 'mobile_breakpoint' ) ) ) );
	printf(
		"<script>(function(d){d.classList.remove('no-js');d.classList.add('js');d.setAttribute('data-gs-bp','%1\$d');if(window.innerWidth<%1\$d){d.classList.add('gs-nav-collapsed');}})(document.documentElement);</script>\n",
		(int) $bp
	);
}
add_action( 'wp_head', 'gami_head_js', 0 );

/**
 * No-JS fallback for the mobile header, at the same breakpoint.
 */
function gami_breakpoint_css() {
	$bp = max( 600, min( 1400, absint( gami_option( 'mobile_breakpoint' ) ) ) );
	return '@media (max-width:' . ( $bp - 1 ) . 'px){.no-js .site-header__search{grid-column:1/-1}}';
}
