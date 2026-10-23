<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\LazyRenderContent\Frontend\Processor\Regex;

use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\Regex;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group LRC
 */
class TestAddHashes extends TestCase {
	private $processor;

	public function set_up() {
		parent::set_up();

		$this->processor = new Regex();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $html, $expected ) {
		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $this->processor->add_hashes( $html ) )
		);
	}

	/**
	 * @dataProvider providerBackreferenceLikeValues
	 */
	public function testShouldKeepBackreferenceLikeAttributeValuesLiteral( $class ) {
		$html   = '<html><body><div class="' . $class . '"><p>Content</p></div></body></html>';
		$result = $this->processor->add_hashes( $html );

		$this->assertSame( 1, substr_count( $result, 'data-rocket-location-hash=' ) );
		$this->assertMatchesRegularExpression(
			'/^<html><body><div data-rocket-location-hash="[a-f0-9]{32}" class="' . preg_quote( $class, '/' ) . '"><p>Content<\/p>/',
			$result
		);
	}

	public function providerBackreferenceLikeValues() {
		return [
			'dollar zero'  => [ '$0 foo' ],
			'dollar brace' => [ '${0} foo' ],
			'backslash'    => [ '\\0 foo' ],
		];
	}
}
