<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Support\Meta;
use WP_Rocket\Engine\Support\Rest;
use WP_Rocket\Engine\Support\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::register_column
 *
 * @group Support
 */
class TestRegisterColumn extends TestCase {
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

	/**
	 * The WAP client library is not loaded in this process, so there is nothing to register with.
	 */
	public function testShouldDoNothingWhenTheWapClientIsMissing() {
		$this->options->shouldNotReceive( 'get' );

		$this->subscriber->register_column();

		$this->assertFalse( class_exists( 'WapClient', false ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotRegisterWithoutLicenceData() {
		/** @var \Mockery\MockInterface $wap_client The class is aliased, so PHPStan cannot resolve it. */
		$wap_client = Mockery::mock( 'alias:WapClient' );
		$wap_client->shouldNotReceive( 'register_chat_column' );

		$this->set_licence( '', '' );

		$this->subscriber->register_column();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldRegisterTheChatColumnWithTheConfiguration() {
		/** @var \Mockery\MockInterface $wap_client The class is aliased, so PHPStan cannot resolve it. */
		$wap_client = Mockery::mock( 'alias:WapClient' );
		$wap_client->shouldReceive( 'register_chat_column' )
			->once()
			->with(
				Mockery::on(
					function ( $config ) {
						return 'wprocket' === $config['product']
							&& 'support' === $config['page_context']
							&& 'test-consumer-key|user@example.org' === $config['grnd']['license_key']
							&& [ 'settings_page_wprocket' ] === $config['screens'];
					}
				)
			);

		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		$this->subscriber->register_column();
	}
}
