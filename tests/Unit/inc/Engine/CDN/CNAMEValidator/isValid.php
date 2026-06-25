<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CNAMEValidator;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CDN\CNAMEValidator;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\CNAMEValidator::is_valid
 *
 * @group CDN
 */
class Test_IsValid extends TestCase {

	private $validator;

	protected function setUp(): void {
		parent::setUp();

		$this->validator = new CNAMEValidator();

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

		Functions\when( 'wp_parse_url' )->alias(
			function ( $url, $component = -1 ) {
				return parse_url( $url, $component );
			}
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedResult( array $config, array $expected ): void {
		$transient_key = 'rocket_cname_valid_' . md5( $config['cname_url'] );

		Functions\expect( 'get_transient' )
			->with( $transient_key )
			->andReturn( $config['cached_value'] );

		if ( false === $config['cached_value'] ) {
			Functions\when( 'home_url' )->justReturn( 'https://example.com' );
			Functions\when( 'get_stylesheet_uri' )->justReturn( 'https://example.com/wp-content/themes/mytheme/style.css' );

			Functions\expect( 'wp_remote_head' )
				->with( 'https://cdn.example.com/wp-content/themes/mytheme/style.css', [ 'timeout' => 5 ] )
				->andReturn( $config['response'] );

			Functions\expect( 'is_wp_error' )
				->with( $config['response'] )
				->andReturn( $config['is_wp_error'] );

			if ( ! $config['is_wp_error'] ) {
				Functions\expect( 'wp_remote_retrieve_response_code' )
					->with( $config['response'] )
					->andReturn( $config['response_code'] );
			}

			Functions\expect( 'set_transient' )
				->with( $transient_key, $expected['transient_value'], DAY_IN_SECONDS );
		}

		$this->assertSame( $expected['result'], $this->validator->is_valid( $config['cname_url'] ) );
	}
}
