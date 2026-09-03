<?php
/**
 * Options storage and Settings API registration.
 *
 * @package Repagify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and reads the Repagify settings.
 *
 * The API key is stored in wp_options but is never rendered in full. The
 * settings form only ever shows a mask, and the input that writes the key is
 * always submitted empty unless the site owner is deliberately replacing it.
 *
 * @since 0.1.0
 */
class Repagify_Settings {

	/**
	 * Option name holding every plugin setting.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'repagify_settings';

	/**
	 * Settings API group name.
	 *
	 * @var string
	 */
	const GROUP = 'repagify_settings_group';

	/**
	 * Slug of the settings page the sections are attached to.
	 *
	 * @var string
	 */
	const PAGE = 'repagify-settings';

	/**
	 * Hooks the Settings API registration.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Returns the default value for every setting.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'api_key' => '',
			'api_url' => REPAGIFY_DEFAULT_API_URL,
		);
	}

	/**
	 * Returns every setting, merged over the defaults.
	 *
	 * @since 0.1.0
	 *
	 * @return array
	 */
	public static function all() {
		$stored = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return wp_parse_args( $stored, self::defaults() );
	}

	/**
	 * Returns the stored API key.
	 *
	 * Callers must never render, log or transmit this value anywhere other than
	 * the Authorization header built by Repagify_API.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public static function get_api_key() {
		$settings = self::all();

		return (string) $settings['api_key'];
	}

	/**
	 * Whether an API key has been saved.
	 *
	 * @since 0.1.0
	 *
	 * @return bool
	 */
	public static function has_api_key() {
		return '' !== self::get_api_key();
	}

	/**
	 * Returns a masked preview of the saved key, safe to print.
	 *
	 * Everything but the last four characters is replaced with bullets, so the
	 * site owner can tell which key is saved without the key itself reaching the
	 * page. Short keys are masked completely.
	 *
	 * @since 0.1.0
	 *
	 * @return string Empty string when no key is saved.
	 */
	public static function masked_api_key() {
		$key = self::get_api_key();

		if ( '' === $key ) {
			return '';
		}

		$length = strlen( $key );

		if ( $length < 12 ) {
			return str_repeat( "\xE2\x80\xA2", 12 );
		}

		return str_repeat( "\xE2\x80\xA2", 12 ) . substr( $key, -4 );
	}

	/**
	 * Returns the API base URL, without a trailing slash.
	 *
	 * @since 0.1.0
	 *
	 * @return string
	 */
	public static function get_api_url() {
		$settings = self::all();
		$url      = untrailingslashit( trim( (string) $settings['api_url'] ) );

		return '' === $url ? REPAGIFY_DEFAULT_API_URL : $url;
	}

	/**
	 * Writes the default options if the plugin has never stored any.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function seed_defaults() {
		if ( false === get_option( self::OPTION_NAME, false ) ) {
			add_option( self::OPTION_NAME, self::defaults() );
		}
	}

	/**
	 * Registers the setting, its section and its fields.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function register() {
		register_setting(
			self::GROUP,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'repagify_section_connection',
			__( 'Connection', 'repagify' ),
			array( __CLASS__, 'render_connection_section' ),
			self::PAGE
		);

		add_settings_field(
			'repagify_field_api_key',
			__( 'API key', 'repagify' ),
			array( __CLASS__, 'render_api_key_field' ),
			self::PAGE,
			'repagify_section_connection',
			array( 'label_for' => 'repagify_field_api_key' )
		);

		add_settings_field(
			'repagify_field_api_url',
			__( 'API base URL', 'repagify' ),
			array( __CLASS__, 'render_api_url_field' ),
			self::PAGE,
			'repagify_section_connection',
			array( 'label_for' => 'repagify_field_api_url' )
		);
	}

	/**
	 * Sanitizes the option array before it is written.
	 *
	 * An empty API key field means "keep the saved key", so the key never has to
	 * be printed into the form in order to survive a save.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $input Raw submitted value.
	 * @return array
	 */
	public static function sanitize( $input ) {
		$current = self::all();
		$clean   = self::defaults();

		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$clean['api_key'] = $current['api_key'];

		$submitted_key = isset( $input['api_key'] )
			? trim( sanitize_text_field( wp_unslash( $input['api_key'] ) ) )
			: '';

		if ( '' !== $submitted_key ) {
			$clean['api_key'] = $submitted_key;
		}

		if ( ! empty( $input['remove_api_key'] ) ) {
			$clean['api_key'] = '';
		}

		$submitted_url = isset( $input['api_url'] )
			? esc_url_raw( trim( wp_unslash( $input['api_url'] ) ) )
			: '';

		if ( '' === $submitted_url ) {
			$clean['api_url'] = REPAGIFY_DEFAULT_API_URL;
		} else {
			$clean['api_url'] = untrailingslashit( $submitted_url );
		}

		return $clean;
	}

	/**
	 * Describes the connection section.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function render_connection_section() {
		echo '<p>' . esc_html__( 'Paste the API key from your Repagify account. Once saved, the key is only ever shown as a mask.', 'repagify' ) . '</p>';
	}

	/**
	 * Renders the API key field.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function render_api_key_field() {
		$has_key = self::has_api_key();
		?>
		<?php if ( $has_key ) : ?>
			<p class="repagify-key-preview">
				<code><?php echo esc_html( self::masked_api_key() ); ?></code>
				<span class="repagify-badge repagify-badge--saved"><?php esc_html_e( 'Saved', 'repagify' ); ?></span>
			</p>
		<?php endif; ?>

		<input
			type="password"
			id="repagify_field_api_key"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[api_key]"
			value=""
			class="regular-text"
			autocomplete="off"
			spellcheck="false"
			placeholder="<?php echo $has_key ? esc_attr__( 'Enter a new key to replace the saved one', 'repagify' ) : esc_attr__( 'Paste your Repagify API key', 'repagify' ); ?>"
		/>

		<p class="description">
			<?php if ( $has_key ) : ?>
				<?php esc_html_e( 'Leave this blank to keep the saved key.', 'repagify' ); ?>
				<label for="repagify_field_remove_api_key" class="repagify-inline-label">
					<input
						type="checkbox"
						id="repagify_field_remove_api_key"
						name="<?php echo esc_attr( self::OPTION_NAME ); ?>[remove_api_key]"
						value="1"
					/>
					<?php esc_html_e( 'Delete the saved key', 'repagify' ); ?>
				</label>
			<?php else : ?>
				<?php esc_html_e( 'Find your key in your Repagify account under Settings.', 'repagify' ); ?>
			<?php endif; ?>
		</p>
		<?php
	}

	/**
	 * Renders the API base URL field.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public static function render_api_url_field() {
		?>
		<input
			type="url"
			id="repagify_field_api_url"
			name="<?php echo esc_attr( self::OPTION_NAME ); ?>[api_url]"
			value="<?php echo esc_attr( self::get_api_url() ); ?>"
			class="regular-text code"
			spellcheck="false"
		/>
		<p class="description">
			<?php
			printf(
				/* translators: %s: the default API base URL. */
				esc_html__( 'Leave as %s unless you are pointing this site at a development instance.', 'repagify' ),
				'<code>' . esc_html( REPAGIFY_DEFAULT_API_URL ) . '</code>'
			);
			?>
		</p>
		<?php
	}
}
