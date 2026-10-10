<?php
/**
 * Technical drawing of a hemostat with a title block, used on the homepage hero
 * and by the Spec drawing Elementor widget.
 *
 * @package Gami_Surgical
 */

$args = wp_parse_args(
	isset( $args ) ? $args : array(),
	array(
		'part'     => gami_option( 'hero_part' ),
		'part_no'  => gami_option( 'hero_part_no' ),
		'size'     => gami_option( 'hero_size' ),
		'material' => __( 'AISI 420 stainless', 'gami-surgical' ),
		'animate'  => false,
	)
);
$gami_spec_id = wp_unique_id( 'spec-title-' );
?>
<figure class="spec-sheet<?php echo $args['animate'] ? ' spec-sheet--animate' : ''; ?>">
	<svg class="spec-sheet__svg" viewBox="0 20 640 260" role="img" aria-labelledby="<?php echo esc_attr( $gami_spec_id ); ?>">
		<title id="<?php echo esc_attr( $gami_spec_id ); ?>"><?php echo esc_html( sprintf( /* translators: %s instrument */ __( 'Technical drawing of a %s', 'gami-surgical' ), $args['part'] ) ); ?></title>

		<g class="spec-sheet__instrument" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<!-- finger rings -->
			<ellipse class="draw" pathLength="1" cx="62" cy="92" rx="30" ry="24"/>
			<ellipse class="draw" pathLength="1" cx="62" cy="200" rx="30" ry="24"/>
			<!-- shanks to box joint -->
			<path class="draw" pathLength="1" d="M90 100 C 160 116, 240 132, 330 146"/>
			<path class="draw" pathLength="1" d="M90 192 C 160 176, 240 160, 330 154"/>
			<!-- ratchet -->
			<path class="draw" pathLength="1" d="M110 106 l4 7 l4 -5 l4 7 l4 -5 l4 7 l4 -6"/>
			<path class="draw" pathLength="1" d="M110 194 l4 -7 l4 5 l4 -7 l4 5 l4 -7 l4 6"/>
			<!-- box joint -->
			<rect class="draw" pathLength="1" x="318" y="138" width="26" height="24" rx="2"/>
			<circle class="draw" pathLength="1" cx="331" cy="150" r="3.5"/>
			<!-- curved jaws -->
			<path class="draw" pathLength="1" d="M344 144 C 420 136, 500 134, 560 140 C 580 142, 592 146, 598 152"/>
			<path class="draw" pathLength="1" d="M344 156 C 420 160, 500 158, 560 154 C 580 152, 592 151, 598 152"/>
			<!-- serrations -->
			<path class="draw spec-sheet__fine" pathLength="1" d="M420 142 v14 M434 141 v15 M448 140 v16 M462 140 v16 M476 140 v16 M490 140 v15 M504 140 v15 M518 140 v15 M532 141 v14 M546 141 v13"/>
		</g>

		<g class="spec-sheet__dims" fill="none" stroke="currentColor" stroke-width="0.9">
			<!-- overall length -->
			<path d="M32 236 V258 M598 160 V258"/>
			<path d="M32 250 H598" marker-start="url(#gs-arrow)" marker-end="url(#gs-arrow)"/>
			<!-- jaw length -->
			<path d="M344 120 V88 M598 140 V88"/>
			<path d="M344 96 H598" marker-start="url(#gs-arrow)" marker-end="url(#gs-arrow)"/>
			<!-- leaders -->
			<path d="M331 138 L300 60 H262"/>
			<path d="M124 102 L160 40 H232"/>
		</g>

		<g class="spec-sheet__labels" fill="currentColor">
			<text x="315" y="246" text-anchor="middle"><?php echo esc_html( $args['size'] ); ?></text>
			<text x="471" y="90" text-anchor="middle"><?php esc_html_e( 'Serrated jaws', 'gami-surgical' ); ?></text>
			<text x="258" y="56" text-anchor="end"><?php esc_html_e( 'Box joint', 'gami-surgical' ); ?></text>
			<text x="236" y="36"><?php esc_html_e( 'Three-step ratchet', 'gami-surgical' ); ?></text>
		</g>

		<defs>
			<marker id="gs-arrow" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
				<path d="M0 1 L10 5 L0 9 z" fill="currentColor"/>
			</marker>
		</defs>
	</svg>

	<figcaption class="title-block">
		<dl>
			<div><dt><?php esc_html_e( 'Instrument', 'gami-surgical' ); ?></dt><dd><?php echo esc_html( $args['part'] ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Cat. no.', 'gami-surgical' ); ?></dt><dd><?php echo esc_html( $args['part_no'] ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Material', 'gami-surgical' ); ?></dt><dd><?php echo esc_html( $args['material'] ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Scale', 'gami-surgical' ); ?></dt><dd><?php esc_html_e( 'Not to scale', 'gami-surgical' ); ?></dd></div>
		</dl>
	</figcaption>
</figure>
