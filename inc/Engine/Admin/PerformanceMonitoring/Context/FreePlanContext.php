<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Context;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Credit\Manager as CreditManager;
use WP_Rocket\Engine\Common\Context\ContextInterface;

/**
 * Performance Monitoring Credit Context.
 */
class FreePlanContext implements ContextInterface {
	/**
	 * Constructor.
	 */
	public function __construct() {
	}

	/**
	 * Check if user's plan is free.
	 *
	 * @param array $data Context data.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		return true; // @Todo: We need to check the user API endpoint to get the current plan and check if it's the free one.
	}
}
