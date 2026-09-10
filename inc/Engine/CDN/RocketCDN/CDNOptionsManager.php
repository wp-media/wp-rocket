<?php
namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * Manager for WP Rocket CDN options
 *
 * @since 3.5
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
	 * Reads the current settings via $this->options_api rather than $this->options: see
	 * the note on set_cdn_state() below - a stale Options_Data snapshot here can silently
	 * clobber a write made elsewhere in the same request (e.g. maybe_retry_activation()
	 * reading subscription data, which can trigger a set_cdn_state() write, before calling
	 * this method later in the same request).
	 *
	 * Other classes reading 'cdn' straight off their own injected Options_Data instance
	 * (CDN.php, Render/Controller.php, RocketCDN/Rest.php, Subscriber.php, Support/Meta.php)
	 * still see a live value for the rest of the request: see
	 * CdnStateBridge::resolve_live_cdn(), hooked on pre_get_rocket_option_cdn. Mutating
	 * $this->options here would not help those readers anyway - the 'options' container
	 * service is registered with add(), not addShared(), so every class gets its own
	 * independently-resolved Options_Data instance; there is no single shared object to
	 * mirror a write onto.
	 *
	 * @since 3.5
	 *
	 * @param bool $clear_cache Clear website whole cache.
	 * @return void
	 */
	public function enable( bool $clear_cache = true ) {
		$settings        = $this->options_api->get( 'settings', [] );
		$settings['cdn'] = 1;

		$this->options_api->set( 'settings', $settings );

		delete_transient( 'rocketcdn_status' );
		if ( $clear_cache ) {
			rocket_clean_domain();
		}
	}

	/**
	 * Set the CDN state and persist it.
	 *
	 * Writing through Options::set( 'settings', ... ) triggers WP's
	 * update_option_wp_rocket_settings action, which Subscriber::maybe_clear_cache()
	 * already listens on to clear the right cache scope for the transition.
	 *
	 * Reads the current settings via $this->options_api rather than $this->options:
	 * Options_Data is a per-request snapshot taken when the container built it, so it
	 * won't reflect a write made elsewhere in the same request through a different path
	 * (e.g. a raw update_option() call) - writing that stale snapshot back here would
	 * silently clobber whatever changed since.
	 *
	 * @param string $state One of Context::CDN_STATE_NOTHING, Context::ROCKETCDN_FREE_TYPE,
	 *                       Context::ROCKETCDN_PAID_TYPE, or Context::BYOCDN_TYPE.
	 * @return void
	 */
	public function set_cdn_state( string $state ) {
		$settings              = $this->options_api->get( 'settings', [] );
		$settings['cdn_state'] = $state;

		$this->options_api->set( 'settings', $settings );
	}

	/**
	 * Save RocketCDN user token.
	 *
	 * @since 3.20.5
	 *
	 * @param string $token RocketCDN user token.
	 * @return void
	 */
	public function save_token( string $token ): void {
		update_option( 'rocketcdn_user_token', $token );
	}

	/**
	 * Disable CDN option, remove CDN URL & user token, delete RocketCDN status transient
	 *
	 * Reads the current settings via $this->options_api rather than $this->options - see
	 * the note on enable() above.
	 *
	 * @since 3.5
	 *
	 * @return void
	 */
	public function disable() {
		$settings        = $this->options_api->get( 'settings', [] );
		$settings['cdn'] = 0;

		$this->options_api->set( 'settings', $settings );

		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'rocketcdn_status' );
		rocket_clean_domain();
	}

	/**
	 * Get current CDN cnames.
	 *
	 * @return array
	 */
	public function get_cdn_cnames() {
		return $this->options->get( 'cdn_cnames', [] );
	}

	/**
	 * Check if there is saved user token.
	 *
	 * @return bool
	 */
	public function has_token(): bool {
		return ! empty( get_option( 'rocketcdn_user_token' ) );
	}

	/**
	 * Flush subscription cache.
	 *
	 * @return void
	 */
	public function flush_subscription_cache() {
		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_website_search' );
	}
}
