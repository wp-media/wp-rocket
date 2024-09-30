<?php
namespace WP_Rocket\Engine\Plugin;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Manages common hooks for the plugin updater.
 */
class UpdaterApiCommonSubscriber implements Subscriber_Interface {
	const API_HOST = 'api.wp-rocket.me';

	/**
	 * URL to the site’s home.
	 *
	 * @var string
	 */
	private $site_url;

	/**
	 * Current version of the plugin.
	 *
	 * @var string
	 */
	private $plugin_version;

	/**
	 * Key slug used when submitting new settings (POST).
	 *
	 * @var string
	 */
	private $settings_slug;

	/**
	 * The key (1st part of the action) used for the nonce field used on the settings page. It is also used in the page URL.
	 *
	 * @var string
	 */
	private $settings_nonce_key;

	/**
	 * Options instance.
	 *
	 * @var \WP_Rocket\Admin\Options
	 */
	private $plugin_options;

	/**
	 * Constructor
	 *
	 * @param array $args {
	 *     Required arguments to populate the class properties.
	 *
	 *     @type string  $site_url           URL to the site’s home.
	 *     @type string  $plugin_version     Current version of the plugin.
	 *     @type string  $settings_slug      Key slug used when submitting new settings (POST).
	 *     @type string  $settings_nonce_key The key (1st part of the action) used for the nonce field used on the settings page. It is also used in the page URL.
	 *     @type Options $plugin_options     Options instance.
	 * }
	 */
	public function __construct( $args ) {
		foreach ( [ 'site_url', 'plugin_version', 'settings_slug', 'settings_nonce_key', 'plugin_options' ] as $setting ) {
			if ( isset( $args[ $setting ] ) ) {
				$this->$setting = $args[ $setting ];
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public static function get_subscribed_events() {
		return [
			'http_request_args' => [ 'maybe_set_rocket_user_agent', 10, 2 ],
		];
	}

	/**
	 * Force our user agent header when we hit our URLs.
	 *
	 * @param  array  $request An array of request arguments.
	 * @param  string $url     Requested URL.
	 * @return array           An array of requested arguments
	 */
	public function maybe_set_rocket_user_agent( $request, string $url ) {
		if ( strpos( $url, self::API_HOST ) !== false ) {
			$request['user-agent'] = sprintf( '%s;%s', $request['user-agent'], $this->get_rocket_user_agent() );
		}

		return $request;
	}

	/**
	 * Get the user agent to use when requesting the API.
	 *
	 * @return string WP Rocket user agent
	 */
	public function get_rocket_user_agent() {
		$consumer_key   = $this->get_current_option( 'consumer_key' );
		$consumer_email = $this->get_current_option( 'consumer_email' );
		$php_version    = preg_replace( '@^(\d+\.\d+).*@', '\1', phpversion() );

		return sprintf( 'WP-Rocket|%s|%s|%s|%s|%s;', $this->plugin_version, $consumer_key, $consumer_email, esc_url( $this->site_url ), $php_version );
	}

	/**
	 * Get a plugin option. If the value is currently being posted through the settings page, it is returned instead of the one stored in the database.
	 *
	 * @param  string $field_name Name of a plugin option.
	 * @return string
	 */
	protected function get_current_option( $field_name ) {
		if ( current_user_can( 'rocket_manage_options' ) && wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce' ), $this->settings_nonce_key . '-options' ) ) {
			$posted = filter_input( INPUT_POST, $this->settings_slug, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

			if ( ! empty( $posted[ $field_name ] ) ) {
				// The value has been posted through the settings page.
				return sanitize_text_field( $posted[ $field_name ] );
			}
		}

		$option_value = $this->plugin_options->get( $field_name );

		if ( $option_value && is_string( $option_value ) ) {
			return $option_value;
		}

		return '';
	}
}
