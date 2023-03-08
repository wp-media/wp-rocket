<?php

namespace WP_Rocket\Tests\Integration\Addon\WebP\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Addon\WebP\Subscriber::convert_to_webp
 * @group WebP
 */
class Test_ConvertToWebp extends TestCase {
	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_cache_webp', [ $this, 'return_1' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider noMatchProvider
	 */
	public function testShouldReturnHtmlWithCommentWhenNoMatches( $original, $expected ) {
		add_filter( 'pre_get_rocket_option_cache_webp', [ $this, 'return_1' ] );

		Functions\when( 'apache_request_headers' )
			->alias( function() {
				return [
					'Accept' => 'webp',
				];
			}
		);

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_buffer', $original )
		);
	}

	public function noMatchProvider() {
		return $this->getTestData( __DIR__, 'convert-to-webp-nomatch' );
	}
}
