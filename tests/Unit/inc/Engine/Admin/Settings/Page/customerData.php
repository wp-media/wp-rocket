<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Page;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Database\Optimization;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\Settings\{Page, Render, Settings};
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\SiteList;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Page::customer_data
 *
 * @group SettingsPage
 */
class Test_CustomerData extends TestCase {
	private $user_client;
	private $page;

	protected function setUp(): void {
		parent::setUp();

		$this->user_client = Mockery::mock( UserClient::class );

		$this->page = new Page(
			[
				'slug'       => 'wprocket',
				'title'      => 'WP Rocket',
				'capability' => 'rocket_manage_options',
			],
			Mockery::mock( Settings::class ),
			Mockery::mock( Render::class ),
			Mockery::mock( Beacon::class ),
			Mockery::mock( Optimization::class ),
			$this->user_client,
			Mockery::mock( SiteList::class ),
			'vfs://public/wp-content/plugins/wp-rocket/views',
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Context::class )
		);

		Functions\when( '__' )->returnArg();
		Functions\when( 'rocket_get_license_type' )->justReturn( 'Unknown' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ): void {
		if ( false === $config['user_data'] ) {
			$this->user_client->shouldReceive( 'get_user_data' )->once()->andReturn( false );
			Filters\expectApplied( 'rocket_ai_assistant_available' )->never();
		} else {
			$this->user_client->shouldReceive( 'get_user_data' )->once()->andReturn( (object) $config['user_data'] );

			if ( isset( $config['filter_returns'] ) ) {
				Filters\expectApplied( 'rocket_ai_assistant_available' )
					->once()
					->with( true )
					->andReturn( $config['filter_returns'] );
			}
		}

		$data = $this->page->customer_data();

		if ( $expected['has_assistant_available'] ) {
			$this->assertArrayHasKey( 'assistant_available', $data );
			$this->assertSame( $expected['assistant_available'], $data['assistant_available'] );
		} else {
			$this->assertArrayNotHasKey( 'assistant_available', $data );
		}
	}
}
