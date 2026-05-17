<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\Rest;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\DBTrait;
use WPMedia\PHPUnit\Integration\RESTfulTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Rest::add_page
 * @group  RocketCDN
 * @group AdminOnly
 */
class Test_AddPage extends RESTfulTestCase {
	use CapTrait, DBTrait;

	private $admin_id;
	private $post_id;
	private $post_url;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installRocketCDNTable();
	}

	public static function tear_down_after_class() {
		self::uninstallRocketCDNTable();
		parent::tear_down_after_class();
	}

	private $config;

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

	public function set_up() {
		parent::set_up();
		self::setAdminCap();
		$this->admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->admin_id );

		$this->post_id  = $this->factory()->post->create( [ 'post_status' => 'publish', 'post_type' => 'page' ] );
		$this->post_url = untrailingslashit( get_permalink( $this->post_id ) );

		add_filter( 'pre_http_request', [ $this, 'mock_http_response' ], 10, 3 );
		set_transient(
			'rocketcdn_status',
			[
				'subscription_status' => 'running',
				'cdn_url'             => 'example1.org',
			],
			HOUR_IN_SECONDS
		);

		self::truncateRocketCDNTable();
	}

	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'mock_http_response' ], 10 );
		wp_set_current_user( 0 );
		wp_delete_post( $this->post_id, true );
		self::truncateRocketCDNTable();
		delete_transient( 'rocketcdn_status' );
		parent::tear_down();
	}

	public function mock_http_response( $pre, $args, $url ) {
		if ( strpos( $url, 'this-page-does-not-exist' ) !== false ) {
			return [
				'response' => [
					'code'    => 404,
					'message' => 'Not Found',
				],
				'body' => '',
			];
		}
		if (
			strpos( $url, 'http://example.org' ) === 0 ||
			strpos( $url, 'https://example.org' ) === 0 ||
			strpos( $url, home_url() ) === 0
		) {
			return [
				'response' => [
					'code'    => 200,
					'message' => 'OK',
				],
				'body' => '<html><head><title>Test Page</title></head><body>Test content</body></html>',
			];
		}

		return $pre;
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ) {
		// Pre-fill table to limit if configured.
		if ( ! empty( $config['prefill_count'] ) ) {
			$query = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' );

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

		// Add the URL once first if testing duplicates.
		if ( ! empty( $config['add_first'] ) ) {
			$this->doRestRequest( 'POST', '/wp-rocket/v1/rocketcdn/pages', [ 'url' => $this->post_url ] );
			wp_cache_flush();
		}

		// Set unauthenticated if configured.
		if ( ! empty( $config['unauthenticated'] ) ) {
			wp_set_current_user( 0 );
		}

		if ( 'post_url' === $config['url'] ) {
			$url = $this->post_url;
		} elseif ( 'homepage' === $config['url'] ) {
			$url = untrailingslashit( home_url() );
		} else {
			$url = $config['url'];
		}

		$response = $this->doRestRequest( 'POST', '/wp-rocket/v1/rocketcdn/pages', [ 'url' => $url ] );

		foreach ( $expected as $key => $value ) {
			switch ( $key ) {
				case 'count':
					$this->assertSame( $value, $response['count'] );
					break;
				case 'limit':
					$this->assertSame( $value, $response['limit'] );
					break;
				case 'url':
					$this->assertSame( $url, $response['pages'][0]['url'] );
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
