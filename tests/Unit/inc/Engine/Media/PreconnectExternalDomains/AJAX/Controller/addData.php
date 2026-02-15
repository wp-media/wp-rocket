<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\PreconnectExternalDomains\AJAX\Controller;

use Mockery;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\AJAX\Controller;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Context\Context;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Queries\PreconnectExternalDomains;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * Test class covering WP_Rocket\Engine\Media\PreconnectExternalDomains\AJAX\Controller::add_data
 *
 * @group PreconnectExternalDomains
 */
class Test_AddData extends TestCase {
	private $query;
	private $controller;
	private $context;

	private $temp_post = [];

	protected function setUp(): void {
		parent::setUp();
		$this->query      = $this->createPartialMock( PreconnectExternalDomains::class, [ 'add_item' ] );
		$this->context    = Mockery::mock( Context::class );
		$this->controller = new Controller( $this->query, $this->context );
		$this->temp_post  = $_POST;


		$this->stubEscapeFunctions();
	}

	protected function tearDown(): void {
		unset( $_POST );
		$_POST = $this->temp_post;

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();

		$_POST = [
			'url'       => addslashes( $config['url'] ),
			'is_mobile' => addslashes( $config['is_mobile'] ),
			'results'   => addslashes( $config['results'] ),
			'status'    => addslashes( $config['status'] ?? 'success' ),
		];

		Functions\expect( 'check_ajax_referer' )
			->once()
			->with( 'rocket_beacon', 'rocket_beacon_nonce' )
			->andReturn( true );

		$this->context->shouldReceive( 'is_allowed' )
			->atMost()
			->once()
			->andReturn( $config['filter'] );

		Functions\when('sanitize_url')->alias(function($url) {
			return filter_var($url, FILTER_SANITIZE_URL) ?: '';
		});

		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return is_string( $value ) ? strip_tags( $value ) : $value;
			}
		);

		Functions\when( 'current_time' )
			->justReturn( $expected['item']['last_accessed'] );

		$this->query->method( 'add_item' )
			->with( $expected['item'] )
			->willReturn( $expected['result'] );

		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return is_string( $value ) ? stripslashes( $value ) : $value;
			}
		);

		$this->stubWpParseUrl();

		$this->assertSame( [ 'preconnect_external_domains' => $expected['message'] ], $this->controller->add_data() );
	}
}
