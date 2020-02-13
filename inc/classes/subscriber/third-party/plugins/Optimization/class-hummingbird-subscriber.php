<?php
namespace WP_Rocket\Subscriber\Third_Party\Plugins\Optimization;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Options_Data;

/**
 * Hummingbird compatibility class
 */
class Hummingbird_Subscriber implements Subscriber_Interface {
	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Array containing the errors
	 *
	 * @var array
	 */
	private $errors = [];

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3.3
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_notices' => 'warning_notice',
		];
	}

	/**
	 * Display a notice if conflicting options are active
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function warning_notice() {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return;
		}

		if ( ! is_plugin_active( 'hummingbird-performance/wp-hummingbird.php' ) && ! is_plugin_active( 'wp-hummingbird/wp-hummingbird.php' ) ) {
			return;
		}

		$this->check_cache();
		$this->check_assets();
		$this->check_browser_caching();
		$this->check_gzip();
		$this->check_emoji();
		$this->check_remove_query_strings();

		if ( 0 === count( $this->errors ) ) {
			return;
		}

		// Translators: %s = Plugin name.
		$message = '<p>' . sprintf( _nx( 'Please deactivate the following %s option which conflicts with WP Rocket features:', 'Please deactivate the following %s options which conflict with WP Rocket features:', count( $this->errors ), 'Hummingbird notice', 'rocket' ), 'Hummingbird' ) . '</p>';

		$message .= '<ul>';

		foreach ( $this->errors as $error ) {
			$message .= '<li>' . $error . '</li>';
		}

		$message .= '</ul>';

		\rocket_notice_html(
			[
				'status'  => 'error',
				'message' => $message,
			]
		);
	}

	/**
	 * Checks if the Hummingbird Utils class exists
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	private function is_utils_available() {
		if ( ! class_exists( 'WP_Hummingbird_Utils' ) ) {
			return false;
		}

		if ( ! \method_exists( 'WP_Hummingbird_Utils', 'get_module' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the Hummingbird settings class exists
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	private function is_settings_available() {
		if ( ! class_exists( 'WP_Hummingbird_Settings' ) ) {
			return false;
		}

		if ( ! \method_exists( 'WP_Hummingbird_Settings', 'get_setting' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if Hummingbird and WP Rocket disable emoji options are active at the same time
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function check_emoji() {
		if ( ! $this->is_settings_available() ) {
			return false;
		}

		if ( $this->options->get( 'emoji' ) && \WP_Hummingbird_Settings::get_setting( 'emoji', 'advanced' ) ) {
			// Translators: %1$s = Plugin name, %2$s = <em>, %3$s = </em>.
			$this->errors[] = sprintf( _x( '%1$s %2$sdisable emoji%3$s conflicts with WP Rockets %2$sdisable emoji%3$s', 'Hummingbird notice', 'rocket' ), 'Hummingbird', '<em>', '</em>' );
			return true;
		}

		return false;
	}

	/**
	 * Checks if Hummingbird and WP Rocket remove query strings options are active at the same time
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function check_remove_query_strings() {
		if ( ! $this->is_settings_available() ) {
			return false;
		}

		if ( $this->options->get( 'remove_query_strings' ) && \WP_Hummingbird_Settings::get_setting( 'query_string', 'advanced' ) ) {
			// Translators: %1$s = Plugin name, %2$s = <em>, %3$s = </em>.
			$this->errors[] = sprintf( _x( '%1$s %2$sremove query strings%3$s conflicts with WP Rocket %2$sremove query strings%3$s', 'Hummingbird notice', 'rocket' ), 'Hummingbird', '<em>', '</em>' );
			return true;
		}

		return false;
	}

	/**
	 * Checks if Hummingbird Gzip rules are in the htaccess file.
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function check_gzip() {
		if ( ! $this->is_utils_available() ) {
			return false;
		}

		$gzip = \WP_Hummingbird_Utils::get_module( 'gzip' );

		if ( ! $gzip instanceof \WP_Hummingbird_Module_GZip ) {
			return false;
		}

		if ( ! method_exists( $gzip, 'is_htaccess_written' ) ) {
			return false;
		}

		if ( ! method_exists( $gzip, 'get_server_type' ) ) {
			return false;
		}

		if ( $gzip::is_htaccess_written( 'gzip' ) && 'apache' === $gzip::get_server_type() ) {
			// Translators: %1$s = Plugin name, %2$s = <em>, %3$s = </em>.
			$this->errors[] = sprintf( _x( '%1$s %2$sGZIP compression%3$s conflicts with WP Rocket %2$sGZIP compression%3$s', 'Hummingbird notice', 'rocket' ), 'Hummingbird', '<em>', '</em>' );
			return true;
		}

		return false;
	}

	/**
	 * Checks if Hummingbird browser caching rules are in the htaccess file
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function check_browser_caching() {
		if ( ! $this->is_utils_available() ) {
			return false;
		}

		$caching = \WP_Hummingbird_Utils::get_module( 'caching' );

		if ( ! $caching instanceof \WP_Hummingbird_Module_Caching ) {
			return false;
		}

		if ( ! method_exists( $caching, 'is_htaccess_written' ) ) {
			return false;
		}

		if ( ! method_exists( $caching, 'get_server_type' ) ) {
			return false;
		}

		if ( $caching::is_htaccess_written( 'caching' ) && 'apache' === $caching::get_server_type() ) {
			// Translators: %1$s = Plugin name, %2$s = <em>, %3$s = </em>.
			$this->errors[] = sprintf( _x( '%1$s %2$sbrowser caching%3$s conflicts with WP Rocket %2$sbrowser caching%3$s', 'Hummingbird notice', 'rocket' ), 'Hummingbird', '<em>', '</em>' );
			return true;
		}

		return false;
	}

	/**
	 * Checks if Hummingbird Cache is active
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function check_cache() {
		if ( ! $this->is_utils_available() ) {
			return false;
		}

		$cache = \WP_Hummingbird_Utils::get_module( 'page_cache' );

		if ( ! $cache instanceof \WP_Hummingbird_Module_Page_Cache ) {
			return false;
		}

		if ( ! method_exists( $cache, 'is_active' ) ) {
			return false;
		}

		if ( $cache->is_active() ) {
			// Translators: %1$s = Plugin name, %2$s = <em>, %3$s = </em>.
			$this->errors[] = sprintf( _x( '%1$s %2$spage caching%3$s conflicts with WP Rocket %2$spage caching%3$s', 'Hummingbird notice', 'rocket' ), 'Hummingbird', '<em>', '</em>' );
			return true;
		}

		return false;
	}

	/**
	 * Checks if Hummingbird Assets optimization is active
	 *
	 * Checks against WP Rocket Minify CSS, Minify JS and Defer JS options.
	 *
	 * @since 3.3.3
	 * @author Remy Perona
	 *
	 * @return bool
	 */
	private function check_assets() {
		if ( ! $this->is_utils_available() ) {
			return false;
		}

		$minify = \WP_Hummingbird_Utils::get_module( 'minify' );

		if ( ! $minify instanceof \WP_Hummingbird_Module_Minify ) {
			return false;
		}

		if ( ! method_exists( $minify, 'is_active' ) ) {
			return false;
		}

		if ( $minify->is_active() && ( $this->options->get( 'minify_css' ) || $this->options->get( 'minify_js' ) || $this->options->get( 'defer_all_js' ) ) ) {
			// Translators: %1$s = Plugin name, %2$s = <em>, %3$s = </em>.
			$this->errors[] = sprintf( _x( '%1$s %2$sasset optimization%3$s conflicts with WP Rocket %2$sfile optimization%3$s', 'Hummingbird notice', 'rocket' ), 'Hummingbird', '<em>', '</em>' );
			return true;
		}

		return false;
	}
}
