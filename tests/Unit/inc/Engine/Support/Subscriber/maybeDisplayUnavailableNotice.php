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
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::maybe_display_unavailable_notice
 *
 * @group Support
 */
class TestMaybeDisplayUnavailableNotice extends TestCase {
	private $options;
	private $user_client;
	private $subscriber;

	public function set_up() {
		parent::set_up();

		if ( ! defined( 'WP_ROCKET_PLUGIN_NAME' ) ) {
			define( 'WP_ROCKET_PLUGIN_NAME', 'WP Rocket' );
		}

		$this->options     = Mockery::mock( Options_Data::class );
		$this->user_client = Mockery::mock( UserClient::class );
		$this->subscriber  = new Subscriber( Mockery::mock( Rest::class ), Mockery::mock( Meta::class ), $this->options, $this->user_client );

		Functions\when( 'rocket_valid_key' )->justReturn( true );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( (object) [ 'can_use_ai' => true ] )->byDefault();

		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( '__' )->returnArg();
	}

	private function set_screen( $id ) {
		Functions\when( 'get_current_screen' )->justReturn( (object) [ 'id' => $id ] );
	}

	/**
	 * Mocks the WAP client class, which is only loaded by the library's own bootstrap.
	 *
	 * @param bool $available     Whether Application Passwords are available.
	 * @param bool $https_missing Whether HTTPS is missing.
	 */
	private function mock_app_password_manager( $available, $https_missing = false ) {
		/** @var \Mockery\MockInterface $manager The class is aliased, so PHPStan cannot resolve it. */
		$manager = Mockery::mock( 'alias:GroupOne\WapClient\AppPasswordManager' );
		$manager->shouldReceive( 'are_app_passwords_available' )->andReturn( $available );
		$manager->shouldReceive( 'is_https_missing' )->andReturn( $https_missing );
	}

	private function set_licence( $key, $email ) {
		$this->options->shouldReceive( 'get' )->with( 'consumer_key', '' )->andReturn( $key );
		$this->options->shouldReceive( 'get' )->with( 'consumer_email', '' )->andReturn( $email );
	}

	/**
	 * The library is not loaded in this process, so its availability cannot even be checked.
	 */
	public function testShouldNotDisplayWhenTheWapClientIsMissing() {
		$this->set_screen( 'settings_page_wprocket' );
		$this->options->shouldNotReceive( 'get' );

		Functions\expect( 'rocket_notice_html' )->never();

		$this->subscriber->maybe_display_unavailable_notice();

		$this->assertFalse( class_exists( 'GroupOne\WapClient\AppPasswordManager', false ) );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotDisplayWithoutTheCapability() {
		/** @var \Mockery\MockInterface $manager The class is aliased, so PHPStan cannot resolve it. */
		$manager = Mockery::mock( 'alias:GroupOne\WapClient\AppPasswordManager' );
		$manager->shouldNotReceive( 'are_app_passwords_available' );

		Functions\when( 'current_user_can' )->justReturn( false );
		$this->set_screen( 'settings_page_wprocket' );
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		Functions\expect( 'rocket_notice_html' )->never();

		$this->subscriber->maybe_display_unavailable_notice();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotDisplayWithoutAScreen() {
		/** @var \Mockery\MockInterface $manager The class is aliased, so PHPStan cannot resolve it. */
		$manager = Mockery::mock( 'alias:GroupOne\WapClient\AppPasswordManager' );
		$manager->shouldNotReceive( 'are_app_passwords_available' );

		Functions\when( 'get_current_screen' )->justReturn( null );
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		Functions\expect( 'rocket_notice_html' )->never();

		$this->subscriber->maybe_display_unavailable_notice();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotDisplayOnAnotherScreen() {
		$this->mock_app_password_manager( false );
		$this->set_screen( 'dashboard' );
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		Functions\expect( 'rocket_notice_html' )->never();

		$this->subscriber->maybe_display_unavailable_notice();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotDisplayWithoutLicenceData() {
		$this->mock_app_password_manager( false );
		$this->set_screen( 'settings_page_wprocket' );
		$this->set_licence( '', '' );

		Functions\expect( 'rocket_notice_html' )->never();

		$this->subscriber->maybe_display_unavailable_notice();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotDisplayWhenApplicationPasswordsAreAvailable() {
		$this->mock_app_password_manager( true );
		$this->set_screen( 'settings_page_wprocket' );
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		Functions\expect( 'rocket_notice_html' )->never();

		$this->subscriber->maybe_display_unavailable_notice();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotDisplayWhenTheUserCannotUseTheAssistant() {
		$this->mock_app_password_manager( false );
		$this->set_screen( 'settings_page_wprocket' );
		$this->set_licence( 'test-consumer-key', 'user@example.org' );
		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( (object) [ 'can_use_ai' => false ] );

		Functions\expect( 'rocket_notice_html' )->never();

		$this->subscriber->maybe_display_unavailable_notice();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldDisplayHttpsReasonWhenHttpsIsMissing() {
		$this->mock_app_password_manager( false, true );
		$this->set_screen( 'settings_page_wprocket' );
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		Functions\expect( 'rocket_notice_html' )
			->once()
			->with(
				Mockery::on(
					function ( $args ) {
						return 'warning' === $args['status'] && false !== strpos( $args['message'], 'requires HTTPS' );
					}
				)
			);

		$this->subscriber->maybe_display_unavailable_notice();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldDisplayDisabledReasonWhenHttpsIsAvailable() {
		$this->mock_app_password_manager( false, false );
		$this->set_screen( 'settings_page_wprocket' );
		$this->set_licence( 'test-consumer-key', 'user@example.org' );

		Functions\expect( 'rocket_notice_html' )
			->once()
			->with(
				Mockery::on(
					function ( $args ) {
						return 'warning' === $args['status'] && false !== strpos( $args['message'], 'have been disabled' );
					}
				)
			);

		$this->subscriber->maybe_display_unavailable_notice();
	}
}
