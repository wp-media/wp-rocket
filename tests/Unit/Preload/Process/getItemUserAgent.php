<?php
namespace WP_Rocket\Tests\Unit\Preload\Process;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Preload\Process;

/**
 * @covers \WP_Rocket\Preload\Process::get_item_user_agent
 * @group Preload
 */
class Test_getItemUserAgent extends TestCase {

	public function testShouldReturnMobileUaWhenMobileItem() {
		$stub = $this->getMockForAbstractClass( Process::class );

		$this->assertContains( 'iPhone', $stub->get_item_user_agent( [ 'mobile' => 1 ] ) );
	}

	public function testShouldNotReturnMobileUaWhenNotMobileItem() {
		$stub = $this->getMockForAbstractClass( Process::class );

		$this->assertNotContains( 'iPhone', $stub->get_item_user_agent( [] ) );
		$this->assertNotContains( 'iPhone', $stub->get_item_user_agent( [ 'mobile' => 0 ] ) );
	}
}
