<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\PerformanceHints\AJAX;

interface ControllerInterface {
	/**
	 * Initiates the addition of data.
     * 
     * @return void
	 */
	public function add_data(): void;

	/**
	 * Initiates the checking of data.
     * 
     * @return void
	 */
	public function check_data(): void;
}
