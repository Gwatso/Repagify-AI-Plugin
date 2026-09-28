<?php
/**
 * Plugin Name:       Repagify
 * Plugin URI:        https://repagify.afriflare.com/
 * Description:       Connects your site to Repagify, an AI content repurposing platform. Turns published posts into SEO blog posts, LinkedIn posts, X threads and newsletters.
 * Version:           0.5.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Afriflare
 * Author URI:        https://repagify.afriflare.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       repagify
 * Domain Path:       /languages
 *
 * @package Repagify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'REPAGIFY_VERSION', '0.5.0' );
define( 'REPAGIFY_FILE', __FILE__ );
define( 'REPAGIFY_PATH', plugin_dir_path( __FILE__ ) );
define( 'REPAGIFY_URL', plugin_dir_url( __FILE__ ) );

/**
 * Fallback API base URL, used until the site owner overrides it in settings.
 */
define( 'REPAGIFY_DEFAULT_API_URL', 'https://repagify.afriflare.com/api/v1' );

/**
 * Where site owners without an account are sent to create a free one.
 */
define( 'REPAGIFY_SIGNUP_URL', 'https://repagify.afriflare.com/signup' );

require_once REPAGIFY_PATH . 'includes/class-repagify-settings.php';
require_once REPAGIFY_PATH . 'includes/class-repagify-formats.php';
require_once REPAGIFY_PATH . 'includes/class-repagify-content.php';
require_once REPAGIFY_PATH . 'includes/class-repagify-api.php';
require_once REPAGIFY_PATH . 'includes/class-repagify-quota.php';
require_once REPAGIFY_PATH . 'includes/class-repagify-scanner.php';

if ( is_admin() ) {
	require_once REPAGIFY_PATH . 'admin/class-repagify-admin.php';
}

/**
 * Boots the plugin once WordPress has loaded every plugin.
 *
 * @since 0.1.0
 *
 * @return void
 */
function repagify_bootstrap() {
	Repagify_Settings::init();

	if ( is_admin() ) {
		$admin = new Repagify_Admin();
		$admin->init();
	}
}
add_action( 'plugins_loaded', 'repagify_bootstrap' );

/**
 * Loads translations.
 *
 * Hooked to init because loading a text domain any earlier is flagged as an
 * error by WordPress 6.7 and later.
 *
 * @since 0.1.0
 *
 * @return void
 */
function repagify_load_textdomain() {
	load_plugin_textdomain(
		'repagify',
		false,
		dirname( plugin_basename( REPAGIFY_FILE ) ) . '/languages'
	);
}
add_action( 'init', 'repagify_load_textdomain' );

/**
 * Writes the default options the first time the plugin is activated.
 *
 * @since 0.1.0
 *
 * @return void
 */
function repagify_activate() {
	Repagify_Settings::seed_defaults();
}
register_activation_hook( __FILE__, 'repagify_activate' );
