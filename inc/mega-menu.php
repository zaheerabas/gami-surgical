<?php
/**
 * Mega menu for the primary menu.
 *
 * In Appearance > Menus, open a top-level item:
 * - "Show as mega menu": its sub-items become columns. Second-level items are
 *   column headings, third-level items are the links under them.
 * - "Columns": 2 to 5.
 * - "Fill with product categories": builds the columns automatically from your
 *   product categories (top-level categories as headings, sub-categories as
 *   links, with category images), plus a quote panel. No sub-items needed.
 * - "Badge": a small label next to any item, for example New.
 * Menu item descriptions (Screen Options > Description) show under links
 * inside mega menus.
 *
 * Automatic (Customize > Gami Surgical > Mega menu, on by default):
 * - A product category added as a top-level menu item shows its
 *   sub-categories as a mega menu (columns when they have their own
 *   sub-categories, image tiles when they don't).
 * - A top-level item whose sub-items are product categories becomes a mega
 *   menu, and each of those categories lists its own sub-categories.
 * Empty categories are shown too, so the menu works while products are added.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function gami_mega_fields() {
	return array(
		'_gami_mega'      => 'bool',
		'_gami_mega_cols' => 'int',
		'_gami_mega_auto' => 'bool',
		'_gami_badge'     => 'text',
	);
}

/* -------------------------------------------------------------------------
 * Admin fields (Appearance > Menus)
 * ---------------------------------------------------------------------- */

function gami_mega_admin_fields( $item_id, $item ) {
	$mega  = get_post_meta( $item_id, '_gami_mega', true );
	$cols  = (int) get_post_meta( $item_id, '_gami_mega_cols', true );
	$auto  = get_post_meta( $item_id, '_gami_mega_auto', true );
	$badge = get_post_meta( $item_id, '_gami_badge', true );
	$cols  = $cols ? $cols : 4;
	wp_nonce_field( 'gami_mega_' . $item_id, 'gami_mega_nonce_' . $item_id );
	?>
	<fieldset class="field-gami-mega description description-wide" style="margin:8px 0;padding:8px 10px;border:1px solid #dcdcde;background:#f6f7f7">
		<legend style="font-weight:600;padding:0 4px"><?php esc_html_e( 'Gami Surgical', 'gami-surgical' ); ?></legend>
		<p style="margin:4px 0">
			<label><input type="checkbox" name="gami_mega[<?php echo (int) $item_id; ?>]" value="1" <?php checked( $mega ); ?>> <?php esc_html_e( 'Show as mega menu (top-level items only)', 'gami-surgical' ); ?></label>
		</p>
		<p style="margin:4px 0">
			<label><?php esc_html_e( 'Columns', 'gami-surgical' ); ?>
				<select name="gami_mega_cols[<?php echo (int) $item_id; ?>]">
					<?php for ( $i = 2; $i <= 5; $i++ ) : ?>
						<option value="<?php echo (int) $i; ?>" <?php selected( $cols, $i ); ?>><?php echo (int) $i; ?></option>
					<?php endfor; ?>
				</select>
			</label>
		</p>
		<?php if ( taxonomy_exists( gami_catalog_taxonomy() ) ) : ?>
			<p style="margin:4px 0">
				<label><input type="checkbox" name="gami_mega_auto[<?php echo (int) $item_id; ?>]" value="1" <?php checked( $auto ); ?>> <?php esc_html_e( 'Fill with product categories automatically', 'gami-surgical' ); ?></label>
			</p>
		<?php endif; ?>
		<p style="margin:4px 0">
			<label><?php esc_html_e( 'Badge', 'gami-surgical' ); ?>
				<input type="text" class="widefat" name="gami_badge[<?php echo (int) $item_id; ?>]" value="<?php echo esc_attr( $badge ); ?>" placeholder="<?php esc_attr_e( 'e.g. New', 'gami-surgical' ); ?>" maxlength="20">
			</label>
		</p>
	</fieldset>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'gami_mega_admin_fields', 10, 2 );

function gami_mega_admin_save( $menu_id, $item_id ) {
	$nonce = 'gami_mega_nonce_' . $item_id;
	if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), 'gami_mega_' . $item_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	// phpcs:disable WordPress.Security.NonceVerification.Missing -- verified above.
	$mega  = ! empty( $_POST['gami_mega'][ $item_id ] );
	$auto  = ! empty( $_POST['gami_mega_auto'][ $item_id ] );
	$cols  = isset( $_POST['gami_mega_cols'][ $item_id ] ) ? min( 5, max( 2, absint( $_POST['gami_mega_cols'][ $item_id ] ) ) ) : 4;
	$badge = isset( $_POST['gami_badge'][ $item_id ] ) ? sanitize_text_field( wp_unslash( $_POST['gami_badge'][ $item_id ] ) ) : '';
	// phpcs:enable

	$mega ? update_post_meta( $item_id, '_gami_mega', 1 ) : delete_post_meta( $item_id, '_gami_mega' );
	$auto ? update_post_meta( $item_id, '_gami_mega_auto', 1 ) : delete_post_meta( $item_id, '_gami_mega_auto' );
	update_post_meta( $item_id, '_gami_mega_cols', $cols );
	$badge ? update_post_meta( $item_id, '_gami_badge', $badge ) : delete_post_meta( $item_id, '_gami_badge' );
}
add_action( 'wp_update_nav_menu_item', 'gami_mega_admin_save', 10, 2 );

/* -------------------------------------------------------------------------
 * Front end
 * ---------------------------------------------------------------------- */

function gami_is_primary_menu( $args ) {
	return isset( $args->theme_location ) && 'primary' === $args->theme_location;
}

/**
 * Classes: mega-menu, mega-cols-N, and has-children for auto panels.
 */
function gami_mega_classes( $classes, $item, $args, $depth ) {
	if ( ! gami_is_primary_menu( $args ) || 0 !== (int) $depth ) {
		return $classes;
	}
	$auto = get_post_meta( $item->ID, '_gami_mega_auto', true );
	if ( get_post_meta( $item->ID, '_gami_mega', true ) || $auto ) {
		$cols      = (int) get_post_meta( $item->ID, '_gami_mega_cols', true );
		$classes[] = 'mega-menu';
		$classes[] = 'mega-cols-' . ( $cols ? $cols : 4 );
		if ( $auto && ! in_array( 'menu-item-has-children', $classes, true ) && gami_catalog_terms() ) {
			$classes[] = 'menu-item-has-children';
			$classes[] = 'mega-menu--auto';
		}
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'gami_mega_classes', 10, 4 );

/**
 * Item output: badge, description inside mega menus, auto category panel.
 */
function gami_mega_item_output( $output, $item, $depth, $args ) {
	if ( ! gami_is_primary_menu( $args ) ) {
		return $output;
	}

	$badge = get_post_meta( $item->ID, '_gami_badge', true );
	if ( $badge ) {
		$output = preg_replace( '#</a>\s*$#', '<span class="menu-badge">' . esc_html( $badge ) . '</span></a>', $output, 1 );
	}

	// Links layout: thumbnail and product count on category links.
	if ( ! empty( $item->gami_link_term ) ) {
		$term = get_term( (int) $item->gami_link_term, gami_catalog_taxonomy() );
		if ( $term && ! is_wp_error( $term ) ) {
			$thumb  = gami_mega_thumb( $term, 'mega-link__thumb' );
			$n      = gami_term_count( $term );
			$output = preg_replace( '#(<a\b[^>]*>)#', '$1' . $thumb . '<span class="mega-link__text">', $output, 1 );
			$output = preg_replace( '#</a>\s*$#', '</span>' . ( $n ? '<span class="mega-link__count">' . (int) $n . '</span>' : '' ) . '</a>', $output, 1 );
		}
	}
	if ( ! empty( $item->gami_allrow_term ) ) {
		$term = get_term( (int) $item->gami_allrow_term, gami_catalog_taxonomy() );
		$n    = $term && ! is_wp_error( $term ) ? gami_term_count( $term ) : 0;
		/* translators: %d: number of products */
		$meta   = $n ? '<span class="mega-allrow__count">' . esc_html( sprintf( _n( '%d product', '%d products', $n, 'gami-surgical' ), $n ) ) . '</span>' : '';
		$output = preg_replace( '#</a>\s*$#', $meta . '<span class="mega-allrow__arrow" aria-hidden="true">→</span></a>', $output, 1 );
	}

	if ( $depth > 0 && ! empty( $item->description ) && gami_mega_ancestor( $item ) ) {
		$output = preg_replace( '#</a>\s*$#', '<span class="menu-desc">' . esc_html( $item->description ) . '</span></a>', $output, 1 );
	}

	if ( 0 === (int) $depth && get_post_meta( $item->ID, '_gami_mega_auto', true ) ) {
		$output .= gami_mega_auto_panel();
	} elseif ( ! empty( $item->gami_cat_panel ) ) {
		$output .= gami_mega_cat_panel( $item->gami_cat_panel );
	} elseif ( ! empty( $item->gami_cat_links ) ) {
		$output .= gami_mega_cat_links( $item->gami_cat_links );
	}
	return $output;
}
add_filter( 'walker_nav_menu_start_el', 'gami_mega_item_output', 10, 4 );

/**
 * Product count including sub-categories (WooCommerce keeps this total).
 */
function gami_term_count( $term ) {
	$total = get_term_meta( $term->term_id, 'product_count_product_cat', true );
	return '' !== $total && false !== $total ? (int) $total : (int) $term->count;
}

/**
 * Sub-categories in the order set under Products > Categories, empty ones included.
 */
function gami_term_children( $term_id, $limit = 0 ) {
	$taxonomy = gami_catalog_taxonomy();
	$args     = array(
		'taxonomy'   => $taxonomy,
		'parent'     => (int) $term_id,
		'hide_empty' => false,
		'orderby'    => 'product_cat' === $taxonomy ? 'menu_order' : 'name',
	);
	if ( $limit ) {
		$args['number'] = $limit;
	}
	$terms = get_terms( $args );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Image for a category: its own Thumbnail (Products > Categories), otherwise
 * the photo of a product in it or its sub-categories. Cached; refreshed when
 * products or categories change.
 */
function gami_term_image_id( $term ) {
	if ( ! $term || 'product_cat' !== $term->taxonomy ) {
		return 0;
	}
	$own = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	if ( $own ) {
		return $own;
	}
	if ( ! gami_option( 'cat_auto_images' ) ) {
		return 0;
	}
	$key    = 'gami_cimg_' . $term->term_id . '_' . (int) get_option( 'gami_cimg_ver', 1 );
	$cached = get_transient( $key );
	if ( false !== $cached ) {
		return (int) $cached;
	}
	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'orderby'        => array(
				'menu_order' => 'ASC',
				'date'       => 'DESC',
			),
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy'         => 'product_cat',
					'field'            => 'term_id',
					'terms'            => $term->term_id,
					'include_children' => true,
				),
			),
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
		)
	);
	$image = $ids ? (int) get_post_thumbnail_id( $ids[0] ) : 0;
	set_transient( $key, $image, DAY_IN_SECONDS );
	return $image;
}

/**
 * Refresh the automatic category images when products or categories change.
 */
function gami_bump_cat_images() {
	update_option( 'gami_cimg_ver', (int) get_option( 'gami_cimg_ver', 1 ) + 1, false );
}
add_action( 'save_post_product', 'gami_bump_cat_images' );
add_action( 'edited_product_cat', 'gami_bump_cat_images' );
add_action( 'created_product_cat', 'gami_bump_cat_images' );
add_action( 'delete_product_cat', 'gami_bump_cat_images' );

function gami_term_thumb( $term, $class = 'mega-thumb', $size = 'thumbnail' ) {
	$id = gami_term_image_id( $term );
	return $id ? wp_get_attachment_image( $id, $size, false, array( 'class' => $class, 'alt' => '', 'loading' => 'lazy', 'decoding' => 'async' ) ) : '';
}

/**
 * Category photo inside mega menus (Customize > Gami Surgical > Mega menu).
 */
function gami_mega_thumb( $term, $class = 'mega-thumb' ) {
	return gami_option( 'mega_photos' ) ? gami_term_thumb( $term, $class ) : '';
}

function gami_count_label( $term ) {
	$n = gami_term_count( $term );
	/* translators: %d number of products */
	return $n > 0 ? sprintf( _n( '%d product', '%d products', $n, 'gami-surgical' ), $n ) : '';
}

/**
 * Work out automatic category mega menus before the menu is printed.
 */
function gami_mega_prepare_items( $items, $args ) {
	if ( ! gami_is_primary_menu( $args ) || ! gami_option( 'mega_auto_categories' ) ) {
		return $items;
	}
	$taxonomy = gami_catalog_taxonomy();
	$children = array();
	$extra    = array();
	foreach ( $items as $item ) {
		$children[ (int) $item->menu_item_parent ][] = $item;
	}
	$is_cat = function ( $item ) use ( $taxonomy ) {
		return 'taxonomy' === $item->type && $taxonomy === $item->object;
	};

	foreach ( $items as $item ) {
		if ( 0 !== (int) $item->menu_item_parent ) {
			continue;
		}
		$id   = (int) $item->ID;
		$kids = isset( $children[ $id ] ) ? $children[ $id ] : array();

		if ( ! $kids ) {
			// A category on its own in the menu bar: show its sub-categories.
			if ( $is_cat( $item ) && ! get_post_meta( $id, '_gami_mega_auto', true ) && gami_term_children( $item->object_id, 1 ) ) {
				$item->gami_cat_panel = (int) $item->object_id;
				$item->classes        = array_merge( (array) $item->classes, array( 'menu-item-has-children', 'mega-menu', 'mega-menu--auto', 'mega-menu--cat' ) );
			}
			continue;
		}

		$has_cat_kids = (bool) array_filter( $kids, $is_cat );
		$manual_mega  = (bool) get_post_meta( $id, '_gami_mega', true );
		if ( ! $has_cat_kids && ! $manual_mega ) {
			continue;
		}
		if ( ! $manual_mega ) {
			$item->classes = array_merge( (array) $item->classes, array( 'mega-menu', 'mega-cols-' . min( 5, max( 2, count( $kids ) ) ) ) );
		}
		// Each category column lists its own sub-categories, unless it already has sub-items.
		$deep = false;
		foreach ( $kids as $kid ) {
			if ( ! empty( $children[ (int) $kid->ID ] ) ) {
				$deep = true;
			} elseif ( $is_cat( $kid ) && gami_term_children( $kid->object_id, 1 ) ) {
				$kid->gami_cat_links = (int) $kid->object_id;
				$kid->classes        = array_merge( (array) $kid->classes, array( 'menu-item-has-children' ) );
				$deep                = true;
			}
		}

		// No deeper levels: a clean grid of links instead of empty column headings.
		if ( ! $deep ) {
			$item->classes = array_merge( (array) $item->classes, array( 'mega-menu--links', gami_option( 'mega_photos' ) ? 'mega-menu--photos' : 'mega-menu--plain' ) );
			foreach ( $kids as $kid ) {
				if ( $is_cat( $kid ) ) {
					$kid->gami_link_term = (int) $kid->object_id;
				}
			}
			// A heading row linking to the whole category.
			if ( $is_cat( $item ) ) {
				$extra[ $id ] = gami_mega_all_row( $item );
			}
		}
	}

	if ( empty( $extra ) ) {
		return $items;
	}
	// Put each heading row first among its parent's sub-items.
	$out = array();
	foreach ( $items as $item ) {
		$out[] = $item;
		if ( isset( $extra[ (int) $item->ID ] ) ) {
			$out[] = $extra[ (int) $item->ID ];
		}
	}
	return $out;
}

/**
 * Menu item for "All {category} · N products →" at the top of a links panel.
 */
function gami_mega_all_row( $parent ) {
	$term  = get_term( (int) $parent->object_id, gami_catalog_taxonomy() );
	$row   = clone $parent;
	$fake  = 900000000 + (int) $parent->ID;
	$title = $term && ! is_wp_error( $term ) ? $term->name : $parent->title;

	$row->ID               = $fake;
	$row->db_id            = $fake;
	$row->menu_item_parent = (string) $parent->ID;
	/* translators: %s: category name */
	$row->title            = sprintf( __( 'All %s', 'gami-surgical' ), $title );
	$row->attr_title       = '';
	$row->description      = '';
	$row->classes          = array( 'menu-item', 'mega-allrow' );
	$row->current          = false;
	$row->current_item_ancestor = false;
	$row->current_item_parent   = false;
	$row->gami_cat_panel   = 0;
	$row->gami_cat_links   = 0;
	$row->gami_allrow_term = $term && ! is_wp_error( $term ) ? (int) $term->term_id : 0;
	return $row;
}
add_filter( 'wp_nav_menu_objects', 'gami_mega_prepare_items', 10, 2 );

/**
 * Links to a category's sub-categories (inside a mega column).
 */
function gami_mega_cat_links( $term_id, $limit = 8 ) {
	$terms = gami_term_children( $term_id );
	if ( ! $terms ) {
		return '';
	}
	$html = '<ul class="sub-menu">';
	foreach ( array_slice( $terms, 0, $limit ) as $t ) {
		$html .= sprintf( '<li class="menu-item"><a href="%1$s">%2$s</a></li>', esc_url( get_term_link( $t ) ), esc_html( $t->name ) );
	}
	$parent = get_term( $term_id, gami_catalog_taxonomy() );
	if ( $parent && ! is_wp_error( $parent ) ) {
		$html .= sprintf(
			'<li class="menu-item mega-viewall"><a href="%1$s">%2$s</a></li>',
			esc_url( get_term_link( $parent ) ),
			/* translators: %s category */
			esc_html( sprintf( __( 'View all %s', 'gami-surgical' ), $parent->name ) )
		);
	}
	return $html . '</ul>';
}

/**
 * Mega panel for one category: columns when its sub-categories have their own
 * sub-categories, otherwise image tiles.
 */
function gami_mega_cat_panel( $term_id ) {
	$parent = get_term( $term_id, gami_catalog_taxonomy() );
	$kids   = gami_term_children( $term_id );
	if ( ! $parent || is_wp_error( $parent ) || ! $kids ) {
		return '';
	}
	$deep = false;
	foreach ( $kids as $kid ) {
		if ( gami_term_children( $kid->term_id, 1 ) ) {
			$deep = true;
			break;
		}
	}

	$html = '<ul class="sub-menu ' . ( $deep ? 'mega-auto' : 'mega-tiles' ) . '">';
	foreach ( $kids as $kid ) {
		$count = gami_count_label( $kid );
		$label = sprintf(
			'%1$s<span class="mega-heading__text">%2$s%3$s</span>',
			gami_mega_thumb( $kid ),
			esc_html( $kid->name ),
			$count ? '<span class="mega-count">' . esc_html( $count ) . '</span>' : ''
		);
		if ( $deep ) {
			$sub   = gami_term_children( $kid->term_id, 1 ) ? gami_mega_cat_links( $kid->term_id, 6 ) : '';
			$html .= '<li class="menu-item mega-col' . ( $sub ? ' menu-item-has-children' : '' ) . '">';
			$html .= sprintf( '<a href="%1$s" class="mega-heading">%2$s</a>', esc_url( get_term_link( $kid ) ), $label );
			$html .= $sub . '</li>';
		} else {
			$html .= sprintf( '<li class="menu-item mega-tile"><a href="%1$s">%2$s</a></li>', esc_url( get_term_link( $kid ) ), $label );
		}
	}
	$html .= sprintf(
		'<li class="menu-item mega-viewall mega-viewall--panel"><a href="%1$s">%2$s</a></li>',
		esc_url( get_term_link( $parent ) ),
		/* translators: %s category */
		esc_html( sprintf( __( 'View all %s', 'gami-surgical' ), $parent->name ) )
	);
	$html .= gami_mega_promo_html();
	return $html . '</ul>';
}

function gami_mega_promo_html() {
	if ( ! gami_option( 'mega_promo_show' ) ) {
		return '';
	}
	$html  = '<li class="menu-item mega-promo"><div class="mega-promo__card">';
	$html .= '<p class="mega-promo__title">' . esc_html( gami_option( 'mega_promo_title' ) ) . '</p>';
	if ( gami_option( 'mega_promo_text' ) ) {
		$html .= '<p class="mega-promo__text">' . esc_html( gami_option( 'mega_promo_text' ) ) . '</p>';
	}
	$html .= sprintf( '<a class="button button--primary mega-promo__button" href="%1$s">%2$s</a>', esc_url( gami_header_cta_url() ), esc_html( gami_option( 'mega_promo_button' ) ) );
	$html .= sprintf( '<a class="mega-promo__all" href="%1$s">%2$s</a>', esc_url( gami_catalog_url() ), esc_html__( 'Browse all products', 'gami-surgical' ) );
	return $html . '</div></li>';
}

/**
 * Is this item inside a mega menu?
 */
function gami_mega_ancestor( $item ) {
	$parent = (int) $item->menu_item_parent;
	$guard  = 0;
	while ( $parent && $guard < 5 ) {
		if ( get_post_meta( $parent, '_gami_mega', true ) ) {
			return true;
		}
		$parent = (int) get_post_meta( $parent, '_menu_item_menu_item_parent', true );
		++$guard;
	}
	return false;
}

/**
 * Columns built from catalogue categories, plus the quote panel.
 * Same markup as a manual mega menu, so it styles and collapses identically.
 * Empty categories are included so the menu works before products are added.
 */
function gami_mega_auto_panel() {
	$parents = gami_catalog_terms( array( 'hide_empty' => false ) );
	if ( ! $parents ) {
		return '';
	}
	$html = '<ul class="sub-menu mega-auto">';
	foreach ( $parents as $parent ) {
		$links = gami_term_children( $parent->term_id, 1 ) ? gami_mega_cat_links( $parent->term_id, 6 ) : '';
		$count = gami_count_label( $parent );
		$html .= '<li class="menu-item mega-col' . ( $links ? ' menu-item-has-children' : '' ) . '">';
		$html .= sprintf(
			'<a href="%1$s" class="mega-heading">%2$s<span class="mega-heading__text">%3$s%4$s</span></a>',
			esc_url( get_term_link( $parent ) ),
			gami_mega_thumb( $parent ),
			esc_html( $parent->name ),
			$count ? '<span class="mega-count">' . esc_html( $count ) . '</span>' : ''
		);
		$html .= $links . '</li>';
	}
	$html .= gami_mega_promo_html();
	return $html . '</ul>';
}
