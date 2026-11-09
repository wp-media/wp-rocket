<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Page;

use Brain\Monkey\Actions;
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
 * @covers \WP_Rocket\Engine\Admin\Settings\Page::dismiss_addon_card
 *
 * @group SettingsPage
 */
class TestDismissAddonCard extends TestCase {
	private $page;
	private $render;

	protected function setUp(): void {
		parent::setUp();

		$this->render = Mockery::mock( Render::class );

		$this->page = new Page(
			[
				'slug'       => 'wprocket',
				'title'      => 'WP Rocket',
				'capability' => 'rocket_manage_options',
			],
			Mockery::mock( Settings::class ),
			$this->render,
			Mockery::mock( Beacon::class ),
			Mockery::mock( Optimization::class ),
			Mockery::mock( UserClient::class ),
			Mockery::mock( SiteList::class ),
			'vfs://public/wp-content/plugins/wp-rocket/views',
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Context::class )
		);

		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'sanitize_key' )->alias( 'strtolower' );
		Functions\when( 'wp_unslash' )->returnArg();
	}

	protected function tearDown(): void {
		unset( $_POST['card_id'] );

		parent::tearDown();
	}

	public function testShouldBailWhenUserCannotManageOptions() {
		$_POST['card_id'] = 'imagify';

		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'wp_send_json_error' )->once();
		Functions\expect( 'rocket_dismiss_box' )->never();
		Functions\expect( 'wp_send_json_success' )->never();
		Actions\expectDone( 'rocket_mixpanel_track_event' )->never();

		$this->page->dismiss_addon_card();
	}

	/**
	 * @dataProvider provideInvalidIds
	 */
	public function testShouldBailWhenTheCardIdIsInvalid( $id ) {
		if ( null !== $id ) {
			$_POST['card_id'] = $id;
		}

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\expect( 'wp_send_json_error' )->once();
		Functions\expect( 'rocket_dismiss_box' )->never();
		Functions\expect( 'wp_send_json_success' )->never();
		Actions\expectDone( 'rocket_mixpanel_track_event' )->never();

		$this->page->dismiss_addon_card();
	}

	public function provideInvalidIds() {
		return [
			'missing'  => [ null ],
			'empty'    => [ '' ],
			'too long' => [ str_repeat( 'a', 41 ) ],
		];
	}

	/**
	 * @dataProvider provideDismissalStates
	 */
	public function testShouldDismissTheCard( $card_id, $was_dismissed, $expected_banner ) {
		$_POST['card_id'] = $card_id;

		Functions\when( 'current_user_can' )->justReturn( true );
		$this->render->shouldReceive( 'is_addon_card_dismissed' )
			->once()
			->with( strtolower( $card_id ) )
			->andReturn( $was_dismissed );
		if ( null === $expected_banner ) {
			Actions\expectDone( 'rocket_mixpanel_track_event' )->never();
		} else {
			Actions\expectDone( 'rocket_mixpanel_track_event' )->once()->with( 'Dashboard Banner Dismissed', [ 'banner' => $expected_banner ] );
		}
		$this->render->shouldReceive( 'get_addon_card_box_key' )
			->once()
			->with( strtolower( $card_id ) )
			->andReturn( 'rocket_addon_card_x' );
		Functions\expect( 'rocket_dismiss_box' )->once()->with( 'rocket_addon_card_x' );
		Functions\expect( 'wp_send_json_success' )->once();
		Functions\expect( 'wp_send_json_error' )->never();

		$this->page->dismiss_addon_card();
	}

	public function provideDismissalStates() {
		return [
			'first dismissal'          => [ 'Imagify', false, 'imagify' ],
			'first dismissal cdn'      => [ 'rocketcdn', false, 'rocketcdn' ],
			'already dismissed'        => [ 'imagify', true, null ],
			'unknown card'             => [ 'other', false, null ],
		];
	}
}
