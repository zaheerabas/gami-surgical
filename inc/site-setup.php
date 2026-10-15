<?php
/**
 * Site setup: the theme's pages and the Appearance > Gami Setup screen.
 *
 * When the theme is installed or a new version is uploaded, every page in
 * gami_setup_pages() is created if it is missing. A page that already exists is
 * replaced in place: its title, slug, parent and status are reset to the theme's,
 * but its content (including Elementor sections) is kept. Pages are created blank;
 * their sections are designed in Elementor.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Every page the theme provides: slug => array( title, parent slug, group ).
 * Parents must come before their children.
 */
function gami_setup_pages() {
	$pages = array(
		// Main.
		'home'                  => array( __( 'Home', 'gami-surgical' ), '', 'main' ),
		'about'                 => array( __( 'About Us', 'gami-surgical' ), '', 'main' ),
		'contact'               => array( __( 'Contact Us', 'gami-surgical' ), '', 'main' ),
		'blog'                  => array( __( 'Blog', 'gami-surgical' ), '', 'main' ),
		'inquiry'               => array( __( 'Send an inquiry', 'gami-surgical' ), '', 'main' ),

		// Company.
		'why-gami-surgical'     => array( __( 'Why Gami Surgical', 'gami-surgical' ), '', 'company' ),
		'our-factory'           => array( __( 'Our Factory', 'gami-surgical' ), '', 'company' ),
		'manufacturing-process' => array( __( 'Manufacturing Process', 'gami-surgical' ), '', 'company' ),
		'quality-control'       => array( __( 'Quality Control', 'gami-surgical' ), '', 'company' ),
		'certifications'        => array( __( 'Certifications', 'gami-surgical' ), '', 'company' ),
		'testimonials'          => array( __( 'Testimonials', 'gami-surgical' ), '', 'company' ),
		'careers'               => array( __( 'Careers', 'gami-surgical' ), '', 'company' ),

		// Services.
		'private-label'         => array( __( 'Private Label & OEM', 'gami-surgical' ), '', 'services' ),
		'custom-instruments'    => array( __( 'Custom Instruments', 'gami-surgical' ), '', 'services' ),
		'laser-marking'         => array( __( 'Laser Marking & Branding', 'gami-surgical' ), '', 'services' ),
		'instrument-sets'       => array( __( 'Instrument Sets & Kits', 'gami-surgical' ), '', 'services' ),
		'samples'               => array( __( 'Samples', 'gami-surgical' ), '', 'services' ),
		'wholesale'             => array( __( 'Wholesale', 'gami-surgical' ), '', 'services' ),
		'become-a-distributor'  => array( __( 'Become a Distributor', 'gami-surgical' ), '', 'services' ),

		// Product range landing pages.
		'surgical-instruments'  => array( __( 'Surgical Instruments', 'gami-surgical' ), '', 'range' ),
		'dental-instruments'    => array( __( 'Dental Instruments', 'gami-surgical' ), '', 'range' ),
		'beauty-instruments'    => array( __( 'Beauty Instruments', 'gami-surgical' ), '', 'range' ),
		'veterinary-instruments' => array( __( 'Veterinary Instruments', 'gami-surgical' ), '', 'range' ),

		// Buyer help.
		'how-to-order'          => array( __( 'How to Order', 'gami-surgical' ), '', 'help' ),
		'moq-lead-times'        => array( __( 'MOQ & Lead Times', 'gami-surgical' ), '', 'help' ),
		'shipping-delivery'     => array( __( 'Shipping & Delivery', 'gami-surgical' ), '', 'help' ),
		'catalogue-download'    => array( __( 'Catalogue Download', 'gami-surgical' ), '', 'help' ),
		'faqs'                  => array( __( 'FAQs', 'gami-surgical' ), '', 'help' ),
		'thank-you'             => array( __( 'Thank You', 'gami-surgical' ), '', 'help' ),

		// Policies.
		'privacy-policy'        => array( __( 'Privacy Policy', 'gami-surgical' ), '', 'policy' ),
		'cookie-policy'         => array( __( 'Cookie Policy', 'gami-surgical' ), '', 'policy' ),
		'terms-conditions'      => array( __( 'Terms & Conditions', 'gami-surgical' ), '', 'policy' ),
		'payment-terms'         => array( __( 'Payment Terms', 'gami-surgical' ), '', 'policy' ),
		'return-refund-policy'  => array( __( 'Return & Refund Policy', 'gami-surgical' ), '', 'policy' ),
		'disclaimer'            => array( __( 'Disclaimer', 'gami-surgical' ), '', 'policy' ),
	);

	// If another page was chosen as the inquiry page, don't make a second one.
	$inquiry = function_exists( 'gami_inquiry_page_id' ) ? gami_inquiry_page_id() : 0;
	if ( $inquiry && 'inquiry' !== get_post_field( 'post_name', $inquiry ) ) {
		unset( $pages['inquiry'] );
	}

	return apply_filters( 'gami_setup_pages', $pages );
}

function gami_setup_groups() {
	return array(
		'main'     => __( 'Main', 'gami-surgical' ),
		'company'  => __( 'Company', 'gami-surgical' ),
		'services' => __( 'Services', 'gami-surgical' ),
		'range'    => __( 'Product range', 'gami-surgical' ),
		'help'     => __( 'Buyer help', 'gami-surgical' ),
		'policy'   => __( 'Policies', 'gami-surgical' ),
	);
}

/**
 * Find an existing page for a slug: at its correct place first, then anywhere
 * (moved, draft or in the trash).
 *
 * @return WP_Post|null
 */
function gami_setup_find_page( $slug, $parent_id ) {
	$found = get_posts(
		array(
			'post_type'        => 'page',
			'post_name__in'    => array( $slug, $slug . '__trashed' ),
			'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
			'numberposts'      => -1,
			'orderby'          => 'ID',
			'order'            => 'ASC',
			'suppress_filters' => true,
		)
	);
	if ( ! $found ) {
		return null;
	}
	$score = function ( $p ) use ( $parent_id ) {
		return ( 'trash' !== $p->post_status ? 2 : 0 ) + ( (int) $p->post_parent === (int) $parent_id ? 1 : 0 );
	};
	usort(
		$found,
		function ( $a, $b ) use ( $score ) {
			return $score( $b ) - $score( $a );
		}
	);
	return $found[0];
}

/**
 * Create missing pages and replace existing ones (content kept).
 *
 * @return array Report: created, updated, ids.
 */
function gami_run_page_setup() {
	$ids    = array();
	$report = array(
		'created' => array(),
		'updated' => array(),
		'time'    => time(),
	);
	$order  = 0;

	foreach ( gami_setup_pages() as $slug => $def ) {
		list( $title, $parent_slug ) = $def;
		$parent_id = ( $parent_slug && ! empty( $ids[ $parent_slug ] ) ) ? (int) $ids[ $parent_slug ] : 0;
		$page      = gami_setup_find_page( $slug, $parent_id );
		++$order;

		if ( ! $page ) {
			$id = wp_insert_post(
				array(
					'post_type'   => 'page',
					'post_status' => 'publish',
					'post_title'  => $title,
					'post_name'   => $slug,
					'post_parent' => $parent_id,
					'menu_order'  => $order,
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				$ids[ $slug ]          = $id;
				$report['created'][] = $title;
			}
			continue;
		}

		if ( 'trash' === $page->post_status ) {
			wp_untrash_post( $page->ID );
		}
		$ids[ $slug ] = $page->ID;

		$changes = array();
		if ( html_entity_decode( $page->post_title, ENT_QUOTES ) !== html_entity_decode( $title, ENT_QUOTES ) ) {
			$changes['post_title'] = $title;
		}
		if ( $page->post_name !== $slug ) {
			$changes['post_name'] = $slug;
		}
		if ( (int) $page->post_parent !== $parent_id ) {
			$changes['post_parent'] = $parent_id;
		}
		if ( 'publish' !== get_post_status( $page->ID ) ) {
			$changes['post_status'] = 'publish';
		}
		if ( $changes ) {
			// post_content and Elementor data are never touched.
			$res = wp_update_post( array( 'ID' => $page->ID ) + $changes, true );
			if ( ! is_wp_error( $res ) ) {
				$report['updated'][] = $title;
			}
		}
	}

	// Wire the pages into the theme and WordPress settings.
	if ( ! empty( $ids['inquiry'] ) && ! ( function_exists( 'gami_inquiry_page_id' ) && gami_inquiry_page_id() ) ) {
		set_theme_mod( 'gami_quote_page', $ids['inquiry'] );
	}
	if ( ! empty( $ids['privacy-policy'] ) ) {
		update_option( 'wp_page_for_privacy_policy', $ids['privacy-policy'] );
	}
	if ( ! empty( $ids['home'] ) && get_option( 'gami_setup_use_home', 1 ) && 'posts' === get_option( 'show_on_front' ) && ! get_option( 'gami_setup_front_done' ) ) {
		gami_setup_use_home_page( $ids['home'] );
	}
	update_option( 'gami_setup_front_done', 1 );

	update_option( 'gami_setup_report', $report, false );
	return $report;
}

/**
 * Show the Home page as the homepage, and Blog as the posts page.
 */
function gami_setup_use_home_page( $home_id ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', (int) $home_id );
	$blog = get_page_by_path( 'blog' );
	if ( $blog ) {
		update_option( 'page_for_posts', $blog->ID );
	}
}

/**
 * Run once per theme version: on activation and after a new version is uploaded.
 */
function gami_maybe_run_page_setup() {
	if ( get_option( 'gami_setup_version' ) === GAMI_VERSION || ! current_user_can( 'publish_pages' ) ) {
		return;
	}
	if ( ! get_option( 'gami_setup_auto', 1 ) ) {
		update_option( 'gami_setup_version', GAMI_VERSION );
		return;
	}
	gami_run_page_setup();
	update_option( 'gami_setup_version', GAMI_VERSION );
	update_option( 'gami_setup_notice', 1 );
}
add_action( 'admin_init', 'gami_maybe_run_page_setup', 5 );

function gami_reset_page_setup() {
	delete_option( 'gami_setup_version' );
}
add_action( 'after_switch_theme', 'gami_reset_page_setup' );

/* -------------------------------------------------------------------------
 * Menus (optional, from the setup screen)
 * ---------------------------------------------------------------------- */

/**
 * Create a menu for an empty menu location from theme pages. Locations that
 * already have a menu are left alone.
 *
 * @return string[] Locations that received a menu.
 */
function gami_setup_create_menus() {
	$locations = get_nav_menu_locations();
	$done      = array();
	$page      = function ( $slug ) {
		return get_page_by_path( $slug );
	};

	$plans = array(
		'primary' => array(
			__( 'Main menu', 'gami-surgical' ),
			array(
				array( __( 'Home', 'gami-surgical' ), home_url( '/' ) ),
				array( __( 'Products', 'gami-surgical' ), gami_catalog_url() ),
				'surgical-instruments',
				'dental-instruments',
				'beauty-instruments',
				'about',
				'blog',
				'contact',
			),
		),
		'footer'  => array(
			__( 'Footer menu', 'gami-surgical' ),
			array( 'about', 'why-gami-surgical', 'quality-control', 'private-label', 'how-to-order', 'faqs', 'contact' ),
		),
		'legal'   => array(
			__( 'Legal links', 'gami-surgical' ),
			array( 'privacy-policy', 'terms-conditions', 'return-refund-policy', 'cookie-policy', 'disclaimer' ),
		),
	);

	foreach ( $plans as $location => $plan ) {
		if ( ! empty( $locations[ $location ] ) && wp_get_nav_menu_object( $locations[ $location ] ) ) {
			continue;
		}
		$menu = wp_get_nav_menu_object( $plan[0] );
		$id   = $menu ? $menu->term_id : wp_create_nav_menu( $plan[0] );
		if ( is_wp_error( $id ) ) {
			continue;
		}
		if ( ! wp_get_nav_menu_items( $id ) ) {
			foreach ( $plan[1] as $item ) {
				if ( is_array( $item ) ) {
					wp_update_nav_menu_item(
						$id,
						0,
						array(
							'menu-item-title'  => $item[0],
							'menu-item-url'    => $item[1],
							'menu-item-type'   => 'custom',
							'menu-item-status' => 'publish',
						)
					);
					continue;
				}
				$p = $page( $item );
				if ( $p ) {
					wp_update_nav_menu_item(
						$id,
						0,
						array(
							'menu-item-type'      => 'post_type',
							'menu-item-object'    => 'page',
							'menu-item-object-id' => $p->ID,
							'menu-item-status'    => 'publish',
						)
					);
				}
			}
		}
		$locations[ $location ] = $id;
		$done[]                 = $location;
	}
	set_theme_mod( 'nav_menu_locations', $locations );
	return $done;
}

/* -------------------------------------------------------------------------
 * Appearance > Gami Setup
 * ---------------------------------------------------------------------- */

function gami_setup_menu() {
	add_theme_page(
		__( 'Gami Setup', 'gami-surgical' ),
		__( 'Gami Setup', 'gami-surgical' ),
		'edit_theme_options',
		'gami-setup',
		'gami_setup_screen'
	);
}
add_action( 'admin_menu', 'gami_setup_menu' );

function gami_setup_handle_actions() {
	if ( empty( $_POST['gami_setup_action'] ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	check_admin_referer( 'gami_setup' );
	$action = sanitize_key( wp_unslash( $_POST['gami_setup_action'] ) );
	$msg    = '';

	if ( 'pages' === $action ) {
		$r   = gami_run_page_setup();
		$msg = sprintf( 'pages-%d-%d', count( $r['created'] ), count( $r['updated'] ) );
	} elseif ( 'home' === $action ) {
		$home = get_page_by_path( 'home' );
		if ( $home ) {
			gami_setup_use_home_page( $home->ID );
		}
		$msg = 'home';
	} elseif ( 'menus' === $action ) {
		$msg = 'menus-' . count( gami_setup_create_menus() );
	} elseif ( 'options' === $action ) {
		update_option( 'gami_setup_auto', empty( $_POST['gami_setup_auto'] ) ? 0 : 1 );
		update_option( 'gami_setup_use_home', empty( $_POST['gami_setup_use_home'] ) ? 0 : 1 );
		$msg = 'saved';
	}

	wp_safe_redirect( admin_url( 'themes.php?page=gami-setup&gami-msg=' . rawurlencode( $msg ) ) );
	exit;
}
add_action( 'admin_init', 'gami_setup_handle_actions' );

function gami_setup_button( $action, $label, $primary = false ) {
	echo '<form method="post" style="display:inline-block;margin:0 .5em .5em 0">';
	wp_nonce_field( 'gami_setup' );
	printf( '<input type="hidden" name="gami_setup_action" value="%s">', esc_attr( $action ) );
	submit_button( $label, $primary ? 'primary' : 'secondary', 'submit', false );
	echo '</form>';
}

function gami_setup_screen() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	delete_option( 'gami_setup_notice' );

	$msg = isset( $_GET['gami-msg'] ) ? sanitize_text_field( wp_unslash( $_GET['gami-msg'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$pages  = gami_setup_pages();
	$groups = gami_setup_groups();
	$front  = (int) get_option( 'page_on_front' );
	$home   = get_page_by_path( 'home' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Gami Setup', 'gami-surgical' ); ?></h1>

		<?php
		if ( preg_match( '/^pages-(\d+)-(\d+)$/', $msg, $m ) ) {
			/* translators: 1: created count, 2: replaced count */
			printf( '<div class="notice notice-success"><p>%s</p></div>', esc_html( sprintf( __( 'Pages updated: %1$d created, %2$d replaced. Content was kept.', 'gami-surgical' ), $m[1], $m[2] ) ) );
		} elseif ( 'home' === $msg ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'The Home page is now your homepage, and Blog shows your posts.', 'gami-surgical' ) . '</p></div>';
		} elseif ( preg_match( '/^menus-(\d+)$/', $msg, $m ) ) {
			$text = $m[1] ? sprintf( /* translators: %d: number of menus */ _n( '%d menu created.', '%d menus created.', (int) $m[1], 'gami-surgical' ), (int) $m[1] ) : __( 'Every menu location already has a menu, so nothing was changed.', 'gami-surgical' );
			echo '<div class="notice notice-success"><p>' . esc_html( $text ) . '</p></div>';
		} elseif ( 'saved' === $msg ) {
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'gami-surgical' ) . '</p></div>';
		}
		?>

		<div class="card" style="max-width:900px">
			<h2><?php esc_html_e( '1. Pages', 'gami-surgical' ); ?></h2>
			<p><?php esc_html_e( 'Creates every page below that is missing. Pages that already exist are replaced in place: title, address, parent page and published status are reset to the theme\'s. Their content and Elementor sections are kept.', 'gami-surgical' ); ?></p>
			<p><?php esc_html_e( 'This also runs automatically when you install the theme or upload a new version.', 'gami-surgical' ); ?></p>
			<?php gami_setup_button( 'pages', __( 'Create / replace pages', 'gami-surgical' ), true ); ?>
			<?php
			$r = get_option( 'gami_setup_report' );
			if ( $r ) {
				printf(
					'<p class="description">%s</p>',
					esc_html(
						sprintf(
							/* translators: 1: time ago, 2: created, 3: replaced */
							__( 'Last run %1$s ago: %2$d created, %3$d replaced.', 'gami-surgical' ),
							human_time_diff( (int) $r['time'] ),
							count( $r['created'] ),
							count( $r['updated'] )
						)
					)
				);
			}
			?>
		</div>

		<div class="card" style="max-width:900px">
			<h2><?php esc_html_e( '2. Homepage', 'gami-surgical' ); ?></h2>
			<?php if ( $home && $front === $home->ID ) : ?>
				<p><?php esc_html_e( 'Your homepage is the Home page. Edit it with Elementor to design it.', 'gami-surgical' ); ?>
					<a href="<?php echo esc_url( get_edit_post_link( $home->ID ) ); ?>"><?php esc_html_e( 'Edit Home', 'gami-surgical' ); ?></a></p>
			<?php else : ?>
				<p><?php esc_html_e( 'Use the Home page as your homepage and the Blog page for posts (Settings > Reading).', 'gami-surgical' ); ?></p>
				<?php gami_setup_button( 'home', __( 'Use Home as homepage', 'gami-surgical' ) ); ?>
			<?php endif; ?>
		</div>

		<div class="card" style="max-width:900px">
			<h2><?php esc_html_e( '3. Menus (optional)', 'gami-surgical' ); ?></h2>
			<p><?php esc_html_e( 'Creates a Main menu, Footer menu and Legal links from these pages, only for menu locations that are empty. Existing menus are never changed.', 'gami-surgical' ); ?></p>
			<?php gami_setup_button( 'menus', __( 'Create menus for empty locations', 'gami-surgical' ) ); ?>
		</div>

		<div class="card" style="max-width:900px">
			<h2><?php esc_html_e( 'Settings', 'gami-surgical' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'gami_setup' ); ?>
				<input type="hidden" name="gami_setup_action" value="options">
				<p><label><input type="checkbox" name="gami_setup_auto" value="1" <?php checked( get_option( 'gami_setup_auto', 1 ) ); ?>> <?php esc_html_e( 'Create / replace pages automatically when a new theme version is uploaded', 'gami-surgical' ); ?></label></p>
				<p><label><input type="checkbox" name="gami_setup_use_home" value="1" <?php checked( get_option( 'gami_setup_use_home', 1 ) ); ?>> <?php esc_html_e( 'On first setup, use the Home page as the homepage (only if the site shows latest posts)', 'gami-surgical' ); ?></label></p>
				<?php submit_button( __( 'Save settings', 'gami-surgical' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>

		<h2><?php esc_html_e( 'Theme pages', 'gami-surgical' ); ?></h2>
		<table class="widefat striped" style="max-width:900px">
			<thead><tr><th><?php esc_html_e( 'Page', 'gami-surgical' ); ?></th><th><?php esc_html_e( 'Address', 'gami-surgical' ); ?></th><th><?php esc_html_e( 'Group', 'gami-surgical' ); ?></th><th><?php esc_html_e( 'Status', 'gami-surgical' ); ?></th></tr></thead>
			<tbody>
			<?php
			foreach ( $pages as $slug => $def ) :
				$path = $def[1] ? $def[1] . '/' . $slug : $slug;
				$pg   = get_page_by_path( $path );
				$ok   = $pg && 'publish' === $pg->post_status;
				?>
				<tr>
					<td>
						<?php if ( $pg ) : ?>
							<strong><a href="<?php echo esc_url( get_edit_post_link( $pg->ID ) ); ?>"><?php echo esc_html( $def[0] ); ?></a></strong>
							<?php if ( did_action( 'elementor/loaded' ) ) : ?>
								| <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $pg->ID . '&action=elementor' ) ); ?>"><?php esc_html_e( 'Elementor', 'gami-surgical' ); ?></a>
							<?php endif; ?>
							| <a href="<?php echo esc_url( get_permalink( $pg ) ); ?>" target="_blank"><?php esc_html_e( 'View', 'gami-surgical' ); ?></a>
						<?php else : ?>
							<strong><?php echo esc_html( $def[0] ); ?></strong>
						<?php endif; ?>
					</td>
					<td><code>/<?php echo esc_html( $path ); ?>/</code></td>
					<td><?php echo esc_html( $groups[ $def[2] ] ?? '' ); ?></td>
					<td><?php echo $ok ? '&#10003; ' . esc_html__( 'Ready', 'gami-surgical' ) : '<span style="color:#b32d2e">' . esc_html__( 'Missing or not published', 'gami-surgical' ) . '</span>'; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	<?php
}

/**
 * One-time notice after setup ran automatically.
 */
function gami_setup_notice() {
	if ( ! get_option( 'gami_setup_notice' ) || ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'themes', 'edit-page' ), true ) ) {
		return;
	}
	$r = get_option( 'gami_setup_report', array() );
	printf(
		'<div class="notice notice-info is-dismissible"><p><strong>%1$s</strong> %2$s <a href="%3$s">%4$s</a></p></div>',
		esc_html__( 'Gami Surgical:', 'gami-surgical' ),
		esc_html(
			sprintf(
				/* translators: 1: created, 2: replaced */
				__( 'theme pages are ready (%1$d created, %2$d replaced, content kept).', 'gami-surgical' ),
				count( $r['created'] ?? array() ),
				count( $r['updated'] ?? array() )
			)
		),
		esc_url( admin_url( 'themes.php?page=gami-setup' ) ),
		esc_html__( 'Open Gami Setup', 'gami-surgical' )
	);
}
add_action( 'admin_notices', 'gami_setup_notice' );
