<?php
namespace WP_Rocket\Subscriber\CDN;

use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\CDN\CDN;
use WP_Rocket\Admin\Options_Data;

/**
 * Subscriber for the CDN feature
 *
 * @since 3.4
 * @author Remy Perona
 */
class CDNSubscriber implements Subscriber_Interface {
	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * CDN instance
	 *
	 * @var CDN
	 */
	private $cdn;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket Options instance.
	 * @param CDN          $cdn CDN instance.
	 */
	public function __construct( Options_Data $options, CDN $cdn ) {
		$this->options = $options;
		$this->cdn     = $cdn;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.4
	 * @author Remy Perona
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer'           => [
				[ 'rewrite', 20 ],
				[ 'rewrite_srcset', 21 ],
			],
			'rocket_css_content'      => 'rewrite_css_properties',
			'rocket_cdn_hosts'        => [ 'get_cdn_hosts', 10, 2 ],
			'rocket_dns_prefetch'     => 'add_dns_prefetch_cdn',
			'rocket_facebook_sdk_url' => 'add_cdn_url',
			'rocket_css_url'          => [ 'add_cdn_url', 10, 2 ],
			'rocket_js_url'           => [ 'add_cdn_url', 10, 2 ],
		];
	}

	/**
	 * Rewrites URLs to the CDN URLs if allowed
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function rewrite( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		return $this->cdn->rewrite( $html );
	}

	/**
	 * Rewrites URLs in srcset attributes to the CDN URLs if allowed
	 *
	 * @since 3.4.0.4
	 * @author Remy Perona
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function rewrite_srcset( $html ) {
		if ( ! $this->is_allowed() ) {
			return $html;
		}

		return $this->cdn->rewrite_srcset( $html );
	}

	/**
	 * Rewrites URLs to the CDN URLs in CSS files
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param string $content CSS content.
	 * @return string
	 */
	public function rewrite_css_properties( $content ) {
		/**
		 * Filters the application of the CDN on CSS properties
		 *
		 * @since 2.6
		 *
		 * @param bool true to apply CDN to properties, false otherwise
		 */
		$do_rewrite = apply_filters( 'do_rocket_cdn_css_properties', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		if ( ! $do_rewrite ) {
			return $content;
		}

		if ( ! $this->options->get( 'cdn' ) ) {
			return $content;
		}

		return $this->cdn->rewrite_css_properties( $content );
	}

	/**
	 * Gets the host value for each CDN URLs
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param array $hosts Base hosts.
	 * @param array $zones Zones to get the CND URLs associated with.
	 */
	public function get_cdn_hosts( $hosts, $zones ) {
		$cdn_urls = $this->cdn->get_cdn_urls( $zones );

		if ( ! $cdn_urls ) {
			return $hosts;
		}

		$cdn_hosts = array_map(
			function( $url ) {
				return wp_parse_url( rocket_add_url_protocol( $url ), PHP_URL_HOST );
			},
			$cdn_urls
		);

		return array_merge( $hosts, $cdn_hosts );
	}

	/**
	 * Adds CDN URLs to the DNS prefetch links
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param array $domains Domain names to DNS prefetch.
	 * @return array
	 */
	public function add_dns_prefetch_cdn( $domains ) {
		if ( ! $this->is_allowed() ) {
			return $domains;
		}

		$cdn_urls = $this->cdn->get_cdn_urls( [ 'all', 'images', 'css_and_js', 'css', 'js' ] );

		if ( ! $cdn_urls ) {
			return $domains;
		}

		return array_merge( $domains, $cdn_urls );
	}

	/**
	 * Adds the CDN URL on the provided URL
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @param string $url URL to rewrite.
	 * @param string $original_url Original URL for this URL. Optional.
	 * @return string
	 */
	public function add_cdn_url( $url, $original_url = '' ) {
		if ( ! empty( $original_url ) ) {
			if ( $this->cdn->is_excluded( $original_url ) ) {
				return $url;
			}
		}

		return $this->cdn->rewrite_url( $url );
	}

	/**
	 * Checks if CDN can be applied
	 *
	 * @since 3.4
	 * @author Remy Perona
	 *
	 * @return boolean
	 */
	private function is_allowed() {
		if ( rocket_has_constant( 'DONOTROCKETOPTIMIZE' ) && rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( ! $this->options->get( 'cdn' ) ) {
			return false;
		}

		if ( is_rocket_post_excluded_option( 'cdn' ) ) {
			return false;
		}

		return true;
	}
}
