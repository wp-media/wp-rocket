<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Drivers\RocketCDNFree;

use Mockery;
use PHPUnit\Framework\MockObject\MockObject;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Drivers\RocketCDNFree;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\RocketCDNFree::should_rewrite_url
 * @group  CDN
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var Mockery\MockInterface|CDN
	 */
	private $cdn;

	/**
	 * @var RocketCDN|MockObject
	 */
	private $query;

	/**
	 * @var Mockery\MockInterface|Context
	 */
	private $context;

	/**
	 * @var RocketCDNFree
	 */
	private $driver;

	public function setUp(): void {
		parent::setUp();
		$this->cdn     = Mockery::mock( CDN::class );
		$this->query   = $this->createMock( RocketCDN::class );
		$this->context = Mockery::mock( Context::class );
		$this->driver  = new RocketCDNFree( $this->cdn, $this->query, $this->context );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedResult( array $config, bool $expected ) {
		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->andReturn( $config['cdn_urls'] );

		if ( empty( $config['cdn_urls'] ) ) {
			$this->context->shouldNotReceive( 'is_forced_off' );
			$this->query->expects( $this->never() )->method( 'is_url_found' );

			$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
			return;
		}

		$this->context->shouldReceive( 'is_forced_off' )
			->andReturn( $config['is_forced_off'] ?? false );

		if ( $config['is_forced_off'] ?? false ) {
			$this->query->expects( $this->never() )->method( 'is_url_found' );
		} else {
			$this->query->method( 'is_url_found' )
				->with( $config['url'] )
				->willReturn( $config['is_found'] ?? false );
		}

		$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
	}

	public function testShouldReturnFalseAndNeverQueryWhenNoHostname() {
		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->once()
			->andReturn( [] );

		$this->context->shouldNotReceive( 'is_forced_off' );
		$this->query->expects( $this->never() )->method( 'is_url_found' );

		$this->assertFalse( $this->driver->should_rewrite_url( 'https://example.com/page/' ) );
	}

	public function testShouldCacheIndependentResultsPerUrl() {
		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->once()
			->andReturn( [ 'cdn.example.com' ] );

		$this->context->shouldReceive( 'is_forced_off' )
			->andReturn( false );

		$this->query->expects( $this->exactly( 2 ) )
			->method( 'is_url_found' )
			->willReturnMap(
				[
					[ 'https://example.com/page-a/', true ],
					[ 'https://example.com/page-b/', false ],
				]
			);

		$this->assertTrue( $this->driver->should_rewrite_url( 'https://example.com/page-a/' ) );
		$this->assertFalse( $this->driver->should_rewrite_url( 'https://example.com/page-b/' ) );
		// Repeated calls with the same URLs must not hit the query again.
		$this->assertTrue( $this->driver->should_rewrite_url( 'https://example.com/page-a/' ) );
		$this->assertFalse( $this->driver->should_rewrite_url( 'https://example.com/page-b/' ) );
	}
}
