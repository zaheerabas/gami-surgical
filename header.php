<?php
/**
 * Header. Set in Appearance > Customize > Gami Surgical (Top bar, Header,
 * Search, Mega menu). An Elementor Pro Theme Builder header replaces it.
 *
 * Desktop "two rows": logo | search bar | buttons, then the menu bar.
 * Desktop "one row":  logo | menu | icons (search opens from an icon).
 * Below the mobile breakpoint: logo | icons | menu button, search bar under it,
 * and the menu slides in from the side.
 *
 * @package Gami_Surgical
 */

$gami_layout = 'inline' === gami_option( 'header_layout' ) ? 'inline' : 'stacked';
$gami_search = gami_search_mode();
// In the one-row layout there is no room for a bar, so it opens from an icon.
$gami_search_icon = 'icon' === $gami_search || ( 'bar' === $gami_search && 'inline' === $gami_layout );
$gami_search_bar  = 'bar' === $gami_search && 'stacked' === $gami_layout;

$gami_header_classes = array(
	'site-header',
	'site-header--' . $gami_layout,
	'site-header--nav-' . sanitize_html_class( gami_option( 'header_nav_align' ) ),
);
if ( $gami_search_bar ) {
	$gami_header_classes[] = 'site-header--has-search';
}
if ( gami_option( 'header_sticky' ) ) {
	$gami_header_classes[] = 'site-header--sticky';
	if ( $gami_search_bar && gami_option( 'mobile_header_condense' ) ) {
		$gami_header_classes[] = 'site-header--condense';
	}
	if ( gami_option( 'header_shrink' ) ) {
		$gami_header_classes[] = 'site-header--shrink';
	}
}
$gami_cta = gami_option( 'header_cta_show' ) && gami_option( 'header_cta_text' );
?><!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'gami-surgical' ); ?></a>

<?php if ( ! gami_elementor_location( 'header' ) ) : ?>

	<?php if ( gami_option( 'topbar_show' ) ) : ?>
		<div class="topbar">
			<div class="<?php echo esc_attr( gami_wrap_class( 'header' ) ); ?> topbar__inner">
				<p class="topbar__text"><?php echo esc_html( gami_option( 'topbar_text' ) ); ?></p>
				<div class="topbar__contact">
					<?php
					if ( gami_option( 'topbar_phone' ) && gami_option( 'phone' ) ) {
						gami_contact_chip( 'phone' );
					}
					if ( gami_option( 'topbar_phone' ) && gami_option( 'phone2' ) ) {
						gami_contact_chip( 'phone2' );
					}
					if ( gami_option( 'topbar_email' ) && gami_option( 'email' ) ) {
						gami_contact_chip( 'email' );
					}
					if ( gami_option( 'topbar_social' ) ) {
						gami_social_links( 'social-links--topbar' );
					}
					?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<header class="<?php echo esc_attr( implode( ' ', $gami_header_classes ) ); ?>" id="masthead">
		<div class="<?php echo esc_attr( gami_wrap_class( 'header' ) ); ?> site-header__inner">
			<div class="site-branding">
				<?php gami_logo(); ?>
			</div>

			<?php if ( $gami_search_bar ) : ?>
				<div class="site-header__search">
					<?php gami_search_bar( 'gs-header-search' ); ?>
				</div>
			<?php endif; ?>

			<?php
			if ( 'inline' === $gami_layout ) {
				get_template_part( 'template-parts/header/navigation', null, array( 'cta' => $gami_cta ) );
			}
			?>

			<div class="header-icons">
				<?php if ( $gami_search_icon ) : ?>
					<button class="header-icon header-search-toggle" type="button" aria-controls="header-search" aria-expanded="false" title="<?php esc_attr_e( 'Search', 'gami-surgical' ); ?>">
						<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m15 15 5.5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
						<span class="screen-reader-text"><?php esc_html_e( 'Search', 'gami-surgical' ); ?></span>
					</button>
				<?php endif; ?>
				<?php gami_header_inquiry(); ?>
				<?php if ( $gami_cta && 'stacked' === $gami_layout ) : ?>
					<a class="button button--primary header-cta header-cta--bar" href="<?php echo esc_url( gami_header_cta_url() ); ?>"><?php echo esc_html( gami_option( 'header_cta_text' ) ); ?><span class="header-cta__count gs-inquiry-count" aria-hidden="true" hidden>0</span></a>
				<?php endif; ?>
				<button class="menu-toggle" type="button" aria-controls="site-navigation" aria-expanded="false">
					<span class="menu-toggle__bars" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'gami-surgical' ); ?></span>
				</button>
			</div>
		</div>

		<?php if ( 'stacked' === $gami_layout ) : ?>
			<div class="site-header__nav">
				<div class="<?php echo esc_attr( gami_wrap_class( 'header' ) ); ?> site-header__nav-inner">
					<?php get_template_part( 'template-parts/header/navigation', null, array( 'cta' => $gami_cta ) ); ?>
					<?php if ( gami_option( 'header_nav_phone' ) && ( gami_option( 'phone' ) || gami_option( 'hours' ) ) ) : ?>
						<div class="nav-aside">
							<?php if ( gami_option( 'phone' ) ) : ?>
								<?php gami_contact_chip( 'phone' ); ?>
							<?php endif; ?>
							<?php if ( gami_option( 'hours' ) ) : ?>
								<span class="nav-aside__hours"><?php echo esc_html( gami_option( 'hours' ) ); ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<?php if ( $gami_search_icon ) : ?>
			<div class="header-search" id="header-search" hidden>
				<div class="<?php echo esc_attr( gami_wrap_class( 'header' ) ); ?> header-search__row">
					<?php gami_search_bar( 'gs-panel-search' ); ?>
					<button class="header-search__close" type="button" data-close-search>
						<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
						<span class="screen-reader-text"><?php esc_html_e( 'Close search', 'gami-surgical' ); ?></span>
					</button>
				</div>
			</div>
		<?php endif; ?>
	</header>
<?php endif; ?>

<div id="content" class="site-content">
