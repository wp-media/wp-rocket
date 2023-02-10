<?php

namespace WP_Rocket\Tests\Integration\inc\Addons\Cloudflare\Subscriber;

use ReflectionClass;
use WPMedia\Cloudflare\Cloudflare;
use WP_Rocket\Tests\Integration\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase {
	protected static $container;
	protected static $subscriber;

	// Original instances within Subscriber. Don't touch. We want to retain and then restore when done.
	private static $subscriber_cf;
	private static $subscriber_options;
	private static $cf_property;
	private static $options_property;

	// API Credentials.
	protected static $api_credentials_config_file;
	protected static $api_credentials = [
		'api_key'  => '',
		'email'    => '',
		'zone_id'  => '',
		'site_url' => '',
	];

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::$api_credentials_config_file = WP_ROCKET_PLUGIN_ROOT . '/tests/env/local/cloudflare.php';

		self::$container = apply_filters( 'rocket_container', '' );
		self::setApiCredentials();
		self::resetTransients();
		self::$subscriber = self::$container->get( 'cloudflare_subscriber' );

		// Store original state. Why? We'll restore it before exiting this class.
		$class             = new ReflectionClass( self::$subscriber );
		self::$cf_property = $class->getProperty( 'cloudflare' );
		self::$cf_property->setAccessible( true );
		self::$subscriber_cf    = self::$cf_property->getValue( self::$subscriber );
		self::$options_property = $class->getProperty( 'options' );
		self::$options_property->setAccessible( true );
		self::$subscriber_options = self::$options_property->getValue( self::$subscriber );
	}

	public static function tear_down_after_class() {
		// Restore original state.
		self::$cf_property->setValue( self::$subscriber, self::$subscriber_cf );
		self::$cf_property->setAccessible( false );
		self::$options_property->setValue( self::$subscriber, self::$subscriber_options );
		self::$options_property->setAccessible( false );

		parent::tear_down_after_class();
	}

	protected static function setApiCredentials() {
		self::$api_credentials['email']    = static::getApiCredential( 'ROCKET_CLOUDFLARE_EMAIL' );
		self::$api_credentials['api_key']  = static::getApiCredential( 'ROCKET_CLOUDFLARE_API_KEY' );
		self::$api_credentials['zone_id']  = static::getApiCredential( 'ROCKET_CLOUDFLARE_ZONE_ID' );
		self::$api_credentials['site_url'] = static::getApiCredential( 'ROCKET_CLOUDFLARE_SITE_URL' );
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'site_url', [ $this, 'setSiteUrl' ] );
	}

	public function tear_down() {
		self::resetTransients();

		remove_filter( 'site_url', [ $this, 'setSiteUrl' ] );

		parent::tear_down();
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
		var_dump("set Api Credentials In Options");
		$options = array_merge(
			[
				'cloudflare_email'   => self::$api_credentials['email'],
				'cloudflare_api_key' => self::$api_credentials['api_key'],
				'cloudflare_zone_id' => self::$api_credentials['zone_id'],
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
		delete_transient( 'rocket_cloudflare_is_api_keys_valid' );
		$cf = new Cloudflare( $cf_options, $this->getConcrete( 'cloudflare_api' ) );
		self::$cf_property->setValue( self::$subscriber, $cf );
		self::$options_property->setValue( self::$subscriber, $cf_options );
	}

	public function setSiteUrl() {
		return self::$api_credentials['site_url'];
	}

	/**
	 * Gets the credential's value from either an environment variable (stored locally on the machine or CI) or from a
	 * local constant defined in `tests/env/local/cloudflare.php`.
	 *
	 * @param string $name Name of the environment variable or constant to find.
	 *
	 * @return string returns the value if available; else an empty string.
	 */
	protected static function getApiCredential( $name ) {
		$var = getenv( $name );
		if ( ! empty( $var ) ) {
			return $var;
		}

		if ( ! static::$api_credentials_config_file ) {
			return '';
		}

		if ( ! is_readable( self::$api_credentials_config_file ) ) {
			return '';
		}

		// This file is local to the developer's machine and not stored in the repo.
		require_once self::$api_credentials_config_file;

		return rocket_get_constant( $name, '' );
	}
}
