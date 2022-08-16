<?php

namespace WP_Rocket\ThirdParty\Plugins;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\ThirdParty\ReturnTypesTrait;

class Jetpack implements Subscriber_Interface {

	use ReturnTypesTrait;

	/**
	 * Option instance.
	 *
	 * @var Options_Data
	 */
	protected $option;

	/**
	 * Instantiate class.
	 *
	 * @param Options_Data $option Option instance.
	 */
	public function __construct( Options_Data $option ) {
		$this->option = $option;
	}

	/**
	 * Subscribed events.
	 */
	public static function get_subscribed_events() {
		$events = [];

		if ( ! class_exists( 'Jetpack' ) ) {
			return $events;
		}

		if ( \Jetpack::is_module_active( 'sitemaps' ) ) {
			$events['rocket_sitemap_preload_list'] = 'add_jetpack_sitemap';
		}

		return $events;
	}

	/**
	 * Add Jetpack sitemap to preload list
	 *
	 * @param Array $sitemaps Array of sitemaps to preload.
	 * @return Array Updated Array of sitemaps to preload
	 */
	public function add_jetpack_sitemap( $sitemaps ) {
		if ( ! function_exists( 'jetpack_sitemap_uri' ) ) {
			return $sitemaps;
		}

		$sitemaps['jetpack'] = jetpack_sitemap_uri();

		return $sitemaps;
	}
}
