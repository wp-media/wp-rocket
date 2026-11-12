<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::async_css
 * @group  CriticalPath
 */
class Test_AsyncCss extends TestCase {
	use SubscriberTrait;

	public function setUp(): void {
		parent::setUp();

		$this->setUpTests();
	}

	/**
	 * @dataProvider providerSpecialCharacters
	 */
	public function testShouldKeepSpecialCharactersLiteral( $value ) {
		$this->constants['DONOTROCKETOPTIMIZE'] = false;

		Functions\when( 'is_rocket_post_excluded_option' )->justReturn( false );
		Functions\when( 'wp_parse_url' )->alias( 'parse_url' );

		$this->options->shouldReceive( 'get' )->with( 'async_css', 0 )->andReturn( 1 );
		$this->options->shouldReceive( 'get' )->with( 'critical_css', '' )->andReturn( '' );
		$this->critical_css->shouldReceive( 'get_current_page_critical_css' )->andReturn( 'body{}' );
		$this->critical_css->shouldReceive( 'get_exclude_async_css' )->andReturn( [] );

		$buffer = '<html><head><link rel="stylesheet" href="http://example.org/style.css" data-test="' . $value . '"></head><body></body></html>';

		$result = $this->subscriber->async_css( $buffer );

		$this->assertSame( 2, substr_count( $result, 'data-test="' . $value . '"' ) ); // Preload link and noscript copy.
		$this->assertSame( 1, substr_count( $result, 'rel="preload"' ) );
	}

	public function providerSpecialCharacters() {
		return [
			'dollar one'   => [ '$1 foo' ],
			'dollar brace' => [ '${1} foo' ],
			'backslash'    => [ '\\1 foo' ],
		];
	}
}
