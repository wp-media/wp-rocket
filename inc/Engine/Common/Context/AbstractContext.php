<?php

namespace WP_Rocket\Engine\Common\Context;

use WP_Rocket\Admin\Options_Data;

abstract class AbstractContext implements ContextInterface {


	/**
	 * WPR options.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options WPR options.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Run common checks.
	 *
	 * @param array $args Arguments to configure the checks.
	 *
	 * @return bool
	 */
	public function run_common_checks( array $args = [] ): bool {
		if ( key_exists( 'do_not_optimize', $args ) && (bool) rocket_get_constant( 'DONOTROCKETOPTIMIZE' ) !== (bool) $args['do_not_optimize'] ) {
			return false;
		}

		if ( key_exists( 'bypass', $args ) && rocket_bypass() !== (bool) $args['bypass'] ) {
			return false;
		}

		if ( key_exists( 'option', $args ) && is_string( $args['option'] ) && ! (bool) $this->options->get( $args['option'], 0 ) ) {
			return false;
		}

		if ( key_exists( 'password_protected', $args ) && $this->is_password_protected() !== (bool) $args['password_protected'] ) {
			return false;
		}

		if ( key_exists( 'post_excluded', $args ) && is_string( $args['post_excluded'] ) && is_rocket_post_excluded_option( $args['post_excluded'] ) ) {
			return false;
		}

		// Bailout if user is logged in.
		if ( key_exists( 'logged_in', $args ) && is_user_logged_in() !== (bool) $args['logged_in'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if on a single post and if it is password protected
	 *
	 * @since 3.11
	 *
	 * @return bool
	 */
	private function is_password_protected(): bool {
		if ( ! is_singular() ) {
			return false;
		}

		return post_password_required();
	}
}
