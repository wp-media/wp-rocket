<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Admin\Settings;
use WP_Rocket\Engine\Preload\Controller\{LoadInitialSitemap, PreloadUrl};
use WP_Rocket\Engine\Preload\Database\Tables\Cache as CacheTable;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Admin\Settings::preload_homepage
 *
 * @group Preload
 */
class TestPreloadHomepage extends TestCase {
	private $settings;
	private $options;
	private $preload_url;

	protected function setUp(): void {
		parent::setUp();

		$this->options     = Mockery::mock( Options_Data::class );
		$this->preload_url = Mockery::mock( PreloadUrl::class );
		$this->settings    = new Settings(
			$this->options,
			$this->preload_url,
			Mockery::mock( LoadInitialSitemap::class ),
			$this->createMock( CacheTable::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'home_url' )->justReturn( 'http://example.org/' );

		$this->options->shouldReceive( 'get' )
			->with( 'manual_preload', 0 )
			->andReturn( $config['options']['manual_preload'] );

		if ( $expected ) {
			$this->preload_url->shouldReceive( 'preload_url' )
				->once();
		} else {
			$this->preload_url->shouldReceive( 'preload_url' )
				->never();
		}

		$this->settings->preload_homepage();
	}
}
