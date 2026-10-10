<?php
/**
 * Product inquiries (replaces the cart).
 *
 * - "Send inquiry" on a product adds it to the visitor's inquiry list and
 *   opens the Inquiry page (created automatically, like a cart page).
 * - "Add to inquiry list" collects several products (kept in the visitor's
 *   browser) to send in one inquiry.
 * - The Inquiry page works without JavaScript too.
 * - Each inquiry is emailed to the sales address, an optional confirmation
 *   goes to the buyer, and a copy is saved under Dashboard > Inquiries so
 *   nothing is lost if email fails.
 * - Spam protection that works with page caching: hidden honeypot field,
 *   signed minimum fill time, and a per-visitor rate limit.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Storage: Dashboard > Inquiries
 * ---------------------------------------------------------------------- */

function gami_register_inquiry_type() {
	register_post_type(
		'gami_inquiry',
		array(
			'labels'          => array(
				'name'          => __( 'Inquiries', 'gami-surgical' ),
				'singular_name' => __( 'Inquiry', 'gami-surgical' ),
				'all_items'     => __( 'All inquiries', 'gami-surgical' ),
				'edit_item'     => __( 'Inquiry', 'gami-surgical' ),
				'search_items'  => __( 'Search inquiries', 'gami-surgical' ),
				'not_found'     => __( 'No inquiries yet.', 'gami-surgical' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_position'   => 26,
			'menu_icon'       => 'dashicons-email-alt',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
			'rewrite'         => false,
			'query_var'       => false,
		)
	);
}
add_action( 'init', 'gami_register_inquiry_type' );

/**
 * Save an inquiry. Used by the inquiry form and the quote form.
 *
 * @param array $d name, email, company, country, phone, message, items (array of rows), source.
 * @return int Post ID or 0.
 */
function gami_save_inquiry( $d ) {
	$d = wp_parse_args(
		$d,
		array(
			'name'    => '',
			'email'   => '',
			'company' => '',
			'country' => '',
			'phone'   => '',
			'message' => '',
			'items'   => array(),
			'source'  => '',
		)
	);
	$count = count( $d['items'] );
	$title = trim( $d['name'] . ( $d['company'] ? ' (' . $d['company'] . ')' : '' ) );
	if ( $count ) {
		/* translators: %d number of products */
		$title .= ' · ' . sprintf( _n( '%d product', '%d products', $count, 'gami-surgical' ), $count );
	}

	$id = wp_insert_post(
		array(
			'post_type'    => 'gami_inquiry',
			'post_status'  => 'private',
			'post_title'   => $title,
			'post_content' => gami_inquiry_text( $d ),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return 0;
	}
	foreach ( array( 'name', 'email', 'company', 'country', 'phone', 'source' ) as $key ) {
		update_post_meta( $id, '_gami_' . $key, $d[ $key ] );
	}
	update_post_meta( $id, '_gami_items', $d['items'] );
	update_post_meta( $id, '_gami_message', $d['message'] );
	update_post_meta( $id, '_gami_status', 'new' );
	return (int) $id;
}

/**
 * Plain-text summary (email body and admin view).
 */
function gami_inquiry_text( $d, $for_customer = false ) {
	$lines = array();
	if ( ! $for_customer ) {
		$lines[] = 'Name: ' . $d['name'];
		$lines[] = 'Company: ' . ( $d['company'] ? $d['company'] : '-' );
		$lines[] = 'Email: ' . $d['email'];
		$lines[] = 'Phone / WhatsApp: ' . ( $d['phone'] ? $d['phone'] : '-' );
		$lines[] = 'Country: ' . ( $d['country'] ? $d['country'] : '-' );
		$lines[] = '';
	}
	if ( $d['items'] ) {
		$lines[] = $for_customer ? __( 'Products', 'gami-surgical' ) . ':' : 'Products:';
		foreach ( $d['items'] as $i => $item ) {
			$lines[] = sprintf(
				'%1$d. %2$s%3$s%4$s · Qty: %5$s',
				$i + 1,
				$item['name'],
				$item['sku'] ? ' [' . $item['sku'] . ']' : '',
				$item['size'] ? ' · Size: ' . $item['size'] : '',
				$item['qty']
			);
			if ( ! $for_customer && ! empty( $item['url'] ) ) {
				$lines[] = '   ' . $item['url'];
			}
		}
		$lines[] = '';
	}
	if ( $d['message'] ) {
		$lines[] = ( $for_customer ? __( 'Your message', 'gami-surgical' ) : 'Message' ) . ':';
		$lines[] = $d['message'];
		$lines[] = '';
	}
	if ( ! $for_customer && $d['source'] ) {
		$lines[] = 'Sent from: ' . $d['source'];
	}
	return implode( "\n", $lines );
}

/**
 * The quote form saves its requests too.
 */
function gami_save_quote_as_inquiry( $q ) {
	gami_save_inquiry(
		array(
			'name'    => $q['name'],
			'email'   => $q['email'],
			'company' => $q['company'],
			'country' => $q['country'],
			'message' => $q['items'] . ( $q['needs'] ? "\n\nNeeds: " . implode( ', ', $q['needs'] ) : '' ),
			'source'  => __( 'Quote form', 'gami-surgical' ),
		)
	);
}
add_action( 'gami_quote_received', 'gami_save_quote_as_inquiry' );

/* Admin list columns and detail view */

function gami_inquiry_columns( $cols ) {
	return array(
		'cb'           => $cols['cb'],
		'title'        => __( 'From', 'gami-surgical' ),
		'gami_email'   => __( 'Email', 'gami-surgical' ),
		'gami_country' => __( 'Country', 'gami-surgical' ),
		'gami_items'   => __( 'Products', 'gami-surgical' ),
		'gami_status'  => __( 'Status', 'gami-surgical' ),
		'date'         => __( 'Received', 'gami-surgical' ),
	);
}
add_filter( 'manage_gami_inquiry_posts_columns', 'gami_inquiry_columns' );

function gami_inquiry_column( $col, $id ) {
	switch ( $col ) {
		case 'gami_email':
			$email = get_post_meta( $id, '_gami_email', true );
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
			break;
		case 'gami_country':
			echo esc_html( get_post_meta( $id, '_gami_country', true ) );
			break;
		case 'gami_items':
			$items = (array) get_post_meta( $id, '_gami_items', true );
			echo esc_html( implode( ', ', wp_list_pluck( array_filter( $items ), 'name' ) ) );
			break;
		case 'gami_status':
			$status = get_post_meta( $id, '_gami_status', true );
			echo 'new' === $status ? '<strong style="color:#b26200">' . esc_html__( 'New', 'gami-surgical' ) . '</strong>' : esc_html__( 'Seen', 'gami-surgical' );
			break;
	}
}
add_action( 'manage_gami_inquiry_posts_custom_column', 'gami_inquiry_column', 10, 2 );

function gami_inquiry_meta_box() {
	add_meta_box( 'gami_inquiry_details', __( 'Inquiry details', 'gami-surgical' ), 'gami_inquiry_meta_box_html', 'gami_inquiry', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'gami_inquiry_meta_box' );

function gami_inquiry_meta_box_html( $post ) {
	update_post_meta( $post->ID, '_gami_status', 'seen' );
	$email = get_post_meta( $post->ID, '_gami_email', true );
	$items = (array) get_post_meta( $post->ID, '_gami_items', true );
	echo '<table class="widefat striped" style="margin-bottom:16px"><tbody>';
	foreach ( array(
		'name'    => __( 'Name', 'gami-surgical' ),
		'company' => __( 'Company', 'gami-surgical' ),
		'email'   => __( 'Email', 'gami-surgical' ),
		'phone'   => __( 'Phone / WhatsApp', 'gami-surgical' ),
		'country' => __( 'Country', 'gami-surgical' ),
		'source'  => __( 'Sent from', 'gami-surgical' ),
	) as $key => $label ) {
		printf( '<tr><th style="width:180px">%1$s</th><td>%2$s</td></tr>', esc_html( $label ), esc_html( get_post_meta( $post->ID, '_gami_' . $key, true ) ) );
	}
	echo '</tbody></table>';

	$items = array_filter( $items );
	if ( $items ) {
		echo '<table class="widefat striped" style="margin-bottom:16px"><thead><tr><th>' . esc_html__( 'Product', 'gami-surgical' ) . '</th><th>' . esc_html__( 'Cat. no.', 'gami-surgical' ) . '</th><th>' . esc_html__( 'Size', 'gami-surgical' ) . '</th><th>' . esc_html__( 'Qty', 'gami-surgical' ) . '</th></tr></thead><tbody>';
		foreach ( $items as $item ) {
			printf(
				'<tr><td><a href="%1$s" target="_blank">%2$s</a></td><td>%3$s</td><td>%4$s</td><td>%5$s</td></tr>',
				esc_url( $item['url'] ),
				esc_html( $item['name'] ),
				esc_html( $item['sku'] ),
				esc_html( $item['size'] ),
				esc_html( $item['qty'] )
			);
		}
		echo '</tbody></table>';
	}
	$message = get_post_meta( $post->ID, '_gami_message', true );
	if ( $message ) {
		echo '<h4>' . esc_html__( 'Message', 'gami-surgical' ) . '</h4><p>' . nl2br( esc_html( $message ) ) . '</p>';
	}
	if ( $email ) {
		printf(
			'<p><a class="button button-primary" href="mailto:%1$s?subject=%2$s">%3$s</a></p>',
			esc_attr( $email ),
			rawurlencode( __( 'Re: your inquiry', 'gami-surgical' ) . ' – ' . get_bloginfo( 'name' ) ),
			esc_html__( 'Reply by email', 'gami-surgical' )
		);
	}
}

/**
 * Count of new inquiries next to the menu item.
 */
function gami_inquiry_menu_count() {
	global $menu;
	$new = get_posts(
		array(
			'post_type'      => 'gami_inquiry',
			'post_status'    => 'private',
			'meta_key'       => '_gami_status', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => 'new', // phpcs:ignore WordPress.DB.SlowDBQuery
			'fields'         => 'ids',
			'posts_per_page' => 99,
		)
	);
	if ( ! $new || ! is_array( $menu ) ) {
		return;
	}
	foreach ( $menu as $i => $item ) {
		if ( isset( $item[2] ) && 'edit.php?post_type=gami_inquiry' === $item[2] ) {
			$menu[ $i ][0] .= ' <span class="awaiting-mod"><span class="pending-count">' . count( $new ) . '</span></span>'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
		}
	}
}
add_action( 'admin_menu', 'gami_inquiry_menu_count', 99 );

/* -------------------------------------------------------------------------
 * The inquiry page
 * ---------------------------------------------------------------------- */

/**
 * ID of the inquiry page (Customize > Gami Surgical > Contact details).
 * Created automatically if it doesn't exist.
 */
function gami_inquiry_page_id() {
	$id = absint( gami_option( 'quote_page' ) );
	return ( $id && 'publish' === get_post_status( $id ) ) ? $id : 0;
}

function gami_inquiry_page_url( $args = array() ) {
	$id  = gami_inquiry_page_id();
	$url = $id ? get_permalink( $id ) : home_url( '/inquiry/' );
	return $args ? add_query_arg( $args, $url ) : $url;
}

/**
 * Create the "Inquiry" page once, and whenever the chosen page is missing.
 */
function gami_ensure_inquiry_page() {
	if ( gami_inquiry_page_id() || ! current_user_can( 'publish_pages' ) ) {
		return;
	}
	$existing = get_page_by_path( 'inquiry' );
	if ( $existing && 'publish' === $existing->post_status ) {
		set_theme_mod( 'gami_quote_page', $existing->ID );
		return;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => __( 'Send an inquiry', 'gami-surgical' ),
			'post_name'    => 'inquiry',
			'post_content' => '',
		)
	);
	if ( $id && ! is_wp_error( $id ) ) {
		set_theme_mod( 'gami_quote_page', $id );
	}
}
add_action( 'after_switch_theme', 'gami_ensure_inquiry_page' );
add_action( 'admin_init', 'gami_ensure_inquiry_page' );

function gami_inquiry_body_class( $classes ) {
	if ( gami_is_inquiry_page() ) {
		$classes[] = 'gs-inquiry-page';
	}
	return $classes;
}
add_filter( 'body_class', 'gami_inquiry_body_class' );

function gami_is_inquiry_page() {
	$id = gami_inquiry_page_id();
	return $id && is_page( $id );
}

/**
 * The inquiry page uses its own template (the page content shows above the list).
 */
function gami_inquiry_template( $template ) {
	if ( gami_is_inquiry_page() && ! gami_is_elementor_page() ) {
		$custom = locate_template( 'templates/inquiry-page.php' );
		if ( $custom ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'gami_inquiry_template', 99 );

/**
 * Elementor-built inquiry page: append the inquiry app after the content.
 */
function gami_inquiry_after_elementor_content( $content ) {
	if ( gami_is_inquiry_page() && gami_is_elementor_page() && in_the_loop() && is_main_query() ) {
		ob_start();
		gami_inquiry_app();
		$content .= ob_get_clean();
	}
	return $content;
}
add_filter( 'the_content', 'gami_inquiry_after_elementor_content', 20 );

/**
 * Shortcode [gami_inquiry] for using the inquiry form on any page.
 */
add_shortcode(
	'gami_inquiry',
	function () {
		ob_start();
		gami_inquiry_app();
		return ob_get_clean();
	}
);

function gami_inquiry_no_index( $robots ) {
	if ( gami_is_inquiry_page() ) {
		$robots['noindex'] = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'gami_inquiry_no_index' );
add_filter(
	'wpseo_robots',
	function ( $robots ) {
		return gami_is_inquiry_page() ? 'noindex, follow' : $robots;
	}
);

/* -------------------------------------------------------------------------
 * Submitting (AJAX, or a normal form post without JavaScript)
 * ---------------------------------------------------------------------- */

/**
 * Time token: when the form was printed. Bots submit instantly.
 */
function gami_inquiry_time_token() {
	$t = time();
	return $t . '.' . substr( wp_hash( 'gami_inquiry|' . $t ), 0, 12 );
}

function gami_inquiry_time_ok( $token ) {
	$parts = explode( '.', (string) $token );
	if ( 2 !== count( $parts ) || ! hash_equals( substr( wp_hash( 'gami_inquiry|' . $parts[0] ), 0, 12 ), $parts[1] ) ) {
		return false;
	}
	return ( time() - (int) $parts[0] ) >= 3;
}

/**
 * Validate, save and email an inquiry.
 *
 * @param array $p Unslashed POST data.
 * @return array { ok: bool, message: string, fields: array, reference: string }
 */
function gami_inquiry_process( $p ) {
	$fail = function ( $message, $fields = array() ) {
		return array(
			'ok'      => false,
			'message' => $message,
			'fields'  => $fields,
		);
	};

	// Honeypot: pretend success so bots learn nothing.
	if ( ! empty( $p['website'] ) ) {
		return array(
			'ok'        => true,
			'message'   => gami_option( 'inquiry_success' ),
			'reference' => '',
			'fields'    => array(),
		);
	}
	if ( ! gami_inquiry_time_ok( isset( $p['ts'] ) ? $p['ts'] : '' ) ) {
		return $fail( __( 'Please take a moment to check your details, then send again.', 'gami-surgical' ) );
	}
	$ip    = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$key   = 'gami_inq_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= 5 ) {
		return $fail( __( 'You have sent several inquiries just now. Please wait a few minutes or contact us by email.', 'gami-surgical' ) );
	}

	$d = array(
		'name'    => isset( $p['name'] ) ? sanitize_text_field( $p['name'] ) : '',
		'email'   => isset( $p['email'] ) ? sanitize_email( $p['email'] ) : '',
		'company' => isset( $p['company'] ) ? sanitize_text_field( $p['company'] ) : '',
		'country' => isset( $p['country'] ) ? sanitize_text_field( $p['country'] ) : '',
		'phone'   => isset( $p['phone'] ) ? sanitize_text_field( $p['phone'] ) : '',
		'message' => isset( $p['message'] ) ? sanitize_textarea_field( $p['message'] ) : '',
		'source'  => isset( $p['source'] ) ? esc_url_raw( $p['source'] ) : '',
		'items'   => array(),
	);

	$rows = isset( $p['items'] ) ? $p['items'] : array();
	if ( is_string( $rows ) ) {
		$rows = json_decode( $rows, true );
	}
	$seen = array();
	foreach ( array_slice( (array) $rows, 0, 50 ) as $row ) {
		$id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
		$size_key = $id . '|' . ( isset( $row['size'] ) ? strtolower( trim( (string) $row['size'] ) ) : '' );
		if ( ! $id || isset( $seen[ $size_key ] ) || ! in_array( get_post_type( $id ), array( 'product', 'instrument' ), true ) || 'publish' !== get_post_status( $id ) ) {
			continue;
		}
		$seen[ $size_key ] = true;
		$d['items'][] = array(
			'id'   => $id,
			'name' => html_entity_decode( wp_strip_all_tags( get_the_title( $id ) ), ENT_QUOTES, 'UTF-8' ),
			'sku'  => gami_catalogue_no( $id ),
			'url'  => get_permalink( $id ),
			'qty'  => isset( $row['qty'] ) ? max( 1, min( 1000000, absint( $row['qty'] ) ) ) : 1,
			'size' => isset( $row['size'] ) ? sanitize_text_field( mb_substr( (string) $row['size'], 0, 60 ) ) : '',
		);
	}

	$errors = array();
	if ( '' === $d['name'] ) {
		$errors['name'] = __( 'Please enter your name.', 'gami-surgical' );
	}
	if ( ! is_email( $d['email'] ) ) {
		$errors['email'] = __( 'Please enter a valid email address.', 'gami-surgical' );
	}
	if ( ! $d['items'] && mb_strlen( $d['message'] ) < 5 ) {
		$errors['message'] = __( 'Add at least one product, or tell us what you need here.', 'gami-surgical' );
	}
	if ( $errors ) {
		return $fail( __( 'Please check the highlighted fields.', 'gami-surgical' ), $errors );
	}

	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );

	$id  = gami_save_inquiry( $d );
	$ref = $id ? 'GS-' . $id : '';

	$to = gami_option( 'email' );
	if ( ! is_email( $to ) || 'sales@example.com' === $to ) {
		$to = get_option( 'admin_email' );
	}
	$subject = trim(
		sprintf(
			/* translators: 1: reference, 2: name, 3: company */
			__( 'New inquiry %1$s from %2$s %3$s', 'gami-surgical' ),
			$ref,
			$d['name'],
			$d['company'] ? '(' . $d['company'] . ')' : ''
		)
	);
	$body = gami_inquiry_text( $d );
	if ( $id ) {
		$body .= "\n\nView in dashboard: " . admin_url( 'post.php?post=' . $id . '&action=edit' );
	}
	$sent = wp_mail( $to, $subject, $body, array( 'Reply-To: ' . $d['name'] . ' <' . $d['email'] . '>' ) );
	if ( $id ) {
		update_post_meta( $id, '_gami_mailed', $sent ? 1 : 0 );
	}

	if ( gami_option( 'inquiry_autoreply' ) ) {
		$site  = get_bloginfo( 'name' );
		$reply = sprintf(
			/* translators: %s: name */
			__( "Dear %s,\n\nThank you for your inquiry. Our sales team will reply with prices, lead time and shipping.\n\n", 'gami-surgical' ),
			$d['name']
		);
		$reply  .= gami_inquiry_text( $d, true );
		$reply  .= "\n" . $site . "\n" . home_url( '/' );
		$sales   = gami_option( 'email' );
		$headers = ( is_email( $sales ) && 'sales@example.com' !== $sales ) ? array( 'Reply-To: ' . $site . ' <' . $sales . '>' ) : array();
		/* translators: 1: reference, 2: site name */
		wp_mail( $d['email'], trim( sprintf( __( 'We received your inquiry %1$s – %2$s', 'gami-surgical' ), $ref, $site ) ), $reply, $headers );
	}

	do_action( 'gami_inquiry_received', $d, $id );

	if ( ! $id && ! $sent ) {
		return $fail( __( 'Sorry, your inquiry could not be sent. Please email or WhatsApp us directly.', 'gami-surgical' ) );
	}
	return array(
		'ok'        => true,
		'message'   => gami_option( 'inquiry_success' ),
		'reference' => $ref,
		'fields'    => array(),
	);
}

function gami_inquiry_ajax() {
	$result = gami_inquiry_process( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- public form, see spam checks.
	if ( $result['ok'] ) {
		wp_send_json_success(
			array(
				'message'   => $result['message'],
				'reference' => $result['reference'],
				'url'       => gami_inquiry_page_url( array( 'sent' => $result['reference'] ? $result['reference'] : '1' ) ),
			)
		);
	}
	wp_send_json_error(
		array(
			'message' => $result['message'],
			'fields'  => $result['fields'],
		),
		$result['fields'] ? 422 : 400
	);
}
add_action( 'wp_ajax_gami_inquiry', 'gami_inquiry_ajax' );
add_action( 'wp_ajax_nopriv_gami_inquiry', 'gami_inquiry_ajax' );

/**
 * Without JavaScript the form posts here, then returns to the inquiry page.
 */
function gami_inquiry_post() {
	$result = gami_inquiry_process( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( $result['ok'] ) {
		wp_safe_redirect( gami_inquiry_page_url( array( 'sent' => $result['reference'] ? $result['reference'] : '1' ) ) );
		exit;
	}
	set_transient( 'gami_inq_err_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' ), $result, 5 * MINUTE_IN_SECONDS );
	wp_safe_redirect( gami_inquiry_page_url( array( 'inquiry_error' => 1 ) ) . '#inquiry-form' );
	exit;
}
add_action( 'admin_post_gami_inquiry', 'gami_inquiry_post' );
add_action( 'admin_post_nopriv_gami_inquiry', 'gami_inquiry_post' );

/* -------------------------------------------------------------------------
 * Buttons and header link
 * ---------------------------------------------------------------------- */

function gami_inquiry_enabled_list() {
	return (bool) gami_option( 'inquiry_list' );
}

/**
 * Data a product row needs (used by buttons and the inquiry page).
 */
function gami_inquiry_item_data( $post_id ) {
	$image = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
	if ( ! $image && function_exists( 'wc_placeholder_img_src' ) ) {
		$image = wc_placeholder_img_src( 'thumbnail' );
	}
	return array(
		'id'    => (int) $post_id,
		'name'  => html_entity_decode( wp_strip_all_tags( get_the_title( $post_id ) ), ENT_QUOTES, 'UTF-8' ),
		'sku'   => (string) gami_catalogue_no( $post_id ),
		'image' => $image ? $image : '',
		'sizes' => implode( ', ', gami_product_sizes( $post_id ) ),
		'url'   => get_permalink( $post_id ),
	);
}

function gami_inquiry_data_attrs( $post_id ) {
	$out = '';
	foreach ( gami_inquiry_item_data( $post_id ) as $k => $v ) {
		$out .= ' data-inquiry-' . $k . '="' . esc_attr( $v ) . '"';
	}
	return $out;
}

/**
 * "Send inquiry" (a link to the inquiry page, works without JavaScript)
 * and "Add to inquiry list".
 *
 * @param int    $post_id Product ID.
 * @param string $context loop|single|band.
 */
function gami_inquiry_buttons( $post_id, $context = 'loop' ) {
	$attrs = gami_inquiry_data_attrs( $post_id );
	$name  = wp_strip_all_tags( get_the_title( $post_id ) );
	$html  = '<div class="inquiry-actions inquiry-actions--' . esc_attr( $context ) . '">';
	$html .= sprintf(
		'<a href="%1$s" class="button button--primary gs-inquire" data-inquiry-action="send"%2$s aria-label="%3$s" rel="nofollow">%4$s<span>%5$s</span></a>',
		esc_url( gami_inquiry_page_url( array( 'add' => $post_id ) ) ),
		$attrs,
		esc_attr( sprintf( /* translators: %s product */ __( 'Send inquiry about %s', 'gami-surgical' ), $name ) ),
		gami_icon( 'email', 18 ),
		esc_html( gami_option( 'inquiry_button' ) )
	);
	if ( gami_inquiry_enabled_list() ) {
		$html .= sprintf(
			'<button type="button" class="button button--ghost gs-add-list" data-inquiry-action="add"%1$s aria-pressed="false" aria-label="%2$s" title="%3$s">%4$s%5$s<span class="gs-add-list__text">%6$s</span></button>',
			$attrs,
			esc_attr( sprintf( /* translators: %s product */ __( 'Add %s to inquiry list', 'gami-surgical' ), $name ) ),
			esc_attr__( 'Add to inquiry list', 'gami-surgical' ),
			gami_icon( 'list-add', 18 ),
			gami_icon( 'check', 18 ),
			esc_html__( 'Add to inquiry list', 'gami-surgical' )
		);
	}
	return $html . '</div>';
}

/**
 * Header: link to the inquiry page with the item count.
 */
function gami_header_inquiry() {
	printf(
		'<a class="header-icon header-inquiry" href="%1$s" title="%2$s">%3$s<span class="screen-reader-text">%2$s</span><span class="header-cart__count gs-inquiry-count" aria-hidden="true" hidden>0</span></a>',
		esc_url( gami_inquiry_page_url() ),
		esc_attr__( 'Inquiry list', 'gami-surgical' ),
		gami_icon( 'inquiry', 22 ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/* -------------------------------------------------------------------------
 * The inquiry list and form (inquiry page, [gami_inquiry] shortcode)
 * ---------------------------------------------------------------------- */

/**
 * One product row. JavaScript builds the same markup from the saved list.
 */
function gami_inquiry_row( $item, $i, $qty = 1, $size = '' ) {
	$sizes = array_filter( array_map( 'trim', explode( ',', $item['sizes'] ) ) );
	?>
	<li class="gs-inq__item" data-id="<?php echo (int) $item['id']; ?>">
		<img class="gs-inq__img" src="<?php echo esc_url( $item['image'] ); ?>" alt="" width="72" height="72" loading="lazy">
		<div class="gs-inq__info">
			<a class="gs-inq__name" href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['name'] ); ?></a>
			<?php if ( $item['sku'] ) : ?>
				<span class="gs-inq__sku"><?php echo esc_html__( 'Cat. no.', 'gami-surgical' ) . ' ' . esc_html( $item['sku'] ); ?></span>
			<?php endif; ?>
			<input type="hidden" name="items[<?php echo (int) $i; ?>][id]" value="<?php echo (int) $item['id']; ?>">
			<div class="gs-inq__opts">
				<label class="gs-inq__opt">
					<span class="gs-inq__opt-label"><?php echo $sizes ? esc_html__( 'Size', 'gami-surgical' ) : esc_html__( 'Size (optional)', 'gami-surgical' ); ?></span>
					<?php if ( $sizes ) : ?>
						<select name="items[<?php echo (int) $i; ?>][size]">
							<option value=""><?php esc_html_e( 'Any / all sizes', 'gami-surgical' ); ?></option>
							<?php foreach ( $sizes as $s ) : ?>
								<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $size, $s ); ?>><?php echo esc_html( $s ); ?></option>
							<?php endforeach; ?>
						</select>
					<?php else : ?>
						<input type="text" name="items[<?php echo (int) $i; ?>][size]" value="<?php echo esc_attr( $size ); ?>" maxlength="60">
					<?php endif; ?>
				</label>
				<label class="gs-inq__opt gs-inq__opt--qty">
					<span class="gs-inq__opt-label"><?php esc_html_e( 'Quantity', 'gami-surgical' ); ?></span>
					<input type="number" name="items[<?php echo (int) $i; ?>][qty]" value="<?php echo (int) $qty; ?>" min="1" step="1" inputmode="numeric">
				</label>
			</div>
		</div>
	</li>
	<?php
}

/**
 * The inquiry app: success message, product list, form, contact details.
 */
function gami_inquiry_app() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only display parameters.
	$sent    = isset( $_GET['sent'] ) ? sanitize_text_field( wp_unslash( $_GET['sent'] ) ) : '';
	$add     = isset( $_GET['add'] ) ? absint( $_GET['add'] ) : 0;
	$message = isset( $_GET['message'] ) ? sanitize_textarea_field( wp_unslash( $_GET['message'] ) ) : '';
	$error   = null;
	if ( isset( $_GET['inquiry_error'] ) ) {
		$ip_key = 'gami_inq_err_' . md5( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' );
		$error  = get_transient( $ip_key );
		delete_transient( $ip_key );
	}
	// phpcs:enable
	$preload = ( $add && in_array( get_post_type( $add ), array( 'product', 'instrument' ), true ) && 'publish' === get_post_status( $add ) ) ? gami_inquiry_item_data( $add ) : null;
	$wa      = gami_whatsapp_url();
	$ref     = preg_match( '/^GS-\d+$/', $sent ) ? $sent : '';
	?>
	<div class="gs-inquiry<?php echo $sent ? ' is-sent' : ''; ?>" data-inquiry-app>

		<section class="gs-inquiry__done" <?php echo $sent ? '' : 'hidden'; ?> aria-labelledby="gs-inq-done-title">
			<div class="gs-inq__tick" aria-hidden="true"><svg viewBox="0 0 24 24" width="34" height="34"><path d="m5 12.5 4.5 4.5L19 7.5" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></div>
			<h2 class="gs-inquiry__done-title" id="gs-inq-done-title" tabindex="-1"><?php esc_html_e( 'Thank you, your inquiry is on its way', 'gami-surgical' ); ?></h2>
			<p class="gs-inquiry__done-text"><?php echo esc_html( gami_option( 'inquiry_success' ) ); ?></p>
			<p class="gs-inquiry__ref" <?php echo $ref ? '' : 'hidden'; ?>><?php esc_html_e( 'Your reference:', 'gami-surgical' ); ?> <strong><?php echo esc_html( $ref ); ?></strong></p>
			<div class="gs-inquiry__done-actions">
				<a class="button button--primary" href="<?php echo esc_url( gami_catalog_url() ); ?>"><?php esc_html_e( 'Continue browsing', 'gami-surgical' ); ?></a>
				<?php if ( $wa ) : ?>
					<a class="button button--ghost" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Follow up on WhatsApp', 'gami-surgical' ); ?></a>
				<?php endif; ?>
			</div>
		</section>

		<div class="gs-inquiry__layout" <?php echo $sent ? 'hidden' : ''; ?>>
			<form class="gs-inquiry__main gs-inq__form" id="inquiry-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
				<input type="hidden" name="action" value="gami_inquiry">
				<input type="hidden" name="ts" value="<?php echo esc_attr( gami_inquiry_time_token() ); ?>">
				<input type="hidden" name="source" value="<?php echo esc_url( gami_inquiry_page_url() ); ?>">

				<section class="gs-inquiry__card" aria-labelledby="gs-inq-items-title">
					<div class="gs-inquiry__card-head">
						<h2 class="gs-inquiry__h" id="gs-inq-items-title"><span class="gs-inquiry__step" aria-hidden="true">1</span><?php esc_html_e( 'Products', 'gami-surgical' ); ?></h2>
						<p class="gs-inq__count" aria-live="polite"></p>
					</div>
					<ul class="gs-inq__items" data-preload="<?php echo $preload ? esc_attr( wp_json_encode( $preload ) ) : ''; ?>">
						<?php
						if ( $preload ) {
							gami_inquiry_row( $preload, 0 );
						}
						?>
					</ul>
					<div class="gs-inq__empty" <?php echo $preload ? 'hidden' : ''; ?>>
						<?php echo gami_icon( 'inquiry', 32 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<div>
							<p class="gs-inq__empty-title"><?php esc_html_e( 'Your inquiry list is empty', 'gami-surgical' ); ?></p>
							<p><?php esc_html_e( 'Add products from the catalogue, or describe what you need in the message below.', 'gami-surgical' ); ?></p>
						</div>
					</div>
					<p class="gs-inq__more"><a href="<?php echo esc_url( gami_catalog_url() ); ?>"><?php esc_html_e( '+ Add more products', 'gami-surgical' ); ?></a></p>
				</section>

				<section class="gs-inquiry__card" aria-labelledby="gs-inq-details-title">
					<div class="gs-inquiry__card-head">
						<h2 class="gs-inquiry__h" id="gs-inq-details-title"><span class="gs-inquiry__step" aria-hidden="true">2</span><?php esc_html_e( 'Your details', 'gami-surgical' ); ?></h2>
					</div>
					<?php if ( gami_option( 'inquiry_intro' ) ) : ?>
						<p class="gs-inq__intro"><?php echo esc_html( gami_option( 'inquiry_intro' ) ); ?></p>
					<?php endif; ?>

					<div class="gs-inq__error" role="alert" <?php echo $error ? '' : 'hidden'; ?>><?php echo $error ? esc_html( $error['message'] ) : ''; ?></div>

					<div class="gs-inq__grid">
						<?php
						$fields = array(
							'name'    => array( __( 'Your name', 'gami-surgical' ), 'text', 'name', true, false ),
							'email'   => array( __( 'Email', 'gami-surgical' ), 'email', 'email', true, false ),
							'company' => array( __( 'Company / hospital', 'gami-surgical' ), 'text', 'organization', false, false ),
							'country' => array( __( 'Country', 'gami-surgical' ), 'text', 'country-name', false, false ),
							'phone'   => array( __( 'Phone / WhatsApp', 'gami-surgical' ), 'tel', 'tel', false, true ),
						);
						foreach ( $fields as $key => $f ) :
							$err = $error && isset( $error['fields'][ $key ] ) ? $error['fields'][ $key ] : '';
							?>
							<p class="gs-field<?php echo $f[4] ? ' gs-field--wide' : ''; ?>">
								<label for="gs-inq-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $f[0] ); ?><?php echo $f[3] ? ' <span class="gs-req" aria-hidden="true">*</span>' : ' <span class="gs-opt">' . esc_html__( '(optional)', 'gami-surgical' ) . '</span>'; ?></label>
								<input id="gs-inq-<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>" type="<?php echo esc_attr( $f[1] ); ?>" autocomplete="<?php echo esc_attr( $f[2] ); ?>"<?php echo $f[3] ? ' required aria-required="true"' : ''; ?><?php echo $err ? ' aria-invalid="true" aria-describedby="gs-err-' . esc_attr( $key ) . '"' : ''; ?>>
								<?php if ( $err ) : ?>
									<span class="gs-field__error" id="gs-err-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $err ); ?></span>
								<?php endif; ?>
							</p>
						<?php endforeach; ?>
						<p class="gs-field gs-field--wide">
							<label for="gs-inq-message"><?php esc_html_e( 'Message', 'gami-surgical' ); ?> <span class="gs-opt"><?php esc_html_e( '(optional when products are listed)', 'gami-surgical' ); ?></span></label>
							<textarea id="gs-inq-message" name="message" rows="4" placeholder="<?php esc_attr_e( 'Quantities, sizes, packing, logo marking, delivery date…', 'gami-surgical' ); ?>"><?php echo esc_textarea( $message ); ?></textarea>
						</p>
						<p class="gs-hp" aria-hidden="true">
							<label for="gs-inq-website">Website</label>
							<input id="gs-inq-website" name="website" type="text" tabindex="-1" autocomplete="off">
						</p>
					</div>

					<div class="gs-inq__actions">
						<button type="submit" class="button button--primary gs-inq__submit"><?php esc_html_e( 'Send inquiry', 'gami-surgical' ); ?></button>
						<p class="gs-inq__privacy"><?php esc_html_e( 'We only use your details to answer this inquiry.', 'gami-surgical' ); ?></p>
					</div>
				</section>
			</form>

			<aside class="gs-inquiry__aside" aria-label="<?php esc_attr_e( 'How it works and contact', 'gami-surgical' ); ?>">
				<div class="gs-inquiry__card gs-inquiry__card--dark">
					<h2 class="gs-inquiry__aside-title"><?php esc_html_e( 'What happens next', 'gami-surgical' ); ?></h2>
					<ol class="gs-next">
						<li><strong><?php esc_html_e( 'You send your list', 'gami-surgical' ); ?></strong><span><?php esc_html_e( 'Products, sizes and quantities, or just a description.', 'gami-surgical' ); ?></span></li>
						<li><strong><?php esc_html_e( 'We reply by email', 'gami-surgical' ); ?></strong><span><?php esc_html_e( 'With prices, lead time and shipping to your country.', 'gami-surgical' ); ?></span></li>
						<li><strong><?php esc_html_e( 'You confirm', 'gami-surgical' ); ?></strong><span><?php esc_html_e( 'We agree the details and arrange production and delivery.', 'gami-surgical' ); ?></span></li>
					</ol>
				</div>
				<div class="gs-inquiry__card">
					<h2 class="gs-inquiry__aside-title"><?php esc_html_e( 'Prefer to talk?', 'gami-surgical' ); ?></h2>
					<ul class="gs-inquiry__contact">
						<?php if ( $wa ) : ?>
							<li><a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php echo gami_icon( 'whatsapp', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'Chat on WhatsApp', 'gami-surgical' ); ?></span></a></li>
						<?php endif; ?>
						<?php if ( gami_option( 'phone' ) ) : ?>
							<li><?php echo gami_icon( 'phone', 18 ) . gami_phone_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
							<?php if ( gami_option( 'phone2' ) ) : ?>
								<li><?php echo gami_icon( 'phone', 18 ) . gami_phone_link( 'phone2' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
							<?php endif; ?>
						<?php endif; ?>
						<?php if ( gami_option( 'email' ) ) : ?>
							<li><?php echo gami_icon( 'email', 18 ) . gami_email_link(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
						<?php endif; ?>
						<?php if ( gami_option( 'hours' ) ) : ?>
							<li><?php echo gami_icon( 'clock', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( gami_option( 'hours' ) ); ?></span></li>
						<?php endif; ?>
					</ul>
				</div>
			</aside>
		</div>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Assets and the "added" toast
 * ---------------------------------------------------------------------- */

function gami_inquiry_assets() {
	wp_enqueue_style( 'gami-inquiry', GAMI_URI . '/assets/css/inquiry.css', array( 'gami-main' ), GAMI_VERSION );
	wp_enqueue_script(
		'gami-inquiry',
		GAMI_URI . '/assets/js/inquiry.js',
		array(),
		GAMI_VERSION,
		array(
			'strategy'  => 'defer',
			'in_footer' => true,
		)
	);
	wp_localize_script(
		'gami-inquiry',
		'gamiInquiry',
		array(
			'ajax' => admin_url( 'admin-ajax.php' ),
			'page' => gami_inquiry_page_url(),
			'i18n' => array(
				'added'    => __( 'Added to your inquiry list', 'gami-surgical' ),
				'already'  => __( 'Already in your inquiry list', 'gami-surgical' ),
				'updated'  => __( 'Quantity updated in your inquiry list', 'gami-surgical' ),
				'view'     => __( 'View list', 'gami-surgical' ),
				'remove'   => __( 'Remove', 'gami-surgical' ),
				'qty'      => __( 'Quantity', 'gami-surgical' ),
				'size'     => __( 'Size', 'gami-surgical' ),
				'anySize'  => __( 'Any / all sizes', 'gami-surgical' ),
				'sizeText' => __( 'Size (optional)', 'gami-surgical' ),
				'products' => __( '%d products', 'gami-surgical' ),
				'product'  => __( '1 product', 'gami-surgical' ),
				'sending'  => __( 'Sending…', 'gami-surgical' ),
				'error'    => __( 'Sorry, something went wrong. Please try again, or contact us by email or WhatsApp.', 'gami-surgical' ),
				'catNo'    => __( 'Cat. no.', 'gami-surgical' ),
				'removed'  => __( '%s removed from your list', 'gami-surgical' ),
				'undo'     => __( 'Undo', 'gami-surgical' ),
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'gami_inquiry_assets', 30 );

function gami_inquiry_toast() {
	echo '<div class="gs-toast" role="status" aria-live="polite" hidden></div>';
}
add_action( 'wp_footer', 'gami_inquiry_toast', 5 );
