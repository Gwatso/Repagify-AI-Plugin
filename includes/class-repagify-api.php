<?php
/**
 * HTTP client for the Repagify service.
 *
 * Every outbound request in this plugin goes through this class. No other file
 * may call the HTTP API directly.
 *
 * @package Repagify
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Talks to the Repagify REST API over the WordPress HTTP API.
 *
 * The service is not live yet, so every method is written to fail gracefully.
 * A 404 comes back as a plain WP_Error explaining that the endpoint does not
 * exist yet, rather than as a fatal or a confusing raw response.
 *
 * @since 0.1.0
 */
class Repagify_API {

	/**
	 * Seconds to wait for a response before giving up.
	 *
	 * @var int
	 */
	const TIMEOUT = 30;

	/**
	 * API key used for Bearer authentication.
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * API base URL, without a trailing slash.
	 *
	 * @var string
	 */
	protected $api_url;

	/**
	 * Builds a client.
	 *
	 * @since 0.1.0
	 *
	 * @param string|null $api_key Key to authenticate with. Defaults to the stored key.
	 * @param string|null $api_url Base URL to call. Defaults to the stored URL.
	 */
	public function __construct( $api_key = null, $api_url = null ) {
		$this->api_key = null === $api_key
			? Repagify_Settings::get_api_key()
			: (string) $api_key;

		$this->api_url = null === $api_url
			? Repagify_Settings::get_api_url()
			: untrailingslashit( (string) $api_url );
	}

	/**
	 * Fetches the account: plan tier, usage and conversions remaining.
	 *
	 * @since 0.1.0
	 *
	 * @return array|WP_Error Decoded account payload, or an error.
	 */
	public function get_me() {
		return $this->request( 'GET', '/me' );
	}

	/**
	 * Asks the service to repurpose a piece of text.
	 *
	 * @since 0.1.0
	 *
	 * @param string $text        Source text, already stripped of markup.
	 * @param string $output_type Requested output, e.g. 'linkedin_post'.
	 * @param string $tone        Requested tone, e.g. 'professional'.
	 * @return array|WP_Error Decoded generation payload, or an error.
	 */
	public function generate( $text, $output_type, $tone ) {
		$text = trim( (string) $text );

		if ( '' === $text ) {
			return new WP_Error(
				'repagify_empty_source',
				__( 'There is no text to repurpose.', 'repagify' )
			);
		}

		return $this->request(
			'POST',
			'/generate',
			array(
				'text'        => $text,
				'output_type' => (string) $output_type,
				'tone'        => (string) $tone,
			)
		);
	}

	/**
	 * Performs a request against the Repagify API.
	 *
	 * @since 0.1.0
	 *
	 * @param string $method   HTTP method, 'GET' or 'POST'.
	 * @param string $endpoint Path beginning with a slash, e.g. '/me'.
	 * @param array  $body     Payload, JSON encoded for POST requests.
	 * @return array|WP_Error Decoded response body, or an error.
	 */
	protected function request( $method, $endpoint, $body = array() ) {
		if ( '' === $this->api_key ) {
			return new WP_Error(
				'repagify_no_api_key',
				__( 'Add your Repagify API key and save the settings first.', 'repagify' )
			);
		}

		$url = $this->api_url . $endpoint;

		$args = array(
			'timeout' => self::TIMEOUT,
			'headers' => array(
				'Authorization' => 'Bearer ' . $this->api_key,
				'Accept'        => 'application/json',
			),
		);

		if ( 'POST' === $method ) {
			$args['headers']['Content-Type'] = 'application/json; charset=utf-8';
			$args['body']                    = wp_json_encode( $body );

			$response = wp_remote_post( $url, $args );
		} else {
			$response = wp_remote_get( $url, $args );
		}

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'repagify_http_error',
				sprintf(
					/* translators: %s: transport level error message. */
					__( 'Could not reach Repagify: %s', 'repagify' ),
					$this->redact( $response->get_error_message() )
				)
			);
		}

		return $this->handle_response( $response, $endpoint );
	}

	/**
	 * Turns a raw HTTP response into decoded data or a friendly WP_Error.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $response Response from the HTTP API.
	 * @param string $endpoint Endpoint that was requested.
	 * @return array|WP_Error
	 */
	protected function handle_response( $response, $endpoint ) {
		$status = (int) wp_remote_retrieve_response_code( $response );
		$raw    = wp_remote_retrieve_body( $response );

		if ( 401 === $status || 403 === $status ) {
			return new WP_Error(
				'repagify_unauthorized',
				__( 'Repagify rejected the saved API key. Check that you copied it correctly, then save again.', 'repagify' ),
				array( 'status' => $status )
			);
		}

		if ( 404 === $status ) {
			return new WP_Error(
				'repagify_not_available',
				sprintf(
					/* translators: %s: API endpoint path, e.g. /generate. */
					__( 'Repagify has not launched this feature yet (%s is not available). Nothing on your site was changed — try again once the service is live.', 'repagify' ),
					$endpoint
				),
				array( 'status' => $status )
			);
		}

		if ( 429 === $status ) {
			return new WP_Error(
				'repagify_rate_limited',
				__( 'Repagify is rate limiting this site. Wait a minute and try again.', 'repagify' ),
				array( 'status' => $status )
			);
		}

		if ( $status >= 500 ) {
			return new WP_Error(
				'repagify_server_error',
				sprintf(
					/* translators: %d: HTTP status code. */
					__( 'Repagify had a problem on its end (HTTP %d). This is not something you can fix — try again shortly.', 'repagify' ),
					$status
				),
				array( 'status' => $status )
			);
		}

		$decoded = json_decode( $raw, true );

		if ( $status >= 400 ) {
			return new WP_Error(
				'repagify_request_failed',
				$this->error_message_from( $decoded, $status ),
				array( 'status' => $status )
			);
		}

		if ( ! is_array( $decoded ) ) {
			return new WP_Error(
				'repagify_bad_response',
				__( 'Repagify returned a response this plugin could not read.', 'repagify' ),
				array( 'status' => $status )
			);
		}

		return $decoded;
	}

	/**
	 * Builds a readable message from an error response body.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $decoded Decoded response body.
	 * @param int   $status  HTTP status code.
	 * @return string
	 */
	protected function error_message_from( $decoded, $status ) {
		$message = '';

		if ( is_array( $decoded ) ) {
			foreach ( array( 'message', 'error', 'detail' ) as $key ) {
				if ( isset( $decoded[ $key ] ) && is_string( $decoded[ $key ] ) ) {
					$message = $decoded[ $key ];
					break;
				}
			}
		}

		if ( '' === $message ) {
			return sprintf(
				/* translators: %d: HTTP status code. */
				__( 'Repagify refused the request (HTTP %d).', 'repagify' ),
				$status
			);
		}

		return $this->redact( sanitize_text_field( $message ) );
	}

	/**
	 * Removes the API key from a string before it can be surfaced anywhere.
	 *
	 * Transport errors occasionally echo request headers back. This guarantees
	 * the key cannot leak into an admin notice through that path.
	 *
	 * @since 0.1.0
	 *
	 * @param string $message Message that may contain the key.
	 * @return string
	 */
	protected function redact( $message ) {
		if ( '' === $this->api_key ) {
			return (string) $message;
		}

		return str_replace( $this->api_key, '[redacted]', (string) $message );
	}
}
