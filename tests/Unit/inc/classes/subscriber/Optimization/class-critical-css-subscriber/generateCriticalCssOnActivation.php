<?php
namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Optimization\Critical_CSS_Subscriber;

use FilesystemIterator;
use Brain\Monkey\Functions;
use Mockery;
use UnexpectedValueException;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Optimization\CSS\Critical_CSS;
use WP_Rocket\Optimization\CSS\Critical_CSS_Generation;
use WP_Rocket\Subscriber\Optimization\Critical_CSS_Subscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

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
				'critical.css' => 'body { font-family: Helvetica, Arial, sans-serif; text-align: center;}',
			],
			// This one is empty.
			'2' => [
				'.'  => '',
				'..' => '',
			],
		],
	];

	private $critical_css;
	private $subscriber;

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )->atLeast( 1 )->with( 'WP_ROCKET_CRITICAL_CSS_PATH' )->andReturn( $this->filesystem->getUrl( 'cache/critical-css/' ) );
		Functions\when( 'get_current_blog_id' )->justReturn( 1 );
		Functions\expect( 'home_url' )->once()->with( '/' )->andReturn( 'http://example.com' );

		$this->critical_css = Mockery::mock( Critical_CSS::class, [ $this->createMock( Critical_CSS_Generation::class ) ] );
		$this->subscriber   = new Critical_CSS_Subscriber(
			$this->critical_css,
			$this->createMock( Options_Data::class )
		);
	}

	public function testShouldBailOutWhenCriticalCSSOptionIsFalse() {
		$this->critical_css->shouldReceive( 'process_handler' )->never();

		$this->subscriber->generate_critical_css_on_activation( [ 'async_css' => 0 ], [ 'async_css' => 0 ] );
	}

	public function testShouldBailOutWhenCriticalCssPathIsInvalid() {
		$this->critical_css->shouldReceive( 'process_handler' )->never();
		$this->critical_css->shouldReceive( 'get_critical_css_path' )->once()->andReturn( 'invalid' );

		$this->subscriber->generate_critical_css_on_activation( [ 'async_css' => 0 ], [ 'async_css' => 1 ] );
	}


	public function testShouldBailOutWhenCriticalCssPathIsNotEmpty() {
		$this->critical_css->shouldReceive( 'process_handler' )->never();
		$this->critical_css->shouldReceive( 'get_critical_css_path' )->once()->andReturn( 'vfs://cache/critical-css/1/' );

		$this->subscriber->generate_critical_css_on_activation( [ 'async_css' => 0 ], [ 'async_css' => 1 ] );
	}

	public function testShouldInvokeProcessHandlerWhenCriticalCssPathIsEmpty() {
		$this->critical_css->shouldReceive( 'process_handler' )->once()->andReturn();
		$this->critical_css->shouldReceive( 'get_critical_css_path' )->once()->andReturn( 'vfs://cache/critical-css/2/' );

		$this->subscriber->generate_critical_css_on_activation( [ 'async_css' => 0 ], [ 'async_css' => 1 ] );
	}

}
