<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Abilities\Options;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Abilities\AbilitiesInterface;
use WP_Rocket\Engine\Tracking\TrackingTrait;

class GetOptions implements AbilitiesInterface {
	use TrackingTrait;

	/**
	 * Options data instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Constructor.
	 *
	 * @param Options_Data $options Options data instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Registers the ability to get WP Rocket options.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		wp_register_ability(
			'wp-rocket/get-options',
			[
				'label'               => __( 'Get WP Rocket options', 'rocket' ),
				'description'         => _x(
					'Retrieves current WP Rocket settings as a flat key-value object, including toggles and array-type options.
Use this when the user asks what is enabled, disabled, or excluded. Do not use it to change settings; use set-option instead.
Always call this before set-option when changing an array-type option, to avoid overwriting existing values. Do not show internal, credential, or read-only keys to the user.
Unless the user asks for another format, present settings as a grouped dashboard with cards for Caching, File Optimisation, Media, CDN, Database, and Heartbeat. Each card should show a group-level badge, setting rows, status pills, impact bars, and plain-language descriptions using the approved colors.',
					'Ability description',
					'rocket'
					),
				'category'            => 'wp-rocket-options',
				'output_schema'       => [
					'type'       => 'object',
					'properties' => [
						// Cache settings.
						'cache_mobile'                 => [
							'type'        => 'integer',
							'description' => 'Enable caching for mobile devices.',
							'enum'        => [ 0, 1 ],
						],
						'do_caching_mobile_files'      => [
							'type'        => 'integer',
							'description' => 'Create separate cache files for mobile.',
							'enum'        => [ 0, 1 ],
						],
						'cache_webp'                   => [
							'type'        => 'integer',
							'description' => 'Enable WebP caching.',
							'enum'        => [ 0, 1 ],
						],
						'cache_logged_user'            => [
							'type'        => 'integer',
							'description' => 'Enable caching for logged-in users.',
							'enum'        => [ 0, 1 ],
						],
						'cache_reject_uri'             => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'URLs to exclude from caching.',
						],
						'cache_reject_cookies'         => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Cookies that prevent caching.',
						],
						'cache_reject_ua'              => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'User agents to exclude from caching.',
						],
						'cache_query_strings'          => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Query strings to cache.',
						],
						'cache_purge_pages'            => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'URLs to always purge.',
						],
						'purge_cron_interval'          => [
							'type'        => 'integer',
							'description' => 'Cache lifespan interval value.',
						],
						'purge_cron_unit'              => [
							'type'        => 'string',
							'description' => 'Cache lifespan time unit.',
							'enum'        => [ 'MINUTE_IN_SECONDS', 'HOUR_IN_SECONDS', 'DAY_IN_SECONDS' ],
						],

						// File optimization - CSS.
						'minify_css'                   => [
							'type'        => 'integer',
							'description' => 'Enable CSS minification.',
							'enum'        => [ 0, 1 ],
						],
						'exclude_css'                  => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'CSS files to exclude from minification.',
						],
						'async_css'                    => [
							'type'        => 'integer',
							'description' => 'Enable asynchronous CSS loading.',
							'enum'        => [ 0, 1 ],
						],
						'async_css_mobile'             => [
							'type'        => 'integer',
							'description' => 'Enable asynchronous CSS loading on mobile.',
							'enum'        => [ 0, 1 ],
						],
						'critical_css'                 => [
							'type'        => 'string',
							'description' => 'Fallback critical CSS content.',
						],
						'remove_unused_css'            => [
							'type'        => 'integer',
							'description' => 'Enable Remove Unused CSS (RUCSS).',
							'enum'        => [ 0, 1 ],
						],
						'remove_unused_css_safelist'   => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'CSS selectors to always keep.',
						],
						'optimize_css_delivery'        => [
							'type'        => 'integer',
							'description' => 'Enable CSS delivery optimization.',
							'enum'        => [ 0, 1 ],
						],

						// File optimization - JS.
						'minify_js'                    => [
							'type'        => 'integer',
							'description' => 'Enable JavaScript minification.',
							'enum'        => [ 0, 1 ],
						],
						'minify_concatenate_js'        => [
							'type'        => 'integer',
							'description' => 'Enable JavaScript concatenation.',
							'enum'        => [ 0, 1 ],
						],
						'exclude_js'                   => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'JavaScript files to exclude from minification.',
						],
						'exclude_inline_js'            => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Inline JavaScript patterns to exclude.',
						],
						'defer_all_js'                 => [
							'type'        => 'integer',
							'description' => 'Enable JavaScript defer loading.',
							'enum'        => [ 0, 1 ],
						],
						'exclude_defer_js'             => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'JavaScript files to exclude from defer.',
						],
						'delay_js'                     => [
							'type'        => 'integer',
							'description' => 'Enable Delay JavaScript execution.',
							'enum'        => [ 0, 1 ],
						],
						'delay_js_exclusions'          => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'JavaScript files to exclude from delay.',
						],
						'delay_js_execution_safe_mode' => [
							'type'        => 'integer',
							'description' => 'Enable safe mode for Delay JavaScript.',
							'enum'        => [ 0, 1 ],
						],
						'delay_js_exclusions_selected' => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Selected plugin/theme keys for delay JS exclusions.',
						],
						'delay_js_exclusions_selected_exclusions' => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Resolved exclusion patterns from selected items.',
						],

						// Media.
						'lazyload'                     => [
							'type'        => 'integer',
							'description' => 'Enable image lazy loading.',
							'enum'        => [ 0, 1 ],
						],
						'lazyload_iframes'             => [
							'type'        => 'integer',
							'description' => 'Enable iframe/video lazy loading.',
							'enum'        => [ 0, 1 ],
						],
						'lazyload_youtube'             => [
							'type'        => 'integer',
							'description' => 'Replace YouTube videos with preview image.',
							'enum'        => [ 0, 1 ],
						],
						'lazyload_css_bg_img'          => [
							'type'        => 'integer',
							'description' => 'Enable CSS background image lazy loading.',
							'enum'        => [ 0, 1 ],
						],
						'exclude_lazyload'             => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Patterns to exclude from lazy loading.',
						],
						'image_dimensions'             => [
							'type'        => 'integer',
							'description' => 'Add missing image dimensions.',
							'enum'        => [ 0, 1 ],
						],

						// Fonts.
						'host_fonts_locally'           => [
							'type'        => 'integer',
							'description' => 'Host Google Fonts locally.',
							'enum'        => [ 0, 1 ],
						],
						'auto_preload_fonts'           => [
							'type'        => 'integer',
							'description' => 'Automatically preload fonts.',
							'enum'        => [ 0, 1 ],
						],

						// Preload.
						'manual_preload'               => [
							'type'        => 'integer',
							'description' => 'Enable cache preloading.',
							'enum'        => [ 0, 1 ],
						],
						'preload_fonts'                => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Font URLs to preload.',
						],
						'dns_prefetch'                 => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Domains to DNS prefetch.',
						],
						'preload_links'                => [
							'type'        => 'integer',
							'description' => 'Enable link preloading on hover.',
							'enum'        => [ 0, 1 ],
						],

						// Database.
						'database_revisions'           => [
							'type'        => 'integer',
							'description' => 'Clean post revisions.',
							'enum'        => [ 0, 1 ],
						],
						'database_auto_drafts'         => [
							'type'        => 'integer',
							'description' => 'Clean auto drafts.',
							'enum'        => [ 0, 1 ],
						],
						'database_trashed_posts'       => [
							'type'        => 'integer',
							'description' => 'Clean trashed posts.',
							'enum'        => [ 0, 1 ],
						],
						'database_spam_comments'       => [
							'type'        => 'integer',
							'description' => 'Clean spam comments.',
							'enum'        => [ 0, 1 ],
						],
						'database_trashed_comments'    => [
							'type'        => 'integer',
							'description' => 'Clean trashed comments.',
							'enum'        => [ 0, 1 ],
						],
						'database_all_transients'      => [
							'type'        => 'integer',
							'description' => 'Clean all transients.',
							'enum'        => [ 0, 1 ],
						],
						'database_optimize_tables'     => [
							'type'        => 'integer',
							'description' => 'Optimize database tables.',
							'enum'        => [ 0, 1 ],
						],
						'schedule_automatic_cleanup'   => [
							'type'        => 'integer',
							'description' => 'Enable automatic database cleanup.',
							'enum'        => [ 0, 1 ],
						],
						'automatic_cleanup_frequency'  => [
							'type'        => 'string',
							'description' => 'Frequency of automatic cleanup.',
							'enum'        => [ 'daily', 'weekly', 'monthly' ],
						],

						// CDN.
						'cdn'                          => [
							'type'        => 'integer',
							'description' => 'Enable CDN.',
							'enum'        => [ 0, 1 ],
						],
						'cdn_cnames'                   => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'CDN CNAME URLs.',
						],
						'cdn_zone'                     => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'CDN zones for each CNAME.',
						],
						'cdn_reject_files'             => [
							'type'        => 'array',
							'items'       => [ 'type' => 'string' ],
							'description' => 'Files to exclude from CDN.',
						],

						// Cloudflare.
						'do_cloudflare'                => [
							'type'        => 'integer',
							'description' => 'Enable Cloudflare integration.',
							'enum'        => [ 0, 1 ],
						],
						'cloudflare_devmode'           => [
							'type'        => 'integer',
							'description' => 'Enable Cloudflare development mode.',
							'enum'        => [ 0, 1 ],
						],
						'cloudflare_protocol_rewrite'  => [
							'type'        => 'integer',
							'description' => 'Enable Cloudflare protocol rewrite.',
							'enum'        => [ 0, 1 ],
						],
						'cloudflare_auto_settings'     => [
							'type'        => 'integer',
							'description' => 'Apply optimal Cloudflare settings.',
							'enum'        => [ 0, 1 ],
						],

						// Heartbeat.
						'control_heartbeat'            => [
							'type'        => 'integer',
							'description' => 'Enable Heartbeat control.',
							'enum'        => [ 0, 1 ],
						],
						'heartbeat_site_behavior'      => [
							'type'        => 'string',
							'description' => 'Heartbeat behavior on frontend.',
							'enum'        => [ '', 'reduce_periodicity', 'disable' ],
						],
						'heartbeat_admin_behavior'     => [
							'type'        => 'string',
							'description' => 'Heartbeat behavior in admin.',
							'enum'        => [ '', 'reduce_periodicity', 'disable' ],
						],
						'heartbeat_editor_behavior'    => [
							'type'        => 'string',
							'description' => 'Heartbeat behavior in post editor.',
							'enum'        => [ '', 'reduce_periodicity', 'disable' ],
						],

						// Add-ons.
						'varnish_auto_purge'           => [
							'type'        => 'integer',
							'description' => 'Enable Varnish auto purge.',
							'enum'        => [ 0, 1 ],
						],
						'sucury_waf_cache_sync'        => [
							'type'        => 'integer',
							'description' => 'Enable Sucuri WAF cache sync.',
							'enum'        => [ 0, 1 ],
						],

						// Analytics.
						'analytics_enabled'            => [
							'type'        => 'integer',
							'description' => 'Enable anonymous analytics.',
							'enum'        => [ 0, 1 ],
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
						'readonly'    => true,
						'destructive' => false,
						'idempotent'  => true,
					],
				],
			]
		);
	}

	/**
	 * Checks if the current user has permission to get WP Rocket options.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Executes the ability to get WP Rocket options, returning all options except those on the denylist.
	 *
	 * @return array
	 */
	public function execute(): array {
		$this->track_event( 'MCP Ability Executed', [ 'ability' => 'wp-rocket/get-options' ] );
		$denylist = [
			'secret_cache_key',
			'cache_ssl',
			'minify_css_key',
			'minify_js_key',
			'defer_all_js_safe',
			'cloudflare_email',
			'cloudflare_api_key',
			'cloudflare_zone_id',
			'cloudflare_old_settings',
			'sucury_waf_api_key',
			'consumer_key',
			'consumer_email',
			'secret_key',
			'license',
		];

		$options = $this->options->get_options();

		return array_diff_key( $options, array_flip( $denylist ) );
	}
}
