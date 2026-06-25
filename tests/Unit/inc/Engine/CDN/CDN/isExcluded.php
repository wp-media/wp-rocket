<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;

/**
 * Test class covering \WP_Rocket\Engine\CDN\CDN::is_excluded
 *
 * @group CDN
 */
class Test_IsExcluded extends TestCase {

	private $cdn;

	public function setUp(): void {
		parent::setUp();

		$options   = Mockery::mock( Options_Data::class );
		$this->cdn = new CDN( $options );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( string $url, bool $expected ): void {
		$this->assertSame( $expected, $this->cdn->is_excluded( $url ) );
	}

	public function providerTestData(): array {
		return $this->getTestData( __DIR__, 'isExcluded' );
	}
}
