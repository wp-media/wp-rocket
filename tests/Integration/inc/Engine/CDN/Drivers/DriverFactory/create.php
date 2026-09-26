<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Drivers\DriverFactory;

use WP_Rocket\Engine\CDN\Context;
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
	 * @var \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager
	 */
	private $options_manager;

	/**
	 * Value returned by the pre_get_rocket_option_cdn filter.
	 *
	 * @var int
	 */
	private $cdn_enabled = 1;

	/**
	 * Value returned by the pre_get_rocket_option_cdn_type filter.
	 *
	 * @var string
	 */
	private $cdn_type_value = Context::ROCKETCDN_TYPE;

	/**
	 * Value returned by the pre_get_rocket_option_cdn_state filter.
	 *
	 * @var string
	 */
	private $cdn_state_value = Context::CDN_STATE_NOTHING;

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

		$container             = apply_filters( 'rocket_container', null );
		$this->driver_factory  = $container->get( 'cdn_driver_factory' );
		$this->options_manager = $container->get( 'rocketcdn_options_manager' );

		delete_transient( 'rocketcdn_status' );
		// Clears any existing token via the public API rather than a direct option call.
		$this->options_manager->save_token( '' );

		// Options_Data snapshots wp_rocket_settings at container-boot time, so a direct
		// update_option() call mid-test would never be observed — these filters are live
		// (Options_Data::get() re-applies pre_get_rocket_option_{key} on every call).
		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'filter_cdn' ] );
		add_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'filter_cdn_type' ] );
		add_filter( 'pre_get_rocket_option_cdn_state', [ $this, 'filter_cdn_state' ] );
	}

	public function tear_down() {
		delete_transient( 'rocketcdn_status' );
		$this->options_manager->save_token( '' );

		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'filter_cdn' ] );
		remove_filter( 'pre_get_rocket_option_cdn_type', [ $this, 'filter_cdn_type' ] );
		remove_filter( 'pre_get_rocket_option_cdn_state', [ $this, 'filter_cdn_state' ] );

		parent::tear_down();
	}

	/**
	 * @return int
	 */
	public function filter_cdn(): int {
		return $this->cdn_enabled;
	}

	/**
	 * @return string
	 */
	public function filter_cdn_type(): string {
		return $this->cdn_type_value;
	}

	/**
	 * @return string
	 */
	public function filter_cdn_state(): string {
		return $this->cdn_state_value;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, string $expected ): void {
		$this->setup_driver_state( $config['effective_cdn_state'] );

		$driver = $this->driver_factory->create();

		$this->assertInstanceOf( self::DRIVER_CLASS_MAP[ $expected ], $driver );
	}

	/**
	 * Configures the live filters so that Context::get_effective_cdn_state() resolves to
	 * the requested state.
	 *
	 * @param string $effective_cdn_state One of the CDN_STATE_NOTHING, BYOCDN_TYPE, or
	 *                                    ROCKETCDN_FREE_TYPE/ROCKETCDN_PAID_TYPE constants,
	 *                                    or an arbitrary unrecognized value.
	 * @return void
	 */
	private function setup_driver_state( string $effective_cdn_state ): void {
		switch ( $effective_cdn_state ) {
			case Context::CDN_STATE_NOTHING:
				$this->cdn_enabled = 0;
				break;

			case Context::BYOCDN_TYPE:
				$this->cdn_enabled    = 1;
				$this->cdn_type_value = Context::BYOCDN_TYPE;
				break;

			case Context::ROCKETCDN_FREE_TYPE:
				$this->cdn_enabled     = 1;
				$this->cdn_type_value  = Context::ROCKETCDN_TYPE;
				$this->cdn_state_value = Context::ROCKETCDN_FREE_TYPE;
				// A genuine free-tier subscriber always has a token — see Context::get_effective_cdn_state().
				$this->options_manager->save_token( 'test-token' );
				break;

			case Context::ROCKETCDN_PAID_TYPE:
				$this->cdn_enabled     = 1;
				$this->cdn_type_value  = Context::ROCKETCDN_TYPE;
				$this->cdn_state_value = Context::ROCKETCDN_PAID_TYPE;
				break;

			default:
				// Unrecognized cdn_state: Context::get_cdn_state() itself sanitizes any value
				// outside its allow-list back to CDN_STATE_NOTHING, so this exercises the same
				// Disabled-driver outcome as the explicit "nothing" case above.
				$this->cdn_enabled     = 1;
				$this->cdn_type_value  = Context::ROCKETCDN_TYPE;
				$this->cdn_state_value = $effective_cdn_state;
				break;
		}
	}
}
