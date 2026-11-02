<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\CDN::get_cdn_urls
 *
 * Task 9.3 regression coverage: configured, zone-matching CNAME hosts are
 * returned unfiltered — the CNAMEValidator reachability probe (and the
 * Context dependency it relied on) has been removed from this method
 * entirely, so no host is excluded based on reachability anymore.
 *
 * @group CDN
 */
class Test_GetCdnUrls extends TestCase {

	private $cdn;
	private $options;

	public function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->cdn      = new CDN( $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnUnfilteredCdnUrls( array $config, array $expected ): void {
		$this->options->shouldReceive( 'get' )
			->with( 'cdn_cnames', [] )
			->andReturn( $config['cdn_cnames'] );

		if ( ! empty( $config['cdn_cnames'] ) ) {
			$this->options->shouldReceive( 'get' )
				->with( 'cdn_zone', [] )
				->andReturn( $config['cdn_zone'] );
		}

		Filters\expectApplied( 'rocket_cdn_cnames' )->andReturnFirstArg();

		$this->assertSame( $expected['cdn_urls'], $this->cdn->get_cdn_urls( $config['zones'] ) );
	}
}
