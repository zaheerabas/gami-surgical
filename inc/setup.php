<?php
/**
 * Theme setup: supports, menus, image sizes, widget areas.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gami_setup() {
	load_theme_textdomain( 'gami-surgical', GAMI_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'yoast-seo-breadcrumbs' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 260,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	add_image_size( 'gami-card', 640, 640, true );
	add_image_size( 'gami-wide', 1400, 700, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary menu', 'gami-surgical' ),
			'footer'  => __( 'Footer menu', 'gami-surgical' ),
			'legal'   => __( 'Legal links', 'gami-surgical' ),
		)
	);
}
add_action( 'after_setup_theme', 'gami_setup' );

function gami_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'gami_content_width', 760 );
}
add_action( 'after_setup_theme', 'gami_content_width', 0 );

function gami_widgets_init() {
	$shared = array(
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	);

	register_sidebar(
		array_merge(
			$shared,
			array(
				'name'        => __( 'Blog sidebar', 'gami-surgical' ),
				'id'          => 'sidebar-blog',
				'description' => __( 'Shown beside blog posts and archives.', 'gami-surgical' ),
			)
		)
	);

	register_sidebar(
		array_merge(
			$shared,
			array(
				'name'        => __( 'Catalogue sidebar', 'gami-surgical' ),
				'id'          => 'sidebar-catalogue',
				'description' => __( 'Shown below the category list on instrument archives.', 'gami-surgical' ),
			)
		)
	);

	for ( $i = 1; $i <= 4; $i++ ) {
		register_sidebar(
			array_merge(
				$shared,
				array(
					/* translators: %d: footer column number */
					'name' => sprintf( __( 'Footer column %d', 'gami-surgical' ), $i ),
					'id'   => 'footer-' . $i,
				)
			)
		);
	}
}
add_action( 'widgets_init', 'gami_widgets_init' );

/**
 * Body classes for layout hooks.
 */
function gami_body_classes( $classes ) {
	if ( gami_is_elementor_page() ) {
		$classes[] = 'is-elementor-page';
	}
	if ( ! is_active_sidebar( 'sidebar-blog' ) && ! is_singular( 'post' ) ) {
		$classes[] = 'no-sidebar';
	}
	return $classes;
}
add_filter( 'body_class', 'gami_body_classes' );

/**
 * Basic meta description when no SEO plugin is handling it.
 */
function gami_fallback_meta_description() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) ) {
		return;
	}
	$desc = '';
	if ( is_singular() ) {
		$desc = get_the_excerpt( get_queried_object_id() );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$desc = term_description();
	}
	if ( ! $desc ) {
		$desc = get_bloginfo( 'description' );
	}
	$desc = wp_trim_words( wp_strip_all_tags( $desc ), 30, '' );
	if ( $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}
}
add_action( 'wp_head', 'gami_fallback_meta_description', 1 );
