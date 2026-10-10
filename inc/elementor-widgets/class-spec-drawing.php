<?php
/**
 * Elementor widget: Technical spec drawing (the hero drawing as a reusable block).
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gami_Widget_Spec_Drawing extends \Elementor\Widget_Base {

	public function get_name() {
		return 'gami-spec-drawing';
	}

	public function get_title() {
		return __( 'Spec drawing', 'gami-surgical' );
	}

	public function get_icon() {
		return 'eicon-image-box';
	}

	public function get_categories() {
		return array( 'gami-surgical' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_drawing',
			array( 'label' => __( 'Title block', 'gami-surgical' ) )
		);

		$fields = array(
			'part'     => array( __( 'Instrument', 'gami-surgical' ), gami_option( 'hero_part' ) ),
			'part_no'  => array( __( 'Catalogue no.', 'gami-surgical' ), gami_option( 'hero_part_no' ) ),
			'size'     => array( __( 'Overall length', 'gami-surgical' ), gami_option( 'hero_size' ) ),
			'material' => array( __( 'Material', 'gami-surgical' ), 'AISI 420 stainless' ),
		);
		foreach ( $fields as $key => $f ) {
			$this->add_control(
				$key,
				array(
					'label'   => $f[0],
					'type'    => \Elementor\Controls_Manager::TEXT,
					'default' => $f[1],
				)
			);
		}

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		get_template_part(
			'template-parts/spec-drawing',
			null,
			array(
				'part'     => $s['part'],
				'part_no'  => $s['part_no'],
				'size'     => $s['size'],
				'material' => $s['material'],
			)
		);
	}
}
