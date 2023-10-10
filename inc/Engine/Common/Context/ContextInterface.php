<?php

namespace WP_Rocket\Engine\Common\Context;

interface ContextInterface {

	/**
	 * Determine if the action is allowed.
	 *
	 * @param array $data Data to pass to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool;
}
