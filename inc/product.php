<?php
/**
 * Product page (single product).
 *
 * Built on WooCommerce's own hooks, so structured data and other plugins
 * keep working:
 *
 *  Gallery (sticky on desktop) | Summary:
 *                                category · catalogue no. (copy)
 *                                title, short description
 *                                key specs
 *                                size chips + quantity (go into the inquiry)
 *                                Send inquiry / Add to inquiry list
 *                                WhatsApp + points, share
 *  Details: tabs (Specifications, Description) | sticky inquiry card
 *  Feature strip, related instruments, recently viewed.
 *  Phones: sticky bottom bar with the inquiry buttons.
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
 * Layout: replace the default summary pieces with ours
 * ---------------------------------------------------------------------- */

function gami_product_layout() {
	if ( ! is_product() ) {
		return;
	}
	// Summary.
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
	if ( ! gami_show_prices() ) {
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	}
	add_action( 'woocommerce_single_product_summary', 'gami_product_eyebrow', 3 );
	add_action( 'woocommerce_single_product_summary', 'gami_product_excerpt_fallback', 21 );
	add_action( 'woocommerce_single_product_summary', 'gami_product_highlights', 22 );
	add_action( 'woocommerce_single_product_summary', 'gami_product_key_specs', 23 );
	add_action( 'woocommerce_single_product_summary', 'gami_product_panel_open', 25 );
	add_action( 'woocommerce_single_product_summary', 'gami_product_config', 26 );
	add_action( 'woocommerce_single_product_summary', 'gami_product_buttons', 30 );
	add_action( 'woocommerce_single_product_summary', 'gami_product_panel_close', 32 );
	add_action( 'woocommerce_single_product_summary', 'gami_product_assurances', 35 );
	add_action( 'woocommerce_single_product_summary', 'gami_product_meta', 40 );

	// Below the summary.
	remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
	add_action( 'woocommerce_after_single_product_summary', 'gami_product_details', 10 );
	add_action( 'woocommerce_after_single_product_summary', 'gami_product_recent', 25 );
}
add_action( 'wp', 'gami_product_layout' );

function gami_product_post_class( $classes ) {
	if ( is_product() && in_the_loop() ) {
		$classes[] = 'gs-product';
	}
	return $classes;
}
add_filter( 'woocommerce_post_class', 'gami_product_post_class' );

/* -------------------------------------------------------------------------
 * Summary pieces
 * ---------------------------------------------------------------------- */

/**
 * Category link and catalogue number with a copy button.
 */
function gami_product_eyebrow() {
	global $product;
	$sku   = $product->get_sku();
	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	$term  = null;
	if ( $terms && ! is_wp_error( $terms ) ) {
		// The deepest category reads best (e.g. "Forceps" rather than "Surgical").
		usort(
			$terms,
			function ( $a, $b ) {
				return count( get_ancestors( $b->term_id, 'product_cat' ) ) - count( get_ancestors( $a->term_id, 'product_cat' ) );
			}
		);
		$term = $terms[0];
	}
	if ( ! $term && ! $sku ) {
		return;
	}
	?>
	<div class="gs-eyebrow">
		<?php if ( $term ) : ?>
			<a class="gs-eyebrow__cat" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
		<?php endif; ?>
		<?php if ( $sku ) : ?>
			<span class="gs-catno">
				<span class="gs-catno__label"><?php esc_html_e( 'Cat. no.', 'gami-surgical' ); ?></span>
				<span class="gs-catno__value"><?php echo esc_html( $sku ); ?></span>
				<button type="button" class="gs-catno__copy" data-copy="<?php echo esc_attr( $sku ); ?>" title="<?php esc_attr_e( 'Copy catalogue number', 'gami-surgical' ); ?>">
					<svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true" focusable="false"><rect x="8.5" y="8.5" width="11" height="11" rx="2" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="M15.5 8.5v-2a2 2 0 0 0-2-2h-7a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h2" fill="none" stroke="currentColor" stroke-width="1.8"/></svg>
					<span class="screen-reader-text"><?php esc_html_e( 'Copy catalogue number', 'gami-surgical' ); ?></span>
				</button>
			</span>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * No short description? Show the start of the full description instead,
 * so the summary never looks empty.
 */
function gami_product_excerpt_fallback() {
	global $product;
	if ( trim( wp_strip_all_tags( $product->get_short_description() ) ) ) {
		return;
	}
	$text = trim( wp_strip_all_tags( strip_shortcodes( $product->get_description() ) ) );
	if ( '' === $text ) {
		return;
	}
	printf(
		'<div class="woocommerce-product-details__short-description gs-excerpt"><p>%1$s</p>%2$s</div>',
		esc_html( wp_trim_words( $text, 34 ) ),
		str_word_count( $text ) > 34 ? '<a class="gs-excerpt__more" href="#tab-title-description" data-open-tab="description">' . esc_html__( 'Read full description', 'gami-surgical' ) . '</a>' : ''
	);
}

/**
 * The selling points (Customize > Gami Surgical > Inquiries > points) as a
 * neat row of highlights under the title.
 */
function gami_product_highlights() {
	$points = array_slice( array_filter( array_map( 'trim', explode( "\n", (string) gami_option( 'inquiry_points' ) ) ) ), 0, 3 );
	if ( ! $points ) {
		return;
	}
	$icons = array(
		'<path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="10" r="2.3" fill="none" stroke="currentColor" stroke-width="1.7"/>',
		'<circle cx="12" cy="12" r="8.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 12h17M12 3.5c2.4 2.4 3.5 5.3 3.5 8.5s-1.1 6.1-3.5 8.5c-2.4-2.4-3.5-5.3-3.5-8.5S9.6 5.9 12 3.5Z" fill="none" stroke="currentColor" stroke-width="1.7"/>',
		'<path d="M4 6.5h16v11H4z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 7 7.5 6 7.5-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
	);
	echo '<ul class="gs-highlights">';
	foreach ( $points as $i => $point ) {
		printf(
			'<li><span class="gs-highlights__icon"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">%1$s</svg></span><span>%2$s</span></li>',
			$icons[ $i % 3 ], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static icon markup.
			esc_html( $point )
		);
	}
	echo '</ul>';
}

/**
 * "Request a quote" panel around size, quantity and the buttons.
 */
function gami_product_panel_open() {
	?>
	<div class="gs-buy">
		<div class="gs-buy__head">
			<h2 class="gs-buy__title"><?php esc_html_e( 'Request a quote', 'gami-surgical' ); ?></h2>
			<p class="gs-buy__sub">
				<?php
				global $product;
				echo esc_html(
					( $product && gami_product_sizes( $product->get_id() ) )
						? __( 'Choose size and quantity. We reply with prices, lead time and shipping.', 'gami-surgical' )
						: __( 'Choose the quantity you need. We reply with prices, lead time and shipping.', 'gami-surgical' )
				);
				?>
			</p>
		</div>
	<?php
}

function gami_product_panel_close() {
	?>
		<ol class="gs-buy__steps" aria-label="<?php esc_attr_e( 'How ordering works', 'gami-surgical' ); ?>">
			<li><span>1</span><?php esc_html_e( 'Send inquiry', 'gami-surgical' ); ?></li>
			<li><span>2</span><?php esc_html_e( 'Get prices & lead time', 'gami-surgical' ); ?></li>
			<li><span>3</span><?php esc_html_e( 'Confirm your order', 'gami-surgical' ); ?></li>
		</ol>
	</div>
	<?php
}

/**
 * Up to four key specs as a compact grid.
 */
function gami_product_key_specs() {
	global $product;
	$fields = gami_instrument_fields( 'product' );
	$icons  = array(
		'material' => '<path d="M12 3 4 7.5v9L12 21l8-4.5v-9L12 3Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4 7.5 8 4.5 8-4.5M12 12v9" fill="none" stroke="currentColor" stroke-width="1.7"/>',
		'finish'   => '<circle cx="12" cy="12" r="8" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M8.5 10.5a4 4 0 0 1 4-3" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
		'usage'    => '<path d="M5 12a7 7 0 0 1 12-4.9M19 12a7 7 0 0 1-12 4.9" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M17 3.5v3.6h-3.6M7 20.5v-3.6h3.6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
		'moq'      => '<rect x="4" y="7" width="16" height="13" rx="1.5" fill="none" stroke="currentColor" stroke-width="1.7"/><path d="M4 11h16M9.5 4h5l1 3h-7l1-3Z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
	);
	$items = array();
	foreach ( array_keys( $icons ) as $key ) {
		$val = get_post_meta( $product->get_id(), '_gami_' . $key, true );
		if ( $val && isset( $fields[ $key ] ) ) {
			$items[] = array( $icons[ $key ], $fields[ $key ], $val );
		}
	}
	if ( ! $items ) {
		return;
	}
	echo '<dl class="gs-keyspecs">';
	foreach ( $items as $item ) {
		printf(
			'<div class="gs-keyspecs__item"><svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">%1$s</svg><dt>%2$s</dt><dd>%3$s</dd></div>',
			$item[0], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static icon markup.
			esc_html( $item[1] ),
			esc_html( $item[2] )
		);
	}
	echo '</dl>';
}

/**
 * Minimum order quantity as a number, read from the "Minimum order" field
 * (e.g. "50 pieces per size" -> 50). 1 if none.
 */
function gami_product_min_qty( $product_id ) {
	$moq = (string) get_post_meta( $product_id, '_gami_moq', true );
	return preg_match( '/(\d[\d,]*)/', $moq, $m ) ? max( 1, (int) str_replace( ',', '', $m[1] ) ) : 1;
}

/**
 * Size chips and quantity. The inquiry buttons anywhere on the page read
 * these ([data-inquiry-config="ID"]).
 */
function gami_product_config() {
	global $product;
	$id    = $product->get_id();
	$sizes = gami_product_sizes( $id );
	$min   = gami_product_min_qty( $id );
	$moq   = get_post_meta( $id, '_gami_moq', true );
	?>
	<div class="gs-config" data-inquiry-config="<?php echo (int) $id; ?>">
		<?php if ( $sizes ) : ?>
			<fieldset class="gs-config__sizes">
				<legend class="gs-config__label"><?php esc_html_e( 'Size', 'gami-surgical' ); ?> <span class="gs-config__chosen" data-chosen-size><?php esc_html_e( 'Any / all sizes', 'gami-surgical' ); ?></span></legend>
				<div class="gs-chips">
					<label class="gs-chip">
						<input type="radio" name="gs_size_<?php echo (int) $id; ?>" value="" checked>
						<span><?php esc_html_e( 'All sizes', 'gami-surgical' ); ?></span>
					</label>
					<?php foreach ( $sizes as $size ) : ?>
						<label class="gs-chip">
							<input type="radio" name="gs_size_<?php echo (int) $id; ?>" value="<?php echo esc_attr( $size ); ?>">
							<span><?php echo esc_html( $size ); ?></span>
						</label>
					<?php endforeach; ?>
				</div>
			</fieldset>
		<?php endif; ?>

		<div class="gs-config__qty">
			<label class="gs-config__label" for="gs-qty-main-<?php echo (int) $id; ?>"><?php esc_html_e( 'Quantity', 'gami-surgical' ); ?></label>
			<div class="gs-config__qty-row">
				<span class="gs-qty gs-qty--lg">
					<button type="button" class="gs-qty__btn" data-qty-step="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'gami-surgical' ); ?>">−</button>
					<input type="number" id="gs-qty-main-<?php echo (int) $id; ?>" data-config-qty min="1" step="1" inputmode="numeric" value="<?php echo (int) $min; ?>">
					<button type="button" class="gs-qty__btn" data-qty-step="1" aria-label="<?php esc_attr_e( 'Increase quantity', 'gami-surgical' ); ?>">+</button>
				</span>
				<?php if ( $moq ) : ?>
					<span class="gs-config__moq"><?php echo esc_html( sprintf( /* translators: %s minimum order */ __( 'Minimum order: %s', 'gami-surgical' ), $moq ) ); ?></span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}

function gami_product_buttons() {
	global $product;
	echo gami_inquiry_buttons( $product->get_id(), 'single' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * WhatsApp link and the selling points (Customize > Gami Surgical > Inquiries).
 */
function gami_product_assurances() {
	global $product;
	$name = $product->get_name() . ( $product->get_sku() ? ' (' . $product->get_sku() . ')' : '' );
	$wa   = gami_whatsapp_url( sprintf( /* translators: %s product */ __( 'Inquiry: %s', 'gami-surgical' ), $name ) );
	if ( ! $wa ) {
		return;
	}
	printf(
		'<a class="gs-wa-card" href="%1$s" target="_blank" rel="noopener"><span class="gs-wa-card__icon">%2$s</span><span class="gs-wa-card__text"><strong>%3$s</strong><span>%4$s</span></span><span class="gs-wa-card__arrow" aria-hidden="true">→</span></a>',
		esc_url( $wa ),
		gami_icon( 'whatsapp', 22 ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		esc_html__( 'Questions? Chat with us on WhatsApp', 'gami-surgical' ),
		esc_html( gami_option( 'hours' ) ? gami_option( 'hours' ) : __( 'Quick answers from our sales team', 'gami-surgical' ) )
	);
}

/**
 * Categories and tags (the catalogue number is shown above the title).
 */
function gami_product_meta() {
	global $product;
	$cats = wc_get_product_category_list( $product->get_id(), ', ' );
	$tags = wc_get_product_tag_list( $product->get_id(), ', ' );
	echo '<div class="gs-meta-row"><div class="gs-meta">';
	if ( $cats ) {
		echo '<p><span>' . esc_html( _n( 'Category', 'Categories', count( $product->get_category_ids() ), 'gami-surgical' ) ) . '</span>' . wp_kses_post( $cats ) . '</p>';
	}
	if ( $tags ) {
		echo '<p><span>' . esc_html__( 'Tags', 'gami-surgical' ) . '</span>' . wp_kses_post( $tags ) . '</p>';
	}
	echo '</div>';
	gami_product_share();
	echo '</div>';
}

function gami_product_share() {
	global $product;
	$url   = get_permalink( $product->get_id() );
	$title = wp_strip_all_tags( $product->get_name() );
	$links = array(
		'whatsapp' => array( 'WhatsApp', 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $url ) ),
		'linkedin' => array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $url ) ),
		'email'    => array( __( 'Email', 'gami-surgical' ), 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $url ) ),
	);
	$paths = gami_social_networks();
	?>
	<div class="gs-share">
		<span class="gs-share__label"><?php esc_html_e( 'Share', 'gami-surgical' ); ?></span>
		<?php foreach ( $links as $key => $l ) : ?>
			<a class="gs-share__btn" href="<?php echo esc_url( $l[1], array( 'https', 'mailto' ) ); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr( $l[0] ); ?>">
				<?php
				if ( 'whatsapp' === $key ) {
					echo gami_icon( 'whatsapp', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} elseif ( 'email' === $key ) {
					echo gami_icon( 'email', 17 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo '<svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true" focusable="false"><path fill="currentColor" d="' . esc_attr( $paths['linkedin']['path'] ) . '"/></svg>';
				}
				?>
				<span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s network */ __( 'Share on %s', 'gami-surgical' ), $l[0] ) ); ?></span>
			</a>
		<?php endforeach; ?>
		<button type="button" class="gs-share__btn" data-copy="<?php echo esc_url( $url ); ?>" data-copy-done="<?php esc_attr_e( 'Link copied', 'gami-surgical' ); ?>" title="<?php esc_attr_e( 'Copy link', 'gami-surgical' ); ?>">
			<svg viewBox="0 0 24 24" width="17" height="17" aria-hidden="true" focusable="false"><path d="M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1 1M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1-1" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
			<span class="screen-reader-text"><?php esc_html_e( 'Copy link', 'gami-surgical' ); ?></span>
		</button>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Below the summary
 * ---------------------------------------------------------------------- */

/**
 * Tabs with a sticky inquiry card beside them (desktop).
 */
function gami_product_details() {
	global $product;
	$id    = $product->get_id();
	$image = get_the_post_thumbnail_url( $id, 'woocommerce_thumbnail' );
	?>
	<section class="gs-details" aria-label="<?php esc_attr_e( 'Product details', 'gami-surgical' ); ?>">
		<div class="gs-details__main">
			<?php woocommerce_output_product_data_tabs(); ?>
		</div>
		<aside class="gs-details__aside" aria-label="<?php esc_attr_e( 'Inquiry', 'gami-surgical' ); ?>">
			<div class="gs-inq-card">
				<div class="gs-inq-card__product">
					<?php if ( $image ) : ?>
						<img src="<?php echo esc_url( $image ); ?>" alt="" width="64" height="64" loading="lazy">
					<?php endif; ?>
					<div>
						<p class="gs-inq-card__name"><?php echo esc_html( $product->get_name() ); ?></p>
						<?php if ( $product->get_sku() ) : ?>
							<p class="gs-inq-card__sku"><?php echo esc_html__( 'Cat. no.', 'gami-surgical' ) . ' ' . esc_html( $product->get_sku() ); ?></p>
						<?php endif; ?>
					</div>
				</div>
				<dl class="gs-inq-card__chosen">
					<div><dt><?php esc_html_e( 'Size', 'gami-surgical' ); ?></dt><dd data-chosen-size><?php esc_html_e( 'Any / all sizes', 'gami-surgical' ); ?></dd></div>
					<div><dt><?php esc_html_e( 'Quantity', 'gami-surgical' ); ?></dt><dd data-chosen-qty><?php echo (int) gami_product_min_qty( $id ); ?></dd></div>
				</dl>
				<?php echo gami_inquiry_buttons( $id, 'card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<p class="gs-inq-card__note"><?php esc_html_e( 'We reply with prices, lead time and shipping to your country.', 'gami-surgical' ); ?></p>
			</div>
		</aside>
	</section>
	<?php
}

/**
 * Selling points as a strip (Customize > Gami Surgical > Inquiries > points).
 */
function gami_product_features() {
	$points = array_filter( array_map( 'trim', explode( "\n", (string) gami_option( 'inquiry_points' ) ) ) );
	if ( ! $points ) {
		return;
	}
	echo '<ul class="gs-features">';
	foreach ( array_slice( $points, 0, 4 ) as $point ) {
		printf( '<li class="gs-features__item"><span class="gs-features__icon">%1$s</span><span>%2$s</span></li>', gami_icon( 'check', 20 ), esc_html( $point ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</ul>';
}

/**
 * Recently viewed: filled in the browser from the visitor's own history.
 */
function gami_product_recent() {
	global $product;
	printf(
		'<section class="gs-recent" data-recent data-current="%1$s" data-item="%2$s" hidden><h2 class="gs-section-title">%3$s</h2><ul class="gs-recent__list"></ul></section>',
		(int) $product->get_id(),
		esc_attr( wp_json_encode( gami_inquiry_item_data( $product->get_id() ) ) ),
		esc_html__( 'Recently viewed', 'gami-surgical' )
	);
}

/**
 * Gallery slider: arrows on, slide animation, no height jumps.
 */
function gami_product_carousel_options( $options ) {
	$options['directionNav']   = true;
	$options['prevText']       = __( 'Previous photo', 'gami-surgical' );
	$options['nextText']       = __( 'Next photo', 'gami-surgical' );
	$options['animation']      = 'slide';
	$options['animationSpeed'] = 350;
	$options['smoothHeight']   = false;
	// No looping: looping adds hidden copies of the first/last photo, which
	// makes the full-screen view open the wrong photo.
	$options['animationLoop']  = false;
	return $options;
}
add_filter( 'woocommerce_single_product_carousel_options', 'gami_product_carousel_options' );

/**
 * Photo background colour (Customize > Gami Surgical > Shop page).
 */
function gami_product_gallery_css() {
	if ( ! is_product() ) {
		return;
	}
	$bg = sanitize_hex_color( gami_option( 'gallery_bg' ) );
	if ( $bg ) {
		wp_add_inline_style( 'gami-product', '.gs-product{--gs-photo-bg:' . $bg . ';}' );
	}
}
add_action( 'wp_enqueue_scripts', 'gami_product_gallery_css', 40 );

add_filter(
	'woocommerce_product_related_products_heading',
	function () {
		return __( 'Related instruments', 'gami-surgical' );
	}
);

/* -------------------------------------------------------------------------
 * Phones: sticky bar with the inquiry buttons
 * ---------------------------------------------------------------------- */

function gami_product_mobile_bar() {
	if ( ! is_product() ) {
		return;
	}
	$id   = get_queried_object_id();
	$name = get_the_title( $id );
	$sku  = gami_catalogue_no( $id );
	$wa   = gami_whatsapp_url( sprintf( /* translators: %s product */ __( 'Inquiry: %s', 'gami-surgical' ), wp_strip_all_tags( $name ) . ( $sku ? ' (' . $sku . ')' : '' ) ) );
	echo '<div class="mobile-inquiry-bar woocommerce" aria-hidden="true">';
	echo gami_inquiry_buttons( $id, 'bar' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	if ( $wa ) {
		printf( '<a class="mobile-inquiry-bar__wa" href="%1$s" target="_blank" rel="noopener" tabindex="-1">%2$s<span class="screen-reader-text">WhatsApp</span></a>', esc_url( $wa ), gami_icon( 'whatsapp', 22 ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</div>';
}
add_action( 'wp_footer', 'gami_product_mobile_bar', 4 );

/* -------------------------------------------------------------------------
 * Assets
 * ---------------------------------------------------------------------- */

function gami_product_assets() {
	if ( ! is_product() ) {
		return;
	}
	wp_enqueue_style( 'gami-product', GAMI_URI . '/assets/css/product.css', array( 'gami-woocommerce', 'gami-inquiry' ), GAMI_VERSION );
	wp_enqueue_script(
		'gami-product',
		GAMI_URI . '/assets/js/product.js',
		array(),
		GAMI_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_localize_script(
		'gami-product',
		'gamiProduct',
		array(
			'copied'     => __( 'Copied', 'gami-surgical' ),
			'fullscreen' => __( 'View full screen', 'gami-surgical' ),
			'photoOf'    => __( 'Photo %1$d of %2$d', 'gami-surgical' ),
			'gallery'    => __( 'Product photos', 'gami-surgical' ),
			'anySize' => __( 'Any / all sizes', 'gami-surgical' ),
			'catNo'   => __( 'Cat. no.', 'gami-surgical' ),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'gami_product_assets', 35 );
