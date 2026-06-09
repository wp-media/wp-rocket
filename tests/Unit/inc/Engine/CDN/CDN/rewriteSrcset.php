<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;

/**
 * Test class covering \WP_Rocket\Engine\CDN\CDN::rewrite_srcset
 * @group  CDN
 */
class Test_RewriteSrcset extends TestCase {
	private $options;
	private $cdn;
	private $config;

	public function setUp() : void {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
			return 'http://' . $url;
		} );

		$this->options = Mockery::mock( Options_Data::class );
		$this->cdn     = new CDN( $this->options );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRewriteURLToCDN( $options ) {
		foreach ( $options as $key => $option ) {
			$this->options->shouldReceive( 'get' )
				->with( $key, $option['default'] )
				->andReturn( $option['value'] );
		}

		$this->assertSame(
			$this->format_the_html( $this->config['expected'] ),
			$this->format_the_html( $this->cdn->rewrite_srcset( $this->config['original'] ) )
		);
	}

	public function providerTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

	private function loadConfig() {
		$this->config = $this->getTestData( __DIR__, 'rewriteSrcset' );
	}
}
