<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold\Context;

use WP_Rocket\Engine\Common\Context\ContextInterface;

class Context implements ContextInterface {
	/**
	 * Determine if the action is allowed.
	 *
	 * @param array $data Data to pass to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		/**
		 * Filters to manage above the fold optimization
		 *
		 * @param bool $allow True to allow, false otherwise.
		 */
		$is_atf_enabled = apply_filters( 'rocket_above_the_fold_optimization', true );

		if ( ! is_bool( $is_atf_enabled ) ) {
			return true;
		}

		return $is_atf_enabled;
	}
}
