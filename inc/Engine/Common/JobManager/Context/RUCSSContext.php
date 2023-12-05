<?php

namespace WP_Rocket\Engine\Common\JobManager\Context;

class RUCSSContext extends AbstractContext {
	/**
	 * Check if the operation is allowed.
	 *
	 * @param array $data Data to provide to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		$is_allowed = $this->run_common_checks( ['option' => 'remove_unused_css' ] );

		if ( ! $is_allowed ) {
			return false;
		}
	}
}
