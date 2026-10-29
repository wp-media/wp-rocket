<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Support\Meta;
use WP_Rocket\Engine\Support\Rest;
use WP_Rocket\Engine\Support\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::get_column_config
 * and \WP_Rocket\Engine\Support\Subscriber::get_rocket_capability
 *
 * @group Support
 */
class TestGetColumnConfig extends TestCase {
	private $options;
	private $user_client;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->options     = Mockery::mock( Options_Data::class );
		$this->user_client = Mockery::mock( UserClient::class );
		$this->subscriber  = new Subscriber( Mockery::mock( Rest::class ), Mockery::mock( Meta::class ), $this->options, $this->user_client );

		Functions\when( 'rocket_valid_key' )->justReturn( true );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( (object) [ 'can_use_ai' => true ] )->byDefault();
	}

	private function set_licence( $key, $email ) {
		$this->options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( $key );
		$this->options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( $email );
	}

	public function testShouldReturnEmptyWhenKeyIsMissing() {
		$this->set_licence( '', 'user@example.org' );

		$this->assertSame( [], $this->subscriber->get_column_config() );
	}

	public function testShouldReturnEmptyWhenEmailIsMissing() {
		$this->set_licence( 'test-consumer-key', '' );

		$this->assertSame( [], $this->subscriber->get_column_config() );
	}

	public function testShouldReturnTheSupportChatConfiguration() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		$config = $this->subscriber->get_column_config();

		$this->assertSame( 'wprocket', $config['product'] );
		$this->assertSame( 'group-one', $config['brand'] );
		$this->assertSame( 'support', $config['page_context'] );
		$this->assertSame( 'test-consumer-key|user@example.org', $config['grnd']['license_key'] );
		$this->assertSame( 'https://wp-rocket.me/terms/', $config['terms_url'] );
		$this->assertSame( 'https://wp-rocket.me/privacy-policy/', $config['privacy_url'] );
		$this->assertSame( 'https://wp-rocket.me/privacy-policy/', $config['site_access_url'] );
		$this->assertSame( [ 'settings_page_wprocket' ], $config['screens'] );
		$this->assertSame( [ 'side' => 'right' ], $config['column'] );
		$this->assertFalse( $config['layout']['showAgentSelector'] );
	}

	public function testShouldUseTheLiveUrlsByDefault() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) {
				return $default;
			}
		);

		$config = $this->subscriber->get_column_config();

		$this->assertSame( 'https://wordpress-agentic-platform-production.public-default.k8spod4-cph3.ingress.k8s.g1i.one', $config['server_url'] );
		$this->assertSame( 'https://wp-rocket.me/wp-json/grnd/v1/token', $config['grnd']['issuer_url'] );
	}

	public function testShouldFallBackToTheLiveUrlsWhenTheConstantsAreEmpty() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		$constants = [
			'WP_ROCKET_WAP_SERVER_URL' => '',
			'WP_ROCKET_WAP_ISSUER_URL' => '',
		];

		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) use ( $constants ) {
				return array_key_exists( $name, $constants ) ? $constants[ $name ] : $default;
			}
		);

		$config = $this->subscriber->get_column_config();

		$this->assertSame( 'https://wordpress-agentic-platform-production.public-default.k8spod4-cph3.ingress.k8s.g1i.one', $config['server_url'] );
		$this->assertSame( 'https://wp-rocket.me/wp-json/grnd/v1/token', $config['grnd']['issuer_url'] );
	}

	public function testShouldUseTheUrlsFromTheConstants() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		$constants = [
			'WP_ROCKET_WAP_SERVER_URL' => 'https://wap.example.org',
			'WP_ROCKET_WAP_ISSUER_URL' => 'https://issuer.example.org/token',
		];

		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) use ( $constants ) {
				return $constants[ $name ] ?? $default;
			}
		);

		$config = $this->subscriber->get_column_config();

		$this->assertSame( 'https://wap.example.org', $config['server_url'] );
		$this->assertSame( 'https://issuer.example.org/token', $config['grnd']['issuer_url'] );
	}

	public function testShouldAlwaysAskForTheSupportAgent() {
		// An expired license is kept off this agent by the token, not by the page context.
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		$this->assertSame( 'support', $this->subscriber->get_column_config()['page_context'] );
	}

	public function testShouldMapTheWapCapabilityToWpRocketCapability() {
		$this->assertSame( 'rocket_manage_options', $this->subscriber->get_rocket_capability() );
	}

	public function testShouldReturnEmptyWhenTheUserCannotUseTheAssistant() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( (object) [ 'can_use_ai' => false ] );

		$this->assertSame( [], $this->subscriber->get_column_config() );
	}

	public function testShouldReturnTheConfigurationWhenTheFlagIsMissing() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( (object) [ 'licence_account' => 3 ] );

		$this->assertSame( 'wprocket', $this->subscriber->get_column_config()['product'] );
	}

	public function testShouldReturnTheConfigurationWhenTheUserDataCannotBeRead() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( false );

		$this->assertSame( 'wprocket', $this->subscriber->get_column_config()['product'] );
	}

	public function testShouldReturnEmptyWithoutReadingTheUserDataWhenTheKeyIsNotValid() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );
		Functions\when( 'rocket_valid_key' )->justReturn( false );
		$this->user_client->shouldNotReceive( 'get_user_data' );

		$this->assertSame( [], $this->subscriber->get_column_config() );
	}
}
