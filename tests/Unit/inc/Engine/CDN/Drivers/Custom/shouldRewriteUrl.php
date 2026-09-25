<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Drivers\Custom;

use Mockery;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Drivers\Custom;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\Custom::should_rewrite_url
 * @group  CDN
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var Mockery\MockInterface|CDN
	 */
	private $cdn;

	/**
	 * @var Custom
	 */
	private $custom;

	public function setUp(): void {
		parent::setUp();
		$this->cdn    = Mockery::mock( CDN::class );
		$this->custom = new Custom( $this->cdn );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedResult( array $config, bool $expected ) {
		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->with( [ 'all', 'images', 'css_and_js', 'css', 'js' ] )
			->andReturn( $config['cdn_urls'] );

		$this->assertSame( $expected, $this->custom->should_rewrite_url( $config['url'] ) );
	}

	public function testShouldMemoizeHostnameCheckAcrossCalls() {
		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->once()
			->with( [ 'all', 'images', 'css_and_js', 'css', 'js' ] )
			->andReturn( [ 'cdn.example.com' ] );

		$this->assertTrue( $this->custom->should_rewrite_url( 'https://example.com/page/' ) );
		$this->assertTrue( $this->custom->should_rewrite_url( 'https://example.com/page/' ) );
	}
}
