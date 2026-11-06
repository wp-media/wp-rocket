<?php

namespace WP_Rocket\Tests\Unit\Engine\Common\PerformanceHints\AJAX;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\Processor;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\AJAX\Subscriber::add_nonce
 *
 * @group PerformanceHints
 */
class SubscriberTest extends TestCase {
	public function testShouldSendFreshNonceOnAddNonce() {
		$subscriber = new Subscriber( Mockery::mock( Processor::class ) );

		Functions\expect( 'wp_create_nonce' )
			->once()
			->with( 'rocket_beacon' )
			->andReturn( 'fresh_nonce' );

		Functions\expect( 'wp_send_json_success' )
			->once()
			->with( [ 'nonce' => 'fresh_nonce' ] );

		$subscriber->add_nonce();

		$this->assertTrue( true ); // @phpstan-ignore-line
	}

	public function testShouldRegisterNonceCallbacks() {
		$events = Subscriber::get_subscribed_events();

		$this->assertSame(
			'add_nonce',
			$events['wp_ajax_rocket_beacon_nonce']
		);
		$this->assertSame(
			'add_nonce',
			$events['wp_ajax_nopriv_rocket_beacon_nonce']
		);
	}
}
