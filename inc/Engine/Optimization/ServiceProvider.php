<?php
namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\AbstractServiceProvider;
use WP_Rocket\Buffer\Config;
use WP_Rocket\Engine\Optimization\Buffer\Optimization;
use WP_Rocket\Buffer\Tests;
use WP_Rocket\Engine\Optimization\GoogleFonts\Combine;
use WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2;
use WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber;
use WP_Rocket\Engine\Optimization\Buffer\Subscriber as BufferSubscriber;

/**
 * Service provider for the WP Rocket optimizations
 *
 * @since  3.3
 * @since  3.6 Renamed and moved into this module.
 */
class ServiceProvider extends AbstractServiceProvider {
	public function get_license_subscribers(): array
	{
		return [
			$this->generate_container_id('buffer_subscriber'),
			$this->generate_container_id('combine_google_fonts_subscriber'),
			$this->generate_container_id('ie_conditionals_subscriber'),
			$this->generate_container_id('minify_css_subscriber'),
			$this->generate_container_id('minify_js_subscriber'),
			$this->generate_container_id('cache_dynamic_resource'),
		];
	}

	public function declare()
	{
		$filesystem = rocket_direct_filesystem();

		$this->register_service('config', function ($id) {
			$this->add( $id, Config::class )
				->addArgument( [ 'config_dir_path' => rocket_get_constant( 'WP_ROCKET_CONFIG_PATH' ) ] );
		});

		$this->register_service('tests', function ($id) {
			$this->add( $id, Tests::class )
				->addArgument( $this->get_internal( 'config' ) );
		});

		$this->register_service('buffer_optimization', function ($id) {
			$this->add( $id, Optimization::class )
				->addArgument( $this->get_internal( 'tests' ) );
		});

		$this->register_service('buffer_subscriber', function ($id) {
			$this->share( $id, BufferSubscriber::class )
				->addArgument( $this->get_internal( 'buffer_optimization' ) )
				->addTag( 'front_subscriber' );
		});

		$this->register_service('cache_dynamic_resource', function ($id) {
			$this->share( $id, CacheDynamicResource::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( WP_ROCKET_CACHE_BUSTING_PATH )
				->addArgument( WP_ROCKET_CACHE_BUSTING_URL )
				->addTag( 'front_subscriber' );
		});

		$this->register_service('optimize_google_fonts', function ($id) {
			$this->add( $id, Combine::class );

		});

		$this->register_service('optimize_google_fonts_v2', function ($id) {
			$this->add( $id, CombineV2::class );

		});

		$this->register_service('combine_google_fonts_subscriber', function ($id) {
			$this->share( $id, Subscriber::class )
				->addArgument( $this->get_internal( 'optimize_google_fonts' ) )
				->addArgument( $this->get_internal( 'optimize_google_fonts_v2' ) )
				->addArgument( $this->get_external( 'options' ) )
				->addTag( 'front_subscriber' );
		});

		$this->register_service('minify_css_subscriber', function ($id) use ($filesystem) {
			$this->share( $id, Minify\CSS\Subscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $filesystem )
				->addTag( 'front_subscriber' );
		});

		$this->register_service('minify_js_subscriber', function ($id) use ($filesystem) {
			$this->share( $id, Minify\JS\Subscriber::class )
				->addArgument( $this->get_external( 'options' ) )
				->addArgument( $filesystem )
				->addTag( 'front_subscriber' );
		});

		$this->register_service('ie_conditionals_subscriber', function ($id) {
			$this->share( $id, IEConditionalSubscriber::class )
				->addTag( 'front_subscriber' );
		});
	}
}
