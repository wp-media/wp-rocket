<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Context;

/**
 * Context class for Performance Monitoring activation
 *
 * Simplified context that doesn't require dependencies during activation
 */
class ActivationContext {

	/**
	 * Check if Performance Monitoring tests are allowed during activation
	 *
	 * @param array $data Context data.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool { // phpcs:ignore
		// During activation, use simple checks.
		// For now, always allow during activation.
		// This can be enhanced later with more sophisticated checks.
		return true;
	}

	/**
	 * Check if this is the first install (not an upgrade)
	 *
	 * @return bool
	 */
	public function is_first_install(): bool {
		// @phpstan-ignore-next-line
		$settings = get_option( 'wp_rocket_settings', [] ); // @phpcs-ignore We will have to change get_option for option object.
		return empty( $settings['version'] );
	}

	/**
	 * Check if we should run homepage tests during activation
	 *
	 * @return bool
	 */
	public function should_run_homepage_tests(): bool {
		// Only run on first install.
		if ( ! $this->is_first_install() ) {
			return false;
		}

		// Check if context allows it.
		if ( ! $this->is_allowed() ) {
			return false;
		}

		// Additional checks can be added here.
		return true;
	}
}
