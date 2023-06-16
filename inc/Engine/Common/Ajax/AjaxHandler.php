<?php

namespace WP_Rocket\Engine\Common\Ajax;

class AjaxHandler {

	/**
	 * Validate the referer before going futher.
	 *
	 * @param array $args Arguments to configure the validation.
	 *
	 * @return bool
	 */
	public function validate_referer( array $args ) {

		if ( key_exists( 'referer', $args ) && is_string( $args['referer'] ) ) {
			check_admin_referer( $args['referer'] );
		}

		if ( key_exists( 'capacities', $args ) && is_string( $args['capacities'] ) && ! current_user_can( $args['capacities'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Redirect the page at the end of the logic.
	 *
	 * @param string $url URl to redirect to (by default referrer).
	 * @return void
	 */
	public function redirect( string $url = '' ) {
		wp_safe_redirect( '' === $url ? wp_get_referer() : $url );
		rocket_get_constant( 'WP_ROCKET_IS_TESTING', false ) ? wp_die() : exit;
	}
}
