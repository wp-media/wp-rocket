<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Managers;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;

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
	 *  User client API instance.
	 *
	 * @var UserClient
	 */
	private $user_client;

	/**
	 * Current plan option name.
	 */
	const CURRENT_PLAN_OPTION_NAME = 'insights_current_plan';

	/**
	 * Credit option name.
	 */
	const CREDIT_OPTION_NAME = 'pm_credit';

	/**
	 * Last reset date option name.
	 */
	const RESET_CREDIT_OPTION_NAME = 'pm_last_reset';

	/**
	 * Constructor.
	 *
	 * @param Options                      $options Options instance.
	 * @param PerformanceMonitoringContext $context Context instance.
	 * @param User                         $user User instance.
	 * @param UserClient                   $user_client  User client API instance.
	 */
	public function __construct( Options $options, PerformanceMonitoringContext $context, User $user, UserClient $user_client ) {
		$this->options     = $options;
		$this->context     = $context;
		$this->user        = $user;
		$this->user_client = $user_client;
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
			$this->validate_plan_expiration();

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
	 *
	 * @return void
	 */
	public function remove_current_plan() {
		$this->options->delete( self::CURRENT_PLAN_OPTION_NAME );
	}

	/**
	 * Validate plan expiration.
	 *
	 * @return void
	 */
	private function validate_plan_expiration() {
		$expiration = $this->user->get_license_expiration();
		if ( empty( $expiration ) ) {
			return;
		}

		if ( $expiration >= time() ) {
			return;
		}

		$this->remove_customer_data_cache();
	}

	/**
	 * Flush customer data cache.
	 *
	 * @return void
	 */
	public function remove_customer_data_cache() {
		$this->user_client->flush_cache();
	}

	/**
	 * Get current credit number.
	 *
	 * @return int
	 */
	public function get_credit(): int {
		return (int) $this->options->get( self::CREDIT_OPTION_NAME, 0 );
	}

	/**
	 * Check if we have one credit at least.
	 *
	 * @return bool
	 */
	public function has_credit(): bool {
		return ! $this->context->is_free_user() || 0 < $this->get_credit();
	}

	/**
	 * Decrease credit.
	 *
	 * @return bool
	 */
	public function decrease_credit(): bool {
		$credit = $this->get_credit();
		if ( 0 === $credit ) {
			return false;
		}
		$this->options->set( self::CREDIT_OPTION_NAME, $credit - 1 );
		return true;
	}

	/**
	 * Reset credit, this will be called mainly each month.
	 *
	 * @return void
	 */
	public function reset_credit() {
		// Check if the duration from last reset date and time now is more than or equal 1 month
		// As a sanity check not to have this action to run manually and hack the system.
		$last_reset_date = $this->get_last_reset_date();
		if ( ! empty( $last_reset_date ) && MONTH_IN_SECONDS > ( time() - $last_reset_date ) ) {
			return;
		}

		$this->options->set( self::CREDIT_OPTION_NAME, 3 );
		$this->set_last_reset_date();
	}


	/**
	 * Get number of settings saving per month.
	 *
	 * @return int
	 */
	public function get_last_reset_date(): int {
		return (int) $this->options->get( self::RESET_CREDIT_OPTION_NAME, 0 );
	}

	/**
	 * Reset number of settings saving per month.
	 *
	 * @return void
	 */
	public function set_last_reset_date(): void {
		$this->options->set( self::RESET_CREDIT_OPTION_NAME, time() );
	}

	/**
	 * Gets max URLs allowed based on current plan.
	 *
	 * @return int
	 */
	public function max_urls(): int {
		return $this->user->get_pma_addon_limit( $this->user->get_pma_addon_sku_active() );
	}
}
