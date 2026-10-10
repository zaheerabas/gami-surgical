<?php
/**
 * Footer (Appearance > Customize > Gami Surgical > Footer).
 *
 *  1. Call-to-action card: "Looking for a specific instrument?" + buttons.
 *  2. Highlights row (the "points" from Customize > Inquiries).
 *  3. Main area: brand + contact | one column per main product category |
 *     Company (Footer menu).
 *  4. Bottom bar: copyright, Legal menu, back to top.
 *
 * Widget areas (Appearance > Widgets) replace parts when they have widgets:
 * Footer column 1 = brand column, column 2 = the category columns,
 * column 3 = Company column, column 4 = an extra column at the end.
 * An Elementor Pro Theme Builder footer replaces all of this.
 *
 * @package Gami_Surgical
 */

$gami_wrap   = gami_wrap_class( 'footer' );
$gami_cats   = gami_option( 'footer_cat_columns' ) ? array_slice( gami_catalog_terms(), 0, 4 ) : array();
$gami_points = gami_option( 'footer_points_show' ) ? array_slice( array_filter( array_map( 'trim', explode( "\n", (string) gami_option( 'inquiry_points' ) ) ) ), 0, 4 ) : array();
$gami_wa_url = gami_whatsapp_url();
?>
</div><!-- #content -->

<?php if ( ! gami_elementor_location( 'footer' ) ) : ?>
	<footer class="site-footer<?php echo gami_option( 'footer_cta_show' ) ? ' site-footer--has-cta' : ''; ?><?php echo ( gami_option( 'whatsapp_float' ) && $gami_wa_url ) ? ' site-footer--has-float' : ''; ?>">

		<?php if ( gami_option( 'footer_cta_show' ) ) : ?>
			<div class="<?php echo esc_attr( $gami_wrap ); ?> footer-cta-wrap">
				<section class="footer-cta" aria-labelledby="footer-cta-title">
					<div class="footer-cta__text">
						<h2 class="footer-cta__title" id="footer-cta-title"><?php echo esc_html( gami_option( 'footer_cta_title' ) ); ?></h2>
						<?php if ( gami_option( 'footer_cta_text' ) ) : ?>
							<p class="footer-cta__sub"><?php echo esc_html( gami_option( 'footer_cta_text' ) ); ?></p>
						<?php endif; ?>
					</div>
					<div class="footer-cta__actions">
						<a class="button button--primary footer-cta__btn" href="<?php echo esc_url( gami_quote_url() ); ?>"><?php echo gami_icon( 'email', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html( gami_option( 'inquiry_button' ) ); ?></span></a>
						<?php if ( $gami_wa_url ) : ?>
							<a class="footer-cta__wa" href="<?php echo esc_url( $gami_wa_url ); ?>" target="_blank" rel="noopener"><?php echo gami_icon( 'whatsapp', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php esc_html_e( 'WhatsApp', 'gami-surgical' ); ?></span></a>
						<?php endif; ?>
					</div>
				</section>
			</div>
		<?php endif; ?>

		<?php if ( $gami_points ) : ?>
			<div class="<?php echo esc_attr( $gami_wrap ); ?>">
				<ul class="footer-points footer-points--<?php echo (int) count( $gami_points ); ?>">
					<?php foreach ( $gami_points as $gami_point ) : ?>
						<li><span class="footer-points__icon"><?php echo gami_icon( 'check', 18 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span><span><?php echo esc_html( $gami_point ); ?></span></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="<?php echo esc_attr( $gami_wrap ); ?> site-footer__grid site-footer__grid--v2" style="--footer-cat-cols: <?php echo (int) ( is_active_sidebar( 'footer-2' ) ? 1 : count( $gami_cats ) ); ?>">
			<div class="footer-col footer-col--1 footer-brand-col">
				<?php
				if ( is_active_sidebar( 'footer-1' ) ) {
					dynamic_sidebar( 'footer-1' );
				} else {
					gami_footer_brand();
				}
				?>
			</div>

			<?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
				<div class="footer-col footer-col--2"><?php dynamic_sidebar( 'footer-2' ); ?></div>
			<?php else : ?>
				<?php foreach ( $gami_cats as $gami_i => $gami_cat ) : ?>
					<div class="footer-col footer-col--cat footer-col--cat-<?php echo (int) $gami_i; ?>">
						<?php gami_footer_category_column( $gami_cat ); ?>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
				<div class="footer-col footer-col--3"><?php dynamic_sidebar( 'footer-3' ); ?></div>
			<?php elseif ( gami_option( 'footer_links_show' ) ) : ?>
				<div class="footer-col footer-col--3 footer-col--links"><?php gami_footer_default_column( 4 ); ?></div>
			<?php endif; ?>

			<?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
				<div class="footer-col footer-col--4"><?php dynamic_sidebar( 'footer-4' ); ?></div>
			<?php endif; ?>
		</div>

		<?php if ( gami_option( 'footer_bottom_show' ) ) : ?>
			<div class="site-footer__bottom-bar">
				<div class="<?php echo esc_attr( $gami_wrap ); ?> site-footer__bottom">
					<p class="copyright"><?php echo esc_html( gami_copyright_text() ); ?></p>
					<?php
					wp_nav_menu(
						array(
							'theme_location'       => 'legal',
							'container'            => 'nav',
							'container_class'      => 'legal-nav',
							'container_aria_label' => __( 'Legal', 'gami-surgical' ),
							'depth'                => 1,
							'fallback_cb'          => false,
						)
					);
					?>
					<a class="footer-top-link" href="#"><?php esc_html_e( 'Back to top', 'gami-surgical' ); ?> <span aria-hidden="true">↑</span></a>
				</div>
			</div>
		<?php endif; ?>
	</footer>
<?php endif; ?>

<?php
$gami_wa = gami_option( 'whatsapp_float' ) ? gami_whatsapp_url() : '';
if ( $gami_wa ) :
	?>
	<a class="whatsapp-float whatsapp-float--<?php echo esc_attr( 'left' === gami_option( 'whatsapp_float_side' ) ? 'left' : 'right' ); ?>" href="<?php echo esc_url( $gami_wa ); ?>" target="_blank" rel="noopener">
		<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path fill="currentColor" d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2a8.2 8.2 0 0 1-4.2-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.1-1.2l-.4-.2Z"/></svg>
		<span><?php echo esc_html( gami_option( 'whatsapp_float_label' ) ? gami_option( 'whatsapp_float_label' ) : __( 'WhatsApp', 'gami-surgical' ) ); ?></span>
	</a>
<?php endif; ?>

<?php if ( gami_option( 'back_to_top' ) ) : ?>
	<a class="back-to-top back-to-top--<?php echo esc_attr( 'left' === gami_option( 'whatsapp_float_side' ) && $gami_wa ? 'right' : ( $gami_wa ? 'left' : 'right' ) ); ?>" href="#" hidden>
		<?php echo gami_icon( 'arrow-up', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<span class="screen-reader-text"><?php esc_html_e( 'Back to top', 'gami-surgical' ); ?></span>
	</a>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>
