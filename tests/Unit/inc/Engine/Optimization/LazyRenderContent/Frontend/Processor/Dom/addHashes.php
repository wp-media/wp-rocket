<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\LazyRenderContent\Frontend\Processor\Dom;

use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\Dom;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group LRC
 */
class TestAddHashes extends TestCase {
	private $processor;

	public function set_up() {
		parent::set_up();

		$this->processor = new Dom();
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
}
