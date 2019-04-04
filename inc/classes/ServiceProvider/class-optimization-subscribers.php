<?php
namespace WP_Rocket\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;

/**
 * Service provider for the WP Rocket optimizations
 *
 * @since 3.3
 * @author Remy Perona
 */
class Optimization_Subscribers extends AbstractServiceProvider {

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
		'cdn_favicons',
		'remove_query_string',
		'ie_conditionals_subscriber',
		'minify_html_subscriber',
		'combine_google_fonts_subscriber',
		'minify_css_subscriber',
		'minify_js_subscriber',
		'cache_dynamic_resource_subscriber',
		'cdn_favicons_subscriber',
		'remove_query_string_subscriber',
	];

	/**
	 * Registers the option array in the container
	 *
	 * @since 3.3
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function register() {
		$this->getContainer()->add( 'config', 'WP_Rocket\Buffer\Config' )
			->withArgument( [ 'config_dir_path' => WP_ROCKET_CONFIG_PATH ] );
		$this->getContainer()->add( 'tests', 'WP_Rocket\Buffer\Tests' )
			->withArgument( $this->getContainer()->get( 'config' ) );
		$this->getContainer()->add( 'buffer_optimization', 'WP_Rocket\Buffer\Optimization' )
			->withArgument( $this->getContainer()->get( 'tests' ) );
		$this->getContainer()->add( 'buffer_subscriber', 'WP_Rocket\Subscriber\Optimization\Buffer_Subscriber' )
			->withArgument( $this->getContainer()->get( 'buffer_optimization' ) );
		$this->getContainer()->add( 'cache_dynamic_resource', 'WP_Rocket\Optimization\Cache_Dynamic_Resource' )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( WP_ROCKET_CACHE_BUSTING_PATH )
			->withArgument( WP_ROCKET_CACHE_BUSTING_URL );
		$this->getContainer()->add( 'cdn_favicons', 'WP_Rocket\Optimization\CDN_Favicons' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'remove_query_string', 'WP_Rocket\Optimization\Remove_Query_String' )
			->withArgument( $this->getContainer()->get( 'options' ) )
			->withArgument( WP_ROCKET_CACHE_BUSTING_PATH )
			->withArgument( WP_ROCKET_CACHE_BUSTING_URL );
		$this->getContainer()->add( 'ie_conditionals_subscriber', 'WP_Rocket\Subscriber\Optimization\IE_Conditionals_Subscriber' );
		$this->getContainer()->add( 'minify_html_subscriber', 'WP_Rocket\Subscriber\Optimization\Minify_HTML_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'combine_google_fonts_subscriber', 'WP_Rocket\Subscriber\Optimization\Combine_Google_Fonts_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'minify_css_subscriber', 'WP_Rocket\Subscriber\Optimization\Minify_CSS_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'minify_js_subscriber', 'WP_Rocket\Subscriber\Optimization\Minify_JS_Subscriber' )
			->withArgument( $this->getContainer()->get( 'options' ) );
		$this->getContainer()->add( 'cache_dynamic_resource_subscriber', 'WP_Rocket\Subscriber\Optimization\Cache_Dynamic_Resource_Subscriber' )
			->withArgument( $this->getContainer()->get( 'cache_dynamic_resource' ) );
		$this->getContainer()->add( 'cdn_favicons_subscriber', 'WP_Rocket\Subscriber\Optimization\CDN_Favicons_Subscriber' )
			->withArgument( $this->getContainer()->get( 'cdn_favicons' ) );
		$this->getContainer()->add( 'remove_query_string_subscriber', 'WP_Rocket\Subscriber\Optimization\Remove_Query_String_Subscriber' )
			->withArgument( $this->getContainer()->get( 'remove_query_string' ) );
	}
}
