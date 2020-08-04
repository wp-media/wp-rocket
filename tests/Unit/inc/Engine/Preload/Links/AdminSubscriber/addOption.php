<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Links\AdminSubscriber;

use WP_Rocket\Engine\Preload\Links\AdminSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Links\AdminSubscriber::add_option
 *
 * @group  PreloadLinks
 */
class Test_AddOption extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddOption( $options, $expected ) {
		$subscriber = new AdminSubscriber();

		$result = $subscriber->add_option( $options );

		$this->assertArrayHasKey( 'preload_links', $result );
		$this->assertSame( $expected['preload_links'], $result['preload_links'] );
	}
}
