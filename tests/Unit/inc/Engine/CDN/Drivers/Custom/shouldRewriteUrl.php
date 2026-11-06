<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Drivers\Custom;

use WP_Rocket\Engine\CDN\Drivers\Custom;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\Custom::should_rewrite_url
 * @group  CDN
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var Custom
	 */
	private $custom;

	public function setUp(): void {
		parent::setUp();
		$this->custom = new Custom();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAlwaysReturnTrue( array $config, bool $expected ) {
		$this->assertSame( $expected, $this->custom->should_rewrite_url( $config['url'] ) );
	}
}