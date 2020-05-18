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
		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEnqueueAdminScript( $config, $expected ) {
		if ( in_array( $config['page'], [ 'edit.php', 'post.php' ], true ) ) {
			$this->setUpTest( $config );
		}

		if ( $expected ) {
			Functions\expect( 'wp_enqueue_script' )->once();
		} else {
			Functions\expect( 'wp_enqueue_script' )->never();
		}

		$this->subscriber->enqueue_admin_edit_script( $config['page'] );
	}
}
