<?php
/**
 * Template Name: Contained (no title)
 * Template Post Type: page
 *
 * Content in the site container without the page title. Good for Elementor sections that should align with the header.
 *
 * @package Gami_Surgical
 */

get_header();
?>
<main id="primary" class="site-main container">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
