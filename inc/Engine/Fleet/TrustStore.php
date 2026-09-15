<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Fleet;

use WPMedia\FleetBridge\Contract\TrustStore as TrustStoreContract;
use WP_Rocket\Engine\License\API\RemoteSettingsClient;

/**
 * Who this install trusts, read from the licence channel it already polls.
 *
 * Identity and key locations only, deliberately no consent. This channel is
 * cached for a day, so consent read from here would take a day to withdraw.
 * Key locations are safe to cache that long, because a rotation publishes two
 * keys at once. Whether the owner allows a command arrives with the command.
 *
 * Two issuers, because there are two things to verify: Fleet's key proves a
 * command came from Fleet, wp-rocket.me's proves the owner allows it.
 *
 * @since 3.23.4
 */
class TrustStore implements TrustStoreContract {
	/**
	 * The remote settings API client.
	 *
	 * @var RemoteSettingsClient
	 */
	private $api_client;

	/**
	 * The `fleet` block, once read.
	 *
	 * @var array|null
	 */
	private $block = null;

	/**
	 * Instantiate the class.
	 *
	 * @param RemoteSettingsClient $api_client The remote settings API client.
	 */
	public function __construct( RemoteSettingsClient $api_client ) {
		$this->api_client = $api_client;
	}

	/**
	 * The issuer GRN accepted for a role.
	 *
	 * @since 3.23.4
	 *
	 * @param string $role One of the contract's role constants.
	 *
	 * @return string Empty when this install has not been told, which the
	 *                bridge reads as "trust nobody".
	 */
	public function issuer( string $role ): string {
		return $this->value( self::CONSENT === $role ? 'crm_issuer' : 'issuer' );
	}

	/**
	 * Where that issuer publishes its key set.
	 *
	 * @since 3.23.4
	 *
	 * @param string $role One of the contract's role constants.
	 *
	 * @return string Empty when this install has not been told.
	 */
	public function keySetUrl( string $role ): string {
		return $this->value( self::CONSENT === $role ? 'crm_jwks_url' : 'jwks_url' );
	}

	/**
	 * One string from the `fleet` block.
	 *
	 * @since 3.23.4
	 *
	 * @param string $key Field name.
	 *
	 * @return string
	 */
	private function value( string $key ): string {
		$block = $this->block();

		return isset( $block[ $key ] ) && is_string( $block[ $key ] ) ? trim( $block[ $key ] ) : '';
	}

	/**
	 * The `fleet` block from the remote settings response.
	 *
	 * Normalises the two shapes `get_remote_settings_data()` returns: the whole
	 * decoded response on a cache miss, where settings live under `->data`, and
	 * the inner `data` itself on a hit. Reading one shape works for half the
	 * calls, depending on transient expiry.
	 *
	 * @since 3.23.4
	 *
	 * @return array
	 */
	private function block(): array {
		if ( null !== $this->block ) {
			return $this->block;
		}

		$this->block = [];
		$response    = $this->api_client->get_remote_settings_data();

		if ( ! is_object( $response ) ) {
			return $this->block;
		}

		$settings = isset( $response->data ) && is_object( $response->data ) ? $response->data : $response;

		if ( isset( $settings->fleet ) && is_object( $settings->fleet ) ) {
			$this->block = (array) $settings->fleet;
		}

		return $this->block;
	}
}
