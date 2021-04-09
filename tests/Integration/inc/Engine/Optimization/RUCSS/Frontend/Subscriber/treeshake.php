<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Frontend\APIClient;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::treeshake
 *
 * @group  RUCSS
 */
class Test_Treeshake extends FilesystemTestCase {
	use DBTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Frontend/Subscriber/treeshake.php';

	private $config_data = [];

	public static function setUpBeforeClass(): void {
		self::installFresh();

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		self::uninstallAll();
	}

	public function setUp(): void {

		$GLOBALS['wp'] = (object) [
			'query_vars'        => [],
			'request'           => 'home',
			'public_query_vars' => [
				'embed',
			],
		];

		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'treeshake' );
	}

	public function tearDown() {
		unset( $GLOBALS['wp'] );

		remove_filter( 'pre_get_rocket_option_rucss', [ $this, 'set_rucss_option' ] );
		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'set_cpcss_option' ] );
		remove_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'set_cached_user' ] );
		remove_filter( 'pre_http_request', [ $this, 'set_api_response' ] );

		self::truncateUsedCssTable();

		parent::tearDown();

		$this->restoreWpFilter( 'rocket_buffer' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $mockApiResponse, $expected ): void {
		$this->config_data = $config;

		$this->donotrocketoptimize = isset( $config['no-optimize'] ) ? $config['no-optimize'] : false;

		if ( isset( $config['bypass'] ) ) {
			$GLOBALS['wp']->query_vars['nowprocket'] = $config['bypass'];
		}

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		add_filter( 'pre_get_rocket_option_async_css', [ $this, 'set_cpcss_option' ] );

		if ( $config['logged-in'] ?? false ) {
			$user = $this->factory->user->create();
			wp_set_current_user( $user );
		}

		add_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'set_cached_user' ] );

		$this->config_data['api-response'] = $mockApiResponse;
		add_filter( 'pre_http_request', [ $this, 'set_api_response' ] );

		if ( isset( $config['used-css-row-contents'] ) ) {
			$container           = apply_filters( 'rocket_container', null );
			$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

			$rucss_usedcss_query->add_item( $config['used-css-row-contents'] );
		}

		$this->assertEquals(
			$this->format_the_html( $expected ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $config['html'] ) )
		);

		if ( isset( $this->config_data['cpcss-enabled'] ) && true === $this->config_data['cpcss-enabled'] ) {
			$this->assertTrue(
				$this->filesystem->is_readable('public/wp-content/cache/used-css/2664e301f9920094b0c21e1378f8702a.css' )
			);
			$this->assertEquals(
				$config['shaked-css'],
				$this->filesystem->get_contents('public/wp-content/cache/used-css/2664e301f9920094b0c21e1378f8702a.css' )
			);
		}
	}

	public function set_rucss_option() {
		return $this->config_data['rucss-enabled'] ?? true;
	}

	public function set_cpcss_option() {
		return $this->config_data['cpcss-enabled'] ?? false;
	}

	public function set_cached_user() {
		return $this->config_data['logged-in-cache'] ?? false;
	}

	public function set_api_response() {
		return $this->config_data['api-response'];
	}
}
