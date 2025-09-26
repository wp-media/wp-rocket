<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\URLLimit;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PMQuery;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Credit\Manager as CreditManager;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext as Context;

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
	 * Credit Manager instance.
	 *
	 * @var CreditManager
	 */
	private $credit_manager;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor
	 *
	 * @param PMQuery       $pm_query       Performance monitoring query instance.
	 * @param User          $user           User client API instance.
	 * @param CreditManager $credit_manager Credit Manager instance.
	 * @param Context       $context        Context instance.
	 */
	public function __construct( PMQuery $pm_query, User $user, CreditManager $credit_manager, Context $context ) {
		$this->pm_query       = $pm_query;
		$this->user           = $user;
		$this->credit_manager = $credit_manager;
		$this->context        = $context;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wpr_pm_allow_add_page'   => 'is_adding_page_allowed',
			'rocket_insights_upgrade' => [ 'clean_upgrade_plan_urls', 10, 2 ],
		];
	}

	/**
	 * Checks if adding a new page is allowed based on user license and current URL count.
	 * For free users, also considers credit availability.
	 *
	 * @return bool True if adding a page is allowed, false otherwise.
	 */
	public function is_adding_page_allowed(): bool {
		$current_url_count = $this->get_url_count();
		$max_urls          = $this->user->get_pma_addon_limit( $this->user->get_pma_addon_sku_active() );
		// Check URL limit first.
		if ( $current_url_count >= $max_urls ) {
			return false;
		}
		
		// For free users, also check credit availability.
		if ( $this->context->is_free_user() && ! $this->credit_manager->has_credit() ) {
			return false;
		}
		
		return true;
	}

	/**
	 * Gets the current count of URLs in the performance monitoring table.
	 *
	 * @return int Number of URLs.
	 */
	private function get_url_count(): int {
		return $this->pm_query->get_total_count();
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
		if ( $this->get_url_count() <= $limit ) {
			return;
		}
		$this->pm_query->prune_old_items( $limit );
	}
}
