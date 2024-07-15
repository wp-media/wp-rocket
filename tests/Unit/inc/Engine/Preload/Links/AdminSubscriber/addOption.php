<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Links\AdminSubscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Links\AdminSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Links\AdminSubscriber::add_option
 *
 * @group  PreloadLinks
 */
class Test_AddOption extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddOption( $options, $expected ) {
		$subscriber = new AdminSubscriber( Mockery::mock( Options_Data::class ) );

		$result = $subscriber->add_option( $options );

		$this->assertArrayHasKey( 'preload_links', $result );
		$this->assertSame( $expected['preload_links'], $result['preload_links'] );
	}
}
