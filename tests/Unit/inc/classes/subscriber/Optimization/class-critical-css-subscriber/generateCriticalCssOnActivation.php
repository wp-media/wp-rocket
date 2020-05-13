<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\Optimization\Critical_CSS_Subscriber;

use Brain\Monkey\Functions;
use FilesystemIterator;
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

	/**
	 * @dataProvider nonMultisiteTestData
	 */
	public function testShouldGenerateCriticalCss( $values ) {
		$critical_css_path = $this->config['vfs_dir'] . '1/';

		$this->assertTrue( $this->filesystem->is_dir( $critical_css_path ) );
		Functions\expect( 'rocket_mkdir_p' )->with( $critical_css_path )->never();
		$this->critical_css->shouldReceive( 'process_handler' )->never();

		if ( $values['old']['async_css'] !== $values['new']['async_css'] && 1 === (int) $values['new']['async_css'] ) {
			$this->critical_css->shouldReceive( 'get_critical_css_path' )->once()->andReturn( $critical_css_path );
		} else {
			$this->critical_css->shouldReceive( 'get_critical_css_path' )->never();
		}

		// Run it.
		$this->subscriber->generate_critical_css_on_activation( $values['old'], $values['new'] );
	}

	/**
	 * @dataProvider multisiteTestData
	 */
	public function testShouldProcessMultisite( $values, $site_id, $should_generate ) {
		$critical_css_path = $this->filesystem->getUrl( $this->config['vfs_dir'] . "{$site_id}/" );

		$will_bailout = (
			$values['old']['async_css'] === $values['new']['async_css']
			||
			1 !== (int) $values['new']['async_css']
		);

		if ( $will_bailout ) {
			$this->critical_css->shouldReceive( 'get_critical_css_path' )->never();
			$this->critical_css->shouldReceive( 'process_handler' )->never();
		} else {
			$this->critical_css->shouldReceive( 'get_critical_css_path' )->once()->andReturn( $critical_css_path );

			if ( $should_generate ) {
				$this->assertFalse( $this->filesystem->is_dir( $critical_css_path ) );
				Functions\expect( 'rocket_mkdir_p' )
					->with( $critical_css_path )
					->andReturnUsing(
						function ( $target ) {
							$this->filesystem->mkdir( $target );
							$this->assertTrue( $this->filesystem->is_dir( $target ) );

							return true;
						}
					);
				$this->critical_css->shouldReceive( 'process_handler' )->once()->andReturn();

			} else {
				$this->critical_css->shouldReceive( 'process_handler' )->never();
				Functions\expect( 'rocket_mkdir_p' )->with( $critical_css_path )->never();
			}
		}

		// Run it.
		$this->subscriber->generate_critical_css_on_activation( $values['old'], $values['new'] );

		$this->assertTrue( $this->filesystem->is_dir( $critical_css_path ) );
	}

	public function nonMultisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['non_multisite'];
	}

	public function multisiteTestData() {
		if ( empty( $this->config ) ) {
			$this->loadConfig();
		}

		return $this->config['test_data']['multisite'];
	}
}
