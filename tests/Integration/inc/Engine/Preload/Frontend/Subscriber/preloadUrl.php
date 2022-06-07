<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Frontend\Subscriber;

use WP_Error;
use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\ASTrait;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Frontend\Subscriber::preload_url
 * @group  Preload
 */
class Test_PreloadUrl extends AdminTestCase
{
	use ASTrait;

	protected $mobile_cache;

	public static function set_up_before_class()
	{
		parent::set_up_before_class();
		self::installFresh();

	}

	public static function tear_down_after_class()
	{
		self::uninstallAll();
		parent::tear_down_after_class();
	}

	public function setUp(): void
	{
		parent::setUp();
		add_filter('pre_get_rocket_option_cache_mobile', [$this, 'mobile_cache']);
	}

	public function tearDown(): void
	{
		parent::tearDown();
		remove_filter('pre_get_rocket_option_cache_mobile', [$this, 'mobile_cache']);
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {

		$this->mobile_cache = $config['mobile_cache'];


		self::addCache($config['existing_job']);
		$this->configureRequest($config);
		$this->configureMobileRequest($config);

		do_action('rocket_preload_job_preload_url', $config['url']);

		$this->assertTrue(self::cacheFound($expected));
	}

	protected function configureRequest($config) {
		if ( ! isset( $config['process_generate'] ) ) {
			return;
		}

		if ( ! empty( $config['process_generate']['is_wp_error'] ) ) {
			Functions\expect( 'wp_remote_get' )
				->once()
				->with(
					$config['url'] . '/',
					$config['config']
				)
				->andReturn( new WP_Error( 'error', 'error_data' ) );
		} else {
			$message = $config['process_generate']['response'];
			Functions\expect( 'wp_remote_get' )
				->once()
				->with(
					$config['url'] . '/',
					$config['config']
				)
				->andReturn( [ 'body' => $message, 'response' => ['code' => 200 ]] );
		}
	}

	protected function configureMobileRequest($config) {
		if ( ! isset( $config['process_mobile_generate'] ) ) {
			return;
		}

		if ( ! empty( $config['process_mobile_generate']['is_wp_error'] ) ) {
			Functions\expect( 'wp_remote_get' )
				->once()
				->with(
					$config['url'] . '/',
					$config['config_mobile']
				)
				->andReturn( new WP_Error( 'error', 'error_data' ) );
		} else {
			$message = $config['process_mobile_generate']['response'];
			Functions\expect( 'wp_remote_get' )
				->once()
				->with(
					$config['url'] . '/',
					$config['config_mobile']
				)
				->andReturn( [ 'body' => $message, 'response' => ['code' => 200 ]] );
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preloadUrl' );
	}

	public function mobile_cache() {
		return $this->mobile_cache;
	}
}
