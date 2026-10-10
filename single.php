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
		$gami_content   = str_replace( ']]>', ']]&gt;', apply_filters( 'the_content', get_the_content() ) );
		$gami_article   = $gami_elementor ? array(
			'html' => $gami_content,
			'toc'  => array(),
		) : gami_prepare_article( $gami_content );
		?>
		<?php if ( gami_option( 'article_progress' ) ) : ?>
			<div class="read-progress" aria-hidden="true"><span></span></div>
		<?php endif; ?>

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
					<div class="article-header__meta">
						<?php echo get_avatar( get_the_author_meta( 'ID' ), 40, '', '', array( 'class' => 'article-header__avatar' ) ); ?>
						<div>
							<span class="article-header__author"><?php echo esc_html( get_the_author() ); ?></span>
							<span class="article-header__facts">
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
							</span>
						</div>
					</div>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="article-thumb container">
					<?php the_post_thumbnail( 'gami-wide', array( 'loading' => 'eager' ) ); ?>
					<?php if ( get_the_post_thumbnail_caption() ) : ?>
						<figcaption><?php the_post_thumbnail_caption(); ?></figcaption>
					<?php endif; ?>
				</figure>
			<?php endif; ?>

			<div class="site-main container <?php echo $gami_elementor ? 'article-body--elementor' : 'article-layout'; ?>">
				<div class="layout-main article-main">
					<?php gami_article_toc( $gami_article['toc'], 'inline' ); ?>

					<div class="entry-content article-content">
						<?php
						echo $gami_article['html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered post content.
						wp_link_pages(
							array(
								'before' => '<nav class="page-links">' . esc_html__( 'Pages:', 'gami-surgical' ),
								'after'  => '</nav>',
							)
						);
						?>
					</div>

					<footer class="article-footer">
						<?php the_tags( '<p class="article-tags"><span class="screen-reader-text">' . esc_html__( 'Tags:', 'gami-surgical' ) . '</span>', '', '</p>' ); ?>
						<?php gami_share_links(); ?>
						<?php gami_author_box(); ?>
						<?php gami_article_cta(); ?>
					</footer>

					<?php
					the_post_navigation(
						array(
							'prev_text' => '<span class="nav-label">' . esc_html__( 'Previous article', 'gami-surgical' ) . '</span> %title',
							'next_text' => '<span class="nav-label">' . esc_html__( 'Next article', 'gami-surgical' ) . '</span> %title',
						)
					);

					if ( comments_open() || get_comments_number() ) {
						comments_template();
					}
					?>
				</div>
				<?php
				if ( ! $gami_elementor ) {
					gami_article_sidebar( $gami_article['toc'] );
				}
				?>
			</div>

			<?php gami_related_posts(); ?>
		</article>
		<?php
	endwhile;
endif;

get_footer();
