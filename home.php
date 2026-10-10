<?php
/**
 * Blog posts page (Settings → Reading → Posts page).
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'archive' ) ) {
	global $wp_query;
	$gami_blog_page = (int) get_option( 'page_for_posts' );
	gami_render_blog_home(
		$gami_blog_page ? get_the_title( $gami_blog_page ) : __( 'Blog', 'gami-surgical' ),
		gami_blog_intro( $gami_blog_page ),
		$wp_query
	);
}

get_footer();
