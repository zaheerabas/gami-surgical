<?php
/**
 * Helpers used across templates.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is the current singular page built with Elementor?
 */
function gami_is_elementor_page( $post_id = null ) {
	if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}
	if ( null === $post_id ) {
		if ( ! is_singular() ) {
			return false;
		}
		$post_id = get_queried_object_id();
	}
	$document = \Elementor\Plugin::$instance->documents->get( $post_id );
	return $document && $document->is_built_with_elementor();
}

/**
 * Is WooCommerce active?
 */
function gami_wc_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Should the theme's own Instruments post type be registered?
 * Off when WooCommerce runs the catalogue, unless instruments already exist
 * (so nothing disappears) or a developer forces it with the filter.
 */
function gami_use_instruments() {
	$use = ! gami_wc_active() || (bool) get_option( 'gami_has_instruments' );
	return (bool) apply_filters( 'gami_use_instruments', $use );
}

/**
 * Post type, taxonomy and URL of the main catalogue.
 */
function gami_catalog_post_type() {
	return gami_wc_active() ? 'product' : 'instrument';
}

function gami_catalog_taxonomy() {
	return gami_wc_active() ? 'product_cat' : 'instrument_category';
}

function gami_catalog_url() {
	if ( gami_wc_active() ) {
		$shop = wc_get_page_permalink( 'shop' );
		return $shop ? $shop : home_url( '/' );
	}
	$url = get_post_type_archive_link( 'instrument' );
	return $url ? $url : home_url( '/' );
}

/**
 * Top-level catalogue categories (skips WooCommerce's "Uncategorized").
 */
function gami_catalog_terms( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'taxonomy'   => gami_catalog_taxonomy(),
			'parent'     => 0,
			'hide_empty' => false,
		)
	);
	if ( 'product_cat' === $args['taxonomy'] ) {
		// Follow the drag-and-drop order from Products > Categories.
		if ( ! isset( $args['orderby'] ) ) {
			$args['orderby'] = 'menu_order';
		}
		// Hide WooCommerce's "Uncategorized" only. Many shops rename that default
		// category (e.g. to "Surgical Instruments"), and then it must show.
		$hide = gami_uncategorized_term_id();
		if ( $hide ) {
			$args['exclude'] = array( $hide );
		}
	}
	$terms = get_terms( $args );
	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Catalogue number: the WooCommerce SKU for products, the spec field for instruments.
 */
function gami_catalogue_no( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( 'product' === get_post_type( $post_id ) && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $post_id );
		return $product ? $product->get_sku() : '';
	}
	return get_post_meta( $post_id, '_gami_catalogue_no', true );
}

/**
 * Get a theme option from the Customizer with its default.
 */
function gami_option( $key ) {
	$defaults = gami_customizer_defaults();
	$default  = isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
	return get_theme_mod( 'gami_' . $key, $default );
}

function gami_posted_on() {
	printf(
		'<time class="entry-date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
}

function gami_pagination() {
	the_posts_pagination(
		array(
			'mid_size'  => 1,
			'prev_text' => __( 'Previous', 'gami-surgical' ),
			'next_text' => __( 'Next', 'gami-surgical' ),
		)
	);
}

/**
 * Spec fields for an instrument, labelled, skipping empty values.
 */
function gami_instrument_specs( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	$specs   = array();
	foreach ( gami_instrument_fields( get_post_type( $post_id ) ) as $key => $label ) {
		$value = 'catalogue_no' === $key ? gami_catalogue_no( $post_id ) : get_post_meta( $post_id, '_gami_' . $key, true );
		if ( '' !== $value && null !== $value ) {
			$specs[ $key ] = array(
				'label' => $label,
				'value' => $value,
			);
		}
	}
	return $specs;
}

/**
 * Spec table for an instrument or product.
 */
function gami_spec_table( $post_id = null ) {
	$specs = gami_instrument_specs( $post_id );
	if ( ! $specs ) {
		return;
	}
	echo '<table class="spec-table"><tbody>';
	foreach ( $specs as $spec ) {
		printf( '<tr><th scope="row">%1$s</th><td>%2$s</td></tr>', esc_html( $spec['label'] ), esc_html( $spec['value'] ) );
	}
	echo '</tbody></table>';
}

/**
 * Inline SVG placeholder for instruments without a photo.
 */
function gami_placeholder_svg() {
	return '<svg class="placeholder-svg" viewBox="0 0 200 120" aria-hidden="true" focusable="false"><g fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="30" cy="42" r="14"/><circle cx="30" cy="78" r="14"/><path d="M43 47 L100 60 L188 58 M43 73 L100 60 L188 62"/><circle cx="100" cy="60" r="4"/></g></svg>';
}

/**
 * Contact link helpers.
 */
/**
 * Phone link. $key: 'phone' (main) or 'phone2' (second number).
 * Adds the number's label (e.g. "USA") when one is set.
 */
function gami_phone_link( $key = 'phone' ) {
	$phone = gami_option( $key );
	if ( ! $phone ) {
		return '';
	}
	$label = gami_option( $key . '_label' );
	return sprintf(
		'<a href="tel:%1$s">%2$s</a>%3$s',
		esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ),
		esc_html( $phone ),
		$label ? ' <span class="phone-label">' . esc_html( $label ) . '</span>' : ''
	);
}

function gami_email_link() {
	$email = gami_option( 'email' );
	if ( ! $email ) {
		return '';
	}
	return sprintf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( antispambot( $email ) ) );
}

function gami_whatsapp_url( $message = '' ) {
	$number = preg_replace( '/[^0-9]/', '', gami_option( 'whatsapp' ) );
	if ( ! $number ) {
		return '';
	}
	$url = 'https://wa.me/' . $number;
	if ( $message ) {
		$url .= '?text=' . rawurlencode( $message );
	}
	return $url;
}

/**
 * URL of the page used for quote requests.
 */
function gami_quote_url( $instrument_id = 0 ) {
	if ( function_exists( 'gami_inquiry_page_url' ) ) {
		return gami_inquiry_page_url( $instrument_id ? array( 'add' => $instrument_id ) : array() );
	}
	$page_id = absint( gami_option( 'quote_page' ) );
	return $page_id ? get_permalink( $page_id ) : home_url( '/' );
}

/**
 * Logo: the Customizer logo, else the logo bundled with the theme.
 */
function gami_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	printf(
		'<a class="custom-logo-link" href="%1$s" rel="home"><img class="custom-logo" src="%2$s" alt="%3$s" width="520" height="322"></a>',
		esc_url( home_url( '/' ) ),
		esc_url( GAMI_URI . '/assets/images/logo.png' ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

/**
 * Fallback for the primary menu when none is assigned.
 */
function gami_menu_fallback() {
	echo '<ul id="primary-menu" class="menu primary-menu">';
	echo '<li class="menu-item' . ( is_front_page() ? ' current-menu-item' : '' ) . '"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'gami-surgical' ) . '</a></li>';
	// Products opens a mega menu of catalogue categories until a real menu is assigned.
	$panel = function_exists( 'gami_mega_auto_panel' ) ? gami_mega_auto_panel() : '';
	printf(
		'<li class="menu-item%1$s"><a href="%2$s">%3$s</a>%4$s</li>',
		$panel ? ' menu-item-has-children mega-menu mega-menu--auto' : '',
		esc_url( gami_catalog_url() ),
		esc_html__( 'Products', 'gami-surgical' ),
		$panel // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in gami_mega_auto_panel().
	);
	$exclude = array( (int) get_option( 'page_on_front' ) );
	if ( gami_wc_active() ) {
		foreach ( array( 'shop', 'cart', 'checkout', 'myaccount' ) as $wc_page ) {
			$exclude[] = wc_get_page_id( $wc_page );
		}
	}
	wp_list_pages(
		array(
			'title_li' => '',
			'depth'    => 1,
			'number'   => 5,
			'exclude'  => implode( ',', array_filter( array_map( 'absint', $exclude ) ) ),
		)
	);
	echo '</ul>';
}

/* -------------------------------------------------------------------------
 * Header and footer helpers
 * ---------------------------------------------------------------------- */

/**
 * Container class for the header or footer, from the width setting.
 */
function gami_wrap_class( $area ) {
	return 'full' === gami_option( $area . '_width' ) ? 'container container--full' : 'container';
}

/**
 * Header button URL: custom link, else the quote page.
 */
function gami_header_cta_url() {
	$url = gami_option( 'header_cta_url' );
	return $url ? $url : gami_quote_url();
}

/**
 * Copyright text with {year} and {site} replaced.
 */
function gami_copyright_text() {
	$text = gami_option( 'copyright' );
	return strtr(
		(string) $text,
		array(
			'{year}' => wp_date( 'Y' ),
			'{site}' => get_bloginfo( 'name' ),
		)
	);
}

/**
 * Supported social networks with their icons (24x24, currentColor).
 */
function gami_social_networks() {
	return array(
		'facebook'  => array(
			'label' => __( 'Facebook', 'gami-surgical' ),
			'path'  => 'M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.3-1.5 1.5-1.5h1.6V4.4a21 21 0 0 0-2.3-.1c-2.3 0-3.8 1.4-3.8 3.9v2.3H8v3h2.5V21h3Z',
		),
		'instagram' => array(
			'label' => __( 'Instagram', 'gami-surgical' ),
			'path'  => 'M12 7.3a4.7 4.7 0 1 0 0 9.4 4.7 4.7 0 0 0 0-9.4Zm0 7.7a3 3 0 1 1 0-6 3 3 0 0 1 0 6Zm4.9-8.9a1.1 1.1 0 1 0 0 2.2 1.1 1.1 0 0 0 0-2.2ZM12 4.6c2.4 0 2.7 0 3.6.1 2.4.1 3.6 1.3 3.7 3.7.1.9.1 1.2.1 3.6s0 2.7-.1 3.6c-.1 2.4-1.3 3.6-3.7 3.7-.9.1-1.2.1-3.6.1s-2.7 0-3.6-.1c-2.4-.1-3.6-1.3-3.7-3.7-.1-.9-.1-1.2-.1-3.6s0-2.7.1-3.6C4.8 6 6 4.8 8.4 4.7c.9-.1 1.2-.1 3.6-.1ZM12 3c-2.4 0-2.8 0-3.7.1C5 3.2 3.2 5 3.1 8.3 3 9.2 3 9.6 3 12s0 2.8.1 3.7c.1 3.3 1.9 5.1 5.2 5.2.9.1 1.3.1 3.7.1s2.8 0 3.7-.1c3.3-.1 5.1-1.9 5.2-5.2.1-.9.1-1.3.1-3.7s0-2.8-.1-3.7C20.8 5 19 3.2 15.7 3.1 14.8 3 14.4 3 12 3Z',
		),
		'linkedin'  => array(
			'label' => __( 'LinkedIn', 'gami-surgical' ),
			'path'  => 'M6.9 8.9H3.6V20h3.3V8.9ZM5.3 3.7a1.9 1.9 0 1 0 0 3.8 1.9 1.9 0 0 0 0-3.8ZM20.4 13.7c0-3-.6-5.1-4.1-5.1-1.7 0-2.8.9-3.2 1.8V8.9H9.9V20h3.3v-5.5c0-1.5.3-2.9 2.1-2.9 1.8 0 1.8 1.7 1.8 3V20h3.3v-6.3Z',
		),
		'youtube'   => array(
			'label' => __( 'YouTube', 'gami-surgical' ),
			'path'  => 'M21.6 7.2a2.5 2.5 0 0 0-1.8-1.8C18.2 5 12 5 12 5s-6.2 0-7.8.4A2.5 2.5 0 0 0 2.4 7.2C2 8.8 2 12 2 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.8 1.8C5.8 19 12 19 12 19s6.2 0 7.8-.4a2.5 2.5 0 0 0 1.8-1.8c.4-1.6.4-4.8.4-4.8s0-3.2-.4-4.8ZM10 15V9l5.2 3L10 15Z',
		),
		'x'         => array(
			'label' => __( 'X (Twitter)', 'gami-surgical' ),
			'path'  => 'M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.4L5.3 21H2.2l7.3-8.3L1.9 3h6.4l4.4 5.8L17.8 3Zm-1.1 16.2h1.7L7.4 4.7H5.6l11.1 14.5Z',
		),
		'tiktok'    => array(
			'label' => __( 'TikTok', 'gami-surgical' ),
			'path'  => 'M16.6 3h-3.2v12.4a2.7 2.7 0 1 1-2.7-2.7c.3 0 .5 0 .8.1V9.5h-.8a6 6 0 1 0 6 6V9.2a7.6 7.6 0 0 0 4.3 1.3V7.3a4.4 4.4 0 0 1-4.4-4.3Z',
		),
	);
}

/**
 * Social icon links. Prints nothing when no URLs are set.
 */
function gami_social_links( $class = '' ) {
	$items = array();
	foreach ( gami_social_networks() as $key => $network ) {
		$url = gami_option( 'social_' . $key );
		if ( ! $url ) {
			continue;
		}
		$items[] = sprintf(
			'<li><a href="%1$s" target="_blank" rel="noopener me"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path fill="currentColor" d="%2$s"/></svg><span class="screen-reader-text">%3$s</span></a></li>',
			esc_url( $url ),
			esc_attr( $network['path'] ),
			esc_html( $network['label'] )
		);
	}
	if ( ! $items ) {
		return;
	}
	printf(
		'<ul class="social-links %1$s" aria-label="%2$s">%3$s</ul>',
		esc_attr( $class ),
		esc_attr__( 'Social media', 'gami-surgical' ),
		implode( '', $items ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	);
}

/**
 * Footer logo: the dedicated footer logo, else the header logo.
 */
function gami_footer_logo() {
	$src = gami_option( 'footer_logo' );
	if ( ! $src ) {
		gami_logo();
		return;
	}
	printf(
		'<a class="custom-logo-link" href="%1$s" rel="home"><img class="custom-logo" src="%2$s" alt="%3$s"></a>',
		esc_url( home_url( '/' ) ),
		esc_url( $src ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}

/**
 * Default content for footer columns that have no widgets.
 *
 * 1: logo, description, certifications, social icons.
 * 2: product categories.
 * 3: contact details.
 * 4: the "Footer menu" (Appearance > Menus).
 */
function gami_footer_default_column( $col ) {
	switch ( (int) $col ) {
		case 1:
			if ( gami_option( 'footer_logo_show' ) ) {
				echo '<div class="footer-brand">';
				gami_footer_logo();
				echo '</div>';
			}
			if ( gami_option( 'footer_about' ) ) {
				echo '<p class="footer-about">' . esc_html( gami_option( 'footer_about' ) ) . '</p>';
			}
			if ( gami_option( 'certifications' ) ) {
				echo '<p class="footer-certs">' . esc_html( gami_option( 'certifications' ) ) . '</p>';
			}
			if ( gami_option( 'footer_social' ) ) {
				gami_social_links( 'social-links--footer' );
			}
			break;

		case 2:
			$terms = array_slice( gami_catalog_terms(), 0, 8 );
			if ( gami_option( 'footer_col2_title' ) ) {
				echo '<h2 class="widget-title">' . esc_html( gami_option( 'footer_col2_title' ) ) . '</h2>';
			}
			echo '<ul class="footer-list">';
			foreach ( $terms as $term ) {
				printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_term_link( $term ) ), esc_html( $term->name ) );
			}
			printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( gami_catalog_url() ), esc_html__( 'All products', 'gami-surgical' ) );
			echo '</ul>';
			break;

		case 3:
			if ( gami_option( 'footer_col3_title' ) ) {
				echo '<h2 class="widget-title">' . esc_html( gami_option( 'footer_col3_title' ) ) . '</h2>';
			}
			echo '<address class="footer-contact">';
			$rows = array();
			if ( gami_option( 'address' ) ) {
				$rows[] = array( 'pin', nl2br( esc_html( gami_option( 'address' ) ) ) );
			}
			if ( gami_option( 'phone' ) ) {
				$rows[] = array( 'phone', gami_phone_link() );
			}
			if ( gami_option( 'phone2' ) ) {
				$rows[] = array( 'phone', gami_phone_link( 'phone2' ) );
			}
			if ( gami_option( 'email' ) ) {
				$rows[] = array( 'email', gami_email_link() );
			}
			$wa = gami_whatsapp_url();
			if ( $wa ) {
				$rows[] = array( 'whatsapp', sprintf( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', esc_url( $wa ), esc_html__( 'Chat on WhatsApp', 'gami-surgical' ) ) );
			}
			if ( gami_option( 'hours' ) ) {
				$rows[] = array( 'clock', esc_html( gami_option( 'hours' ) ) );
			}
			foreach ( $rows as $row ) {
				echo '<span class="footer-contact__row">' . gami_icon( $row[0], 18 ) . '<span>' . $row[1] . '</span></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped when built.
			}
			echo '</address>';
			break;

		case 4:
			if ( gami_option( 'footer_col4_title' ) ) {
				echo '<h2 class="widget-title">' . esc_html( gami_option( 'footer_col4_title' ) ) . '</h2>';
			}
			if ( has_nav_menu( 'footer' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'footer-list',
						'depth'          => 1,
					)
				);
			} else {
				echo '<ul class="footer-list">';
				$exclude = array( (int) get_option( 'page_on_front' ) );
				if ( gami_wc_active() ) {
					foreach ( array( 'cart', 'checkout', 'myaccount' ) as $wc_page ) {
						$exclude[] = (int) wc_get_page_id( $wc_page );
					}
				}
				wp_list_pages(
					array(
						'title_li' => '',
						'depth'    => 1,
						'number'   => 6,
						'exclude'  => implode( ',', array_filter( $exclude, function ( $id ) {
							return $id > 0;
						} ) ),
					)
				);
				echo '</ul>';
			}
			break;
	}
}

/**
 * Small UI icons (24x24, currentColor).
 */
function gami_icon( $name, $size = 18 ) {
	$paths = array(
		'phone'    => '<path d="M6.6 3.5h2.6l1.4 4-2 1.3a11 11 0 0 0 5.6 5.6l1.3-2 4 1.4v2.6a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 4.6 5.7a2 2 0 0 1 2-2.2Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
		'email'    => '<rect x="3" y="5" width="18" height="14" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="m3.5 6 8.5 7 8.5-7" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
		'whatsapp' => '<path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2l-.4-.2Z"/>',
		'pin'      => '<path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.3" fill="none" stroke="currentColor" stroke-width="1.7"/>',
		'clock'    => '<circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M12 7.5V12l3 2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
		'chevron'  => '<path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		'arrow-up' => '<path d="M12 19V5m-6 6 6-6 6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		'filter'   => '<path d="M4 6h16M7 12h10M10 18h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
		'inquiry'  => '<rect x="5" y="4" width="14" height="17" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M9 4V3h6v1M8.5 9.5h7M8.5 13h7M8.5 16.5h4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
 		'check'    => '<path d="m5 12.5 4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>',
		'list-add' => '<path d="M4 7h10M4 12h10M4 17h6M18 14v6M15 17h6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>',
	);
	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}
	return sprintf( '<svg class="gs-icon" viewBox="0 0 24 24" width="%1$d" height="%1$d" aria-hidden="true" focusable="false">%2$s</svg>', (int) $size, $paths[ $name ] );
}

/**
 * Phone or email link with an icon (top bar, footer).
 */
function gami_contact_chip( $type ) {
	if ( 'phone' === $type || 'phone2' === $type ) {
		$phone = gami_option( $type );
		if ( ! $phone ) {
			return;
		}
		$label = gami_option( $type . '_label' );
		printf(
			'<a class="contact-chip contact-chip--%4$s" href="tel:%1$s">%2$s<span>%3$s</span>%5$s</a>',
			esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ),
			gami_icon( 'phone', 16 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $phone ),
			esc_attr( $type ),
			$label ? '<span class="phone-label">' . esc_html( $label ) . '</span>' : ''
		);
	} elseif ( 'email' === $type ) {
		$email = antispambot( gami_option( 'email' ) );
		printf(
			'<a class="contact-chip" href="mailto:%1$s">%2$s<span>%1$s</span></a>',
			esc_attr( $email ),
			gami_icon( 'email', 16 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
}

/**
 * Call / WhatsApp / Email buttons at the bottom of the mobile menu.
 */
function gami_drawer_contact() {
	if ( ! gami_option( 'drawer_contact' ) ) {
		return;
	}
	$items = array();
	$phone = gami_option( 'phone' );
	if ( $phone ) {
		$items[] = array( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ), 'phone', __( 'Call', 'gami-surgical' ), false );
	}
	$wa = gami_whatsapp_url();
	if ( $wa ) {
		$items[] = array( $wa, 'whatsapp', __( 'WhatsApp', 'gami-surgical' ), true );
	}
	$email = gami_option( 'email' );
	if ( $email ) {
		$items[] = array( 'mailto:' . antispambot( $email ), 'email', __( 'Email', 'gami-surgical' ), false );
	}
	if ( ! $items ) {
		return;
	}
	echo '<div class="drawer-contact">';
	foreach ( $items as $item ) {
		printf(
			'<a class="drawer-contact__item" href="%1$s"%4$s>%2$s<span>%3$s</span></a>',
			esc_url( $item[0], array( 'tel', 'mailto', 'https', 'http' ) ),
			gami_icon( $item[1], 20 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			esc_html( $item[2] ),
			$item[3] ? ' target="_blank" rel="noopener"' : ''
		);
	}
	echo '</div>';
}

/**
 * Helpful "nothing found" block: tips, categories and an inquiry button
 * pre-filled with what the visitor searched for.
 */
function gami_no_results_help() {
	$q     = get_search_query( false );
	$terms = array_slice( gami_catalog_terms(), 0, 6 );
	$msg   = $q ? sprintf( /* translators: %s search */ __( 'I am looking for: %s', 'gami-surgical' ), $q ) : '';
	?>
	<section class="no-results-help">
		<div class="no-results-help__icon" aria-hidden="true"><?php echo gami_icon( 'inquiry', 30 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<div class="no-results-help__body">
			<h2 class="no-results-help__title">
				<?php
				echo $q
					/* translators: %s search */
					? esc_html( sprintf( __( 'No products match “%s”', 'gami-surgical' ), $q ) )
					: esc_html__( 'No products here yet', 'gami-surgical' );
				?>
			</h2>
			<p><?php esc_html_e( 'Try a shorter word, another spelling or a catalogue number. Or tell us what you need: send a description, size or photo and we will reply with prices and lead time.', 'gami-surgical' ); ?></p>
			<p class="no-results-help__actions">
				<a class="button button--primary" href="<?php echo esc_url( gami_inquiry_page_url_safe( $msg ) ); ?>"><?php esc_html_e( 'Send an inquiry', 'gami-surgical' ); ?></a>
				<a class="button button--ghost" href="<?php echo esc_url( gami_catalog_url() ); ?>"><?php esc_html_e( 'Browse all products', 'gami-surgical' ); ?></a>
			</p>
			<?php if ( $terms ) : ?>
				<p class="no-results-help__label"><?php esc_html_e( 'Or browse a category:', 'gami-surgical' ); ?></p>
				<ul class="no-results-help__cats">
					<?php foreach ( $terms as $term ) : ?>
						<li><a class="chip" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Inquiry page URL with an optional pre-filled message (safe without inquiry.php).
 */
function gami_inquiry_page_url_safe( $message = '' ) {
	if ( function_exists( 'gami_inquiry_page_url' ) ) {
		return gami_inquiry_page_url( $message ? array( 'message' => rawurlencode( $message ) ) : array() );
	}
	return gami_quote_url();
}

/**
 * ID of a product category still named "Uncategorized" (to hide), or 0.
 */
function gami_uncategorized_term_id() {
	$term = get_term_by( 'slug', 'uncategorized', 'product_cat' );
	if ( ! $term ) {
		$default = (int) get_option( 'default_product_cat' );
		$term    = $default ? get_term( $default, 'product_cat' ) : null;
	}
	if ( $term && ! is_wp_error( $term ) && ( 'uncategorized' === $term->slug || 'uncategorized' === strtolower( $term->name ) ) ) {
		return (int) $term->term_id;
	}
	return 0;
}

/**
 * Sizes of a product as a list: the theme's Sizes field (comma separated),
 * otherwise a WooCommerce attribute named "Size" (e.g. on variable products).
 */
function gami_product_sizes( $post_id ) {
	$own = array_values( array_filter( array_map( 'trim', explode( ',', (string) get_post_meta( $post_id, '_gami_sizes', true ) ) ) ) );
	if ( $own || ! function_exists( 'wc_get_product' ) ) {
		return $own;
	}
	$product = wc_get_product( $post_id );
	if ( ! $product ) {
		return array();
	}
	foreach ( $product->get_attributes() as $attr ) {
		$label = $attr->is_taxonomy() ? wc_attribute_label( $attr->get_name() ) : $attr->get_name();
		if ( false === stripos( $label, 'size' ) ) {
			continue;
		}
		if ( $attr->is_taxonomy() ) {
			$terms = wc_get_product_terms( $post_id, $attr->get_name(), array( 'fields' => 'names' ) );
			return array_values( array_filter( array_map( 'trim', (array) $terms ) ) );
		}
		return array_values( array_filter( array_map( 'trim', $attr->get_options() ) ) );
	}
	return array();
}

/**
 * Footer: brand column (logo, about, contact details, certifications, social).
 */
function gami_footer_brand() {
	if ( gami_option( 'footer_logo_show' ) ) {
		echo '<div class="footer-brand">';
		gami_footer_logo();
		echo '</div>';
	}
	if ( gami_option( 'footer_about' ) ) {
		echo '<p class="footer-about">' . esc_html( gami_option( 'footer_about' ) ) . '</p>';
	}

	$rows = array();
	if ( gami_option( 'address' ) ) {
		$rows[] = array( 'pin', nl2br( esc_html( gami_option( 'address' ) ) ) );
	}
	if ( gami_option( 'phone' ) ) {
		$rows[] = array( 'phone', gami_phone_link() );
	}
	if ( gami_option( 'phone2' ) ) {
		$rows[] = array( 'phone', gami_phone_link( 'phone2' ) );
	}
	if ( gami_option( 'email' ) ) {
		$rows[] = array( 'email', gami_email_link() );
	}
	$wa = gami_whatsapp_url();
	if ( $wa ) {
		$rows[] = array( 'whatsapp', sprintf( '<a href="%1$s" target="_blank" rel="noopener">%2$s</a>', esc_url( $wa ), esc_html__( 'Chat on WhatsApp', 'gami-surgical' ) ) );
	}
	if ( gami_option( 'hours' ) ) {
		$rows[] = array( 'clock', esc_html( gami_option( 'hours' ) ) );
	}
	if ( $rows ) {
		if ( gami_option( 'footer_col3_title' ) ) {
			echo '<p class="footer-brand-col__label">' . esc_html( gami_option( 'footer_col3_title' ) ) . '</p>';
		}
		echo '<address class="footer-contact">';
		foreach ( $rows as $row ) {
			echo '<span class="footer-contact__row"><span class="footer-contact__icon">' . gami_icon( $row[0], 16 ) . '</span><span>' . $row[1] . '</span></span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped when built.
		}
		echo '</address>';
	}
	if ( gami_option( 'certifications' ) ) {
		echo '<p class="footer-certs">' . esc_html( gami_option( 'certifications' ) ) . '</p>';
	}
	if ( gami_option( 'footer_social' ) ) {
		gami_social_links( 'social-links--footer' );
	}
}

/**
 * Footer: one main category with its sub-categories and "View all".
 */
function gami_footer_category_column( $term ) {
	$limit = max( 3, min( 12, absint( gami_option( 'footer_cat_links' ) ) ) );
	$kids  = function_exists( 'gami_term_children' ) ? gami_term_children( $term->term_id ) : array();
	echo '<h2 class="widget-title">' . esc_html( $term->name ) . '</h2>';
	echo '<ul class="footer-list">';
	foreach ( array_slice( $kids, 0, $limit ) as $kid ) {
		printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( get_term_link( $kid ) ), esc_html( $kid->name ) );
	}
	printf(
		'<li class="footer-list__all"><a href="%1$s">%2$s <span aria-hidden="true">→</span></a></li>',
		esc_url( get_term_link( $term ) ),
		esc_html(
			count( $kids ) > $limit
				/* translators: %d: number of sub-categories */
				? sprintf( __( 'View all %d', 'gami-surgical' ), count( $kids ) )
				: __( 'View all', 'gami-surgical' )
		)
	);
	echo '</ul>';
}
