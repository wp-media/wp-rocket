<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\FullProcess;

use WP_Rocket\Tests\Integration\inc\Engine\Preload\PreloadTestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\FullProcess::get_item_user_agent
 * @group  Preload
 */
class Test_GetItemUserAgent extends PreloadTestCase {

	public function testShouldBeDetectedAsMobileByWordPressWhenMobileItem() {
		$previous_ua                = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$_SERVER['HTTP_USER_AGENT'] = $this->process->get_item_user_agent( [ 'mobile' => 1 ] );

		$this->assertTrue( wp_is_mobile() );

		$_SERVER['HTTP_USER_AGENT'] = $previous_ua;
	}

	public function testShouldNotBeDetectedAsMobileByWordPressWhenNotMobileItem() {
		$previous_ua                = ! empty( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
		$_SERVER['HTTP_USER_AGENT'] = $this->process->get_item_user_agent( [ 'mobile' => 0 ] );

		$this->assertFalse( wp_is_mobile() );

		$_SERVER['HTTP_USER_AGENT'] = $previous_ua;
	}
}
