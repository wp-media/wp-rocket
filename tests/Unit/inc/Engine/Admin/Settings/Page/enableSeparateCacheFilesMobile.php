<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\Database\Optimization;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Settings\Page;
use WP_Rocket\Engine\Admin\Settings\Settings;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Page::enable_separate_cache_files_mobile
 */
class Test_EnableSeparateCacheFilesMobile extends TestCase {
    private $page;
	private $beacon;
	private $options;

	public function setUp() : void {
		parent::setUp();

		$this->beacon       = Mockery::mock( Beacon::class );
		$this->options      = Mockery::mock( Options_Data::class );
		$config = [
			'slug'       => 'wprocket',
			'title'      => 'WP Rocket',
			'capability' => 'rocket_manage_options',
		];

		$template_path = 'vfs://public/wp-content/plugins/wp-rocket/views';

		$this->page = new Page(
			$config,
			Mockery::mock( Settings::class ),
			Mockery::mock( 'WP_Rocket\Interfaces\Render_Interface'),
			$this->beacon,
			Mockery::mock( Optimization::class ),
			Mockery::mock( UserClient::class ),
			Mockery::mock( SiteList::class ),
			$template_path,
			$this->options
		);
	}

	/**
	 * @dataProvider provideTestData
	 */
	public function testShouldEnableSeparateCacheFilesMobile( $config ) {
        Functions\expect('get_rocket_option')
            ->once()
            ->andReturn( $config['cache_mobile'] );

		if ( ! $config['cache_mobile'] ) {
			$this->shouldBail();
		} else {
			$this->shouldSetOption( $config );
		}

        $this->page->enable_separate_cache_files_mobile();
	}

	public function shouldBail() {
        $this->options->shouldReceive( 'set' )->never();
        $this->options->shouldReceive( 'get_options' )->never();
		Functions\expect( 'update_option' )->never();
	}

	public function shouldSetOption( $config ) {
        $this->options->shouldReceive( 'set' )
		              ->once()
		              ->with( 'do_caching_mobile_files', $config['cache_mobile'] );

		$this->options->shouldReceive( 'get_options' )
		              ->once();

		Functions\expect( 'update_option' )->once();
	}

	public function provideTestData() {
		return $this->getTestData( __DIR__, 'enableSeparateCacheFilesMobile' );
	}
}
