<?php
/**
 * Single post (article).
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'single' ) ) :
	while ( have_posts() ) :
		the_post();
		$gami_cat       = gami_primary_category();
		$gami_elementor = gami_is_elementor_page();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'article' ); ?>>
			<header class="article-header">
				<div class="container article-header__inner">
					<?php gami_breadcrumbs(); ?>
					<?php if ( $gami_cat ) : ?>
						<a class="post-card__cat" href="<?php echo esc_url( get_category_link( $gami_cat ) ); ?>"><?php echo esc_html( $gami_cat->name ); ?></a>
					<?php endif; ?>
					<?php the_title( '<h1 class="article-header__title">', '</h1>' ); ?>
					<?php if ( has_excerpt() ) : ?>
						<p class="article-header__lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
					<?php endif; ?>
					<p class="article-header__meta">
						<span><?php echo esc_html( get_the_author() ); ?></span>
						<?php gami_posted_on(); ?>
						<?php if ( get_the_modified_date( 'Ymd' ) > get_the_date( 'Ymd' ) ) : ?>
							<span>
								<?php
								/* translators: %s: date */
								printf( esc_html__( 'Updated %s', 'gami-surgical' ), esc_html( get_the_modified_date() ) );
								?>
							</span>
						<?php endif; ?>
						<span><?php echo esc_html( gami_reading_time_label() ); ?></span>
					</p>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="article-thumb container"><?php the_post_thumbnail( 'gami-wide', array( 'loading' => 'eager' ) ); ?></figure>
			<?php endif; ?>

			<div class="site-main container <?php echo $gami_elementor ? 'article-body--elementor' : 'layout-with-sidebar article-layout'; ?>">
				<div class="layout-main">
					<div class="entry-content article-content">
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

					<footer class="article-footer">
						<?php the_tags( '<p class="article-tags">', '', '</p>' ); ?>
						<?php gami_share_links(); ?>
						<?php gami_article_cta(); ?>
					</footer>

					<?php
					the_post_navigation(
						array(
							'prev_text' => '<span class="nav-label">' . esc_html__( 'Previous article', 'gami-surgical' ) . '</span> %title',
							'next_text' => '<span class="nav-label">' . esc_html__( 'Next article', 'gami-surgical' ) . '</span> %title',
						)
					);

					gami_related_posts();

					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>
				<?php
				if ( ! $gami_elementor ) {
					get_sidebar();
				}
				?>
			</div>
		</article>
		<?php
	endwhile;
endif;

get_footer();
