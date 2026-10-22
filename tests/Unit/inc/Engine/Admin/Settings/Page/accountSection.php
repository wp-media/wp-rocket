<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Page;

use Brain\Monkey\Functions;
use Mockery;
use ReflectionMethod;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Database\Optimization;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\Settings\{Page, Render, Settings};
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Page::account_section
 *
 * @group SettingsPage
 */
class Test_AccountSection extends TestCase {
	private $settings;
	private $user_client;
	private $page;

	protected function setUp(): void {
		parent::setUp();

		$this->settings    = Mockery::mock( Settings::class );
		$this->user_client = Mockery::mock( UserClient::class );

		$this->page = new Page(
			[
				'slug'       => 'wprocket',
				'title'      => 'WP Rocket',
				'capability' => 'rocket_manage_options',
			],
			$this->settings,
			Mockery::mock( Render::class ),
			Mockery::mock( Beacon::class ),
			Mockery::mock( Optimization::class ),
			$this->user_client,
			Mockery::mock( SiteList::class ),
			'vfs://public/wp-content/plugins/wp-rocket/views',
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Context::class ),
			Mockery::mock( SubscriptionController::class )
		);

		Functions\when( '__' )->returnArg();
		Functions\when( 'rocket_get_license_type' )->justReturn( 'Unknown' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRegisterAccountSection( $config, $expected ) {
		$this->user_client->shouldReceive( 'get_user_data' )->once()->andReturn( $config['user_data'] );

		$this->settings->shouldReceive( 'add_page_section' )
			->once()
			->with( 'account', $expected );

		$method = new ReflectionMethod( Page::class, 'account_section' );
		$method->setAccessible( true );
		$method->invoke( $this->page );
	}
}
