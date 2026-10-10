<?php
/**
 * Theme updates from GitHub Releases.
 *
 * Checks the latest release of GAMI_GITHUB_REPO and, when its tag (e.g.
 * "v1.17.0") is newer than the installed theme's Version, shows the normal
 * "Update now" button in Appearance → Themes and Dashboard → Updates.
 *
 * Each release needs a zip asset of the theme (gami-surgical.zip) whose top
 * folder holds style.css. The release notes are shown as the changelog.
 *
 * The update is added whenever WordPress reads its theme update list, so it
 * appears even before WordPress's own twice-daily check runs, and even when
 * the server cannot reach wordpress.org.
 *
 * @package Gami_Surgical
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'GAMI_GITHUB_REPO' ) ) {
	define( 'GAMI_GITHUB_REPO', 'zaheerabas/gami-surgical' );
}
define( 'GAMI_UPDATE_ASSET', 'gami-surgical.zip' );
define( 'GAMI_UPDATE_CACHE', 'gami_github_release' );
define( 'GAMI_UPDATE_STATUS', 'gami_github_status' );

/**
 * Folder this theme is installed under. WordPress keys theme updates by
 * folder name, so it is read at runtime instead of hardcoded.
 */
function gami_theme_slug() {
	return get_template();
}

/**
 * Latest release from the GitHub API.
 *
 * @return array|WP_Error
 */
function gami_fetch_release_api() {
	$response = wp_remote_get(
		'https://api.github.com/repos/' . GAMI_GITHUB_REPO . '/releases/latest',
		array(
			'timeout' => 8,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'GamiSurgicalTheme/' . GAMI_VERSION . '; ' . home_url(),
			),
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $code ) {
		/* translators: %d: HTTP status code */
		return new WP_Error( 'gami_api_http', sprintf( __( 'GitHub API answered %d.', 'gami-surgical' ), $code ) );
	}

	$release = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $release['tag_name'] ) ) {
		return new WP_Error( 'gami_api_body', __( 'GitHub API returned no release.', 'gami-surgical' ) );
	}

	$package = '';
	foreach ( (array) ( $release['assets'] ?? array() ) as $asset ) {
		$name = $asset['name'] ?? '';
		if ( GAMI_UPDATE_ASSET === $name ) {
			$package = $asset['browser_download_url'];
			break;
		}
		if ( ! $package && '.zip' === substr( $name, -4 ) && false === strpos( $name, 'articles' ) ) {
			$package = $asset['browser_download_url'];
		}
	}
	if ( ! $package ) {
		return new WP_Error( 'gami_api_asset', __( 'The latest release has no theme zip attached.', 'gami-surgical' ) );
	}

	return array(
		'version'   => ltrim( $release['tag_name'], 'vV' ),
		'package'   => $package,
		'url'       => $release['html_url'] ?? 'https://github.com/' . GAMI_GITHUB_REPO . '/releases',
		'notes'     => (string) ( $release['body'] ?? '' ),
		'published' => (string) ( $release['published_at'] ?? '' ),
	);
}

/**
 * Latest release without the API (no hourly limit): github.com/…/releases/latest
 * redirects to …/releases/tag/vX.Y.Z, and the zip has a fixed download address.
 *
 * @return array|WP_Error
 */
function gami_fetch_release_redirect() {
	$response = wp_remote_head(
		'https://github.com/' . GAMI_GITHUB_REPO . '/releases/latest',
		array(
			'timeout'     => 8,
			'redirection' => 0,
			'user-agent'  => 'GamiSurgicalTheme/' . GAMI_VERSION,
		)
	);
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	$location = (string) wp_remote_retrieve_header( $response, 'location' );
	if ( ! preg_match( '#/releases/tag/([^/?\#]+)#', $location, $m ) ) {
		return new WP_Error( 'gami_redirect', __( 'GitHub did not say which release is the latest.', 'gami-surgical' ) );
	}
	$tag = rawurldecode( $m[1] );

	return array(
		'version'   => ltrim( $tag, 'vV' ),
		'package'   => 'https://github.com/' . GAMI_GITHUB_REPO . '/releases/download/' . rawurlencode( $tag ) . '/' . GAMI_UPDATE_ASSET,
		'url'       => 'https://github.com/' . GAMI_GITHUB_REPO . '/releases/tag/' . rawurlencode( $tag ),
		'notes'     => '',
		'published' => '',
	);
}

/**
 * Latest release as array( version, package, url, notes, published ).
 * Cached for 3 hours; failures are retried after 30 minutes.
 *
 * @param bool $force Skip the cache.
 * @return array|false
 */
function gami_get_latest_release( $force = false ) {
	if ( ! $force ) {
		$cached = get_transient( GAMI_UPDATE_CACHE );
		if ( false !== $cached ) {
			return $cached ? $cached : false;
		}
	}

	$data = gami_fetch_release_api();
	if ( is_wp_error( $data ) ) {
		$api_error = $data->get_error_message();
		$data      = gami_fetch_release_redirect();
		if ( is_wp_error( $data ) ) {
			$data->add( 'gami_api', $api_error );
		}
	}

	if ( is_wp_error( $data ) ) {
		set_transient( GAMI_UPDATE_CACHE, 0, 30 * MINUTE_IN_SECONDS );
		update_option(
			GAMI_UPDATE_STATUS,
			array(
				'time'  => time(),
				'error' => implode( ' ', $data->get_error_messages() ),
			),
			false
		);
		return false;
	}

	set_transient( GAMI_UPDATE_CACHE, $data, 3 * HOUR_IN_SECONDS );
	update_option(
		GAMI_UPDATE_STATUS,
		array(
			'time'    => time(),
			'version' => $data['version'],
		),
		false
	);
	return $data;
}

/**
 * Add (or clear) this theme's entry in a theme update list.
 *
 * @param object|false $transient Value of the update_themes site transient.
 * @return object|false
 */
function gami_add_theme_update( $transient ) {
	// Only where updates are shown or run; never slows down visitors.
	if ( ! is_admin() && ! wp_doing_cron() && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return $transient;
	}

	$release = gami_get_latest_release();
	if ( ! $release ) {
		return $transient;
	}

	if ( ! is_object( $transient ) ) {
		$transient = new stdClass();
	}
	foreach ( array( 'response', 'no_update', 'checked' ) as $key ) {
		if ( ! isset( $transient->$key ) || ! is_array( $transient->$key ) ) {
			$transient->$key = array();
		}
	}

	$slug      = gami_theme_slug();
	$theme     = wp_get_theme( $slug );
	$installed = $theme->get( 'Version' );
	$item      = array(
		'theme'        => $slug,
		'new_version'  => $release['version'],
		'url'          => $release['url'],
		'package'      => $release['package'],
		'requires'     => $theme->get( 'RequiresWP' ),
		'requires_php' => $theme->get( 'RequiresPHP' ),
	);

	if ( version_compare( $release['version'], $installed, '>' ) ) {
		$transient->response[ $slug ] = $item;
		unset( $transient->no_update[ $slug ] );
	} else {
		unset( $transient->response[ $slug ] );
		$transient->no_update[ $slug ] = $item;
	}

	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'gami_add_theme_update' );
add_filter( 'site_transient_update_themes', 'gami_add_theme_update' );

/**
 * "View version details" popup: show the GitHub release notes.
 */
function gami_theme_info( $result, $action, $args ) {
	if ( 'theme_information' !== $action || ( $args->slug ?? '' ) !== gami_theme_slug() ) {
		return $result;
	}

	$release = gami_get_latest_release();
	if ( ! $release ) {
		return $result;
	}

	$theme = wp_get_theme( gami_theme_slug() );

	return (object) array(
		'name'          => $theme->get( 'Name' ),
		'slug'          => gami_theme_slug(),
		'version'       => $release['version'],
		'author'        => $theme->get( 'Author' ),
		'homepage'      => $release['url'],
		'download_link' => $release['package'],
		'last_updated'  => $release['published'],
		'requires'      => $theme->get( 'RequiresWP' ),
		'requires_php'  => $theme->get( 'RequiresPHP' ),
		'sections'      => array(
			'description' => $theme->get( 'Description' ),
			'changelog'   => $release['notes'] ? wpautop( esc_html( $release['notes'] ) ) : esc_html__( 'See the release on GitHub.', 'gami-surgical' ),
		),
	);
}
add_filter( 'themes_api', 'gami_theme_info', 20, 3 );

/**
 * The zip may unpack as "gami-surgical/", "gami-surgical-1.17.0/" or similar.
 * Rename it to the installed folder so the update replaces the theme in place
 * instead of installing a second copy.
 */
function gami_fix_update_folder( $source, $remote_source, $upgrader, $hook_extra = array() ) {
	global $wp_filesystem;

	$slug = gami_theme_slug();
	if ( ( $hook_extra['theme'] ?? '' ) !== $slug ) {
		return $source;
	}

	$desired = trailingslashit( $remote_source ) . $slug;
	if ( untrailingslashit( $source ) === $desired ) {
		return $source;
	}

	if ( $wp_filesystem && $wp_filesystem->move( untrailingslashit( $source ), $desired, true ) ) {
		return trailingslashit( $desired );
	}

	return new WP_Error( 'gami_update_folder', __( 'Could not prepare the Gami Surgical update folder.', 'gami-surgical' ) );
}
add_filter( 'upgrader_source_selection', 'gami_fix_update_folder', 10, 4 );

/**
 * Forget the cached release after an update and on Dashboard → Updates → "Check again".
 */
function gami_clear_release_cache_after_update( $upgrader, $options ) {
	if ( 'theme' === ( $options['type'] ?? '' ) ) {
		delete_transient( GAMI_UPDATE_CACHE );
	}
}
add_action( 'upgrader_process_complete', 'gami_clear_release_cache_after_update', 10, 2 );

function gami_clear_release_cache_on_force_check() {
	if ( ! empty( $_GET['force-check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		delete_transient( GAMI_UPDATE_CACHE );
	}
}
add_action( 'load-update-core.php', 'gami_clear_release_cache_on_force_check' );

/**
 * Themes page → "Check now": ask GitHub straight away.
 */
function gami_handle_update_check_now() {
	if ( empty( $_GET['gami-check-updates'] ) || ! current_user_can( 'update_themes' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	check_admin_referer( 'gami-check-updates' );
	gami_get_latest_release( true );
	delete_site_transient( 'update_themes' );
	wp_safe_redirect( admin_url( 'themes.php?gami-checked=1' ) );
	exit;
}
add_action( 'load-themes.php', 'gami_handle_update_check_now' );

/**
 * Status line on Appearance → Themes: installed version, what GitHub has,
 * when it was checked, and any error.
 */
function gami_update_status_notice() {
	$screen = get_current_screen();
	if ( ! $screen || 'themes' !== $screen->id || ! current_user_can( 'update_themes' ) ) {
		return;
	}

	$installed = wp_get_theme( gami_theme_slug() )->get( 'Version' );
	$status    = get_option( GAMI_UPDATE_STATUS, array() );
	$check_url = wp_nonce_url( admin_url( 'themes.php?gami-check-updates=1' ), 'gami-check-updates' );
	$class     = 'notice-info';

	if ( ! empty( $status['error'] ) ) {
		$class = 'notice-warning';
		/* translators: %s: error message */
		$state = sprintf( __( 'Could not reach GitHub: %s', 'gami-surgical' ), $status['error'] );
	} elseif ( ! empty( $status['version'] ) && version_compare( $status['version'], $installed, '>' ) ) {
		$class = 'notice-warning';
		/* translators: %s: version */
		$state = sprintf( __( 'Version %s is available. Use “Update now” on the theme below.', 'gami-surgical' ), $status['version'] );
	} elseif ( ! empty( $status['version'] ) ) {
		$state = __( 'You have the latest version.', 'gami-surgical' );
	} else {
		$state = __( 'Not checked yet.', 'gami-surgical' );
	}

	$checked = ! empty( $status['time'] )
		/* translators: %s: time ago */
		? sprintf( __( 'Checked %s ago.', 'gami-surgical' ), human_time_diff( (int) $status['time'] ) )
		: '';
	?>
	<div class="notice <?php echo esc_attr( $class ); ?>">
		<p>
			<strong><?php echo esc_html( wp_get_theme( gami_theme_slug() )->get( 'Name' ) ); ?> <?php echo esc_html( $installed ); ?></strong>
			— <?php echo esc_html( $state ); ?> <?php echo esc_html( $checked ); ?>
			<a href="<?php echo esc_url( $check_url ); ?>"><?php esc_html_e( 'Check now', 'gami-surgical' ); ?></a>
			| <a href="<?php echo esc_url( 'https://github.com/' . GAMI_GITHUB_REPO . '/releases' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Releases on GitHub', 'gami-surgical' ); ?></a>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'gami_update_status_notice' );
