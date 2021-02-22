<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Htaccess\AdminSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Htaccess\AdminSubscriber::flush_htaccess
 *
 * @group admin
 * @group AdminOnly
 */
class Test_FlushHtaccess extends FilesystemTestCase {

	protected $path_to_test_data = '/inc/Engine/Htaccess/AdminSubscriber/flushHtaccess.php';

	private $is_apache;

	public function setUp() {
		parent::setUp();
		$this->is_apache = isset( $GLOBALS['is_apache'] ) ? $GLOBALS['is_apache'] : null;
		$GLOBALS['is_apache'] = true;

		Functions\expect( 'get_home_path' )->andReturn( 'vfs://public/' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpectedForFlushHtaccess( $config, $expected ) {
		//do_action( 'permalink_structure_changed', $config['old_permalink_structure'], $config['new_permalink_structure'] );
		$this->set_permalink_structure( $config['old_permalink_structure'] );
		$this->set_permalink_structure( $config['new_permalink_structure'] );

		$htaccess_content = $this->filesystem->get_contents( 'vfs://public/.htaccess' );
		$this->assertContains( $expected, $htaccess_content );
	}

	public function tearDown() {
		parent::tearDown();

		$this->set_permalink_structure( '/%postname%/' );

		// Restore the original state.
		if ( ! empty( $this->is_apache ) ) {
			$GLOBALS['is_apache'] = $this->is_apache;
		}
	}

}
