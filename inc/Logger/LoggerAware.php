<?php

namespace WP_Rocket\Logger;

trait LoggerAware {

	/**
	 * Logger instance.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Set the logger.
	 *
	 * @param Logger $logger Logger instance.
	 * @return void
	 */
	public function set_logger( Logger $logger ) {
		$this->logger = $logger;
	}
}
