<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Drivers\RocketCDNPaid;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Drivers\RocketCDNPaid;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\RocketCDNPaid::should_rewrite_url
 * @group  CDN
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var Options_Data|\Mockery\MockInterface
	 */
	private $options;

	/**
	 * @var RocketCDNPaid
	 */
	private $driver;

	public function setUp(): void {
		parent::setUp();

		Functions\when( 'untrailingslashit' )->alias(
			function ( $url ) {
				return rtrim( $url, '/' );
			}
		);

		$this->options = Mockery::mock( Options_Data::class );
		$this->driver  = new RocketCDNPaid( $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedResult( array $config, bool $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'cdn_reject_pages', [] )
			->andReturn( $config['excluded_pages'] );

		$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
	}
}