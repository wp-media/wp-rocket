<?php

namespace WP_Rocket\tests\Unit\inc\Engine\Optimization\LazyRenderContent\AJAX\Controller;

use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Optimization\LazyRenderContent\AJAX\Controller;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Queries\LazyRenderContent;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Mockery;

/**
 * Test class covering WP_Rocket\Engine\Optimization\LazyRenderContent\AJAX\Controller::check_data
 *
 * @group LazyRenderContent
 */
class Test_CheckData extends TestCase {
	private $query;
	private $controller;

	private $temp_post = [];

	private $context;

	protected function setUp(): void {
		parent::setUp();
		$this->query      = $this->createPartialMock( LazyRenderContent::class, [ 'get_row' ] );
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

		$this->assertSame( [ 'lrc' => $expected['message'] ], $this->controller->check_data() );
	}
}
