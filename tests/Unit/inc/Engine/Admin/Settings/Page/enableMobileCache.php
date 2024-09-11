<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Page;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Database\Optimization;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Settings\{Page, Render, Settings};
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Page::enable_mobile_cache
 *
 * @group SettingsPage
 */
class TestEnableMobileCache extends TestCase {
    private $page;
	private $beacon;
	private $options;

	protected function setUp(): void {
		parent::setUp();

		$this->beacon  = Mockery::mock( Beacon::class );
		$this->options = Mockery::mock( Options_Data::class );
		$config = [
			'slug'       => 'wprocket',
			'title'      => 'WP Rocket',
			'capability' => 'rocket_manage_options',
		];

		$template_path = 'vfs://public/wp-content/plugins/wp-rocket/views';

		$this->page = new Page(
			$config,
			Mockery::mock( Settings::class ),
			Mockery::mock( Render::class ),
			$this->beacon,
			Mockery::mock( Optimization::class ),
			Mockery::mock( UserClient::class ),
			Mockery::mock( SiteList::class ),
			$template_path,
			$this->options
		);

		Functions\when( 'check_ajax_referer' )->justReturn( true );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEnableMobileCache( $user_auth ) {
		Functions\when( 'current_user_can' )->justReturn( $user_auth );

		if ( ! $user_auth ) {
			$this->shouldBail();
		} else {
			$this->shouldSetOption();
		}

        $this->page->enable_mobile_cache();
	}

	public function shouldBail() {
		Functions\expect( 'wp_send_json_error' )->once();
	}

	public function shouldSetOption() {
		$this->options->shouldReceive( 'set' )
		              ->once()
		              ->with( 'cache_mobile', 1 );
        $this->options->shouldReceive( 'set' )
		              ->once()
		              ->with( 'do_caching_mobile_files', 1 );

		$this->options->shouldReceive( 'get_options' )
		              ->once();

		Functions\expect( 'update_option' )->once();
		Functions\expect( 'wp_send_json_success' )->once();
	}
}
