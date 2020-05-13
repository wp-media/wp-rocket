<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Media\WebpSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\Media\Webp_Subscriber::convert_to_webp
 * @group Subscriber
 * @group WebP
 */
class Test_ConvertToWebp extends TestCase {
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

		$this->assertSame( $expected, apply_filters( 'rocket_buffer', $original ) );

		remove_filter( 'pre_get_rocket_option_cache_webp', [ $this, 'return_1' ] );
	}

	public function noMatchProvider() {
		return $this->getTestData( __DIR__, 'convert-to-webp-nomatch' );
	}
}