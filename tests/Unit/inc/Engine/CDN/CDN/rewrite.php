<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;

/**
 * Test class covering \WP_Rocket\Engine\CDN\CDN::rewrite
 * @group  CDN
 */
class Test_Rewrite extends TestCase {
	private $cdn;
	private $options;

	public function setUp() : void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->cdn     = new CDN( $this->options );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRewriteURLToCDN( $home_url, $original, $expected ) {
		Functions\when( 'content_url' )->justReturn( "{$home_url}/wp-content/" );
		Functions\when( 'includes_url' )->justReturn( "{$home_url}/wp-includes/" );
		Functions\when( 'wp_upload_dir' )->justReturn( "{$home_url}/wp-content/uploads/" );
		Functions\when( 'home_url' )->justReturn( $home_url );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
			if ( strpos( $url, 'http://' ) !== false || strpos( $url, 'https://' ) !== false ) {
				return $url;
			}

			if ( substr( $url, 0, 2 ) === '//' ) {
				return 'http:' . $url;
			}

			return 'http://' . $url;
		} );

		$options = [
			'cdn' => [
				'default' => 0,
				'value'   => 1,
			],
			'cdn_cnames' => [
				'default' => [],
				'value' => [
					'http://cdn.example.org',
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
				'value' => [],
			],
		];

		foreach ( $options as $key => $option ) {
			$this->options->shouldReceive( 'get' )
				->with( $key, $option['default'] )
				->andReturn( $option['value'] );
		}

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $this->cdn->rewrite( $original ) )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rewrite' );
	}
}
