<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Class to check if the current WordPress and PHP versions meet our requirements
 *
 * @since 3.0
 * @author Remy Perona
 */
class WP_Rocket_Requirements_Check {
	/**
	 * Plugin Name
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin filepath
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $plugin_version;

	/**
	 * Plugin previous version
	 *
	 * @var string
	 */
	private $plugin_last_version;

	/**
	 * Required WordPress version
	 *
	 * @var string
	 */
	private $wp_version;

	/**
	 * Required PHP version
	 *
	 * @var string
	 */
	private $php_version;

	/**
	 * WP Rocket options
	 *
	 * @var array
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $args {
	 *     Arguments to populate the class properties.
	 *
	 *     @type string $plugin_name Plugin name.
	 *     @type string $wp_version  Required WordPress version.
	 *     @type string $php_version Required PHP version.
	 *     @type string $plugin_file Plugin filepath.
	 * }
	 */
	public function __construct( $args ) {
		foreach ( array( 'plugin_name', 'plugin_file', 'plugin_version', 'plugin_last_version', 'wp_version', 'php_version' ) as $setting ) {
			if ( isset( $args[ $setting ] ) ) {
				$this->$setting = $args[ $setting ];
			}
		}

		$this->plugin_last_version = version_compare( PHP_VERSION, '5.3' ) >= 0 ? $this->plugin_last_version : '2.10.12';
		$this->options             = get_option( 'wp_rocket_settings' );
	}

	/**
	 * Checks if all requirements are ok, if not, display a notice and the rollback
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	public function check() {
		if ( ! $this->php_passes() || ! $this->wp_passes() ) {

			add_action( 'admin_notices', array( $this, 'notice' ) );
			add_action( 'admin_post_rocket_rollback', array( $this, 'rollback' ) );
			add_filter( 'http_request_args', array( $this, 'add_own_ua' ), 10, 2 );

			return false;
		}

		return true;
	}

	/**
	 * Checks if the current PHP version is equal or superior to the required PHP version
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function php_passes() {
		return version_compare( PHP_VERSION, $this->php_version ) >= 0;
	}

	/**
	 * Checks if the current WordPress version is equal or superior to the required PHP version
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function wp_passes() {
		global $wp_version;

		return version_compare( $wp_version, $this->wp_version ) >= 0;
	}

	/**
	 * Warns if PHP version is less than 5.4 and offers to rollback.
	 *
	 * @since 3.0 Updated minimum PHP version to 5.4 and minimum WordPress version to 4.2
	 * @since 3.0 Moved to class
	 * @since 2.11
	 * @author Remy Perona
	 */
	public function notice() {
		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		// Translators: %1$s = Plugin name, %2$s = Plugin version.
		$message = '<p>' . sprintf( __( 'To function properly, %1$s %2$s requires at least:', 'rocket' ), $this->plugin_name, $this->plugin_version ) . '</p><ul>';

		if ( ! $this->php_passes() ) {
			// Translators: %1$s = PHP version required.
			$message .= '<li>' . sprintf( __( 'PHP %1$s. To use this WP Rocket version, please ask your web host how to upgrade your server to PHP %1$s or higher.', 'rocket' ), $this->php_version ) . '</li>';
		}

		if ( ! $this->wp_passes() ) {
			// Translators: %1$s = WordPress version required.
			$message .= '<li>' . sprintf( __( 'WordPress %1$s. To use this WP Rocket version, please upgrade WordPress to version %1$s or higher.', 'rocket' ), $this->wp_version ) . '</li>';
		}

		$message .= '</ul><p>' . __( 'If you are not able to upgrade, you can rollback to the previous version by using the button below.', 'rocket' ) . '</p><p><a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=rocket_rollback' ), 'rocket_rollback' ) . '" class="button">' .
		// Translators: %s = Previous plugin version.
		sprintf( __( 'Re-install version %s', 'rocket' ), $this->plugin_last_version )
		. '</a></p>';

		echo '<div class="notice notice-error">' . $message . '</div>';
	}

	/**
	 * Do the rollback
	 *
	 * @since 3.0
	 * @author Remy Perona
	 */
	public function rollback() {
		check_ajax_referer( 'rocket_rollback' );

		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			wp_die();
		}

		$consumer_key = isset( $this->options['consumer_key'] ) ? $this->options['consumer_key'] : false;

		if ( ! $consumer_key && defined( 'WP_ROCKET_KEY' ) ) {
			$consumer_key = WP_ROCKET_KEY;
		}

		$plugin_transient = get_site_transient( 'update_plugins' );
		$plugin_folder    = plugin_basename( dirname( $this->plugin_file ) );
		$plugin_file      = basename( $this->plugin_file );
		$url              = sprintf( 'https://wp-rocket.me/%s/wp-rocket_%s.zip', $consumer_key, $this->plugin_last_version );
		$temp_array       = array(
			'slug'        => $plugin_folder,
			'new_version' => $this->plugin_last_version,
			'url'         => 'https://wp-rocket.me',
			'package'     => $url,
		);

		$temp_object = (object) $temp_array;
		$plugin_transient->response[ $plugin_folder . '/' . $plugin_file ] = $temp_object;
		set_site_transient( 'update_plugins', $plugin_transient );

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		// translators: %s is the plugin name.
		$title         = sprintf( __( '%s Update Rollback', 'rocket' ), $this->plugin_name );
		$plugin        = 'wp-rocket/wp-rocket.php';
		$nonce         = 'upgrade-plugin_' . $plugin;
		$url           = 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $plugin );
		$upgrader_skin = new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) );
		$upgrader      = new Plugin_Upgrader( $upgrader_skin );
		remove_filter( 'site_transient_update_plugins', 'rocket_check_update', 1 );
		$upgrader->upgrade( $plugin );
		wp_die(
			// translators: %s is the plugin name.
			'', sprintf( __( '%s Update Rollback', 'rocket' ), $this->plugin_name ), array(
				'response' => 200,
			)
		);
	}

	/**
	 * Filters the User Agent when doing a request to WP Rocket server
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array  $request   Array of arguments associated with the request.
	 * @param string $url       URL requested.
	 */
	public function add_own_ua( $request, $url ) {
		if ( strpos( $url, 'wp-rocket.me' ) !== false ) {
			$consumer_key = '';

			if ( defined( 'WP_ROCKET_KEY' ) ) {
				$consumer_key = WP_ROCKET_KEY;
			}

			$consumer_email = '';

			if ( defined( 'WP_ROCKET_EMAIL' ) ) {
				$consumer_email = WP_ROCKET_EMAIL;
			}

			$request['user-agent'] = sprintf( '%s;WP-Rocket|%s%s|%s|%s|%s|;', $request['user-agent'], $this->plugin_version, '', $consumer_key, $consumer_email, esc_url( home_url() ) );

			return $request;
		}

		return $request;
	}
}
