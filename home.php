<?php
/**
 * Blog posts page (Settings → Reading → Posts page).
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'archive' ) ) :
	$gami_blog_page = (int) get_option( 'page_for_posts' );
	gami_blog_hero(
		$gami_blog_page ? get_the_title( $gami_blog_page ) : __( 'Blog', 'gami-surgical' ),
		$gami_blog_page && has_excerpt( $gami_blog_page ) ? get_the_excerpt( $gami_blog_page ) : __( 'Guides on choosing, using and caring for surgical, dental and beauty instruments, from the makers in Sialkot.', 'gami-surgical' )
	);
	?>
	<main id="primary" class="site-main site-main--blog container">
		<?php gami_blog_category_nav(); ?>
		<?php gami_blog_listing(); ?>
	</main>
	<?php
endif;

get_footer();
