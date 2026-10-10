<?php
/**
 * Quote request form: markup, shortcode and handler.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the quote form.
 *
 * @param array $args { instrument_id, title, show_note }.
 */
function gami_quote_form( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'instrument_id' => 0, // Instrument or WooCommerce product ID.
			'title'         => __( 'Request a quote', 'gami-surgical' ),
			'show_note'     => true,
		)
	);

	$instrument_id = absint( $args['instrument_id'] );
	if ( ! $instrument_id && isset( $_GET['instrument'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$instrument_id = absint( $_GET['instrument'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}
	$instrument_name = '';
	if ( $instrument_id && in_array( get_post_type( $instrument_id ), array( 'instrument', 'product' ), true ) ) {
		$instrument_name = wp_strip_all_tags( get_the_title( $instrument_id ) );
		$cat_no          = gami_catalogue_no( $instrument_id );
		if ( $cat_no ) {
			$instrument_name .= ' (' . $cat_no . ')';
		}
	}

	$status = isset( $_GET['quote'] ) ? sanitize_key( $_GET['quote'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$uid    = wp_unique_id( 'gq-' );

	ob_start();
	?>
	<div class="quote-form-wrap" id="quote">
		<?php if ( $args['title'] ) : ?>
			<h2 class="quote-form__title"><?php echo esc_html( $args['title'] ); ?></h2>
		<?php endif; ?>

		<?php if ( 'sent' === $status ) : ?>
			<p class="notice notice--success" role="status"><?php esc_html_e( 'Quote request sent. We reply within one working day with prices, lead time and shipping options.', 'gami-surgical' ); ?></p>
		<?php elseif ( 'invalid' === $status ) : ?>
			<p class="notice notice--error" role="alert"><?php esc_html_e( 'Add your name, a valid email and the instruments you need, then send again.', 'gami-surgical' ); ?></p>
		<?php elseif ( 'error' === $status ) : ?>
			<p class="notice notice--error" role="alert"><?php esc_html_e( 'The request could not be emailed. Write to us directly or send it on WhatsApp.', 'gami-surgical' ); ?></p>
		<?php endif; ?>

		<form class="quote-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="gami_quote">
			<input type="hidden" name="return_to" value="<?php echo esc_url( get_permalink() ? get_permalink() : home_url( '/' ) ); ?>">
			<?php wp_nonce_field( 'gami_quote', 'gami_quote_nonce' ); ?>
			<p class="hp-field" aria-hidden="true">
				<label for="<?php echo esc_attr( $uid ); ?>-website">Website</label>
				<input type="text" id="<?php echo esc_attr( $uid ); ?>-website" name="website" tabindex="-1" autocomplete="off">
			</p>

			<div class="field-row">
				<p class="field">
					<label for="<?php echo esc_attr( $uid ); ?>-name"><?php esc_html_e( 'Your name', 'gami-surgical' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $uid ); ?>-name" name="name" required autocomplete="name">
				</p>
				<p class="field">
					<label for="<?php echo esc_attr( $uid ); ?>-company"><?php esc_html_e( 'Company or hospital', 'gami-surgical' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $uid ); ?>-company" name="company" autocomplete="organization">
				</p>
			</div>
			<div class="field-row">
				<p class="field">
					<label for="<?php echo esc_attr( $uid ); ?>-email"><?php esc_html_e( 'Email', 'gami-surgical' ); ?></label>
					<input type="email" id="<?php echo esc_attr( $uid ); ?>-email" name="email" required autocomplete="email">
				</p>
				<p class="field">
					<label for="<?php echo esc_attr( $uid ); ?>-country"><?php esc_html_e( 'Country', 'gami-surgical' ); ?></label>
					<input type="text" id="<?php echo esc_attr( $uid ); ?>-country" name="country" autocomplete="country-name">
				</p>
			</div>
			<p class="field">
				<label for="<?php echo esc_attr( $uid ); ?>-items"><?php esc_html_e( 'Instruments, sizes and quantities', 'gami-surgical' ); ?></label>
				<textarea id="<?php echo esc_attr( $uid ); ?>-items" name="items" rows="5" required placeholder="<?php esc_attr_e( 'Example: Kelly hemostat, curved, 14 cm, 200 pcs', 'gami-surgical' ); ?>"><?php echo $instrument_name ? esc_textarea( $instrument_name . ', ' ) : ''; ?></textarea>
			</p>
			<fieldset class="field field--choices">
				<legend><?php esc_html_e( 'You need', 'gami-surgical' ); ?></legend>
				<label><input type="checkbox" name="needs[]" value="samples"> <?php esc_html_e( 'Samples first', 'gami-surgical' ); ?></label>
				<label><input type="checkbox" name="needs[]" value="private-label"> <?php esc_html_e( 'Private label / custom marking', 'gami-surgical' ); ?></label>
				<label><input type="checkbox" name="needs[]" value="catalogue"> <?php esc_html_e( 'Full PDF catalogue', 'gami-surgical' ); ?></label>
			</fieldset>
			<p class="field-actions">
				<button type="submit" class="button button--primary"><?php esc_html_e( 'Send quote request', 'gami-surgical' ); ?></button>
				<?php
				$wa = gami_whatsapp_url( $instrument_name ? sprintf( /* translators: %s instrument */ __( 'Quote request: %s', 'gami-surgical' ), $instrument_name ) : '' );
				if ( $wa ) :
					?>
					<a class="button button--ghost" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ask on WhatsApp', 'gami-surgical' ); ?></a>
				<?php endif; ?>
			</p>
			<?php if ( $args['show_note'] && gami_option( 'moq' ) ) : ?>
				<p class="quote-form__note"><?php echo esc_html( gami_option( 'moq' ) ); ?></p>
			<?php endif; ?>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

function gami_quote_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'instrument' => 0,
			'product'    => 0,
			'title'      => __( 'Request a quote', 'gami-surgical' ),
		),
		$atts,
		'gami_quote_form'
	);
	return gami_quote_form(
		array(
			'instrument_id' => $atts['product'] ? $atts['product'] : $atts['instrument'],
			'title'         => $atts['title'],
		)
	);
}
add_shortcode( 'gami_quote_form', 'gami_quote_shortcode' );

/**
 * Handle submissions.
 */
function gami_handle_quote() {
	$return = isset( $_POST['return_to'] ) ? esc_url_raw( wp_unslash( $_POST['return_to'] ) ) : home_url( '/' );
	$return = wp_validate_redirect( $return, home_url( '/' ) );
	$return = remove_query_arg( 'quote', $return );

	$fail = function ( $code ) use ( $return ) {
		wp_safe_redirect( add_query_arg( 'quote', $code, $return ) . '#quote' );
		exit;
	};

	if ( ! isset( $_POST['gami_quote_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['gami_quote_nonce'] ) ), 'gami_quote' ) ) {
		$fail( 'invalid' );
	}
	// Honeypot: bots fill hidden fields. Pretend success.
	if ( ! empty( $_POST['website'] ) ) {
		$fail( 'sent' );
	}

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$company = isset( $_POST['company'] ) ? sanitize_text_field( wp_unslash( $_POST['company'] ) ) : '';
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$country = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '';
	$items   = isset( $_POST['items'] ) ? sanitize_textarea_field( wp_unslash( $_POST['items'] ) ) : '';
	$needs   = isset( $_POST['needs'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['needs'] ) ) : array();

	if ( ! $name || ! is_email( $email ) || ! $items ) {
		$fail( 'invalid' );
	}

	$to = gami_option( 'email' );
	if ( ! is_email( $to ) || 'sales@example.com' === $to ) {
		$to = get_option( 'admin_email' );
	}

	/* translators: 1: name, 2: company */
	$subject = sprintf( __( 'Quote request from %1$s %2$s', 'gami-surgical' ), $name, $company ? '(' . $company . ')' : '' );
	$body    = implode(
		"\n",
		array(
			'Name: ' . $name,
			'Company: ' . $company,
			'Email: ' . $email,
			'Country: ' . $country,
			'Needs: ' . ( $needs ? implode( ', ', $needs ) : '-' ),
			'',
			'Instruments:',
			$items,
			'',
			'Sent from: ' . $return,
		)
	);
	$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );

	do_action( 'gami_quote_received', compact( 'name', 'company', 'email', 'country', 'items', 'needs' ) );

	$sent = wp_mail( $to, trim( $subject ), $body, $headers );
	$fail( $sent ? 'sent' : 'error' );
}
add_action( 'admin_post_gami_quote', 'gami_handle_quote' );
add_action( 'admin_post_nopriv_gami_quote', 'gami_handle_quote' );
