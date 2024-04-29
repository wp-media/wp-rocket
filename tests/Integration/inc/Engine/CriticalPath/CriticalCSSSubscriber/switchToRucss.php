<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::switch_to_rucss
 * @group  AdminOnly
 */
class Test_switchToRucss extends TestCase {
	protected static $user_id;
	protected static $options;
	protected static $options_api;
	protected static $original_options;
	public static function wpSetUpBeforeClass( $factory ) {
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_purge_cache' );
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		$container     = apply_filters( 'rocket_container', null );
		self::$options = $container->get('options');
		self::$original_options = self::$options->get_options();
		self::$options_api = $container->get('options_api');
		self::installFresh();
	}

	public static function tear_down_after_class() {
		parent::tear_down_after_class();
		self::$options_api->set( 'settings', self::$original_options );

		self::uninstallAll();
	}

	public function set_up() {
		update_user_meta( get_current_user_id(), 'rocket_boxes', [] );
		parent::set_up();
	}

	public function tear_down() {
		update_user_meta( get_current_user_id(), 'rocket_boxes', [] );
		parent::tear_down();
	}

	/**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
	    self::$options->set( 'async_css', true );
	    self::$options->set( 'remove_unused_css', false );
		self::$options_api->set( 'settings', self::$options->get_options() );

	    if ( $config['user_can'] ) {
		    wp_set_current_user( self::$user_id );
	    }

		$_GET['_wpnonce'] = wp_create_nonce( "rucss_switch" );
	    Functions\expect( 'check_admin_referer' )->once()->with( 'rucss_switch' )->andReturnNull();
	    Functions\expect( 'wp_safe_redirect' )->once()->andReturnNull();
	    Functions\expect( 'wp_die' )->once()->andReturnNull();
        do_action('admin_post_switch_to_rucss');

		$boxes = get_user_meta( self::$user_id, 'rocket_boxes', true ) ?: [];

		if($config['has_box']) {
			$this->assertContains('switch_to_rucss_notice', $boxes);
		}else {
			$this->assertNotContains('switch_to_rucss_notice', $boxes);
		}
		self::$options->set_values(self::$options_api->get('settings'));
    }
}
