<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Links\AdminSubscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Links\AdminSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Links\AdminSubscriber::add_incompatible_plugins
 *
 * @group  PreloadLinks
 */
class Test_AddIncompatiblePlugins extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $option, $plugins, $expected ) {
		$options    = Mockery::mock( Options_Data::class );
		$subscriber = new AdminSubscriber( $options );

		$options->shouldReceive( 'get' )
			->once()
			->with( 'preload_links', 0 )
			->andReturn( $option );

		$this->assertSame( $expected, $subscriber->add_incompatible_plugins( $plugins ) );
	}
}
