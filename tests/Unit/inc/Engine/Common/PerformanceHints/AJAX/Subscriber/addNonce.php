<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Common\PerformanceHints\AJAX\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\Processor;
use WP_Rocket\Engine\Common\PerformanceHints\AJAX\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Common\PerformanceHints\AJAX\Subscriber::add_nonce
 *
 * @group PerformanceHints
 */
class Test_AddNonce extends TestCase {
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->subscriber = new Subscriber( Mockery::mock( Processor::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSendFreshNonce( $config, $expected ) {
		Functions\expect( 'wp_create_nonce' )
			->once()
			->with( 'rocket_beacon' )
			->andReturn( $config['nonce'] );

		Functions\expect( 'wp_send_json_success' )
			->once()
			->with( [ 'nonce' => $config['nonce'] ] );

		$this->subscriber->add_nonce();
	}
}
