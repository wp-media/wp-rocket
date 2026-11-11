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
	 * Check if Rocket Insights is enabled.
	 *
	 * @param array $data Context data.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {

		// This filter is documented in inc/Engine/Admin/RocketInsights/Context/Context.php.
		$enabled = wpm_apply_filters_typed( 'boolean', 'rocket_rocket_insights_enabled', true );

		// Block for non-live installations.
		if ( $enabled && ! rocket_is_live_site() ) {
			return false;
		}

		return $enabled;
	}
}
