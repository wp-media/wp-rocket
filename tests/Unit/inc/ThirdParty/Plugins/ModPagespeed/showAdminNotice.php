<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\ModPagespeed;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\ModPagespeed;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\ModPagespeed::show_admin_notice
 * @group mod_pagespeed
 * @group ThirdParty
 */
class Test_ShowAdminNotice extends TestCase {
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		Functions\stubTranslationFunctions();

		$this->subscriber = new ModPagespeed();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )->justReturn( $config['capability'] ?? false );

		if ( isset( $config['current_screen'] ) ) {
			Functions\when( 'get_current_screen' )->alias(
				function () use ( $config ) {
					return (object) [
						'id' => $config['current_screen'],
					];
				}
			);
		}

		if ( isset( $config['rocket_mod_pagespeed_enabled'] ) ) {
			Functions\expect( 'get_transient' )->with( 'rocket_mod_pagespeed_enabled', 1 )->andReturn( $config['rocket_mod_pagespeed_enabled'] );
		}

		if ( isset( $config['boxes'] ) ) {
			Functions\expect( 'get_current_user_id' )->once()->andReturn( 1 );
			Functions\expect( 'get_user_meta' )
				->once()
				->with( 1, 'rocket_boxes', true )
				->andReturn( $config['boxes'] );
		}

		Functions\when( 'apache_mod_loaded' )->justReturn( $config['apache_mod_loaded'] ?? false );

		if ( isset( $config['home_response_headers'] ) ) {
			Functions\expect( 'home_url' )->andReturn( 'http://example.org' );
			Functions\expect( 'wp_remote_get' )
				->once()
				->with( 'http://example.org', ['timeout' => 3,'sslverify'=>false] )
				->andReturn( 'response' );
			Functions\expect( 'wp_remote_retrieve_headers' )
				->once()
				->with( 'response' )
				->andReturn( $config['home_response_headers'] );
		}

		if ( isset( $expected['set_transient'] ) ) {
			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_mod_pagespeed_enabled', $expected['show_notice'], DAY_IN_SECONDS )
				->andReturnNull();
		}

		if ( $expected['show_notice'] ) {
			Functions\when( 'rocket_notice_html' )->alias(
				function ( $args ) {
					echo '<div class="notice notice-' . $args['status'] . ' "><p>' . $args['message'] . '</p><p><a class="rocket-dismiss " href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_error_mod_pagespeed&amp;_wpnonce=123456">Dismiss this notice</a></p></div>';
				}
			);
		}

		ob_start();
		$this->subscriber->show_admin_notice();
		$actual = ob_get_clean();

		$this->assertSame( $this->format_the_html( str_replace("{{nonce}}", "123456", $expected['html'] ?? '' ) ), $this->format_the_html( $actual ) );
	}
}
