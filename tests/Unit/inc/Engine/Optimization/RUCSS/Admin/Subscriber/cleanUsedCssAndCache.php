<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Preload\Homepage;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Database;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber;
use WP_Rocket\Engine\Optimization\RUCSS\Controller\UsedCSS;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::clean_used_css_and_cache
 *
 * @uses   ::rocket_clean_domain
 *
 * @group  RUCSS
 */
class Test_CleanUsedCssAndCache extends FilesystemTestCase {

	private $settings;
	private $database;
	private $usedCSS;
	private $subscriber;
	private $options_api;
	private $homepage_preloader;

	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Admin/Subscriber/cleanUsedCssAndCache.php';

	public function setUp() : void {
		parent::setUp();

		$this->settings           = Mockery::mock( Settings::class );
		$this->database           = Mockery::mock( Database::class );
		$this->usedCSS            = Mockery::mock( UsedCSS::class );
		$this->options_api        = Mockery::mock( Options::class );
		$this->homepage_preloader = Mockery::mock( Homepage::class );
		$this->subscriber  = new Subscriber( $this->settings, $this->database, $this->usedCSS, $this->options_api, $this->homepage_preloader );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input ) {
		Functions\expect( 'current_user_can' )
				->once()
				->with( 'rocket_manage_options' )
				->andReturn( true );

		$this->settings
				->shouldReceive( 'is_enabled' )
				->once()
				->andReturn( $input['remove_unused_css'] );

		if ( $input['remove_unused_css']
				&&
				isset( $input['settings']['remove_unused_css_safelist'], $input['old_settings']['remove_unused_css_safelist'] )
				&&
				$input['settings']['remove_unused_css_safelist'] !== $input['old_settings']['remove_unused_css_safelist']
		 ) {
			$this->database
				->shouldReceive( 'truncate_used_css_table' )
				->once();

			Functions\expect( 'rocket_clean_domain' )
				->once();
		} else {
			$this->database
				->shouldReceive( 'truncate_used_css_table' )
				->never();

			Functions\expect( 'rocket_clean_domain' )
				->never();
		}

		$this->subscriber->clean_used_css_and_cache( $input['settings'], $input['old_settings'] );
	}
}
