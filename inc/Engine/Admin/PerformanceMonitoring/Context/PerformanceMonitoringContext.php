<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Context;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;
use WP_Rocket\Engine\License\API\User;

/**
 * Performance Monitoring Context
 *
 * Provides context for Performance Monitoring operations
 */
class PerformanceMonitoringContext implements ContextInterface {

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * User client API instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Options instance.
	 * @param User         $user User client API instance.
	 */
	public function __construct( Options_Data $options, User $user ) {
		$this->options = $options;
		$this->user    = $user;
	}

	/**
	 * Check if Performance Monitoring is enabled.
	 *
	 * @param array $data Context data.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		$enabled = current_user_can( 'rocket_manage_options' ) || wp_doing_cron();

		/**
		 * Filters performance monitoring addon enable status.
		 *
		 * @param boolean $enabled Current status, default is true.
		 */
		$enabled = wpm_apply_filters_typed( 'boolean', 'rocket_performance_monitoring_enabled', $enabled );

		// Block for reseller accounts and non-live installations.
		if ( $enabled && $this->is_reseller_or_non_live() ) {
			return false;
		}

		return $enabled;
	}

	/**
	 * Check if the current user is on the free plan or not.
	 *
	 * @return bool
	 */
	public function is_free_user(): bool {
		return $this->user->is_pma_free_active( $this->user->get_pma_addon_sku_active() );
	}

	/**
	 * Determines if scheduling for Performance Monitoring is allowed.
	 *
	 * @return bool True if Performance Monitoring is enabled, false otherwise.
	 */
	public function is_schedule_allowed(): bool {
		return (bool) $this->options->get( 'performance_monitoring', 0 );
	}

	/**
	 * Check if current installation is a reseller account or non-live site.
	 *
	 * This will block Performance Monitoring functionality for reseller accounts and localhost installations.
	 *
	 * @since 3.20
	 *
	 * @return bool True if is reseller account or non-live installation, false otherwise.
	 */
	public function is_reseller_or_non_live(): bool {
		// Hide for reseller accounts.
		if ( $this->user->is_reseller_account() ) {
			return true;
		}

		// Hide for non-live installations.
		if ( ! rocket_is_live_site() ) {
			return true;
		}

		return false;
	}
}
