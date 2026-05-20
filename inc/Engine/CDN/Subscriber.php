<?php
namespace WP_Rocket\Engine\CDN;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Drivers\DriverInterface;
use WP_Rocket\Engine\Optimization\UrlTrait;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Subscriber for the CDN feature
 *
 * @since 3.4
 */
class Subscriber implements Subscriber_Interface {
	use UrlTrait;

	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * CDN instance
	 *
	 * @var CDN
	 */
	private $cdn;

	/**
	 * CDN Driver (Strategy)
	 *
	 * @var DriverInterface|null
	 */
	private $driver;

	/**
	 * Constructor
	 *
	 * @param Options_Data         $options WP Rocket Options_Data instance.
	 * @param CDN                  $cdn     CDN instance.
	 * @param Options              $options_api     Options instance.
	 * @param DriverInterface|null $driver   CDN Driver instance, optional.
	 */
	public function __construct( Options_Data $options, CDN $cdn, Options $options_api, ?DriverInterface $driver = null ) {
		$this->options     = $options;
		$this->cdn         = $cdn;
		$this->options_api = $options_api;
		$this->driver      = $driver;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.4
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rocket_buffer'                => [
				[ 'rewrite', 2 ],
				[ 'rewrite_srcset', 3 ],
			],
			'rocket_css_content'           => 'rewrite_css_properties',
			'rocket_usedcss_content'       => 'rewrite_css_properties',
			'rocket_cdn_hosts'             => [ 'get_cdn_hosts', 10, 2 ],
			'rocket_dns_prefetch'          => 'add_dns_prefetch_cdn',
			'rocket_facebook_sdk_url'      => 'add_cdn_url',
			'rocket_css_url'               => [ 'add_cdn_url', 10, 2 ],
			'rocket_js_url'                => [ 'add_cdn_url', 10, 2 ],
			'rocket_asset_url'             => [ 'maybe_replace_url', 10, 2 ],
			'wp_resource_hints'            => [ 'add_preconnect_cdn', 10, 2 ],
			'rocket_font_url'              => [ 'add_cdn_url', 10, 2 ],
			'rocket_first_install_options' => 'add_cdn_type_option',
			'wp_rocket_upgrade'            => [
				[ 'on_update_add_cdn_type_option', 10, 2 ],
			],
		];
	}

	/**
	 * Rewrites URLs to the CDN URLs if allowed
	 *
	 * @since 3.4
	 *
	 * @param string $html HTML content.
	 *
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
	 *
	 * @param string $html HTML content.
	 *
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
	 *
	 * @param string $content CSS content.
	 *
	 * @return string
	 */
	public function rewrite_css_properties( $content ) {
		/**
		 * Filters the application of the CDN on CSS properties
		 *
		 * @since 2.6
		 *
		 * @param bool $do_rewrite true to apply CDN to properties, false otherwise.
		 */
		$do_rewrite = apply_filters( 'do_rocket_cdn_css_properties', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		if ( ! $do_rewrite ) {
			return $content;
		}

		if ( ! $this->is_cdn_enabled() ) {
			return $content;
		}

		return $this->cdn->rewrite_css_properties( $content );
	}

	/**
	 * Gets the host value for each CDN URLs
	 *
	 * @since 3.4
	 *
	 * @param array $hosts Base hosts.
	 * @param array $zones Zones to get the CND URLs associated with.
	 *
	 * @return array
	 */
	public function get_cdn_hosts( array $hosts = [], array $zones = [ 'all' ] ) {
		$cdn_urls = $this->cdn->get_cdn_urls( $zones );

		if ( empty( $cdn_urls ) ) {
			return $hosts;
		}

		foreach ( $cdn_urls as $cdn_url ) {
			$parsed = get_rocket_parse_url( rocket_add_url_protocol( $cdn_url ) );

			if ( empty( $parsed['host'] ) ) {
				continue;
			}

			$hosts[] = untrailingslashit( $parsed['host'] . $parsed['path'] );
		}

		return array_unique( $hosts );
	}

	/**
	 * Adds CDN URLs to the DNS prefetch links
	 *
	 * @since 3.4
	 *
	 * @param array $domains Domain names to DNS prefetch.
	 *
	 * @return array
	 */
	public function add_dns_prefetch_cdn( $domains ) {
		if ( ! $this->is_allowed() || ! $this->can_insert_resource_hints() ) {
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
	 *
	 * @param string $url          URL to rewrite.
	 * @param string $original_url Original URL for this URL. Optional.
	 *
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
	 * Replace CDN URL with site URL on the provided asset URL.
	 *
	 * @since 3.5.3
	 *
	 * @param string $url   URL of the asset.
	 * @param array  $zones Array of corresponding zones for the asset.
	 *
	 * @return string
	 */
	public function maybe_replace_url( $url, array $zones = [ 'all' ] ) {
		if ( ! $this->is_allowed() ) {
			return $url;
		}

		$url_parts = get_rocket_parse_url( $url );

		if ( empty( $url_parts['host'] ) ) {
			return $url;
		}

		$site_url_parts = get_rocket_parse_url( site_url() );

		if ( empty( $site_url_parts['host'] ) ) {
			return $url;
		}

		if ( $url_parts['host'] === $site_url_parts['host'] ) {
			return $url;
		}

		$cdn_urls = $this->cdn->get_cdn_urls( $zones );

		if ( empty( $cdn_urls ) ) {
			return $url;
		}

		$cdn_urls = array_map( 'rocket_add_url_protocol', $cdn_urls );

		$site_url = $site_url_parts['scheme'] . '://' . $site_url_parts['host'];

		foreach ( $cdn_urls as $cdn_url ) {
			if ( false === strpos( $url, $cdn_url ) ) {
				continue;
			}

			return str_replace( $cdn_url, $site_url, $url );
		}

		return $url;
	}

	/**
	 * Add a preconnect tag for the CDN.
	 *
	 * @since 3.8.3
	 *
	 * @param array  $urls          The initial array of wp_resource_hint urls.
	 * @param string $relation_type The relation type for the hint: eg., 'preconnect', 'prerender', etc.
	 *
	 * @return array The filtered urls.
	 */
	public function add_preconnect_cdn( array $urls, string $relation_type ): array {
		if (
			'preconnect' !== $relation_type
			||
			rocket_bypass()
			||
			! $this->is_allowed()
			||
			! $this->is_cdn_enabled()
			||
			! $this->can_insert_resource_hints()
		) {
			return $urls;
		}

		$cdn_urls = $this->cdn->get_cdn_urls( [ 'all', 'images', 'css_and_js', 'css', 'js' ] );

		if ( empty( $cdn_urls ) ) {
			return $urls;
		}

		foreach ( $cdn_urls as $url ) {
			$url_parts = get_rocket_parse_url( $url );

			if ( empty( $url_parts['scheme'] ) ) {
				if ( preg_match( '/^(?![\/])(?=[^\.]+\/).+/i', $url ) ) {
					continue;
				}

				$url       = '//' . $url;
				$url_parts = get_rocket_parse_url( $url );
			}

			$domain = empty( $url_parts['scheme'] )
				? '//' . $url_parts['host']
				: $url_parts['scheme'] . '://' . $url_parts['host'];

			// Note: As of 22 Feb, 2021 we cannot add more than one instance of a domain url
			// on the wp_resource_hint() hook -- wp_resource_hint() will
			// only actually print the first one.
			// Ideally, we want both because CSS resources will use the crossorigin version,
			// But JS resources will not.
			// Jonathan has submitted a ticket to change this behavior:
			// @see https://core.trac.wordpress.org/ticket/52465
			// Until then, we order these to prefer/print the non-crossorigin version.
			$urls[] = [ 'href' => $domain ];
			$urls[] = [
				'href'        => $domain,
				'crossorigin' => 'anonymous',
			];
		}

		return $urls;
	}

	/**
	 * Checks if CDN can be applied
	 *
	 * @since 3.4
	 *
	 * @return boolean
	 */
	private function is_allowed() {
		if ( rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) ) {
			return false;
		}

		if ( ! $this->is_cdn_enabled() ) {
			return false;
		}

		if ( is_rocket_post_excluded_option( 'cdn' ) ) {
			return false;
		}

		if ( $this->driver && ! $this->driver->should_rewrite_url( $this->get_current_url() ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if the CDN option is enabled
	 *
	 * @since 3.5.5
	 *
	 * @return bool
	 */
	private function is_cdn_enabled() {
		return (bool) $this->options->get( 'cdn', 0 );
	}

	/**
	 * Check if CDN can insert resource hints into head.
	 *
	 * @return bool
	 */
	private function can_insert_resource_hints(): bool {
		/**
		 * Enable adding resource hints by CDN feature.
		 *
		 * @since 3.19
		 *
		 * @param bool $can_insert Can cdn insert resource hints or not, default is true.
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_cdn_insert_resource_hints', true );
	}

	/**
	 * Adds cdn_type option to WP Rocket options.
	 *
	 * @since 3.22
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_cdn_type_option( array $options ) {
		$options = (array) $options;

		$options['cdn_type'] = 'rocketcdn';

		return $options;
	}

	/**
	 * Add cdn_type option when upgrading from a version older than 3.22
	 *
	 * @since 3.22
	 *
	 * @param string $new_version New plugin version.
	 * @param string $old_version Previously installed plugin version.
	 *
	 * @return void
	 */
	public function on_update_add_cdn_type_option( string $new_version, string $old_version ) {
		// Bail early.
		if ( version_compare( $old_version, '3.22.0', '>=' ) ) {
			return;
		}
		$cdn_type = 'rocketcdn';
		// Check if cdn was enabled in previous version and default to byocdn.
		if ( (bool) $this->options->get( 'cdn', 0 ) ) {
			$cdn_type = 'byocdn';
		}

		$current_options             = $this->options_api->get( 'settings', [] );
		$current_options['cdn_type'] = $cdn_type;

		$this->options_api->set( 'settings', $current_options );
	}
}
