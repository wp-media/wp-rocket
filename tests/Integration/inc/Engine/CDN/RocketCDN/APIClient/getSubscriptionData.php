<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\APIClient;

use ReflectionMethod;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 * and the private fetch/locking logic it delegates to.
 *
 * @group RocketCDN
 * @group CDN
 */
class Test_GetSubscriptionData extends TestCase {

	protected static $use_settings_trait = true;

	protected static $transients = [
		'rocketcdn_status' => null,
	];

	const TOKEN = '1234567890123456789012345678901234567890';

	/**
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Number of times the mocked subscription endpoint was actually hit.
	 *
	 * @var int
	 */
	private $request_count = 0;

	public function set_up() {
		parent::set_up();

		$container        = apply_filters( 'rocket_container', null );
		$this->api_client = $container->get( 'rocketcdn_api_client' );

		$this->request_count = 0;

		add_filter( 'home_url', [ $this, 'home_url_cb' ] );

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( APIClient::SUBSCRIPTION_FETCH_LOCK );
	}

	public function tear_down() {
		remove_all_filters( 'pre_http_request' );
		remove_filter( 'home_url', [ $this, 'home_url_cb' ] );

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( APIClient::SUBSCRIPTION_FETCH_LOCK );

		parent::tear_down();
	}

	public function home_url_cb(): string {
		return 'http://example.org';
	}

	private function set_token(): void {
		update_option( 'rocketcdn_user_token', self::TOKEN );
	}

	/**
	 * Intercepts calls to the RocketCDN subscription status endpoint and counts
	 * how many times it's actually hit, so tests can assert on duplicate-request
	 * behavior. Any other outbound request is left untouched.
	 *
	 * @param int        $code HTTP response code to return.
	 * @param array|null $body Response body to JSON-encode, or null for an empty body.
	 */
	private function mock_subscription_endpoint( int $code, $body = [] ): void {
		add_filter(
			'pre_http_request',
			function ( $preempt, $_args, $url ) use ( $code, $body ) {
				if ( false === strpos( $url, 'https://rocketcdn.me/api/subscription/example.org/status' ) ) {
					return $preempt;
				}

				$this->request_count++;

				return [
					'response' => [ 'code' => $code ],
					'body'     => null === $body ? '' : wp_json_encode( $body ),
				];
			},
			10,
			3
		);
	}

	/**
	 * Invokes the private get_remote_subscription_data() directly, for scenarios
	 * that need to bypass get_subscription_data()'s own transient short-circuit.
	 *
	 * @return array
	 */
	private function invoke_get_remote_subscription_data() {
		$method = new ReflectionMethod( $this->api_client, 'get_remote_subscription_data' );

		// PHP 8.1+: setAccessible() is not needed and is deprecated.
		// PHP 7.4: still required to invoke a private method.
		if ( PHP_VERSION_ID < 80100 ) {
			$method->setAccessible( true );
		}

		return $method->invoke( $this->api_client );
	}

	public function testShouldReturnCachedTransientWithoutHittingApi() {
		$cached = [
			'success'             => true,
			'subscription_status' => 'running',
		];
		set_transient( 'rocketcdn_status', $cached, DAY_IN_SECONDS );

		$this->mock_subscription_endpoint( 200, [ 'success' => true ] );

		$this->assertSame( $cached, $this->api_client->get_subscription_data() );
		$this->assertSame( 0, $this->request_count, 'A populated transient must short-circuit the API call entirely.' );
	}

	public function testShouldReturnDefaultWithoutApiCallWhenTokenMissing() {
		$this->mock_subscription_endpoint( 200, [ 'success' => true ] );

		$data = $this->api_client->get_subscription_data();

		$this->assertFalse( $data['success'] );
		$this->assertSame( 0, $this->request_count, 'No API request should be made without a saved token.' );
		$this->assertFalse( get_option( APIClient::SUBSCRIPTION_FETCH_LOCK ), 'No lock should be taken when the request never reaches the API.' );
	}

	public function testShouldReturnDefaultOnEmptyResponseBody() {
		$this->set_token();
		$this->mock_subscription_endpoint( 200, null );

		$data = $this->api_client->get_subscription_data();

		$this->assertFalse( $data['success'] );
		$this->assertSame( 1, $this->request_count );
		$this->assertFalse( get_option( APIClient::SUBSCRIPTION_FETCH_LOCK ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldMapAndCacheRemoteResponse( array $config, array $expected_subset ) {
		$this->set_token();
		$this->mock_subscription_endpoint( $config['status_code'], $config['body'] );

		$data = $this->api_client->get_subscription_data();

		foreach ( $expected_subset as $key => $value ) {
			$this->assertSame( $value, $data[ $key ], "Unexpected value for '{$key}'." );
		}

		$this->assertSame( 1, $this->request_count );
		$this->assertFalse( get_option( APIClient::SUBSCRIPTION_FETCH_LOCK ), 'The lock must be released once the fetch completes.' );

		// A second call within the same request must reuse the transient it just set,
		// not fire another request against the API.
		$this->api_client->get_subscription_data();
		$this->assertSame( 1, $this->request_count, 'A second call must reuse the freshly cached transient.' );
	}

	public function testShouldNotFireDuplicateRequestWhenAnotherFetchIsInFlight() {
		$this->set_token();
		$this->mock_subscription_endpoint(
			200,
			[
				'success' => true,
				'status'  => 'running',
			]
		);

		// Simulate a concurrent request that is already fetching the same data.
		// Deliberately a few seconds in the past (not time() exactly): a same-second
		// value would mask the add_option()-based bug this guards against, since
		// MySQL's "INSERT ... ON DUPLICATE KEY UPDATE" reports no rows changed - and
		// so correctly refuses to "acquire" - when the new value equals the old one,
		// which happens to be true whenever both timestamps land in the same second.
		add_option( APIClient::SUBSCRIPTION_FETCH_LOCK, time() - 5, '', false );

		$data = $this->api_client->get_subscription_data();

		$this->assertSame( 0, $this->request_count, 'A held lock must prevent firing a duplicate outbound API call.' );
		$this->assertFalse( $data['success'], 'Without a populated transient, the loser falls back to the safe default.' );

		// The loser must not touch a lock it doesn't own.
		$this->assertNotFalse( get_option( APIClient::SUBSCRIPTION_FETCH_LOCK ), 'The loser must leave the winner\'s lock untouched.' );
	}

	public function testShouldReuseFreshTransientWhenLockIsHeldByAnotherRequest() {
		$this->set_token();
		$cached = [
			'success'             => true,
			'subscription_status' => 'running',
		];
		set_transient( 'rocketcdn_status', $cached, DAY_IN_SECONDS );

		$this->mock_subscription_endpoint( 200, [ 'success' => true ] );

		// get_subscription_data() short-circuits on the transient before the lock is
		// even considered, so call the fetch method directly to prove that the
		// lock-held fallback itself prefers a freshly populated transient over the
		// generic default. A few seconds in the past, not time() exactly - see the
		// comment in testShouldNotFireDuplicateRequestWhenAnotherFetchIsInFlight().
		add_option( APIClient::SUBSCRIPTION_FETCH_LOCK, time() - 5, '', false );

		$this->assertSame( $cached, $this->invoke_get_remote_subscription_data() );
		$this->assertSame( 0, $this->request_count );
	}

	public function testShouldReclaimStaleLock() {
		$this->set_token();
		$this->mock_subscription_endpoint(
			200,
			[
				'success' => true,
				'status'  => 'running',
			]
		);

		// A lock older than the TTL must be treated as abandoned, e.g. left behind by
		// a request that crashed or timed out mid-fetch.
		add_option( APIClient::SUBSCRIPTION_FETCH_LOCK, time() - ( APIClient::SUBSCRIPTION_FETCH_LOCK_TTL + 5 ), '', false );

		$data = $this->api_client->get_subscription_data();

		$this->assertTrue( $data['success'] );
		$this->assertSame( 1, $this->request_count, 'A stale lock must be reclaimed so the fetch can proceed.' );
		$this->assertFalse( get_option( APIClient::SUBSCRIPTION_FETCH_LOCK ), 'The lock must be released once the reclaiming fetch completes.' );
	}

	public function testShouldReleaseLockEvenWhenFetchFails() {
		$this->set_token();
		$this->mock_subscription_endpoint( 500 );

		$this->api_client->get_subscription_data();

		$this->assertSame( 1, $this->request_count );
		$this->assertFalse( get_option( APIClient::SUBSCRIPTION_FETCH_LOCK ), 'The lock must be released even when the API call does not succeed.' );
	}

	public function testConcurrentCallsShouldOnlyFireOneApiRequest() {
		$this->set_token();
		$this->mock_subscription_endpoint(
			200,
			[
				'success' => true,
				'status'  => 'running',
			]
		);

		// First caller wins the lock and performs the real fetch.
		$winner = $this->api_client->get_subscription_data();

		// A second, independent APIClient instance (mirroring the non-shared
		// 'rocketcdn_api_client' container registration used by different
		// subscribers/controllers within the same request) must not repeat the
		// request now that the transient has been populated.
		$container     = apply_filters( 'rocket_container', null );
		$second_client = $container->get( 'rocketcdn_api_client' );
		$loser         = $second_client->get_subscription_data();

		$this->assertSame( 1, $this->request_count );
		$this->assertTrue( $winner['success'] );
		$this->assertSame( $winner, $loser );
	}
}
