<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\Rest;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\DBTrait;
use WPMedia\PHPUnit\Integration\RESTfulTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Rest::add_homepage
 * @group  RocketCDN
 * @group AdminOnly
 */
class Test_AddHomepage extends RESTfulTestCase {
	use CapTrait, DBTrait;

	private $admin_id;

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

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installRocketCDNTable();
	}

	public static function tear_down_after_class() {
		self::uninstallRocketCDNTable();
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();
		self::setAdminCap();
		$this->admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->admin_id );
		add_filter( 'pre_http_request', [ $this, 'mock_http_response' ], 10, 3 );

		// add_page() calls create_subscription() before inserting the page. Simulate an
		// already-active subscription so it short-circuits without an external API call
		// (matches the pattern used in AddPage.php).
		set_transient(
			'rocketcdn_status',
			[
				'subscription_status' => 'running',
				'cdn_url'              => 'example1.org',
			],
			HOUR_IN_SECONDS
		);

		self::truncateRocketCDNTable();
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'mock_http_response' ], 10 );
		wp_set_current_user( 0 );
		self::truncateRocketCDNTable();
		delete_transient( 'rocketcdn_status' );
		parent::tear_down();
	}

	public function mock_http_response( $pre, $args, $url ) {
		// Mock successful response for URLs on the test domain (example.org)
		if ( strpos( $url, 'http://example.org' ) === 0 || strpos( $url, 'https://example.org' ) === 0 ) {
			return [
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
				'body'     => '<html><head><title>Test Page Title</title></head><body>Test content</body></html>',
			];
		}

		return $pre;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( $config, $expected ) {
		$query = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' );

		if ( ! empty( $config['prefill_count'] ) ) {
			for ( $i = 1; $i <= $config['prefill_count']; $i++ ) {
				$query->add_item(
					[
						'url'           => "http://example.org/page-{$i}",
						'title'         => "Page {$i}",
						'modified'      => current_time( 'mysql' ),
						'last_accessed' => current_time( 'mysql' ),
					]
				);
			}
		}

		if ( ! empty( $config['add_homepage_first'] ) ) {
			$this->doRestRequest( 'POST', '/wp-rocket/v1/rocketcdn/pages/homepage' );
			wp_cache_flush();
		}

		// Set unauthenticated if configured.
		if ( ! empty( $config['unauthenticated'] ) ) {
			wp_set_current_user( 0 );
		}

		$response = $this->doRestRequest( 'POST', '/wp-rocket/v1/rocketcdn/pages/homepage' );

		foreach ( $expected as $key => $value ) {
			switch ( $key ) {
				case 'count':
					$this->assertSame( $value, $response['count'] );
					break;
				case 'url':
					$this->assertSame( untrailingslashit( home_url() ), $response['pages'][0]['url'] );
					break;
				case 'title':
					$this->assertSame( 'Homepage', $response['pages'][0]['title'] );
					break;
				case 'code':
					$this->assertSame( $value, $response['code'] );
					break;
				case 'status':
					$this->assertSame( $value, $response['data']['status'] );
					break;
			}
		}
	}
}
