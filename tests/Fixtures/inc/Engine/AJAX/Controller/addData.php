<?php

namespace WP_Rocket\tests\Fixtures\inc\Engine\AJAX\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Optimization\LazyRenderContent\AJAX\Controller;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Database\Queries\LazyRenderContent;
use WP_Rocket\Tests\Unit\TestCase;
use function Brain\Monkey\Functions;

/**
 * Test class covering WP_Rocket\Engine\Media\AboveTheFold\AJAX\Controller::add_data
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
		$this->controller = new Controller( $this->query );
		$this->context    = Mockery::mock( Context::class );
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


		$this->controller->add_data();
	}
}
