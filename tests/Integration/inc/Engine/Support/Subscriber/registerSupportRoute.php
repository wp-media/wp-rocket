<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Support\Subscriber;

use WPMedia\PHPUnit\Integration\ApiTrait;
use WPMedia\PHPUnit\Integration\RESTfulTestCase as WPMediaRESTfulTestCase;
use WP_Rocket\Tests\StubTrait;

/**
 * @covers \WP_Rocket\Engine\Support\Subscriber::register_support_route
 *
 * @group Support
 */
class Test_RegisterSupportRoute extends WPMediaRESTfulTestCase {
	use ApiTrait;
	use StubTrait;

	protected static $api_credentials_config_file = 'license.php';
	protected $config;
	private $consumer_key;
	private $consumer_email;

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );
	}

	public function setUp() : void {
		parent::setUp();

		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		add_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );

		$this->stubRocketGetConstant();

		global $wp_version;

		$this->wp_version = $wp_version;
	}

	public function tearDown() {
		global $wp_version;

		$wp_version = $this->wp_version;

		remove_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );

		$this->resetStubProperties();

		parent::tearDown();
	}

	public function testShouldRegisterRoute() {
		$this->assertArrayHasKey( '/wp-rocket/v1/support', $this->server->get_routes() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $params, $expected ) {
		global $wp_version;

		$this->rocket_version = '3.7.5';
		$wp_version           = '5.5';

		$body = [
			'email' => $params['email'] ? self::getApiCredential( 'ROCKET_EMAIL' ) : '',
			'key'   => $params['key'] ? self::getApiCredential( 'ROCKET_KEY' ) : '',
		];

		$this->assertArraySubset(
			$expected,
			$this->requestSupportEndpoint( $body )
		);
	}

	protected function requestSupportEndpoint(  array $body_params = [] ) {
		if ( empty( $body_params ) ) {
			$body_params = [
				'email' => '',
				'key'   => '',
			];
		}

		return $this->doRestRequest( 'POST', '/wp-rocket/v1/support', $body_params );
	}

	public function set_consumer_key() {
		return self::getApiCredential( 'ROCKET_KEY' );
	}

	public function set_consumer_email() {
		return self::getApiCredential( 'ROCKET_EMAIL' );
	}

	public function configTestData() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		return isset( $this->config['test_data'] )
			? $this->config['test_data']
			: $this->config;
	}

	protected function loadTestDataConfig() {
		$obj      = new \ReflectionObject( $this );
		$filename = $obj->getFileName();

		$this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
	}
}
