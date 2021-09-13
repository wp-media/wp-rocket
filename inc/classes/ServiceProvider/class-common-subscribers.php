<?php
namespace WP_Rocket\ServiceProvider;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for WP Rocket features common for admin and front
 *
 * @since 3.3
 */
class Common_Subscribers extends AbstractServiceProvider {

	/**
	 * The provides array is a way to let the container
	 * know that a service is provided by this service
	 * provider. Every service that is registered via
	 * this service provider must have an alias added
	 * to this array or it will be ignored.
	 *
	 * @var array
	 */
	protected $provides = [
		'webp_subscriber',
		'detect_missing_tags_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()->share( 'webp_subscriber', 'WP_Rocket\Subscriber\Media\Webp_Subscriber' )
			->addArgument( $options )
			->addArgument( $this->getContainer()->get( 'options_api' ) )
			->addArgument( $this->getContainer()->get( 'cdn_subscriber' ) )
			->addArgument( $this->getContainer()->get( 'beacon' ) )
			->addTag( 'common_subscriber' );
		$this->getContainer()->share( 'detect_missing_tags_subscriber', 'WP_Rocket\Subscriber\Tools\Detect_Missing_Tags_Subscriber' )
			->addTag( 'common_subscriber' );
	}
}
