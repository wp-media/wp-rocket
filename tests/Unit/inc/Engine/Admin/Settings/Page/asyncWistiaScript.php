<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Page;

use Mockery;
use WP_Rocket\Engine\Admin\Database\Optimization;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Settings\Page;
use WP_Rocket\Engine\Admin\Settings\Settings;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Page::async_wistia_script
 * @group  Admin
 * @group  SettingsPage
 */
class Test_AsyncWistiaScript extends TestCase {
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMaybeAsyncScript( $tag, $handle, $expected ) {
		$config = [
			'slug'       => 'wprocket',
			'title'      => 'WP Rocket',
			'capability' => 'rocket_manage_options',
		];

		$page = new Page(
			$config,
			Mockery::mock( Settings::class ),
			Mockery::mock( 'WP_Rocket\Interfaces\Render_Interface'),
			Mockery::mock( Beacon::class),
			Mockery::mock( Optimization::class ),
			Mockery::mock( UserClient::class ),
			Mockery::mock( SiteList::class )
		);

        $this->assertSame(
            $expected,
            $page->async_wistia_script( $tag, $handle )
        );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'asyncWistiaScript' );
	}
}
