<?php
/**
 * Search form.
 *
 * @package Gami_Surgical
 */

$gami_sid = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $gami_sid ); ?>"><?php esc_html_e( 'Search', 'gami-surgical' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $gami_sid ); ?>" class="search-field" placeholder="<?php esc_attr_e( 'Instrument name or catalogue no.', 'gami-surgical' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	<button type="submit" class="button button--primary"><?php esc_html_e( 'Search', 'gami-surgical' ); ?></button>
</form>
