<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\Rest;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\DBTrait;
use WPMedia\PHPUnit\Integration\RESTfulTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Rest::remove_page
 * @group  RocketCDN
 * @group AdminOnly
 */
class Test_RemovePage extends RESTfulTestCase {
	use CapTrait, DBTrait;

	private $admin_id;

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
		self::truncateRocketCDNTable();
	}

	public function tear_down() {
		wp_set_current_user( 0 );
		self::truncateRocketCDNTable();
		parent::tear_down();
	}

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

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ) {
		$query       = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' );
		$inserted_ids = [];

		// Pre-insert items if configured.
		foreach ( $config['prefill'] as $item ) {
			$inserted_ids[ $item['url'] ] = $query->add_item(
				[
					'url'           => $item['url'],
					'title'         => $item['title'],
					'modified'      => current_time( 'mysql' ),
					'last_accessed' => current_time( 'mysql' ),
				]
			);
		}

		// Resolve the ID to delete.
		if ( 'nonexistent' === $config['delete'] ) {
			$id = 99999;
		} elseif ( 'unauthenticated' === $config['delete'] ) {
			wp_set_current_user( 0 );
			$id = 1;
		} else {
			$id = $inserted_ids[ $config['delete'] ] ?? 99999;
		}

		$response = $this->doRestRequest( 'DELETE', "/wp-rocket/v1/rocketcdn/pages/{$id}" );

		foreach ( $expected as $key => $value ) {
			switch ( $key ) {
				case 'count':
					$this->assertSame( $value, $response['count'] );
					break;
				case 'pages':
					$this->assertSame( $value, $response['pages'] );
					break;
				case 'remaining_url':
					$this->assertSame( $value, $response['pages'][0]['url'] );
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
