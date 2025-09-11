<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Context;

use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Credit\Manager as CreditManager;
use WP_Rocket\Engine\Common\Context\ContextInterface;

/**
 * Performance Monitoring Credit Context.
 */
class FreePlanContext implements ContextInterface {
	/**
	 * User client API instance.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Constructor.
	 *
	 * @param User $user User client API instance.
	 */
	public function __construct( User $user ) {
		$this->user = $user;
	}

	/**
	 * Check if user's plan is free.
	 *
	 * @param array $data Context data.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		return $this->user->is_pma_addon_active( $this->user->get_pma_addon_sku_active() );
	}
}
