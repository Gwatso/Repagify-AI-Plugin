<?php
/**
 * Admin menu, assets and AJAX endpoints.
 *
 * @package Repagify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Repagify admin pages and handles their AJAX requests.
 *
 * @since 0.1.0
 */
class Repagify_Admin {

	/**
	 * Nonce action shared by every Repagify AJAX endpoint.
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'repagify_ajax';

	/**
	 * Capability required to reach any Repagify screen.
	 *
	 * @var string
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Menu hook suffixes for the plugin's own screens.
	 *
	 * @var string[]
	 */
	protected $screens = array();

	/**
	 * Registers admin hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_filter(
			'plugin_action_links_' . plugin_basename( REPAGIFY_FILE ),
			array( $this, 'add_action_links' )
		);

		add_action( 'wp_ajax_repagify_test_connection', array( $this, 'ajax_test_connection' ) );
	}

	/**
	 * Adds the top level Repagify menu, whose first page is Settings.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_menu() {
		$this->screens['settings'] = add_menu_page(
			__( 'Repagify', 'repagify' ),
			__( 'Repagify', 'repagify' ),
			self::CAPABILITY,
			Repagify_Settings::PAGE,
			array( $this, 'render_settings_page' ),
			'dashicons-book-alt',
			58
		);

		// Names the auto-generated first submenu entry, leaving room for the
		// scanner dashboard to be added alongside it later.
		add_submenu_page(
			Repagify_Settings::PAGE,
			__( 'Repagify settings', 'repagify' ),
			__( 'Settings', 'repagify' ),
			self::CAPABILITY,
			Repagify_Settings::PAGE,
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Adds a Settings shortcut to the plugin list row.
	 *
	 * @since 0.1.0
	 *
	 * @param string[] $links Existing action links.
	 * @return string[]
	 */
	public function add_action_links( $links ) {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return $links;
		}

		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=' . Repagify_Settings::PAGE ) ),
			esc_html__( 'Settings', 'repagify' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Loads the plugin's stylesheet and script on its own screens only.
	 *
	 * @since 0.1.0
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( ! in_array( $hook_suffix, $this->screens, true ) ) {
			return;
		}

		wp_enqueue_style(
			'repagify-admin',
			REPAGIFY_URL . 'admin/assets/admin.css',
			array(),
			REPAGIFY_VERSION
		);

		wp_enqueue_script(
			'repagify-admin',
			REPAGIFY_URL . 'admin/assets/admin.js',
			array(),
			REPAGIFY_VERSION,
			true
		);

		wp_localize_script(
			'repagify-admin',
			'repagifyAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
				'i18n'    => array(
					'testing'      => __( 'Testing…', 'repagify' ),
					'genericError' => __( 'Something went wrong. Please try again.', 'repagify' ),
					'planLabel'    => __( 'Plan', 'repagify' ),
					'remaining'    => __( 'Conversions remaining', 'repagify' ),
				),
			)
		);
	}

	/**
	 * Renders the settings page.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_die( esc_html__( 'You do not have permission to manage these settings.', 'repagify' ) );
		}

		require REPAGIFY_PATH . 'admin/views/settings.php';
	}

	/**
	 * Tests that the saved key and URL can reach the service.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function ajax_test_connection() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! current_user_can( self::CAPABILITY ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to do that.', 'repagify' ) ),
				403
			);
		}

		$api      = new Repagify_API();
		$response = $api->get_me();

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'message' => $response->get_error_message(),
					'code'    => $response->get_error_code(),
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'Connected to Repagify.', 'repagify' ),
				'account' => $this->summarise_account( $response ),
			)
		);
	}

	/**
	 * Reduces an account payload to the few fields the page displays.
	 *
	 * @since 0.1.0
	 *
	 * @param array $account Decoded /me response.
	 * @return array
	 */
	protected function summarise_account( $account ) {
		$summary = array(
			'plan'                  => '',
			'conversions_remaining' => null,
		);

		foreach ( array( 'plan', 'plan_tier', 'tier' ) as $key ) {
			if ( isset( $account[ $key ] ) && is_scalar( $account[ $key ] ) ) {
				$summary['plan'] = sanitize_text_field( (string) $account[ $key ] );
				break;
			}
		}

		foreach ( array( 'conversions_remaining', 'remaining', 'credits_remaining' ) as $key ) {
			if ( isset( $account[ $key ] ) && is_numeric( $account[ $key ] ) ) {
				$summary['conversions_remaining'] = (int) $account[ $key ];
				break;
			}
		}

		return $summary;
	}
}
