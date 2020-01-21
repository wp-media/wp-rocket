<?php
namespace WP_Rocket\Subscriber\Admin\Settings;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Admin\Settings\Page;

defined( 'ABSPATH' ) || exit;

/**
 * WP Rocket settings page subscriber
 *
 * @since 3.3
 * @author Remy Perona
 */
class Page_Subscriber implements Subscriber_Interface {
	/**
	 * Page instance
	 *
	 * @var Page
	 */
	private $page;

	/**
	 * Constructor
	 *
	 * @param Page $page Page instance.
	 */
	public function __construct( Page $page ) {
			$this->page = $page;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.3
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'admin_menu'                           => 'add_admin_page',
			'admin_init'                           => 'configure',
			'wp_ajax_rocket_refresh_customer_data' => 'refresh_customer_data',
			'wp_ajax_rocket_toggle_option'         => 'toggle_option',
			'rocket_settings_menu_navigation'      => [
				[ 'add_menu_tools_page' ],
				[ 'add_imagify_page', 9 ],
				[ 'add_tutorials_page', 11 ],
			],
		];
	}

	/**
	 * Adds plugin page to the Settings menu
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function add_admin_page() {
		add_options_page(
			$this->page->get_title(),
			$this->page->get_title(),
			$this->page->get_capability(),
			$this->page->get_slug(),
			[ $this->page, 'render_page' ]
		);
	}

	/**
	 * Registers the settings, page sections, fields sections and fields.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function configure() {
		$this->page->configure();
	}

	/**
	 * Gets customer data to refresh it on the dashboard with AJAX
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function refresh_customer_data() {
		check_ajax_referer( 'rocket-ajax' );

		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			wp_die();
		}

		delete_transient( 'wp_rocket_customer_data' );

		return wp_send_json_success( $this->page->customer_data() );
	}

	/**
	 * Toggle sliding checkboxes option value
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function toggle_option() {
		$this->page->toggle_option();
	}

	/**
	 * Add Tools section to navigation
	 *
	 * @since 3.0
	 * @author Remy Perona
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
	 * Add Imagify section to navigation
	 *
	 * @since 3.2
	 * @author Remy Perona
	 *
	 * @param array $navigation Array of menu items.
	 * @return array
	 */
	public function add_imagify_page( $navigation ) {
		if ( \Imagify_Partner::has_imagify_api_key() ) {
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
	 * Add Tutorials section to navigation
	 *
	 * @since 3.4
	 * @author Remy Perona
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
}
