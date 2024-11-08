<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\Fonts\Context;

use WP_Rocket\Engine\Common\Context\AbstractContext;

class Context extends AbstractContext {
	/**
	 * Checks if the feature is allowed.
	 *
	 * @param array $data Optional. Data to check against.
	 *
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		$is_allowed = $this->run_common_checks(
			[
				'do_not_optimize'    => false,
				'bypass'             => false,
				'option'             => 'host_fonts_locally',
				'logged_in'          => false,
			]
		);

		return $is_allowed;
	}
}
