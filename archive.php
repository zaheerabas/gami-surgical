<?php
/**
 * Archive template (categories, tags, authors, dates).
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'archive' ) ) :
	gami_blog_hero( wp_strip_all_tags( get_the_archive_title() ), get_the_archive_description() );
	?>
	<main id="primary" class="site-main site-main--blog container">
		<?php
		gami_blog_category_nav();
		gami_blog_listing();
		?>
	</main>
	<?php
endif;

get_footer();
