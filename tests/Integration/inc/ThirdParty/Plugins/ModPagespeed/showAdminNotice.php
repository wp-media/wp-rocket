<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\ModPagespeed;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\ModPagespeed::show_admin_notice
 * @group AdminOnly
 * @group mod_pagespeed
 * @group ThirdParty
 */
class Test_ShowAdminNotice extends TestCase {
	use HttpRequestTrait;

	private static $admin_user_id  = 0;
	private static $editor_user_id = 0;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::setAdminCap();

		//create an editor user that has the capability
		self::$admin_user_id = static::factory()->user->create( [ 'role' => 'administrator' ] );
		//create an editor user that has no capability
		self::$editor_user_id = static::factory()->user->create( [ 'role' => 'editor' ] );
	}

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		// Don't trigger modules that depend on the current_screen hook.
		$this->unregisterAllCallbacks( 'current_screen' );

		// Keep only the notice under test: other notices make their own requests.
		$this->unregisterAllCallbacksExcept( 'admin_notices', 'show_admin_notice', 10 );
	}

	public function tear_down() {
		delete_transient( 'rocket_mod_pagespeed_enabled' );

		// IsolateHookTrait keeps a single backup, so only admin_notices can be restored; core restores current_screen.
		$this->restoreWpHook( 'admin_notices' );

		$this->tear_down_http();

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

		// has_pagespeed() requests the home page and looks for the mod_pagespeed headers.
		if ( isset( $config['home_response_headers'] ) ) {
			$this->config['http'] = [
				home_url() => [
					'headers'  => $config['home_response_headers'],
					'body'     => '',
					'response' => [ 'code' => 200, 'message' => 'OK' ],
					'cookies'  => [],
				],
			];
		}

		ob_start();
		do_action( 'admin_notices' );
		$actual = ob_get_clean();

		$this->assertStringContainsString(
			$this->format_the_html( str_replace('{{nonce}}', wp_create_nonce('rocket_ignore_rocket_error_mod_pagespeed'), $expected['html'] ?? '') ),
			$this->format_the_html( $actual )
		);
	}
}
