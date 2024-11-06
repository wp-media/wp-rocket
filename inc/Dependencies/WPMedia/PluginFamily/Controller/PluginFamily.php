<?php

namespace WP_Rocket\Dependencies\WPMedia\PluginFamily\Controller;

/**
 * Handles installation and Activation of plugin family members.
 */
class PluginFamily implements PluginFamilyInterface {

	/**
	 * Error transient.
	 *
	 * @var string
	 */
	protected $error_transient = 'plugin_family_error';

	/**
	 * Returns an array of events this subscriber listens to
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		$events                  = self::get_post_install_event();
		$events['admin_notices'] = 'display_error_notice';

		return $events;
	}

	/**
	 * Set post install event.
	 *
	 * @return array
	 */
	public static function get_post_install_event(): array {
		$allowed_plugin = [
			'uk-cookie-consent',
			'backwpup',
			'imagify',
			'seo-by-rank-math',
			'wp-rocket',
		];

		if ( ! isset( $_GET['action'], $_GET['_wpnonce'], $_GET['plugin_to_install'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return [];
		}

		$plugin = str_replace( 'plugin_family_install_', '', sanitize_text_field( wp_unslash( $_GET['action'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! in_array( $plugin, $allowed_plugin, true ) ) {
			return [];
		}

		return [
			'admin_post_plugin_family_install_' . $plugin => 'install_activate',
		];
	}

	/**
	 * Process to install and activate plugin.
	 *
	 * @return void
	 */
	public function install_activate() {
		if ( ! $this->is_allowed() ) {
			wp_die(
				'Plugin Installation is not allowed.',
				'',
				[ 'back_link' => true ]
			);
		}

		// Install plugin.
		$this->install();

		// Activate plugin.
		$result = activate_plugin( $this->get_plugin(), '', is_multisite() );

		if ( is_wp_error( $result ) ) {
			$this->set_error( $result );
		}

		wp_safe_redirect( wp_get_referer() );
		exit;
	}

	/**
	 * Install plugin.
	 *
	 * @return void
	 */
	private function install() {
		if ( $this->is_installed() ) {
			return;
		}

		$upgrader_class = ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		if ( ! defined( 'ABSPATH' ) || ! file_exists( $upgrader_class ) ) {
			wp_die(
				'Plugin Installation failed. class-wp-upgrader.php not found',
				'',
				[ 'back_link' => true ]
			);
		}

		require_once $upgrader_class; // @phpstan-ignore-line

		$upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
		$result   = $upgrader->install( $this->get_download_url() );

		if ( is_wp_error( $result ) ) {
			$this->set_error( $result );
		}

		clearstatcache();
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @return boolean
	 */
	private function is_installed(): bool {
		return file_exists( WP_PLUGIN_DIR . '/' . $this->get_plugin() );
	}

	/**
	 * Check if installation is allowed.
	 *
	 * @return boolean
	 */
	private function is_allowed(): bool {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'plugin_family_install_' . $this->get_slug() ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
			return false;
		}

		if ( ! current_user_can( is_multisite() ? 'manage_network_plugins' : 'install_plugins' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get plugin slug.
	 *
	 * @return string
	 */
	private function get_slug(): string {
		return dirname( rawurldecode( sanitize_text_field( wp_unslash( $_GET['plugin_to_install'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	}

	/**
	 * Get plugin identifier.
	 *
	 * @return string
	 */
	private function get_plugin(): string {
		return rawurldecode( sanitize_text_field( wp_unslash( $_GET['plugin_to_install'] ) ) ) . '.php'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	}

	/**
	 * Get plugin download url.
	 *
	 * @return string
	 */
	private function get_download_url(): string {
		$plugin_install = ABSPATH . 'wp-admin/includes/plugin-install.php';

		if ( ! defined( 'ABSPATH' ) || ! file_exists( $plugin_install ) ) {
			wp_die(
				'Plugin Installation failed. plugin-install.php not found',
				'',
				[ 'back_link' => true ]
			);
		}

		require_once $plugin_install; // @phpstan-ignore-line

		$data = [
			'slug'   => $this->get_slug(),
			'fields' => [
				'download_link'     => true,
				'short_description' => false,
				'sections'          => false,
				'rating'            => false,
				'ratings'           => false,
				'downloaded'        => false,
				'last_updated'      => false,
				'added'             => false,
				'tags'              => false,
				'homepage'          => false,
				'donate_link'       => false,
			],
		];

		// Get Plugin Infos.
		$plugin_info = plugins_api( 'plugin_information', $data );

		if ( is_wp_error( $plugin_info ) ) {
			$this->set_error( $plugin_info );
		}

		// Ensure that $plugin_info is an object before accessing the property.
		if ( ! is_object( $plugin_info ) || ! isset( $plugin_info->download_link ) ) {
			return '';
		}

		return $plugin_info->download_link;
	}

	/**
	 * Maybe display error notice.
	 *
	 * @return void
	 */
	public function display_error_notice() {
		$errors = get_transient( $this->error_transient );

		if ( ! $errors ) {
			return;
		}

		if ( ! is_wp_error( $errors ) ) {
			delete_transient( $this->error_transient );
			return;
		}

		$errors = $errors->get_error_messages();

		if ( ! $errors ) {
			$errors[] = 'Installation process failed';
		}

		$notice = '<div class="error notice is-dismissible"><p>' . implode( '<br/>', $errors ) . '</p></div>';
		echo wp_kses_post( $notice );

		// Remove transient after displaying notice.
		delete_transient( $this->error_transient );
	}

	/**
	 * Store an error message in a transient then redirect.
	 *
	 * @param object $error A WP_Error object.
	 * @return void
	 */
	private function set_error( $error ) {
		set_transient( $this->error_transient, $error, 30 );

		wp_safe_redirect( wp_get_referer() );
		exit;
	}
}
