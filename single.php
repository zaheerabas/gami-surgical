<?php
/**
 * Single post.
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'single' ) ) :
	?>
	<main id="primary" class="site-main container layout-with-sidebar">
		<div class="layout-main">
			<?php gami_breadcrumbs(); ?>
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
					<header class="entry-header">
						<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
						<p class="entry-meta">
							<?php gami_posted_on(); ?>
							<?php
							$cats = get_the_category_list( ', ' );
							if ( $cats ) {
								echo '<span class="entry-cats">' . wp_kses_post( $cats ) . '</span>';
							}
							?>
						</p>
					</header>
					<?php if ( has_post_thumbnail() ) : ?>
						<figure class="entry-thumb"><?php the_post_thumbnail( 'gami-wide' ); ?></figure>
					<?php endif; ?>
					<div class="entry-content">
						<?php
						the_content();
						wp_link_pages(
							array(
								'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'gami-surgical' ),
								'after'  => '</nav>',
							)
						);
						?>
					</div>
					<footer class="entry-footer">
						<?php the_tags( '<p class="entry-tags">', ', ', '</p>' ); ?>
					</footer>
				</article>

				<?php
				the_post_navigation(
					array(
						'prev_text' => '<span class="nav-label">' . esc_html__( 'Previous', 'gami-surgical' ) . '</span> %title',
						'next_text' => '<span class="nav-label">' . esc_html__( 'Next', 'gami-surgical' ) . '</span> %title',
					)
				);
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
			endwhile;
			?>
		</div>
		<?php get_sidebar(); ?>
	</main>
	<?php
endif;

get_footer();
