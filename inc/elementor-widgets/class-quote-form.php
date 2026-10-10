<?php
/**
 * Elementor widget: Quote form.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Gami_Widget_Quote_Form extends \Elementor\Widget_Base {

	public function get_name() {
		return 'gami-quote-form';
	}

	public function get_title() {
		return __( 'Quote form', 'gami-surgical' );
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_categories() {
		return array( 'gami-surgical' );
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_form',
			array( 'label' => __( 'Form', 'gami-surgical' ) )
		);

		$this->add_control(
			'title',
			array(
				'label'   => __( 'Heading', 'gami-surgical' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => __( 'Request a quote', 'gami-surgical' ),
			)
		);

		$this->add_control(
			'show_note',
			array(
				'label'        => __( 'Show ordering note', 'gami-surgical' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'info',
			array(
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Requests are emailed to the sales address set in Appearance > Customize > Gami Surgical > Contact details.', 'gami-surgical' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$instrument = is_singular( 'instrument' ) ? get_the_ID() : 0;
		echo gami_quote_form( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'instrument_id' => $instrument,
				'title'         => $s['title'],
				'show_note'     => 'yes' === $s['show_note'],
			)
		);
	}
}
