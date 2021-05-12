<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Frontend\Subscriber::treeshake
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS::treeshake()
 *
 * @group  RUCSS
 */
class Test_Treeshake extends FilesystemTestCase {
	use DBTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Frontend/Subscriber/treeshake.php';

	private $config_data = [];

	private $resource_ids = [];

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

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'treeshake', 12 );
	}

	public function tearDown() {
		unset( $GLOBALS['wp'] );

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );
		remove_filter( 'pre_get_rocket_option_async_css', [ $this, 'set_cpcss_option' ] );
		remove_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'set_cached_user' ] );
		remove_filter( 'pre_http_request', [ $this, 'set_api_response' ] );
		remove_filter( 'pre_option_wp_rocket_prewarmup_stats', [ $this, 'return_prewarmup_stats' ] );

		$this->resource_ids = [];

		self::truncateUsedCssTable();

		parent::tearDown();

		$this->restoreWpFilter( 'rocket_buffer' );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $mockApiResponse, $expected ): void {
		$this->config_data = $config;

		$container                 = apply_filters( 'rocket_container', null );
		$rucss_resources_query     = $container->get( 'rucss_resources_query' );
		$this->donotrocketoptimize = isset( $config['no-optimize'] ) ? $config['no-optimize'] : false;

		if ( isset( $config['bypass'] ) ) {
			$GLOBALS['wp']->query_vars['nowprocket'] = $config['bypass'];
		}

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		if ( isset( $config['has_cpcss'] ) ) {
			$this->config_data['cpcss_option'] = $config['has_cpcss'];
			add_filter( 'pre_get_rocket_option_async_css', [ $this, 'set_cpcss_option' ] );
		}

		if ( $config['logged-in'] ?? false ) {
			$user = $this->factory->user->create();
			wp_set_current_user( $user );
		}

		add_filter( 'pre_get_rocket_option_cache_logged_user', [ $this, 'set_cached_user' ] );

		$this->config_data['api-response'] = $mockApiResponse;
		add_filter( 'pre_http_request', [ $this, 'set_api_response' ] );

		if ( isset( $config['used-css-row-contents'] ) ) {
			$rucss_usedcss_query = $container->get( 'rucss_used_css_query' );

			$rucss_usedcss_query->add_item( $config['used-css-row-contents'] );
		}

		if ( isset( $config['saved-resources'] ) ) {
			foreach ( $config['saved-resources'] as $resource ) {
				$rucss_resources_query->add_item(
					[
						'url'     => $resource,
						'type'    => 'css',
						'content' => '/*fancy-styling*/',
						'media'   => 'all',
					]
				);
			}
		}

		add_filter( 'pre_option_wp_rocket_prewarmup_stats', [ $this, 'return_prewarmup_stats' ] );

		$actual = apply_filters( 'rocket_buffer', $config['html'] );

		if ( isset( $config['generated-file'] ) ) {
			$file_mtime = $this->filesystem->mtime( $config['generated-file'] );
			$expected   = str_replace( "?ver={{mtime}}", "?ver=" . $file_mtime, $expected );
		}

		$this->assertEquals(
			$this->format_the_html( $expected ),
			$this->format_the_html( $actual )
		);

		if ( isset( $config['saved-resources'] ) ) {
			foreach ( $config['saved-resources'] as $resource ) {
				$this->assertFalse( $rucss_resources_query->get_item_by( 'url', $resource ) );
			}
		}
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

	public function set_cpcss_option() {
		return $this->config_data['cpcss_option'] ?? false;
	}

	public function return_prewarmup_stats( $option_value ) {
		return [
			'allow_optimization' => true
		];
	}
}
