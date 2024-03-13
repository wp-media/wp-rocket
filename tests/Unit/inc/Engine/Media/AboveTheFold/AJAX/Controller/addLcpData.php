<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\AboveTheFold\AJAX\Controller;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold;
use WP_Rocket\Engine\Media\AboveTheFold\AJAX\Controller;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Engine\Media\AboveTheFold\AJAX\Controller::add_lcp_data
 *
 * @group AboveTheFold
 */
class Test_AddLcpData extends TestCase {
	private $query;
	private $controller;
	private $context;

	protected function setUp(): void {
		parent::setUp();

		$this->query      = $this->createPartialMock( AboveTheFold::class, [ 'get_row' ] );
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
		$this->context->shouldReceive( 'is_allowed' )
			->atMost()
			->once()
			->andReturn( $config['filter'] );

		$this->controller->add_lcp_data();
	}
}
