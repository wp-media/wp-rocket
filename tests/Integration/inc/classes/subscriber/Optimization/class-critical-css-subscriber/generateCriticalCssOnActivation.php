<?php
namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\Optimization\Critical_CSS_Subscriber;

use FilesystemIterator;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Critical_CSS_Subscriber::generate_critical_css_on_activation
 * @group  Subscribers
 * @group  CriticalCss
 */
class Test_GenerateCriticalCssOnActivation extends FilesystemTestCase {

	protected $structure = [
		'critical-css' => [
			'1' => [
				'.'            => '',
				'..'           => '',
				'critical.css' => 'css content',
			],
		],
	];

	private static $container;
	private $subscriber;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::$container = apply_filters( 'rocket_container', null );
	}

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )->atLeast( 1 )->with( 'WP_ROCKET_CRITICAL_CSS_PATH' )->andReturn( $this->filesystem->getUrl( 'cache/critical-css/' ) );
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

}
