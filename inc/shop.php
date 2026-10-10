<?php
/**
 * Shop and category pages.
 *
 * - Toolbar: result count, Grid / List, 2 / 3 / 4 per row, products per page,
 *   sorting. View and columns are remembered in a cookie (and applied in the
 *   browser too, so page caching can't show the wrong layout). Products per
 *   page travels in the address (?per_page=24), so it works with caching and
 *   in shared links.
 * - Shop page: a card for each parent category (Surgical, Dental, Beauty).
 * - Category pages: sub-categories as quick links.
 * - Sidebar: categories as expandable sections, inquiry list, help (search is
 *   in the header). Product-category widgets in the Shop sidebar are skipped
 *   because the Categories card already lists them.
 *
 * Defaults: Appearance > Customize > Gami Surgical > Shop page.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/* -------------------------------------------------------------------------
 * View state
 * ---------------------------------------------------------------------- */

/**
 * Allowed "per page" choices from the Customizer, e.g. "8, 12, 24, 48".
 */
function gami_shop_per_page_choices() {
	$raw     = (string) gami_option( 'shop_per_page_choices' );
	$choices = array_values( array_unique( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) ) );
	$choices = array_filter(
		$choices,
		function ( $n ) {
			return $n > 0 && $n <= 96;
		}
	);
	sort( $choices );
	return $choices ? array_values( $choices ) : array( 12, 24, 48 );
}

/**
 * Current view: grid|list, columns 2-4, products per page.
 */
function gami_shop_state() {
	static $state = null;
	if ( null !== $state ) {
		return $state;
	}
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- display options only.
	$view = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : ( isset( $_COOKIE['gs_view'] ) ? sanitize_key( $_COOKIE['gs_view'] ) : '' );
	$cols = isset( $_GET['cols'] ) ? absint( $_GET['cols'] ) : ( isset( $_COOKIE['gs_cols'] ) ? absint( $_COOKIE['gs_cols'] ) : 0 );
	$pp   = isset( $_GET['per_page'] ) ? absint( $_GET['per_page'] ) : 0;
	$mcol = isset( $_COOKIE['gs_mcols'] ) ? absint( $_COOKIE['gs_mcols'] ) : 0;
	// phpcs:enable
	if ( ! in_array( $mcol, array( 1, 2 ), true ) ) {
		$mcol = 2;
	}

	if ( ! in_array( $view, array( 'grid', 'list' ), true ) ) {
		$view = 'list' === gami_option( 'shop_view' ) ? 'list' : 'grid';
	}
	if ( $cols < 2 || $cols > 4 ) {
		$cols = min( 4, max( 2, absint( gami_option( 'shop_columns' ) ) ) );
	}
	$choices = gami_shop_per_page_choices();
	if ( ! in_array( $pp, $choices, true ) ) {
		$default = absint( gami_option( 'shop_per_page' ) );
		$pp      = in_array( $default, $choices, true ) ? $default : $choices[0];
	}
	$state = compact( 'view', 'cols', 'pp', 'mcol' );
	return $state;
}

function gami_is_catalogue_archive() {
	return is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) );
}

add_filter(
	'loop_shop_per_page',
	function () {
		$s = gami_shop_state();
		return $s['pp'];
	},
	20
);

add_filter(
	'loop_shop_columns',
	function () {
		$s = gami_shop_state();
		return $s['cols'];
	},
	20
);

/* -------------------------------------------------------------------------
 * Toolbar
 * ---------------------------------------------------------------------- */

remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
add_action( 'woocommerce_before_shop_loop', 'gami_shop_toolbar', 20 );

/**
 * Hidden fields that keep the current filters when a toolbar form is sent.
 */
function gami_shop_hidden_fields( $skip ) {
	$skip = array_merge( (array) $skip, array( 'paged', 'product-page', 'submit' ) );
	if ( function_exists( 'wc_query_string_form_fields' ) ) {
		wc_query_string_form_fields( null, $skip );
	}
}

/**
 * The page-1 address of this listing, without its query string.
 */
function gami_shop_base_url() {
	$url = get_pagenum_link( 1, false );
	return strtok( $url, '?' );
}

function gami_shop_toolbar() {
	if ( ! wc_get_loop_prop( 'is_paginated' ) || ! woocommerce_products_will_display() ) {
		return;
	}
	$state   = gami_shop_state();
	$total   = (int) wc_get_loop_prop( 'total' );
	$per     = (int) wc_get_loop_prop( 'per_page' );
	$current = (int) wc_get_loop_prop( 'current_page' );
	$first   = ( $per * $current ) - $per + 1;
	$last    = min( $total, $per * $current );
	$show    = gami_option( 'shop_controls' );
	?>
	<div class="shop-toolbar">
		<p class="shop-toolbar__count" role="status">
			<?php
			if ( $total <= $per || -1 === $per ) {
				/* translators: %d: total products */
				printf( esc_html( _n( '%d product', '%d products', $total, 'gami-surgical' ) ), (int) $total );
			} else {
				/* translators: 1: first, 2: last, 3: total */
				printf( esc_html__( '%1$d–%2$d of %3$d products', 'gami-surgical' ), (int) $first, (int) $last, (int) $total );
			}
			?>
		</p>

		<div class="shop-toolbar__controls">
			<button class="button button--ghost shop-toolbar__filter" type="button" data-open-filters aria-controls="shop-filters" aria-expanded="false">
				<?php echo gami_icon( 'filter', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<span><?php esc_html_e( 'Filter', 'gami-surgical' ); ?></span>
			</button>
			<?php if ( $show ) : ?>
				<div class="shop-toolbar__group shop-toolbar__mview" role="group" aria-label="<?php esc_attr_e( 'Layout', 'gami-surgical' ); ?>">
					<?php
					foreach ( array(
						'grid1' => array( __( '1 per row', 'gami-surgical' ), '<rect x="5" y="4" width="14" height="16" rx="1.5"/>' ),
						'grid2' => array( __( '2 per row', 'gami-surgical' ), '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>' ),
						'list'  => array( __( 'List view', 'gami-surgical' ), '<rect x="4" y="4.5" width="5" height="5" rx="1"/><rect x="4" y="14.5" width="5" height="5" rx="1"/><path d="M12 6h8M12 8.5h5M12 16h8M12 18.5h5" stroke-linecap="round"/>' ),
					) as $key => $data ) :
						$pressed = ( 'list' === $key && 'list' === $state['view'] ) || ( 'list' !== $state['view'] && 'grid' . $state['mcol'] === $key );
						?>
						<button type="button" class="view-btn" data-mview="<?php echo esc_attr( $key ); ?>" aria-pressed="<?php echo $pressed ? 'true' : 'false'; ?>" title="<?php echo esc_attr( $data[0] ); ?>">
							<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.7"><?php echo $data[1]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></svg>
							<span class="screen-reader-text"><?php echo esc_html( $data[0] ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>

				<div class="shop-toolbar__group shop-toolbar__view" role="group" aria-label="<?php esc_attr_e( 'Layout', 'gami-surgical' ); ?>">
					<?php
					foreach ( array(
						'grid' => array( __( 'Grid view', 'gami-surgical' ), '<rect x="4" y="4" width="6.5" height="6.5" rx="1"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1"/>' ),
						'list' => array( __( 'List view', 'gami-surgical' ), '<rect x="4" y="4.5" width="5" height="5" rx="1"/><rect x="4" y="14.5" width="5" height="5" rx="1"/><path d="M12 6h8M12 8.5h5M12 16h8M12 18.5h5" stroke-linecap="round"/>' ),
					) as $view => $data ) :
						?>
						<a class="view-btn" href="<?php echo esc_url( add_query_arg( 'view', $view ) ); ?>" data-view="<?php echo esc_attr( $view ); ?>" aria-pressed="<?php echo $state['view'] === $view ? 'true' : 'false'; ?>" title="<?php echo esc_attr( $data[0] ); ?>" rel="nofollow">
							<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.7"><?php echo $data[1]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></svg>
							<span class="screen-reader-text"><?php echo esc_html( $data[0] ); ?></span>
						</a>
					<?php endforeach; ?>
				</div>

				<div class="shop-toolbar__group shop-toolbar__cols" role="group" aria-label="<?php esc_attr_e( 'Products per row', 'gami-surgical' ); ?>">
					<?php for ( $c = 2; $c <= 4; $c++ ) : ?>
						<a class="view-btn view-btn--cols" href="<?php echo esc_url( add_query_arg( array( 'cols' => $c, 'view' => 'grid' ) ) ); ?>" data-cols="<?php echo (int) $c; ?>" aria-pressed="<?php echo ( 'grid' === $state['view'] && $state['cols'] === $c ) ? 'true' : 'false'; ?>" title="<?php echo esc_attr( sprintf( /* translators: %d columns */ __( '%d per row', 'gami-surgical' ), $c ) ); ?>" rel="nofollow">
							<span class="cols-icon" aria-hidden="true"><?php echo str_repeat( '<i></i>', $c ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %d columns */ __( '%d products per row', 'gami-surgical' ), $c ) ); ?></span>
						</a>
					<?php endfor; ?>
				</div>

				<form class="shop-toolbar__perpage" method="get" action="<?php echo esc_url( gami_shop_base_url() ); ?>">
					<label for="gs-per-page"><?php esc_html_e( 'Show', 'gami-surgical' ); ?></label>
					<select id="gs-per-page" name="per_page" data-autosubmit>
						<?php foreach ( gami_shop_per_page_choices() as $n ) : ?>
							<option value="<?php echo (int) $n; ?>" <?php selected( $state['pp'], $n ); ?>><?php echo (int) $n; ?></option>
						<?php endforeach; ?>
					</select>
					<?php gami_shop_hidden_fields( array( 'per_page' ) ); ?>
					<noscript><button type="submit" class="button"><?php esc_html_e( 'Apply', 'gami-surgical' ); ?></button></noscript>
				</form>
			<?php endif; ?>

			<div class="shop-toolbar__sort">
				<span class="shop-toolbar__label" aria-hidden="true"><?php esc_html_e( 'Sort by', 'gami-surgical' ); ?></span>
				<?php woocommerce_catalog_ordering(); ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Wrap the product list so the layout classes apply before any JavaScript runs.
 */
function gami_shop_loop_classes( $html ) {
	if ( ! gami_is_catalogue_archive() || ! wc_get_loop_prop( 'is_paginated' ) ) {
		return $html;
	}
	$state = gami_shop_state();
	return str_replace( 'class="products ', 'class="products is-' . $state['view'] . ' m-cols-' . $state['mcol'] . ' ', $html );
}
add_filter( 'woocommerce_product_loop_start', 'gami_shop_loop_classes' );

/* -------------------------------------------------------------------------
 * List view details (hidden in grid view)
 * ---------------------------------------------------------------------- */

function gami_shop_list_details() {
	global $product;
	if ( ! $product || ! gami_is_catalogue_archive() ) {
		return;
	}
	$excerpt = $product->get_short_description();
	if ( ! $excerpt ) {
		$excerpt = $product->get_description();
	}
	$excerpt = wp_trim_words( wp_strip_all_tags( $excerpt ), 26 );
	$specs   = array();
	$fields  = gami_instrument_fields( 'product' );
	foreach ( array( 'material', 'finish', 'usage' ) as $key ) {
		$val = get_post_meta( $product->get_id(), '_gami_' . $key, true );
		if ( $val && isset( $fields[ $key ] ) ) {
			$specs[ $fields[ $key ] ] = $val;
		}
	}
	if ( ! $excerpt && ! $specs ) {
		return;
	}
	echo '<div class="product-list-details">';
	if ( $excerpt ) {
		echo '<p class="product-excerpt">' . esc_html( $excerpt ) . '</p>';
	}
	if ( $specs ) {
		echo '<dl class="product-mini-specs">';
		foreach ( $specs as $label => $val ) {
			printf( '<div><dt>%1$s</dt><dd>%2$s</dd></div>', esc_html( $label ), esc_html( $val ) );
		}
		echo '</dl>';
	}
	echo '</div>';
}
add_action( 'woocommerce_after_shop_loop_item_title', 'gami_shop_list_details', 6 );

/* -------------------------------------------------------------------------
 * Category cards (shop page) and sub-category links (category pages)
 * ---------------------------------------------------------------------- */

function gami_shop_category_cards() {
	if ( ! gami_option( 'shop_cat_cards' ) || ! is_shop() || is_search() || is_paged() ) {
		return;
	}
	$parents = gami_catalog_terms();
	if ( ! $parents ) {
		return;
	}
	?>
	<nav class="cat-cards cat-cards--<?php echo (int) min( 4, count( $parents ) ); ?>" aria-label="<?php esc_attr_e( 'Product categories', 'gami-surgical' ); ?>">
		<?php
		foreach ( $parents as $term ) :
			$thumb = gami_term_thumb( $term, 'cat-card__img' );
			$count = gami_count_label( $term );
			$kids  = gami_term_children( $term->term_id, 5 );
			?>
			<div class="cat-card">
				<a class="cat-card__main" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
					<span class="cat-card__media"><?php echo $thumb ? $thumb : gami_icon( 'inquiry', 32 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="cat-card__text">
						<span class="cat-card__name"><?php echo esc_html( $term->name ); ?></span>
						<?php if ( $count ) : ?>
							<span class="cat-card__count"><?php echo esc_html( $count ); ?></span>
						<?php endif; ?>
					</span>
					<span class="cat-card__arrow" aria-hidden="true">→</span>
				</a>
				<?php if ( $kids ) : ?>
					<ul class="cat-card__kids">
						<?php foreach ( $kids as $kid ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $kid ) ); ?>"><?php echo esc_html( $kid->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</nav>
	<?php
}
add_action( 'woocommerce_archive_description', 'gami_shop_category_cards', 20 );

function gami_shop_subcategory_links() {
	if ( ! is_product_category() ) {
		return;
	}
	$term = get_queried_object();
	$kids = gami_term_children( $term->term_id );
	// On a sub-category with no children, show its siblings instead.
	$parent_id = $kids ? $term->term_id : (int) $term->parent;
	if ( ! $kids && $parent_id ) {
		$kids = gami_term_children( $parent_id );
	}
	if ( ! $kids ) {
		return;
	}
	$parent = get_term( $parent_id, 'product_cat' );
	$cards  = array();
	if ( $parent && ! is_wp_error( $parent ) ) {
		$cards[] = array(
			'term'  => $parent,
			/* translators: %s: category name */
			'label' => sprintf( __( 'All %s', 'gami-surgical' ), $parent->name ),
			'all'   => true,
		);
	}
	foreach ( $kids as $kid ) {
		$cards[] = array(
			'term'  => $kid,
			'label' => $kid->name,
			'all'   => false,
		);
	}
	?>
	<div class="subcat-slider" data-slider>
		<button type="button" class="subcat-slider__btn subcat-slider__btn--prev" data-slide="-1" aria-label="<?php esc_attr_e( 'Scroll sub-categories left', 'gami-surgical' ); ?>" hidden>
			<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="m14.5 6-6 6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</button>
		<nav class="subcat-slider__viewport" aria-label="<?php esc_attr_e( 'Sub-categories', 'gami-surgical' ); ?>">
			<ul class="subcat-slider__track">
				<?php
				foreach ( $cards as $card ) :
					$t       = $card['term'];
					$current = $t->term_id === $term->term_id;
					$n       = gami_term_count( $t );
					$thumb   = gami_term_thumb( $t, 'subcat-card__img' );
					?>
					<li class="subcat-slider__item">
						<a class="subcat-card<?php echo $current ? ' is-current' : ''; ?><?php echo $card['all'] ? ' subcat-card--all' : ''; ?>" href="<?php echo esc_url( get_term_link( $t ) ); ?>"<?php echo $current ? ' aria-current="page"' : ''; ?> draggable="false">
							<span class="subcat-card__media">
								<?php
								if ( $card['all'] ) {
									echo '<span class="subcat-card__all-icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26"><rect x="4" y="4" width="6.5" height="6.5" rx="1.2" fill="none" stroke="currentColor" stroke-width="1.7"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.2" fill="none" stroke="currentColor" stroke-width="1.7"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.2" fill="none" stroke="currentColor" stroke-width="1.7"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.2" fill="none" stroke="currentColor" stroke-width="1.7"/></svg></span>';
								} elseif ( $thumb ) {
									echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								} else {
									echo '<span class="subcat-card__placeholder" aria-hidden="true">' . esc_html( mb_substr( $t->name, 0, 1 ) ) . '</span>';
								}
								?>
							</span>
							<span class="subcat-card__name"><?php echo esc_html( $card['label'] ); ?></span>
							<?php if ( $n ) : ?>
								<span class="subcat-card__count"><?php echo esc_html( sprintf( /* translators: %d products */ _n( '%d product', '%d products', $n, 'gami-surgical' ), $n ) ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</nav>
		<button type="button" class="subcat-slider__btn subcat-slider__btn--next" data-slide="1" aria-label="<?php esc_attr_e( 'Scroll sub-categories right', 'gami-surgical' ); ?>" hidden>
			<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="m9.5 6 6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
		</button>
	</div>
	<?php
}
add_action( 'woocommerce_archive_description', 'gami_shop_subcategory_links', 20 );

/* -------------------------------------------------------------------------
 * Sidebar
 * ---------------------------------------------------------------------- */

function gami_shop_sidebar() {
	$current   = is_product_category() ? get_queried_object() : null;
	$ancestors = $current ? array_merge( array( $current->term_id ), get_ancestors( $current->term_id, 'product_cat' ) ) : array();
	$parents   = gami_catalog_terms();
	$total     = (int) wp_count_posts( 'product' )->publish;
	$wa        = gami_whatsapp_url();
	?>
	<div class="filters-head">
		<span class="filters-head__title"><?php esc_html_e( 'Categories', 'gami-surgical' ); ?></span>
		<button class="drawer-close filters-head__close" type="button" data-close-filters>
			<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			<span class="screen-reader-text"><?php esc_html_e( 'Close filters', 'gami-surgical' ); ?></span>
		</button>
	</div>

	<section class="side-card side-card--cats">
		<h2 class="side-card__title"><?php esc_html_e( 'Categories', 'gami-surgical' ); ?></h2>
		<ul class="side-cats">
			<li class="side-cats__all<?php echo is_shop() && ! is_search() ? ' is-current' : ''; ?>">
				<a href="<?php echo esc_url( gami_catalog_url() ); ?>"<?php echo is_shop() && ! is_search() ? ' aria-current="page"' : ''; ?>>
					<span><?php esc_html_e( 'All products', 'gami-surgical' ); ?></span>
					<span class="side-cats__count"><?php echo (int) $total; ?></span>
				</a>
			</li>
			<?php
			foreach ( $parents as $parent ) :
				$kids    = gami_term_children( $parent->term_id );
				$is_open = in_array( $parent->term_id, $ancestors, true );
				$is_cur  = $current && $current->term_id === $parent->term_id;
				?>
				<li class="side-cats__group<?php echo $is_cur ? ' is-current' : ''; ?>">
					<?php if ( $kids ) : ?>
						<details <?php echo $is_open ? 'open' : ''; ?>>
							<summary>
								<a href="<?php echo esc_url( get_term_link( $parent ) ); ?>"<?php echo $is_cur ? ' aria-current="page"' : ''; ?>>
									<span><?php echo esc_html( $parent->name ); ?></span>
									<span class="side-cats__count"><?php echo (int) gami_term_count( $parent ); ?></span>
								</a>
								<span class="side-cats__toggle" aria-hidden="true"><?php echo gami_icon( 'chevron', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							</summary>
							<ul class="side-cats__kids">
								<?php
								foreach ( $kids as $kid ) :
									$kid_cur  = $current && $current->term_id === $kid->term_id;
									$kid_open = in_array( $kid->term_id, $ancestors, true );
									$grand    = gami_term_children( $kid->term_id );
									?>
									<li class="<?php echo $kid_cur ? 'is-current' : ''; ?>">
										<a href="<?php echo esc_url( get_term_link( $kid ) ); ?>"<?php echo $kid_cur ? ' aria-current="page"' : ''; ?>>
											<span><?php echo esc_html( $kid->name ); ?></span>
											<span class="side-cats__count"><?php echo (int) gami_term_count( $kid ); ?></span>
										</a>
										<?php if ( $grand && ( $kid_open || $kid_cur ) ) : ?>
											<ul class="side-cats__grand">
												<?php foreach ( $grand as $g ) : ?>
													<li class="<?php echo $current && $current->term_id === $g->term_id ? 'is-current' : ''; ?>">
														<a href="<?php echo esc_url( get_term_link( $g ) ); ?>"<?php echo $current && $current->term_id === $g->term_id ? ' aria-current="page"' : ''; ?>><span><?php echo esc_html( $g->name ); ?></span><span class="side-cats__count"><?php echo (int) gami_term_count( $g ); ?></span></a>
													</li>
												<?php endforeach; ?>
											</ul>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						</details>
					<?php else : ?>
						<a class="side-cats__single" href="<?php echo esc_url( get_term_link( $parent ) ); ?>"<?php echo $is_cur ? ' aria-current="page"' : ''; ?>>
							<span><?php echo esc_html( $parent->name ); ?></span>
							<span class="side-cats__count"><?php echo (int) gami_term_count( $parent ); ?></span>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<?php if ( gami_shop_has_extra_widgets() ) : ?>
		<div class="side-widgets"><?php dynamic_sidebar( 'sidebar-shop' ); ?></div>
	<?php endif; ?>

	<?php if ( function_exists( 'gami_inquiry_page_url' ) && gami_option( 'inquiry_list' ) ) : ?>
		<section class="side-card side-card--list" data-side-list>
			<h2 class="side-card__title"><?php esc_html_e( 'Your inquiry list', 'gami-surgical' ); ?></h2>
			<p class="side-list__empty"><?php esc_html_e( 'Use the list button on products to collect several items and send one inquiry.', 'gami-surgical' ); ?></p>
			<p class="side-list__filled" hidden>
				<span class="side-list__num gs-inquiry-count-text">0</span>
				<span><?php esc_html_e( 'products on your list', 'gami-surgical' ); ?></span>
			</p>
			<a class="button button--primary side-list__btn" href="<?php echo esc_url( gami_inquiry_page_url() ); ?>" hidden><?php esc_html_e( 'View list & send', 'gami-surgical' ); ?></a>
		</section>
	<?php endif; ?>

	<section class="side-card side-card--help">
		<h2 class="side-card__title"><?php esc_html_e( 'Can’t find an instrument?', 'gami-surgical' ); ?></h2>
		<p><?php esc_html_e( 'Send a description, catalogue number or photo and we will reply with prices and lead time.', 'gami-surgical' ); ?></p>
		<div class="side-card__actions">
			<a class="button button--primary" href="<?php echo esc_url( gami_quote_url() ); ?>"><?php esc_html_e( 'Send an inquiry', 'gami-surgical' ); ?></a>
			<?php if ( $wa ) : ?>
				<a class="side-card__wa" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php echo gami_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'WhatsApp us', 'gami-surgical' ); ?></span></a>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Skip product-category widgets in the Shop sidebar (classic and block):
 * the Categories card above already lists every category.
 */
function gami_shop_skip_category_widgets( $instance, $widget, $args ) {
	if ( ! isset( $args['id'] ) || 'sidebar-shop' !== $args['id'] ) {
		return $instance;
	}
	if ( in_array( $widget->id_base, array( 'woocommerce_product_categories', 'categories' ), true ) ) {
		return false;
	}
	if ( 'block' === $widget->id_base && isset( $instance['content'] ) && gami_is_category_block( $instance['content'] ) ) {
		return false;
	}
	return $instance;
}
add_filter( 'widget_display_callback', 'gami_shop_skip_category_widgets', 10, 3 );

function gami_is_category_block( $content ) {
	return (bool) preg_match( '/<!-- wp:(woocommerce\/product-categories|categories|woocommerce\/product-filter-taxonomy)\b/', (string) $content );
}

/**
 * Show the widget area only if something is left in it after the skip.
 */
function gami_shop_has_extra_widgets() {
	$sidebars = wp_get_sidebars_widgets();
	if ( empty( $sidebars['sidebar-shop'] ) ) {
		return false;
	}
	foreach ( (array) $sidebars['sidebar-shop'] as $id ) {
		$base = preg_replace( '/-\d+$/', '', $id );
		if ( in_array( $base, array( 'woocommerce_product_categories', 'categories' ), true ) ) {
			continue;
		}
		if ( 'block' === $base ) {
			$blocks = get_option( 'widget_block', array() );
			$num    = (int) substr( $id, strrpos( $id, '-' ) + 1 );
			$body   = isset( $blocks[ $num ]['content'] ) ? $blocks[ $num ]['content'] : '';
			if ( gami_is_category_block( $body ) ) {
				continue;
			}
		}
		return true;
	}
	return false;
}

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */

function gami_shop_assets() {
	if ( ! function_exists( 'is_woocommerce' ) || ! gami_is_catalogue_archive() ) {
		return;
	}
	wp_enqueue_style( 'gami-shop', GAMI_URI . '/assets/css/shop.css', array( 'gami-woocommerce' ), GAMI_VERSION );
	wp_enqueue_script(
		'gami-shop',
		GAMI_URI . '/assets/js/shop.js',
		array(),
		GAMI_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
}
add_action( 'wp_enqueue_scripts', 'gami_shop_assets', 25 );
