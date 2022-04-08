<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Optimization\BufferSubscriber;

use WP_Rocket\Buffer\Optimization;
use WP_Rocket\Subscriber\Optimization\Buffer_Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Buffer_Subscriber::redirect_canonical
 * @group Subscriber
 */
class Test_RedirectCanonical extends TestCase
{
	protected $subscriber;
	protected $optimizer;

	public function setUp(): void
	{
		$this->optimizer = \Mockery::mock(Optimization::class);
		$this->subscriber = new Buffer_Subscriber($this->optimizer);
		parent::setUp();
	}

	public function testShouldCallRedirect() {
		Functions\expect('redirect_canonical')->with();
		$this->subscriber->redirect_canonical();
	}

}
