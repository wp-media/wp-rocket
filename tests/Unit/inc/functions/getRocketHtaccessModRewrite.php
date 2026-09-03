<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::get_rocket_htaccess_mod_rewrite
 *
 * @group Functions
 */
class Test_GetRocketHtaccessModRewrite extends TestCase {
	/**
	 * ABSPATH value for the current test case.
	 *
	 * @var string
	 */
	protected $test_abs_path = '';

	/**
	 * WP_ROCKET_CACHE_PATH value for the current test case.
	 *
	 * @var string
	 */
	protected $test_cache_path = '';

	/**
	 * Returns the requested constant, using test-case values for ABSPATH and WP_ROCKET_CACHE_PATH.
	 *
	 * @param string     $constant_name Constant name.
	 * @param mixed|null $default       Optional default value.
	 * @return mixed
	 */
	public function getConstant( $constant_name, $default = null ) {
		if ( 'ABSPATH' === $constant_name ) {
			return $this->test_abs_path ?: parent::getConstant( $constant_name, $default );
		}

		if ( 'WP_ROCKET_CACHE_PATH' === $constant_name ) {
			return $this->test_cache_path ?: parent::getConstant( $constant_name, $default );
		}

		return parent::getConstant( $constant_name, $default );
	}

	/**
	 * Sets up the test environment.
	 */
	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'wp_normalize_path' )->alias(
			function ( $path ) {
				$path = str_replace( '\\', '/', $path );
				$path = preg_replace( '|(?<=.)/+|', '/', $path );

				if ( ':' === substr( $path, 1, 1 ) ) {
					$path = ucfirst( $path );
				}

				return $path;
			}
		);
		Functions\when( 'is_multisite' )->justReturn( false );
		Functions\when( 'get_locale' )->justReturn( 'en_US' );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );
		Functions\when( 'site_url' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_extract_url_component' )->justReturn( '' );
		Functions\when( 'trailingslashit' )->alias(
			function ( $value ) {
				return rtrim( $value, '/\\' ) . '/';
			}
		);
		Functions\when( 'sanitize_text_field' )->returnArg( 1 );
		Functions\when( 'wp_unslash' )->returnArg( 1 );
		Functions\when( 'get_rocket_htaccess_ssl_rewritecond' )->justReturn( '' );
		Functions\when( 'rocket_get_webp_rewritecond' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_reject_cookies' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_reject_uri' )->justReturn( '' );
		Functions\when( 'is_rocket_cache_mobile' )->justReturn( false );
		Functions\when( 'get_rocket_htaccess_mobile_rewritecond' )->justReturn( '' );
		Functions\when( 'get_rocket_cache_reject_ua' )->justReturn( '' );
		Functions\when( 'apply_filters' )->alias(
			function ( $tag, $value ) {
				if ( 'rocket_force_gzip_htaccess_rules' === $tag ) {
					return false;
				}

				return $value;
			}
		);
	}

	/**
	 * Tears down the test environment.
	 */
	protected function tearDown(): void {
		$this->test_abs_path   = '';
		$this->test_cache_path = '';
		unset( $_SERVER['DOCUMENT_ROOT'] );

		parent::tearDown();
	}

	/**
	 * Tests that the generated rewrite rules contain the expected cache paths.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected substrings in the generated rules.
	 */
	public function testShouldGenerateCorrectRewriteRules( $config, $expected ) {
		$this->test_abs_path   = $config['abspath'];
		$this->test_cache_path = $config['cache_path'];

		if ( null !== $config['document_root'] ) {
			$_SERVER['DOCUMENT_ROOT'] = $config['document_root'];
		} else {
			unset( $_SERVER['DOCUMENT_ROOT'] );
		}

		$rules = get_rocket_htaccess_mod_rewrite();

		foreach ( $expected as $substring ) {
			$this->assertStringContainsString( $substring, $rules );
		}
	}
}
