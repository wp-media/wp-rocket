<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Admin;

interface ControllerInterface {
	/**
	 * Cleans rows for the current URL.
	 *
	 * @return void
	 */
	public function clean_url(): void;
}
