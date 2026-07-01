<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities\Options;

use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Tracking\TrackingTrait;

class SetOption implements AbilitiesInterface {
	use TrackingTrait;

	/**
	 * Options that accept boolean values (0 or 1).
	 */
	private const BOOLEAN_OPTIONS = [
		// Cache settings.
		'cache_webp',
		'cache_logged_user',
		// File optimization - CSS.
		'minify_css',
		'async_css',
		'async_css_mobile',
		'remove_unused_css',
		'optimize_css_delivery',
		// File optimization - JS.
		'minify_js',
		'minify_concatenate_js',
		'defer_all_js',
		'delay_js',
		'delay_js_execution_safe_mode',
		// Media.
		'lazyload',
		'lazyload_iframes',
		'lazyload_youtube',
		'lazyload_css_bg_img',
		'image_dimensions',
		// Fonts.
		'host_fonts_locally',
		'auto_preload_fonts',
		// Preload.
		'manual_preload',
		'preload_links',
		// Database.
		'database_revisions',
		'database_auto_drafts',
		'database_trashed_posts',
		'database_spam_comments',
		'database_trashed_comments',
		'database_all_transients',
		'database_optimize_tables',
		'schedule_automatic_cleanup',
		// CDN.
		'cdn',
		// Cloudflare.
		'do_cloudflare',
		'cloudflare_devmode',
		'cloudflare_protocol_rewrite',
		'cloudflare_auto_settings',
		// Heartbeat.
		'control_heartbeat',
		// Add-ons.
		'varnish_auto_purge',
		'sucury_waf_cache_sync',
		// Analytics.
		'analytics_enabled',
	];

	/**
	 * Options that accept integer values.
	 */
	private const INTEGER_OPTIONS = [
		'purge_cron_interval',
	];

	/**
	 * Options with predefined allowed values (enums).
	 */
	private const ENUM_OPTIONS = [
		'purge_cron_unit'             => [ 'MINUTE_IN_SECONDS', 'HOUR_IN_SECONDS', 'DAY_IN_SECONDS' ],
		'automatic_cleanup_frequency' => [ 'daily', 'weekly', 'monthly' ],
		'heartbeat_site_behavior'     => [ '', 'reduce_periodicity', 'disable' ],
		'heartbeat_admin_behavior'    => [ '', 'reduce_periodicity', 'disable' ],
		'heartbeat_editor_behavior'   => [ '', 'reduce_periodicity', 'disable' ],
	];

	/**
	 * Options that require special string sanitization (CSS content).
	 */
	private const STRING_OPTIONS = [
		'critical_css',
	];

	/**
	 * Options that use rocket_sanitize_textarea_field() for sanitization.
	 */
	private const TEXTAREA_FIELD_OPTIONS = [
		'cache_reject_uri',
		'cache_reject_cookies',
		'cache_reject_ua',
		'cache_query_strings',
		'cache_purge_pages',
		'exclude_css',
		'exclude_js',
		'exclude_inline_js',
		'exclude_defer_js',
		'exclude_lazyload',
		'delay_js_exclusions',
		'remove_unused_css_safelist',
		'cdn_reject_files',
	];

	/**
	 * Options that accept arrays with sanitize_text_field per item.
	 */
	private const ARRAY_OPTIONS = [
		'cdn_cnames',
		'cdn_zone',
		'delay_js_exclusions_selected',
		'delay_js_exclusions_selected_exclusions',
	];

	/**
	 * Registers the set option ability.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/set-option',
			[
				'label'               => __( 'Set a WP Rocket option', 'rocket' ),
				'description'         => _x(
					'Writes one WP Rocket option using option_name and option_value. update_mode defaults to update, which appends to arrays; use replace to overwrite a full array.
Use this when the user wants to enable, disable, or update a specific setting. For array-type options, always call get-options first.
Confirmation is required before calling. First show the setting name and the current value to new value. Then ask: `Confirm this change?` and wait for a clear yes or no.
A user request such as `enable it` or `disable it` is not enough confirmation. Only call this ability after the user gives an affirmative answer in the same turn.',
					'Ability description',
					'rocket'
					),
				'category'            => 'wp-rocket-options',
				'input_schema'        => [
					'type'       => 'object',
					'properties' => [
						'option_name'  => [
							'type'        => 'string',
							'description' => __( 'The name of the WP Rocket option to set', 'rocket' ),
						],
						'option_value' => [
							'anyOf'       => [
								[ 'type' => 'string' ],
								[ 'type' => 'boolean' ],
								[ 'type' => 'integer' ],
								[
									'type'  => 'array',
									'items' => [ 'type' => 'string' ],
								],
							],
							'description' => __( 'The value to set for the specified WP Rocket option', 'rocket' ),
						],
						'update_mode'  => [
							'type'        => 'string',
							'enum'        => [ 'update', 'replace' ],
							'description' => __( 'For array and textarea options: "update" (default) adds new entries to the existing list; "replace" overwrites the entire list.', 'rocket' ),
						],
					],
					'required'   => [ 'option_name', 'option_value' ],
				],
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						'success'        => [
							'type'        => 'boolean',
							'description' => __( 'Indicates whether the option was successfully set', 'rocket' ),
						],
						'error'          => [
							'type'        => 'string',
							'description' => __( 'Error message if the option could not be set', 'rocket' ),
						],
						'previous_value' => [
							'anyOf'       => [
								[ 'type' => 'string' ],
								[ 'type' => 'boolean' ],
								[ 'type' => 'integer' ],
								[
									'type'  => 'array',
									'items' => [ 'type' => 'string' ],
								],
							],
							'description' => __( 'The previous value of the option before it was updated', 'rocket' ),
						],
						'new_value'      => [
							'anyOf'       => [
								[ 'type' => 'string' ],
								[ 'type' => 'boolean' ],
								[ 'type' => 'integer' ],
								[
									'type'  => 'array',
									'items' => [ 'type' => 'string' ],
								],
							],
							'description' => __( 'The new value of the option after it was updated', 'rocket' ),
						],
					],
				],
				'execute_callback'    => [ $this, 'execute' ],
				'permission_callback' => [ $this, 'check_permissions' ],
				'meta'                => [
					'show_in_rest' => true,
					'mcp'          => [
						'public' => true,
					],
					'annotations'  => [
						'readonly'    => false,
						'destructive' => true,
						'idempotent'  => true,
					],
				],
			]
		);
	}

	/**
	 * Checks if the current user has permission to set WP Rocket options.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Executes the ability to set a WP Rocket option.
	 *
	 * @param array|null $input Input containing option_name, option_value, and optionally update_mode.
	 * @return array Response with success status and option values.
	 */
	public function execute( $input = null ): array {
		$this->track_event( 'MCP Ability Executed', [ 'ability' => 'wp-rocket/set-option' ] );
		$option_name  = $input['option_name'];
		$option_value = $input['option_value'];
		$update_mode  = $input['update_mode'] ?? 'update';

		if ( ! $this->validate_option_name( $option_name ) ) {
			return [
				'success' => false,
				'error'   => sprintf(
					/* translators: %s: option name */
					__( 'Invalid option name: %s. This option cannot be set via the ability.', 'rocket' ),
					$option_name
				),
			];
		}

		$previous_value  = get_rocket_option( $option_name );
		$sanitized_value = $this->sanitize_value( $option_name, $option_value, $update_mode, $previous_value );

		update_rocket_option( $option_name, $sanitized_value );

		return [
			'success'        => true,
			'previous_value' => $previous_value,
			'new_value'      => $sanitized_value,
		];
	}

	/**
	 * Returns an array of all allowed option names.
	 *
	 * @return array List of allowed option names.
	 */
	private function get_allowed_options(): array {
		return array_merge(
			self::BOOLEAN_OPTIONS,
			self::INTEGER_OPTIONS,
			array_keys( self::ENUM_OPTIONS ),
			self::STRING_OPTIONS,
			self::TEXTAREA_FIELD_OPTIONS,
			self::ARRAY_OPTIONS
		);
	}

	/**
	 * Validates that the option name is in the allowed list.
	 *
	 * @param string $option_name The option name to validate.
	 * @return bool True if valid, false otherwise.
	 */
	private function validate_option_name( string $option_name ): bool {
		return in_array( $option_name, $this->get_allowed_options(), true );
	}

	/**
	 * Sanitizes the option value based on the option type.
	 *
	 * @param string $option_name     The option name.
	 * @param mixed  $option_value    The value to sanitize.
	 * @param string $update_mode     Either 'update' (merge into existing) or 'replace' (overwrite).
	 * @param mixed  $previous_value  The current stored value, used for merging in update mode.
	 * @return mixed Sanitized value.
	 */
	private function sanitize_value( string $option_name, $option_value, string $update_mode = 'update', $previous_value = null ) {
		// Boolean options: convert to 0 or 1.
		if ( in_array( $option_name, self::BOOLEAN_OPTIONS, true ) ) {
			return ! empty( $option_value ) ? 1 : 0;
		}

		// Integer options: cast to integer.
		if ( in_array( $option_name, self::INTEGER_OPTIONS, true ) ) {
			return (int) $option_value;
		}

		// Enum options: validate against allowed values.
		if ( isset( self::ENUM_OPTIONS[ $option_name ] ) ) {
			$allowed_values = self::ENUM_OPTIONS[ $option_name ];
			if ( in_array( $option_value, $allowed_values, true ) ) {
				return $option_value;
			}
			// Return current value if invalid.
			return $previous_value;
		}

		// String options (critical_css): strip tags and style elements.
		if ( in_array( $option_name, self::STRING_OPTIONS, true ) ) {
			if ( empty( $option_value ) ) {
				return '';
			}
			return wp_strip_all_tags(
				str_replace( [ '<style>', '</style>' ], '', $option_value ),
				true
			);
		}

		// Textarea field options: use rocket_sanitize_textarea_field.
		if ( in_array( $option_name, self::TEXTAREA_FIELD_OPTIONS, true ) ) {
			if ( ! function_exists( 'rocket_sanitize_textarea_field' ) ) {
				$sanitized = is_array( $option_value ) ? $option_value : [];
			} else {
				$sanitized = rocket_sanitize_textarea_field( $option_name, $option_value ) ?? [];
			}

			if ( 'replace' === $update_mode ) {
				return $sanitized;
			}

			// update mode: merge new entries into the existing list.
			$existing = is_array( $previous_value ) ? $previous_value : [];
			return array_values( array_unique( array_merge( $existing, $sanitized ) ) );
		}

		// Array options: sanitize each item with sanitize_text_field.
		if ( in_array( $option_name, self::ARRAY_OPTIONS, true ) ) {
			if ( ! is_array( $option_value ) ) {
				return [];
			}

			$sanitized = array_map( 'sanitize_text_field', $option_value );

			if ( 'replace' === $update_mode ) {
				return $sanitized;
			}

			// update mode: merge new entries into the existing list.
			$existing = is_array( $previous_value ) ? $previous_value : [];
			return array_values( array_unique( array_merge( $existing, $sanitized ) ) );
		}

		// Fallback: return value as-is (should not reach here for valid options).
		return $option_value;
	}
}
