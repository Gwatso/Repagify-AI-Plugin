<?php
/**
 * Uninstall cleanup.
 *
 * Runs when the site owner deletes the plugin from the Plugins screen.
 *
 * @package Repagify
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes the plugin's options, caches and post meta from the current site.
 *
 * @since 0.1.0
 *
 * @return void
 */
function repagify_uninstall_site() {
	delete_option( 'repagify_settings' );
	delete_transient( 'repagify_scan_cache' );
	delete_transient( 'repagify_account_cache' );
	delete_transient( 'repagify_account_failure' );
	delete_post_meta_by_key( '_repagify_converted' );
}

if ( is_multisite() ) {
	$repagify_site_ids = get_sites(
		array(
			'fields'                 => 'ids',
			'number'                 => 0,
			'update_site_meta_cache' => false,
		)
	);

	foreach ( $repagify_site_ids as $repagify_site_id ) {
		switch_to_blog( $repagify_site_id );
		repagify_uninstall_site();
		restore_current_blog();
	}

	unset( $repagify_site_ids, $repagify_site_id );
} else {
	repagify_uninstall_site();
}
