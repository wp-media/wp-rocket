<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\AboveTheFold\Frontend\Controller;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\AboveTheFold\Context\Context;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Queries\AboveTheFold as ATFQuery;
use WP_Rocket\Engine\Media\AboveTheFold\Database\Rows\AboveTheFold as ATFRow;
use WP_Rocket\Engine\Media\AboveTheFold\Frontend\Controller;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\AboveTheFold\Frontend\Controller::optimize
 *
 * @group Media
 * @group AboveTheFold
 */
class Test_optimize extends TestCase {
	private $options;
	private $query;
	private $controller;
	private $context;

	protected function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->query   = $this->createPartialMock( ATFQuery::class, [ 'get_row' ] );
		$this->context = Mockery::mock( Context::class );

		$this->controller = new Controller( $this->options, $this->query, $this->context );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		$this->stubEscapeFunctions();
		$this->stubWpParseUrl();

		Functions\expect( 'rocket_bypass' )
			->atMost()
			->once()
			->andReturn( false );

		Functions\when( 'wp_http_validate_url' )->justReturn( false );

		Filters\expectApplied( 'rocket_disable_meta_generator' )
			->atMost()
			->once()
			->andReturn( true );

		$row = $this->getMockBuilder( ATFRow::class )
					->disableOriginalConstructor()
					->getMock();

		$row->expects( $this->once() )
			->method( 'has_lcp' )
			->willReturn( $config['has_lcp'] );

		$row->lcp = $config['lcp'];

		$this->assertSame(
			$expected,
			$this->controller->optimize( $html, $row )
		);
	}

	/**
	 * Ensures that a `preg_replace_callback()` failure inside `set_fetchpriority()` (e.g. a PCRE
	 * error) never bubbles up as a TypeError against the method's declared `string` return type.
	 * `set_fetchpriority()` is private, so this is exercised through the public `optimize()` entry
	 * point, forcing every `preg_replace_callback()` call site in this class to return `null`.
	 */
	public function testShouldNotThrowTypeErrorWhenPregReplaceCallbackReturnsNull() {
		$this->stubEscapeFunctions();
		$this->stubWpParseUrl();

		Functions\expect( 'rocket_bypass' )
			->atMost()
			->once()
			->andReturn( false );

		Functions\when( 'wp_http_validate_url' )->justReturn( false );

		// Simulate every preg_replace_callback() call in this class (including the unguarded one
		// this fix adds a null-check for) returning null, as PHP does on a PCRE engine error.
		Functions\when( 'preg_replace_callback' )->justReturn( null );

		Filters\expectApplied( 'rocket_disable_meta_generator' )
			->atMost()
			->once()
			->andReturn( true );

		$html = '<html><head><title>Test</title></head><body><img src="image.jpg" alt="test"></body></html>';

		$row = $this->getMockBuilder( ATFRow::class )
					->disableOriginalConstructor()
					->getMock();

		$row->expects( $this->once() )
			->method( 'has_lcp' )
			->willReturn( true );

		$row->lcp = json_encode(
			(object) [
				'type' => 'img',
				'src'  => 'image.jpg',
			]
		);

		$expected = '<html><head><title>Test</title><link rel="preload" data-rocket-preload as="image" href="image.jpg" fetchpriority="high"></head><body><img src="image.jpg" alt="test"></body></html>';

		$result = $this->controller->optimize( $html, $row );

		$this->assertIsString( $result );
		$this->assertSame( $expected, $result );
	}
}
