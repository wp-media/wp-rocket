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
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Page::get_sidebar_show_option
 *
 * @group Admin
 * @group SettingsPage
 */
class TestGetSidebarShowOption extends TestCase {
	/**
	 * Options_Data mock.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Page instance under test.
	 *
	 * @var Page
	 */
	private $page;

	/**
	 * Sets up the test instance.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );

		$config = [
			'slug'       => 'wprocket',
			'title'      => 'WP Rocket',
			'capability' => 'rocket_manage_options',
		];

		$this->page = new Page(
			$config,
			Mockery::mock( Settings::class ),
			Mockery::mock( Render::class ),
			Mockery::mock( Beacon::class ),
			Mockery::mock( Optimization::class ),
			Mockery::mock( UserClient::class ),
			Mockery::mock( SiteList::class ),
			'vfs://public/wp-content/plugins/wp-rocket/views',
			$this->options,
			Mockery::mock( Context::class )
		);
	}

	/**
	 * Tests that get_sidebar_show_option returns the expected integer.
	 *
	 * @dataProvider configTestData
	 *
	 * @param mixed $option_value Value returned by Options_Data::get().
	 * @param int   $expected     Expected return value.
	 */
	public function testShouldReturnExpected( $option_value, $expected ): void {
		$this->options
			->shouldReceive( 'get' )
			->once()
			->with( 'wpr-js-tips', 1 )
			->andReturn( $option_value );

		$this->assertSame( $expected, $this->page->get_sidebar_show_option() );
	}
}
