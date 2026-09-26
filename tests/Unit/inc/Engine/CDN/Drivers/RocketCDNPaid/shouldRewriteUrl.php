<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Drivers\RocketCDNPaid;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Drivers\RocketCDNPaid;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\RocketCDNPaid::should_rewrite_url
 * @group  CDN
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var Mockery\MockInterface|CDN
	 */
	private $cdn;

	/**
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * @var Mockery\MockInterface|Context
	 */
	private $context;

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

		$this->cdn     = Mockery::mock( CDN::class );
		$this->options = Mockery::mock( Options_Data::class );
		$this->context = Mockery::mock( Context::class );
		$this->driver  = new RocketCDNPaid( $this->cdn, $this->options, $this->context );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedResult( array $config, bool $expected ) {
		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->andReturn( $config['cdn_urls'] );

		if ( empty( $config['cdn_urls'] ) ) {
			$this->context->shouldNotReceive( 'is_forced_off' );
			$this->options->shouldNotReceive( 'get' );
			$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
			return;
		}

		$this->context->shouldReceive( 'is_forced_off' )
			->andReturn( $config['is_forced_off'] ?? false );

		if ( $config['is_forced_off'] ?? false ) {
			$this->options->shouldNotReceive( 'get' );
		} else {
			$this->options->shouldReceive( 'get' )
				->with( 'cdn_reject_pages', [] )
				->andReturn( $config['excluded_pages'] ?? [] );
		}

		$this->assertSame( $expected, $this->driver->should_rewrite_url( $config['url'] ) );
	}

	public function testShouldReturnFalseAndNeverCheckExclusionsWhenNoHostname() {
		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->once()
			->andReturn( [] );

		$this->context->shouldNotReceive( 'is_forced_off' );
		$this->options->shouldNotReceive( 'get' );

		$this->assertFalse( $this->driver->should_rewrite_url( 'https://example.com/page/' ) );
	}

	public function testShouldCacheIndependentResultsPerUrl() {
		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->once()
			->andReturn( [ 'cdn.example.com' ] );

		$this->context->shouldReceive( 'is_forced_off' )
			->andReturn( false );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn_reject_pages', [] )
			->andReturn( [ '/shop' ] );

		$this->assertFalse( $this->driver->should_rewrite_url( 'https://example.com/shop' ) );
		$this->assertTrue( $this->driver->should_rewrite_url( 'https://example.com/other' ) );
		// Repeated calls with the same URLs must return the same cached results.
		$this->assertFalse( $this->driver->should_rewrite_url( 'https://example.com/shop' ) );
		$this->assertTrue( $this->driver->should_rewrite_url( 'https://example.com/other' ) );
	}
}
