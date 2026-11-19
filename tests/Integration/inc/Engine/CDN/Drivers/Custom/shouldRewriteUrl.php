<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Drivers\Custom;

use WP_Rocket\Engine\CDN\Drivers\Custom;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\Custom::should_rewrite_url
 * @group  CDN
 * @group  AdminOnly
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var Custom
	 */
	private $driver;

	public function set_up() {
		parent::set_up();

		$container    = apply_filters( 'rocket_container', null );
		$this->driver = $container->get( 'cdn_driver_byocdn' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
	}
}
