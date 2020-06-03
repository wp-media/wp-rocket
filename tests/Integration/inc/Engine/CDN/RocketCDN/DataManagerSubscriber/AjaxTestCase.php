<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\AjaxTestCase as BaseAjaxTestCase;
use WP_Rocket\Tests\Integration\CapTrait;

abstract class AjaxTestCase extends BaseAjaxTestCase {
	private static   $user_id = 0;
	protected static $ajax_action;

	public static function wpSetUpBeforeClass( $factory ) {
		CapTrait::hasAdminCapBeforeClass();
		CapTrait::setAdminCap();

		self::$user_id = $factory->user->create( [ 'role' => 'administrator' ] );
	}

	public function setUp() {
		parent::setUp();

		wp_set_current_user( self::$user_id );

		$_POST['nonce']  = wp_create_nonce( 'rocket-ajax' );
		$_POST['action'] = static::$ajax_action;
		$this->action    = static::$ajax_action;
	}

	protected function assertSettings( $settings ) {
		$options = get_option( 'wp_rocket_settings' );
		foreach ( $settings as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[ $key ] );
		}
	}
}
