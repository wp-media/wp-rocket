<?php

namespace WP_Rocket\Tests\Integration\inc\admin;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers ::rocket_first_install
 * @group admin
 * @group upgrade
 * @group AdminOnly
 */
class Test_RocketFirstInstall extends TestCase {
	private $box_name = 'rocket_warning_plugin_modification';
	private $previous_user_id;
	private $pagenow;
	private $options;
	private $option_name;
	private $user_boxes;
	private $transient_value;
	private $transient_timeout;

	private static $user_id = 0;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public function set_up() {
		parent::set_up();

		$this->previous_user_id = get_current_user_id();

		wp_set_current_user( self::$user_id );

		$this->pagenow         = $GLOBALS['pagenow'];
		$this->option_name     = rocket_get_constant( 'WP_ROCKET_SLUG' );
		$this->options         = get_option( $this->option_name );
		$this->user_boxes      = get_user_meta( self::$user_id, 'rocket_boxes', true );
		$this->transient_value = get_transient( $this->box_name );

		if ( $this->transient_value ) {
			$this->transient_timeout = get_option( '_transient_timeout_' . $this->box_name );
		}

		$GLOBALS['pagenow'] = 'options.php';
		delete_option( $this->option_name );
		delete_user_meta( self::$user_id, 'rocket_boxes' );
		set_transient( $this->box_name, 'foobar' );

		add_filter( 'rocket_delay_js_default_exclusions', '__return_empty_array' );
	}

	public function tear_down() {
		parent::tear_down();

		wp_set_current_user( $this->previous_user_id );

		$GLOBALS['pagenow'] = $this->pagenow;

		if ( $this->options ) {
			update_option( $this->option_name, $this->options );
		} else {
			delete_option( $this->option_name );
		}

		if ( $this->user_boxes ) {
			update_user_meta( self::$user_id, 'rocket_boxes', $this->user_boxes );
		} else {
			delete_user_meta( self::$user_id, 'rocket_boxes' );
		}

		if ( $this->transient_value ) {
			set_transient( $this->box_name, $this->transient_value );

			if ( $this->transient_timeout ) {
				update_option( '_transient_timeout_' . $this->box_name, $this->transient_timeout );
			}
		} else {
			delete_transient( $this->box_name );
		}

		remove_filter( 'rocket_delay_js_default_exclusions', '__return_empty_array' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddOption( $expected ) {
		$uniqids = [
			'secret_cache_key' => '',
			'minify_css_key'   => '',
			'minify_js_key'    => '',
		];

		rocket_first_install();

		$options = get_option( $this->option_name );

		$this->assertTrue( is_array( $options ) );
		$this->assertTrue( ! empty( $options['secret_cache_key'] ) && ! empty( $options['minify_css_key'] ) && ! empty( $options['minify_js_key'] ) );

		$expected = array_diff_key( $expected['integration'], $uniqids );
		$options  = array_diff_key( $options, $uniqids );

		$this->assertSame( $expected, $options );

		$user_boxes = get_user_meta( self::$user_id, 'rocket_boxes', true );

		$this->assertTrue( is_array( $user_boxes ) );
		$this->assertContains( $this->box_name, $user_boxes );
		$this->assertNotSame( 'foobar', get_transient( $this->box_name ) );
	}

	public function addProvider() {
		return $this->getTestData( __DIR__, 'rocketFirstInstall' );
	}
}
