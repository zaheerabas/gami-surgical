<?php
/**
 * Template Name: Blog
 * Template Post Type: page
 *
 * Lists the latest posts. The theme assigns it to the Blog page, so the blog
 * works whatever is set in Settings → Reading.
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'archive' ) ) :
	while ( have_posts() ) :
		the_post();
		gami_blog_hero(
			get_the_title(),
			has_excerpt() ? get_the_excerpt() : __( 'Guides on choosing, using and caring for surgical, dental and beauty instruments, from the makers in Sialkot.', 'gami-surgical' )
		);
	endwhile;

	$gami_posts = new WP_Query(
		array(
			'post_type'   => 'post',
			'post_status' => 'publish',
			'paged'       => max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) ),
		)
	);
	?>
	<main id="primary" class="site-main site-main--blog container">
		<?php gami_blog_category_nav(); ?>
		<?php gami_blog_listing( $gami_posts ); ?>
	</main>
	<?php
endif;

get_footer();
