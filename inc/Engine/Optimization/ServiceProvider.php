<?php
namespace WP_Rocket\Engine\Optimization;

use League\Container\ServiceProvider\AbstractServiceProvider;

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
		'minify_html_subscriber',
		'combine_google_fonts_subscriber',
		'minify_css_subscriber',
		'minify_js_subscriber',
		'dequeue_jquery_migrate_subscriber',
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
		$options = $this->getContainer()->get( 'options' );

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

		$this->getContainer()->share( 'minify_html_subscriber', 'WP_Rocket\Subscriber\Optimization\Minify_HTML_Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'combine_google_fonts_subscriber', 'WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'minify_css_subscriber', 'WP_Rocket\Engine\Optimization\Minify\CSS\Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'minify_js_subscriber', 'WP_Rocket\Engine\Optimization\Minify\JS\Subscriber' )
			->withArgument( $options );
		$this->getContainer()->share( 'dequeue_jquery_migrate_subscriber', 'WP_Rocket\Subscriber\Optimization\Dequeue_JQuery_Migrate_Subscriber' )
			->withArgument( $options );

		$this->getContainer()->share( 'ie_conditionals_subscriber', 'WP_Rocket\Engine\Optimization\IEConditionalSubscriber' );
	}
}
