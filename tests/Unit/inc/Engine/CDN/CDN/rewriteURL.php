<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;

/**
 * @covers \WP_Rocket\Engine\CDN\CDN::rewrite_url
 * @group  CDN
 */
class Test_RewriteURL extends TestCase {
	private $cdn;
	private $options;

	public function setUp() {
		parent::setUp();

		Functions\when( 'site_url' )->justReturn( 'http://example.org' );

		$this->options = Mockery::mock( Options_Data::class );
		$this->cdn     = new CDN( $this->options );
	}
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRewriteURLWithCDNURL( $url, $expected ) {
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) use ( $expected ) {
			$scheme = parse_url( $expected, PHP_URL_SCHEME );
			if ( ! $scheme ) {
				return 'http://' . $url;
			}

			return $url;
		} );
		Functions\when( 'rocket_remove_url_protocol' )->alias( function( $url ) {
			return str_replace( [ 'http://', 'https://' ], '', $url );
		} );

		$options = [
			'cdn' => [
				'default' => 0,
				'value'   => 1,
			],
			'cdn_cnames' => [
				'default' => [],
				'value' => [
					'https://cdn.example.org',
				],
			],
			'cdn_zone' => [
				'default' => [],
				'value' => [
					'all',
				],
			],
			'cdn_reject_files' => [
				'default' => [],
				'value' => [
					'/wp-content/uploads/post.css',
				],
			],
		];

		foreach ( $options as $key => $option ) {
			$this->options->shouldReceive( 'get' )
				->with( $key, $option['default'] )
				->andReturn( $option['value'] );
		}

		$this->assertSame(
			$expected,
			$this->cdn->rewrite_url( $url )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rewriteUrl' );
	}
}
