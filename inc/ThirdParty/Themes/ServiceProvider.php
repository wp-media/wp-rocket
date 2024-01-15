<?php
declare(strict_types=1);

namespace WP_Rocket\ThirdParty\Themes;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

class ServiceProvider extends AbstractServiceProvider {
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
		'avada_subscriber',
		'bridge_subscriber',
		'divi',
		'flatsome',
		'jevelin',
		'minimalist_blogger',
		'polygon',
		'uncode',
		'xstore',
		'themify',
		'shoptimizer',
	];

	/**
	 * Registers the subscribers in the container
	 *
	 * @return void
	 */
	public function register() {
		$options = $this->getContainer()->get( 'options' );

		$this->getContainer()
			->share( 'avada_subscriber', Avada::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'bridge_subscriber', Bridge::class )
			->addArgument( $options )
			->addTag( 'common_subscriber' );

		$factory   = new LazyLoadingValueHolderFactory();
		$container = $this->getContainer();
		$proxy     = $factory->createProxy(
			Divi::class,
			function ( &$wrapped_object, LazyLoadingInterface $proxy, $method, array $parameters, &$initializer ) use ( $container ) {
				$initializer    = null; // disable initialization.
				$wrapped_object = new Divi(
					$container->get( 'options_api' ),
					$container->get( 'options' ),
					$container->get( 'delay_js_html' ),
					$container->get( 'rucss_used_css_controller' )
				);

				return true; // confirm that initialization occurred correctly.
			}
		);

		$this->getContainer()
			->share( 'divi', $proxy )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'flatsome', Flatsome::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'jevelin', Jevelin::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'minimalist_blogger', MinimalistBlogger::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'polygon', Polygon::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'uncode', Uncode::class )
			->addTag( 'common_subscriber' );
		$this->getContainer()
			->share( 'xstore', Xstore::class )
			->addTag( 'common_subscriber' );

		$this->getContainer()->share( 'themify', Themify::class )
			->addArgument( $options );

		$this->getContainer()->share( 'shoptimizer', Shoptimizer::class );
	}
}
