<?php
/**
 * Instrument catalogue archive (also used for instrument categories).
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'archive' ) ) :
	$gami_current = is_tax( 'instrument_category' ) ? get_queried_object() : null;
	?>
	<main id="primary" class="site-main container catalogue">
		<?php gami_breadcrumbs(); ?>

		<header class="page-header catalogue__header">
			<h1 class="page-title">
				<?php echo $gami_current ? esc_html( $gami_current->name ) : esc_html__( 'Instrument catalogue', 'gami-surgical' ); ?>
			</h1>
			<?php if ( $gami_current && $gami_current->description ) : ?>
				<div class="archive-description"><?php echo wp_kses_post( wpautop( $gami_current->description ) ); ?></div>
			<?php endif; ?>
			<?php get_search_form(); ?>
		</header>

		<div class="catalogue__layout">
			<aside class="catalogue__filters" aria-label="<?php esc_attr_e( 'Categories', 'gami-surgical' ); ?>">
				<h2 class="widget-title"><?php esc_html_e( 'Categories', 'gami-surgical' ); ?></h2>
				<ul class="filter-list">
					<li class="<?php echo $gami_current ? '' : 'is-current'; ?>">
						<a href="<?php echo esc_url( get_post_type_archive_link( 'instrument' ) ); ?>" <?php echo $gami_current ? '' : 'aria-current="page"'; ?>><?php esc_html_e( 'All instruments', 'gami-surgical' ); ?></a>
					</li>
					<?php
					wp_list_categories(
						array(
							'taxonomy'         => 'instrument_category',
							'title_li'         => '',
							'show_count'       => true,
							'current_category' => $gami_current ? $gami_current->term_id : 0,
							'show_option_none' => '',
						)
					);
					?>
				</ul>
				<?php if ( is_active_sidebar( 'sidebar-catalogue' ) ) : ?>
					<?php dynamic_sidebar( 'sidebar-catalogue' ); ?>
				<?php endif; ?>
			</aside>

			<div class="catalogue__results">
				<?php if ( have_posts() ) : ?>
					<p class="results-count">
						<?php
						global $wp_query;
						/* translators: %s: number of instruments */
						echo esc_html( sprintf( _n( '%s instrument', '%s instruments', $wp_query->found_posts, 'gami-surgical' ), number_format_i18n( $wp_query->found_posts ) ) );
						?>
					</p>
					<ul class="instrument-grid" style="--cols:3">
						<?php
						while ( have_posts() ) :
							the_post();
							get_template_part( 'template-parts/content', 'instrument-card' );
						endwhile;
						?>
					</ul>
					<?php gami_pagination(); ?>
				<?php else : ?>
					<?php get_template_part( 'template-parts/content', 'none' ); ?>
				<?php endif; ?>
			</div>
		</div>
	</main>
	<?php
endif;

get_footer();
