<?php
namespace WP_Rocket\Tests\Unit\inc\classes\preload\Process;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Preload\Process;

/**
 * @covers \WP_Rocket\Preload\Process::get_item_user_agent
 * @group Preload
 */
class Test_GetItemUserAgent extends TestCase {
	private $user_agent = 'WP Rocket/Preload';
	private $prefix     = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

	public function testShouldReturnMobileUaWhenMobileItem() {
		$expected = $this->prefix . ' ' . $this->user_agent;
		$stub     = $this->getMockForAbstractClass( Process::class );

		$this->assertSame( $expected, $stub->get_item_user_agent( [ 'mobile' => 1 ] ) );
	}

	public function testShouldNotReturnMobileUaWhenNotMobileItem() {
		$expected = $this->user_agent;
		$stub     = $this->getMockForAbstractClass( Process::class );

		$this->assertSame( $expected, $stub->get_item_user_agent( [ 'mobile' => 0 ] ) );
	}
}
