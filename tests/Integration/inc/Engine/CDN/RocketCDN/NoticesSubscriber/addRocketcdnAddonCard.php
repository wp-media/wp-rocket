<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::add_rocketcdn_addon_card
 *
 * Runs through the real `rocket_dashboard_sidebar_addons` and `rocket_display_rocketcdn_cta` hook chains.
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_AddRocketcdnAddonCard extends TestCase {
	const LOADING_TRANSIENT = 'rocket_cdn_subscription_creation_in_progress';

	/**
	 * Subscribers registered by this test, removed on tear down.
	 *
	 * @var array
	 */
	private $subscribers = [];

	/**
	 * Registers the subscribers and the subscription fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// The dashboard card and its display filter are only wired by the real admin boot.
		$this->home_url = 'https://www.mysite.com';
		add_filter( 'home_url', [ $this, 'home_url_cb' ] );

		$container     = apply_filters( 'rocket_container', null );
		$event_manager = $container->get( 'event_manager' );

		foreach ( [ 'cdn_render_subscriber', 'rocketcdn_notices_subscriber' ] as $id ) {
			$subscriber = $container->get( $id );
			$event_manager->add_subscriber( $subscriber );
			$this->subscribers[] = $subscriber;
		}

		$this->config['http'][ UserClient::USER_ENDPOINT ] = [
			'response' => [ 'code' => 200 ],
			'body'     => wp_json_encode( [ 'licence_account' => 'Plus' ] ),
		];

		add_filter( 'pre_option_rocketcdn_user_token', [ $this, 'return_token' ] );
	}

	/**
	 * Removes the subscribers and the subscription fixtures.
	 *
	 * @return void
	 */
	public function tear_down() {
		$event_manager = ( apply_filters( 'rocket_container', null ) )->get( 'event_manager' );

		foreach ( $this->subscribers as $subscriber ) {
			$event_manager->remove_subscriber( $subscriber );
		}

		$this->subscribers = [];

		remove_filter( 'pre_option_rocketcdn_user_token', [ $this, 'return_token' ] );
		delete_transient( self::LOADING_TRANSIENT );
		delete_transient( 'rocketcdn_status' );

		parent::tear_down();
	}

	/**
	 * Tests that the card follows the subscription plan, whether or not the creation is loading.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Whether the card is expected.
	 *
	 * @return void
	 */
	public function testShouldDisplayCardBasedOnSubscription( $config, $expected ) {
		set_transient( 'rocketcdn_status', $config['subscription_data'], MINUTE_IN_SECONDS );

		if ( $config['loading'] ) {
			set_transient( self::LOADING_TRANSIENT, time(), HOUR_IN_SECONDS );
		}

		$ids = wp_list_pluck( apply_filters( 'rocket_dashboard_sidebar_addons', [] ), 'id' );

		$this->assertSame( $expected, in_array( 'rocketcdn', $ids, true ) );
	}

	/**
	 * Provides a RocketCDN user token.
	 *
	 * @return string
	 */
	public function return_token() {
		return 'token';
	}
}
