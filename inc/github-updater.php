<?php
/**
 * Theme updates from GitHub Releases.
 *
 * Checks the latest release of GAMI_GITHUB_REPO and, when its tag (e.g.
 * "v1.14.0") is newer than the installed theme's Version, shows the normal
 * "Update now" button in Appearance → Themes and Dashboard → Updates.
 *
 * Each release needs a zip asset of the theme (gami-surgical.zip) whose top
 * folder holds style.css. The release notes are shown as the changelog.
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

/**
 * Folder this theme is installed under. WordPress keys theme updates by
 * folder name, so it is read at runtime instead of hardcoded.
 */
function gami_theme_slug() {
	return get_template();
}

/**
 * Latest release as array( version, package, url, notes, published ).
 * Cached for 12 hours; failures are cached for 1 hour.
 *
 * @return array|false
 */
function gami_get_latest_release() {
	$cached = get_transient( GAMI_UPDATE_CACHE );
	if ( false !== $cached ) {
		return $cached ? $cached : false;
	}

	$response = wp_remote_get(
		'https://api.github.com/repos/' . GAMI_GITHUB_REPO . '/releases/latest',
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'GamiSurgicalTheme/' . GAMI_VERSION . '; ' . home_url(),
			),
		)
	);

	$release = null;
	if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
		$release = json_decode( wp_remote_retrieve_body( $response ), true );
	}

	$data = false;
	if ( ! empty( $release['tag_name'] ) && empty( $release['draft'] ) ) {
		$package = '';
		foreach ( (array) ( $release['assets'] ?? array() ) as $asset ) {
			$name = $asset['name'] ?? '';
			if ( GAMI_UPDATE_ASSET === $name ) {
				$package = $asset['browser_download_url'];
				break;
			}
			if ( ! $package && '.zip' === substr( $name, -4 ) ) {
				$package = $asset['browser_download_url'];
			}
		}

		if ( $package ) {
			$data = array(
				'version'   => ltrim( $release['tag_name'], 'vV' ),
				'package'   => $package,
				'url'       => $release['html_url'] ?? 'https://github.com/' . GAMI_GITHUB_REPO . '/releases',
				'notes'     => (string) ( $release['body'] ?? '' ),
				'published' => (string) ( $release['published_at'] ?? '' ),
			);
		}
	}

	set_transient( GAMI_UPDATE_CACHE, $data ? $data : 0, $data ? 12 * HOUR_IN_SECONDS : HOUR_IN_SECONDS );
	return $data;
}

/**
 * Add the release to WordPress's theme update list.
 */
function gami_check_for_update( $transient ) {
	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	$slug    = gami_theme_slug();
	$release = gami_get_latest_release();
	if ( ! $release ) {
		return $transient;
	}

	$installed = $transient->checked[ $slug ] ?? wp_get_theme( $slug )->get( 'Version' );
	$item      = array(
		'theme'        => $slug,
		'new_version'  => $release['version'],
		'url'          => $release['url'],
		'package'      => $release['package'],
		'requires'     => wp_get_theme( $slug )->get( 'RequiresWP' ),
		'requires_php' => wp_get_theme( $slug )->get( 'RequiresPHP' ),
	);

	if ( version_compare( $release['version'], $installed, '>' ) ) {
		$transient->response[ $slug ] = $item;
	} else {
		unset( $transient->response[ $slug ] );
		$transient->no_update[ $slug ] = $item;
	}

	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'gami_check_for_update' );

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
 * The zip may unpack as "gami-surgical/", "gami-surgical-1.14.0/" or similar.
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
