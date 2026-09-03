<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::add_exclude_cdn_section
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::add_exclude_cdn_section
 * @group  CDN
 */
class Test_AddExcludeCdnSection extends TestCase {

	/**
	 * Beacon mock instance.
	 *
	 * @var Mockery\MockInterface|Beacon
	 */
	private $beacon;

	/**
	 * CDN Context mock instance.
	 *
	 * @var Mockery\MockInterface|Context
	 */
	private $context;

	/**
	 * Options_Data mock instance.
	 *
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * Options API mock instance.
	 *
	 * @var Mockery\MockInterface|Options
	 */
	private $options_api;

	/**
	 * RocketCDNQuery mock instance.
	 *
	 * @var \PHPUnit\Framework\MockObject\MockObject|RocketCDNQuery
	 */
	private $cdn_query;

	/**
	 * SubscriptionController mock instance.
	 *
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * User mock instance.
	 *
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * Cache mock instance.
	 *
	 * @var Mockery\MockInterface|Cache
	 */
	private $cache;

	/**
	 * Sets up the test fixture.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		$this->stubTranslationFunctions();

		$this->beacon                  = Mockery::mock( Beacon::class );
		$this->context                 = Mockery::mock( Context::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->options_api             = Mockery::mock( Options::class );
		$this->cdn_query               = $this->createMock( RocketCDNQuery::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );
		$this->cache                   = Mockery::mock( Cache::class );
	}

	/**
	 * Creates a Controller instance under test.
	 *
	 * @return Controller
	 */
	private function get_controller(): Controller {
		return new Controller(
			$this->beacon,
			'',
			$this->context,
			$this->options,
			$this->options_api,
			$this->cdn_query,
			$this->subscription_controller,
			$this->user,
			$this->cache
		);
	}

	/**
	 * Tests that add_exclude_cdn_section sets the correct initial URL based on active driver,
	 * and always includes both rocketcdn_url and other_cdn_url in the help array.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected values.
	 *
	 * @return void
	 */
	public function testShouldReturnCorrectHelpUrl( array $config, array $expected ): void {
		$this->beacon->shouldReceive( 'get_suggest' )
			->with( 'exclude_cdn' )
			->andReturn(
				[
					'id'  => '54c7fa3de4b0512429885b5c',
					'url' => 'https://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn#exclude-files-from-your-cdn',
				]
			);

		$this->beacon->shouldReceive( 'get_suggest' )
			->with( 'exclude_cdn_rocketcdn' )
			->andReturn(
				[
					'id'  => '5e4c84bd04286364bc958833',
					'url' => 'https://docs.wp-rocket.me/article/1307-rocketcd/?utm_source=wp_plugin&utm_medium=wp_rocket#exclude-files-from-rocketcdn',
				]
			);

		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( $config['is_rocketcdn'] );

		if ( $config['is_rocketcdn'] ) {
			// should_disable_element_for_rocketcdn() is called when is_rocketcdn() is true.
			// Mock is_subscription_loading() path to return false so full chain executes.
			$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
				->andReturn( false );

			// is_cdn_paused() checks options->get('cdn').
			$this->options->shouldReceive( 'get' )
				->with( 'cdn' )
				->andReturn( true );

			// has_active_subscription() is called directly in should_disable_element_for_rocketcdn.
			$this->subscription_controller->shouldReceive( 'has_active_subscription' )
				->andReturn( true );

			// should_display_licence_expired_notice() path: is_free() returning false short-circuits
			// before is_license_invalid() is ever called.
			$this->subscription_controller->shouldReceive( 'is_free' )
				->andReturn( false );
		}

		$controller = $this->get_controller();
		$sections   = $controller->add_exclude_cdn_section( [] );

		$this->assertArrayHasKey( 'exclude_cdn_section', $sections );
		$this->assertSame( $expected['initial_url'], $sections['exclude_cdn_section']['help']['url'] );
		$this->assertSame( $expected['rocketcdn_url'], $sections['exclude_cdn_section']['help']['rocketcdn_url'] );
		$this->assertSame( $expected['rocketcdn_id'], $sections['exclude_cdn_section']['help']['rocketcdn_id'] );
		$this->assertSame( $expected['other_cdn_url'], $sections['exclude_cdn_section']['help']['other_cdn_url'] );
		$this->assertSame( $expected['other_cdn_id'], $sections['exclude_cdn_section']['help']['other_cdn_id'] );
	}
}
