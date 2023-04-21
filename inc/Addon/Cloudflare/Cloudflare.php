<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\Cloudflare;

use CloudFlare\IpRewrite;
use DateTimeImmutable;
use Exception;
use WP_Error;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Addon\Cloudflare\API\Endpoints;
use WP_Rocket\Addon\Cloudflare\Auth\CredentialsException;

class Cloudflare {

	/**
	 * Options Data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Endpoints instance.
	 *
	 * @var Endpoints
	 */
	private $endpoints;

	/**
	 * WP_Error if Cloudflare Credentials are not valid.
	 *
	 * @var WP_Error
	 */
	private $cloudflare_api_error;

	/**
	 * Constructor
	 *
	 * @param Options_Data $options WP Rocket options instance.
	 * @param Endpoints    $endpoints Endpoints instance.
	 */
	public function __construct( Options_Data $options, Endpoints $endpoints ) {
		$this->endpoints = $endpoints;
		$this->options   = $options;
		// Update api_error with WP_Error if credentials are not valid.
		// Update API with Cloudflare instance with correct auth data.
		$this->check_connection();
	}

	/**
	 * Check valid connection with Cloudflare
	 */
	private function check_connection() {
		$is_api_keys_valid_cf = get_transient( 'rocket_cloudflare_is_api_keys_valid' );

		if ( false === $is_api_keys_valid_cf ) {
			$is_api_keys_valid_cf = $this->is_auth_valid( $this->options->get( 'cloudflare_zone_id', '' ) );
			set_transient( 'rocket_cloudflare_is_api_keys_valid', $is_api_keys_valid_cf, 2 * WEEK_IN_SECONDS );
		}

		if ( is_wp_error( $is_api_keys_valid_cf ) ) {
			// Sets Cloudflare API as WP_Error if credentials are not valid.
			$this->cloudflare_api_error = $is_api_keys_valid_cf;

			return;
		}
	}

	/**
	 * Validate Cloudflare input data.
	 *
	 * @param string $zone_id Cloudflare zone ID.
	 *
	 * @return mixed true if credentials are ok, WP_Error otherwise.
	 */
	public function is_auth_valid( string $zone_id ) {
		if ( empty( $zone_id ) ) {
			$msg = __( 'Missing Cloudflare Zone ID.', 'rocket' );

			$msg .= ' ' . sprintf(
				/* translators: %1$s = opening link; %2$s = closing link */
				__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
				// translators: Documentation exists in EN, FR; use localized URL if applicable.
				'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
				'</a>'
			);

			return new WP_Error( 'cloudflare_no_zone_id', $msg );
		}

		try {
			$result     = $this->endpoints->get_zones( $zone_id );
			$zone_found = false;
			$site_url   = get_site_url();

			if ( function_exists( 'domain_mapping_siteurl' ) ) {
				$site_url = domain_mapping_siteurl( $site_url );
			}

			if ( ! empty( $result ) ) {
				$parsed_url = wp_parse_url( $site_url );

				if ( false !== strpos( strtolower( $parsed_url['host'] ), $result->name ) ) {
					$zone_found = true;
				}
			}

			if ( ! $zone_found ) {
				$msg = __( 'It looks like your domain is not set up on Cloudflare.', 'rocket' );

				$msg .= ' ' . sprintf(
					/* translators: %1$s = opening link; %2$s = closing link */
					__( 'Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
					// translators: Documentation exists in EN, FR; use localized URL if applicable.
					'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
					'</a>'
				);

				return new WP_Error( 'cloudflare_wrong_zone_id', $msg );
			}

			$this->cloudflare_api_error = null;
			return true;
		} catch ( CredentialsException $e ) {
			return new WP_Error(
				'cloudflare_credentials_empty',
				sprintf(
					/* translators: %1$s = opening link; %2$s = closing link */
					__( 'Cloudflare email and/or API key are not set. Read the %1$sdocumentation%2$s for further guidance.', 'rocket' ),
					// translators: Documentation exists in EN, FR; use localized URL if applicable.
					'<a href="' . esc_url( __( 'https://docs.wp-rocket.me/article/18-using-wp-rocket-with-cloudflare/?utm_source=wp_plugin&utm_medium=wp_rocket#add-on', 'rocket' ) ) . '" rel="noopener noreferrer" target="_blank">',
					'</a>'
				)
			);
		}
		catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_invalid_auth', $e->getMessage() );
		}
	}

	/**
	 * Checks if CF has the $action_value set as a Page Rule.
	 *
	 * @param string $action_value Action value.
	 *
	 * @return mixed true/false if $action_value was found or not, WP_Error otherwise.
	 */
	public function has_page_rule( $action_value ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$result    = $this->endpoints->list_pagerules( $this->options->get( 'cloudflare_zone_id', '' ), 'active' );
			$page_rule = wp_json_encode( $result );

			return (bool) preg_match( '/' . $action_value . '/', $page_rule );
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_page_rule_failed', $e->getMessage() );
		}
	}

	/**
	 * Purge Cloudflare cache.
	 *
	 * @return mixed true if the purge is successful, WP_Error otherwise.
	 */
	public function purge_cloudflare() {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$this->endpoints->purge( $this->options->get( 'cloudflare_zone_id', '' ) );

			return true;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_purge_failed', $e->getMessage() );
		}
	}

	/**
	 * Purge Cloudflare Cache by URL
	 *
	 * @param WP_Post $post       The post object.
	 * @param array   $purge_urls URLs cache files to remove.
	 * @param string  $lang       The post language.
	 *
	 * @return mixed true if the purge is successful, WP_Error otherwise
	 */
	public function purge_by_url( $post, $purge_urls, $lang ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$this->endpoints->purge_files( $this->options->get( 'cloudflare_zone_id', '' ), $purge_urls );

			return true;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_purge_failed', $e->getMessage() );
		}
	}

	/**
	 * Set the Browser Cache TTL in Cloudflare.
	 *
	 * @param string $value Value for Cloudflare browser cache TTL.
	 *
	 * @return mixed Value if the update is successful, WP_Error otherwise.
	 */
	public function set_browser_cache_ttl( $value ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$this->endpoints->update_browser_cache_ttl( $this->options->get( 'cloudflare_zone_id', '' ), (int) $value );

			return $this->convert_time( $value );
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_browser_cache', $e->getMessage() );
		}
	}

	/**
	 * Convert value in seconds to seconds/minutes/hours/days
	 *
	 * @param int $value Value in seconds.
	 *
	 * @return string
	 */
	private function convert_time( $value ): string {
		if ( ! is_int( $value ) ) {
			$value = 0;
		}

		$base   = new DateTimeImmutable( '@0' );
		$time   = new DateTimeImmutable( "@$value" );
		$format = '%a ' . __( 'days', 'rocket' );

		if ( 60 > $value ) {
			$format = '%s ' . __( 'seconds', 'rocket' );
		} elseif ( 3600 > $value ) {
			$format = '%i ' . __( 'minutes', 'rocket' );
		} elseif ( 86400 > $value ) {
			$format = '%h ' . __( 'hours', 'rocket' );
		}

		return $base->diff( $time )->format( $format );
	}

	/**
	 * Set the Cloudflare Rocket Loader.
	 *
	 * @param string $value Value for Cloudflare Rocket Loader.
	 *
	 * @return mixed Value if the update is successful, WP_Error otherwise.
	 */
	public function set_rocket_loader( $value ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$this->endpoints->update_rocket_loader( $this->options->get( 'cloudflare_zone_id', '' ), $value );

			return $value;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_rocket_loader', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Minification.
	 *
	 * @param string $value Value for Cloudflare minification.
	 *
	 * @return mixed Value if the update is successful, WP_Error otherwise.
	 */
	public function set_minify( $value ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		$cf_minify_settings = [
			'css'  => $value,
			'html' => 'off',
			'js'   => $value,
		];

		try {
			$this->endpoints->update_minify( $this->options->get( 'cloudflare_zone_id', '' ),  $cf_minify_settings );

			return $value;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_minification', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Caching level.
	 *
	 * @param string $value Value for Cloudflare caching level.
	 *
	 * @return mixed Value if the update is successful, WP_Error otherwise.
	 */
	public function set_cache_level( $value ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$this->endpoints->change_cache_level( $this->options->get( 'cloudflare_zone_id', '' ), $value );

			return $value;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_cache_level', $e->getMessage() );
		}
	}

	/**
	 * Set the Cloudflare Development mode.
	 *
	 * @param string $value Value for Cloudflare development mode.
	 *
	 * @return mixed Value if the update is successful, WP_Error otherwise.
	 */
	public function set_devmode( $value ) {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		if ( 0 === (int) $value ) {
			$value = 'off';
		} else {
			$value = 'on';
		}

		try {
			$this->endpoints->change_development_mode( $this->options->get( 'cloudflare_zone_id', '' ), $value );

			if ( 'on' === $value ) {
				wp_schedule_single_event( time() + 3 * HOUR_IN_SECONDS, 'rocket_cron_deactivate_cloudflare_devmode' );
			}

			return $value;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_dev_mode', $e->getMessage() );
		}
	}

	/**
	 * Get all the current Cloudflare settings for a given domain.
	 *
	 * @return mixed Array of Cloudflare settings, false if any error connection to Cloudflare.
	 */
	public function get_settings() {
		if ( is_wp_error( $this->cloudflare_api_error ) ) {
			return $this->cloudflare_api_error;
		}

		try {
			$cf_settings = $this->endpoints->get_settings( $this->options->get( 'cloudflare_zone_id', '' ) );

			foreach ( $cf_settings as $cloudflare_option ) {
				switch ( $cloudflare_option->id ) {
					case 'browser_cache_ttl':
						$browser_cache_ttl = $cloudflare_option->value;
						break;
					case 'cache_level':
						$cache_level = $cloudflare_option->value;
						break;
					case 'rocket_loader':
						$rocket_loader = $cloudflare_option->value;
						break;
					case 'minify':
						$cf_minify = $cloudflare_option->value;
						break;
				}
			}
			$cf_minify_value = 'on';

			if (
				'off' === $cf_minify->js
				||
				'off' === $cf_minify->css
			) {
				$cf_minify_value = 'off';
			}

			$cf_settings_array = [
				'cache_level'       => $cache_level,
				'minify'            => $cf_minify_value,
				'rocket_loader'     => $rocket_loader,
				'browser_cache_ttl' => $browser_cache_ttl,
			];

			return $cf_settings_array;
		} catch ( Exception $e ) {
			return new WP_Error( 'cloudflare_current_settings', $e->getMessage() );
		}
	}

	/**
	 * Get Cloudflare IPs. No API validation needed, all exceptions returns the default CF IPs array.
	 *
	 * @return object Result of API request if successful, default CF IPs otherwise.
	 */
	public function get_cloudflare_ips() {
		$cf_ips = get_transient( 'rocket_cloudflare_ips' );

		if ( false !== $cf_ips ) {
			return $cf_ips;
		}

		try {
			$cf_ips = $this->endpoints->get_ips();

			if ( empty( $cf_ips ) ) {
				// Set default IPs from Cloudflare if call to Cloudflare /ips API does not contain a success.
				// Prevents from making API calls on each page load.
				$cf_ips = $this->get_default_ips();
			}
		} catch ( Exception $e ) {
			// Set default IPs from Cloudflare if call to Cloudflare /ips API fails.
			// Prevents from making API calls on each page load.
			$cf_ips = $this->get_default_ips();
		}

		set_transient( 'rocket_cloudflare_ips', $cf_ips, 2 * WEEK_IN_SECONDS );

		return $cf_ips;
	}

	/**
	 * Get default Cloudflare IPs.
	 *
	 * @return object Default Cloudflare connecting IPs.
	 */
	private function get_default_ips() {
		$cf_ips = (object) [
			'ipv4_cidrs' => [],
			'ipv6_cidrs' => [],
		];

		$cf_ips->ipv4_cidrs = [
			'173.245.48.0/20',
			'103.21.244.0/22',
			'103.22.200.0/22',
			'103.31.4.0/22',
			'141.101.64.0/18',
			'108.162.192.0/18',
			'190.93.240.0/20',
			'188.114.96.0/20',
			'197.234.240.0/22',
			'198.41.128.0/17',
			'162.158.0.0/15',
			'104.16.0.0/12',
			'104.24.0.0/14',
			'172.64.0.0/13',
			'131.0.72.0/22',
		];

		$cf_ips->ipv6_cidrs = [
			'2400:cb00::/32',
			'2606:4700::/32',
			'2803:f800::/32',
			'2405:b500::/32',
			'2405:8100::/32',
			'2a06:98c0::/29',
			'2c0f:f248::/32',
		];

		return $cf_ips;
	}

	/**
	 * Sets the Cloudflare IP Rewrite
	 *
	 * @return IpRewrite
	 */
	public static function set_ip_rewrite() {
		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new IpRewrite();

			return $instance;
		}

		return $instance;
	}

	/**
	 * Fixes Cloudflare Flexible SSL redirect loop
	 */
	public static function fix_cf_flexible_ssl() {
		$ip_rewrite = self::set_ip_rewrite();

		if ( $ip_rewrite->isCloudFlare() ) {
			// Fixes Flexible SSL.
			if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && 'https' === $_SERVER['HTTP_X_FORWARDED_PROTO'] ) {
				$_SERVER['HTTPS'] = 'on';
			}
		}
	}
}
