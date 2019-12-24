<?php
namespace WP_Rocket\Tests\Unit\Addons\Cloudflare;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Addons\Cloudflare\Cloudflare;
use Brain\Monkey\Functions;

class TestGetInstance extends TestCase {
	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
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
	 * Test Get Instance with Invalid Cloudflare credentials and no transient set.
	 */
	public function testGetInstanceWithInvalidCFCredentialsNoTransient() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error   = \Mockery::mock( \WP_Error::class );

		Functions\when( 'get_transient' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('is_api_keys_valid')->andReturn( $wp_error );
		Functions\expect( 'set_transient' )->once();
		Functions\when( 'is_wp_error' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
	}

	/**
	 * Test Get Instance with valid Cloudflare credentials and no transient set.
	 */
	public function testGetInstanceWithValidCFCredentialsNoTransient() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error   = \Mockery::mock( \WP_Error::class );

		Functions\when( 'get_transient' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('is_api_keys_valid')->andReturn( true );
		Functions\expect( 'set_transient' )->once();
		Functions\when( 'is_wp_error' )->justReturn( false );
		$cloudflare_facade_mock->shouldReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
	}

	/**
	 * Test Get Instance with invalid Cloudflare credentials and transient set.
	 */
	public function testGetInstanceWithInValidCFCredentialsAndTransient() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		$wp_error   = \Mockery::mock( \WP_Error::class );

		Functions\when( 'get_transient' )->justReturn( $wp_error );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
	}

	/**
	 * Test Get Instance with valid Cloudflare credentials and transient set.
	 */
	public function testGetInstanceWithValidCFCredentialsAndTransient() {
		$mocks = $this->getConstructorMocks( 1,  '',  '', '');

		$cloudflare_facade_mock = $mocks['facade'];
		Functions\when( 'get_transient' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('is_api_keys_valid');
		Functions\expect( 'set_transient' )->never();
		Functions\when( 'is_wp_error' )->justReturn( true );
		$cloudflare_facade_mock->shouldNotReceive('set_api_credentials');

		$cloudflare = new Cloudflare( $mocks['options'], $cloudflare_facade_mock );
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

		$facade = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\CloudflareFacade::class);

		$mocks = [
			'options' => $options,
			'facade'  => $facade,
		];
		return $mocks;
	}
}
