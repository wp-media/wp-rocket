<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;

/**
 * Test class covering \WP_Rocket\Engine\CDN\CDN::rewrite_css_properties
 * @group  CDN
 */
class TestRewriteCSSProperties extends TestCase {
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
	public function testShouldRewriteCSSProperties( $original, $expected ) {
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
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
					'cdn.example.org',
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
			$expected,
			$this->cdn->rewrite_css_properties( $original )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rewriteCssProperties' );
	}
}
