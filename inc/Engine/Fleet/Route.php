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
 * Authenticates through `wp-media/fleet-bridge` — see that package for why a
 * command and a consent grant are both required and why they must be signed by
 * different parties — and then calls the abilities this plugin already ships.
 * It does **not** reimplement them, so there is one definition of what may be
 * written and what a value means.
 *
 * ## Why this calls `execute()` directly, and does not go through MCP
 *
 * `permission_callback` on both abilities is
 * `current_user_can( 'rocket_manage_options' )`. That gate is for a human
 * caller holding a WordPress session. Fleet holds no WordPress credential and
 * never will — a design decision, not an omission — so it could never satisfy
 * it.
 *
 * This route's authorisation is the verified pair instead: licence scoped,
 * because the owner opted in; site scoped, because the tokens name this host;
 * single use, because both identifiers are burned; and body bound, because the
 * command's digest covers the exact bytes. That is a stronger claim than a
 * logged-in administrator makes, not a weaker one.
 *
 * Calling `execute()` also means this route has **no WordPress version floor**.
 * `wp_register_ability()` is 6.9 core and only `register()` uses it; the
 * `execute()` methods touch nothing from the Abilities API, and the service
 * provider that builds them is registered unconditionally. An agency fleet full
 * of older sites therefore works, which the MCP surface could not do.
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
	 * A header of its own rather than a second value in `Authorization`,
	 * because the two credentials come from different parties and prove
	 * different things. Sharing one header would invite code that checks
	 * whichever it happens to find first.
	 */
	const CONSENT_HEADER = 'X-WP-Rocket-Fleet-Consent';

	/**
	 * The capability a read needs.
	 */
	const SCOPE_READ = 'settings:read';

	/**
	 * The capability a write needs.
	 *
	 * Separate from the read scope so a licence can allow Fleet to show a
	 * customer their settings without allowing it to change them — a position
	 * plenty of agencies will want to start from.
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
					// Deliberately not declaring `args`. The REST validator
					// would reject a bad option name with its own message
					// before SetOption ever saw it, which would make an option
					// outside the allowlist indistinguishable from a malformed
					// request. SetOption's own refusal is the one Fleet needs.
				],
			]
		);
	}

	/**
	 * Two proofs, from two parties, before anything happens.
	 *
	 * The scope is derived from the method rather than read from the request,
	 * so a caller cannot nominate the permission it is checked against.
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
				// The raw body, not the parsed parameters. The digest covers
				// the bytes that travelled, and re-encoding a parsed array
				// would produce different ones.
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
	 * @since 3.23.4
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return WP_REST_Response
	 */
	public function read( WP_REST_Request $request ): WP_REST_Response { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found -- Required by the REST callback signature; a read takes nothing from the request.
		$schema  = $this->get_options->schema();
		$allowed = $this->allowed_options->get();

		// The ability answers first, and answers for the options this site has
		// actually written. It applies the plugin's own read filters, so its
		// value for a stored option is the one to report.
		$stored = $this->get_options->execute();

		$settings = [];
		$unset    = [];

		foreach ( $allowed as $option ) {
			if ( array_key_exists( $option, $stored ) ) {
				$settings[ $option ] = $stored[ $option ];

				continue;
			}

			// An option nobody has ever saved is missing from the stored array
			// entirely, so a caller shown only that array sees a fraction of
			// the settings and cannot tell the rest exist. It is reported with
			// the value the plugin itself acts on for it — an unwritten
			// `minify_css` behaves as off, so saying "off" is accurate rather
			// than invented.
			$settings[ $option ] = get_rocket_option( $option, $this->blank_for( $schema[ $option ] ?? [] ) );
			$unset[]             = $option;
		}

		return new WP_REST_Response(
			[
				'site'     => (string) wp_parse_url( home_url(), PHP_URL_HOST ),
				// Cast, so the shape does not change with the contents. An
				// empty PHP array encodes as `[]` and a populated one as an
				// object, which means a site with nothing saved yet answers
				// with a different type from every other site — and the reader
				// at the other end is not PHP and has no reason to forgive it.
				'settings' => (object) $settings,
				// The type of each one, so a caller can render a checkbox for a
				// toggle and a list for a list rather than a text box for
				// everything. Without this a caller has only the value to go
				// on, and an unset toggle is indistinguishable from empty text.
				'schema'   => (object) $schema,
				// Which of those have never been written on this site.
				// Reported rather than hidden: "off" and "never configured" are
				// the same behaviour but not the same fact, and only one of
				// them is worth a customer's attention.
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
	 * One request carrying many options rather than many requests carrying one:
	 * applying a saved set of settings across an agency's sites would otherwise
	 * be one signed round trip **per option per site**, and a forty-option set
	 * across fifty sites is two thousand of them. Batched it is fifty.
	 *
	 * A batch reports **per option** and always answers 200 when it ran. One
	 * option outside the allowlist must not fail the other thirty-nine, and
	 * must not be silently dropped either — so the caller gets an outcome for
	 * each and decides what to do about it.
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

		// A single change keeps the shape it has always had, so a caller that
		// asks for one option is not made to unwrap a list of one.
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
	 * Static so the key set fetcher in the bridge can be handed it without
	 * being handed this whole object.
	 *
	 * @since 3.23.4
	 *
	 * @param string $message What happened.
	 *
	 * @return void
	 */
	public static function log( string $message ): void {
		if ( ! rocket_get_constant( 'WP_DEBUG' ) ) {
			return;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[wp-rocket-fleet] ' . $message );
	}

	/**
	 * The changes a request is asking for, validated.
	 *
	 * Accepts one option at the top level, or many under `options`. Bounded by
	 * the allowlist's own size: a caller cannot ask to write more distinct
	 * options than exist, so the limit is derived rather than invented.
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
			// null is a request we answer rather than one we call malformed.
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
			// A refusal, not a failure. The option exists as a concept and the
			// request was well formed; this site will not write it.
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
	 * Typed rather than a bare empty string, because the reader at the other
	 * end is not PHP: `""` for a list option would arrive as text where every
	 * other site sends an array, and a caller would have to guess which it
	 * meant.
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
