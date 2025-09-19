<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Managers;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\License\API\User;

class Plan {

	/**
	 * Options instance.
	 *
	 * @var Options
	 */
	private $options;

	/**
	 * Context instance.
	 *
	 * @var PerformanceMonitoringContext
	 */
	private $context;

	/**
	 * User instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Current plan option name.
	 */
	const CURRENT_PLAN_OPTION_NAME = 'insights_current_plan';

	/**
	 * Constructor.
	 *
	 * @param Options                      $options Options instance.
	 * @param PerformanceMonitoringContext $context Context instance.
	 * @param User                         $user User instance.
	 */
	public function __construct( Options $options, PerformanceMonitoringContext $context, User $user ) {
		$this->options = $options;
		$this->context = $context;
		$this->user    = $user;
	}

	/**
	 * Get current plan name.
	 *
	 * @return string
	 */
	public function get_current_plan(): string {
		return $this->options->get( self::CURRENT_PLAN_OPTION_NAME, 'perf-monitor-free' );
	}

	/**
	 * Check if user upgrades.
	 *
	 * @return void
	 */
	public function check_upgrade() {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		$new_plan = $this->user->get_pma_addon_sku_active();
		$old_plan = $this->get_current_plan();
		if ( $old_plan === $new_plan ) {
			return;
		}

		$this->options->set( self::CURRENT_PLAN_OPTION_NAME, $new_plan );

		/**
		 * Upgrade rocket insights plan.
		 *
		 * @param string $old_plan Old plan.
		 * @param string $new_plan New plan.
		 */
		do_action( 'rocket_insights_upgrade', $old_plan, $new_plan );
	}

	/**
	 * Remove current plan option.
	 */
	public function remove_current_plan(): string {
		$this->options->delete( self::CURRENT_PLAN_OPTION_NAME );
	}
}
