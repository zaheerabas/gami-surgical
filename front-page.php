<?php
/**
 * Front page: header, the homepage's own content, footer.
 *
 * The theme adds no sections of its own here. Whatever you put on the page set
 * as the homepage (Settings > Reading) is shown full width, whether built with
 * Elementor or the block editor. An empty page shows only the header and footer.
 *
 * @package Gami_Surgical
 */

// Latest posts on the front page: use the normal blog template.
if ( ! is_page() ) {
	require get_home_template() ? get_home_template() : get_index_template();
	return;
}

get_header();

if ( ! gami_elementor_location( 'single' ) ) :
	while ( have_posts() ) :
		the_post();
		if ( gami_is_elementor_page() ) :
			?>
			<main id="primary" class="site-main site-main--elementor">
				<?php the_content(); ?>
			</main>
			<?php
		else :
			?>
			<main id="primary" class="site-main site-main--front">
				<?php if ( trim( get_the_content() ) ) : ?>
					<div class="container entry-content entry-content--front">
						<?php the_content(); ?>
					</div>
				<?php endif; ?>
			</main>
			<?php
		endif;
	endwhile;
endif;

get_footer();
