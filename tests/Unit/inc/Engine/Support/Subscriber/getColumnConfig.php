<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\User;
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
	private $user;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->user       = Mockery::mock( User::class );
		$this->subscriber = new Subscriber( Mockery::mock( Rest::class ), Mockery::mock( Meta::class ), $this->options, $this->user );
	}

	private function set_licence( $key, $email ) {
		$this->options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( $key );
		$this->options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( $email );
	}

	private function set_expiration( $expiration, $expired ) {
		$this->user->shouldReceive( 'get_license_expiration' )->andReturn( $expiration );
		$this->user->shouldReceive( 'is_license_expired' )->andReturn( $expired );
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
		$this->set_expiration( time() + 3600, false );

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
		$this->set_expiration( time() + 3600, false );

		Functions\when( 'rocket_get_constant' )->alias(
			function ( $name, $default = null ) {
				return $default;
			}
		);

		$config = $this->subscriber->get_column_config();

		$this->assertSame( 'https://wordpress-agentic-platform-production.public-default.k8spod4-cph3.ingress.k8s.g1i.one', $config['server_url'] );
		$this->assertSame( 'https://wp-rocket.me/wp-json/grnd/v1/token', $config['grnd']['issuer_url'] );
	}

	public function testShouldUseTheUrlsFromTheConstants() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );
		$this->set_expiration( time() + 3600, false );

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

	public function testShouldUseTheExpiredSupportAgentWhenTheLicenseIsExpired() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );
		$this->set_expiration( time() - 3600, true );

		$this->assertSame( 'support-expired', $this->subscriber->get_column_config()['page_context'] );
	}

	public function testShouldKeepTheSupportAgentWhenTheExpirationIsUnknown() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );
		$this->set_expiration( 0, true );

		$this->assertSame( 'support', $this->subscriber->get_column_config()['page_context'] );
	}

	public function testShouldMapTheWapCapabilityToWpRocketCapability() {
		$this->assertSame( 'rocket_manage_options', $this->subscriber->get_rocket_capability( 'wap_use_ai' ) );
	}
}
