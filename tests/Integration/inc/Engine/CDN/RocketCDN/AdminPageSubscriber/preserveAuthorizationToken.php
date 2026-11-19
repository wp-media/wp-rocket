<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;
use WP_Rocket\Tests\TestCredentials;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::preserve_authorization_token
 *
 * @uses \WP_Rocket\Engine\CDN\RocketCDN\APIClient::preserve_authorization_token
 *
 * @group AdminOnly
 * @group RocketCDN
 * @group RocketCDNAdminPage
 */
class Test_PreserveAuthorizationToken extends TestCase {
	public function set_up() {
		parent::set_up();

		add_option( 'rocketcdn_user_token', TestCredentials::ROCKETCDN_TOKEN );
	}

	public function tear_down() {
		delete_option( 'rocketcdn_user_token' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldPreserveAuthorizationToken( $config, $expected, $sent = null ) {
		if ( isset( $config['append_token'] ) ) {
			$expected['headers']['Authorization'] .= TestCredentials::ROCKETCDN_TOKEN;
		}

		if ( is_null( $sent ) ) {
			$sent = $expected;
		}

		$args = apply_filters( 'http_request_args', $sent, $config['url'] );

		$this->assertSame( $expected, $args );
	}
}
