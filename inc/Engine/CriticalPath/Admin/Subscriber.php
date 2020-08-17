<?php

namespace WP_Rocket\Engine\CriticalPath\Admin;

use WP_Rocket\Event_Management\Subscriber_Interface;

class Subscriber implements Subscriber_Interface {
	/**
	 * Instance of the CPCSS Settings handler.
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Instance of the Post handler
	 *
	 * @var Post
	 */
	private $post;

	/**
	 * Instance of the Admin handler
	 *
	 * @var Admin
	 */
	private $admin;

	/**
	 * Creates an instance of the subscriber.
	 *
	 * @param Post     $post      Post instance.
	 * @param Settings $settings  CPCSS Settings instance.
	 * @param Admin    $admin     Admin instance.
	 */
	public function __construct( Post $post, Settings $settings, Admin $admin ) {
		$this->post     = $post;
		$this->settings = $settings;
		$this->admin    = $admin;
	}

	/**
	 * Events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_after_options_metabox'       => 'cpcss_section',
			'rocket_metabox_cpcss_content'       => 'cpcss_actions',
			'rocket_first_install_options'       => 'add_async_css_mobile_option',
			'wp_rocket_upgrade'                  => [ 'set_async_css_mobile_default_value', 12, 2 ],
			'rocket_hidden_settings_fields'      => 'add_hidden_async_css_mobile',
			'rocket_settings_tools_content'      => 'display_cpcss_mobile_section',
			'wp_ajax_rocket_enable_mobile_cpcss' => 'enable_mobile_cpcss',
			'wp_ajax_rocket_cpcss_heartbeat'     => 'cpcss_heartbeat',
			'admin_enqueue_scripts'              => [
				[ 'enqueue_admin_edit_script' ],
				[ 'enqueue_admin_cpcss_heartbeat_script' ],
			],
			'rocket_admin_bar_items'             => 'add_regenerate_menu_item',
		];
	}

	/**
	 * Enable CPCSS mobile.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function enable_mobile_cpcss() {
		$this->settings->enable_mobile_cpcss();
	}

	/**
	 * Display CPCSS mobile section tool admin view.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function display_cpcss_mobile_section() {
		$this->settings->display_cpcss_mobile_section();
	}

	/**
	 * Enqueue CPCSS generation / deletion script on edit.php page.
	 *
	 * @since 3.6
	 *
	 * @param string $page The current admin page.
	 *
	 * @return void
	 */
	public function enqueue_admin_edit_script( $page ) {
		$this->post->enqueue_admin_edit_script( $page );
	}

	/**
	 * Displays the critical CSS block in WP Rocket options metabox.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function cpcss_section() {
		$this->post->cpcss_section();
	}

	/**
	 * Displays the content inside the critical CSS block.
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public function cpcss_actions() {
		$this->post->cpcss_actions();
	}

	/**
	 * Adds async_css_mobile option to WP Rocket options.
	 *
	 * @since 3.6
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_async_css_mobile_option( $options ) {
		return $this->settings->add_async_css_mobile_option( $options );
	}

	/**
	 * Sets the default value of async_css_mobile to 0 when upgrading from < 3.6.
	 *
	 * @since 3.6
	 *
	 * @param string $new_version New WP Rocket version.
	 * @param string $old_version Previous WP Rocket version.
	 */
	public function set_async_css_mobile_default_value( $new_version, $old_version ) {
		$this->settings->set_async_css_mobile_default_value( $new_version, $old_version );
	}

	/**
	 * Adds async_css_mobile to the hidden settings fields.
	 *
	 * @since 3.6
	 *
	 * @param array $hidden_settings_fields An array of hidden settings fields ID.
	 *
	 * @return array
	 */
	public function add_hidden_async_css_mobile( $hidden_settings_fields ) {
		return $this->settings->add_hidden_async_css_mobile( $hidden_settings_fields );
	}

	/**
	 * Check the CPCSS heartbeat.
	 *
	 * @since 3.6
	 */
	public function cpcss_heartbeat() {
		$this->admin->cpcss_heartbeat();
	}

	/**
	 * Enqueue CPCSS heartbeat script on all admin pages.
	 *
	 * @since 3.6
	 */
	public function enqueue_admin_cpcss_heartbeat_script() {
		$this->admin->enqueue_admin_cpcss_heartbeat_script();
	}

	/**
	 * Add Regenerate Critical CSS link to WP Rocket admin bar item
	 *
	 * @since 3.6
	 *
	 * @param WP_Admin_Bar $wp_admin_bar WP_Admin_Bar instance, passed by reference.
	 * @return void
	 */
	public function add_regenerate_menu_item( $wp_admin_bar ) {
		$this->admin->add_regenerate_menu_item( $wp_admin_bar );
	}
}
