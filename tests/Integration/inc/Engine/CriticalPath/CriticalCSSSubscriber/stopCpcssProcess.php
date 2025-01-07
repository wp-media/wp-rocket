<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use WP_Rocket\Tests\Integration\ContentTrait;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::stop_critical_css_generation
 *
 * @group  CriticalPath
 * @group  vfs
 */
class Test_stopCpcssProcess extends FilesystemTestCase {
	use ContentTrait;

	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/stopCpcssProcess.php';

	private static $container;
	private $subscriber;
	private $cancel_file_path;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$container = apply_filters( 'rocket_container', null );
	}

	public function set_up() {
		parent::set_up();
		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'stop_critical_css_generation', 9 );
		$this->unregisterAllCallbacksExcept( 'rocket_before_rollback', 'stop_critical_css_generation', 9 );
		$this->subscriber   = self::$container->get( 'critical_css_subscriber' );
		$this->cancel_file_path              = WP_ROCKET_CACHE_ROOT_PATH . '.' . 'rocket_critical_css_generation_process_cancelled';

	}

	public function tear_down() {
		parent::tear_down();
		if($this->filesystem->exists( $this->cancel_file_path )){
			$this->filesystem->delete( $this->cancel_file_path );
		}
		$this->restoreWpHook( 'wp_rocket_upgrade' );
		$this->restoreWpHook( 'rocket_before_rollback' );
	}
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected($config, $expected) {
		$this->subscriber->generate_critical_css_on_activation( $config['old'], $config['new'] );

		if('rollback' === $config['upgrade_rollback']){
			do_action('rocket_before_rollback');
		} else{
			do_action('wp_rocket_upgrade','3.9.4.1', '3.10');
		}

		$this->assertSame(  $expected, $this->filesystem->exists( $this->cancel_file_path ) );
	}


}
