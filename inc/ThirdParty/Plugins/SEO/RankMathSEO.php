<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Plugins\SEO;

use RankMath\Helper;
use RankMath\Sitemap\Router;
use WP_Rocket\Event_Management\Subscriber_Interface;

class RankMathSEO implements Subscriber_Interface {
	/**
	 * Returns an array of events this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		if ( ! class_exists( 'RankMath\Helper' ) ) {
			return [];
		}

		if ( ! defined( 'RANK_MATH_FILE' ) || ! Helper::is_module_active( 'sitemap' ) ) {
			return [];
		}

		return [
			'rocket_sitemap_preload_list' => [ 'add_sitemap', 15 ],
		];
	}

	/**
	 * Add SEO sitemap URL to the sitemaps to preload
	 *
	 * @param array $sitemaps Sitemaps to preload.
	 *
	 * @return array
	 */
	public function add_sitemap( $sitemaps ) {
		if ( ! class_exists( 'RankMath\Sitemap\Router' ) ) {
			return $sitemaps;
		}

		$sitemaps[] = Router::get_base_url( 'sitemap_index.xml' );

		return $sitemaps;
	}
}
