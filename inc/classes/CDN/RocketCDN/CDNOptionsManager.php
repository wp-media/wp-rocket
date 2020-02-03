<?php
namespace WP_Rocket\CDN\RocketCDN;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * Manager for WP Rocket CDN options
 *
 * @since 3.5
 * @author Remy Perona
 */
class CDNOptionsManager {
	/**
	 * WP Options API instance
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * WP Rocket Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor
	 *
	 * @param Options      $options_api WP Options API instance.
	 * @param Options_Data $options     WP Rocket Options instance.
	 */
	public function __construct( Options $options_api, Options_Data $options ) {
		$this->options_api = $options_api;
		$this->options     = $options;
	}

	/**
	 * Enable CDN option, save CDN URL & delete RocketCDN status transient
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @param string $cdn_url CDN URL.
	 * @return void
	 */
	public function enable( $cdn_url ) {
		$this->options->set( 'cdn', 1 );
		$this->options->set( 'cdn_cnames', [ $cdn_url ] );
		$this->options->set( 'cdn_zone', [ 'all' ] );

		$this->options_api->set( 'settings', $this->options->get_options() );

		delete_transient( 'rocketcdn_status' );
	}

	/**
	 * Disable CDN option, remove CDN URL & user token, delete RocketCDN status transient
	 *
	 * @since 3.5
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function disable() {
		$this->options->set( 'cdn', 0 );
		$this->options->set( 'cdn_cnames', [] );
		$this->options->set( 'cdn_zone', [] );

		$this->options_api->set( 'settings', $this->options->get_options() );

		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'rocketcdn_status' );
	}
}
