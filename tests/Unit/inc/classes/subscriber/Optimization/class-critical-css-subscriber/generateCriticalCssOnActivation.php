<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Optimization\Critical_CSS_Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Optimization\CSS\Critical_CSS;
use WP_Rocket\Optimization\CSS\Critical_CSS_Generation;
use WP_Rocket\Subscriber\Optimization\Critical_CSS_Subscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Optimization\Critical_CSS_Subscriber::generate_critical_css_on_activation
 * @group  Subscribers
 * @group  CriticalCss
 * @group  vfs
 */
class Test_GenerateCriticalCssOnActivation extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/classes/subscriber/Optimization/class-critical-css-subscriber/generateCriticalCssOnActivation.php';
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

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldGenerateCriticalCss( $critical_css_path, $old_value, $new_value ) {
		$dirs = $this->filesystem->getDirsListing( $this->config['vfs_dir'] );
		if ( ! in_array( $critical_css_path['path'], $dirs, true ) ) {
			$this->critical_css->shouldReceive( 'process_handler' )->never();
		} else {
			$this->critical_css->shouldReceive( 'process_handler' )->once()->andReturn();
		}

		$this->critical_css->shouldReceive( 'get_critical_css_path' )->once()->andReturn( $critical_css_path['path'] );

		$this->subscriber->generate_critical_css_on_activation( $old_value, $new_value );
	}
}
