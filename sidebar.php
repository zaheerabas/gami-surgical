<?php
/**
 * Blog sidebar.
 *
 * @package Gami_Surgical
 */

if ( ! is_active_sidebar( 'sidebar-blog' ) ) {
	return;
}
?>
<aside id="secondary" class="widget-area layout-aside" aria-label="<?php esc_attr_e( 'Sidebar', 'gami-surgical' ); ?>">
	<?php dynamic_sidebar( 'sidebar-blog' ); ?>
</aside>
