<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Drivers\DriverFactory;

use WP_Rocket\Engine\CDN\Drivers\Custom;
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
	 * CDN type value returned by the pre_get_rocket_option_cdn_type filter.
	 *
	 * @var string
	 */
	private $cdn_type_value = 'rocketcdn';

	/**
	 * Maps fixture expected strings to concrete driver classes.
	 *
	 * @var array<string, class-string<DriverInterface>>
	 */
	private const DRIVER_CLASS_MAP = [
		'cdn_driver_free'   => RocketCDNFree::class,
		'cdn_driver_paid'   => RocketCDNPaid::class,
		'cdn_driver_byocdn' => Custom::class,
	];

	public function set_up() {
		parent::set_up();

		$container            = apply_filters( 'rocket_container', null );
		$this->driver_factory = $container->get( 'cdn_driver_factory' );

		delete_transient( 'rocketcdn_status' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type_cb' ] );
		$this->cdn_type_value = 'rocketcdn';

		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	public function cdn_type_cb(): string {
		return $this->cdn_type_value;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, ?string $expected ): void {
		$this->setup_driver_state( $config['active_driver'] );

		$driver = $this->driver_factory->create();

		if ( null === $expected ) {
			$this->assertNull( $driver );
			return;
		}

		$this->assertInstanceOf( self::DRIVER_CLASS_MAP[ $expected ], $driver );
	}

	/**
	 * Sets up options and transient so that Context::get_driver() returns the desired driver type.
	 *
	 * @param string $active_driver One of: rocketcdn_free, rocketcdn_paid, byocdn, unknown_driver.
	 * @return void
	 */
	private function setup_driver_state( string $active_driver ): void {
		switch ( $active_driver ) {
			case 'rocketcdn_free':
				$this->cdn_type_value = 'rocketcdn';
				add_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type_cb' ] );
				set_transient(
					'rocketcdn_status',
					[
						'subscription_status' => 'running',
						'plan_type'           => 'free',
						'status_code'         => 200,
						'cdn_url'             => 'https://test.delivery.rocketcdn.me',
					],
					HOUR_IN_SECONDS
				);
				break;

			case 'rocketcdn_paid':
				$this->cdn_type_value = 'rocketcdn';
				add_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type_cb' ] );
				set_transient(
					'rocketcdn_status',
					[
						'subscription_status' => 'running',
						'plan_type'           => 'paid',
						'status_code'         => 200,
						'cdn_url'             => 'https://test.delivery.rocketcdn.me',
					],
					HOUR_IN_SECONDS
				);
				break;

			case 'byocdn':
				$this->cdn_type_value = 'byocdn';
				add_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type_cb' ] );
				break;

			default:
				// unknown_driver: cancelled subscription → Context returns 'rocketcdn' → factory returns null.
				$this->cdn_type_value = 'rocketcdn';
				add_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'cdn_type_cb' ] );
				set_transient(
					'rocketcdn_status',
					[
						'subscription_status' => 'cancelled',
						'plan_type'           => 'free',
						'status_code'         => 200,
						'cdn_url'             => '',
					],
					HOUR_IN_SECONDS
				);
				break;
		}
	}
}
