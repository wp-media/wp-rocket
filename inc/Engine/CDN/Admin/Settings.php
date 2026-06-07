<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\Admin;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Event_Management\Subscriber_Interface;

class Settings {

	/**
	 * WP Rocket Options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Subscription controller instance.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * Constructor.
	 *
	 * @param Options_Data           $options                 WP Rocket Options_Data instance.
	 * @param Options                $options_api             Options instance.
	 * @param SubscriptionController $subscription_controller Subscription controller instance.
	 */
	public function __construct(
		Options_Data $options,
		Options $options_api,
		SubscriptionController $subscription_controller
	) {
		$this->options                 = $options;
		$this->options_api             = $options_api;
		$this->subscription_controller = $subscription_controller;
	}

	/**
	 * Adds CDN driver options to WP Rocket options.
	 *
	 * @since 3.22
	 *
	 * @param array $options WP Rocket options array.
	 *
	 * @return array
	 */
	public function add_cdn_driver_options_on_first_install( array $options ): array {
		$options = (array) $options;

		$options[ Context::BYOCDN_TYPE ]    = 1;
		$options[ Context::ROCKETCDN_TYPE ] = 1;

		return $options;
	}

	/**
	 * Sets the CDN driver options when updating from a version prior to 3.22.0.
	 *
	 * @param string $new_version The new plugin version.
	 * @param string $old_version The previous plugin version.
	 * @return void
	 */
	public function add_cdn_driver_options_on_update( string $new_version, string $old_version ): void {
		if ( version_compare( $old_version, '3.22.0', '>=' ) ) {
			return;
		}

		$current_options                            = $this->options_api->get( 'settings', [] );
		$current_options[ Context::BYOCDN_TYPE ]    = 1;
		$current_options[ Context::ROCKETCDN_TYPE ] = 1;
		$cdn_enabled                                = (bool) $current_options['cdn'];
		$has_active_rocketcdn_sub                   = $this->subscription_controller->has_active_subscription();
		$has_cnames                                 = ! empty( $current_options['cdn_cnames'] );

		if ( ! $cdn_enabled && $has_active_rocketcdn_sub ) {
			$current_options[ Context::ROCKETCDN_TYPE ] = 0;
		}

		if ( ! $cdn_enabled && ! $has_active_rocketcdn_sub && $has_cnames ) {
			$current_options[ Context::BYOCDN_TYPE ] = 0;
		}

		$this->options_api->set( 'settings', $current_options );
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
	public function on_update_add_cdn_type_option( string $new_version, string $old_version ): void {
		// Bail early.
		if ( version_compare( $old_version, '3.22.0', '>=' ) ) {
			return;
		}
		$cdn_type = 'rocketcdn';
		// Check if a CNAME is saved and no RocketCDN subscription, then default to byocdn.
		if (
			! $this->subscription_controller->has_active_subscription()
			&&
			! empty( $this->options->get( 'cdn_cnames', [] ) )
		) {
			$cdn_type = 'byocdn';
		}

		$current_options             = $this->options_api->get( 'settings', [] );
		$current_options['cdn_type'] = $cdn_type;

		$this->options_api->set( 'settings', $current_options );
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
	public function add_cdn_type_option( array $options ): array {
		$options = (array) $options;

		$options['cdn_type'] = 'rocketcdn';

		return $options;
	}
}
