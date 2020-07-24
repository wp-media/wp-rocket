<?php

namespace WP_Rocket\ThirdParty\Hostings;

use WP_Rocket\Engine\Activation\ActivationInterface;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Engine\Deactivation\DeactivationInterface;
use WP_Rocket\Event_Management\Event_Manager;
use WP_Rocket\Event_Management\Event_Manager_Aware_Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

/**
 * Subscriber for compatibility with WordPress.com hosting.
 *
 * @since 3.6.3
 */
class WordPressCom implements ActivationInterface, DeactivationInterface, Event_Manager_Aware_Subscriber_Interface {
	use ReturnTypesTrait;

	/**
	 * Admin Cache Subsriber instance.
	 *
	 * @var AdminSubscriber
	 */
	protected $admin_cache_subscriber;

	/**
	 * Event Manager instance.
	 *
	 * @var Event_Manager
	 */
	protected $event_manager;

	/**
	 * WordPressCom constructor.
	 *
	 * @param AdminSubscriber $admin_cache_subscriber Cache Admin Subscriber instance.
	 */
	public function __construct( AdminSubscriber $admin_cache_subscriber ) {
		$this->admin_cache_subscriber = $admin_cache_subscriber;
	}

	/**
	 * Set the subscriber's event manager.
	 *
	 * @since 3.6.3
	 *
	 * @param Event_Manager $event_manager An Event Manager instance.
	 *
	 * @return void
	 */
	public function set_event_manager( Event_Manager $event_manager ) {
		$this->event_manager = $event_manager;
	}

	/**
	 * Array of events this subscriber listens to.
	 *
	 * @since 3.6.3
	 *
	 * @return array The array of subscribed events.
	 */
	public static function get_subscribed_events() {
		return [
			'do_rocket_generate_caching_files'    => 'return_false',
			'rocket_cache_mandatory_cookies'      => 'return_empty_array',
			'rocket_display_varnish_options_tab'  => 'return_false',
			'admin_notices'                       => 'remove_admin_subscriber_advanced_cache_permissions_notice',
			'rocket_generate_advanced_cache_file' => 'return_false',
			'after_rocket_clean_domain'           => 'purge_wpcom_cache',
		];
	}

	/**
	 * Actions to perform on plugin activation
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function activate() {
		add_action( 'rocket_activation', [ $this, 'no_advanced_cache' ] );
	}

	/**
	 * Actions to perform on plugin deactivation
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function deactivate() {
		add_action( 'rocket_deactivation', [ $this, 'no_advanced_cache' ] );
	}

	/**
	 * Prevent writing in advanced-cache.php when on self-caching host.
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function no_advanced_cache() {
		add_filter( 'rocket_generate_advanced_cache_file', [ $this, 'return_false' ] );
	}

	/**
	 * Callback to remove the AdminSubscriber's 'Advanced Cache Permissions' notice.
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function remove_admin_subscriber_advanced_cache_permissions_notice() {
		$this->event_manager->remove_callback(
			'admin_notices',
			[ $this->admin_cache_subscriber, 'notice_advanced_cache_permissions' ]
		);
	}

	/**
	 * Purge WordPress.com cache
	 *
	 * @since 3.6.3
	 *
	 * @return void
	 */
	public function purge_wpcom_cache() {
		wp_cache_flush();
	}
}
