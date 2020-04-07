<?php
namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::generate_critical_css_on_activation
 * @group  Subscribers
 * @group  CriticalCss
 * @group  vfs
 */
class Test_GenerateCriticalCssOnActivation extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/generateCriticalCssOnActivation.php';
	private static $container;
	private $subscriber;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$container = apply_filters( 'rocket_container', null );
	}

	public function setUp() {
		parent::setUp();

		$this->subscriber = self::$container->get( 'critical_css_subscriber' );
	}

	public function testShouldBailOutWhenCriticalCSSOptionIsFalse() {
		$this->assertEquals( 0, Filters\applied( 'do_rocket_critical_css_generation' ) );
		Functions\expect( 'get_transient' )->with( 'rocket_critical_css_generation_process_running' )->never();

		$this->subscriber->generate_critical_css_on_activation( [ 'async_css' => 0 ], [ 'async_css' => 0 ] );
	}

	public function testShouldBailOutWhenCriticalCssPathIsValid() {
		$this->assertEquals( 0, Filters\applied( 'do_rocket_critical_css_generation' ) );
		Functions\expect( 'get_transient' )->with( 'rocket_critical_css_generation_process_running' )->never();

		$this->subscriber->generate_critical_css_on_activation( [ 'async_css' => 0 ], [ 'async_css' => 1 ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGenerateCriticalCss( $old_value, $new_value ) {
		$this->assertTrue( true );
		// TODO: Add test data and the tests here.
	}
}
