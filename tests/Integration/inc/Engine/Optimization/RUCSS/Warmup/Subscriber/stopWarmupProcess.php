<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Warmup\Subscriber;

use WP_Rocket\Tests\Integration\DBTrait;
use WP_Rocket\Tests\Integration\FilesystemTestCase;


/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\Subscriber::cancel_resource_fetching
 *
 * @group RUCSS
 */
class Test_stopWarmupProcess extends FilesystemTestCase {
	use DBTrait;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Warmup/Subscriber/stopWarmupProcess.php';

	private $input = [];
	private $cancel_file_path;


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
			'query_vars' => [],
			'request'    => 'http://example.org',
			'public_query_vars' => [
				'embed',
			],
		];

		parent::setUp();
		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'collect_resources', 11 );
		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'cancel_resource_fetching', 9 );
		$this->unregisterAllCallbacksExcept( 'admin_post_rocket_rollback', 'cancel_resource_fetching', 9 );
		$this->cancel_file_path              = WP_ROCKET_CACHE_ROOT_PATH . '.' . 'rocket_rucss_warmup_resource_fetcher_process_cancelled';
	}

	public function tearDown() : void {
		unset( $GLOBALS['wp'] );

		remove_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		parent::tearDown();

		$this->restoreWpFilter( 'wp_rocket_upgrade' );
		$this->restoreWpFilter( 'admin_post_rocket_rollback' );

		if($this->filesystem->exists( $this->cancel_file_path )){
			$this->filesystem->delete( $this->cancel_file_path );
		}
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input, $expected ){

		$this->input = $input;

		add_filter( 'pre_get_rocket_option_remove_unused_css', [ $this, 'set_rucss_option' ] );

		apply_filters( 'rocket_buffer', $input['html'] );

		//$this->assertSame( $input['html'], apply_filters( 'rocket_buffer', $input['html'] ) );
		if('rollback' === $input['upgrade_rollback']){
			do_action('admin_post_rocket_rollback');
		} else{
			do_action('wp_rocket_upgrade','3.9.4.1', '3.10');
		}

		$this->assertSame(  $expected, $this->filesystem->exists( $this->cancel_file_path ) );
	}

	public function set_rucss_option() {
		return $this->input['remove_unused_css'] ?? false;
	}
}
