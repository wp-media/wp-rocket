<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Addons\CloudflareSubscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;
use Brain\Monkey\Functions;

class TestPurgeCache extends TestCase {
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
	 * Test should not Purge Cloudflare if Cloudflare addon is disabled.
	 */
	public function testShouldNotPurgeWhenCloudflareIsDisabled() {
		$mocks = $this->getConstructorMocks( 0, '', '', '' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldNotReceive('purge_cloudflare');

		$_GET['_wpnonce'] = 'nonce';
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'sanitize_key' )->justReturn( '' );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->purge_cache_no_die();
	}

	/**
	 * Test should not Purge Cloudflare if Cloudflare addon is enabled but user has no permissions.
	 */
	public function testShouldNotPurgeWhenCloudflareIsEnabledNoUserPermission() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldNotReceive('purge_cloudflare');

		$_GET['_wpnonce'] = '';
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'sanitize_key' )->justReturn( '' );
		Functions\when( 'current_user_can' )->justReturn( false );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );
		$cloudflare_subscriber->purge_cache_no_die();
	}

	/**
	 * Test should Purge Cloudflare and return error.
	 */
	public function testShouldPurgeWithError() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		Functions\when( 'is_wp_error' )->justReturn( true );

		$wp_error   = \Mockery::mock( \WP_Error::class );
		$wp_error->shouldReceive('get_error_message')->andReturn( 'Error!' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('purge_cloudflare')->andReturn( $wp_error );

		$_GET['_wpnonce'] = '';
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'sanitize_key' )->justReturn( '' );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$cf_purge_result = [
			'result'  => 'error',
			// translators: %s = CloudFare API return message.
			'message' => sprintf( __( '<strong>WP Rocket:</strong> %s', 'rocket' ), 'Error!' ),
		];

		Functions\expect( 'set_transient' )
			->once()
			->with('1_cloudflare_purge_result', $cf_purge_result );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$cloudflare_subscriber->purge_cache_no_die();
	}

	/**
	 * Test should Purge Cloudflare and return success.
	 */
	public function testShouldPurgeWithSuccess() {
		$mocks = $this->getConstructorMocks( 1, '', '', '' );

		$cloudflare = \Mockery::mock(\WP_Rocket\Addons\Cloudflare\Cloudflare::class);
		$cloudflare->shouldReceive('purge_cloudflare')->andReturn( true );

		$_GET['_wpnonce'] = '';
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'sanitize_key' )->justReturn( '' );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );

		$cf_purge_result = [
			'result'  => 'success',
			'message' => __( '<strong>WP Rocket:</strong> Cloudflare cache successfully purged.', 'rocket' ),
		];

		Functions\expect( 'set_transient' )
			->once()
			->with('1_cloudflare_purge_result', $cf_purge_result );

		$cloudflare_subscriber = new CloudflareSubscriber( $cloudflare, $mocks['options_data'], $mocks['options'] );

		$cloudflare_subscriber->purge_cache_no_die();
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
	 * @return array                      - Array of Mocks
	 */
	private function getConstructorMocks( $do_cloudflare = 1, $cloudflare_email = '',  $cloudflare_api_key = '', $cloudflare_zone_id = '') {
		$options      = $this->createMock('WP_Rocket\Admin\Options');
		$options_data = $this->createMock('WP_Rocket\Admin\Options_Data');
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
		$options_data->method('get')->will( $this->returnValueMap( $map ) );

		$mocks = [
			'options_data' => $options_data,
			'options'      => $options,
		];

		return $mocks;
	}
}
