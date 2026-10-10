<?php
/**
 * Page template. Elementor pages get the full width; others a readable column.
 *
 * @package Gami_Surgical
 */

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
			<main id="primary" class="site-main container">
				<?php gami_breadcrumbs(); ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry entry--page' ); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					</header>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="entry-thumb"><?php the_post_thumbnail( 'gami-wide' ); ?></figure>
					<?php endif; ?>
					<div class="entry-content">
						<?php
						the_content();
						wp_link_pages();
						?>
					</div>
				</article>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</main>
			<?php
		endif;
	endwhile;
endif;

get_footer();
