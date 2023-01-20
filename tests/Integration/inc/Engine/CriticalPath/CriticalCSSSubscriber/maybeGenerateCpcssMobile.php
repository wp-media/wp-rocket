<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use WP_Rocket\Tests\Integration\FilesystemTestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::maybe_generate_cpcss_mobile
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCss::process_handler
 * @uses   \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration::cancel_process
 * @uses   ::rocket_get_constant
 *
 * @group  Subscribers
 * @group  CriticalPath
 */
class Test_MaybeGenerateCpcssMobile extends FilesystemTestCase {
	protected      $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/maybeGenerateCpcssMobile.php';
	private        $subscriber;
	private static $container;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$container = apply_filters( 'rocket_container', null );
	}

	public function set_up() {
		parent::set_up();

		$this->subscriber = self::$container->get( 'wp_rocket.engine.criticalpath.serviceprovider.critical_css_subscriber' );
	}

	public function tear_down() {
		parent::tear_down();

		delete_transient( 'rocket_critical_css_generation_process_running' );
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testShouldCallProcessHandler( $config, $expected ) {

		$this->assertEquals( 0, Filters\applied( 'do_rocket_critical_css_generation' ) );
		if ( $expected['process_handler_called'] ) {
			Functions\expect( 'set_transient' )->withAnyArgs()->once();
		}
		$this->assertTrue( $this->filesystem->is_dir( $this->config['vfs_dir'] . '1/' ) );

		$this->subscriber->maybe_generate_cpcss_mobile( $config['old_value'], $config['value'] );

	}

	public function dataProvider() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data'];
	}

}
