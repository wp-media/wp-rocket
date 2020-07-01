<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\AsyncCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\AsyncCSS::build_onload
 *
 * @group  CriticalPath
 * @group  AsyncCSS
 * @group  DOM
 */
class Test_BuildOnload extends TestCase {
	private $dom;
	private $instance;
	private $css_links;

	private function setupTest( $html ) {
		Functions\expect( 'get_current_blog_id' )->andReturn( 1 );
		Functions\when( 'home_url' )->justReturn( 'http://example.com/' );

		$options      = Mockery::mock( Options_Data::class, [ [] ] );
		$critical_css = Mockery::mock( CriticalCSS::class,
			[
				Mockery::mock( CriticalCSSGeneration::class ),
				$options,
				null,
			]
		);
		$options->shouldReceive( 'get' )->with( 'async_css', 0 )->andReturn( true );
		$critical_css->shouldReceive( 'get_current_page_critical_css' )->andReturn( 'some css' );
		Functions\expect( 'is_rocket_post_excluded_option' )->with( 'async_css' )->andReturn( false );

		$this->instance  = AsyncCSS::from_html( $critical_css, $options, $html );
		$this->dom       = $this->getNonPublicPropertyValue( 'dom', AsyncCSS::class, $this->instance );
		$this->css_links = $this->dom->query( '//link[@type="text/css"]' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAsyncCss( $html, $expected_onload, $expected_html ) {
		$this->setupTest( $html );
		$build_onload = $this->get_reflective_method( 'build_onload', AsyncCSS::class );

		foreach ( $this->css_links as $index => $css ) {
			$build_onload->invoke( $this->instance, $css );
			$this->assertEquals( $expected_onload[ $index ], $css->getAttribute( 'onload' ) );
		}

		$this->assertEquals(
			$this->format_the_html( $expected_html ),
			$this->format_the_html( $this->dom->saveHtml() )
		);
	}
}
