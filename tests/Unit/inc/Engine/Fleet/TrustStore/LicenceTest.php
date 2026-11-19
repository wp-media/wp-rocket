<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Fleet\TrustStore;

use Mockery;
use WP_Rocket\Engine\Fleet\TrustStore;
use WP_Rocket\Engine\License\API\RemoteSettingsClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Tests for WP_Rocket\Engine\Fleet\TrustStore::licence()
 *
 * The bridge refuses every consent grant whose licence differs from this, so a
 * wrong answer must be empty ("trust nobody"), never a guess.
 *
 * @group Fleet
 */
class LicenceTest extends TestCase {
	/**
	 * Test the licence is read from either response shape, and nothing else is trusted.
	 *
	 * @dataProvider configTestData
	 *
	 * @param mixed  $response What get_remote_settings_data() returns.
	 * @param string $expected The licence.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( $response, string $expected ): void {
		$client = Mockery::mock( RemoteSettingsClient::class );
		$client->shouldReceive( 'get_remote_settings_data' )->andReturn( $response );

		$this->assertSame( $expected, ( new TrustStore( $client ) )->licence() );
	}
}
