<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\LazyRenderContent\Frontend\Filter;

use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\Dom;
use WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\Processor\HelperTrait;
use WP_Rocket\Tests\Integration\TestCase;
use ReflectionClass;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\LazyRenderContent\Frontend\ProcessorHelperTrait::get_processed_tags()
 *
 * @group PerformanceHints
 */
class Test_lrcProcessedTagsFilter extends TestCase
{
	public function testShouldReturnAsExpected() {
		add_filter( 'rocket_lazy_render_content_processed_tags', function( $tags ) {
			$tags[]= 'h2';
			$tags[]= 'h1';
			$tags[]= 'li';

			return $tags;
		});

		$dom      = new Dom();
		$instance = new ReflectionClass( $dom );
		$method   = $instance->getMethod( 'get_processed_tags' );

		$method->setAccessible(true);
		$result = $method->invoke( $dom );

		$expected = [ 'DIV', 'MAIN', 'FOOTER', 'SECTION', 'ARTICLE', 'HEADER', 'H2', 'H1', 'LI' ];

		$this->assertSame( $expected, $result );
	}
}
