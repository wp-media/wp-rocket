<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Subscriber;

use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Admin\Options_Data;
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
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new Subscriber( Mockery::mock( Rest::class ), Mockery::mock( Meta::class ), $this->options );
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
		$this->assertSame( 'wprocket', $config['brand'] );
		$this->assertSame( 'support', $config['page_context'] );
		$this->assertSame( 'test-consumer-key|user@example.org', $config['grnd']['license_key'] );
		$this->assertSame( 'https://wp-rocket.me/terms/', $config['terms_url'] );
		$this->assertSame( 'https://wp-rocket.me/privacy-policy/', $config['privacy_url'] );
		$this->assertSame( 'https://wp-rocket.me/privacy-policy/', $config['site_access_url'] );
		$this->assertSame( [ 'settings_page_wprocket' ], $config['screens'] );
		$this->assertSame( [ 'side' => 'right' ], $config['column'] );
	}

	public function testShouldUseTheFilteredServerAndIssuerUrls() {
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		Filters\expectApplied( 'rocket_wap_server_url' )->once()->andReturn( 'https://wap.example.org' );
		Filters\expectApplied( 'rocket_wap_issuer_url' )->once()->andReturn( 'https://issuer.example.org/token' );

		$config = $this->subscriber->get_column_config();

		$this->assertSame( 'https://wap.example.org', $config['server_url'] );
		$this->assertSame( 'https://issuer.example.org/token', $config['grnd']['issuer_url'] );
	}

	public function testShouldMapTheWapCapabilityToWpRocketCapability() {
		$this->assertSame( 'rocket_manage_options', $this->subscriber->get_rocket_capability( 'wap_use_ai' ) );
	}
}
