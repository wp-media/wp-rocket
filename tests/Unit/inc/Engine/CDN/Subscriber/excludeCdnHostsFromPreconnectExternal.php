<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN;

use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Mockery;

/**
 * Test class covering Subscriber::exclude_cdn_hosts_from_preconnect_external
 *
 * @group CDN
 */
class Test_ExcludeCdnHostsFromPreconnectExternal extends TestCase {
	private $options;
	private $subscriber;
	private $cdn;

	public function setUp(): void {
		parent::setUp();

		// Mock dependencies if needed
		$this->options = Mockery::mock( Options_Data::class );
		$this->cdn     = Mockery::mock( CDN::class );

		// Create the instance of the class
		$this->subscriber = new Subscriber( $this->options, $this->cdn );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testExcludeCdnHostsFromPreconnectExternal( array $config, array $expected ): void {
		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->once()
			->andReturn( $config['cdn'] );

		if ( $config['cdn'] ) {
			$this->cdn->shouldReceive( 'get_cdn_urls' )
				->with( [ 'all' ] )
				->once()
				->andReturn( $config['cdn_hosts'] );
		}

		Functions\when( 'set_url_scheme')->alias( function ( $url ) {
			$url = trim( $url );
			if ( substr( $url, 0, 2 ) === '//' ) {
				$url = 'http:' . $url;
			}

			return preg_replace( '#^\w+://#', 'http://', $url );
		});

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

		// Call the method
		$result = $this->subscriber->exclude_cdn_hosts_from_preconnect_external( $config['exclusions'] );

		// Assert the result
		$this->assertSame( $expected, $result );
	}
}
