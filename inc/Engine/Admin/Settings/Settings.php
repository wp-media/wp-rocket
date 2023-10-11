<?php
namespace WP_Rocket\Engine\Admin\Settings;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Addon\Sucuri\Subscriber as SucuriSubscriber;

/**
 * Settings class.
 *
 * @since 3.5.5 Moves into the new architecture.
 */
class Settings {
	/**
	 * Options_Data instance.
	 *
	 * @since 3.0
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Array of settings to build the settings page.
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Hidden settings on the settings page.
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $hidden_settings;

	/**
	 * Font formats allowed to be preloaded.
	 *
	 * @since 3.6
	 * @see   $this->sanitize_font()
	 *
	 * @var array
	 */
	private $font_formats = [
		'otf',
		'ttf',
		'svg',
		'woff',
		'woff2',
	];

	/**
	 * Array of valid hosts.
	 *
	 * @since 3.6
	 * @see   $this->get_hosts()
	 *
	 * @var array
	 */
	private $hosts;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 *
	 * @param Options_Data $options Options_Data instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Adds a page section to the settings.
	 *
	 * A page section is a top-level block containing settings sections.
	 *
	 * @since 3.0
	 *
	 * @param string $id Page section identifier.
	 * @param array  $args {
	 *      Data to build the section.
	 *
	 *      @type string $title            Section title.
	 *      @type string $menu_description Description displayed in the navigation.
	 * }
	 * @return void
	 */
	public function add_page_section( $id, $args ) {
		$args['id'] = $id;

		$this->settings[ $id ] = $args;
	}

	/**
	 * Adds settings sections to the settings.
	 *
	 * A setting section is a block containing settings fields.
	 *
	 * @since 3.0
	 *
	 * @param array $settings_sections {
	 *      Data to build the section.
	 *
	 *      @type string $title       Section title.
	 *      @type string $type        Type of section.
	 *      @type string $page        Page section identifier it belongs to.
	 *      @type string $description Section description.
	 *      @type string $help        Helper IDs for beacon.
	 * }
	 * @return void
	 */
	public function add_settings_sections( $settings_sections ) {
		foreach ( $settings_sections as $id => $args ) {
			$args['id'] = $id;

			$this->settings[ $args['page'] ]['sections'][ $id ] = $args;
		}
	}

	/**
	 * Adds settings fields to the settings.
	 *
	 * @since 3.0
	 *
	 * @param array $settings_fields {
	 *      Data to build the section.
	 *
	 *      @type string $id    Identifier.
	 *      @type string $title Field title.
	 * }
	 * @return void
	 */
	public function add_settings_fields( $settings_fields ) {
		foreach ( $settings_fields as $id => $args ) {
			$args['id']    = $id;
			$args['value'] = $this->options->get( $id, $args['default'] );
			$page          = $args['page'];
			$section       = $args['section'];
			unset( $args['page'], $args['section'] );
			/**
			 * Filters the field  before add to the settings
			 *
			 * @since 3.10
			 *
			 * @param array    $input    Array of sanitized values after being submitted by the form.
			 */
			$args = apply_filters( 'rocket_before_add_field_to_settings', $args );
			$this->settings[ $page ]['sections'][ $section ]['fields'][ $id ] = $args;
		}
	}

	/**
	 * Adds hidden settings fields to the settings.
	 *
	 * @since 3.0
	 *
	 * @param array $hidden_settings_fields {
	 *      Data to build the section.
	 *
	 *      @type string $id    Identifier.
	 * }
	 * @return void
	 */
	public function add_hidden_settings_fields( $hidden_settings_fields ) {
		foreach ( $hidden_settings_fields as $id ) {
			$value = $this->options->get( $id );

			$this->hidden_settings[] = [
				'id'    => $id,
				'value' => $value,
			];
		}
	}

	/**
	 * Returns the plugin settings
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Returns the plugin hidden settings
	 *
	 * @since 3.0
	 *
	 * @return array
	 */
	public function get_hidden_settings() {
		return $this->hidden_settings;
	}

	/**
	 * Sanitizes the submitted values.
	 *
	 * @since 3.0
	 *
	 * @param array $input Array of values submitted by the form.
	 * @return array
	 */
	public function sanitize_callback( $input ) {
		global $wp_settings_errors;

		$input['cache_logged_user'] = ! empty( $input['cache_logged_user'] ) ? 1 : 0;

		$input['cache_ssl'] = ! empty( $input['cache_ssl'] ) ? 1 : 0;

		$input['cache_mobile']            = ! empty( $input['cache_mobile'] ) ? 1 : 0;
		$input['do_caching_mobile_files'] = ! empty( $input['do_caching_mobile_files'] ) ? 1 : 0;

		$input['minify_google_fonts'] = ! empty( $input['minify_google_fonts'] ) ? 1 : 0;

		// Option : Minification CSS & JS.
		$input['minify_css'] = ! empty( $input['minify_css'] ) ? 1 : 0;
		$input['minify_js']  = ! empty( $input['minify_js'] ) ? 1 : 0;

		$input['minify_concatenate_js'] = ! empty( $input['minify_concatenate_js'] ) ? 1 : 0;

		$input['defer_all_js']     = ! empty( $input['defer_all_js'] ) ? 1 : 0;
		$input['exclude_defer_js'] = ! empty( $input['exclude_defer_js'] ) ? rocket_sanitize_textarea_field( 'exclude_defer_js', $input['exclude_defer_js'] ) : [];

		$input['emoji'] = ! empty( $input['emoji'] ) ? 1 : 0;

		$input['lazyload']         = ! empty( $input['lazyload'] ) ? 1 : 0;
		$input['lazyload_iframes'] = ! empty( $input['lazyload_iframes'] ) ? 1 : 0;
		$input['lazyload_youtube'] = ! empty( $input['lazyload_youtube'] ) ? 1 : 0;

		// If iframes lazyload is not checked, uncheck youtube thumbnail option too.
		if ( 0 === $input['lazyload_iframes'] ) {
			$input['lazyload_youtube'] = 0;
		}

		// Option : Purge interval.
		$input['purge_cron_interval'] = isset( $input['purge_cron_interval'] ) ? (int) $input['purge_cron_interval'] : $this->options->get( 'purge_cron_interval' );

		$allowed_cron_units = [
			'HOUR_IN_SECONDS' => 1,
			'DAY_IN_SECONDS'  => 1,
		];

		$input['purge_cron_unit'] = isset( $input['purge_cron_unit'], $allowed_cron_units[ $input['purge_cron_unit'] ] ) ? $input['purge_cron_unit'] : $this->options->get( 'purge_cron_unit' );

		// Option : Prefetch DNS requests.
		$input['dns_prefetch'] = $this->sanitize_dns_prefetch( $input );

		// Option : Empty the cache of the following pages when updating a post.
		if ( ! empty( $input['cache_purge_pages'] ) ) {
			$input['cache_purge_pages'] = rocket_sanitize_textarea_field( 'cache_purge_pages', $input['cache_purge_pages'] );
		} else {
			$input['cache_purge_pages'] = [];
		}

		// Option : Never cache the following pages.
		if ( ! empty( $input['cache_reject_uri'] ) ) {
			$input['cache_reject_uri'] = rocket_sanitize_textarea_field( 'cache_reject_uri', $input['cache_reject_uri'] );
			$input['cache_reject_uri'] = $this->check_global_exclusion( $input['cache_reject_uri'] );
		} else {
			$input['cache_reject_uri'] = [];
		}

		// Option : Don't cache pages that use the following cookies.
		if ( ! empty( $input['cache_reject_cookies'] ) ) {
			$input['cache_reject_cookies'] = rocket_sanitize_textarea_field( 'cache_reject_cookies', $input['cache_reject_cookies'] );
		} else {
			$input['cache_reject_cookies'] = [];
		}

		// Option : Cache pages that use the following query strings (GET parameters).
		if ( ! empty( $input['cache_query_strings'] ) ) {
			$input['cache_query_strings'] = rocket_sanitize_textarea_field( 'cache_query_strings', $input['cache_query_strings'] );
		} else {
			$input['cache_query_strings'] = [];
		}

		// Option : Never send cache pages for these user agents.
		if ( ! empty( $input['cache_reject_ua'] ) ) {
			$input['cache_reject_ua'] = rocket_sanitize_textarea_field( 'cache_reject_ua', $input['cache_reject_ua'] );
		} else {
			$input['cache_reject_ua'] = [];
		}

		// Option : CSS files to exclude from the minification.
		if ( ! empty( $input['exclude_css'] ) ) {
			$input['exclude_css'] = rocket_sanitize_textarea_field( 'exclude_css', $input['exclude_css'] );
		} else {
			$input['exclude_css'] = [];
		}

		// Option : JS files to exclude from the minification.
		if ( ! empty( $input['exclude_js'] ) ) {
			$input['exclude_js'] = rocket_sanitize_textarea_field( 'exclude_js', $input['exclude_js'] );
		} else {
			$input['exclude_js'] = [];
		}

		// Option: inline JS patterns to exclude from combine JS.
		if ( ! empty( $input['exclude_inline_js'] ) ) {
			$input['exclude_inline_js'] = rocket_sanitize_textarea_field( 'exclude_inline_js', $input['exclude_inline_js'] );
		} else {
			$input['exclude_inline_js'] = [];
		}

		// Option: Async CSS.
		$input['async_css'] = ! empty( $input['async_css'] ) ? 1 : 0;

		// Option: Critical CSS.
		$input['critical_css'] = ! empty( $input['critical_css'] ) ? wp_strip_all_tags( str_replace( [ '<style>', '</style>' ], '', $input['critical_css'] ), [ "\'", '\"' ] ) : '';

		// Database options.
		$input['database_revisions']         = ! empty( $input['database_revisions'] ) ? 1 : 0;
		$input['database_auto_drafts']       = ! empty( $input['database_auto_drafts'] ) ? 1 : 0;
		$input['database_trashed_posts']     = ! empty( $input['database_trashed_posts'] ) ? 1 : 0;
		$input['database_spam_comments']     = ! empty( $input['database_spam_comments'] ) ? 1 : 0;
		$input['database_trashed_comments']  = ! empty( $input['database_trashed_comments'] ) ? 1 : 0;
		$input['database_all_transients']    = ! empty( $input['database_all_transients'] ) ? 1 : 0;
		$input['database_optimize_tables']   = ! empty( $input['database_optimize_tables'] ) ? 1 : 0;
		$input['schedule_automatic_cleanup'] = ! empty( $input['schedule_automatic_cleanup'] ) ? 1 : 0;

		$cleanup_frequencies = [
			'daily'   => 1,
			'weekly'  => 1,
			'monthly' => 1,
		];

		$input['automatic_cleanup_frequency'] = isset( $input['automatic_cleanup_frequency'], $cleanup_frequencies[ $input['automatic_cleanup_frequency'] ] ) ? $input['automatic_cleanup_frequency'] : $this->options->get( 'automatic_cleanup_frequency' );

		if ( 1 !== $input['schedule_automatic_cleanup'] && ( 'daily' !== $input['automatic_cleanup_frequency'] || 'weekly' !== $input['automatic_cleanup_frequency'] || 'monthly' !== $input['automatic_cleanup_frequency'] ) ) {
			$input['automatic_cleanup_frequency'] = $this->options->get( 'automatic_cleanup_frequency' );
		}

		// Options: Activate bot preload.
		$input['manual_preload'] = ! empty( $input['manual_preload'] ) ? 1 : 0;

		// Option : fonts to preload.
		$input['preload_fonts'] = ! empty( $input['preload_fonts'] ) ? $this->sanitize_fonts( $input['preload_fonts'] ) : [];

		// Options: Sucuri cache. And yeah, there's a typo, but now it's too late to fix ^^'.
		$input['sucury_waf_cache_sync'] = ! empty( $input['sucury_waf_cache_sync'] ) ? 1 : 0;

		if ( defined( 'WP_ROCKET_SUCURI_API_KEY' ) ) {
			$input['sucury_waf_api_key'] = WP_ROCKET_SUCURI_API_KEY;
		} else {
			$input['sucury_waf_api_key'] = isset( $input['sucury_waf_api_key'] ) ? sanitize_text_field( $input['sucury_waf_api_key'] ) : '';
		}

		$input['sucury_waf_api_key'] = trim( $input['sucury_waf_api_key'] );

		if ( ! SucuriSubscriber::is_api_key_valid( $input['sucury_waf_api_key'] ) ) {
			$input['sucury_waf_api_key'] = '';

			if ( $input['sucury_waf_cache_sync'] && empty( $input['ignore'] ) ) {
				add_settings_error( 'general', 'sucuri_waf_api_key_invalid', __( 'Sucuri Add-on: The API key for the Sucuri firewall must be in format <code>{32 characters}/{32 characters}</code>.', 'rocket' ), 'error' );
			}
		}

		// Options : Heartbeat.
		$choices = [
			''                   => 1,
			'reduce_periodicity' => 1,
			'disable'            => 1,
		];

		$input['control_heartbeat']         = ! empty( $input['control_heartbeat'] ) ? 1 : 0;
		$input['heartbeat_site_behavior']   = isset( $input['heartbeat_site_behavior'], $choices[ $input['heartbeat_site_behavior'] ] ) ? $input['heartbeat_site_behavior'] : '';
		$input['heartbeat_admin_behavior']  = isset( $input['heartbeat_admin_behavior'], $choices[ $input['heartbeat_admin_behavior'] ] ) ? $input['heartbeat_admin_behavior'] : '';
		$input['heartbeat_editor_behavior'] = isset( $input['heartbeat_editor_behavior'], $choices[ $input['heartbeat_editor_behavior'] ] ) ? $input['heartbeat_editor_behavior'] : '';

		// Option : CDN.
		$input['cdn'] = ! empty( $input['cdn'] ) ? 1 : 0;

		// Option : CDN Cnames.
		if ( isset( $input['cdn_cnames'] ) ) {
			$input['cdn_cnames'] = $this->sanitize_cdn_cnames( $input['cdn_cnames'] );
		} else {
			$input['cdn_cnames'] = [];
		}

		if ( ! $input['cdn_cnames'] ) {
			$input['cdn_zone'] = [];
		} else {
			$total_cdn_cnames = max( array_keys( $input['cdn_cnames'] ) );
			for ( $i = 0; $i <= $total_cdn_cnames; $i++ ) {
				if ( ! isset( $input['cdn_cnames'][ $i ] ) ) {
					unset( $input['cdn_zone'][ $i ] );
				} else {
					$allowed_cdn_zones = [
						'all'        => 1,
						'images'     => 1,
						'css_and_js' => 1,
						'css'        => 1,
						'js'         => 1,
					];

					$input['cdn_zone'][ $i ] = isset( $allowed_cdn_zones[ $input['cdn_zone'][ $i ] ] ) ? $input['cdn_zone'][ $i ] : 'all';
				}
			}

			$input['cdn_cnames'] = array_values( $input['cdn_cnames'] );
			$input['cdn_cnames'] = array_map( 'untrailingslashit', $input['cdn_cnames'] );

			ksort( $input['cdn_zone'] );

			$input['cdn_zone'] = array_values( $input['cdn_zone'] );
		}

		// Option : Files to exclude from the CDN process.
		if ( ! empty( $input['cdn_reject_files'] ) ) {
			$input['cdn_reject_files'] = rocket_sanitize_textarea_field( 'cdn_reject_files', $input['cdn_reject_files'] );
		} else {
			$input['cdn_reject_files'] = [];
		}

		$input['varnish_auto_purge'] = ! empty( $input['varnish_auto_purge'] ) ? 1 : 0;

		if ( ! rocket_valid_key() ) {
			$checked = rocket_check_key();
		}

		if ( isset( $checked ) && is_array( $checked ) ) {
			$input['consumer_key']   = $checked['consumer_key'];
			$input['consumer_email'] = $checked['consumer_email'];
			$input['secret_key']     = $checked['secret_key'];
		}

		if ( ! empty( $input['secret_key'] ) && empty( $input['ignore'] ) && rocket_valid_key() ) {
			// Add a "Settings saved." admin notice only if not already added.
			$notices = array_merge( (array) $wp_settings_errors, (array) get_transient( 'settings_errors' ) );
			$notices = array_filter(
				$notices,
				function ( $error ) {
					if ( ! $error || ! is_array( $error ) ) {
						return false;
					}
					if ( ! isset( $error['setting'], $error['code'], $error['type'] ) ) {
						return false;
					}
					return 'general' === $error['setting'] && 'settings_updated' === $error['code'] && 'updated' === $error['type'];
				}
			);

			if ( ! $notices ) {
				add_settings_error( 'general', 'settings_updated', __( 'Settings saved.', 'rocket' ), 'updated' );
			}
		}

		unset( $input['ignore'] );

		/**
		 * Filters the sanitized input before returning the array
		 *
		 * @param array    $input    Array of sanitized values after being submitted by the form.
		 * @param Settings $settings Current class instance.
		 */
		return apply_filters( 'rocket_input_sanitize', $input, $this );
	}

	/**
	 * Sanitizes the returned value of a checkbox
	 *
	 * @since 3.0
	 *
	 * @param array  $array Options array.
	 * @param string $key   Array key to check.
	 * @return int
	 */
	public function sanitize_checkbox( $array, $key ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.arrayFound
		return isset( $array[ $key ] ) && ! empty( $array[ $key ] ) ? 1 : 0;
	}

	/**
	 * Sanitizes the DNS Prefetch sub-option value
	 *
	 * @since 3.5.1
	 *
	 * @param array $input Array of values for the WP Rocket settings option.
	 * @return array Sanitized array for the DNS Prefetch sub-option
	 */
	private function sanitize_dns_prefetch( array $input ) {
		if ( empty( $input['dns_prefetch'] ) ) {
			return [];
		}

		$value = $input['dns_prefetch'];

		if ( ! is_array( $value ) ) {
			$value = explode( "\n", $value );
		}

		$urls = [];

		foreach ( $value as $url ) {
			$url = trim( $url );

			if ( empty( $url ) ) {
				continue;
			}

			$url = preg_replace( '/^(?:https?)?:?\/{3,}/i', 'http://', $url );
			$url = esc_url_raw( $url );

			if ( empty( $url ) ) {
				continue;
			}

			$urls[] = $url;
		}

		if ( empty( $urls ) ) {
			return [];
		}

		return array_unique(
			array_map(
				function ( $url ) {
					return '//' . wp_parse_url( $url, PHP_URL_HOST );
				},
				$urls
			)
		);
	}

	/**
	 * Sanitize a list of font file paths.
	 *
	 * @since 3.6
	 *
	 * @param  array|string $files List of filepaths to sanitize. Can be an array of strings or a string listing paths separated by "\n".
	 * @return array               Sanitized filepaths.
	 */
	private function sanitize_fonts( $files ) {
		if ( ! is_array( $files ) ) {
			$files = explode( "\n", trim( $files ) );
		}

		$files = array_map( [ $this, 'sanitize_font' ], $files );

		return array_unique( array_filter( $files ) );
	}

	/**
	 * Sanitize an entry for the preload fonts option.
	 *
	 * @since 3.6
	 *
	 * @param string $file URL or path to a font file.
	 * @return string|bool
	 */
	private function sanitize_font( $file ) {
		if ( ! is_string( $file ) ) {
			return false;
		}

		$file = trim( $file );

		if ( empty( $file ) ) {
			return false;
		}

		$parsed_url = wp_parse_url( $file );
		$hosts      = $this->get_hosts();

		if ( ! empty( $parsed_url['host'] ) ) {
			$match = false;

			foreach ( $hosts as $host ) {
				if ( false !== strpos( $file, $host ) ) {
					$match = true;
					break;
				}
			}

			if ( ! $match ) {
				return false;
			}
		}

		$file = str_replace( [ 'http:', 'https:' ], '', $file );
		$file = str_replace( $hosts, '', $file );
		$file = '/' . ltrim( $file, '/' );

		$ext = strtolower( pathinfo( $parsed_url['path'], PATHINFO_EXTENSION ) );

		if ( ! in_array( $ext, $this->font_formats, true ) ) {
			return false;
		}

		return $file;
	}

	/**
	 * Gets an array of valid hosts.
	 *
	 * @since 3.6
	 *
	 * @return array
	 */
	private function get_hosts() {
		if ( isset( $this->hosts ) ) {
			return $this->hosts;
		}

		$urls   = (array) $this->options->get( 'cdn_cnames', [] );
		$urls[] = home_url();
		$urls   = array_map( 'rocket_add_url_protocol', $urls );

		foreach ( $urls as $url ) {
			$parsed_url = get_rocket_parse_url( $url );

			if ( empty( $parsed_url['host'] ) ) {
				continue;
			}

			$parsed_url['path'] = ( '/' === $parsed_url['path'] ) ? '' : $parsed_url['path'];

			$this->hosts[] = "//{$parsed_url['host']}{$parsed_url['path']}";
		}

		if ( empty( $this->hosts ) ) {
			$this->hosts = [];
		}

		return $this->hosts;
	}

	/**
	 * Sets radio buttons sub fields value from wp options.
	 *
	 * @since 3.10
	 *
	 * @param array $sub_fields Array of fields to display..
	 * @return array
	 */
	public function set_radio_buttons_sub_fields_value( $sub_fields ) {

		foreach ( $sub_fields as $id => &$args ) {
			$args['id']    = $id;
			$args['value'] = $this->options->get( $id, $args['default'] );
			$args          = apply_filters( 'rocket_before_render_option_extra_field', $args );
		}

		return $sub_fields;
	}

	/**
	 * Checks if the global exclusion pattern is used in the given field
	 *
	 * @since 3.10.3
	 *
	 * @param array $field A field array value.
	 *
	 * @return array
	 */
	private function check_global_exclusion( $field ) {
		if ( ! in_array( '/(.*)', $field, true ) ) {
			return $field;
		}

		add_settings_error( 'general', 'reject_uri_global_exclusion', __( 'Sorry! Adding /(.*) in Advanced Rules > Never Cache URL(s) was not saved because it disables caching and optimizations for every page on your site.', 'rocket' ) );

		return array_diff_key( $field, array_flip( array_keys( $field, '/(.*)', true ) ) );
	}

	/**
	 * Sanitizes the CDN cnames values
	 *
	 * @param array $cnames Array of user submitted values for the cnames.
	 *
	 * @return array
	 */
	private function sanitize_cdn_cnames( array $cnames ) {
		$cnames = array_map(
			function ( $cname ) {
				$cname = trim( $cname );

				if ( empty( $cname ) ) {
					return false;
				}

				$cname_parts = get_rocket_parse_url( rocket_add_url_protocol( $cname ) );

				if ( false === filter_var( $cname_parts['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME ) ) {
					return false;
				}

				return $cname_parts['scheme'] . '://' . $cname_parts['host'] . $cname_parts['path'];
			},
			$cnames
		);

		return array_unique( array_filter( $cnames ) );
	}
}
