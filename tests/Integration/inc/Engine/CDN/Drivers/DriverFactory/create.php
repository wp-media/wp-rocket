<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Drivers\DriverFactory;

use WP_Rocket\Engine\CDN\Drivers\Custom;
use WP_Rocket\Engine\CDN\Drivers\Disabled;
use WP_Rocket\Engine\CDN\Drivers\DriverFactory;
use WP_Rocket\Engine\CDN\Drivers\DriverInterface;
use WP_Rocket\Engine\CDN\Drivers\RocketCDNFree;
use WP_Rocket\Engine\CDN\Drivers\RocketCDNPaid;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\DriverFactory::create
 * @group  CDN
 * @group  AdminOnly
 */
class Test_Create extends TestCase {

	/**
	 * @var DriverFactory
	 */
	private $driver_factory;

	/**
	 * Maps fixture expected strings to concrete driver classes.
	 *
	 * @var array<string, class-string<DriverInterface>>
	 */
	private const DRIVER_CLASS_MAP = [
		'cdn_driver_free'     => RocketCDNFree::class,
		'cdn_driver_paid'     => RocketCDNPaid::class,
		'cdn_driver_byocdn'   => Custom::class,
		'cdn_driver_disabled' => Disabled::class,
	];

	public function set_up() {
		parent::set_up();

		$container            = apply_filters( 'rocket_container', null );
		$this->driver_factory = $container->get( 'cdn_driver_factory' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, string $expected ): void {
		// DriverFactory resolves from Context::get_cdn_state() at this point in the series;
		// setting cdn_state directly is sufficient to exercise every branch.
		$this->mergeExistingSettingsAndUpdate( [ 'cdn_state' => $config['effective_cdn_state'] ] );

		$driver = $this->driver_factory->create();

		$this->assertInstanceOf( self::DRIVER_CLASS_MAP[ $expected ], $driver );
	}
}
