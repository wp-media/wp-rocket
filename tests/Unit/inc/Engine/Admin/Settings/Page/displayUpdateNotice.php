<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Page;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Database\Optimization;
use WP_Rocket\Engine\Admin\Settings\{Page, Render, Settings};
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Page::display_update_notice
 *
 * @group Admin
 * @group SettingsPage
 */
class TestDisplayUpdateNotice extends TestCase {
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

		$this->stubEscapeFunctions();
		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShoulDoExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )->justReturn( $config['capability'] );

		Functions\when( 'get_current_screen' )->justReturn( $config['screen'] );

		Functions\when( 'get_current_user_id' )->justReturn( 1 );
		Functions\when( 'get_user_meta' )->justReturn( $config['boxes'] );

		$this->options->shouldReceive( 'get' )
			->andReturn( $config['previous_version'] );

		$this->beacon->shouldReceive( 'get_suggest' )
			->andReturn( $config['beacon'] );

		if ( ! $expected ) {
			Functions\expect( 'rocket_notice_html' )->never();
		} else {
			Functions\expect( 'rocket_notice_html' )
				->once()
				->with( $expected );
		}

		$this->page->display_update_notice();
	}
}
