<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Context;

use WP_Rocket\Engine\Common\Context\ContextInterface;

class Context implements ContextInterface {
	/**
	 * Determine if the action is allowed.
	 *
	 * @param array $data Data to pass to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		if ( get_option( 'wp_rocket_no_licence' ) ) {
			return false;
		}

		return true;
	}
}
