<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Drivers\Disabled;

use WP_Rocket\Engine\CDN\Drivers\Disabled;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Drivers\Disabled::should_rewrite_url
 * @group  CDN
 */
class Test_ShouldRewriteUrl extends TestCase {

	/**
	 * @var Disabled
	 */
	private $disabled;

	public function setUp(): void {
		parent::setUp();
		$this->disabled = new Disabled();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAlwaysReturnFalse( array $config, bool $expected ) {
		$this->assertSame( $expected, $this->disabled->should_rewrite_url( $config['url'] ) );
	}
}
