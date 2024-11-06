<?php

namespace WP_Rocket\Engine\Media\Fonts\Context;

use WP_Rocket\Engine\Common\Context\ContextInterface;


class Context implements ContextInterface {
	/**
	 * Checks if the feature is allowed.
	 *
	 * @param array $data Optional. Data to check against.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		if ( get_option( 'local_google_fonts' ) ) {
			return false;
		}

		return wpm_apply_filters_typed( 'boolean', 'rocket_self_host_fonts', true );
	}
}
