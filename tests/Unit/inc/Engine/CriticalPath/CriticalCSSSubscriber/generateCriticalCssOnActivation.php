<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSSubscriber::generate_critical_css_on_activation
 *
 * @group  Subscribers
 * @group  CriticalPath
 * @group  vfs
 */
class Test_GenerateCriticalCssOnActivation extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/CriticalPath/CriticalCSSSubscriber/generateCriticalCssOnActivation.php';
	private   $critical_css;
	private   $subscriber;

	public function setUp() {
		parent::setUp();

		$this->critical_css = Mockery::mock( CriticalCSS::class );
		$this->subscriber   = new CriticalCSSSubscriber(
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
						function( $target ) {
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
