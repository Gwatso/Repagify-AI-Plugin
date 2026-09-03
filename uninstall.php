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
 * Removes the plugin's options from the current site.
 *
 * Phase 1 stores a single option. Post meta cleanup is added alongside the
 * generation flow that creates it.
 *
 * @since 0.1.0
 *
 * @return void
 */
function repagify_uninstall_site() {
	delete_option( 'repagify_settings' );
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
