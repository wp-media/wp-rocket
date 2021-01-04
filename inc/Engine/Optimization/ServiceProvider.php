<?php
namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\Engine\Container\ServiceProvider\AbstractServiceProvider;

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
		'delay_js_html',
		'delay_js_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @since  3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register() {
		$options    = $this->getContainer()->get( 'options' );
		$filesystem = rocket_direct_filesystem();

		$this->getContainer()->add( 'config', 'WP_Rocket\Buffer\Config' )
			->withArgument( [ 'config_dir_path' => rocket_get_constant( 'WP_ROCKET_CONFIG_PATH' ) ] );
		$this->getContainer()->add( 'tests', 'WP_Rocket\Buffer\Tests' )
			->withArgument( $this->getContainer()->get( 'config' ) );
		$this->getContainer()->add( 'buffer_optimization', 'WP_Rocket\Buffer\Optimization' )
			->withArgument( $this->getContainer()->get( 'tests' ) );
		$this->getContainer()->share( 'buffer_subscriber', 'WP_Rocket\Subscriber\Optimization\Buffer_Subscriber' )
			->withArgument( $this->getContainer()->get( 'buffer_optimization' ) );
		$this->getContainer()->share( 'cache_dynamic_resource', 'WP_Rocket\Engine\Optimization\CacheDynamicResource' )
			->withArgument( $options )
			->withArgument( WP_ROCKET_CACHE_BUSTING_PATH )
			->withArgument( WP_ROCKET_CACHE_BUSTING_URL );
		$this->getContainer()->add( 'optimize_google_fonts', 'WP_Rocket\Engine\Optimization\GoogleFonts\Combine' );
		$this->getContainer()->add( 'optimize_google_fonts_v2', 'WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2' );
		$this->getContainer()->share( 'combine_google_fonts_subscriber', 'WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber' )
			->withArgument( $this->getContainer()->get( 'optimize_google_fonts' ) )
			->withArgument( $this->getContainer()->get( 'optimize_google_fonts_v2' ) )
			->withArgument( $options );
		$this->getContainer()->share( 'minify_css_subscriber', 'WP_Rocket\Engine\Optimization\Minify\CSS\Subscriber' )
			->withArgument( $options )
			->withArgument( $filesystem );
		$this->getContainer()->share( 'minify_js_subscriber', 'WP_Rocket\Engine\Optimization\Minify\JS\Subscriber' )
			->withArgument( $options )
			->withArgument( $filesystem );
		$this->getContainer()->share( 'ie_conditionals_subscriber', 'WP_Rocket\Engine\Optimization\IEConditionalSubscriber' );

		$this->getContainer()->add( 'delay_js_html', 'WP_Rocket\Engine\Optimization\DelayJS\HTML' )
			->withArgument( $options );
		$this->getContainer()->share( 'delay_js_subscriber', 'WP_Rocket\Engine\Optimization\DelayJS\Subscriber' )
			->withArgument( $this->getContainer()->get( 'delay_js_html' ) )
			->withArgument( $filesystem );
	}
}
