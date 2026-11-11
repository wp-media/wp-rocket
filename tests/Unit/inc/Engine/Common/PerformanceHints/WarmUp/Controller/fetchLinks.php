<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Common\PerformanceHints\WarmUp\Controller;

use Brain\Monkey\{Filters, Functions};
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Common\PerformanceHints\WarmUp\{APIClient, Controller, Queue};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\PerformanceHints\WarmUp\Controller::fetch_links
 *
 * @group PerformanceHints
 */
class Test_FetchLinks extends TestCase {
	private $user;
	private $controller;
	private $queue;

	protected function setUp(): void {
		parent::setUp();

		$options          = Mockery::mock( Options_Data::class );
		$api_client       = Mockery::mock( APIClient::class );
		$this->user       = Mockery::mock( User::class );
		$this->queue      = Mockery::mock( Queue::class );
		$this->controller = new Controller( [1], $options, $api_client, $this->user, $this->queue );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

		Functions\when( 'home_url' )->alias(
			function ( $link = '' ) {
				return '' === $link ? 'https://example.org' : 'https://example.org' . $link;
			}
		);

		Functions\expect( 'wp_safe_remote_get' )
			->atMost()
			->once()
			->with( 'https://example.org', $config['headers'] )
			->andReturn( $config['response'] );

		Functions\expect( 'wp_remote_retrieve_response_code' )
			->atMost()
			->once()
			->with( $config['response'] )
			->andReturn( $config['response']['response']['code'] );

		if ( 200 === $config['response']['response']['code'] ) {
			Functions\expect( 'wp_remote_retrieve_body' )
				->once()
				->with( $config['response'] )
				->andReturn( $config['response']['body'] );
		}

		if ( isset( $config['found_link'] ) && $config['found_link'] ) {
			$this->stubWpParseUrl();

			Functions\when( 'wp_http_validate_url' )->alias(
				function ( $link ) {
					return false !== strpos( $link, 'https' ) ? $link : false;
				}
			);

			Filters\expectApplied( 'rocket_performance_hints_warmup_links_number' )
				->once()
				->with( 10 );
		}

		$this->assertSame(
			$expected,
			$this->controller->fetch_links()
		);
	}
}
