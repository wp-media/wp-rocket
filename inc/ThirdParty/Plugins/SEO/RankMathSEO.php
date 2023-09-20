<?php

namespace WP_Rocket\ThirdParty\Plugins\SEO;

use RankMath\Helper;
use RankMath\Sitemap\Router;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class RankMathSEO implements Subscriber_Interface {


	/**
	 * Options instance.
	 *
	 * @var Options_Data
	 */
	protected $option;

	/**
	 * Instantiate class.
	 *
	 * @param Options_Data $option Options instance.
	 */
	public function __construct( Options_Data $option ) {
		$this->option = $option;
	}

	/**
	 * Subscribed events.
	 */
	public static function get_subscribed_events() {
		if ( ! defined( 'RANK_MATH_FILE' ) || ! Helper::is_module_active( 'sitemap' ) ) {
			return [];
		}

		return [
			'rocket_sitemap_preload_list' => [ 'rocket_sitemap', 15 ],
		];
	}

	/**
	 * Add SEO sitemap URL to the sitemaps to preload
	 *
	 * @param array $sitemaps Sitemaps to preload.
	 * @return array Updated Sitemaps to preload
	 */
	public function rocket_sitemap( $sitemaps ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		$sitemaps[] = Router::get_base_url( 'sitemap_index.xml' );

		return $sitemaps;
	}
}
