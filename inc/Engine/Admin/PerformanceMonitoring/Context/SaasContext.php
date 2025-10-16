<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Context;

use WP_Rocket\Engine\Common\Context\ContextInterface;

/**
 * Performance Monitoring Saas Context.
 *
 * Provides context for Performance Monitoring SaaS operations.
 */
class SaasContext implements ContextInterface {

    /**
	 * Context instance.
	 *
	 * @var PerformanceMonitoringContext
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * @param PerformanceMonitoringContext $context Context instance.
	 */
	public function __construct( PerformanceMonitoringContext $context ) {
		$this->context  = $context;
	}

	/**
	 * Check if Performance Monitoring is enabled.
	 *
	 * @param array $data Context data.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {

		// This filter is documented in inc/Engine/Admin/PerformanceMonitoring/Context/PerformanceMonitoringContext.php.
		$enabled = wpm_apply_filters_typed( 'boolean', 'rocket_performance_monitoring_enabled', true );

		// Block for reseller accounts and non-live installations.
		if ( $enabled && $this->context->is_reseller_or_non_live() ) {
			return false;
		}

		return $enabled;
	}
}
