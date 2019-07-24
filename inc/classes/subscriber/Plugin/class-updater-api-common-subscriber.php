<?php
namespace WP_Rocket\Subscriber\Plugin;

use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Manages common hooks for the plugin updater.
 *
 * @since  3.3.6
 * @author Grégory Viguier
 */
class Updater_Api_Common_Subscriber implements Subscriber_Interface {

	/**
	 * API’s URL domain.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $api_host;

	/**
	 * URL to the site’s home.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $site_url;

	/**
	 * Current version of the plugin.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $plugin_version;

	/**
	 * Key slug used when submitting new settings (POST).
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $settings_slug;

	/**
	 * The key (1st part of the action) used for the nonce field used on the settings page. It is also used in the page URL.
	 *
	 * @var    string
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $settings_nonce_key;

	/**
	 * Options instance.
	 *
	 * @var    \WP_Rocket\Admin\Options
	 * @since  3.3.6
	 * @access private
	 * @author Grégory Viguier
	 */
	private $plugin_options;

	/**
	 * Constructor
	 *
	 * @since  3.3.6
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param array $args {
	 *     Required arguments to populate the class properties.
	 *
	 *     @type string  $api_host           API’s URL domain.
	 *     @type string  $site_url           URL to the site’s home.
	 *     @type string  $plugin_version     Current version of the plugin.
	 *     @type string  $settings_slug      Key slug used when submitting new settings (POST).
	 *     @type string  $settings_nonce_key The key (1st part of the action) used for the nonce field used on the settings page. It is also used in the page URL.
	 *     @type Options $plugin_options     Options instance.
	 * }
	 */
	public function __construct( $args ) {
		foreach ( [ 'api_host', 'site_url', 'plugin_version', 'settings_slug', 'settings_nonce_key', 'plugin_options' ] as $setting ) {
			if ( isset( $args[ $setting ] ) ) {
				$this->$setting = $args[ $setting ];
			}
		}
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3.6
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'http_request_args' => [ 'maybe_set_rocket_user_agent', 10, 2 ],
		];
	}

	/** ----------------------------------------------------------------------------------------- */
	/** HOOKS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Force our user agent header when we hit our URLs.
	 *
	 * @since  3.3.6
	 * @access public
	 *
	 * @param  array  $request An array of request arguments.
	 * @param  string $url     Requested URL.
	 * @return array           An array of requested arguments
	 */
	public function maybe_set_rocket_user_agent( $request, $url ) {
		if ( ! is_string( $url ) ) {
			return $request;
		}

		if ( $this->api_host && strpos( $url, $this->api_host ) !== false ) {
			$request['user-agent'] = sprintf( '%s;%s', $request['user-agent'], $this->get_rocket_user_agent() );
		}

		return $request;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** TOOLS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the user agent to use when requesting the API.
	 *
	 * @since  3.3.6
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string WP Rocket user agent
	 */
	public function get_rocket_user_agent() {
		$consumer_key   = $this->get_current_option( 'consumer_key' );
		$consumer_email = $this->get_current_option( 'consumer_email' );
		$bonus          = $this->plugin_options && $this->plugin_options->get( 'do_beta' ) ? '+' : '';
		$php_version    = preg_replace( '@^(\d+\.\d+).*@', '\1', phpversion() );

		return sprintf( 'WP-Rocket|%s%s|%s|%s|%s|%s;', $this->plugin_version, $bonus, $consumer_key, $consumer_email, esc_url( $this->site_url ), $php_version );
	}

	/**
	 * Get a plugin option. If the value is currently being posted through the settings page, it is returned instead of the one stored in the database.
	 *
	 * @since  3.3.6
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @param  string $field_name Name of a plugin option.
	 * @return string
	 */
	protected function get_current_option( $field_name ) {
		if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) && wp_verify_nonce( filter_input( INPUT_POST, '_wpnonce' ), $this->settings_nonce_key . '-options' ) ) {
			$posted = filter_input( INPUT_POST, $this->settings_slug, FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

			if ( ! empty( $posted[ $field_name ] ) && is_string( $posted[ $field_name ] ) ) {
				// The value has been posted through the settings page.
				return sanitize_text_field( $posted[ $field_name ] );
			}
		}

		if ( ! $this->plugin_options ) {
			return '';
		}

		$option_value = $this->plugin_options->get( $field_name );

		if ( $option_value && is_string( $option_value ) ) {
			return $option_value;
		}

		return '';
	}
}
