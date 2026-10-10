<?php
/**
 * WooCommerce wrapper for the shop, category, search and single product pages.
 *
 * @package Gami_Surgical
 */

get_header();

$gami_is_archive = is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) );

// Elementor Pro Theme Builder templates take over when assigned.
if ( ( is_product() && gami_elementor_location( 'single' ) ) || ( $gami_is_archive && gami_elementor_location( 'archive' ) ) ) {
	get_footer();
	return;
}
?>

<main id="primary" class="site-main site-main--shop">
	<div class="container">
		<?php gami_breadcrumbs(); ?>

		<?php if ( $gami_is_archive ) : ?>
			<?php
			$gami_current = is_product_category() ? get_queried_object_id() : 0;
			$gami_chips   = gami_catalog_terms();
			?>
			<div class="shop-mobilebar">
				<?php if ( ! woocommerce_products_will_display() ) : // Otherwise the Filter button sits in the toolbar. ?>
					<button class="button button--ghost shop-mobilebar__filter" type="button" data-open-filters aria-controls="shop-filters" aria-expanded="false">
						<?php echo gami_icon( 'filter', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span><?php esc_html_e( 'Filter', 'gami-surgical' ); ?></span>
					</button>
				<?php endif; ?>
				<?php if ( $gami_chips ) : ?>
					<nav class="chip-scroller" aria-label="<?php esc_attr_e( 'Product categories', 'gami-surgical' ); ?>">
						<a class="chip<?php echo is_shop() && ! is_search() ? ' is-current' : ''; ?>" href="<?php echo esc_url( gami_catalog_url() ); ?>"><?php esc_html_e( 'All', 'gami-surgical' ); ?></a>
						<?php
						foreach ( $gami_chips as $gami_chip ) :
							$gami_in = $gami_current && ( $gami_current === $gami_chip->term_id || term_is_ancestor_of( $gami_chip, $gami_current, 'product_cat' ) );
							?>
							<a class="chip<?php echo $gami_in ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_term_link( $gami_chip ) ); ?>"<?php echo $gami_in ? ' aria-current="true"' : ''; ?>><?php echo esc_html( $gami_chip->name ); ?></a>
						<?php endforeach; ?>
					</nav>
				<?php endif; ?>
			</div>

			<div class="catalogue__layout">
				<div class="filters-overlay" data-close-filters></div>
				<aside class="catalogue__filters" id="shop-filters" aria-label="<?php esc_attr_e( 'Product filters', 'gami-surgical' ); ?>">
					<?php gami_shop_sidebar(); ?>
				</aside>

				<div class="catalogue__results">
					<?php woocommerce_content(); ?>
				</div>
			</div>
		<?php else : ?>
			<?php woocommerce_content(); ?>
		<?php endif; ?>
	</div>
</main>

<?php
get_footer();
