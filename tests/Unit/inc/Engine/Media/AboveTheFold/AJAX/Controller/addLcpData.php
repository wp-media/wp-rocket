<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\AboveTheFold\AJAX\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold;
use WP_Rocket\Engine\Media\AboveTheFold\AJAX\Controller;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * Test class covering WP_Rocket\Engine\Media\AboveTheFold\AJAX\Controller::add_lcp_data
 *
 * @group AboveTheFold
 */
class Test_AddLcpData extends TestCase {
	private $query;
	private $controller;
	private $context;

	private $temp_post = [];

	protected function setUp(): void {
		parent::setUp();

		$this->stubEscapeFunctions();

		$this->query      = $this->createPartialMock( AboveTheFold::class, [ 'add_item' ] );
		$this->context    = Mockery::mock( Context::class );
		$this->controller = new Controller( $this->query, $this->context );
		$this->temp_post = $_POST;
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
			'lcp_images'    => addslashes( $config['lcp_images'] ),
			'status'    => addslashes( $config['status'] ?? 'success' ),
		];

		Functions\expect( 'check_ajax_referer' )
			->once()
			->with( 'rocket_lcp', 'rocket_lcp_nonce' )
			->andReturn( true );

		$this->context->shouldReceive( 'is_allowed' )
			->atMost()
			->once()
			->andReturn( $config['filter'] );

		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return is_string( $value ) ? stripslashes( $value ) : $value;
			}
		);

		Functions\when('wp_parse_url')->justReturn('example.org');

		Functions\when( 'sanitize_text_field' )->alias(
			function ( $value ) {
				return is_string( $value ) ? strip_tags( $value ) : $value;
			}
		);

		$images_valid_sources = $expected['images_valid_sources'] ?? [];

		Functions\when( 'sanitize_url' )->alias(
			function( $url ) use ( $images_valid_sources ) {
				return $images_valid_sources[$url] ?? $url;
			}
		);

		Functions\when('esc_url_raw')->alias(
			function( $url ) use ( $images_valid_sources ) {
				return $images_valid_sources[$url] ?? $url;
			}
		);

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

		$this->stubWpParseUrl();

		Filters\expectApplied('rocket_atf_invalid_schemes')->with([ 'chrome-[^:]+://' ])->andReturn([ 'chrome-[^:]+://' ]);

		if ( ! empty( $config['filetype'] ) ) {
			Functions\when('get_allowed_mime_types')->justReturn( $config['allowed_mime_types'] );
			Functions\when('wp_check_filetype')->justReturn( $config['filetype'] );
		}

		$this->controller->add_lcp_data();
	}
}
