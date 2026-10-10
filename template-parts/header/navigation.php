<?php
/**
 * Primary navigation. On desktop it is the menu bar (with mega menus);
 * below the mobile breakpoint it becomes the slide-in drawer.
 *
 * @package Gami_Surgical
 */

$gami_cta = ! empty( $args['cta'] );
?>
<nav id="site-navigation" class="main-navigation" aria-label="<?php esc_attr_e( 'Primary', 'gami-surgical' ); ?>">
	<div class="drawer-head">
		<span class="drawer-head__title"><?php esc_html_e( 'Menu', 'gami-surgical' ); ?></span>
		<button class="drawer-close" type="button" data-close-menu>
			<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
			<span class="screen-reader-text"><?php esc_html_e( 'Close menu', 'gami-surgical' ); ?></span>
		</button>
	</div>
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'menu_id'        => 'primary-menu',
			'menu_class'     => 'menu primary-menu',
			'container'      => false,
			'fallback_cb'    => 'gami_menu_fallback',
		)
	);
	?>
	<?php if ( $gami_cta ) : ?>
		<a class="button button--primary header-cta header-cta--drawer" href="<?php echo esc_url( gami_header_cta_url() ); ?>"><?php echo esc_html( gami_option( 'header_cta_text' ) ); ?></a>
	<?php endif; ?>
	<?php gami_drawer_contact(); ?>
</nav>
<div class="nav-overlay" data-close-menu hidden></div>
