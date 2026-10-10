<?php
/**
 * Main template (blog index and fallback).
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'archive' ) ) :
	?>
	<main id="primary" class="site-main container layout-with-sidebar">
		<div class="layout-main">
			<?php gami_breadcrumbs(); ?>
			<?php if ( is_home() && ! is_front_page() ) : ?>
				<header class="page-header">
					<h1 class="page-title"><?php single_post_title(); ?></h1>
				</header>
			<?php endif; ?>

			<?php if ( have_posts() ) : ?>
				<div class="post-list">
					<?php
					while ( have_posts() ) :
						the_post();
						get_template_part( 'template-parts/content', get_post_type() );
					endwhile;
					?>
				</div>
				<?php gami_pagination(); ?>
			<?php else : ?>
				<?php get_template_part( 'template-parts/content', 'none' ); ?>
			<?php endif; ?>
		</div>
		<?php get_sidebar(); ?>
	</main>
	<?php
endif;

get_footer();
