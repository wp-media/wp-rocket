<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\URLLimit;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PMQuery;

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
	 * Constructor
	 *
	 * @param PMQuery $pm_query    Performance monitoring query instance.
	 * @param User    $user User client API instance.
	 */
	public function __construct( PMQuery $pm_query, User $user ) {
		$this->pm_query = $pm_query;
		$this->user     = $user;
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return [
			'wpr_pm_allow_add_page' => 'is_adding_page_allowed',
		];
	}

	/**
	 * Checks if adding a new page is allowed based on user license and current URL count.
	 *
	 * @return bool True if adding a page is allowed, false otherwise.
	 */
	public function is_adding_page_allowed(): bool {
		$current_url_count = $this->get_url_count();
		$max_urls          = $this->user->get_pma_addon_limit( $this->user->get_pma_addon_sku_active() );
		return $current_url_count < $max_urls;
	}

	/**
	 * Gets the current count of URLs in the performance monitoring table.
	 *
	 * @return int Number of URLs.
	 */
	private function get_url_count(): int {
		$count = $this->pm_query->query(
			[
				'count' => true,
			]
			);

		return (int) $count;
	}
}
