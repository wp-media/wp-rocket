<?php

namespace WP_Rocket\Logger;

interface LoggerAwareInterface {

	/**
	 * Set the logger.
	 *
	 * @param Logger $logger Logger instance.
	 * @return void
	 */
	public function set_logger( Logger $logger );
}
