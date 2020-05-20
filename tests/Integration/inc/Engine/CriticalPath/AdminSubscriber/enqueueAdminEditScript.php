<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\AdminSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AdminSubscriber::enqueue_admin_edit_script
 * @uses   ::rocket_get_constant
 *
 * @group  AdminOnly
 * @group  CriticalPath
 */
class Test_EnqueueAdminEditScript extends TestCase {
	private        $post_id;
	private static $user_id;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		CapTrait::setAdminCap();

		self::$user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );
		delete_post_meta( $this->post_id, '_rocket_exclude_async_css' );
		unset( $GLOBALS['post'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEnqueueAdminScript( $config, $expected ) {
		wp_set_current_user( static::$user_id );
		set_current_screen( $config['page'] );

		Functions\when( 'wp_create_nonce' )->justReturn( 'wp_rest_nonce' );

		if ( in_array( $config['page'], [ 'edit.php', 'post.php' ], true ) ) {
			$this->async_css = $config['options']['async_css'];
			$this->post_id   = $config['post']->ID;

			add_filter( 'pre_get_rocket_option_async_css', [ $this, 'setCPCSSOption' ] );

			if ( $config['is_option_excluded'] ) {
				add_post_meta( $this->post_id, '_rocket_exclude_async_css', $config['is_option_excluded'], true );
			}

			$GLOBALS['post'] = $config['post'];
		}

		$wp_scripts = wp_scripts();
		$wp_scripts->init();

		do_action( 'admin_enqueue_scripts', $config['page'] );

		if ( $expected ) {
			$this->assertArrayHasKey( 'wpr-edit-cpcss-script', $wp_scripts->registered );
			$this->assertArrayHasKey( 'data', $wp_scripts->registered['wpr-edit-cpcss-script']->extra );
		} else {
			$this->assertArrayNotHasKey( 'wpr-edit-cpcss-script', $wp_scripts->registered );
		}
	}

	public function setCPCSSOption() {
		return $this->async_css;
	}
}
