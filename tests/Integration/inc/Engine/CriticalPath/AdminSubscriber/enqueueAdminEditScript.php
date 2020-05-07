<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::enqueue_admin_edit_script
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_EnqueueAdminEditScript extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/AdminSubscriber/enqueueAdminEditScript.php';

	private        $post_id;
	private static $user_id;

	public static function wpSetUpBeforeClass( $factory ) {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_manage_options' );
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		$admin = get_role( 'administrator' );
		$admin->remove_cap( 'rocket_manage_options' );
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );
		delete_post_meta( $this->post_id, '_rocket_exclude_async_css' );
		unset( $GLOBALS['post'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldEnqueueAdminScript( $config, $expected ) {
		wp_set_current_user( static::$user_id );
		set_current_screen( $config['page'] );

		if ( in_array( $config['page'], [ 'edit.php', 'post.php' ], true ) ) {
			$this->async_css = $config['options']['async_css'];
			$this->post_id   = $config['post']['ID'];

			add_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );

			if ( $config['is_option_excluded'] ) {
				add_post_meta( $this->post_id, '_rocket_exclude_async_css', $config['is_option_excluded'], true );
			}

			$GLOBALS['post'] = (object) [
				'ID'          => $this->post_id,
				'post_status' => $config['post']['post_status'],
				'post_type'   => $config['post']['post_type'],
			];
		}

		$wp_scripts = wp_scripts();
		$wp_scripts->init();

		do_action( 'admin_enqueue_scripts', $config['page'] );

		if ( $expected ) {
			$this->assertArrayHasKey( 'wpr-edit-cpcss-script', $wp_scripts->registered );
		} else {
			$this->assertArrayNotHasKey( 'wpr-edit-cpcss-script', $wp_scripts->registered );
		}
	}

	public function setCPCSSOption() {
		return $this->async_css;
	}
}
