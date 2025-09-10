<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\URLLimit;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Event_Management\Subscriber_Interface;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring as PMQuery;

/**
 * Subscriber for URL limit functionality
 *
 * @since 3.20
 */
class Subscriber implements Subscriber_Interface {
	/**
	 * Maximum number of URLs for free users.
	 *
	 * @var int
	 */
	const FREE_USER_MAX_URLS = 3;

	/**
	 * Maximum number of URLs for paid users.
	 *
	 * @var int
	 */
	const PAID_USER_MAX_URLS = 10;

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
		$max_urls          = $this->get_max_urls();
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

	/**
	 * Gets the maximum number of URLs allowed based on license type.
	 *
	 * @return int Maximum number of URLs.
	 */
	private function get_max_urls(): int {
		$is_paid_user = $this->user->is_pma_addon_active( $this->user->get_pma_addon_sku_active() );

		return $is_paid_user ? self::PAID_USER_MAX_URLS : self::FREE_USER_MAX_URLS;
	}
}
