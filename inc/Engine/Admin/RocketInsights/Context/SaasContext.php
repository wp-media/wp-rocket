<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Context;

use WP_Rocket\Engine\Common\Context\ContextInterface;

/**
 * Rocket Insights Saas Context.
 *
 * Provides context for Rocket Insights SaaS operations.
 */
class SaasContext implements ContextInterface {

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param Context $context Context instance.
	 */
	public function __construct( Context $context ) {
		$this->context = $context;
	}

	/**
	 * Check if Rocket Insights is enabled.
	 *
	 * @param array $data Context data.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {

		// This filter is documented in inc/Engine/Admin/RocketInsights/Context/Context.php.
		$enabled = wpm_apply_filters_typed( 'boolean', 'rocket_rocket_insights_enabled', true );

		// Block for reseller accounts and non-live installations.
		if ( $enabled && $this->context->is_reseller_or_non_live() ) {
			return false;
		}

		return $enabled;
	}
}
