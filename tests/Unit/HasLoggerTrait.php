<?php

namespace WP_Rocket\Tests\Unit;

use Mockery;
use WP_Rocket\Logger\Logger;
use WP_Rocket\Logger\LoggerAwareInterface;

trait HasLoggerTrait
{
	/**
	 * Logger mock.
	 *
	 * @var Logger
	 */
	protected $logger;

	/**
	 * Set logger mock.
	 *
	 * @param LoggerAwareInterface $aware Class to add logger on.
	 * @return void
	 */
	public function set_logger(LoggerAwareInterface $aware) {
		$this->logger = Mockery::mock(Logger::class, [
			'notice' => [],
			'debug' => [],
		]);
		$aware->set_logger($this->logger);
	}
}
