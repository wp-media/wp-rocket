<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring\Context;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Context\ContextInterface;

/**
 * Performance Monitoring Context
 *
 * Provides context for Performance Monitoring operations
 */
class PerformanceMonitoringContext implements ContextInterface {

	/**
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Check if Performance Monitoring is enabled.
	 *
	 * @param array $data Context data.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		// @TODO: Add a check if the user has a license.

		return true;
	}

	/**
	 * Check if we should process the homepage first.
	 *
	 * @return bool
	 */
	public function should_process_homepage(): bool {
		// For fresh installations, always process homepage first.
		return true;
	}

	/**
	 * Get the homepage URL for testing.
	 *
	 * @return string
	 */
	public function get_homepage_url(): string {
		return home_url( '/' );
	}

	/**
	 * Check if SaaS credentials are available.
	 *
	 * @return bool
	 */
	public function has_valid_credentials(): bool {
		$email = $this->options->get( 'consumer_email', '' );
		$key   = $this->options->get( 'consumer_key', '' );

		return ! empty( $email ) && ! empty( $key );
	}

	/**
	 * Check if the current environment supports Performance Monitoring.
	 *
	 * @return bool
	 */
	public function is_supported_environment(): bool {
		// Don't run on localhost or development environments.
		if ( rocket_get_constant( 'WP_ENVIRONMENT_TYPE' ) === 'development' ) {
			return false;
		}

		// Don't run if external requests are blocked.
		if ( rocket_get_constant( 'WP_HTTP_BLOCK_EXTERNAL', false ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get test options based on context.
	 *
	 * @param array $data Context data.
	 * @return array
	 */
	public function get_test_options( array $data = [] ): array {
		$options = [
			'device'   => 'desktop', // Default to desktop.
			'location' => 'auto',    // Let SaaS choose optimal location.
		];

		// Allow override of device type.
		if ( ! empty( $data['device'] ) ) {
			$options['device'] = $data['device'];
		}

		// Allow override of location.
		if ( ! empty( $data['location'] ) ) {
			$options['location'] = $data['location'];
		}

		return $options;
	}
}
