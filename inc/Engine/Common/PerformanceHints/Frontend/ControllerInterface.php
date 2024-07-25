<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\PerformanceHints\Frontend;

interface ControllerInterface {
	/**
	 * Applies optimization.
	 *
	 * @param string $html HTML content.
	 * @param object $row Database Row.
	 *
	 * @return string
	 */
	public function optimize( string $html, $row ): string;

	/**
	 * List of elements to be considered for optimization.
	 *
	 * @param array $data Array of data passed in beacon.
	 *
	 * @return array
	 */
	public function target_elements( array $data ): array;
}
