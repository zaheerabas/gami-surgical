<?php
/**
 * Template Name: Full width (no title)
 * Template Post Type: page, post, instrument
 *
 * Edge-to-edge canvas that keeps the site header and footer. Ideal for Elementor layouts.
 *
 * @package Gami_Surgical
 */

get_header();
?>
<main id="primary" class="site-main site-main--elementor">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
