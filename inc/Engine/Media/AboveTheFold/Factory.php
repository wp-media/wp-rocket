<?php

declare(strict_types=1);

namespace WP_Rocket\Engine\Media\AboveTheFold;

use WP_Rocket\Engine\Common\PerformanceHints\AbstractFactory;

class Factory implements AbstractFactory {

	/**
	 * Instatiate the class.
	 */
	public function __construct() {
		// Assign objects to their properties.
	}

	/**
	 * Provides an Ajax object.
	 */
	public function ajax() {
		// Return Ajax object.
	}

	/**
	 * Provides a Frontend object.
	 */
	public function frontend() {
		// Return Fontend object.
	}

	/**
	 * Provides a Table object.
	 */
	public function table() {
		// Return Table object.
	}

	/**
	 * Provides a Queries object.
	 */
	public function queries() {
		// Return Queries object.
	}
}
