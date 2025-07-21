<?php
namespace WP_Rocket\Engine\Admin\Settings;

use Imagify_Partner;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Dependencies\WPMedia\PluginFamily\Controller\{ PluginFamily, PluginFamilyInterface };

/**
 * WP Rocket settings page subscriber.
 *
 * @since 3.5.5 Moves into the new architecture.
 * @since 3.3
 */
class Subscriber implements Subscriber_Interface, PluginFamilyInterface {
	/**
	 * Page instance
	 *
	 * @var Page
	 */
	private $page;

	/**
	 * PluginFamily instance
	 *
	 * @var PluginFamily
	 *
	 * @since 3.17.2
	 */
	protected $plugin_family;

	/**
	 * Creates an instance of the object.
	 *
	 * @param Page         $page Page instance.
	 * @param PluginFamily $plugin_family Plugin Family Instance.
	 */
	public function __construct( Page $page, PluginFamily $plugin_family ) {
		$this->page          = $page;
		$this->plugin_family = $plugin_family;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		$events = [
			'admin_menu'                           => 'add_admin_page',
			'admin_init'                           => 'configure',
			'wp_ajax_rocket_refresh_customer_data' => 'refresh_customer_data',
			'wp_ajax_rocket_toggle_option'         => 'toggle_option',
			'rocket_settings_menu_navigation'      => [
				[ 'add_menu_tools_page' ],
				[ 'add_imagify_page', 9 ],
				[ 'add_tutorials_page', 11 ],
				[ 'add_plugins_page', 12 ],
			],
			'admin_enqueue_scripts'                => [
				[ 'enqueue_rocket_scripts' ],
				[ 'enqueue_url' ],
			],
			'script_loader_tag'                    => [ 'async_wistia_script', 10, 2 ],
			'rocket_after_settings_radio_options'  => [ 'display_radio_options_sub_fields', 11 ],
			'rocket_settings_tools_content'        => 'display_mobile_cache_option',
			'wp_ajax_rocket_enable_mobile_cache'   => 'enable_mobile_cache',
			'wp_rocket_upgrade'                    => [ 'enable_separate_cache_files_mobile', 9, 2 ],
			'admin_notices'                        => 'display_update_notice',
		];

		foreach ( PluginFamily::get_subscribed_events() as $hook => $callback ) {
			if ( isset( $events[ $hook ] ) ) {
				// Make sure it's an array of callbacks.
				if ( ! is_array( $events[ $hook ][0] ) ) {
					$events[ $hook ] = [ $events[ $hook ] ];
				}

				// Wrap single callback in array if needed.
				$events[ $hook ][] = is_array( $callback ) ? $callback : [ $callback ];
			} else {
				$events[ $hook ] = is_array( $callback ) ? $callback : [ [ $callback ] ];
			}
		}

		return $events;
	}

	/**
	 * Enqueue the URL for option exporting.
	 *
	 * @return void
	 */
	public function enqueue_url() {
		wp_localize_script(
			'wpr-admin-common',
			'rocket_option_export',
			[
				'rest_url_option_export' => rest_url( 'wp-rocket/v1/options/export/' ),
			]
		);
	}

	/**
	 * Enqueues WP Rocket scripts on the settings page
	 *
	 * @since 3.6
	 *
	 * @param string $hook The current admin page.
	 *
	 * @return void
	 */
	public function enqueue_rocket_scripts( $hook ) {
		$this->page->enqueue_rocket_scripts( $hook );
	}

	/**
	 * Adds the async attribute to the Wistia script
	 *
	 * @param string $tag    The <script> tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 *
	 * @return string
	 */
	public function async_wistia_script( $tag, $handle ) {
		return $this->page->async_wistia_script( $tag, $handle );
	}

	/**
	 * Adds plugin page to the Settings menu.
	 *
	 * @since 3.0
	 */
	public function add_admin_page() {
		add_options_page(
			$this->page->get_title(),
			/**
			 * Filters the menu title to display in the Settings sub-menu
			 *
			 * @since 3.7.4
			 *
			 * @param string $menu_title The text to be used for the menu.
			 */
			apply_filters( 'rocket_menu_title', $this->page->get_title() ),
			$this->page->get_capability(),
			$this->page->get_slug(),
			[ $this->page, 'render_page' ]
		);
	}

	/**
	 * Registers the settings, page sections, fields sections and fields.
	 *
	 * @since 3.0
	 */
	public function configure() {
		$this->page->configure();
	}

	/**
	 * Gets customer data to refresh it on the dashboard with AJAX.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function refresh_customer_data() {
		check_ajax_referer( 'rocket-ajax' );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_die();
		}

		delete_transient( 'wp_rocket_customer_data' );
		delete_transient( 'wpr_user_information_timeout_active' );
		delete_transient( 'wpr_user_information_timeout' );

		return wp_send_json_success( $this->page->customer_data() );
	}

	/**
	 * Toggle sliding checkboxes option value.
	 *
	 * @since 3.0
	 */
	public function toggle_option() {
		$this->page->toggle_option();
	}

	/**
	 * Add Tools section to navigation.
	 *
	 * @since 3.0
	 *
	 * @param array $navigation Array of menu items.
	 * @return array
	 */
	public function add_menu_tools_page( $navigation ) {
		$navigation['tools'] = [
			'id'               => 'tools',
			'title'            => __( 'Tools', 'rocket' ),
			'menu_description' => __( 'Import, Export, Rollback', 'rocket' ),
		];

		return $navigation;
	}

	/**
	 * Add Imagify section to navigation.
	 *
	 * @since 3.2
	 *
	 * @param array $navigation Array of menu items.
	 * @return array
	 */
	public function add_imagify_page( $navigation ) {
		if (
			rocket_get_constant( 'WP_ROCKET_WHITE_LABEL_ACCOUNT' )
			||
			Imagify_Partner::has_imagify_api_key()
		) {
			return $navigation;
		}

		$navigation['imagify'] = [
			'id'               => 'imagify',
			'title'            => __( 'Image Optimization', 'rocket' ),
			'menu_description' => __( 'Compress your images', 'rocket' ),
		];

		return $navigation;
	}

	/**
	 * Add Tutorials section to navigation.
	 *
	 * @since 3.4
	 *
	 * @param array $navigation Array of menu items.
	 * @return array
	 */
	public function add_tutorials_page( $navigation ) {
		$navigation['tutorials'] = [
			'id'               => 'tutorials',
			'title'            => __( 'Tutorials', 'rocket' ),
			'menu_description' => __( 'Getting started and how to videos', 'rocket' ),
		];

		return $navigation;
	}

	/**
	 * Displays the radio option sub fields
	 *
	 * @since 3.10
	 *
	 * @param array $option_data array of option_id and sub_fields of the option.
	 *
	 * @return void
	 */
	public function display_radio_options_sub_fields( $option_data ) {
		if ( empty( $option_data['sub_fields'] ) ) {
			return;
		}
		$this->page->display_radio_options_sub_fields( $option_data['sub_fields'] );
	}

	/**
	 * Render mobile cache option.
	 *
	 * @return void
	 */
	public function display_mobile_cache_option(): void {
		$this->page->display_mobile_cache_option();
	}

	/**
	 * Callback method for the AJAX request to mobile cache.
	 *
	 * @return void
	 */
	public function enable_mobile_cache(): void {
		$this->page->enable_mobile_cache();
	}

	/**
	 * Enable Separate cache files for mobile devices on upgrade.
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previous plugin version.
	 * @return void
	 */
	public function enable_separate_cache_files_mobile( $new_version, $old_version ): void {
		if ( version_compare( $old_version, '3.16', '>' ) ) {
			return;
		}

		$this->page->enable_separate_cache_files_mobile();
	}

	/**
	 * Display the update notice.
	 *
	 * @return void
	 */
	public function display_update_notice() {
		$this->page->display_update_notice();
	}

	/**
	 * Add Plugins section to navigation.
	 *
	 * @since 3.17.2
	 *
	 * @param array $navigation Array of menu items.
	 * @return array
	 */
	public function add_plugins_page( $navigation ) {
		$navigation['plugins'] = [
			'id'               => 'plugins',
			'title'            => __( 'Our Plugins', 'rocket' ),
			'menu_description' => __( 'Build Better, Faster, Safer', 'rocket' ),
		];

		return $navigation;
	}

	/**
	 * Install and activate plugin method for plugin family
	 *
	 * @return void
	 */
	public function install_activate(): void {
		$this->plugin_family->install_activate();
	}

	/**
	 * Display error related to plugin family
	 *
	 * @return void
	 */
	public function display_error_notice(): void {
		$this->plugin_family->display_error_notice();
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		$this->plugin_family->enqueue_assets();
	}

	/**
	 * Install Imagify using the ajax request.
	 *
	 * @return void
	 */
	public function install_imagify(): void {
		$this->plugin_family->install_imagify();
	}

	/**
	 * Enqueue Admin assets.
	 *
	 * @param string $page Page ID.
	 * @return void
	 */
	public function enqueue_admin_assets( $page ): void {
		$this->plugin_family->enqueue_admin_assets( $page );
	}

	/**
	 * Insert admin footer JS templates.
	 *
	 * @return void
	 */
	public function insert_footer_templates(): void {
		$this->plugin_family->insert_footer_templates();
	}

	/**
	 * Dismiss promote Imagify using the ajax request.
	 *
	 * @return void
	 */
	public function dismiss_promote_imagify(): void {
		$this->plugin_family->dismiss_promote_imagify();
	}
}
