<?php
declare(strict_types=1);

namespace WP_Rocket\Addon\MaxCache;

use WP_Rocket\Buffer\Cache;
use WP_Rocket\Buffer\Tests;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * MAx Cache integration.
 *
 * @since 3.24
 */
class MaxCache {
	/**
	 * Path of the file installed by the Apache build of the module.
	 *
	 * @var string
	 */
	const VERSION_FILE_APACHE = '/opt/cloudlinux/maxcache/.version';

	/**
	 * Path of the file installed by the NGINX build of the module.
	 *
	 * @var string
	 */
	const VERSION_FILE_NGINX = '/opt/cloudlinux/maxcache/.nginx-version';

	/**
	 * A name inside the module directory that is never installed.
	 *
	 * @var string
	 */
	const PROBE_ABSENT = '/opt/cloudlinux/maxcache/.not-installed-probe';

	/**
	 * Opening marker of this add-on's contribution to the .htaccess file.
	 *
	 * @var string
	 */
	const MARKER_BEGIN = '# BEGIN MAx Cache';

	/**
	 * Closing marker of this add-on's contribution to the .htaccess file.
	 *
	 * @var string
	 */
	const MARKER_END = '# END MAx Cache';

	/**
	 * The markers around the section of .htaccess the plugin owns, spelled as it spells them.
	 *
	 * @var string
	 */
	const ROCKET_MARKER_BEGIN = '# BEGIN WP Rocket';

	/**
	 * Closing marker of that section.
	 *
	 * @var string
	 */
	const ROCKET_MARKER_END = '# END WP Rocket';

	/**
	 * Path of the socket the NGINX configuration daemon listens on.
	 *
	 * @var string
	 */
	const CONFIGD_SOCKET = '/opt/cloudlinux/maxcache/notify.sock';

	/**
	 * Detected server mode, memoized for the current request.
	 *
	 * @var string|null
	 */
	private $mode;

	/**
	 * Whether a probe of the plugin's serving rules is in progress.
	 *
	 * @var bool
	 */
	private $probing_serving_rules = false;

	/**
	 * Contents of the .htaccess file as this request first read them.
	 *
	 * @var string|null
	 */
	private $htaccess = null;

	/**
	 * Absolute path to that file, worked out once and kept: where the file is does not change with
	 * what is written into it, and finding out re-derives the home path.
	 *
	 * @var string|null
	 */
	private $htaccess_path = null;

	/**
	 * Modification time and size of the file when it was last read.
	 *
	 * @var array|null
	 */
	private $htaccess_stamp = null;

	/**
	 * Settings as the plugin's filters answered them earlier in this request.
	 *
	 * @var array
	 */
	private $lists = [];

	/**
	 * Why this configuration cannot be handed over, worked out once per request.
	 *
	 * @var string|null
	 */
	private $unsupported_reason;

	/**
	 * Options outside the settings row.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Constructor.
	 *
	 * @param Options $options_api Options instance.
	 */
	public function __construct( Options $options_api ) {
		$this->options_api = $options_api;
	}

	/**
	 * Returns the server mode the module runs in on this host.
	 *
	 * @since 3.24
	 *
	 * @return string 'apache', 'nginx' or 'none'.
	 */
	public function get_mode(): string {
		if ( null !== $this->mode ) {
			return $this->mode;
		}

		// Answered before the probes: a filter they apply may ask this class the same question and re-enter.
		$this->mode = 'none';

		// "Permission denied" proves the file is there: the module is installed root-owned, the site is not root.
		$control = $this->probe_errno( self::PROBE_ABSENT );

		$probed = 'none';

		if ( $this->path_exists( $this->get_socket_path(), $control ) || $this->path_exists( self::VERSION_FILE_NGINX, $control ) ) {
			$probed = 'nginx';
		} elseif ( $this->path_exists( self::VERSION_FILE_APACHE, $control ) ) {
			$probed = 'apache';
		}

		/**
		 * Filters the detected MAx Cache server mode.
		 *
		 * @since 3.24
		 *
		 * @param string $mode 'apache', 'nginx' or 'none'.
		 */
		// Remembered before the filter, for the same reason as above.
		$this->mode = $probed;

		$answer = wpm_apply_filters_typed( 'string', 'rocket_maxcache_mode', $probed );

		$this->mode = in_array( $answer, [ 'apache', 'nginx', 'none' ], true ) ? $answer : $probed;

		return $this->mode;
	}

	/**
	 * Whether the module is installed on this host.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	public function is_available(): bool {
		return 'none' !== $this->get_mode();
	}

	/**
	 * Whether the module is installed on an NGINX host.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	public function is_nginx(): bool {
		return 'nginx' === $this->get_mode();
	}

	/**
	 * Whether the add-on is switched on and usable.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	public function is_enabled(): bool {
		return (bool) $this->options()->get( 'maxcache', 0 ) && $this->is_available() && '' === $this->get_unsupported_reason();
	}

	/**
	 * Whether this site may be switched on without being asked.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	public function can_switch_on(): bool {
		if ( ! $this->is_available() || '' !== $this->get_unsupported_reason() ) {
			return false;
		}

		return ! $this->is_nginx() || $this->configd_answers();
	}

	/**
	 * Whether the configuration daemon accepts a connection right now.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	protected function configd_answers(): bool {
		if ( ! function_exists( 'stream_socket_client' ) ) {
			return false;
		}

		$socket = $this->open_socket( $this->get_socket_path(), 1 );

		if ( ! is_resource( $socket ) ) {
			return false;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Unix socket.
		fclose( $socket );

		return true;
	}

	/**
	 * Returns why this configuration cannot be handed to the module, or an empty string when it can.
	 *
	 * @since 3.24
	 *
	 * @return string Empty string when the configuration is supported.
	 */
	public function get_unsupported_reason(): string {
		if ( $this->probing_serving_rules ) {
			// Asked from inside this add-on's own callback on rocket_htaccess_mod_rewrite.
			return '';
		}

		if ( null === $this->unsupported_reason ) {
			// Answered before the chain runs: a filter in it asking this again would not return.
			$this->unsupported_reason = '';
			$this->unsupported_reason = $this->find_unsupported_reason();
		}

		if ( '' !== $this->unsupported_reason ) {
			return $this->unsupported_reason;
		}

		// Asked live rather than remembered: this one comes and goes inside a single request.
		if ( $this->serving_by_uri_vetoed() ) {
			return __( 'Another integration has turned off serving cache files by request URI on this site, and the module has no other way to find them.', 'rocket' );
		}

		return '';
	}

	/**
	 * Drops what was worked out about this configuration.
	 *
	 * @since 3.24
	 *
	 * @return void
	 */
	public function forget(): void {
		$this->unsupported_reason = null;
		$this->htaccess           = null;
		$this->htaccess_stamp     = null;
		$this->lists              = [];

		// Not the mode: what is installed does not change because a setting was saved, and probing costs sockets.
	}

	/**
	 * Returns a settings list as this request already read it, reading it the first time.
	 *
	 * @since 3.24
	 *
	 * @param string   $name  Name to remember the list under.
	 * @param callable $build Reads the list.
	 *
	 * @return mixed
	 */
	private function remembered( string $name, callable $build ) {
		if ( ! array_key_exists( $name, $this->lists ) ) {
			$this->lists[ $name ] = $build();
		}

		return $this->lists[ $name ];
	}

	/**
	 * Works out why this configuration cannot be handed to the module.
	 *
	 * @since 3.24
	 *
	 * @return string Empty string when the configuration is supported.
	 */
	private function find_unsupported_reason(): string {
		if ( ! $this->is_available() ) {
			return __( 'The MAx Cache module is not installed on this server.', 'rocket' );
		}

		if ( is_multisite() ) {
			return __( 'On a multisite installation WP Rocket does not serve the cache by request URI, so there is nothing for the module to take over.', 'rocket' );
		}

		// Off Apache the file is written for one reader: the configuration daemon of the NGINX build.
		if ( ! $this->htaccess_reaches_the_module() ) {
			return __( 'This web server does not read .htaccess itself, and the module installed here is the Apache build, so the rules would not reach it.', 'rocket' );
		}

		if ( ! $this->plugin_writes_cache_files() ) {
			return __( 'Page caching is disabled, so there are no files to serve.', 'rocket' );
		}

		if ( $this->options()->get( 'cache_ssl', 0 ) && $this->scheme_decided_by_proxy() ) {
			return __( 'A proxy in front of this server decides whether a request is secure, and the server itself cannot see that, so the two would not agree on which cache file a visitor gets.', 'rocket' );
		}

		// With mobile caching off the plugin serves no cache to a mobile device at all.
		if ( ! is_rocket_cache_mobile() ) {
			return __( 'Caching for mobile devices is disabled, and the list of devices WP Rocket leaves out is not one the module can be given.', 'rocket' );
		}

		if ( 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) && ! $this->options()->get( 'cache_ssl', 0 ) ) {
			return __( 'The site is served over HTTPS while caching for HTTPS is disabled, so there are no files to serve.', 'rocket' );
		}

		// Not refused for an encoded locale: the path names a file the plugin never wrote, and misses into PHP.
		if ( (bool) wpm_apply_filters_typed( '?boolean|?integer|?string', 'rocket_url_no_dots', false ) ) {
			return __( 'The host name is written into cache paths with its dots replaced, and the module cannot reproduce that.', 'rocket' );
		}

		$unwritable = $this->unwritable_value();

		if ( '' !== $unwritable ) {
			// Named by what the value is: neither reaches the plugin as a setting, only through a filter.
			$values = [
				'cookie'          => __( 'a cookie name', 'rocket' ),
				'required-cookie' => __( 'a required cookie name', 'rocket' ),
			];

			return sprintf(
				// translators: %s = what the value is, e.g. "a cookie name".
				__( 'This site varies its cache by %s that cannot be written into the server configuration as it stands, and rewriting it would name a different file than the plugin does.', 'rocket' ),
				$values[ $unwritable ] ?? $unwritable
			);
		}

		$unwritable_pattern = $this->unwritable_pattern();

		if ( '' !== $unwritable_pattern ) {
			$lists = [
				'uri'     => __( 'URLs', 'rocket' ),
				'ua'      => __( 'user agents', 'rocket' ),
				'cookies' => __( 'cookies', 'rocket' ),
			];

			return sprintf(
				// translators: %s = URLs, user agents or cookies.
				__( 'The %s this site never caches are listed in a form that cannot be written into the server configuration, and the module would serve visitors the plugin keeps out.', 'rocket' ),
				$lists[ $unwritable_pattern ] ?? $unwritable_pattern
			);
		}

		// The path is written as one directive argument, and the module's directive takes exactly one.
		if ( 1 === preg_match( '/[\s"]/', $this->get_path_template() ) ) {
			return __( 'The cache directory of this site is at a path that cannot be written into the server configuration as it stands.', 'rocket' );
		}

		if ( $this->has_nested_dynamic_cookies() ) {
			return __( 'A plugin varies the cache by parts of one cookie, and the server names those files differently.', 'rocket' );
		}

		if ( '' === $this->get_cache_root() ) {
			return __( 'The cache directory is outside the part of the filesystem the web server addresses.', 'rocket' );
		}

		return '';
	}

	/**
	 * Whether anything on this server would read the directives.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function htaccess_reaches_the_module(): bool {
		global $is_apache;

		if ( (bool) $is_apache || $this->is_nginx() ) {
			return true;
		}

		// No request to judge the server by (WP-CLI, cron): leave the answer as configured rather than flip it.
		return ! $this->request_can_judge_the_server();
	}

	/**
	 * Whether this request says anything about the server it arrived at.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function request_can_judge_the_server(): bool {
		return '' !== (string) ( isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '' );
	}

	/**
	 * Whether the plugin is set up to write cache files at all.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	protected function plugin_writes_cache_files(): bool {
		return (bool) rocket_get_constant( 'WP_CACHE', false )
			&& rocket_direct_filesystem()->exists( rocket_get_constant( 'WP_CONTENT_DIR', '' ) . '/advanced-cache.php' )
			// The same question get_rocket_htaccess_marker() puts before it writes the plugin's own rules.
			&& Cache::can_generate_caching_files();
	}

	/**
	 * Whether something in front of this server decides the scheme of a request.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	protected function scheme_decided_by_proxy(): bool {
		/**
		 * Filters whether a proxy in front of this server decides the scheme of a request.
		 *
		 * @since 3.24
		 *
		 * @param bool $decided True when the scheme is decided in front of this server.
		 */
		return wpm_apply_filters_typed( 'boolean', 'rocket_maxcache_scheme_decided_by_proxy', $this->request_scheme_comes_from_a_proxy() );
	}

	/**
	 * Whether this request was secure only in front of this server.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function request_scheme_comes_from_a_proxy(): bool {
		if ( ! is_ssl() ) {
			// PHP says the request is plain and so will the server: same port, same environment.
			return false;
		}

		// The port is the one thing in front of this server nothing can dress up, and the module reads it first.
		if ( 443 === (int) ( isset( $_SERVER['SERVER_PORT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_PORT'] ) ) : 0 ) ) {
			return false;
		}

		if ( $this->request_carries_forwarded_scheme() ) {
			return true;
		}

		// HTTPS in the environment with no header to explain it: server or wp-config snippet cannot be told apart.
		return false;
	}

	/**
	 * Whether this request carries a header that states the scheme on behalf of a proxy.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function request_carries_forwarded_scheme(): bool {
		$headers = [
			'HTTP_X_FORWARDED_PROTO',
			'HTTP_X_FORWARDED_SCHEME',
			'HTTP_X_FORWARDED_SSL',
			'HTTP_CF_VISITOR',
			'HTTP_FRONT_END_HTTPS',
		];

		foreach ( $headers as $header ) {
			if ( empty( $_SERVER[ $header ] ) ) {
				continue;
			}

			$value = strtolower( sanitize_text_field( wp_unslash( (string) $_SERVER[ $header ] ) ) );

			if ( false !== strpos( $value, 'https' ) || 'on' === $value || '1' === $value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether something else has declared that cache files must not be served by request URI.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	protected function serving_by_uri_vetoed(): bool {
		if ( $this->probing_serving_rules ) {
			return false;
		}

		$this->probing_serving_rules = true;

		try {
			/** This filter is documented in inc/functions/htaccess.php */
			$answer = wpm_apply_filters_typed( '?string|?boolean|?integer|array', 'rocket_htaccess_mod_rewrite', '' );

			// Anything but a string leaves the plugin's marker without serving rules.
			return ! is_string( $answer );
		} finally {
			$this->probing_serving_rules = false;
		}
	}


	/**
	 * Returns the directives to add to the .htaccess file.
	 *
	 * @since 3.24
	 *
	 * @return string Empty string when nothing should be written.
	 */
	public function get_rules(): string {
		if ( ! $this->is_enabled() ) {
			return '';
		}

		// Marked at both ends: the gzip lines below describe the files, not the module, and belong outside it.
		$rules = self::MARKER_BEGIN . PHP_EOL;

		$rules .= $this->get_gzip_directives();

		$rules .= '<IfModule maxcache_module>' . PHP_EOL;
		$rules .= "\t" . 'MaxCache On' . PHP_EOL;

		// Mobile devices are served like any other: the name says which of the two variants it is.
		$tablet    = $this->mobile_files_tablet();
		$separate  = $this->serves_separate_mobile_files();
		$directive = 'MaxCacheOptions -SkipCacheOnMobile';

		if ( $separate ) {
			$directive .= ' ' . ( 'mobile' === $tablet ? '+' : '-' ) . 'TabletAsMobile';
		}

		$rules .= "\t" . $directive . PHP_EOL;

		$rules .= $this->get_exclusion_directives();

		$rules .= "\t" . 'MaxCachePath ' . $this->get_path_template() . PHP_EOL;
		$rules .= '</IfModule>' . PHP_EOL;
		$rules .= self::MARKER_END . PHP_EOL;

		return $rules;
	}

	/**
	 * Whether the plugin writes a separate cache file for mobile devices.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function serves_separate_mobile_files(): bool {
		// Without the detection library the plugin's own writer names no mobile file at all.
		return $this->device_detection_available()
			&& is_rocket_generate_caching_mobile_files()
			&& '' !== $this->mobile_files_tablet();
	}

	/**
	 * Whether the library the plugin names mobile cache files with is loadable.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	protected function device_detection_available(): bool {
		return class_exists( \WP_Rocket\Dependencies\Detection\MobileDetect::class );
	}

	/**
	 * Which devices the plugin writes a separate cache file for.
	 *
	 * @since 3.24
	 *
	 * @return string 'desktop' for phones only, 'mobile' for phones and tablets, empty when neither.
	 */
	private function mobile_files_tablet(): string {
		return (string) $this->remembered(
			'mobile_files_tablet',
			function () {
				/** This filter is documented in inc/functions/files.php */
				// Read loosely: the plugin writes whatever comes back into its config and then tests it for truth.
				$tablet = wpm_apply_filters_typed( '?string|?boolean|?integer', 'rocket_cache_mobile_files_tablet', 'desktop' );

						return in_array( $tablet, [ 'desktop', 'mobile' ], true ) ? $tablet : '';
			}
		);
	}

	/**
	 * Returns the directives that describe the pre-compressed cache files.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function get_gzip_directives(): string {
		// Asked of the installed build, not of the request: behind nginx the module names the encoding itself.
		if ( $this->is_nginx() || ! $this->serves_gzip() ) {
			return '';
		}

		// The plugin's own copy of these lines, so a change to them reaches both writers.
		return get_rocket_htaccess_gzip_mime();
	}

	/**
	 * Whether the plugin writes a pre-compressed variant of each cache file.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function serves_gzip(): bool {
		return (bool) $this->remembered(
			'gzip',
			function () {
				if ( ! function_exists( 'gzencode' ) ) {
					return false;
				}

				/** This filter is documented in inc/functions/htaccess.php */
						return (bool) wpm_apply_filters_typed( '?boolean|?integer|?string', 'rocket_force_gzip_htaccess_rules', true );
			}
		);
	}

	/**
	 * Request paths that hold files rather than pages the plugin caches.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function asset_directories(): string {
		return (string) $this->remembered(
			'asset_directories',
			function () {
				return $this->build_asset_directories();
			}
		);
	}

	/**
	 * Works that out.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function build_asset_directories(): string {
		$paths = [];

		foreach ( [ content_url(), includes_url() ] as $url ) {
			$path = untrailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) );

			if ( '' !== $path ) {
				// Delimiter given because the ruleset asks for one; "#" cannot occur in a URL path.
				$paths[] = preg_quote( $path, '#' ) . '/(.*)';
			}
		}

		return implode( '|', $paths );
	}

	/**
	 * Returns everything the MaxCacheExcludeURI directive keeps from the cache.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function excluded_uri_pattern(): string {
		return implode( '|', array_filter( [ $this->excluded_uri(), $this->asset_directories() ] ) );
	}

	/**
	 * Returns the exclusion and cache-key directives derived from the plugin settings.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function get_exclusion_directives(): string {
		$directives = '';

		$excluded_uri = $this->excluded_uri_pattern();

		// Anchored at both ends and case insensitive, as the plugin's own condition is.
		if ( '' !== $excluded_uri ) {
			$directives .= "\t" . 'MaxCacheExcludeURI "(?i)^(' . $excluded_uri . ')$"' . PHP_EOL;
		}

		$excluded_ua = $this->get_excluded_ua();

		if ( ! empty( $excluded_ua ) ) {
			$directives .= "\t" . 'MaxCacheExcludeUA "(?i)^(' . $excluded_ua . ').*"' . PHP_EOL;
		}

		$excluded_cookies = $this->get_excluded_cookies();

		if ( ! empty( $excluded_cookies ) ) {
			$directives .= "\t" . 'MaxCacheExcludeCookie "(?i)(' . $excluded_cookies . ')"' . PHP_EOL;
		}

		$mandatory_cookies = $this->mandatory_cookies();

		if ( ! empty( $mandatory_cookies ) ) {
			// The getter joins names with a pipe, the directive takes them space separated.
			$directives .= "\t" . 'MaxCacheMandatoryCookies ' . implode( ' ', $mandatory_cookies ) . PHP_EOL;
		}

		$dynamic_cookies = $this->flatten_dynamic_cookies();

		if ( ! empty( $dynamic_cookies ) ) {
			$directives .= "\t" . 'MaxCacheDynamicCookies ' . implode( ' ', $dynamic_cookies ) . PHP_EOL;
		}

		$allowed_query_strings = $this->get_allowed_query_params();

		if ( ! empty( $allowed_query_strings ) ) {
			$directives .= "\t" . 'MaxCacheQSAllowedParams ' . implode( ' ', $allowed_query_strings ) . PHP_EOL;
		}

		// Independent of the list above: the plugin drops these before the path is built.
		$ignored = $this->ignored_parameters();

		if ( ! empty( $ignored ) ) {
			$directives .= "\t" . 'MaxCacheQSIgnoredParams ' . implode( ' ', $ignored ) . PHP_EOL;
		}

		return $directives;
	}

	/**
	 * Returns the cookies that must not be answered from the cache.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function get_excluded_cookies(): string {
		return (string) $this->remembered(
			'excluded_cookies',
			function () {
				return $this->build_excluded_cookies();
			}
		);
	}

	/**
	 * Works out that list.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function build_excluded_cookies(): string {
		$cookies = (string) get_rocket_cache_reject_cookies();
		$login   = $this->login_cookie_pattern();

		if ( '' === $login ) {
			return $cookies;
		}

		// Only the entry the plugin writes is recognised: a match would accept one that excludes some headers.
		if ( '' === $cookies ) {
			return $login;
		}

		return in_array( $login, explode( '|', $cookies ), true ) ? $cookies : $cookies . '|' . $login;
	}

	/**
	 * The login cookie as the plugin spells it in that list, with ".+" where the hash sits.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function login_cookie_pattern(): string {
		$hash   = (string) rocket_get_constant( 'COOKIEHASH', '' );
		$cookie = (string) rocket_get_constant( 'LOGGED_IN_COOKIE', '' );

		// Spelled as get_rocket_cache_reject_cookies() spells it, so the entry already in that list matches.
		return implode( '.+', array_map( 'preg_quote', explode( $hash, $cookie ) ) );
	}

	/**
	 * Returns the user agents that must not be answered from the cache.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function get_excluded_ua(): string {
		return (string) $this->remembered(
			'excluded_ua',
			function () {
				return (string) get_rocket_cache_reject_ua();
			}
		);
	}

	/**
	 * Whether the option is on but no WebP variant is actually written.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function webp_cache_disabled(): bool {
		return (bool) $this->remembered(
			'webp_disabled',
			function () {
				/** This filter is documented in inc/classes/Buffer/class-cache.php */
						return (bool) wpm_apply_filters_typed( '?boolean|?integer|?string', 'rocket_disable_webp_cache', false );
			}
		);
	}

	/**
	 * Returns the query parameters a cached page may be addressed by.
	 *
	 * @since 3.24
	 *
	 * @return array
	 */
	private function get_allowed_query_params(): array {
		return (array) $this->remembered(
			'query_params',
			function () {
				// The plugin's own list: a parameter added there has to reach the directive too.
				$always_allowed = array_keys( Tests::ALWAYS_ALLOWED_QUERY_PARAMS );

				return $this->directive_safe_names( array_values( array_unique( array_merge( $always_allowed, (array) get_rocket_cache_query_string() ) ) ) );
			}
		);
	}

	/**
	 * Returns the path template the module resolves for every request.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	public function get_path_template(): string {
		$segments = [ $this->get_cache_root(), '{HTTP_HOST}', '{REQUEST_URI}', '{QS_SUFFIX}', '/index' ];

		// Named before the scheme, as the plugin names it (Buffer\Cache::get_cache_path()).
		if ( $this->serves_separate_mobile_files() ) {
			$segments[] = '{MOBILE_SUFFIX}';
		}

		$segments[] = '{SSL_SUFFIX}';

		if ( $this->options()->get( 'cache_webp', 0 ) && ! $this->webp_cache_disabled() ) {
			$segments[] = '{WEBP_SUFFIX}';
		}

		if ( ! empty( $this->flatten_dynamic_cookies() ) ) {
			$segments[] = '{DYNAMIC_COOKIE_SUFFIX}';
		}

		$segments[] = '.html';

		if ( $this->serves_gzip() ) {
			$segments[] = '{GZIP_SUFFIX}';
		}

		return implode( '', $segments );
	}

	/**
	 * Whether nothing in this request can say where the server addresses the cache directory.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	public function rules_cannot_be_built(): bool {
		// Compared as raw as get_rocket_htaccess_mod_rewrite() compares them: this predicts that writer.
		return ! isset( $_SERVER['DOCUMENT_ROOT'] )
			&& false === strpos( (string) rocket_get_constant( 'WP_ROCKET_CACHE_PATH', '' ), (string) rocket_get_constant( 'ABSPATH', '' ) );
	}

	/**
	 * Returns the cache directory as a path starting at the document root.
	 *
	 * @since 3.24
	 *
	 * @return string Trailing-slashed path, or an empty string when there is none.
	 */
	public function get_cache_root(): string {
		$cache_path = trailingslashit( wp_normalize_path( (string) rocket_get_constant( 'WP_ROCKET_CACHE_PATH', '' ) ) );
		$abspath    = trailingslashit( wp_normalize_path( (string) rocket_get_constant( 'ABSPATH', '' ) ) );

		// Nothing to compare against; the empty answer is a refusal handled in find_unsupported_reason().
		if ( '/' === $cache_path || '/' === $abspath ) {
			return '';
		}

		$docroot = isset( $_SERVER['DOCUMENT_ROOT'] )
			? trailingslashit( wp_normalize_path( sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) ) )
			: '';

		// The directory moved out of the install: the plugin names it from the document root.
		if ( false === strpos( $cache_path, $abspath ) ) {
			// Under neither the install nor the document root: nothing on this server addresses it.
			if ( '' === $docroot || '/' === $docroot || 0 !== strpos( $cache_path, $docroot ) ) {
				return '';
			}

			return '/' . ltrim( str_replace( $docroot, '', $cache_path ), '/' );
		}

		// Under the install: the plugin puts the path the site lives at in front of what is left.
		$site_root = (string) wp_parse_url( site_url(), PHP_URL_PATH );
		$site_root = '' !== $site_root ? trailingslashit( $site_root ) : '';

		return '/' . ltrim( $site_root . str_replace( $abspath, '', $cache_path ), '/' );
	}

	/**
	 * Names the first setting carrying a value this add-on cannot put in a directive, if any.
	 *
	 * @since 3.24
	 *
	 * @return string Slug naming what the value is, empty when everything can be written.
	 */
	private function unwritable_value(): string {
		// Keyed by a slug, never by a translated label: two labels alike in some locale would collapse into one.
		// Cookies only: a query-string name left out costs a hit, a cookie name left out hands out the wrong file.
		$names = [
			'cookie'          => $this->flatten_dynamic_cookies(),
			'required-cookie' => $this->mandatory_cookies(),
		];

		foreach ( $names as $setting => $values ) {
			foreach ( $values as $value ) {
				if ( (string) preg_replace( '/[^A-Za-z0-9_\-\.]/', '', (string) $value ) !== (string) $value ) {
					return $setting;
				}
			}
		}

		return '';
	}

	/**
	 * Names what this site never caches by a rule this add-on cannot put in a directive, if any.
	 *
	 * @since 3.24
	 *
	 * @return string Slug naming the list, empty when every exclusion can be written.
	 */
	private function unwritable_pattern(): string {
		$patterns = [
			// The whole value the directive carries, not the setting alone: preg_quote() does not escape a quote.
			'uri'     => $this->excluded_uri_pattern(),
			'ua'      => $this->get_excluded_ua(),
			'cookies' => $this->get_excluded_cookies(),
		];

		foreach ( $patterns as $name => $pattern ) {
			// Written inside a quoted directive argument, which neither can carry.
			if ( false !== strpos( $pattern, '"' ) || 1 === preg_match( '/[\r\n]/', $pattern ) ) {
				return $name;
			}

			// The module logs a pattern it cannot compile and goes on serving, so the exclusion would stop applying.
			if ( '' !== $pattern && false === @preg_match( "\1{$pattern}\1", '' ) ) { // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				return $name;
			}
		}

		return '';
	}

	/**
	 * Whether any dynamic cookie is declared as a set of keys inside one cookie.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	private function has_nested_dynamic_cookies(): bool {
		foreach ( $this->dynamic_cookies() as $cookie ) {
			if ( is_array( $cookie ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * The URL patterns the plugin never serves from the cache.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function excluded_uri(): string {
		return (string) $this->remembered(
			'excluded_uri',
			function () {
				// Asked afresh, as the plugin's own config writer asks it: that getter keeps a static of its own.
				return (string) get_rocket_cache_reject_uri( true );
			}
		);
	}

	/**
	 * The query parameters the plugin drops before it builds the path.
	 *
	 * @since 3.24
	 *
	 * @return array
	 */
	private function ignored_parameters(): array {
		return (array) $this->remembered(
			'ignored_parameters',
			function () {
				return $this->directive_safe_names( array_keys( (array) rocket_get_ignored_parameters() ) );
			}
		);
	}

	/**
	 * Keeps the names that can stand as a bare directive argument, dropping the rest.
	 *
	 * Only for the two query-string lists: a name left out of them narrows what the module may answer,
	 * so the request reaches PHP. A cookie name left out would change which file a visitor gets.
	 *
	 * @since 3.24
	 *
	 * @param array $names Parameter names.
	 *
	 * @return array
	 */
	private function directive_safe_names( array $names ): array {
		return array_values(
			array_filter(
				$names,
				static function ( $name ) {
					$name = (string) $name;

					return '' !== $name && (string) preg_replace( '/[^A-Za-z0-9_\-\.]/', '', $name ) === $name;
				}
			)
		);
	}

	/**
	 * Cookie names the plugin insists on seeing before it serves anything from the cache.
	 *
	 * @since 3.24
	 *
	 * @return array
	 */
	private function mandatory_cookies(): array {
		return (array) $this->remembered(
			'mandatory_cookies',
			function () {
				return array_values( array_filter( explode( '|', (string) get_rocket_cache_mandatory_cookies() ) ) );
			}
		);
	}

	/**
	 * Returns the plugin's dynamic cookies as they stand, read once for the whole block.
	 *
	 * @since 3.24
	 *
	 * @return array
	 */
	private function dynamic_cookies(): array {
		return (array) $this->remembered(
			'dynamic_cookies',
			function () {
				return (array) get_rocket_cache_dynamic_cookies();
			}
		);
	}

	/**
	 * Returns the dynamic cookie names as a flat list.
	 *
	 * @since 3.24
	 *
	 * @return array
	 */
	private function flatten_dynamic_cookies(): array {
		$names = [];

		// A nested definition is refused by has_nested_dynamic_cookies() before anything is written.
		foreach ( $this->dynamic_cookies() as $cookie ) {
			if ( is_array( $cookie ) ) {
				continue;
			}

			$names[] = (string) $cookie;
		}

		return $names;
	}

	/**
	 * Whether the .htaccess file has to be written even though the server is not Apache.
	 *
	 * @since 3.24
	 *
	 * @param bool $needed   Value passed by the filter.
	 * @param bool $removing Whether the caller is taking the plugin's rules out of the file.
	 *
	 * @return bool
	 */
	public function is_htaccess_needed( $needed = false, $removing = false ): bool {
		if ( (bool) $needed ) {
			return true;
		}

		if ( (bool) $removing ) {
			// Directives of ours keep the server serving after the plugin is gone, so taking them out is worth a write.
			if ( $this->has_directives() ) {
				return true;
			}

			// A block behind nginx with nothing of ours left is this add-on's leftover; an unknown path answers no.
			return $this->is_nginx()
				&& '' !== $this->read_rocket_block()
				&& '' !== $this->htaccess_path()
				&& rocket_direct_filesystem()->is_writable( $this->htaccess_path() );
		}

		// Off Apache the file is written for the module’s sake alone.
		if (
			(bool) $this->options()->get( 'maxcache', 0 )
			// The reasons this add-on refuses for are read from the request; a write from WP-CLI would undo one.
			&& $this->request_can_judge_the_server()
			&& $this->is_nginx()
			&& '' !== $this->htaccess_path()
			&& ! $this->rules_cannot_be_built()
			&& $this->is_enabled()
		) {
			return true;
		}

		// Nothing of ours can be in the file on a site where this switch has never been answered.
		if ( ! $this->decision_recorded() ) {
			return false;
		}

		// Ours to take out whatever the server is, and whether or not the module is still installed.
		return $this->has_directives() && ! $this->rules_cannot_be_built();
	}

	/**
	 * Returns the plugin settings as the row holds them now.
	 *
	 * @since 3.24
	 *
	 * @return Options_Data
	 */
	public function options(): Options_Data {
		return new Options_Data( (array) $this->options_api->get( 'settings', [] ) );
	}

	/**
	 * Whether this site has an answer stored for the add-on's switch, either way.
	 *
	 * @since 3.24
	 *
	 * @return bool
	 */
	public function decision_recorded(): bool {
		$settings = (array) $this->options_api->get( 'settings', [] );

		return isset( $settings['maxcache'] );
	}

	/**
	 * Takes this add-on's section out of the file without touching anything else in it.
	 *
	 * @since 3.24
	 *
	 * @return bool Whether the section is gone from the file.
	 */
	public function remove_directives(): bool {
		$filename   = $this->htaccess_path();
		$filesystem = rocket_direct_filesystem();

		if ( '' === $filename || ! $filesystem->exists( $filename ) || ! $filesystem->is_writable( $filename ) ) {
			return false;
		}

		// Read and written through one handle, holding the same lock flush_rocket_htaccess() takes.
		// phpcs:disable WordPress.WP.AlternativeFunctions -- The plugin's own writer, same protocol.
		$pointer = fopen( $filename, 'r+' );

		if ( ! $pointer ) {
			return false;
		}

		flock( $pointer, LOCK_EX );

		$contents = stream_get_contents( $pointer );

		// Unreadable is not "without our section": success here would stop the caller while directives remain.
		if ( ! is_string( $contents ) ) {
			flock( $pointer, LOCK_UN );
			fclose( $pointer );

			return false;
		}

		$stripped = $this->without_own_section( $contents );

		if ( $stripped === $contents ) {
			flock( $pointer, LOCK_UN );
			fclose( $pointer );

			// A section of the same name elsewhere in the file belongs to whoever wrote it.
			return false === strpos( $this->rocket_block_in( $contents ), self::MARKER_BEGIN );
		}

		// Whatever happens next, what was read earlier in this request no longer describes the file.
		$this->htaccess = null;

		fseek( $pointer, 0 );
		$bytes = fwrite( $pointer, $stripped );

		// A short write would leave the server reading a directive that stops mid-word: 500 on every request.
		if ( strlen( $stripped ) !== $bytes ) {
			// Truncated nowhere: a partial write leaves the file at its own length, and cutting throws away the rest.
			fseek( $pointer, 0 );
			fwrite( $pointer, $contents );
			fflush( $pointer );
			flock( $pointer, LOCK_UN );
			fclose( $pointer );

			return false;
		}

		ftruncate( $pointer, $bytes );
		fflush( $pointer );
		flock( $pointer, LOCK_UN );
		fclose( $pointer );
		// phpcs:enable WordPress.WP.AlternativeFunctions

		return true;
	}

	/**
	 * Returns the file without this add-on's contribution, and without touching anything else.
	 *
	 * @since 3.24
	 *
	 * @param string $contents Contents of the .htaccess file.
	 *
	 * @return string
	 */
	private function without_own_section( string $contents ): string {
		foreach ( $this->rocket_blocks_in( $contents ) as $block ) {
			list( $block_start, $block_end ) = $block;

			$start = strpos( $contents, self::MARKER_BEGIN, $block_start );

			if ( false === $start || $start > $block_end ) {
				continue;
			}

			$end = strpos( $contents, self::MARKER_END, $start );

			if ( false === $end || $end > $block_end ) {
				continue;
			}

			$end += strlen( self::MARKER_END );

			// The marker's own line ending goes with it, and only that one.
			if ( "\r" === ( $contents[ $end ] ?? '' ) ) {
				++$end;
			}

			if ( "\n" === ( $contents[ $end ] ?? '' ) ) {
				++$end;
			}

			// One section per pass, and the caller asks again: the offsets after this one have moved.
			return $this->without_own_section( substr( $contents, 0, $start ) . substr( $contents, $end ) );
		}

		return $contents;
	}

	/**
	 * Where the plugin's own blocks sit in the given file contents.
	 *
	 * @since 3.24
	 *
	 * @param string $contents Contents of the .htaccess file.
	 *
	 * @return array List of [ start offset, offset of the closing marker ] pairs.
	 */
	private function rocket_blocks_in( string $contents ): array {
		$blocks = [];
		$start  = strpos( $contents, self::ROCKET_MARKER_BEGIN );

		while ( false !== $start ) {
			$end = strpos( $contents, self::ROCKET_MARKER_END, $start );

			// No closing marker: a write cut short, or a hand-edit.
			if ( false === $end ) {
				$blocks[] = [ $start, strlen( $contents ) ];

				break;
			}

			$blocks[] = [ $start, $end ];

			$start = strpos( $contents, self::ROCKET_MARKER_BEGIN, $end + strlen( self::ROCKET_MARKER_END ) );
		}

		return $blocks;
	}

	/**
	 * Whether the .htaccess file currently holds this add-on's directives.
	 *
	 * @since 3.24
	 *
	 * @phpstan-impure Answered from what this request read once, and that reading is dropped by any
	 *                 write to the file, so a call after one reads the file again.
	 *
	 * @return bool
	 */
	public function has_directives(): bool {
		return false !== strpos( $this->read_rocket_block(), self::MARKER_BEGIN );
	}

	/**
	 * Returns the plugin's own block from the .htaccess file, or an empty string when it is absent.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function read_rocket_block(): string {
		return $this->rocket_block_in( $this->read_htaccess() );
	}

	/**
	 * Returns the plugin's own blocks out of the given file contents, one after another.
	 *
	 * @since 3.24
	 *
	 * @param string $contents Contents of the .htaccess file.
	 *
	 * @return string Empty string when the plugin has nothing in the file.
	 */
	private function rocket_block_in( string $contents ): string {
		$blocks = [];

		foreach ( $this->rocket_blocks_in( $contents ) as $block ) {
			list( $start, $end ) = $block;

			$blocks[] = substr( $contents, $start, $end - $start );
		}

		return implode( PHP_EOL, $blocks );
	}

	/**
	 * Absolute path to the file this add-on reads and writes, or an empty string where it cannot say.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	public function htaccess_path(): string {
		if ( null !== $this->htaccess_path ) {
			return $this->htaccess_path;
		}

		if ( ! function_exists( 'get_home_path' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		// The same expression flush_rocket_htaccess() builds the name it writes from.
		$home = get_home_path();

		$this->htaccess_path = '/' === $home && '/' !== (string) rocket_get_constant( 'ABSPATH', '/' )
			? ''
			: $home . '.htaccess';

		return $this->htaccess_path;
	}

	/**
	 * Returns the contents of the .htaccess file, or an empty string when there is none.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function read_htaccess(): string {
		$filename   = $this->htaccess_path();
		$filesystem = rocket_direct_filesystem();

		if ( '' === $filename || ! $filesystem->exists( $filename ) ) {
			// The stamp goes with it: kept, it could validate this empty answer against a file that is there again.
			$this->htaccess       = '';
			$this->htaccess_stamp = null;

			return '';
		}

		// Kept for the request, but only while the file is the same. Stat cleared first: raw handles leave it stale.
		clearstatcache( true, $filename );

		$stamp = [ (int) $filesystem->mtime( $filename ), (int) $filesystem->size( $filename ) ];

		if ( null !== $this->htaccess && $stamp === $this->htaccess_stamp ) {
			return $this->htaccess;
		}

		$contents = $filesystem->get_contents( $filename );

		$this->htaccess       = is_string( $contents ) ? $contents : '';
		$this->htaccess_stamp = $stamp;

		return $this->htaccess;
	}

	/**
	 * Tells the configuration daemon that the file it reads has changed.
	 *
	 * @since 3.24
	 *
	 * @param string $docroot Directory holding the file that was written.
	 *
	 * @return bool True when the daemon acknowledged the change.
	 */
	public function notify_configd( string $docroot ): bool {
		if ( ! function_exists( 'stream_socket_client' ) ) {
			return false;
		}

		$socket = $this->open_socket( $this->get_socket_path(), 1 );

		if ( ! is_resource( $socket ) ) {
			return false;
		}

		$payload = wp_json_encode(
			[
				'action' => 'reload',
				'path'   => untrailingslashit( $docroot ),
				'domain' => wp_parse_url( home_url(), PHP_URL_HOST ),
			]
		);

		// A daemon that accepts the connection and then goes quiet must not hold up the request.
		stream_set_timeout( $socket, 1 );

		// phpcs:disable WordPress.WP.AlternativeFunctions -- Unix socket, not the filesystem.
		fwrite( $socket, (string) $payload );
		$response = fread( $socket, 4096 );
		fclose( $socket );
		// phpcs:enable WordPress.WP.AlternativeFunctions

		return false !== $response && '' !== trim( (string) $response );
	}

	/**
	 * Returns the daemon socket path.
	 *
	 * @since 3.24
	 *
	 * @return string
	 */
	private function get_socket_path(): string {
		/**
		 * Filters the path to the MAx Cache configuration daemon socket.
		 *
		 * @since 3.24
		 *
		 * @param string $socket_path Absolute path to the socket.
		 */
		return wpm_apply_filters_typed( 'string', 'rocket_maxcache_configd_socket_path', self::CONFIGD_SOCKET );
	}

	/**
	 * What the unix:// wrapper answers for a path, as an error number.
	 *
	 * @since 3.24
	 *
	 * @param string $path Absolute path to probe.
	 *
	 * @return int|null Error number, or null where the probe cannot be made at all.
	 */
	private function probe_errno( string $path ): ?int {
		if ( '' === $path || ! function_exists( 'stream_socket_client' ) ) {
			return null;
		}

		$errno  = null;
		$socket = $this->open_socket( $path, 0.1, $errno );

		if ( is_resource( $socket ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Unix socket.
			fclose( $socket );

			// A name that is never installed answering a connection says the probe tells nothing apart on this host.
			return null;
		}

		return (int) $errno;
	}

	/**
	 * Whether a path outside the document root exists.
	 *
	 * @since 3.24
	 *
	 * @param string   $path    Absolute path to test.
	 * @param int|null $control What a name that is never installed answered, or null to answer only
	 *                          on a connection.
	 *
	 * @return bool
	 */
	private function path_exists( string $path, $control = null ): bool {
		if ( '' === $path || ! function_exists( 'stream_socket_client' ) ) {
			return false;
		}

		$socket = $this->open_socket( $path, 0.1, $errno );

		if ( is_resource( $socket ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- Unix socket.
			fclose( $socket );

			return true;
		}

		// Judged by comparison, not by a table of error numbers: a name never installed is probed the same way.
		return null !== $control && $errno !== $control;
	}

	/**
	 * Opens a Unix socket without surfacing a warning.
	 *
	 * @since 3.24
	 *
	 * @param string   $path    Absolute path to the socket.
	 * @param float    $timeout Connection timeout in seconds.
	 * @param int|null $errno   Set to the connection error code.
	 *
	 * @return resource|false
	 */
	protected function open_socket( string $path, float $timeout, &$errno = null ) {
		$errno  = 0;
		$errstr = '';

		// A path that is not there is the answer; silenced with "@" because the error number is what gets read.
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		return @stream_socket_client( 'unix://' . $path, $errno, $errstr, $timeout );
	}
}
