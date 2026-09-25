<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\SubscriptionController;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\CheckStatusAPIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\CreateAPIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\WebsiteSearch;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Engine\CDN\RocketCDN\Queue;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController::get_express_checkout_url
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController::get_express_checkout_url
 * @group  CDN
 * @group  RocketCDN
 */
class Test_GetExpressCheckoutUrl extends TestCase {

	/**
	 * @var Mockery\MockInterface|UserClient
	 */
	private $user_client;

	/**
	 * @var Mockery\MockInterface|APIClient
	 */
	private $api_client;

	/**
	 * @var Mockery\MockInterface|CreateAPIClient
	 */
	private $create_api_client;

	/**
	 * @var Mockery\MockInterface|CDNOptionsManager
	 */
	private $options_manager;

	/**
	 * @var Mockery\MockInterface|Queue
	 */
	private $queue;

	/**
	 * @var Mockery\MockInterface|CheckStatusAPIClient
	 */
	private $check_status_api_client;

	/**
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * @var Mockery\MockInterface|WebsiteSearch
	 */
	private $website_search;

	public function set_up(): void {
		parent::set_up();

		defined( 'WP_ROCKET_PLUGIN_SLUG' ) || define( 'WP_ROCKET_PLUGIN_SLUG', 'wp-rocket' );

		$this->api_client              = Mockery::mock( APIClient::class );
		$this->create_api_client       = Mockery::mock( CreateAPIClient::class );
		$this->options_manager         = Mockery::mock( CDNOptionsManager::class );
		$this->queue                   = Mockery::mock( Queue::class );
		$this->check_status_api_client = Mockery::mock( CheckStatusAPIClient::class );
		$this->user                    = Mockery::mock( User::class );
		$this->website_search          = Mockery::mock( WebsiteSearch::class );
		$this->user_client             = Mockery::mock( UserClient::class );

		Functions\when( 'admin_url' )->justReturn( 'https://example.com/wp-admin/options-general.php' );
		Functions\when( 'esc_url_raw' )->returnArg();

		// Mirrors WordPress's add_query_arg( $args, $url ) without re-encoding already-encoded values.
		Functions\when( 'add_query_arg' )->alias(
			static function ( $args, $url = '' ) {
				if ( is_array( $args ) && '' !== $url ) {
					$parts = [];
					foreach ( $args as $key => $value ) {
						$parts[] = $key . '=' . $value;
					}
					return $url . '?' . implode( '&', $parts );
				}
				return $url;
			}
		);
	}

	/**
	 * Creates a SubscriptionController instance under test.
	 *
	 * @return SubscriptionController
	 */
	private function get_controller(): SubscriptionController {
		return new SubscriptionController(
			$this->api_client,
			$this->create_api_client,
			$this->options_manager,
			$this->queue,
			$this->check_status_api_client,
			$this->user,
			$this->website_search,
			$this->user_client
		);
	}

	/**
	 * Tests that get_express_checkout_url returns the expected value.
	 *
	 * Config key `button_url`:
	 *   false  → get_user_data() returns false (no API data at all)
	 *   null   → user data exists but the button object has no `url` property
	 *   string → user data has a button URL (empty string triggers the empty-guard; non-empty produces the checkout URL)
	 *
	 * @dataProvider configTestData
	 *
	 * @param array  $config   Test configuration.
	 * @param string $expected Expected return value.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedUrl( array $config, string $expected ): void {
		$button_url = $config['button_url'];

		if ( false === $button_url ) {
			$user_data = false;
		} elseif ( null === $button_url ) {
			$user_data = (object) [ 'rocketcdn' => (object) [ 'button' => (object) [] ] ];
		} else {
			$user_data = (object) [ 'rocketcdn' => (object) [ 'button' => (object) [ 'url' => $button_url ] ] ];
		}

		$this->user_client->shouldReceive( 'get_user_data' )->andReturn( $user_data );

		$this->assertSame( $expected, $this->get_controller()->get_express_checkout_url() );
	}
}
