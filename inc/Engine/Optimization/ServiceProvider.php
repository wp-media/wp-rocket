<?php
namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket optimizations
 *
 * @since  3.3
 * @since  3.6 Renamed and moved into this module.
 */
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
		'config',
		'tests',
		'buffer_optimization',
		'buffer_subscriber',
		'cache_dynamic_resource',
		'ie_conditionals_subscriber',
		'optimize_google_fonts',
		'optimize_google_fonts_v2',
		'combine_google_fonts_subscriber',
		'minify_css_subscriber',
		'minify_js_subscriber',
	];

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register() {
		$options    = $this->getContainer()->get( 'options' );
		$filesystem = rocket_direct_filesystem();

		$this->getContainer()->add( 'config', 'WP_Rocket\Buffer\Config' )
			->addArgument( [ 'config_dir_path' => rocket_get_constant( 'WP_ROCKET_CONFIG_PATH' ) ] );
		$this->getContainer()->add( 'tests', 'WP_Rocket\Buffer\Tests' )
			->addArgument( $this->getContainer()->get( 'config' ) );
		$this->getContainer()->add( 'buffer_optimization', 'WP_Rocket\Buffer\Optimization' )
			->addArgument( $this->getContainer()->get( 'tests' ) );
		$this->getContainer()->share( 'buffer_subscriber', 'WP_Rocket\Subscriber\Optimization\Buffer_Subscriber' )
			->addArgument( $this->getContainer()->get( 'buffer_optimization' ) )
			->addTag( 'front_subscriber' );
		$this->getContainer()->share( 'cache_dynamic_resource', 'WP_Rocket\Engine\Optimization\CacheDynamicResource' )
			->addArgument( $options )
			->addArgument( WP_ROCKET_CACHE_BUSTING_PATH )
			->addArgument( WP_ROCKET_CACHE_BUSTING_URL )
			->addTag( 'front_subscriber' );
		$this->getContainer()->add( 'optimize_google_fonts', 'WP_Rocket\Engine\Optimization\GoogleFonts\Combine' );
		$this->getContainer()->add( 'optimize_google_fonts_v2', 'WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2' );
		$this->getContainer()->share( 'combine_google_fonts_subscriber', 'WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber' )
			->addArgument( $this->getContainer()->get( 'optimize_google_fonts' ) )
			->addArgument( $this->getContainer()->get( 'optimize_google_fonts_v2' ) )
			->addArgument( $options )
			->addTag( 'front_subscriber' );
		$this->getContainer()->share( 'minify_css_subscriber', 'WP_Rocket\Engine\Optimization\Minify\CSS\Subscriber' )
			->addArgument( $options )
			->addArgument( $filesystem )
			->addTag( 'front_subscriber' );
		$this->getContainer()->share( 'minify_js_subscriber', 'WP_Rocket\Engine\Optimization\Minify\JS\Subscriber' )
			->addArgument( $options )
			->addArgument( $filesystem )
			->addTag( 'front_subscriber' );
		$this->getContainer()->share( 'ie_conditionals_subscriber', 'WP_Rocket\Engine\Optimization\IEConditionalSubscriber' )
			->addTag( 'front_subscriber' );
	}
}
