<?php
namespace WP_Rocket\Admin\Settings;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Settings class
 */
class Settings {
	/**
	 * Settings data
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var Array
	 */
	private $options;

	/**
	 * Array of settings to build the settings page.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Hidden settings on the settings page.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var array
	 */
	private $hidden_settings;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param Array $options Array containg the option data.
	 */
	public function __construct( $options ) {
		$this->options = $options;
	}

	/**
	 * Adds a page section to the settings.
	 *
	 * A page section is a top-level block containing settings sections.
	 *
	 * @since 3.0
	 * @author Remy Perona
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
	 * @author Remy Perona
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
	 * @author Remy Perona
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

			$this->settings[ $page ]['sections'][ $section ]['fields'][ $id ] = $args;
		}
	}

	/**
	 * Adds hidden settings fields to the settings.
	 *
	 * @since 3.0
	 * @author Remy Perona
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
	 * @author Remy Perona
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
	 * @author Remy Perona
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
	 * @author Remy Perona
	 *
	 * @param array $input Array of values submitted by the form.
	 * @return array
	 */
	public function sanitize_callback( $input ) {
		$input['do_beta'] = ! empty( $input['do_beta'] ) ? 1 : 0;

		$input['cache_logged_user'] = ! empty( $input['cache_logged_user'] ) ? 1 : 0;

		$input['cache_ssl'] = ! empty( $input['cache_ssl'] ) ? 1 : 0;

		$input['cache_mobile']            = ! empty( $input['cache_mobile'] ) ? 1 : 0;
		$input['do_caching_mobile_files'] = ! empty( $input['do_caching_mobile_files'] ) ? 1 : 0;

		$input['minify_google_fonts'] = ! empty( $input['minify_google_fonts'] ) ? 1 : 0;
		$input['minify_html']         = ! empty( $input['minify_html'] ) ? 1 : 0;

		// Option : Minification CSS & JS.
		$input['minify_css'] = ! empty( $input['minify_css'] ) ? 1 : 0;
		$input['minify_js']  = ! empty( $input['minify_js'] ) ? 1 : 0;

		$input['minify_concatenate_css'] = ! empty( $input['minify_concatenate_css'] ) ? 1 : 0;
		$input['minify_concatenate_js']  = ! empty( $input['minify_concatenate_js'] ) ? 1 : 0;

		$input['defer_all_js']      = ! empty( $input['defer_all_js'] ) ? 1 : 0;
		$input['defer_all_js_safe'] = ! empty( $input['defer_all_js_safe'] ) ? 1 : 0;

		// If Defer JS is deactivated, set Safe Mode for Jquery to active.
		if ( 0 === $input['defer_all_js'] ) {
			$input['defer_all_js_safe'] = 1;
		}

		// Force mobile cache & specific mobile cache if one of the mobile plugins is active.
		if ( rocket_is_mobile_plugin_active() ) {
			$input['cache_mobile']            = 1;
			$input['do_caching_mobile_files'] = 1;
		}

		$input['embeds'] = ! empty( $input['embeds'] ) ? 1 : 0;
		$input['emoji']  = ! empty( $input['emoji'] ) ? 1 : 0;

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
			'MINUTE_IN_SECONDS' => 1,
			'HOUR_IN_SECONDS'   => 1,
			'DAY_IN_SECONDS'    => 1,
		];

		$input['purge_cron_unit'] = isset( $allowed_cron_units[ $input['purge_cron_unit'] ] ) ? $input['purge_cron_unit'] : $this->options->get( 'purge_cron_unit' );

		// Force a minimum 10 minutes value for the purge interval.
		if ( $input['purge_cron_interval'] < 10 && 'MINUTE_IN_SECONDS' === $input['purge_cron_unit'] ) {
			$input['purge_cron_interval'] = 10;
		}

		// Option : Remove query strings.
		$input['remove_query_strings'] = ! empty( $input['remove_query_strings'] ) ? 1 : 0;

		// Option : Prefetch DNS requests.
		if ( ! empty( $input['dns_prefetch'] ) ) {
			if ( ! is_array( $input['dns_prefetch'] ) ) {
				$input['dns_prefetch'] = explode( "\n", $input['dns_prefetch'] );
			}
			$input['dns_prefetch'] = array_map( 'trim', $input['dns_prefetch'] );
			$input['dns_prefetch'] = array_map( 'esc_url', $input['dns_prefetch'] );
			$input['dns_prefetch'] = array_filter( $input['dns_prefetch'] );
			$input['dns_prefetch'] = array_unique( $input['dns_prefetch'] );
		} else {
			$input['dns_prefetch'] = [];
		}

		// Option : Empty the cache of the following pages when updating a post.
		if ( ! empty( $input['cache_purge_pages'] ) ) {
			if ( ! is_array( $input['cache_purge_pages'] ) ) {
				$input['cache_purge_pages'] = explode( "\n", $input['cache_purge_pages'] );
			}
			$input['cache_purge_pages'] = array_map( 'trim', $input['cache_purge_pages'] );
			$input['cache_purge_pages'] = array_map( 'esc_url', $input['cache_purge_pages'] );
			$input['cache_purge_pages'] = array_map( 'rocket_clean_exclude_file', $input['cache_purge_pages'] );
			$input['cache_purge_pages'] = array_filter( $input['cache_purge_pages'] );
			$input['cache_purge_pages'] = array_unique( $input['cache_purge_pages'] );
		} else {
			$input['cache_purge_pages'] = [];
		}

		// Option : Never cache the following pages.
		if ( ! empty( $input['cache_reject_uri'] ) ) {
			if ( ! is_array( $input['cache_reject_uri'] ) ) {
				$input['cache_reject_uri'] = explode( "\n", $input['cache_reject_uri'] );
			}
			$input['cache_reject_uri'] = array_map( 'trim', $input['cache_reject_uri'] );
			$input['cache_reject_uri'] = array_map( 'esc_url', $input['cache_reject_uri'] );
			$input['cache_reject_uri'] = array_map( 'rocket_clean_exclude_file', $input['cache_reject_uri'] );
			$input['cache_reject_uri'] = array_filter( $input['cache_reject_uri'] );
			$input['cache_reject_uri'] = array_unique( $input['cache_reject_uri'] );
		} else {
			$input['cache_reject_uri'] = [];
		}

		// Option : Don't cache pages that use the following cookies.
		if ( ! empty( $input['cache_reject_cookies'] ) ) {
			if ( ! is_array( $input['cache_reject_cookies'] ) ) {
				$input['cache_reject_cookies'] = explode( "\n", $input['cache_reject_cookies'] );
			}
			$input['cache_reject_cookies'] = array_map( 'trim', $input['cache_reject_cookies'] );
			$input['cache_reject_cookies'] = array_map( 'rocket_sanitize_key', $input['cache_reject_cookies'] );
			$input['cache_reject_cookies'] = array_filter( $input['cache_reject_cookies'] );
			$input['cache_reject_cookies'] = array_unique( $input['cache_reject_cookies'] );
		} else {
			$input['cache_reject_cookies'] = [];
		}

		// Option : Cache pages that use the following query strings (GET parameters).
		if ( ! empty( $input['cache_query_strings'] ) ) {
			if ( ! is_array( $input['cache_query_strings'] ) ) {
				$input['cache_query_strings'] = explode( "\n", $input['cache_query_strings'] );
			}
			$input['cache_query_strings'] = array_map( 'trim', $input['cache_query_strings'] );
			$input['cache_query_strings'] = array_map( 'rocket_sanitize_key', $input['cache_query_strings'] );
			$input['cache_query_strings'] = array_filter( $input['cache_query_strings'] );
			$input['cache_query_strings'] = array_unique( $input['cache_query_strings'] );
		} else {
			$input['cache_query_strings'] = [];
		}

		// Option : Never send cache pages for these user agents.
		if ( ! empty( $input['cache_reject_ua'] ) ) {
			if ( ! is_array( $input['cache_reject_ua'] ) ) {
				$input['cache_reject_ua'] = explode( "\n", $input['cache_reject_ua'] );
			}
			$input['cache_reject_ua'] = array_map( 'trim', $input['cache_reject_ua'] );
			$input['cache_reject_ua'] = array_map( 'rocket_sanitize_ua', $input['cache_reject_ua'] );
			$input['cache_reject_ua'] = array_filter( $input['cache_reject_ua'] );
			$input['cache_reject_ua'] = array_unique( $input['cache_reject_ua'] );
		} else {
			$input['cache_reject_ua'] = [];
		}

		// Option : CSS files to exclude from the minification.
		if ( ! empty( $input['exclude_css'] ) ) {
			if ( ! is_array( $input['exclude_css'] ) ) {
				$input['exclude_css'] = explode( "\n", $input['exclude_css'] );
			}
			$input['exclude_css'] = array_map( 'trim', $input['exclude_css'] );
			$input['exclude_css'] = array_map( 'rocket_clean_exclude_file', $input['exclude_css'] );
			$input['exclude_css'] = array_map( 'rocket_sanitize_css', $input['exclude_css'] );
			$input['exclude_css'] = array_filter( $input['exclude_css'] );
			$input['exclude_css'] = array_unique( $input['exclude_css'] );
		} else {
			$input['exclude_css'] = [];
		}

		// Option : JS files to exclude from the minification.
		if ( ! empty( $input['exclude_js'] ) ) {
			if ( ! is_array( $input['exclude_js'] ) ) {
				$input['exclude_js'] = explode( "\n", $input['exclude_js'] );
			}
			$input['exclude_js'] = array_map( 'trim', $input['exclude_js'] );
			$input['exclude_js'] = array_map( 'rocket_clean_exclude_file', $input['exclude_js'] );
			$input['exclude_js'] = array_map( 'rocket_sanitize_js', $input['exclude_js'] );
			$input['exclude_js'] = array_filter( $input['exclude_js'] );
			$input['exclude_js'] = array_unique( $input['exclude_js'] );
		} else {
			$input['exclude_js'] = [];
		}

		// Option: Async CSS.
		$input['async_css'] = ! empty( $input['async_css'] ) ? 1 : 0;

		// Option: Critical CSS.
		$input['critical_css'] = ! empty( $input['critical_css'] ) ? str_replace( [ '<style>', '</style>' ], '', wp_kses( $input['critical_css'], [ "\'", '\"' ] ) ) : '';

		// Database options.
		$input['database_revisions']          = ! empty( $input['database_revisions'] ) ? 1 : 0;
		$input['database_auto_drafts']        = ! empty( $input['database_auto_drafts'] ) ? 1 : 0;
		$input['database_trashed_posts']      = ! empty( $input['database_trashed_posts'] ) ? 1 : 0;
		$input['database_spam_comments']      = ! empty( $input['database_spam_comments'] ) ? 1 : 0;
		$input['database_trashed_comments']   = ! empty( $input['database_trashed_comments'] ) ? 1 : 0;
		$input['database_expired_transients'] = ! empty( $input['database_expired_transients'] ) ? 1 : 0;
		$input['database_all_transients']     = ! empty( $input['database_all_transients'] ) ? 1 : 0;
		$input['database_optimize_tables']    = ! empty( $input['database_optimize_tables'] ) ? 1 : 0;
		$input['schedule_automatic_cleanup']  = ! empty( $input['schedule_automatic_cleanup'] ) ? 1 : 0;

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
		$input['manual_preload']    = ! empty( $input['manual_preload'] ) ? 1 : 0;
		$input['automatic_preload'] = ! empty( $input['automatic_preload'] ) ? 1 : 0;

		// Option: activate sitemap preload.
		$input['sitemap_preload'] = ! empty( $input['sitemap_preload'] ) ? 1 : 0;

		// Option : XML sitemaps URLs.
		if ( ! empty( $input['sitemaps'] ) ) {
			if ( ! is_array( $input['sitemaps'] ) ) {
				$input['sitemaps'] = explode( "\n", $input['sitemaps'] );
			}
			$input['sitemaps'] = array_map( 'trim', $input['sitemaps'] );
			$input['sitemaps'] = array_map( 'rocket_sanitize_xml', $input['sitemaps'] );
			$input['sitemaps'] = array_filter( $input['sitemaps'] );
			$input['sitemaps'] = array_unique( $input['sitemaps'] );
		} else {
			$input['sitemaps'] = [];
		}

		$input['cloudflare_email']            = isset( $input['cloudflare_email'] ) ? sanitize_email( $input['cloudflare_email'] ) : '';
		$input['cloudflare_api_key']          = isset( $input['cloudflare_api_key'] ) ? sanitize_text_field( $input['cloudflare_api_key'] ) : '';
		$input['cloudflare_zone_id']          = isset( $input['cloudflare_zone_id'] ) ? sanitize_text_field( $input['cloudflare_zone_id'] ) : '';
		$input['cloudflare_devmode']          = isset( $input['cloudflare_devmode'] ) && is_numeric( $input['cloudflare_devmode'] ) ? (int) $input['cloudflare_devmode'] : 0;
		$input['cloudflare_auto_settings']    = ( isset( $input['cloudflare_auto_settings'] ) && is_numeric( $input['cloudflare_auto_settings'] ) ) ? (int) $input['cloudflare_auto_settings'] : 0;
		$input['cloudflare_protocol_rewrite'] = ! empty( $input['cloudflare_protocol_rewrite'] ) ? 1 : 0;

		// Option : CloudFlare.
		$input['do_cloudflare'] = ! empty( $input['do_cloudflare'] ) ? 1 : 0;

		if ( defined( 'WP_ROCKET_CF_API_KEY' ) ) {
			$input['cloudflare_api_key'] = WP_ROCKET_CF_API_KEY;
		}

		// Option : CDN.
		$input['cdn'] = ! empty( $input['cdn'] ) ? 1 : 0;

		// Option : CDN Cnames.
		if ( isset( $input['cdn_cnames'] ) ) {
			$input['cdn_cnames'] = array_map( 'sanitize_text_field', $input['cdn_cnames'] );
			$input['cdn_cnames'] = array_filter( $input['cdn_cnames'] );
			$input['cdn_cnames'] = array_unique( $input['cdn_cnames'] );
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
			if ( ! is_array( $input['cdn_reject_files'] ) ) {
				$input['cdn_reject_files'] = explode( "\n", $input['cdn_reject_files'] );
			}
			$input['cdn_reject_files'] = array_map( 'trim', $input['cdn_reject_files'] );
			$input['cdn_reject_files'] = array_map( 'rocket_clean_exclude_file', $input['cdn_reject_files'] );
			$input['cdn_reject_files'] = array_filter( $input['cdn_reject_files'] );
			$input['cdn_reject_files'] = array_unique( $input['cdn_reject_files'] );
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

		if ( rocket_valid_key() && ! empty( $input['secret_key'] ) && ! isset( $input['ignore'] ) ) {
			unset( $input['ignore'] );
			add_settings_error( 'general', 'settings_updated', __( 'Settings saved.', 'rocket' ), 'updated' );
		}

		return apply_filters( 'rocket_input_sanitize', $input );
	}

	/**
	 * Sanitizes the returned value of a checkbox
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param mixed $value Checkbox value.
	 * @return int
	 */
	public function sanitize_checkbox( $value ) {
		return isset( $value ) ? 1 : 0;
	}
}
