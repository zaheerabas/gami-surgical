<?php
/**
 * Template Name: Blog
 * Template Post Type: page
 *
 * The Blog page: latest articles, topics, category rows and the full list.
 * The theme assigns it to the Blog page, so the blog works whatever is set
 * in Settings → Reading.
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'archive' ) ) {
	$gami_page_id = get_queried_object_id();
	gami_render_blog_home(
		get_the_title( $gami_page_id ),
		gami_blog_intro( $gami_page_id ),
		new WP_Query(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
				'paged'       => gami_blog_paged(),
			)
		)
	);
}

get_footer();
