<?php
namespace WP_Rocket\Tests\Unit\Addons\Cloudflare;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use Brain\Monkey\Functions;

/**
 * @covers WP_Rocket\Addons\Cloudflare\Cloudflare::has_page_rule
 *
 * @group Cloudflare
 */
class Test_HasPageRule extends TestCase {

	protected function setUp() {
		parent::setUp();

		$this->mockCommonWpFunctions();

		if ( ! defined('WEEK_IN_SECONDS') ) {
			define('WEEK_IN_SECONDS', 7 * 24 * 60 * 60);
		}

		if ( ! defined('WP_ROCKET_VERSION') ) {
			define('WP_ROCKET_VERSION', '3.5');
		}
	}

	/**
	 * Test Cloudflare has page rules with cached invalid transient.
	 */
	public function testHasRuleWithInvalidCredentials() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( $wp_error );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
		$has_page_rule = $cloudflare->has_page_rule( 'cache_everything' );

		$this->assertEquals(
			$wp_error,
			$has_page_rule
		);
	}

	/**
	 * Test Cloudflare has page rules with exception.
	 */
	public function testHasRuleWithException() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
		$cloudflare_facade_mock->shouldReceive('list_pagerules')->andThrow( new \Exception() );
		$has_page_rule = $cloudflare->has_page_rule( 'cache_everything' );

		$this->assertEquals(
			new \WP_Error(),
			$has_page_rule
		);
	}


	/**
	 * Test Cloudflare has page rules with no success.
	 */
	public function testHasRuleWithNoSuccess() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		Functions\when( 'wp_sprintf_l' )->justReturn( '' );
		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
		$cf_page_rule = json_decode('{"success":false,"errors":[{"code":7003,"message":"Could not route to \/zones\/ZONE_ID, perhaps your object identifier is invalid?"},{"code":7000,"message":"No route for that URI"}],"messages":[],"result":null}');
		$cloudflare_facade_mock->shouldReceive('list_pagerules')->andReturn( $cf_page_rule );
		$has_page_rule = $cloudflare->has_page_rule( 'cache_everything' );

		$this->assertEquals(
			new \WP_Error(),
			$has_page_rule
		);
	}

	/**
	 * Test Cloudflare has page rules with success but no page rule.
	 */
	public function testHasRuleWithSuccessButNoPageRule() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );

		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
		$cf_page_rule = json_decode('{"result":[{"id":"","targets":[{"target":"url","constraint":{"operator":"matches","value":""}}],"actions":[{"id":"cache_level","value":"bypass"}],"priority":3,"status":"active","created_on":"","modified_on":""},{"id":"","targets":[{"target":"url","constraint":{"operator":"matches","value":""}}],"actions":[{"id":"cache_level","value":""}],"priority":2,"status":"active","created_on":"","modified_on":""}],"success":true,"errors":[],"messages":[]}');
		Functions\when( 'wp_json_encode' )->justReturn( json_encode( $cf_page_rule ) );
		$cloudflare_facade_mock->shouldReceive('list_pagerules')->andReturn( $cf_page_rule );
		$has_page_rule = $cloudflare->has_page_rule( 'cache_everything' );

		$this->assertEquals(
			false,
			$has_page_rule
		);
	}

	/**
	 * Test Cloudflare has page rules with success and page rule.
	 */
	public function testHasRuleWithSuccessAndPageRule() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error               = $mocks['wp_error'];

		// The Cloudflare constructor run with transient set as WP_Error.
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
		$cf_page_rule = json_decode('{"result":[{"id":"","targets":[{"target":"url","constraint":{"operator":"matches","value":""}}],"actions":[{"id":"cache_level","value":"bypass"}],"priority":3,"status":"active","created_on":"","modified_on":""},{"id":"","targets":[{"target":"url","constraint":{"operator":"matches","value":""}}],"actions":[{"id":"cache_level","value":"cache_everything"}],"priority":2,"status":"active","created_on":"","modified_on":""}],"success":true,"errors":[],"messages":[]}');
		Functions\when( 'wp_json_encode' )->justReturn( json_encode( $cf_page_rule ) );
		$cloudflare_facade_mock->shouldReceive('list_pagerules')->andReturn( $cf_page_rule );
		$has_page_rule = $cloudflare->has_page_rule( 'cache_everything' );

		$this->assertEquals(
			true,
			$has_page_rule
		);
	}

	/**
	 * Get the mocks required by Cloudflare’s constructor.
	 *
	 * @since  3.5
	 * @author Soponar Cristina
	 * @access private
	 *
	 * @param integer $do_cloudflare      - Value to return for $options->get( 'do_cloudflare' ).
	 * @param string  $cloudflare_email   - Value to return for $options->get( 'cloudflare_email' ).
	 * @param string  $cloudflare_api_key - Value to return for $options->get( 'cloudflare_api_key' ).
	 * @param string  $cloudflare_zone_id - Value to return for $options->get( 'cloudflare_zone_id' ).
	 * @return Mock Options_Data mock
	 */
	private function getConstructorMocks( $do_cloudflare = 1, $cloudflare_email = '',  $cloudflare_api_key = '', $cloudflare_zone_id = '') {
		$options = $this->createMock('WP_Rocket\Admin\Options_Data');
		$map     = [
			[
				'do_cloudflare',
				'',
				$do_cloudflare,
			],
			[
				'cloudflare_email',
				null,
				$cloudflare_email,
			],
			[
				'cloudflare_api_key',
				null,
				$cloudflare_api_key,
			],
			[
				'cloudflare_zone_id',
				null,
				$cloudflare_zone_id,
			],
		];
		$options->method('get')->will( $this->returnValueMap( $map ) );

		$facade   = \Mockery::mock( \WP_Rocket\Addons\Cloudflare\CloudflareFacade::class );
		$wp_error = \Mockery::mock( \WP_Error::class );

		$mocks = [
			'options'  => $options,
			'facade'   => $facade,
			'wp_error' => $wp_error,
		];
		return $mocks;
	}
}
