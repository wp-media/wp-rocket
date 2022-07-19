<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::display_success_notice
 *
 * @group  RUCSS
 */
class Test_DisplaySuccessNotice extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Settings/displaySuccessNotice.php';

	private $options;
	private $beacon;
	private $settings;

	public function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->beacon =  Mockery::mock( Beacon::class );
		$this->settings = new Settings( $this->options, $this->beacon );

		$this->stubTranslationFunctions();
		$this->stubEscapeFunctions();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'get_current_screen' )->justReturn( $config['current_screen'] );
		Functions\when( 'current_user_can' )->justReturn( $config['capability'] );
		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( $config['boxes'] );

		$this->options->shouldReceive( 'get' )
			->with( 'remove_unused_css', 0 )
			->andReturn( $config['remove_unused_css'] );

		Functions\when( 'get_transient' )->justReturn( $config['transient'] );

		$this->options->shouldReceive( 'get' )
			->with( 'manual_preload', 0 )
			->andReturn( $config['manual_preload'] );

		$this->beacon->shouldReceive( 'get_suggest' )
			->andReturn(
				[
					'id' => 123,
					'url' => 'http://example.org',
				]
			);

		if ( $expected ) {
			Functions\expect( 'rocket_notice_html' )
				->with(
					$expected
				);
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		$this->assertTrue( $this->filesystem->is_writable( rocket_get_constant( 'WP_ROCKET_USED_CSS_PATH' ) ) );

		$this->settings->display_success_notice();
	}
}
