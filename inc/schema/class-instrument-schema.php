<?php
/**
 * Yoast schema piece: Product for instruments.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;

class Gami_Instrument_Schema extends Abstract_Schema_Piece {

	public function is_needed() {
		return is_singular( 'instrument' );
	}

	public function generate() {
		$post_id   = $this->context->id;
		$canonical = $this->context->canonical;

		$data = array(
			'@type'            => 'Product',
			'@id'              => $canonical . '#product',
			'name'             => get_the_title( $post_id ),
			'url'              => $canonical,
			'mainEntityOfPage' => array( '@id' => $this->context->main_schema_id ),
			'brand'            => array(
				'@type' => 'Brand',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		$desc = get_the_excerpt( $post_id );
		if ( $desc ) {
			$data['description'] = wp_strip_all_tags( $desc );
		}

		$sku = get_post_meta( $post_id, '_gami_catalogue_no', true );
		if ( $sku ) {
			$data['sku'] = $sku;
			$data['mpn'] = $sku;
		}

		$material = get_post_meta( $post_id, '_gami_material', true );
		if ( $material ) {
			$data['material'] = $material;
		}

		if ( has_post_thumbnail( $post_id ) ) {
			$data['image'] = array( '@id' => $canonical . '#primaryimage' );
		}

		if ( ! empty( $this->context->site_represents ) && 'company' === $this->context->site_represents ) {
			$data['manufacturer'] = array( '@id' => $this->context->site_url . '#organization' );
		}

		$terms = get_the_terms( $post_id, 'instrument_category' );
		if ( $terms && ! is_wp_error( $terms ) ) {
			$data['category'] = $terms[0]->name;
		}

		$props = array();
		foreach ( array( 'sizes', 'finish', 'usage' ) as $key ) {
			$value = get_post_meta( $post_id, '_gami_' . $key, true );
			if ( $value ) {
				$fields  = gami_instrument_fields();
				$props[] = array(
					'@type' => 'PropertyValue',
					'name'  => $fields[ $key ],
					'value' => $value,
				);
			}
		}
		if ( $props ) {
			$data['additionalProperty'] = $props;
		}

		return apply_filters( 'gami_instrument_schema', $data, $post_id );
	}
}
