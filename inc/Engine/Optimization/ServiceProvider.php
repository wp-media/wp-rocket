<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization;

use WP_Rocket\Dependencies\League\Container\Argument\Literal\StringArgument;
use WP_Rocket\Dependencies\League\Container\ServiceProvider\AbstractServiceProvider;
use WP_Rocket\Engine\Optimization\Buffer\Optimization;
use WP_Rocket\Engine\Optimization\Buffer\Subscriber as BufferSubscriber;
use WP_Rocket\Engine\Optimization\GoogleFonts\{Combine, CombineV2, Subscriber};

/**
 * Service provider for the WP Rocket optimizations
 */
class ServiceProvider extends AbstractServiceProvider {
	/**
	 * Array of services provided by this service provider
	 *
	 * @var array
	 */
	protected $provides = [
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
	 * Check if the service provider provides a specific service.
	 *
	 * @param string $id The id of the service.
	 *
	 * @return bool
	 */
	public function provides( string $id ): bool {
		return in_array( $id, $this->provides, true );
	}

	/**
	 * Registers items with the container
	 *
	 * @return void
	 */
	public function register(): void {
		$filesystem = rocket_direct_filesystem();

		$this->getContainer()->add( 'buffer_optimization', Optimization::class )
			->addArgument( 'tests' );
		$this->getContainer()->addShared( 'buffer_subscriber', BufferSubscriber::class )
			->addArgument( 'buffer_optimization' );
		$this->getContainer()->addShared( 'cache_dynamic_resource', CacheDynamicResource::class )
			->addArguments(
				[
					'options',
					new StringArgument( rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_PATH', '' ) ),
					new StringArgument( rocket_get_constant( 'WP_ROCKET_CACHE_BUSTING_URL', '' ) ),
				]
			);
		$this->getContainer()->add( 'optimize_google_fonts', Combine::class );
		$this->getContainer()->add( 'optimize_google_fonts_v2', CombineV2::class );
		$this->getContainer()->addShared( 'combine_google_fonts_subscriber', Subscriber::class )
			->addArguments(
				[
					'optimize_google_fonts',
					'optimize_google_fonts_v2',
					'options',
				]
				);
		$this->getContainer()->addShared( 'minify_css_subscriber', Minify\CSS\Subscriber::class )
			->addArguments(
				[
					'options',
					$filesystem,
				]
			);
		$this->getContainer()->addShared( 'minify_js_subscriber', Minify\JS\Subscriber::class )
			->addArguments(
				[
					'options',
					$filesystem,
				]
			);
		$this->getContainer()->addShared( 'ie_conditionals_subscriber', IEConditionalSubscriber::class );
	}
}
