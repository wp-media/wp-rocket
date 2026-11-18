<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;

/**
 * Test class covering \WP_Rocket\Engine\CDN\CDN::rewrite_url_if_allowed
 *
 * @group CDN
 */
class Test_RewriteUrlIfAllowed extends TestCase {
	private $cdn;
	private $options;

	public function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->cdn     = new CDN( $this->options );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRewriteUrlOnlyWhenAllowed( $config, $url, $expected ) {
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content/' );
		Functions\when( 'includes_url' )->justReturn( 'http://example.org/wp-includes/' );
		Functions\when( 'wp_upload_dir' )->justReturn( [ 'baseurl' => 'http://example.org/wp-content/uploads' ] );
		Functions\when( 'rocket_add_url_protocol' )->alias(
			function ( $url ) {
				if ( 0 === strpos( $url, 'http' ) ) {
					return $url;
				}

				return 0 === strpos( $url, '//' ) ? 'http:' . $url : 'http://' . $url;
			}
		);
		Functions\when( 'rocket_remove_url_protocol' )->alias(
			function ( $url ) {
				return str_replace( [ 'http://', 'https://' ], '', $url );
			}
		);

		Filters\expectApplied( 'rocket_cdn_relative_paths' )
			->once()
			->andReturn( $config['relative_paths'] );

		$options = [
			'cdn'              => [ 0, 1 ],
			'cdn_cnames'       => [ [], [ 'https://cdn.example.org' ] ],
			'cdn_zone'         => [ [], [ 'all' ] ],
			'cdn_reject_files' => [ [], [] ],
		];

		foreach ( $options as $key => $option ) {
			$this->options->shouldReceive( 'get' )
				->with( $key, $option[0] )
				->andReturn( $option[1] );
		}

		$this->assertSame( $expected, $this->cdn->rewrite_url_if_allowed( $url ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rewriteUrlIfAllowed' );
	}
}
