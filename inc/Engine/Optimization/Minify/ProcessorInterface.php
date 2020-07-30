<?php

namespace WP_Rocket\Engine\Optimization\Minify;

interface ProcessorInterface {
	/**
	 * Performs the optimization process on the given HTML
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function optimize( $html );
}
