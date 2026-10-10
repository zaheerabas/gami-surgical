<?php
/**
 * Instrument catalogue: post type, categories and spec fields.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Spec fields shown on every instrument or product. Filterable.
 * Products use the WooCommerce SKU as the catalogue number, so that field
 * is left out of the product editor (but still shown in the spec table).
 *
 * @param string $post_type 'instrument', 'product' or '' for the full list.
 */
function gami_instrument_fields( $post_type = '' ) {
	return apply_filters(
		'gami_instrument_fields',
		array(
			'catalogue_no' => __( 'Catalogue no.', 'gami-surgical' ),
			'sizes'        => __( 'Sizes', 'gami-surgical' ),
			'material'     => __( 'Material', 'gami-surgical' ),
			'finish'       => __( 'Finish', 'gami-surgical' ),
			'usage'        => __( 'Usage', 'gami-surgical' ),
			'packing'      => __( 'Packing', 'gami-surgical' ),
			'moq'          => __( 'Minimum order', 'gami-surgical' ),
		),
		$post_type
	);
}

/**
 * Editable spec fields for a post type (products take the catalogue no. from SKU).
 */
function gami_editable_spec_fields( $post_type ) {
	$fields = gami_instrument_fields( $post_type );
	if ( 'product' === $post_type ) {
		unset( $fields['catalogue_no'] );
	}
	return $fields;
}

/**
 * Example values shown as placeholders in the editor.
 */
function gami_spec_hints() {
	return array(
		'catalogue_no' => 'GS-1021',
		'sizes'        => '12.5 cm, 14 cm, 16 cm',
		'material'     => 'Stainless steel (AISI 420)',
		'finish'       => 'Satin / mirror',
		'usage'        => 'Reusable, autoclavable',
		'packing'      => '1 piece per poly bag, 10 per box',
		'moq'          => '50 pieces per size',
	);
}

function gami_register_instruments() {
	foreach ( array_keys( gami_instrument_fields() ) as $key ) {
		foreach ( array( 'instrument', 'product' ) as $meta_type ) {
			register_post_meta(
				$meta_type,
				'_gami_' . $key,
				array(
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	if ( ! gami_use_instruments() ) {
		return;
	}

	register_post_type(
		'instrument',
		array(
			'labels'        => array(
				'name'               => __( 'Instruments', 'gami-surgical' ),
				'singular_name'      => __( 'Instrument', 'gami-surgical' ),
				'add_new'            => __( 'Add instrument', 'gami-surgical' ),
				'add_new_item'       => __( 'Add new instrument', 'gami-surgical' ),
				'edit_item'          => __( 'Edit instrument', 'gami-surgical' ),
				'new_item'           => __( 'New instrument', 'gami-surgical' ),
				'view_item'          => __( 'View instrument', 'gami-surgical' ),
				'search_items'       => __( 'Search instruments', 'gami-surgical' ),
				'not_found'          => __( 'No instruments found', 'gami-surgical' ),
				'not_found_in_trash' => __( 'No instruments in the bin', 'gami-surgical' ),
				'all_items'          => __( 'All instruments', 'gami-surgical' ),
				'menu_name'          => __( 'Instruments', 'gami-surgical' ),
			),
			'public'        => true,
			'has_archive'   => 'instruments',
			'rewrite'       => array(
				'slug'       => 'instrument',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-admin-tools',
			'menu_position' => 20,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'revisions', 'custom-fields' ),
			'show_in_rest'  => true,
			'taxonomies'    => array( 'instrument_category' ),
		)
	);

	register_taxonomy(
		'instrument_category',
		'instrument',
		array(
			'labels'            => array(
				'name'          => __( 'Instrument categories', 'gami-surgical' ),
				'singular_name' => __( 'Instrument category', 'gami-surgical' ),
				'menu_name'     => __( 'Categories', 'gami-surgical' ),
				'all_items'     => __( 'All categories', 'gami-surgical' ),
				'edit_item'     => __( 'Edit category', 'gami-surgical' ),
				'add_new_item'  => __( 'Add new category', 'gami-surgical' ),
			),
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array(
				'slug'         => 'instruments/category',
				'with_front'   => false,
				'hierarchical' => true,
			),
		)
	);
}
add_action( 'init', 'gami_register_instruments' );

function gami_flush_rewrites() {
	$counts = wp_count_posts( 'instrument' );
	if ( $counts && ! empty( $counts->publish ) ) {
		update_option( 'gami_has_instruments', 1 );
	}
	gami_register_instruments();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'gami_flush_rewrites' );

/**
 * Meta box with spec fields.
 */
function gami_add_instrument_metabox() {
	add_meta_box(
		'gami_instrument_specs',
		__( 'Specifications', 'gami-surgical' ),
		'gami_render_instrument_metabox',
		'instrument',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'gami_add_instrument_metabox' );

function gami_render_instrument_metabox( $post ) {
	wp_nonce_field( 'gami_save_specs', 'gami_specs_nonce' );
	$hints = gami_spec_hints();
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( gami_editable_spec_fields( 'instrument' ) as $key => $label ) {
		$value = get_post_meta( $post->ID, '_gami_' . $key, true );
		printf(
			'<tr><th scope="row"><label for="gami_%1$s">%2$s</label></th><td><input type="text" class="regular-text" id="gami_%1$s" name="gami_specs[%1$s]" value="%3$s" placeholder="%4$s"></td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $value ),
			esc_attr( isset( $hints[ $key ] ) ? $hints[ $key ] : '' )
		);
	}
	echo '</tbody></table>';
}

function gami_save_instrument_specs( $post_id ) {
	if ( ! isset( $_POST['gami_specs_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['gami_specs_nonce'] ) ), 'gami_save_specs' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$input = isset( $_POST['gami_specs'] ) ? (array) wp_unslash( $_POST['gami_specs'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	foreach ( array_keys( gami_editable_spec_fields( 'instrument' ) ) as $key ) {
		$value = isset( $input[ $key ] ) ? sanitize_text_field( $input[ $key ] ) : '';
		if ( '' === $value ) {
			delete_post_meta( $post_id, '_gami_' . $key );
		} else {
			update_post_meta( $post_id, '_gami_' . $key, $value );
		}
	}
	if ( 'publish' === get_post_status( $post_id ) && ! get_option( 'gami_has_instruments' ) ) {
		update_option( 'gami_has_instruments', 1 );
	}
}
add_action( 'save_post_instrument', 'gami_save_instrument_specs' );

/**
 * Catalogue number column in the admin list.
 */
function gami_instrument_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'cb' === $key ) {
			$new['gami_thumb'] = '';
		}
		if ( 'title' === $key ) {
			$new['gami_catalogue_no'] = __( 'Catalogue no.', 'gami-surgical' );
		}
	}
	return $new;
}
add_filter( 'manage_instrument_posts_columns', 'gami_instrument_columns' );

function gami_instrument_column_content( $column, $post_id ) {
	if ( 'gami_catalogue_no' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_gami_catalogue_no', true ) );
	}
	if ( 'gami_thumb' === $column ) {
		echo get_the_post_thumbnail( $post_id, array( 40, 40 ) );
	}
}
add_action( 'manage_instrument_posts_custom_column', 'gami_instrument_column_content', 10, 2 );

/**
 * Let site search match catalogue numbers.
 */
function gami_search_catalogue_numbers( $search, $query ) {
	global $wpdb;
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return $search;
	}
	$term = $query->get( 's' );
	if ( ! $term || ! $search ) {
		return $search;
	}
	$like = '%' . $wpdb->esc_like( $term ) . '%';
	$ids  = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('_gami_catalogue_no','_sku') AND meta_value LIKE %s", $like ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	if ( $ids ) {
		$ids    = implode( ',', array_map( 'absint', $ids ) );
		$search = " AND ( {$wpdb->posts}.ID IN ({$ids}) OR ( 1=1 {$search} ) ) ";
	}
	return $search;
}
add_filter( 'posts_search', 'gami_search_catalogue_numbers', 10, 2 );

/**
 * Catalogue archive: more items per page, menu order first.
 */
function gami_instrument_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'instrument' ) || $query->is_tax( 'instrument_category' ) ) {
		$query->set( 'posts_per_page', 24 );
		$query->set(
			'orderby',
			array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			)
		);
	}
}
add_action( 'pre_get_posts', 'gami_instrument_archive_query' );
