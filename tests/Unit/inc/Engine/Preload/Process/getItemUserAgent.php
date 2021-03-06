<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Process;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Preload\PartialProcess;

/**
 * @covers \WP_Rocket\Engine\Preload\AbstractProcess::get_item_user_agent
 * @group Preload
 */
class Test_GetItemUserAgent extends TestCase {
	private $process;
	private $user_agent = 'WP Rocket/Preload';
	private $prefix     = 'Mozilla/5.0 (iPhone; CPU iPhone OS 9_1 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13B143 Safari/601.1';

	public function setUp() : void {
		parent::setUp();

		$this->process = new PartialProcess();
	}

	public function testShouldReturnMobileUaWhenMobileItem() {
		$expected = $this->prefix . ' ' . $this->user_agent;

		$this->assertSame( $expected, $this->process->get_item_user_agent( [ 'mobile' => 1 ] ) );
	}

	public function testShouldNotReturnMobileUaWhenNotMobileItem() {
		$expected = $this->user_agent;

		$this->assertSame( $expected, $this->process->get_item_user_agent( [ 'mobile' => 0 ] ) );
	}
}
