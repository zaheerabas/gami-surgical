<?php
/**
 * Elementor widget: Instrument grid.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gami_Widget_Instrument_Grid extends \Elementor\Widget_Base {

	public function get_name() {
		return 'gami-instrument-grid';
	}

	public function get_title() {
		return gami_wc_active() ? __( 'Product grid', 'gami-surgical' ) : __( 'Instrument grid', 'gami-surgical' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return array( 'gami-surgical' );
	}

	public function get_keywords() {
		return array( 'instrument', 'catalogue', 'products', 'surgical' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_query',
			array( 'label' => __( 'Instruments', 'gami-surgical' ) )
		);

		$terms   = gami_catalog_terms( array( 'parent' => '' ) );
		$options = array();
		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		$this->add_control(
			'category',
			array(
				'label'       => __( 'Categories', 'gami-surgical' ),
				'type'        => \Elementor\Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => $options,
				'description' => __( 'Leave empty to show all.', 'gami-surgical' ),
			)
		);

		$this->add_control(
			'count',
			array(
				'label'   => __( 'Number to show', 'gami-surgical' ),
				'type'    => \Elementor\Controls_Manager::NUMBER,
				'min'     => 1,
				'max'     => 48,
				'default' => 8,
			)
		);

		$this->add_control(
			'columns',
			array(
				'label'   => __( 'Columns', 'gami-surgical' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '4',
				'options' => array(
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				),
			)
		);

		$this->add_control(
			'orderby',
			array(
				'label'   => __( 'Order', 'gami-surgical' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'menu_order',
				'options' => array(
					'menu_order' => __( 'Manual order', 'gami-surgical' ),
					'date'       => __( 'Newest first', 'gami-surgical' ),
					'title'      => __( 'Name', 'gami-surgical' ),
					'rand'       => __( 'Random', 'gami-surgical' ),
				),
			)
		);

		if ( gami_wc_active() ) {
			$this->add_control(
				'featured',
				array(
					'label'        => __( 'Featured products only', 'gami-surgical' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'return_value' => 'yes',
				)
			);
		}

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => __( 'Cards', 'gami-surgical' ),
				'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'Card background', 'gami-surgical' ),
				'type'      => \Elementor\Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .instrument-card, {{WRAPPER}} ul.products li.product' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			\Elementor\Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => __( 'Name typography', 'gami-surgical' ),
				'selector' => '{{WRAPPER}} .instrument-card__title, {{WRAPPER}} .woocommerce-loop-product__title',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		gami_render_instrument_grid(
			array(
				'count'    => isset( $s['count'] ) ? $s['count'] : 8,
				'category' => isset( $s['category'] ) ? $s['category'] : array(),
				'columns'  => isset( $s['columns'] ) ? $s['columns'] : 4,
				'orderby'  => isset( $s['orderby'] ) ? $s['orderby'] : 'menu_order',
				'featured' => ! empty( $s['featured'] ) && 'yes' === $s['featured'],
			)
		);
	}
}
