<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\ModPagespeed;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\ModPagespeed::show_admin_notice
 * @group AdminOnly
 * @group mod_pagespeed
 * @group ThirdParty
 */
class Test_ShowAdminNotice extends TestCase {
	private static $admin_user_id  = 0;
	private static $editor_user_id = 0;
	private $headers;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::setAdminCap();

		//create an editor user that has the capability
		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		//create an editor user that has no capability
		self::$editor_user_id = static::factory()->user->create( [ 'role' => 'editor' ] );
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'bypass_request'] );
		delete_transient( 'rocket_mod_pagespeed_enabled' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$user_id = $config['capability'] ? self::$admin_user_id : self::$editor_user_id;
		wp_set_current_user( $user_id );

		if ( isset( $config['current_screen'] ) ) {
			set_current_screen( $config['current_screen'] );
		}

		if ( isset( $config['rocket_mod_pagespeed_enabled'] ) ) {
			if (false !== $config['rocket_mod_pagespeed_enabled']) {
				set_transient( 'rocket_mod_pagespeed_enabled', $config['rocket_mod_pagespeed_enabled'], DAY_IN_SECONDS );
			}
		}

		if ( isset( $config['boxes'] ) ) {
			update_user_meta( $user_id, 'rocket_boxes', $config['boxes'] );
		} else {
			delete_user_meta( $user_id, 'rocket_boxes' );
		}

		Functions\when( 'apache_mod_loaded' )->justReturn( $config['apache_mod_loaded'] ?? false );

		if ( isset( $config['home_response_headers'] ) ) {
			$this->headers = $config['home_response_headers'];
			add_filter( 'pre_http_request', [ $this, 'bypass_request'] );
		}

		ob_start();
		do_action( 'admin_notices' );
		$actual = ob_get_clean();

		$this->assertStringContainsString(
			$this->format_the_html( str_replace('{{nonce}}', wp_create_nonce('rocket_ignore_rocket_error_mod_pagespeed'), $expected['html'] ?? '') ),
			$this->format_the_html( $actual )
		);
	}

	public function bypass_request() {
		return [
			'headers' => $this->headers,
			'body' => '',
			'response' => [],
			'cookies' => [],
			'filename' => '',
		];
	}
}
