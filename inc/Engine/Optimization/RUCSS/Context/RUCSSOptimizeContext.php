<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Context;

use WP_Rocket\Engine\Common\Context\AbstractContext;

class RUCSSOptimizeContext extends AbstractContext {

	/**
	 * Check if the operation is allowed.
	 *
	 * @param array $data Data to provide to the context.
	 *
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		$is_allowed = $this->run_common_checks(
			[
				'bypass'        => false,
				'option'        => 'remove_unused_css',
				'post_excluded' => 'remove_unused_css',
			]
		);

		if ( ! current_user_can( 'rocket_remove_unused_css' ) ) {
			return false;
		}

		if ( ! $is_allowed ) {
			return false;
		}

		if ( ! rocket_can_display_options() ) {
			return false;
		}

		return true;
	}
}
