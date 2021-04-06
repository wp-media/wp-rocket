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

		if ( $config['logged-in'] ?? false ) {
			$user = $this->factory->user->create();
			wp_set_current_user( $user );
		}

		add_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'set_cached_user' ] );

		$this->config_data['api-response'] = $mockApiResponse;
		add_filter( 'pre_http_request', [ $this, 'set_api_response' ] );

		$container           = apply_filters( 'rocket_container', null );
		$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

		/**
		 * @type string $url             The page URL.
		 * @type string $css             The page used css.
		 * @type array  $unprocessed_css The page unprocessed CSS list.
		 * @type int    $retries         No of automatically retries for generating the unused css.
		 * @type bool   $is_mobile       Is mobile page.
		 */
			$rucss_usedcss_query->add_item( [
				'url'             => 'http://example.org/' . $GLOBALS['wp']->request,
				'css'             => 'h1{color:red;}',
				'unprocessedcss' => wp_json_encode([]),
				'retries'         => 3,
				'is_mobile'       => false,
			] );

		$this->assertEquals( $expected, apply_filters( 'rocket_buffer', $config['html'] ) );
	}

	public function set_rucss_option() {
		return $this->config_data['rucss-enabled'] ?? true;
	}

	public function set_cached_user() {
		return $this->config_data['logged-in-cache'] ?? false;
	}

	public function set_api_response() {
		return $this->config_data['api-response'];
	}
}
