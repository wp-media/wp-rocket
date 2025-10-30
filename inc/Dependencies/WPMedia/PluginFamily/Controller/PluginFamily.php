<?php

namespace WP_Rocket\Dependencies\WPMedia\PluginFamily\Controller;

/**
 * Handles installation and Activation of plugin family members.
 */
class PluginFamily implements PluginFamilyInterface {

	/**
	 * Plugin family version.
	 *
	 * @var string
	 */
	private $version = '1.0.6';

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
		$events                                    = self::get_post_install_event();
		$events['admin_notices']                   = 'display_error_notice';
		$events['enqueue_block_editor_assets']     = 'enqueue_assets';
		$events['wp_ajax_install_imagify']         = 'install_imagify';
		$events['wp_ajax_dismiss_promote_imagify'] = 'dismiss_promote_imagify';
		$events['admin_enqueue_scripts']           = 'enqueue_admin_assets';
		$events['admin_footer']                    = 'insert_footer_templates';

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
	 * @param string $slug Plugin slug if found.
	 * @return void
	 */
	private function install( $slug = '' ) {
		if ( $this->is_installed( $slug ) ) {
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
		$result   = $upgrader->install( $this->get_download_url( $slug ) );

		if ( is_wp_error( $result ) ) {
			$this->set_error( $result );
		}

		clearstatcache();
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @param string $slug Plugin slug if found.
	 * @return boolean
	 */
	private function is_installed( $slug = '' ): bool {
		return file_exists( WP_PLUGIN_DIR . '/' . $this->get_plugin( $slug ) );
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
	 * @param string $slug Plugin slug if found.
	 * @return string
	 */
	private function get_plugin( $slug = '' ): string {
		if ( 'imagify' === $slug ) {
			return 'imagify/imagify.php';
		}
		if ( empty( $_GET['plugin_to_install'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return '';
		}
		return rawurldecode( sanitize_text_field( wp_unslash( $_GET['plugin_to_install'] ) ) ) . '.php'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	}

	/**
	 * Get plugin download url.
	 *
	 * @param string $slug Plugin slug if found.
	 * @return string
	 */
	private function get_download_url( $slug = '' ): string {

		$custom_download_url = $this->maybe_get_custom_download_url( $slug );

		if ( false !== $custom_download_url ) {
			return $custom_download_url;
		}

		$plugin_install = ABSPATH . 'wp-admin/includes/plugin-install.php';

		if ( ! defined( 'ABSPATH' ) || ! file_exists( $plugin_install ) ) {
			wp_die(
				'Plugin Installation failed. plugin-install.php not found',
				'',
				[ 'back_link' => true ]
			);
		}

		require_once $plugin_install; // @phpstan-ignore-line

		if ( empty( $slug ) ) {
			$slug = $this->get_slug();
		}

		$data = [
			'slug'   => $slug,
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

	/**
	 * Returns a custom download url for plugin if exists.
	 *
	 * @param string $plugin_slug plugin slug.
	 * @return string|bool
	 */
	private function maybe_get_custom_download_url( string $plugin_slug ) {
		$parent_plugin_slug = $this->get_parent_plugin_slug();

		$urls = [
			'seo-by-rank-math' => 'https://rankmath.com/downloads/plugin-family/' . $parent_plugin_slug,
		];

		if ( ! isset( $urls[ $plugin_slug ] ) ) {
			return false;
		}

		return $urls[ $plugin_slug ];
	}

	/**
	 * Get parent plugin slug.
	 *
	 * @return string
	 */
	private function get_parent_plugin_slug(): string {
		$plugin_path = plugin_basename( __FILE__ );
		$chunks      = explode( '/', $plugin_path );

		return $chunks[0];
	}

	/**
	 * Check if imagify is installed or not.
	 *
	 * @return bool
	 */
	private function is_imagify_installed(): bool {
		return file_exists( WP_PLUGIN_DIR . '/imagify/imagify.php' );
	}

	/**
	 * Check if imagify is activated or not.
	 *
	 * @return bool
	 */
	private function is_imagify_activated(): bool {
		return defined( 'IMAGIFY_VERSION' );
	}

	/**
	 * Enqueue block editor assets
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if (
			$this->is_promote_imagify_dismissed()
			||
			$this->is_imagify_activated()
			||
			wp_script_is( 'plugin-family-script' )
		) {
			return;
		}

		$script_url = plugin_dir_url( __DIR__ ) . 'assets/js/index.js';

		wp_enqueue_script(
			'plugin-family-script',
			$script_url,
			[ 'react-jsx-runtime', 'wp-block-editor', 'wp-components', 'wp-compose', 'wp-element', 'wp-hooks', 'wp-i18n', 'wp-primitives' ],
			$this->version,
			[
				'in_footer' => true,
			]
		);

		$this->add_install_imagify_localized_script( 'plugin-family-script' );
	}

	/**
	 * Install Imagify using the ajax request.
	 *
	 * @return void
	 */
	public function install_imagify() {
		check_ajax_referer( 'install-imagify-nonce' );

		if ( ! current_user_can( is_multisite() ? 'manage_network_plugins' : 'install_plugins' ) ) {
			wp_send_json_error( __( 'Not Allowed', 'rocket' ) );
		}

		if ( ! $this->is_imagify_installed() ) {
			$this->install( 'imagify' );
		}

		$activated = activate_plugin( $this->get_plugin( 'imagify' ), '', is_multisite() );
		if ( is_wp_error( $activated ) ) {
			wp_send_json_error( $activated->get_error_message() );
		}

		$this->set_imagify_partner( 'wp-rocket' );
		wp_send_json_success( __( 'Imagify installed! Click here to start using it.', 'rocket' ) );
	}

	/**
	 * Set the imagify plugin partner.
	 *
	 * @param string $plugin Current plugin.
	 * @return void
	 */
	private function set_imagify_partner( $plugin ) {
		update_option( 'imagifyp_id', $plugin, false );
	}

	/**
	 * Check if we can enqueue admin assets or not.
	 *
	 * @param string $page Current page ID if found.
	 * @return bool
	 */
	private function can_enqueue_admin_assets( $page = '' ): bool {
		if ( $this->is_promote_imagify_dismissed() ) {
			return false;
		}
		if ( empty( $page ) ) {
			return in_array( get_current_screen()->id, [ 'post', 'upload' ], true );
		}
		return in_array( $page, [ 'post.php', 'post-new.php', 'upload.php' ], true );
	}

	/**
	 * Add localized script to be used by scripts.
	 *
	 * @param string $script_id Script ID.
	 * @return void
	 */
	private function add_install_imagify_localized_script( $script_id ) {
		$data = [
			'ajax_url'         => admin_url( 'admin-ajax.php' ),
			'nonce'            => wp_create_nonce( 'install-imagify-nonce' ),
			'plugins_page_url' => admin_url( 'plugins.php' ),
		];
		wp_add_inline_script(
			$script_id,
			'window.wpmedia_pluginfamily = ' . wp_json_encode( $data ) . ';',
			'before'
		);
	}

	/**
	 * Enqueue Admin assets.
	 *
	 * @param string $page Page ID.
	 * @return void
	 */
	public function enqueue_admin_assets( $page ) {
		if ( ! $this->can_enqueue_admin_assets( $page ) ) {
			return;
		}

		if ( $this->is_imagify_activated() || wp_script_is( 'plugin-family-admin-script' ) ) {
			return;
		}

		$script_url = plugin_dir_url( __DIR__ ) . 'assets/js/admin.js';
		wp_enqueue_script(
			'plugin-family-admin-script',
			$script_url,
			[ 'jquery' ], // jQuery as a dependency.
			$this->version,
			[
				'in_footer' => true,
			]
		);

		$this->add_install_imagify_localized_script( 'plugin-family-admin-script' );

		$style_url = plugin_dir_url( __DIR__ ) . 'assets/css/style.css';
		wp_enqueue_style(
			'plugin-family-admin-style',
			$style_url,
			[],
			$this->version
		);
	}

	/**
	 * Insert admin footer JS templates.
	 *
	 * @return void
	 */
	public function insert_footer_templates() {
		if ( ! $this->can_enqueue_admin_assets() ) {
			return;
		}
		include_once __DIR__ . '/../View/promote-imagify-uploader.php';
	}

	/**
	 * Dismiss promote Imagify using the ajax request.
	 *
	 * @return void
	 */
	public function dismiss_promote_imagify() {
		check_ajax_referer( 'install-imagify-nonce' );

		if ( ! current_user_can( is_multisite() ? 'manage_network_plugins' : 'install_plugins' ) ) {
			wp_send_json_error( __( 'Not Allowed', 'rocket' ) );
		}

		update_option( 'plugin_family_dismiss_promote_imagify', true );
		wp_send_json_success( __( 'Dismissed.', 'rocket' ) );
	}

	/**
	 * Check if promote imagify message is dismissed.
	 *
	 * @return bool
	 */
	private function is_promote_imagify_dismissed() {
		return ! empty( get_option( 'plugin_family_dismiss_promote_imagify' ) );
	}
}
