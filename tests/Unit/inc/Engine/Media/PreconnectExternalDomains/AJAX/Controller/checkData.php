<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Media\PreconnectExternalDomains\AJAX\Controller;

use WP_Rocket\Engine\Media\PreconnectExternalDomains\Context\Context;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\AJAX\Controller;
use WP_Rocket\Engine\Media\PreconnectExternalDomains\Database\Queries\PreconnectExternalDomains;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Test class covering WP_Rocket\Engine\Media\PreconnectExternalDomains\AJAX\Controller::check_data
 *
 * @group PreconnectExternalDomains
 */
class Test_CheckData extends TestCase {
	private $query;
	private $controller;

	private $temp_post = [];

	private $context;

	protected function setUp(): void {
		parent::setUp();
		$this->query      = $this->createPartialMock( PreconnectExternalDomains::class, [ 'get_row' ] );
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
		];

		$this->context->shouldReceive( 'is_allowed' )
			->atMost()
			->once()
			->andReturn( $config['filter'] );

		Functions\expect( 'check_ajax_referer' )
			->once()
			->with( 'rocket_beacon', 'rocket_beacon_nonce' )
			->andReturn( true );

		Functions\when('esc_url_raw')->alias(
			function( $url ){
				return $url;
			}
		);

		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return is_string( $value ) ? stripslashes( $value ) : $value;
			}
		);

		$this->query->method( 'get_row' )
			->with( $config['url'], $config['is_mobile'] )
			->willReturn( $config['row'] );

		$this->assertSame( [ 'preconnect_external_domains' => $expected['message'] ], $this->controller->check_data() );
	}
}
