<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\AboveTheFold\AJAX\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold;
use WP_Rocket\Engine\Media\AboveTheFold\AJAX\Controller;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Media\AboveTheFold\AJAX\Controller::add_lcp_data
 *
 * @group AboveTheFold
 */
class Test_AddLcpData extends TestCase {
	private $query;
	private $controller;
	private $context;

	protected function setUp(): void {
		parent::setUp();

		$this->stubEscapeFunctions();

		$this->query      = $this->createPartialMock( AboveTheFold::class, [ 'add_item' ] );
		$this->context    = Mockery::mock( Context::class );
		$this->controller = new Controller( $this->query, $this->context );
	}

	protected function tearDown(): void {
		unset( $_POST );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$_POST = [
			'url'       => $config['url'],
			'is_mobile' => $config['is_mobile'],
			'images'    => $config['images'],
		];

		Functions\expect( 'check_ajax_referer' )
			->once()
			->with( 'rocket_lcp', 'rocket_lcp_nonce' )
			->andReturn( true );

		$this->context->shouldReceive( 'is_allowed' )
			->atMost()
			->once()
			->andReturn( $config['filter'] );

		Functions\when( 'wp_unslash' )
			->returnArg();

		Functions\when( 'sanitize_text_field' )
			->returnArg();

		Functions\when( 'current_time' )
			->justReturn( $expected['item']['last_accessed'] );

		$this->query->method( 'add_item' )
			->with( $expected['item'] )
			->willReturn( $expected['result'] );

		if ( ! $expected['result'] ) {
			Functions\expect( 'wp_send_json_error' )
				->once()
				->with( $expected['message'] );
		} elseif ( $expected['result'] ) {
			Functions\expect( 'wp_send_json_success' )
				->once()
				->with( $expected['message'] );
		}

		$this->controller->add_lcp_data();
	}
}
