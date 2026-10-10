<?php
/**
 * Customizer: brand, top bar, header, footer, social links, contact details, shop.
 *
 * Appearance > Customize > Gami Surgical.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every theme option with its default value.
 */
function gami_customizer_defaults() {
	return array(
		// Brand.
		'accent'               => '#CB982C',
		'animations'           => true,
		'container_width'      => 1400,
		'certifications'       => '',

		// Top bar.
		'topbar_show'          => true,
		'topbar_text'          => '',
		'topbar_phone'         => true,
		'topbar_email'         => true,
		'topbar_social'        => false,
		'topbar_bg'            => '#0C1520',
		'topbar_color'         => '#C5D0D8',

		// Header.
		'header_width'         => 'contained',
		'header_layout'        => 'stacked',
		'mobile_breakpoint'    => 992,
		'header_nav_bg'        => '#0F1A26',
		'header_nav_phone'     => false,
		'header_sticky'        => true,
		'header_shrink'        => true,
		'mobile_header_condense' => true,
		'drawer_contact'       => true,
		'header_nav_align'     => 'left',
		'header_bg'            => '#13202E',
		'header_link'          => '#E4E9ED',
		'header_link_hover'    => '#CB982C',
		'header_border_color'  => '#CB982C',
		'header_border_width'  => 3,
		'header_height'        => 84,
		'logo_height'          => 68,
		'logo_height_mobile'   => 52,
		'header_cta_show'      => true,
		'header_cta_text'      => 'Send inquiry',
		'header_cta_url'       => '',
		'search_mode'          => 'bar',
		'search_placeholder'   => 'Search instruments or catalogue no.',
		'search_live'          => true,
		'search_cat_select'    => true,
		'search_limit'         => 6,
		'search_pages'         => true,
		'search_popular'       => 'Scissors, Forceps, Needle holder, Dental elevator, Cuticle nipper, Tweezers',
		'mega_promo_show'      => true,
		'mega_promo_title'     => 'Can\'t find an instrument?',
		'mega_promo_text'      => 'Send your list with sizes and quantities and we will reply with prices, lead time and shipping.',
		'mega_promo_button'    => 'Send inquiry',
		'mega_auto_categories' => true,
		'header_account'       => true,
		'header_cart'          => true,

		// Blog.
		'blog_eyebrow'         => 'Gami Surgical Journal',
		'blog_intro'           => 'Guides on choosing, using and caring for surgical, dental and beauty instruments, from the makers in Sialkot.',
		'blog_search'          => true,
		'blog_latest'          => true,
		'blog_topics'          => true,
		'blog_category_rows'   => true,
		'blog_cta'             => true,
		'blog_cta_title'       => 'Have a question about an instrument?',
		'blog_cta_text'        => 'Ask our team about materials, finishes, sizes or care. We usually reply within one working day.',
		'article_toc'          => true,
		'article_progress'     => true,
		'article_author_box'   => true,
		'article_about'        => true,
		'article_about_text'   => 'Gami Surgical manufactures surgical, dental and beauty instruments in Sialkot, Pakistan, and ships worldwide.',

		// Footer.
		'footer_width'         => 'contained',
		'footer_columns'       => 4,
		'footer_logo_show'     => true,
		'footer_logo'          => '',
		'footer_about'         => 'Manufacturer and exporter of reusable surgical, dental and beauty instruments.',
		'footer_col2_title'    => 'Products',
		'footer_col3_title'    => 'Get in touch',
		'footer_col4_title'    => 'Company',
		'footer_cta_show'      => true,
		'footer_cta_title'     => 'Looking for a specific instrument?',
		'footer_cta_text'      => 'Send your list with sizes and quantities. We reply with prices, lead time and shipping to your country.',
		'footer_points_show'   => true,
		'footer_cat_columns'   => true,
		'footer_cat_links'     => 6,
		'footer_links_show'    => true,
		'footer_social'        => true,
		'footer_bg'            => '#0C1520',
		'footer_color'         => '#B7C3CA',
		'footer_heading'       => '#FFFFFF',
		'footer_link'          => '#E3E9EC',
		'footer_link_hover'    => '#CB982C',
		'footer_bottom_show'   => true,
		'footer_bottom_bg'     => '#09111A',
		'copyright'            => '© {year} {site}. All rights reserved.',
		'whatsapp_float'       => true,
		'whatsapp_float_label' => 'WhatsApp us',
		'whatsapp_float_side'  => 'right',
		'back_to_top'          => true,

		// Social links.
		'social_facebook'      => '',
		'social_instagram'     => '',
		'social_linkedin'      => '',
		'social_youtube'       => '',
		'social_x'             => '',
		'social_tiktok'        => '',

		// Contact.
		'phone'                => '+1 347-727-3384',
		'phone_label'          => 'USA',
		'phone2'               => '+92 317 8614535',
		'phone2_label'         => 'Pakistan',
		'email'                => 'info@gamisurgical.us',
		'whatsapp'             => '+923178614535',
		'address'              => 'Sialkot, Punjab, Pakistan',
		'hours'                => 'Mon to Sat, 9:00 to 18:00 PKT',
		'quote_page'           => 0,
		'moq'                  => 'Tell us quantities and destination and we will include shipping in the quote.',

		// Inquiries.
		'shop_view'            => 'grid',
		'shop_columns'         => 3,
		'shop_per_page'        => 12,
		'shop_per_page_choices'=> '8, 12, 24, 48',
		'shop_controls'        => true,
		'shop_cat_cards'       => true,
		'cat_auto_images'      => true,
		'gallery_zoom'         => false,
		'gallery_bg'           => '#F5F6F8',
		'mega_photos'          => true,
		'show_prices'          => false,
		'show_reviews'         => false,
		'inquiry_button'       => 'Send inquiry',
		'inquiry_list'         => true,
		'inquiry_title'        => 'Send an inquiry',
		'inquiry_intro'        => 'Tell us what you need. We reply with prices, lead time and shipping, usually within one business day.',
		'inquiry_success'      => 'Thank you. Your inquiry has reached our sales team and we will reply by email shortly.',
		'inquiry_autoreply'    => true,
		'inquiry_points'       => "Made in Sialkot, Pakistan\nShipped worldwide\nReply with prices, lead time and shipping",

		// Used by the Spec drawing Elementor widget as starting values.
		'hero_part'            => 'Kelly hemostat, curved',
		'hero_part_no'         => 'GS-1021',
		'hero_size'            => '145 mm',
	);
}

/* -------------------------------------------------------------------------
 * Sanitizers
 * ---------------------------------------------------------------------- */

function gami_sanitize_checkbox( $value ) {
	return (bool) $value;
}

function gami_sanitize_choice( $value, $setting ) {
	$control = $setting->manager->get_control( $setting->id );
	$choices = $control ? $control->choices : array();
	return array_key_exists( $value, $choices ) ? $value : $setting->default;
}

function gami_sanitize_int( $value ) {
	return absint( $value );
}

function gami_sanitize_url_or_path( $value ) {
	$value = trim( (string) $value );
	if ( '' === $value ) {
		return '';
	}
	if ( 0 === strpos( $value, '/' ) || 0 === strpos( $value, '#' ) ) {
		return esc_url_raw( home_url( $value ) );
	}
	return esc_url_raw( $value );
}

/* -------------------------------------------------------------------------
 * Registration
 * ---------------------------------------------------------------------- */

/**
 * Add one setting and its control.
 *
 * @param WP_Customize_Manager $c    Manager.
 * @param string               $key  Option key without the gami_ prefix.
 * @param string               $type text|textarea|email|url|checkbox|select|radio|number|color|image.
 * @param string               $section Section ID.
 * @param string               $label   Label.
 * @param array                $extra   description, choices, input_attrs.
 */
function gami_add_option( $c, $key, $type, $section, $label, $extra = array() ) {
	$d        = gami_customizer_defaults();
	$sanitize = array(
		'text'     => 'sanitize_text_field',
		'textarea' => 'sanitize_textarea_field',
		'email'    => 'sanitize_email',
		'url'      => 'gami_sanitize_url_or_path',
		'checkbox' => 'gami_sanitize_checkbox',
		'select'   => 'gami_sanitize_choice',
		'radio'    => 'gami_sanitize_choice',
		'number'   => 'gami_sanitize_int',
		'color'    => 'sanitize_hex_color',
		'image'    => 'esc_url_raw',
		'pages'    => 'absint',
	);

	$c->add_setting(
		'gami_' . $key,
		array(
			'default'           => isset( $d[ $key ] ) ? $d[ $key ] : '',
			'sanitize_callback' => $sanitize[ $type ],
		)
	);

	$args = array_merge(
		array(
			'label'   => $label,
			'section' => $section,
		),
		$extra
	);

	if ( 'color' === $type ) {
		$c->add_control( new WP_Customize_Color_Control( $c, 'gami_' . $key, $args ) );
	} elseif ( 'image' === $type ) {
		$c->add_control( new WP_Customize_Image_Control( $c, 'gami_' . $key, $args ) );
	} else {
		$args['type'] = 'pages' === $type ? 'dropdown-pages' : $type;
		$c->add_control( 'gami_' . $key, $args );
	}
}

function gami_customize_register( $c ) {
	$c->add_panel(
		'gami_panel',
		array(
			'title'       => __( 'Gami Surgical', 'gami-surgical' ),
			'description' => __( 'Header, footer, colours and contact details. The logo itself is under Site Identity.', 'gami-surgical' ),
			'priority'    => 30,
		)
	);

	$sections = array(
		'gami_brand'   => __( 'Brand colour', 'gami-surgical' ),
		'gami_topbar'  => __( 'Top bar', 'gami-surgical' ),
		'gami_header'  => __( 'Header', 'gami-surgical' ),
		'gami_search'  => __( 'Search', 'gami-surgical' ),
		'gami_mega'    => __( 'Mega menu', 'gami-surgical' ),
		'gami_footer'  => __( 'Footer', 'gami-surgical' ),
		'gami_social'  => __( 'Social links', 'gami-surgical' ),
		'gami_contact' => __( 'Contact details', 'gami-surgical' ),
	);
	$sections['gami_inquiry'] = __( 'Inquiries', 'gami-surgical' );
	$sections['gami_blog']    = __( 'Blog and articles', 'gami-surgical' );
	if ( gami_wc_active() ) {
		$sections['gami_shoppage'] = __( 'Shop page', 'gami-surgical' );
	}
	foreach ( $sections as $id => $title ) {
		$c->add_section(
			$id,
			array(
				'title' => $title,
				'panel' => 'gami_panel',
			)
		);
	}

	$width   = array(
		'contained' => __( 'Contained (site width)', 'gami-surgical' ),
		'full'      => __( 'Full width', 'gami-surgical' ),
	);
	$px      = function ( $min, $max, $step = 1 ) {
		return array(
			'input_attrs' => array(
				'min'  => $min,
				'max'  => $max,
				'step' => $step,
			),
		);
	};

	// Brand.
	gami_add_option( $c, 'accent', 'color', 'gami_brand', __( 'Accent colour (buttons, highlights)', 'gami-surgical' ) );
	gami_add_option( $c, 'container_width', 'number', 'gami_brand', __( 'Site width (px)', 'gami-surgical' ), array_merge( $px( 960, 1920, 10 ), array( 'description' => __( 'Maximum width of the content on every page, header and footer included. Default 1400.', 'gami-surgical' ) ) ) );
	gami_add_option( $c, 'animations', 'checkbox', 'gami_brand', __( 'Animations (content fades in on scroll, hover effects)', 'gami-surgical' ), array( 'description' => __( 'Visitors who turn off motion in their device settings never see animations.', 'gami-surgical' ) ) );
	gami_add_option( $c, 'certifications', 'text', 'gami_brand', __( 'Certifications line', 'gami-surgical' ), array( 'description' => __( 'Shown in the footer. Only list certifications you hold, for example ISO 13485, CE. Hidden when empty.', 'gami-surgical' ) ) );

	// Blog.
	gami_add_option( $c, 'blog_eyebrow', 'text', 'gami_blog', __( 'Blog page: small heading above the title', 'gami-surgical' ) );
	gami_add_option( $c, 'blog_intro', 'textarea', 'gami_blog', __( 'Blog page: introduction', 'gami-surgical' ), array( 'description' => __( 'Used when the Blog page has no excerpt.', 'gami-surgical' ) ) );
	gami_add_option( $c, 'blog_search', 'checkbox', 'gami_blog', __( 'Show article search in the banner', 'gami-surgical' ) );
	gami_add_option( $c, 'blog_latest', 'checkbox', 'gami_blog', __( 'Section: Latest articles', 'gami-surgical' ) );
	gami_add_option( $c, 'blog_topics', 'checkbox', 'gami_blog', __( 'Section: Browse by topic', 'gami-surgical' ) );
	gami_add_option( $c, 'blog_category_rows', 'checkbox', 'gami_blog', __( 'Section: one row per category', 'gami-surgical' ), array( 'description' => __( 'Shown when the blog has two or more categories.', 'gami-surgical' ) ) );
	gami_add_option( $c, 'blog_cta', 'checkbox', 'gami_blog', __( 'Section: question banner', 'gami-surgical' ) );
	gami_add_option( $c, 'blog_cta_title', 'text', 'gami_blog', __( 'Question banner: title', 'gami-surgical' ) );
	gami_add_option( $c, 'blog_cta_text', 'textarea', 'gami_blog', __( 'Question banner: text', 'gami-surgical' ) );
	gami_add_option( $c, 'article_toc', 'checkbox', 'gami_blog', __( 'Articles: "On this page" contents', 'gami-surgical' ) );
	gami_add_option( $c, 'article_progress', 'checkbox', 'gami_blog', __( 'Articles: reading progress bar', 'gami-surgical' ) );
	gami_add_option( $c, 'article_author_box', 'checkbox', 'gami_blog', __( 'Articles: author box', 'gami-surgical' ) );
	gami_add_option( $c, 'article_about', 'checkbox', 'gami_blog', __( 'Articles: About box in the sidebar', 'gami-surgical' ) );
	gami_add_option( $c, 'article_about_text', 'textarea', 'gami_blog', __( 'About box text', 'gami-surgical' ) );

	// Top bar.
	gami_add_option( $c, 'topbar_show', 'checkbox', 'gami_topbar', __( 'Show the top bar', 'gami-surgical' ) );
	gami_add_option( $c, 'topbar_text', 'text', 'gami_topbar', __( 'Text on the left', 'gami-surgical' ), array( 'description' => __( 'For example: Worldwide shipping from Sialkot, Pakistan.', 'gami-surgical' ) ) );
	gami_add_option( $c, 'topbar_phone', 'checkbox', 'gami_topbar', __( 'Show phone', 'gami-surgical' ) );
	gami_add_option( $c, 'topbar_email', 'checkbox', 'gami_topbar', __( 'Show email', 'gami-surgical' ) );
	gami_add_option( $c, 'topbar_social', 'checkbox', 'gami_topbar', __( 'Show social icons', 'gami-surgical' ) );
	gami_add_option( $c, 'topbar_bg', 'color', 'gami_topbar', __( 'Background', 'gami-surgical' ) );
	gami_add_option( $c, 'topbar_color', 'color', 'gami_topbar', __( 'Text colour', 'gami-surgical' ) );

	// Header.
	gami_add_option(
		$c,
		'header_layout',
		'radio',
		'gami_header',
		__( 'Desktop layout', 'gami-surgical' ),
		array(
			'choices' => array(
				'stacked' => __( 'Two rows: logo, search bar and buttons, then the menu bar', 'gami-surgical' ),
				'inline'  => __( 'One row: logo, menu and icons', 'gami-surgical' ),
			),
		)
	);
	gami_add_option( $c, 'mobile_breakpoint', 'number', 'gami_header', __( 'Show the mobile menu button below this width (px)', 'gami-surgical' ), array_merge( $px( 600, 1400, 1 ), array( 'description' => __( 'Wider screens always show the full menu. Menu items that do not fit move into a "More" dropdown.', 'gami-surgical' ) ) ) );
	gami_add_option( $c, 'header_width', 'radio', 'gami_header', __( 'Width', 'gami-surgical' ), array( 'choices' => $width ) );
	gami_add_option( $c, 'header_sticky', 'checkbox', 'gami_header', __( 'Keep the header visible when scrolling', 'gami-surgical' ) );
	gami_add_option( $c, 'header_shrink', 'checkbox', 'gami_header', __( 'Make the sticky header slimmer after scrolling', 'gami-surgical' ) );
	gami_add_option( $c, 'mobile_header_condense', 'checkbox', 'gami_header', __( 'Phones: show only the search bar while scrolling down', 'gami-surgical' ), array( 'description' => __( 'The full header comes back as soon as the visitor scrolls up. Needs the sticky header and the search bar.', 'gami-surgical' ) ) );
	gami_add_option( $c, 'drawer_contact', 'checkbox', 'gami_header', __( 'Mobile menu: show Call, WhatsApp and Email buttons', 'gami-surgical' ) );
	gami_add_option(
		$c,
		'header_nav_align',
		'radio',
		'gami_header',
		__( 'Menu position', 'gami-surgical' ),
		array(
			'choices' => array(
				'left'   => __( 'Next to the logo', 'gami-surgical' ),
				'center' => __( 'Centre', 'gami-surgical' ),
				'right'  => __( 'Right', 'gami-surgical' ),
			),
		)
	);
	gami_add_option( $c, 'logo_height', 'number', 'gami_header', __( 'Logo height on desktop (px)', 'gami-surgical' ), $px( 24, 160 ) );
	gami_add_option( $c, 'logo_height_mobile', 'number', 'gami_header', __( 'Logo height on mobile (px)', 'gami-surgical' ), $px( 24, 120 ) );
	gami_add_option( $c, 'header_height', 'number', 'gami_header', __( 'Header height (px)', 'gami-surgical' ), $px( 48, 200 ) );
	gami_add_option( $c, 'header_bg', 'color', 'gami_header', __( 'Background', 'gami-surgical' ), array( 'description' => __( 'Keep it dark while using the bundled logo, its wordmark is white.', 'gami-surgical' ) ) );
	gami_add_option( $c, 'header_link', 'color', 'gami_header', __( 'Menu and icon colour', 'gami-surgical' ) );
	gami_add_option( $c, 'header_link_hover', 'color', 'gami_header', __( 'Menu hover and current page colour', 'gami-surgical' ) );
	gami_add_option( $c, 'header_nav_bg', 'color', 'gami_header', __( 'Menu bar background (two-row layout)', 'gami-surgical' ) );
	gami_add_option( $c, 'header_nav_phone', 'checkbox', 'gami_header', __( 'Show phone and hours at the right of the menu bar (two-row layout)', 'gami-surgical' ) );
	gami_add_option( $c, 'header_border_color', 'color', 'gami_header', __( 'Bottom line colour', 'gami-surgical' ) );
	gami_add_option( $c, 'header_border_width', 'number', 'gami_header', __( 'Bottom line thickness (px, 0 hides it)', 'gami-surgical' ), $px( 0, 12 ) );
	gami_add_option( $c, 'header_cta_show', 'checkbox', 'gami_header', __( 'Show the button', 'gami-surgical' ) );
	gami_add_option( $c, 'header_cta_text', 'text', 'gami_header', __( 'Button text', 'gami-surgical' ) );
	gami_add_option( $c, 'header_cta_url', 'url', 'gami_header', __( 'Button link', 'gami-surgical' ), array( 'description' => __( 'Leave empty to open the inquiry form. A path like /contact/ also works.', 'gami-surgical' ) ) );

	// Search.
	gami_add_option(
		$c,
		'search_mode',
		'radio',
		'gami_search',
		__( 'Header search', 'gami-surgical' ),
		array(
			'choices' => array(
				'bar'  => __( 'Search bar (in one-row layout it opens from an icon)', 'gami-surgical' ),
				'icon' => __( 'Icon that opens the search bar', 'gami-surgical' ),
				'off'  => __( 'Off', 'gami-surgical' ),
			),
		)
	);
	gami_add_option( $c, 'search_placeholder', 'text', 'gami_search', __( 'Placeholder text', 'gami-surgical' ) );
	gami_add_option( $c, 'search_live', 'checkbox', 'gami_search', __( 'Show live results while typing', 'gami-surgical' ) );
	gami_add_option( $c, 'search_cat_select', 'checkbox', 'gami_search', __( 'Show the category dropdown', 'gami-surgical' ) );
	gami_add_option( $c, 'search_limit', 'number', 'gami_search', __( 'Products in live results', 'gami-surgical' ), $px( 3, 12 ) );
	gami_add_option( $c, 'search_pages', 'checkbox', 'gami_search', __( 'Include matching pages', 'gami-surgical' ) );
	gami_add_option( $c, 'search_popular', 'textarea', 'gami_search', __( 'Popular searches (comma separated)', 'gami-surgical' ), array( 'description' => __( 'Shown when someone clicks into an empty search box, with their own recent searches.', 'gami-surgical' ) ) );

	// Mega menu.
	$c->get_section( 'gami_mega' )->description = __( 'Turn any top-level menu item into a mega menu in Appearance > Menus (open the item and tick "Show as mega menu", or "Fill with product categories automatically"). The panel below is added to automatic category mega menus.', 'gami-surgical' );
	gami_add_option( $c, 'mega_auto_categories', 'checkbox', 'gami_mega', __( 'Product category menu items show their sub-categories automatically', 'gami-surgical' ), array( 'description' => __( 'Add a category (for example Surgical instruments) to the menu and its sub-categories appear as a mega menu. Works under a parent item too.', 'gami-surgical' ) ) );
	gami_add_option( $c, 'mega_photos', 'checkbox', 'gami_mega', __( 'Show sub-categories with photos', 'gami-surgical' ) );
	gami_add_option( $c, 'mega_promo_show', 'checkbox', 'gami_mega', __( 'Show the quote panel in category mega menus', 'gami-surgical' ) );
	gami_add_option( $c, 'mega_promo_title', 'text', 'gami_mega', __( 'Panel title', 'gami-surgical' ) );
	gami_add_option( $c, 'mega_promo_text', 'textarea', 'gami_mega', __( 'Panel text', 'gami-surgical' ) );
	gami_add_option( $c, 'mega_promo_button', 'text', 'gami_mega', __( 'Panel button text (links to the header button link)', 'gami-surgical' ) );

	// Footer.
	gami_add_option( $c, 'footer_width', 'radio', 'gami_footer', __( 'Width', 'gami-surgical' ), array( 'choices' => $width ) );
	gami_add_option( $c, 'footer_cta_show', 'checkbox', 'gami_footer', __( 'Show the call-to-action card at the top of the footer', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_cta_title', 'text', 'gami_footer', __( 'Call-to-action title', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_cta_text', 'textarea', 'gami_footer', __( 'Call-to-action text', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_points_show', 'checkbox', 'gami_footer', __( 'Show the highlights row (points from Inquiries)', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_cat_columns', 'checkbox', 'gami_footer', __( 'Show a column for each main product category', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_cat_links', 'number', 'gami_footer', __( 'Sub-categories listed per column', 'gami-surgical' ), $px( 3, 12 ) );
	gami_add_option( $c, 'footer_links_show', 'checkbox', 'gami_footer', __( 'Show the Company column (Footer menu)', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_logo_show', 'checkbox', 'gami_footer', __( 'Show the logo', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_logo', 'image', 'gami_footer', __( 'Footer logo (optional)', 'gami-surgical' ), array( 'description' => __( 'Leave empty to reuse the header logo.', 'gami-surgical' ) ) );
	gami_add_option( $c, 'footer_about', 'textarea', 'gami_footer', __( 'Short company description', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_col3_title', 'text', 'gami_footer', __( 'Label above the contact details', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_social', 'checkbox', 'gami_footer', __( 'Show social icons', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_col4_title', 'text', 'gami_footer', __( 'Company column heading (shows the Footer menu)', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_bg', 'color', 'gami_footer', __( 'Background', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_color', 'color', 'gami_footer', __( 'Text colour', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_heading', 'color', 'gami_footer', __( 'Heading colour', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_link', 'color', 'gami_footer', __( 'Link colour', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_link_hover', 'color', 'gami_footer', __( 'Link hover colour', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_bottom_show', 'checkbox', 'gami_footer', __( 'Show the bottom bar', 'gami-surgical' ) );
	gami_add_option( $c, 'footer_bottom_bg', 'color', 'gami_footer', __( 'Bottom bar background', 'gami-surgical' ) );
	gami_add_option( $c, 'copyright', 'text', 'gami_footer', __( 'Copyright text', 'gami-surgical' ), array( 'description' => __( '{year} and {site} are replaced automatically.', 'gami-surgical' ) ) );
	gami_add_option( $c, 'back_to_top', 'checkbox', 'gami_footer', __( 'Show a back-to-top button', 'gami-surgical' ) );
	gami_add_option( $c, 'whatsapp_float', 'checkbox', 'gami_footer', __( 'Show the floating WhatsApp button', 'gami-surgical' ) );
	gami_add_option( $c, 'whatsapp_float_label', 'text', 'gami_footer', __( 'WhatsApp button text', 'gami-surgical' ) );
	gami_add_option(
		$c,
		'whatsapp_float_side',
		'radio',
		'gami_footer',
		__( 'WhatsApp button position', 'gami-surgical' ),
		array(
			'choices' => array(
				'right' => __( 'Bottom right', 'gami-surgical' ),
				'left'  => __( 'Bottom left', 'gami-surgical' ),
			),
		)
	);

	// Social.
	foreach ( gami_social_networks() as $key => $network ) {
		gami_add_option( $c, 'social_' . $key, 'url', 'gami_social', $network['label'] );
	}

	// Contact.
	gami_add_option( $c, 'phone', 'text', 'gami_contact', __( 'Phone', 'gami-surgical' ) );
	gami_add_option( $c, 'phone_label', 'text', 'gami_contact', __( 'Phone label (optional, e.g. USA)', 'gami-surgical' ) );
	gami_add_option( $c, 'phone2', 'text', 'gami_contact', __( 'Second phone (optional)', 'gami-surgical' ) );
	gami_add_option( $c, 'phone2_label', 'text', 'gami_contact', __( 'Second phone label (optional, e.g. Pakistan)', 'gami-surgical' ) );
	gami_add_option( $c, 'email', 'email', 'gami_contact', __( 'Sales email (receives inquiries)', 'gami-surgical' ) );
	gami_add_option( $c, 'whatsapp', 'text', 'gami_contact', __( 'WhatsApp number, with country code', 'gami-surgical' ) );
	gami_add_option( $c, 'address', 'textarea', 'gami_contact', __( 'Address', 'gami-surgical' ) );
	gami_add_option( $c, 'hours', 'text', 'gami_contact', __( 'Business hours', 'gami-surgical' ) );
	gami_add_option( $c, 'quote_page', 'pages', 'gami_contact', __( 'Inquiry page', 'gami-surgical' ), array( 'description' => __( 'Where "Send inquiry" leads: the inquiry list and form. Created automatically as "Inquiry"; text you add to that page shows above the form.', 'gami-surgical' ) ) );
	gami_add_option( $c, 'moq', 'textarea', 'gami_contact', __( 'Note under the quote form', 'gami-surgical' ) );

	// Shop page.
	if ( gami_wc_active() ) {
		gami_add_option(
			$c,
			'shop_view',
			'radio',
			'gami_shoppage',
			__( 'Default view', 'gami-surgical' ),
			array(
				'choices' => array(
					'grid' => __( 'Grid', 'gami-surgical' ),
					'list' => __( 'List', 'gami-surgical' ),
				),
			)
		);
		gami_add_option(
			$c,
			'shop_columns',
			'select',
			'gami_shoppage',
			__( 'Products per row in grid view (desktop)', 'gami-surgical' ),
			array(
				'choices' => array(
					'2' => '2',
					'3' => '3',
					'4' => '4',
				),
			)
		);
		gami_add_option( $c, 'shop_per_page_choices', 'text', 'gami_shoppage', __( '"Show" choices (products per page, comma separated)', 'gami-surgical' ) );
		gami_add_option( $c, 'shop_per_page', 'number', 'gami_shoppage', __( 'Default products per page (one of the choices above)', 'gami-surgical' ), $px( 1, 96 ) );
		gami_add_option( $c, 'shop_controls', 'checkbox', 'gami_shoppage', __( 'Let visitors change view, products per row and per page', 'gami-surgical' ) );
		gami_add_option( $c, 'shop_cat_cards', 'checkbox', 'gami_shoppage', __( 'Show category cards at the top of the shop page', 'gami-surgical' ) );
		gami_add_option( $c, 'gallery_bg', 'color', 'gami_shoppage', __( 'Product photo background', 'gami-surgical' ), array( 'description' => __( 'Match your photos: white (#FFFFFF) for photos shot on white. White parts of photos always blend into this colour.', 'gami-surgical' ) ) );
		gami_add_option( $c, 'gallery_zoom', 'checkbox', 'gami_shoppage', __( 'Product page: zoom the photo on mouse hover', 'gami-surgical' ), array( 'description' => __( 'Off by default. The full-screen button on the photo always works.', 'gami-surgical' ) ) );
		gami_add_option( $c, 'cat_auto_images', 'checkbox', 'gami_shoppage', __( 'Categories without their own image use a product photo', 'gami-surgical' ), array( 'description' => __( 'Used in category cards, the sub-category row and the mega menu. A Thumbnail set in Products > Categories always wins.', 'gami-surgical' ) ) );
	}

	// Inquiries.
	$c->get_section( 'gami_inquiry' )->description = __( 'Products show "Send inquiry" instead of add to cart. Inquiries are emailed to the sales email (Contact details) and saved under Dashboard > Inquiries.', 'gami-surgical' );
	gami_add_option( $c, 'inquiry_button', 'text', 'gami_inquiry', __( 'Product button text', 'gami-surgical' ) );
	gami_add_option( $c, 'inquiry_list', 'checkbox', 'gami_inquiry', __( 'Let visitors collect several products in an inquiry list', 'gami-surgical' ) );
	gami_add_option( $c, 'show_prices', 'checkbox', 'gami_inquiry', __( 'Show prices (where a product has one)', 'gami-surgical' ) );
	gami_add_option( $c, 'show_reviews', 'checkbox', 'gami_inquiry', __( 'Show the product reviews tab', 'gami-surgical' ) );
	gami_add_option( $c, 'inquiry_points', 'textarea', 'gami_inquiry', __( 'Points under the product buttons (one per line)', 'gami-surgical' ) );
	gami_add_option( $c, 'inquiry_title', 'text', 'gami_inquiry', __( 'Form title', 'gami-surgical' ) );
	gami_add_option( $c, 'inquiry_intro', 'textarea', 'gami_inquiry', __( 'Text above the form', 'gami-surgical' ) );
	gami_add_option( $c, 'inquiry_success', 'textarea', 'gami_inquiry', __( 'Message after sending', 'gami-surgical' ) );
	gami_add_option( $c, 'inquiry_autoreply', 'checkbox', 'gami_inquiry', __( 'Email the buyer a copy of their inquiry', 'gami-surgical' ) );
}
add_action( 'customize_register', 'gami_customize_register' );

/* -------------------------------------------------------------------------
 * Output
 * ---------------------------------------------------------------------- */

/**
 * CSS variables generated from Customizer values.
 */
function gami_customizer_css() {
	$colors = array(
		'--gs-accent'        => 'accent',
		'--gs-topbar-bg'     => 'topbar_bg',
		'--gs-topbar-color'  => 'topbar_color',
		'--gs-header-bg'     => 'header_bg',
		'--gs-header-link'   => 'header_link',
		'--gs-header-hover'  => 'header_link_hover',
		'--gs-header-border' => 'header_border_color',
		'--gs-nav-bg'        => 'header_nav_bg',
		'--gs-footer-bg'     => 'footer_bg',
		'--gs-footer-color'  => 'footer_color',
		'--gs-footer-head'   => 'footer_heading',
		'--gs-footer-link'   => 'footer_link',
		'--gs-footer-hover'  => 'footer_link_hover',
		'--gs-footer-bottom' => 'footer_bottom_bg',
	);
	$d   = gami_customizer_defaults();
	$css = '';
	foreach ( $colors as $var => $key ) {
		$value = sanitize_hex_color( gami_option( $key ) );
		$css  .= $var . ':' . ( $value ? $value : $d[ $key ] ) . ';';
	}
	$width = absint( gami_option( 'container_width' ) );
	$css  .= '--gs-container:' . ( $width >= 960 && $width <= 1920 ? $width : 1400 ) . 'px;';
	$sizes = array(
		'--gs-header-border-w' => 'header_border_width',
		'--gs-header-h'        => 'header_height',
		'--gs-logo-h'          => 'logo_height',
		'--gs-logo-h-m'        => 'logo_height_mobile',
	);
	foreach ( $sizes as $var => $key ) {
		$css .= $var . ':' . absint( gami_option( $key ) ) . 'px;';
	}
	return ':root{' . $css . '}';
}
