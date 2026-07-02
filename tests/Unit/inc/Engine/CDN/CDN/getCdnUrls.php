<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\CNAMEValidator;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\CDN::get_cdn_urls with CNAME validation
 *
 * @group CDN
 */
class Test_GetCdnUrls extends TestCase {

	private $cdn;
	private $options;
	private $cname_validator;

	public function setUp(): void {
		parent::setUp();

		$this->options         = Mockery::mock( Options_Data::class );
		$this->cname_validator = Mockery::mock( CNAMEValidator::class );
		$context               = Mockery::mock( Context::class );
		$context->shouldReceive( 'is_rocketcdn' )->andReturn( false );
		$this->cdn             = new CDN( $this->options, $this->cname_validator, $context );

		Functions\when( 'rocket_add_url_protocol' )->alias(
			function ( $url ) {
				if ( str_contains( $url, 'http://' ) || str_contains( $url, 'https://' ) ) {
					return $url;
				}
				if ( str_starts_with( $url, '//' ) ) {
					return 'https:' . $url;
				}
				return 'https://' . $url;
			}
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnFilteredCdnUrls( array $config, array $expected ): void {
		$this->options->shouldReceive( 'get' )
			->with( 'cdn_cnames', [] )
			->andReturn( $config['cdn_cnames'] );

		if ( ! empty( $config['cdn_cnames'] ) ) {
			$this->options->shouldReceive( 'get' )
				->with( 'cdn_zone', [] )
				->andReturn( $config['cdn_zone'] );
		}

		Filters\expectApplied( 'rocket_cdn_cnames' )->andReturnFirstArg();

		foreach ( $config['validator_map'] as $url => $is_valid ) {
			$this->cname_validator->shouldReceive( 'is_valid' )
				->with( $url )
				->andReturn( $is_valid );
		}

		$this->assertSame( $expected['cdn_urls'], $this->cdn->get_cdn_urls( $config['zones'] ) );
	}
}
