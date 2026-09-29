<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\APIHandler\WebsiteSearch;

use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\WebsiteSearch;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\APIHandler\WebsiteSearch::find
 * and the private fetch/locking logic it delegates to.
 *
 * @group RocketCDN
 * @group CDN
 */
class Test_Find extends TestCase {

	protected static $use_settings_trait = true;

	protected static $transients = [
		'rocket_cdn_website_search' => null,
	];

	const TOKEN = '1234567890123456789012345678901234567890';

	/**
	 * @var WebsiteSearch
	 */
	private $website_search;

	/**
	 * Number of times the mocked website-search endpoint was actually hit.
	 *
	 * @var int
	 */
	private $request_count = 0;

	public function set_up() {
		parent::set_up();

		$container            = apply_filters( 'rocket_container', null );
		$this->website_search = $container->get( 'rocketcdn_website_search_api_client' );
		$this->website_search->set_site_url( 'http://example.org' );

		$this->request_count = 0;

		delete_transient( 'rocket_cdn_website_search' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK );
	}

	public function tear_down() {
		remove_all_filters( 'pre_http_request' );

		delete_transient( 'rocket_cdn_website_search' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK );

		parent::tear_down();
	}

	private function set_token(): void {
		update_option( 'rocketcdn_user_token', self::TOKEN );
	}

	/**
	 * Intercepts calls to the RocketCDN website-search endpoint and counts how
	 * many times it's actually hit, so tests can assert on duplicate-request
	 * behavior. Any other outbound request is left untouched.
	 *
	 * @param int        $code HTTP response code to return.
	 * @param array|null $body Response body to JSON-encode, or null for an empty body.
	 */
	private function mock_search_endpoint( int $code, $body = [] ): void {
		add_filter(
			'pre_http_request',
			function ( $preempt, $_args, $url ) use ( $code, $body ) {
				if ( false === strpos( $url, 'https://rocketcdn.me/api/website/search/' ) ) {
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

	public function testShouldReturnCachedTransientWithoutHittingApi() {
		$cached = [
			'subscription_status' => 'running',
			'plan_type'           => 'paid',
			'status_code'         => 200,
			'website_status'      => 'active',
		];
		set_transient( 'rocket_cdn_website_search', $cached, HOUR_IN_SECONDS );

		$this->mock_search_endpoint( 200, [ 'status' => 'active' ] );

		$this->assertSame( $cached, $this->website_search->find() );
		$this->assertSame( 0, $this->request_count, 'A populated transient must short-circuit the API call entirely.' );
	}

	public function testShouldReturnFalseWithoutApiCallWhenTokenMissing() {
		$this->mock_search_endpoint( 200, [ 'status' => 'active' ] );

		$this->assertFalse( $this->website_search->find() );
		$this->assertSame( 0, $this->request_count, 'No API request should be made without a saved token.' );
		$this->assertFalse( get_option( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK ), 'No lock should be taken when the request never reaches the API.' );
	}

	public function testShouldReturnFalseOnEmptyResponseBody() {
		$this->set_token();
		$this->mock_search_endpoint( 200, null );

		$this->assertFalse( $this->website_search->find() );
		$this->assertSame( 1, $this->request_count );
		$this->assertFalse( get_option( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK ) );
	}

	public function testShouldMapAndCacheSuccessfulResponse() {
		$this->set_token();
		$this->mock_search_endpoint(
			200,
			[
				'subscription_status'    => 'running',
				'subscription_plan_type' => 'paid',
				'status'                 => 'active',
			]
		);

		$result = $this->website_search->find();

		$this->assertSame(
			[
				'subscription_status' => 'running',
				'plan_type'           => 'paid',
				'status_code'         => 200,
				'website_status'      => 'active',
			],
			$result
		);
		$this->assertSame( 1, $this->request_count );
		$this->assertFalse( get_option( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK ), 'The lock must be released once the fetch completes.' );

		// A second call within the same request must reuse the transient it just
		// set, not fire another request against the API.
		$this->website_search->find();
		$this->assertSame( 1, $this->request_count, 'A second call must reuse the freshly cached transient.' );
	}

	public function testShouldNotFireDuplicateRequestWhenAnotherFetchIsInFlight() {
		$this->set_token();
		$this->mock_search_endpoint( 200, [ 'status' => 'active' ] );

		// Simulate a concurrent request that is already fetching the same data.
		// Deliberately a few seconds in the past (not time() exactly): a same-second
		// value would mask the add_option()-based race this guards against, since
		// MySQL's "INSERT ... ON DUPLICATE KEY UPDATE" reports no rows changed - and
		// so correctly refuses to "acquire" - when the new value equals the old one,
		// which happens to be true whenever both timestamps land in the same second.
		add_option( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK, time() - 5, '', false );

		$result = $this->website_search->find();

		$this->assertSame( 0, $this->request_count, 'A held lock must prevent firing a duplicate outbound API call.' );
		$this->assertFalse( $result, 'Without a populated transient, the loser falls back to false.' );

		// The loser must not touch a lock it doesn't own.
		$this->assertNotFalse( get_option( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK ), 'The loser must leave the winner\'s lock untouched.' );
	}

	public function testShouldReclaimStaleLock() {
		$this->set_token();
		$this->mock_search_endpoint( 200, [ 'status' => 'active' ] );

		// A lock older than the TTL must be treated as abandoned, e.g. left behind by
		// a request that crashed or timed out mid-fetch.
		add_option( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK, time() - ( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK_TTL + 5 ), '', false );

		$result = $this->website_search->find();

		$this->assertIsArray( $result );
		$this->assertSame( 1, $this->request_count, 'A stale lock must be reclaimed so the fetch can proceed.' );
		$this->assertFalse( get_option( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK ), 'The lock must be released once the reclaiming fetch completes.' );
	}

	public function testShouldReleaseLockEvenWhenFetchFails() {
		$this->set_token();
		$this->mock_search_endpoint( 500 );

		$this->assertFalse( $this->website_search->find() );
		$this->assertSame( 1, $this->request_count );
		$this->assertFalse( get_option( WebsiteSearch::WEBSITE_SEARCH_FETCH_LOCK ), 'The lock must be released even when the API call does not succeed.' );
	}

	public function testConcurrentCallsShouldOnlyFireOneApiRequest() {
		$this->set_token();
		$this->mock_search_endpoint( 200, [ 'status' => 'active' ] );

		// First caller wins the lock and performs the real fetch.
		$winner = $this->website_search->find();

		// A second, independent WebsiteSearch instance (mirroring the non-shared
		// 'rocketcdn_website_search_api_client' container registration used by
		// different callers within the same request) must not repeat the request
		// now that the transient has been populated.
		$container     = apply_filters( 'rocket_container', null );
		$second_client = $container->get( 'rocketcdn_website_search_api_client' );
		$second_client->set_site_url( 'http://example.org' );
		$loser = $second_client->find();

		$this->assertSame( 1, $this->request_count );
		$this->assertIsArray( $winner );
		$this->assertSame( $winner, $loser );
	}
}
