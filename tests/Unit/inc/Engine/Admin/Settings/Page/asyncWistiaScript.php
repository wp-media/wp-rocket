<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Page;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Database\Optimization;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Settings\{Page, Render, Settings};
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Page::async_wistia_script
 *
 * @group Admin
 * @group SettingsPage
 */
class TestAsyncWistiaScript extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldMaybeAsyncScript( $tag, $handle, $expected ) {
		$config = [
			'slug'       => 'wprocket',
			'title'      => 'WP Rocket',
			'capability' => 'rocket_manage_options',
		];

		$template_path = 'vfs://public/wp-content/plugins/wp-rocket/views';

		$page = new Page(
			$config,
			Mockery::mock( Settings::class ),
			Mockery::mock( Render::class ),
			Mockery::mock( Beacon::class),
			Mockery::mock( Optimization::class ),
			Mockery::mock( UserClient::class ),
			Mockery::mock( SiteList::class ),
			$template_path,
			Mockery::mock( Options_Data::class )
		);

        $this->assertSame(
            $expected,
            $page->async_wistia_script( $tag, $handle )
        );
	}
}
