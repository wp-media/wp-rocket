<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\AdminSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::enqueue_admin_edit_script
 * @uses   ::rocket_get_constant
 *
 * @group  CriticalPath
 */
class Test_EnqueueAdminEditScript extends TestCase {
	use GenerateTrait;

	protected $path_to_test_data = '/inc/Engine/CriticalPath/AdminSubscriber/enqueueAdminEditScript.php';

	protected static $mockCommonWpFunctionsInSetUp = true;

	public function setUp() {
		parent::setUp();

		$this->setUpMocks();
	}

	protected function tearDown() {
		unset( $GLOBALS['post'] );
		unset( $GLOBALS['pagenow'] );
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEnqueueAdminScript( $config, $expected ) {
		if ( in_array( $config['page'], [ 'edit.php', 'post.php' ], true ) ) {
			$this->setUpTest( $config );
		}
		$config['options']['async_css_mobile'] = isset( $config['options']['async_css_mobile'] ) ? $config['options']['async_css_mobile'] : 0;
		$GLOBALS['pagenow']                    = $config['pagenow'];

		Functions\when( 'wp_create_nonce' )->justReturn( 'wp_rest_nonce' );
		Functions\when( 'rest_url' )->justReturn( 'http://example.org/wp-rocket/v1/cpcss/post/' . $config['post']->ID );
		$this->options->shouldReceive( 'get' )->with( 'async_css_mobile', 0 )->andReturn( $config['options']['async_css_mobile'] );

		if ( $expected ) {
			Functions\expect( 'wp_enqueue_script' )->once();
			Functions\expect( 'wp_localize_script' )->once();
		} else {
			Functions\expect( 'wp_enqueue_script' )->never();
			Functions\expect( 'wp_localize_script' )->never();
		}

		$this->subscriber->enqueue_admin_edit_script( $config['page'] );
	}
}
