<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\URLLimit;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\{Context\PerformanceMonitoringContext,
	GlobalScore,
	Database\Queries\PerformanceMonitoring as PMQuery};

class Subscriber implements Subscriber_Interface {

	/**
	 * Performance monitoring query instance.
	 *
	 * @var PMQuery
	 */
	private $pm_query;

	/**
	 * User client API instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * GlobalScore instance.
	 *
	 * @var GlobalScore
	 */
	private $global_score;

	/**
	 * Context instance.
	 *
	 * @var PerformanceMonitoringContext
	 */
	private $context;

	/**
	 * Constructor
	 *
	 * @param PMQuery                      $pm_query       Performance monitoring query instance.
	 * @param User                         $user           User client API instance.
	 * @param GlobalScore                  $global_score GlobalScore instance.
	 * @param PerformanceMonitoringContext $context Context instance.
	 */
	public function __construct( PMQuery $pm_query, User $user, GlobalScore $global_score, PerformanceMonitoringContext $context ) {
		$this->pm_query     = $pm_query;
		$this->user         = $user;
		$this->global_score = $global_score;
		$this->context      = $context;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'rocket_insights_upgrade' => [
				[ 'clean_upgrade_plan_urls', 10, 2 ],
				[ 'unblur_rows', 11 ],
			],

		];
	}

	/**
	 * Make sure that the new plan limits on urls are applied.
	 *
	 * @param string $old_plan Old plan sku.
	 * @param string $new_plan New plan sku.
	 *
	 * @return void
	 */
	public function clean_upgrade_plan_urls( $old_plan, $new_plan ) {
		$limit = $this->user->get_pma_addon_limit( $new_plan );
		if ( $this->pm_query->get_total_count() <= $limit ) {
			return;
		}
		$this->pm_query->prune_old_items( $limit );
	}

	/**
	 * Change blurred rows into unblurred.
	 *
	 * @return void
	 */
	public function unblur_rows() {
		if ( ! $this->context->is_allowed() ) {
			return;
		}

		if ( $this->context->is_free_user() ) {
			return;
		}

		$this->pm_query->unblur_rows();
		$this->global_score->reset();
	}
}
