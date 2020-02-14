<?php

namespace WP_Rocket\Tests\Integration\inc\Addons\Cloudflare\CloudflareSubscriber;

use ReflectionClass;
use WPMedia\Cloudflare\Cloudflare;
use WPMedia\PHPUnit\Integration\ApiTrait;
use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	use ApiTrait;

	protected static $container;
	protected static $subscriber;

	// Original instances within CloudflareSubscriber. Don't touch. We want to retain and then restore when done.
	private static $subscriber_cf;
	private static $subscriber_options;
	private static $cf_property;
	private static $options_property;

	// API Credentials.
	protected static $api_credentials_config_file = 'cloudflare.php';
	protected static $api_credentials = [
		'api_key'  => '',
		'email'    => '',
		'zone_id'  => '',
		'site_url' => '',
	];

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		static::pathToApiCredentialsConfigFile( WP_ROCKET_PLUGIN_ROOT . '/tests/env/local/' );

		self::$container = apply_filters( 'rocket_container', '' );
		self::setApiCredentials();
		self::resetTransients();
		self::$subscriber = self::$container->get( 'cloudflare_subscriber' );

		// Store original state. Why? We'll restore it before existing this class.
		$class    = new ReflectionClass( self::$subscriber );
		self::$cf_property = $class->getProperty( 'cloudflare' );
		self::$cf_property->setAccessible( true );
		self::$subscriber_cf = self::$cf_property->getValue( self::$subscriber );
		self::$options_property            = $class->getProperty( 'options' );
		self::$options_property->setAccessible( true );
		self::$subscriber_options = self::$options_property->getValue( self::$subscriber );
	}

	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();

		// Restore original state.
		self::$cf_property->setValue( self::$subscriber, self::$subscriber_cf );
		self::$cf_property->setAccessible( false );
		self::$options_property->setValue( self::$subscriber, self::$subscriber_options );
		self::$options_property->setAccessible( false );
	}

	protected static function setApiCredentials() {
		self::$api_credentials['email']    = static::getApiCredential( 'ROCKET_CLOUDFLARE_EMAIL' );
		self::$api_credentials['api_key']  = static::getApiCredential( 'ROCKET_CLOUDFLARE_API_KEY' );
		self::$api_credentials['zone_id']  = static::getApiCredential( 'ROCKET_CLOUDFLARE_ZONE_ID' );
		self::$api_credentials['site_url'] = static::getApiCredential( 'ROCKET_CLOUDFLARE_SITE_URL' );
	}

	public function setUp() {
		parent::setUp();

		add_filter( 'site_url', [ $this, 'setSiteUrl' ] );
	}

	public function tearDown() {
		parent::tearDown();

		self::resetTransients();

		remove_filter( 'site_url', [ $this, 'setSiteUrl' ] );
	}

	protected static function resetTransients() {
		// Reset the transients.
		$transients = [
			'rocket_cloudflare_is_api_keys_valid',
			'rocket_cloudflare_ips',
		];
		foreach ( $transients as $transient => $value ) {
			delete_transient( $transient );
		}
	}

	protected function setApiCredentialsInOptions( array $options = [] ) {
		$options = array_merge(
			[
				'cloudflare_email'   => self::$api_credentials['email'],
				'cloudflare_api_key' => self::$api_credentials['api_key'],
				'cloudflare_zone_id' => self::$api_credentials['zone_id'],
				'do_cloudflare'      => 1,
			],
			$options
		);
		$this->setOptions( $options );
	}

	protected function getConcrete( $key ) {
		return self::$container->get( $key );
	}

	protected function setOptions( $data ) {
		update_option( 'wp_rocket_settings', $data );

		$cf_options = $this->getConcrete( 'options' );
		$cf_options->set_values( $data );
		self::$cf_property->setValue( self::$subscriber, new Cloudflare( $cf_options, $this->getConcrete( 'cloudflare_api' ) ) );
		self::$options_property->setValue( self::$subscriber,$cf_options );
	}

	public function setSiteUrl() {
		return self::$api_credentials['site_url'];
	}
}
