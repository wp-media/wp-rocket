<?php

namespace WP_Rocket\Tests\Unit\inc\Addon\WebP\Subscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Subscriber as CDNSubscriber;
use WP_Rocket\Addon\WebP\Subscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Addon\WebP\Subscriber::convert_to_webp
 *
 * @group WebP
 */
class Test_ConvertToWebp extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Addon/WebP/Subscriber/convertToWebp.php';

	private $subscriber;
	private $options;
	private $cdn;

	public function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->cdn = Mockery::mock( CDNSubscriber::class );
		$this->subscriber = new Subscriber(
			$this->options,
			Mockery::mock( Options::class ),
			$this->cdn,
			''
		);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $config, $original, $expected ) {
		$this->options->shouldReceive( 'get' )
			->with( 'cache_webp', 0 )
			->andReturn( $config['webp'] );


		Functions\when('wp_upload_dir')->justReturn([
			'basedir' => '/Internal/path/to/root/wp-content/uploads/',
			'baseurl' => 'http://example.org/custom-folder/'
		]);

		Filters\expectApplied( 'rocket_disable_webp_cache' )
			->atMost()
			->once()
			->andReturn( $config['filter_disable'] );

		Functions\when( 'apache_request_headers' )
			->alias( function() use ( $config ) {
				return [
					'Accept' => $config['headers'],
				];
			} );

		Filters\expectApplied( 'rocket_file_extensions_for_webp' )
			->atMost()
			->once()
			->andReturn( $config['filter_ext'] );

		Filters\expectApplied( 'rocket_attributes_for_webp' )
			->atMost()
			->once()
			->andReturn( $config['filter_attr'] );

		$this->stubWpParseUrl();
		Functions\when( 'site_url' )->justReturn( 'http://example.org' );
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content/' );

		$this->cdn->shouldReceive( 'get_cdn_hosts' )
			->atMost()
			->once()
			->andReturn( [ 'cdn-example.net' ] );

		$this->assertSame(
			$expected,
			$this->subscriber->convert_to_webp( $original )
		);
	}
}
