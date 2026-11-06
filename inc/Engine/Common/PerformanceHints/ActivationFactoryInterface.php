<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\PerformanceHints;

use WP_Rocket\Engine\Common\Context\ContextInterface;

interface ActivationFactoryInterface {
	/**
	 * Provides a Context interface
	 *
	 * @return ContextInterface
	 */
	public function get_context(): ContextInterface;
}
