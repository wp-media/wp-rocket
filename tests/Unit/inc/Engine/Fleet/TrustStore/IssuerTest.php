<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Fleet\TrustStore;

use Mockery;
use WP_Rocket\Engine\Fleet\TrustStore;
use WP_Rocket\Engine\License\API\RemoteSettingsClient;
use WP_Rocket\Tests\Unit\TestCase;
use WPMedia\FleetBridge\Contract\TrustStore as TrustStoreContract;

/**
 * Tests for WP_Rocket\Engine\Fleet\TrustStore::issuer() and ::keySetUrl()
 *
 * These are the only two questions the verifier asks this install, and the
 * answer to both decides whose signature is believed. Every case below is a
 * way of answering wrong, and the required answer to all of them is the empty
 * string — which the verifier reads as "trust nobody".
 *
 * @group Fleet
 */
class IssuerTest extends TestCase {
	/**
	 * Test the trust store reads the fleet block, in either response shape.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected values, per role.
	 *
	 * @return void
	 */
	public function testShouldReturnExpected( array $config, array $expected ): void {
		$store = new TrustStore( $this->clientReturning( $config['response'] ) );

		$this->assertSame( $expected['command_issuer'], $store->issuer( TrustStoreContract::COMMAND ) );
		$this->assertSame( $expected['consent_issuer'], $store->issuer( TrustStoreContract::CONSENT ) );
		$this->assertSame( $expected['command_keys'], $store->keySetUrl( TrustStoreContract::COMMAND ) );
		$this->assertSame( $expected['consent_keys'], $store->keySetUrl( TrustStoreContract::CONSENT ) );
	}

	/**
	 * Test the licence channel is polled once however many questions are asked.
	 *
	 * Four questions are asked per request. Each one going to the licence API
	 * would be four HTTP calls in a permission callback, on a route that is
	 * reachable by anyone.
	 *
	 * @return void
	 */
	public function testShouldReadTheLicenceChannelOnlyOnce(): void {
		$client = Mockery::mock( RemoteSettingsClient::class );
		$client->shouldReceive( 'get_remote_settings_data' )
			->once()
			->andReturn( $this->response( [ 'issuer' => 'grn:fleet' ] ) );

		$store = new TrustStore( $client );

		$store->issuer( TrustStoreContract::COMMAND );
		$store->issuer( TrustStoreContract::CONSENT );
		$store->keySetUrl( TrustStoreContract::COMMAND );
		$store->keySetUrl( TrustStoreContract::CONSENT );
	}

	/**
	 * A settings client answering with the given decoded response.
	 *
	 * @param mixed $response What get_remote_settings_data() returns.
	 *
	 * @return RemoteSettingsClient
	 */
	private function clientReturning( $response ): RemoteSettingsClient {
		$client = Mockery::mock( RemoteSettingsClient::class );
		$client->shouldReceive( 'get_remote_settings_data' )->andReturn( $response );

		return $client;
	}

	/**
	 * A cache-miss shaped response: settings nested under `data`.
	 *
	 * @param array $fleet The fleet block.
	 *
	 * @return object
	 */
	private function response( array $fleet ): object {
		return (object) [ 'data' => (object) [ 'fleet' => (object) $fleet ] ];
	}
}
