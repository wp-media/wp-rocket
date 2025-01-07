<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Links\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Links\AdminSubscriber::add_option
 *
 * @group  AdminOnly
 * @group  PreloadLinks
 */
class Test_AddOption extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddOption( $options, $expected ) {
		$result = apply_filters( 'rocket_first_install_options', $options );

		$this->assertArrayHasKey( 'preload_links', $result );
		$this->assertSame( $expected['preload_links'], $result['preload_links'] );
	}
}
