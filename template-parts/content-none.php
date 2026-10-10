<?php
/**
 * Nothing found.
 *
 * @package Gami_Surgical
 */

?>
<section class="no-results">
	<?php if ( ! is_search() ) : ?>
		<h2><?php esc_html_e( 'Nothing here yet', 'gami-surgical' ); ?></h2>
	<?php endif; ?>
	<?php if ( is_search() ) : ?>
		<?php gami_no_results_help(); ?>
	<?php elseif ( current_user_can( 'publish_posts' ) ) : ?>
		<p>
			<?php
			printf(
				/* translators: %s: link to add new product */
				wp_kses_post( __( 'Ready to add content? <a href="%s">Add your first product</a>.', 'gami-surgical' ) ),
				esc_url( admin_url( 'post-new.php?post_type=' . gami_catalog_post_type() ) )
			);
			?>
		</p>
	<?php else : ?>
		<p><?php esc_html_e( 'This section is being updated. Contact us for the current catalogue.', 'gami-surgical' ); ?></p>
	<?php endif; ?>
</section>
