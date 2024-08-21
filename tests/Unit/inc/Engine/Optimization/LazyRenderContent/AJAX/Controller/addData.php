<?php

namespace WP_Rocket\tests\Fixtures\inc\Engine\AJAX\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Optimization\LazyRenderContent\AJAX\Controller;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Queries\LazyRenderContent;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Optimization\LazyRenderContent\AJAX\Controller::add_data
 *
 * @group LazyRenderContent
 */
class Test_AddData extends TestCase {
	private $query;
	private $controller;
	private $context;

	private $temp_post = [];

	protected function setUp(): void {
		parent::setUp();
		$this->query      = $this->createPartialMock( LazyRenderContent::class, [ 'add_item' ] );
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

		$valid_source = $expected['valid_source'] ?? [];

		if(empty($valid_source)) {
			Functions\when( 'sanitize_text_field' )->alias(
				function ( $value ) {
					return is_string( $value ) ? strip_tags( $value ) : $value;
				}
			);
		} else{
			Functions\when('sanitize_text_field')->alias(
				function ($value) use ($valid_source) {
					$arr_value = [];
					if (!is_string($value)) {
						foreach ($valid_source as $key => $replacement) {
							$arr_value[] = strip_tags($replacement);
						}
						return (object) $arr_value;
					}

					return strip_tags($value);
				}
			);
		}

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

		$this->assertSame( [ 'lrc' => $expected['message'] ], $this->controller->add_data() );
	}
}
