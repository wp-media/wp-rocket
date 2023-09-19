<?php

namespace WP_Rocket\Logger;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

class ServiceProvider extends AbstractServiceProvider {


	/**
	 * Services provided.
	 *
	 * @var string[]
	 */
	protected $provides = [
		'logger',
	];

	/**
	 * Register classes provided.
	 */
	public function register() {
		$this->getLeagueContainer()->share( 'logger', Logger::class );
		$this->getLeagueContainer()
			->inflector( LoggerAwareInterface::class )
			->invokeMethod( 'set_logger', [ $this->getContainer()->get( 'logger' ) ] );
	}
}
