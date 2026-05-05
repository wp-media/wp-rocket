<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Drivers\RocketCDNFree;

use Mockery;
use WP_Rocket\Engine\CDN\Drivers\RocketCDNFree;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\RocketCDNFree::should_rewrite_url
 * @group  CDN
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var RocketCDN|\Mockery\MockInterface
	 */
	private $query;

	/**
	 * @var RocketCDNFree
	 */
	private $driver;

	public function setUp(): void {
		parent::setUp();
		$this->query  = Mockery::mock( RocketCDN::class );
		$this->driver = new RocketCDNFree( $this->query );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedResult( array $config, bool $expected ) {
		$this->query->shouldReceive( 'is_url_found' )
			->once()
			->with( $config['url'] )
			->andReturn( $config['is_found'] );

		$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
	}
}