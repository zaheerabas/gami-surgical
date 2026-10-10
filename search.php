<?php
/**
 * Search results.
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'archive' ) ) :
	?>
	<main id="primary" class="site-main container">
		<?php gami_breadcrumbs(); ?>
		<header class="page-header">
			<h1 class="page-title">
				<?php
				/* translators: %s: search query */
				printf( esc_html__( 'Results for “%s”', 'gami-surgical' ), esc_html( get_search_query() ) );
				?>
			</h1>
			<?php get_search_form(); ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<ul class="search-result-list">
				<?php
				while ( have_posts() ) :
					the_post();
					?>
					<li class="search-result">
						<p class="search-result__type"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></p>
						<h2 class="search-result__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php
						$cat_no = get_post_meta( get_the_ID(), '_gami_catalogue_no', true );
						if ( $cat_no ) :
							?>
							<p class="search-result__meta"><?php echo esc_html( $cat_no ); ?></p>
						<?php endif; ?>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 28 ) ); ?></p>
					</li>
				<?php endwhile; ?>
			</ul>
			<?php gami_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content', 'none' ); ?>
		<?php endif; ?>
	</main>
	<?php
endif;

get_footer();
