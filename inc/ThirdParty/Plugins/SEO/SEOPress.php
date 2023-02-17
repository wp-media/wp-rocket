<?php

namespace WP_Rocket\ThirdParty\Plugins\SEO;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Subscriber_Interface;

class SEOPress implements Subscriber_Interface {

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
		if ( ! function_exists( 'seopress_get_toggle_xml_sitemap_option' ) || 1 !== (int) seopress_get_toggle_xml_sitemap_option() ) {
			return [];
		}
		return [
			'rocket_sitemap_preload_list' => [ 'add_seopress_sitemap', 15 ],
		];
	}

	/**
	 * Add SEOPress sitemap URL to the sitemaps to preload
	 *
	 * @param array $sitemaps Sitemaps to preload.
	 * @return array Updated Sitemaps to preload
	 */
	public function add_seopress_sitemap( $sitemaps ) {
		$sitemaps[] = get_home_url() . '/sitemaps.xml';

		return $sitemaps;
	}
}
