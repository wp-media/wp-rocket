<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WPMedia\PHPUnit\Integration\ApiTrait;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::maybe_disable_cdn
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * @uses   ::rocket_get_constant
 *
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_MaybeDisableCDN extends TestCase {
	use ApiTrait;

	protected static $api_credentials_config_file = 'rocketcdn.php';
	protected static $use_settings_trait          = true;
	protected static $rocketcdn_user_token;
	protected static $transients                  = [
		'rocketcdn_status'     => null,
		'rocketcdn_user_token' => null,
		'wp_rocket_settings'   => null,
	];

	protected $status;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );

		// Save the original "rocketcdn_user_token" option before starting the tests.
		self::$rocketcdn_user_token = get_option( 'rocketcdn_user_token' );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		// Restore the "rocketcdn_user_token" option before leaving this test class.
		if ( empty ( self::$rocketcdn_user_token ) ) {
			delete_option( 'rocketcdn_user_token' );
		} else {
			update_option( 'rocketcdn_user_token', self::$rocketcdn_user_token );
		}
	}

	public function setUp() {
		parent::setUp();

		set_transient( 'rocketcdn_status', [ 'id' => self::getApiCredential( 'ROCKETCDN_WEBSITE_ID' ) ], MINUTE_IN_SECONDS );
		update_option( 'rocketcdn_user_token', self::getApiCredential( 'ROCKETCDN_TOKEN' ) );
		add_filter( 'rocket_pre_get_subscription_data', [ $this, 'get_subscription_data' ] );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'rocket_pre_get_subscription_data', [ $this, 'get_subscription_data' ] );
		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( 'wp_rocket_settings' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->status = $config['status'];
		$this->mergeExistingSettingsAndUpdate( $config['wp_rocket_settings'] );

		// Run it.
		do_action( 'rocketcdn_check_subscription_status_event' );

		// Check if the cron job is scheduled.
		$timestamp = wp_next_scheduled( 'rocketcdn_check_subscription_status_event' );
		if ( false === $expected['cron_is_scheduled'] ) {
			$this->assertFalse( $timestamp );
		} else {
			$this->assertGreaterThan( 0, $timestamp );
		}

		// Check the expected settings.
		$options = get_option( 'wp_rocket_settings' );
		foreach ( $expected['settings'] as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[ $key ] );
		}
	}

	public function get_subscription_data( $subscription ) {
		if (
			! isset( $subscription['subscription_status'] )
			||
			$this->status !== $subscription['subscription_status']
		) {
			$subscription['subscription_status'] = $this->status;
		}

		return $subscription;
	}
}
