<?php
/**
 * Advanced header search.
 *
 * - Live results while typing (REST: /wp-json/gami/v1/search?q=&cat=).
 * - Matches product names, content and catalogue numbers (SKU, including
 *   variation SKUs and the instrument catalogue field). Exact catalogue
 *   number matches come first.
 * - Category filter, matching categories and pages in the results.
 * - Recent searches (stored in the visitor's browser) and popular searches.
 * - "Request a quote" prompt when nothing matches.
 *
 * Settings: Appearance > Customize > Gami Surgical > Search.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search mode in the header: bar, icon or off.
 */
function gami_search_mode() {
	$mode = gami_option( 'search_mode' );
	return in_array( $mode, array( 'bar', 'icon', 'off' ), true ) ? $mode : 'bar';
}

/**
 * Print the search form.
 *
 * @param string $id Unique id prefix (several forms can be on one page).
 */
function gami_search_bar( $id = 'gs-search' ) {
	$post_type = gami_catalog_post_type();
	$taxonomy  = gami_catalog_taxonomy();
	$current   = isset( $_GET[ $taxonomy ] ) ? sanitize_title( wp_unslash( $_GET[ $taxonomy ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$terms     = gami_option( 'search_cat_select' ) ? gami_catalog_terms() : array();
	$live      = gami_option( 'search_live' ) ? '1' : '0';
	?>
	<form class="gs-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-live="<?php echo esc_attr( $live ); ?>" id="<?php echo esc_attr( $id ); ?>">
		<div class="gs-search__field">
			<?php if ( $terms ) : ?>
				<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>-cat"><?php esc_html_e( 'Search in', 'gami-surgical' ); ?></label>
				<div class="gs-search__cat-wrap">
					<select class="gs-search__cat" id="<?php echo esc_attr( $id ); ?>-cat" name="<?php echo esc_attr( $taxonomy ); ?>">
						<option value=""><?php esc_html_e( 'All categories', 'gami-surgical' ); ?></option>
						<?php foreach ( $terms as $term ) : ?>
							<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
						<?php endforeach; ?>
					</select>
					<?php echo gami_icon( 'chevron', 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>

			<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>-q"><?php esc_html_e( 'Search products', 'gami-surgical' ); ?></label>
			<input
				class="gs-search__input"
				id="<?php echo esc_attr( $id ); ?>-q"
				type="search"
				name="s"
				value="<?php echo esc_attr( get_search_query() ); ?>"
				placeholder="<?php echo esc_attr( gami_option( 'search_placeholder' ) ); ?>"
				autocomplete="off"
				spellcheck="false"
				enterkeyhint="search"
				role="combobox"
				aria-autocomplete="list"
				aria-expanded="false"
				aria-controls="<?php echo esc_attr( $id ); ?>-panel"
			>
			<?php if ( 'any' !== $post_type ) : ?>
				<input type="hidden" name="post_type" value="<?php echo esc_attr( $post_type ); ?>">
			<?php endif; ?>

			<span class="gs-search__kbd" aria-hidden="true">/</span>
			<span class="gs-search__spinner" aria-hidden="true"></span>
			<button class="gs-search__clear" type="button" hidden>
				<svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				<span class="screen-reader-text"><?php esc_html_e( 'Clear search', 'gami-surgical' ); ?></span>
			</button>
			<button class="gs-search__submit" type="submit">
				<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6" fill="none" stroke="currentColor" stroke-width="2"/><path d="m15 15 5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
				<span class="gs-search__submit-text"><?php esc_html_e( 'Search', 'gami-surgical' ); ?></span>
			</button>
		</div>
		<div class="gs-search__panel" id="<?php echo esc_attr( $id ); ?>-panel" hidden></div>
		<p class="screen-reader-text gs-search__status" aria-live="polite"></p>
	</form>
	<?php
}

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */

function gami_search_assets() {
	if ( 'off' === gami_search_mode() ) {
		return;
	}
	wp_enqueue_script(
		'gami-search',
		GAMI_URI . '/assets/js/search.js',
		array(),
		GAMI_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);

	$popular = array_values( array_filter( array_map( 'trim', explode( ',', (string) gami_option( 'search_popular' ) ) ) ) );

	wp_localize_script(
		'gami-search',
		'gamiSearch',
		array(
			'endpoint' => esc_url_raw( rest_url( 'gami/v1/search' ) ),
			'minChars' => 2,
			'popular'  => array_slice( $popular, 0, 8 ),
			'quoteUrl' => esc_url_raw( gami_quote_url() ),
			'i18n'     => array(
				'products'   => __( 'Products', 'gami-surgical' ),
				'categories' => __( 'Categories', 'gami-surgical' ),
				'pages'      => __( 'Pages', 'gami-surgical' ),
				'recent'     => __( 'Recent searches', 'gami-surgical' ),
				'popular'    => __( 'Popular searches', 'gami-surgical' ),
				'clear'      => __( 'Clear', 'gami-surgical' ),
				'viewAll'    => __( 'See all results for "%s"', 'gami-surgical' ),
				'noResults'  => __( 'No products found for "%s"', 'gami-surgical' ),
				'noHint'     => __( 'Try a catalogue number or a shorter word. We also make instruments to order: send us an inquiry with what you need.', 'gami-surgical' ),
				'askQuote'   => __( 'Send inquiry', 'gami-surgical' ),
				'inquire'    => __( 'Inquire', 'gami-surgical' ),
				'lookingFor' => __( 'I am looking for: %s', 'gami-surgical' ),
				'items'      => __( '%d items', 'gami-surgical' ),
				'loading'    => __( 'Searching…', 'gami-surgical' ),
				'found'      => __( '%d results available. Use up and down arrows to browse.', 'gami-surgical' ),
				'catNo'      => __( 'Cat. no.', 'gami-surgical' ),
				'error'      => __( 'Search is not available right now. Press Enter to search the site.', 'gami-surgical' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'gami_search_assets' );

/* -------------------------------------------------------------------------
 * REST endpoint
 * ---------------------------------------------------------------------- */

function gami_search_routes() {
	register_rest_route(
		'gami/v1',
		'/search',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'permission_callback' => '__return_true',
			'callback'            => 'gami_search_rest',
			'args'                => array(
				'q'   => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => function ( $value ) {
						return sanitize_text_field( (string) $value );
					},
				),
				'cat' => array(
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => function ( $value ) {
						return sanitize_title( (string) $value );
					},
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'gami_search_routes' );

/**
 * IDs whose catalogue number (SKU) contains the term, exact matches first.
 * Variation SKUs resolve to their parent product.
 */
function gami_search_sku_ids( $term, $post_type ) {
	global $wpdb;
	$like = '%' . $wpdb->esc_like( $term ) . '%';
	$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->prepare(
			"SELECT pm.post_id, p.post_parent, p.post_type, pm.meta_value
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key IN ('_sku','_gami_catalogue_no') AND pm.meta_value LIKE %s
			 AND p.post_status = 'publish'
			 LIMIT 50",
			$like
		)
	);
	$exact = array();
	$other = array();
	foreach ( $rows as $row ) {
		$id = ( 'product_variation' === $row->post_type && $row->post_parent ) ? (int) $row->post_parent : (int) $row->post_id;
		if ( get_post_type( $id ) !== $post_type ) {
			continue;
		}
		if ( 0 === strcasecmp( $row->meta_value, $term ) ) {
			$exact[] = $id;
		} else {
			$other[] = $id;
		}
	}
	return array_values( array_unique( array_merge( $exact, $other ) ) );
}

function gami_search_item_data( $id ) {
	$item = array(
		'id'    => $id,
		'title' => html_entity_decode( wp_strip_all_tags( get_the_title( $id ) ), ENT_QUOTES, 'UTF-8' ),
		'url'   => get_permalink( $id ),
		'image' => get_the_post_thumbnail_url( $id, 'thumbnail' ),
		'sku'   => gami_catalogue_no( $id ),
		'cat'   => '',
		'price' => '',
		'quote' => false,
		'stock' => '',
	);

	$terms = get_the_terms( $id, gami_catalog_taxonomy() );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$item['cat'] = html_entity_decode( $terms[0]->name, ENT_QUOTES, 'UTF-8' );
	}

	if ( 'product' === get_post_type( $id ) && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $id );
		if ( $product ) {
			if ( ! $item['image'] ) {
				$item['image'] = wc_placeholder_img_src( 'thumbnail' );
			}
			$price = ( function_exists( 'gami_show_prices' ) && gami_show_prices() ) ? html_entity_decode( wp_strip_all_tags( $product->get_price_html() ), ENT_QUOTES, 'UTF-8' ) : '';
			if ( $price ) {
				$item['price'] = $price;
			} else {
				$item['quote'] = true;
			}
			if ( ! $product->is_in_stock() ) {
				$item['stock'] = __( 'Out of stock', 'gami-surgical' );
			}
		}
	}
	return $item;
}

function gami_search_rest( WP_REST_Request $request ) {
	$term = trim( (string) $request->get_param( 'q' ) );
	$cat  = (string) $request->get_param( 'cat' );

	$response = array(
		'query'      => $term,
		'products'   => array(),
		'categories' => array(),
		'pages'      => array(),
		'total'      => 0,
		'url'        => '',
	);
	if ( mb_strlen( $term ) < 2 ) {
		return rest_ensure_response( $response );
	}

	$post_type = gami_catalog_post_type();
	$taxonomy  = gami_catalog_taxonomy();
	$limit     = max( 3, min( 12, absint( gami_option( 'search_limit' ) ) ) );

	$tax_query = array();
	if ( $cat ) {
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field'    => 'slug',
			'terms'    => $cat,
		);
	}
	if ( 'product' === $post_type && function_exists( 'wc_get_product_visibility_term_ids' ) ) {
		$hidden = wc_get_product_visibility_term_ids();
		if ( ! empty( $hidden['exclude-from-search'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => array( $hidden['exclude-from-search'] ),
				'operator' => 'NOT IN',
			);
		}
	}

	// 1) Catalogue number matches.
	$sku_ids = gami_search_sku_ids( $term, $post_type );
	if ( $sku_ids && $tax_query ) {
		$sku_ids = get_posts(
			array(
				'post_type'      => $post_type,
				'post__in'       => $sku_ids,
				'orderby'        => 'post__in',
				'fields'         => 'ids',
				'posts_per_page' => 50,
				'tax_query'      => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
	}

	// 2) Text matches (the catalogue-number filter on posts_search is for the main query only).
	$text = new WP_Query(
		array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			's'                      => $term,
			'fields'                 => 'ids',
			'posts_per_page'         => $limit,
			'no_found_rows'          => false,
			'tax_query'              => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery
			'update_post_meta_cache' => false,
		)
	);

	$ids = array_values( array_unique( array_merge( $sku_ids, $text->posts ) ) );

	$response['total'] = count( $sku_ids ) + (int) $text->found_posts - count( array_intersect( $sku_ids, $text->posts ) );
	foreach ( array_slice( $ids, 0, $limit ) as $id ) {
		$response['products'][] = gami_search_item_data( $id );
	}

	// Matching categories.
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'name__like' => $term,
			'hide_empty' => false,
			'number'     => 4,
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$thumb = '';
			if ( 'product_cat' === $taxonomy ) {
				$thumb_id = function_exists( 'gami_term_image_id' ) ? gami_term_image_id( $t ) : get_term_meta( $t->term_id, 'thumbnail_id', true );
				$thumb    = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
			}
			$response['categories'][] = array(
				'title' => html_entity_decode( $t->name, ENT_QUOTES, 'UTF-8' ),
				'url'   => get_term_link( $t ),
				'count' => (int) $t->count,
				'image' => $thumb,
			);
		}
	}

	// Matching pages.
	if ( gami_option( 'search_pages' ) ) {
		$exclude = array();
		if ( function_exists( 'wc_get_page_id' ) ) {
			foreach ( array( 'cart', 'checkout', 'myaccount' ) as $wc_page ) {
				$exclude[] = wc_get_page_id( $wc_page );
			}
		}
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				's'              => $term,
				'posts_per_page' => 3,
				'post__not_in'   => array_filter( $exclude ),
				'fields'         => 'ids',
			)
		);
		foreach ( $pages as $page_id ) {
			$response['pages'][] = array(
				'title' => html_entity_decode( wp_strip_all_tags( get_the_title( $page_id ) ), ENT_QUOTES, 'UTF-8' ),
				'url'   => get_permalink( $page_id ),
			);
		}
	}

	$args = array( 's' => $term );
	if ( 'any' !== $post_type ) {
		$args['post_type'] = $post_type;
	}
	if ( $cat ) {
		$args[ $taxonomy ] = $cat;
	}
	$response['url'] = add_query_arg( array_map( 'rawurlencode', $args ), home_url( '/' ) );

	$result = rest_ensure_response( apply_filters( 'gami_search_response', $response, $request ) );
	$result->header( 'Cache-Control', 'public, max-age=120' );
	return $result;
}
