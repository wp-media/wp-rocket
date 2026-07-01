<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Drivers\RocketCDNPaid;

use WP_Rocket\Engine\CDN\Drivers\RocketCDNPaid;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\RocketCDNPaid::should_rewrite_url
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var RocketCDNPaid
	 */
	private $driver;

	/**
	 * Excluded pages value returned by the pre_get_rocket_option_cdn_reject_pages filter.
	 *
	 * @var array
	 */
	private $cdn_reject_pages_value = [];

	public function set_up() {
		parent::set_up();

		$container    = apply_filters( 'rocket_container', null );
		$this->driver = $container->get( 'cdn_driver_paid' );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cdn_reject_pages', [ $this, 'cdn_reject_pages_cb' ] );
		$this->cdn_reject_pages_value = [];

		parent::tear_down();
	}

	public function cdn_reject_pages_cb(): array {
		return $this->cdn_reject_pages_value;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		if ( ! empty( $config['excluded_pages'] ) ) {
			$this->cdn_reject_pages_value = $config['excluded_pages'];
			add_filter( 'pre_get_rocket_option_cdn_reject_pages', [ $this, 'cdn_reject_pages_cb' ] );
		}

		$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
	}
}
