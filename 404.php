<?php
/**
 * 404.
 *
 * @package Gami_Surgical
 */

get_header();

if ( ! gami_elementor_location( 'single' ) ) :
	?>
	<main id="primary" class="site-main container error-404">
		<h1 class="page-title"><?php esc_html_e( 'This page isn’t in the catalogue', 'gami-surgical' ); ?></h1>
		<p><?php esc_html_e( 'The link may be old or the instrument renamed. Search by name or catalogue number, or browse the full range.', 'gami-surgical' ); ?></p>
		<?php get_search_form(); ?>
		<p>
			<?php if ( gami_wc_active() || post_type_exists( 'instrument' ) ) : ?>
				<a class="button button--primary" href="<?php echo esc_url( gami_catalog_url() ); ?>"><?php esc_html_e( 'Browse products', 'gami-surgical' ); ?></a>
			<?php endif; ?>
			<a class="button button--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the homepage', 'gami-surgical' ); ?></a>
		</p>
	</main>
	<?php
endif;

get_footer();
