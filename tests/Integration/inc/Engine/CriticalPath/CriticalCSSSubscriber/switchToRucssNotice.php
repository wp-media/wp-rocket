<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::switch_to_rucss_notice
 * @group  AdminOnly
 */
class Test_switchToRucssNotice extends TestCase {
	use DBTrait;
	private static $user;

	private $original_user;

	protected $async_css;

	private static $user_id = 0;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::uninstallAll();
		$container     = apply_filters( 'rocket_container', null );
		self::$user    = $container->get( 'user' );
	}

	public function set_up()
	{
		parent::set_up();
		add_filter('pre_get_rocket_option_async_css', [$this, 'async_css']);
		add_filter('rocket_disable_rucss_setting', [$this, 'rucss']);

		wp_set_current_user( self::$user_id );

		$this->original_user    = $this->getNonPublicPropertyValue( 'user', self::$user, self::$user );

	}

	public function tear_down()
	{
		$this->set_reflective_property( $this->original_user, 'user', self::$user );
		remove_filter('pre_get_rocket_option_async_css', [$this, 'async_css']);
		remove_filter('rocket_disable_rucss_setting', [$this, 'rucss']);
		update_user_meta( get_current_user_id(), 'rocket_boxes', [] );
		set_current_screen( 'front' );
		parent::tear_down();
	}

    /**
     * @dataProvider configTestData
     */
    public function testShouldDoAsExpected( $config, $expected )
    {
		$this->config = $config;
	    $this->set_reflective_property( $config['user'], 'user', self::$user );

		if($config['in_boxes']) {
			rocket_dismiss_box( 'switch_to_rucss_notice' );
		}
		if ($config['is_right_screen']) {
			set_current_screen( 'settings_page_wprocket' );
		} else {
			set_current_screen( 'edit.php' );
		}

	    ob_start();
	    do_action('admin_notices');
	    $result = ob_get_clean();
	    if($expected['contains']) {
		    $this->assertStringContainsString(
			    $this->format_the_html( $expected['content'] ),
			    $this->format_the_html( $result )
		    );
	    } else {
		    $this->assertStringNotContainsString(
			    $this->format_the_html( $expected['content'] ),
			    $this->format_the_html( $result )
		    );
	    }
    }

	public function async_css() {
		return $this->config['async_css'];
	}

	public function rucss() {
		return [
			'disable' => $this->config['rucss_status'],
		];
	}
}
