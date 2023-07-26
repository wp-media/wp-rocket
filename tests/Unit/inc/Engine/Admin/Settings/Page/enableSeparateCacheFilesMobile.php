<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Page;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Admin\Database\Optimization;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Settings\Page;
use WP_Rocket\Engine\Admin\Settings\Settings;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Page::enable_separate_cache_files_mobile
 */
class Test_EnableSeparateCacheFilesMobile extends TestCase {
    private $page;
	private $beacon;
	private $options;

	protected function setUp() : void {
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
		$this->options->shouldReceive( 'get' )
				->atLeast()
				->times(1)
				->andReturnUsing(function( $option, $value ) use($config) {
					if ( 'cache_mobile' === $option ) {
						return $config['cache_mobile'];
					}
	
					if ( 'do_caching_mobile_files' === $option ) {
						return $config['do_caching_mobile_files'];
					}
				});

		if ( ! $config['cache_mobile'] || $config['do_caching_mobile_files'] ) {
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
		              ->with( 'do_caching_mobile_files', $config['do_caching_mobile_files'] );

		$this->options->shouldReceive( 'get_options' )
		              ->once();

		Functions\expect( 'update_option' )->once();
	}

	public function provideTestData() {
		return $this->getTestData( __DIR__, 'enableSeparateCacheFilesMobile' );
	}
}
