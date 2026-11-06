<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Context;

use WP_Rocket\Engine\Common\Context\AbstractContext;

class OptimizationContext extends AbstractContext {
	/**
	 * Checks if the feature is allowed.
	 *
	 * @param array $data Optional. Data to check against.
	 *
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		$checks = [
			'option'          => 'host_fonts_locally',
			'do_not_optimize' => false,
			'bypass'          => false,
		];

		return $this->run_common_checks( $checks );
	}
}
