<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Fleet;

use WPMedia\FleetBridge\Bridge;
use WPMedia\FleetBridge\Contract\Verifier;
use WPMedia\FleetBridge\Exception\NotAuthorised;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_Rocket\Engine\Abilities\Options\AllowedOptions;
use WP_Rocket\Engine\Abilities\Options\GetOptions;
use WP_Rocket\Engine\Abilities\Options\SetOption;

/**
 * The route Fleet calls.
 *
 * Authenticates through `wp-media/fleet-bridge`, then calls the abilities this
 * plugin already ships rather than reimplementing them.
 *
 * Calls `execute()` directly instead of going through MCP. The abilities'
 * `permission_callback` expects a logged-in administrator, which Fleet is not;
 * the verified command and consent pair is the authorisation here. It also
 * keeps the route free of any WordPress version floor, since only `register()`
 * touches the 6.9 Abilities API.
 *
 * @since 3.23.4
 */
class Route {
	/**
	 * REST namespace.
	 */
	const NAMESPACE = 'wp-rocket/v1';

	/**
	 * REST base.
	 */
	const BASE = 'fleet/settings';

	/**
	 * The header carrying wp-rocket.me's consent grant.
	 *
	 * Its own header, not a second value in `Authorization`: the two
	 * credentials come from different parties and prove different things.
	 */
	const CONSENT_HEADER = 'X-WP-Rocket-Fleet-Consent';

	/**
	 * The capability a read needs.
	 */
	const SCOPE_READ = 'settings:read';

	/**
	 * The capability a write needs.
	 *
	 * Separate from the read scope, so a licence can allow Fleet to show
	 * settings without allowing it to change them.
	 */
	const SCOPE_WRITE = 'settings:write';

	/**
	 * Verification, from the shared package.
	 *
	 * @var Verifier
	 */
	private $bridge;

	/**
	 * The read ability.
	 *
	 * @var GetOptions
	 */
	private $get_options;

	/**
	 * The write ability.
	 *
	 * @var SetOption
	 */
	private $set_option;

	/**
	 * The allowlist, for reporting what may be written and for bounding a batch.
	 *
	 * @var AllowedOptions
	 */
	private $allowed_options;

	/**
	 * Instantiate the class.
	 *
	 * @param Verifier       $bridge          Verification.
	 * @param GetOptions     $get_options     The read ability.
	 * @param SetOption      $set_option      The write ability.
	 * @param AllowedOptions $allowed_options The allowlist.
	 */
	public function __construct(
		Verifier $bridge,
		GetOptions $get_options,
		SetOption $set_option,
		AllowedOptions $allowed_options
	) {
		$this->bridge          = $bridge;
		$this->get_options     = $get_options;
		$this->set_option      = $set_option;
		$this->allowed_options = $allowed_options;
	}

	/**
	 * Register the route.
	 *
	 * @since 3.23.4
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::NAMESPACE,
			'/' . self::BASE,
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'read' ],
					'permission_callback' => [ $this, 'is_fleet' ],
				],
				[
					'methods'             => 'PATCH',
					'callback'            => [ $this, 'write' ],
					'permission_callback' => [ $this, 'is_fleet' ],
					// No `args` on purpose: the REST validator would reject an
					// option outside the allowlist as a malformed request.
					// SetOption's own refusal is the one Fleet needs.
				],
			]
		);
	}

	/**
	 * Two proofs, from two parties, before anything happens.
	 *
	 * The scope comes from the method, not the request, so a caller cannot
	 * nominate the permission it is checked against.
	 *
	 * @since 3.23.4
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return true|WP_Error
	 */
	public function is_fleet( WP_REST_Request $request ) {
		$scope = 'GET' === strtoupper( $request->get_method() )
			? self::SCOPE_READ
			: self::SCOPE_WRITE;

		try {
			$this->bridge->verifyCommand(
				(string) $request->get_header( 'authorization' ),
				// The raw body, not the parsed parameters: the digest covers
				// the bytes that travelled.
				(string) $request->get_body()
			);

			$this->bridge->verifyGrant(
				(string) $request->get_header( self::CONSENT_HEADER ),
				$scope
			);
		} catch ( NotAuthorised $exception ) {
			// The reason goes to the log; the caller gets one fixed message.
			self::log( $request->get_method() . ' refused: ' . $exception->getMessage() );

			return new WP_Error( 'rocket_fleet_unauthorised', Bridge::REFUSED, [ 'status' => 401 ] );
		}

		return true;
	}

	/**
	 * Every allowlisted option, with its current value and its type.
	 *
	 * Takes no argument: PHP passes the request harmlessly, and declaring a
	 * parameter we never read would only read as unused.
	 *
	 * @since 3.23.4
	 *
	 * @return WP_REST_Response
	 */
	public function read(): WP_REST_Response {
		$schema  = $this->get_options->schema();
		$allowed = $this->allowed_options->get();

		// The ability answers first, for the options this site has actually
		// written, with the plugin's own read filters applied.
		$stored = $this->get_options->execute();

		$settings = [];
		$unset    = [];

		foreach ( $allowed as $option ) {
			if ( array_key_exists( $option, $stored ) ) {
				$settings[ $option ] = $stored[ $option ];

				continue;
			}

			// An option nobody has saved is absent from the stored array, so
			// report it with the value the plugin itself acts on: an unwritten
			// `minify_css` behaves as off.
			$settings[ $option ] = get_rocket_option( $option, $this->blank_for( $schema[ $option ] ?? [] ) );
			$unset[]             = $option;
		}

		return new WP_REST_Response(
			[
				'site'     => (string) wp_parse_url( home_url(), PHP_URL_HOST ),
				// Cast, so the shape does not change with the contents: an
				// empty PHP array would encode as `[]` and a populated one as
				// an object.
				'settings' => (object) $settings,
				// The type of each one, so a caller can render a checkbox for
				// a toggle rather than a text box for everything.
				'schema'   => (object) $schema,
				// Which of those have never been written on this site: "off"
				// and "never configured" behave alike but are not the same fact.
				'unset'    => $unset,
				// What Fleet is allowed to write, so it can render a form
				// without hardcoding a copy of our allowlist.
				'writable' => $allowed,
			],
			200
		);
	}

	/**
	 * Change one option, or several.
	 *
	 * Batched, so applying a settings set across an agency's sites is one
	 * signed round trip per site rather than one per option per site.
	 *
	 * A batch reports per option and answers 200 when it ran: one refused
	 * option must neither fail the rest nor be dropped silently.
	 *
	 * @since 3.23.4
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function write( WP_REST_Request $request ) {
		$body = json_decode( (string) $request->get_body(), true );

		if ( ! is_array( $body ) ) {
			return new WP_Error(
				'rocket_fleet_bad_request',
				'Send a JSON object.',
				[ 'status' => 400 ]
			);
		}

		$changes = $this->changes_in( $body );

		if ( $changes instanceof WP_Error ) {
			return $changes;
		}

		$results = [];

		foreach ( $changes as $change ) {
			$results[] = $this->apply( $change );
		}

		// A single change keeps its original shape, so a caller asking for one
		// option is not made to unwrap a list of one.
		if ( ! isset( $body['options'] ) && 1 === count( $results ) ) {
			$only = $results[0];

			if ( ! $only['success'] ) {
				return new WP_Error(
					'rocket_fleet_option_refused',
					$only['error'],
					[ 'status' => 422 ]
				);
			}

			return new WP_REST_Response(
				[
					'site'           => (string) wp_parse_url( home_url(), PHP_URL_HOST ),
					'option_name'    => $only['option_name'],
					'previous_value' => $only['previous_value'],
					'new_value'      => $only['new_value'],
				],
				200
			);
		}

		return new WP_REST_Response(
			[
				'site'    => (string) wp_parse_url( home_url(), PHP_URL_HOST ),
				'results' => $results,
			],
			200
		);
	}

	/**
	 * Record something worth reading in a log.
	 *
	 * Static, so the bridge's key set fetcher can be handed it alone.
	 *
	 * @since 3.23.4
	 *
	 * @param string $message What happened.
	 *
	 * @return void
	 */
	public static function log( string $message ): void {
		if ( ! rocket_get_constant( 'WP_DEBUG' ) || ! rocket_get_constant( 'WP_DEBUG_LOG' ) ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[wp-rocket-fleet] ' . $message );
	}

	/**
	 * The changes a request is asking for, validated.
	 *
	 * Accepts one option at the top level, or many under `options`, bounded by
	 * the allowlist's own size rather than an invented limit.
	 *
	 * @since 3.23.4
	 *
	 * @param array $body Decoded request body.
	 *
	 * @return array|WP_Error
	 */
	private function changes_in( array $body ) {
		$changes = isset( $body['options'] ) ? $body['options'] : [ $body ];

		if ( ! is_array( $changes ) || [] === $changes ) {
			return new WP_Error(
				'rocket_fleet_bad_request',
				'Send option_name and option_value, or a non-empty options array.',
				[ 'status' => 400 ]
			);
		}

		$limit = count( $this->allowed_options->get() );

		if ( count( $changes ) > $limit ) {
			return new WP_Error(
				'rocket_fleet_bad_request',
				sprintf( 'At most %d options may be sent in one request.', $limit ),
				[ 'status' => 400 ]
			);
		}

		foreach ( $changes as $change ) {
			if ( ! is_array( $change ) || ! isset( $change['option_name'] ) ) {
				return new WP_Error(
					'rocket_fleet_bad_request',
					'Every change needs an option_name.',
					[ 'status' => 400 ]
				);
			}

			// `array_key_exists` rather than `isset`, so setting an option to
			// null is answered rather than called malformed.
			if ( ! array_key_exists( 'option_value', $change ) ) {
				return new WP_Error(
					'rocket_fleet_bad_request',
					'Every change needs an option_value.',
					[ 'status' => 400 ]
				);
			}
		}

		return array_values( $changes );
	}

	/**
	 * Apply one change through the ability's own execution.
	 *
	 * @since 3.23.4
	 *
	 * @param array $change One `{ option_name, option_value, update_mode }`.
	 *
	 * @return array The outcome, in the shape a batch reports.
	 */
	private function apply( array $change ): array {
		$name   = (string) $change['option_name'];
		$result = $this->set_option->execute(
			[
				'option_name'  => $name,
				'option_value' => $change['option_value'],
				'update_mode'  => isset( $change['update_mode'] ) ? (string) $change['update_mode'] : 'update',
			]
		);

		if ( empty( $result['success'] ) ) {
			// A refusal, not a failure: the request was well formed, this site
			// will not write it.
			return [
				'option_name' => $name,
				'success'     => false,
				'error'       => isset( $result['error'] ) ? (string) $result['error'] : 'This option cannot be set.',
			];
		}

		return [
			'option_name'    => $name,
			'success'        => true,
			'previous_value' => $result['previous_value'] ?? null,
			'new_value'      => $result['new_value'] ?? null,
		];
	}

	/**
	 * The value an option of a given type has when nothing has been saved.
	 *
	 * Typed rather than a bare empty string, so a list option never arrives as
	 * text where every other site sends an array.
	 *
	 * @since 3.23.4
	 *
	 * @param array $definition One entry from the schema. May be empty.
	 *
	 * @return mixed
	 */
	private function blank_for( array $definition ) {
		switch ( $definition['type'] ?? '' ) {
			case 'integer':
				return 0;
			case 'array':
				return [];
			default:
				return '';
		}
	}
}
