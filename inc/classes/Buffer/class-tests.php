<?php
namespace WP_Rocket\Buffer;

/**
 * Handle the tests for page cache and optimizations.
 *
 * @since  3.3
 * @author Grégory Viguier
 */
class Tests {
	use \WP_Rocket\Traits\Memoize;

	/**
	 * Config instance
	 *
	 * @var Config
	 */
	private $config;

	/**
	 * Values of $_COOKIE to use for some tests.
	 *
	 * @var    array
	 * @since  3.3
	 * @author Grégory Viguier
	 */
	private static $cookies;

	/**
	 * Values of $_POST to use for some tests.
	 *
	 * @var    array
	 * @since  3.3
	 * @author Grégory Viguier
	 */
	private static $post;

	/**
	 * Values of $_GET to use for some tests.
	 *
	 * @var    array
	 * @since  3.3
	 * @author Grégory Viguier
	 */
	private static $get;

	/**
	 * Array of complementary tests to perform.
	 *
	 * @var    array Tests are listed as array keys.
	 * @since  3.3
	 * @author Grégory Viguier
	 */
	private $tests = [
		'query_string'     => 1,
		'ssl'              => 1,
		'uri'              => 1,
		'rejected_cookie'  => 1,
		'mandatory_cookie' => 1,
		'user_agent'       => 1,
		'mobile'           => 1,
		'donotcachepage'   => 1,
		'wp_404'           => 1,
		'search'           => 1,
		'is_html'          => 1,
	];

	/**
	 * Information about the last "error".
	 * Here an "error" is a test failure.
	 *
	 * @var    array {
	 *     @type string $message A message.
	 *     @type array  $data    Related data.
	 * }
	 * @since  3.3
	 * @author Grégory Viguier
	 */
	private $last_error = [];

	/**
	 * Constructor.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @param Config $config Config instance.
	 * @param array  $args {
	 *     An array of arguments.
	 *
	 *     @type array  $cookies         Values of $_COOKIE to use for the tests. Default is $_COOKIE.
	 *     @type array  $post            Values of $_POST to use for the tests. Default is $_POST
	 *     @type array  $get             Values of $_GET to use for the tests. Default is $_GET.
	 *     @type array  $tests           List of complementary tests to perform. Optional.
	 * }
	 */
	public function __construct( Config $config, array $args = [] ) {
		$this->config = $config;

		if ( ! empty( $args['tests'] ) ) {
			$this->set_tests( (array) $args['tests'] );
		}

		// Provide fallback values.
		if ( ! isset( $args['cookies'] ) && ! empty( $_COOKIE ) && is_array( $_COOKIE ) ) {
			$args['cookies'] = $_COOKIE;
		}

		if ( ! isset( $args['post'] ) && ! empty( $_POST ) && is_array( $_POST ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$args['post'] = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
		if ( ! isset( $args['get'] ) && ! empty( $_GET ) && is_array( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
			$args['get'] = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.NonceVerification.Recommended
		}

		self::$cookies = ! empty( $args['cookies'] ) && is_array( $args['cookies'] ) ? $args['cookies'] : [];
		self::$post    = ! empty( $args['post'] ) && is_array( $args['post'] ) ? $args['post'] : [];
		self::$get     = ! empty( $args['get'] ) && is_array( $args['get'] ) ? $args['get'] : [];

		if ( self::$post ) {
			self::$post = array_intersect_key(
				// Limit self::$post to the values we need, to save a bit of memory.
				self::$post,
				[
					'wp_customize' => '',
				]
			);
		}
	}

	/** ----------------------------------------------------------------------------------------- */
	/** TESTS =================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Tell if the process should be initiated.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_init_process() {
		$this->last_error = [];

		// Don't process robots.txt && .htaccess files (it has happened sometimes with weird server configuration).
		if ( $this->is_rejected_file() ) {
			$this->set_error( 'Robots.txt or .htaccess file is excluded.' );
			return false;
		}

		// Don't process disallowed file extensions (like php, xml, xsl).
		if ( $this->is_rejected_extension() ) {
			$this->set_error( 'PHP, XML, or XSL file is excluded.' );
			return false;
		}

		// Don't cache if in admin or ajax.
		if ( $this->is_admin() ) {
			$this->set_error( 'Admin or AJAX URL is excluded.' );
			return false;
		}

		// Don't process the customizer preview.
		if ( $this->is_customizer_preview() ) {
			$this->set_error( 'Customizer preview is excluded.' );
			return false;
		}

		// Don't process without GET method.
		if ( ! $this->is_allowed_request_method() ) {
			$this->set_error(
				'Request method is not allowed. Page cannot be cached.',
				[
					'request_method' => $this->get_request_method(),
				]
			);
			return false;
		}

		if ( ! $this->has_test() ) {
			$this->last_error = [];
			return true;
		}

		$config = $this->config->get_config_file_path();
		// Exit if no config file exists.
		if ( ! $config['success'] ) {
			$this->set_error(
				'No config file found.',
				[
					'config_path' => $config['path'],
				]
			);
			return false;
		}

		// Don’t process with query strings parameters, but the processed content is served if the visitor comes from an RSS feed, a Facebook action or Google Adsense tracking.
		if ( $this->has_test( 'query_string' ) && ! $this->can_process_query_string() ) {
			$this->set_error( 'Query string URL is excluded.' . PHP_EOL . print_r( $_GET, true ) );// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r, WordPress.Security.NonceVerification.Recommended
			return false;
		}

		// Don't process SSL.
		if ( $this->has_test( 'ssl' ) && ! $this->can_process_ssl() ) {
			$this->set_error( 'SSL cache not applied to page.' );
			return false;
		}

		// Don't process these pages.
		if ( $this->has_test( 'uri' ) && ! $this->can_process_uri() ) {
			$this->set_error( 'Page is excluded.' );
			return false;
		}

		// Don't process page with these cookies.
		if ( $this->has_test( 'rejected_cookie' ) && $this->has_rejected_cookie() ) {
			$this->set_error(
				'Excluded cookie found.',
				[
					'excluded_cookies' => $this->has_rejected_cookie(),
				]
			);
			return false;
		}

		// Don't process page when these cookies don't exist.
		if ( $this->has_test( 'mandatory_cookie' ) && ! $this->is_speed_tool() && is_array( $this->has_mandatory_cookie() ) ) {
			$this->set_error(
				'Missing mandatory cookie: page not processed.',
				[
					'missing_cookies' => $this->has_mandatory_cookie(),
				]
			);
			return false;
		}

		// Don't process page with these user agents.
		if ( $this->has_test( 'user_agent' ) && ! $this->can_process_user_agent() ) {
			$this->set_error(
				'User Agent is excluded.',
				[
					'user_agent' => $this->config->get_server_input( 'HTTP_USER_AGENT' ),
				]
			);
			return false;
		}

		// Don't process if mobile detection is activated.
		if ( $this->has_test( 'mobile' ) && ! $this->can_process_mobile() ) {
			$this->set_error(
				'Mobile User Agent is excluded.',
				[
					'user_agent' => $this->config->get_server_input( 'HTTP_USER_AGENT' ),
				]
			);
			return false;
		}

		$this->last_error = [];

		return true;
	}

	/**
	 * Tell if a test should be performed.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @param  string $test_name Identifier of the test.
	 *                           Possible values are: 'query_string', 'ssl', 'uri', 'rejected_cookie', 'mandatory_cookie', 'user_agent', 'mobile'.
	 * @return bool
	 */
	public function has_test( $test_name = '' ) {
		if ( empty( $test_name ) ) {
			return ! empty( $this->tests );
		}

		return isset( $this->tests[ $test_name ] );
	}

	/**
	 * Set the list of tests to perform.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @param array $tests An array of test names.
	 */
	public function set_tests( array $tests ) {
		$tests = array_flip( $tests );

		array_merge( $this->tests, $tests );
	}

	/**
	 * Tell if the buffer should be processed.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @param  string $buffer The buffer content.
	 * @return bool
	 */
	public function can_process_buffer( $buffer ) {
		$this->last_error = [];

		if ( ! function_exists( 'rocket_mkdir_p' ) ) {
			// Uh?
			$this->set_error( 'WP Rocket not found - page cannot be cached.' );
			return false;
		}

		if ( strlen( $buffer ) <= 255 ) {
			// Buffer length must be > 255 (IE does not read pages under 255 c).
			$this->set_error( 'Buffer content under 255 caracters.' );
			return false;
		}

		if ( $this->get_http_response_code() !== 200 ) {
			// Only cache 200.
			$this->set_error( 'Page is not a 200 HTTP response and cannot be cached.' );
			return false;
		}

		if ( $this->has_test( 'donotcachepage' ) && $this->has_donotcachepage() ) {
			// Don't process templates that use the DONOTCACHEPAGE constant.
			$this->set_error( 'DONOTCACHEPAGE is defined. Page cannot be cached.' );
			return false;
		}

		if ( $this->has_test( 'wp_404' ) && $this->is_404() ) {
			// Don't process WP 404 page.
			$this->set_error( 'WP 404 page is excluded.' );
			return false;
		}

		if ( $this->has_test( 'search' ) && $this->is_search() ) {
			// Don't process search results.
			$this->set_error( 'Search page is excluded.' );
			return false;
		}

		if ( $this->has_test( 'is_html' ) ) {
			if ( $this->is_feed_uri() || defined( 'REST_REQUEST' ) ) {
				unset( $this->tests['is_html'] );
			}
		}

		if (
			$this->has_test( 'is_html' )
			&&
			! $this->is_html( $buffer )
		) {
			// Don't process if there isn't a closing </html>.
			$this->set_error( 'No closing </html> was found.' );
			return false;
		}

		$this->last_error = [];

		return true;
	}

	/**
	 * Return http response to prevent a bug while testing.
	 *
	 * @return bool|int
	 */
	public function get_http_response_code() {
		return http_response_code();
	}

	/** ----------------------------------------------------------------------------------------- */
	/** SEPARATED TESTS ========================================================================= */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Tell if the current URI corresponds to a file that must not be processed.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_rejected_file() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$request_uri = $this->get_request_uri_base();

		if ( ! $request_uri ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		$files = [
			'robots.txt',
			'.htaccess',
		];

		foreach ( $files as $file ) {
			if ( false !== strpos( $request_uri, '/' . $file ) ) {
				return self::memoize( __FUNCTION__, [], true );
			}
		}

		return self::memoize( __FUNCTION__, [], false );
	}

	/**
	 * Tell if the current URI corresponds to a file extension that must not be processed.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_rejected_extension() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$request_uri = $this->get_request_uri_base();

		if ( ! $request_uri ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		if ( strtolower( $request_uri ) === '/index.php' ) {
			// `index.php` is allowed.
			return self::memoize( __FUNCTION__, [], false );
		}

		$extension  = pathinfo( $request_uri, PATHINFO_EXTENSION );
		$extensions = [
			'php' => 1,
			'xml' => 1,
			'xsl' => 1,
		];

		$is_rejected = $extension && isset( $extensions[ $extension ] );

		return self::memoize( __FUNCTION__, [], $is_rejected );
	}

	/**
	 * Tell if the current url is a feed.
	 *
	 * @return bool
	 */
	public function is_feed_uri() {
		global $wp_rewrite;
		$feed_uri = '/(?:.+/)?' . $wp_rewrite->feed_base . '(?:/(?:.+/?)?)?$';
		return (bool) preg_match( '#^(' . $feed_uri . ')$#i', $this->get_clean_request_uri() );
	}

	/**
	 * Tell if we're in the admin area (or ajax) or not.
	 * Test against ajax added in 2e3c0fa74246aa13b36835f132dfd55b90d4bf9e for whatever reason.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_admin() {
		return is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX );
	}

	/**
	 * Tell if we're displaying a customizer preview.
	 * Test added in 769c7377e764a6a8decb4015a167b34043b4b462 for whatever reason.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_customizer_preview() {
		return isset( self::$post['wp_customize'] );
	}

	/**
	 * Tell if the request method is allowed to be cached.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_allowed_request_method() {
		$allowed = [
			'GET'  => 1,
			'HEAD' => 1,
		];

		if ( isset( $allowed[ $this->get_request_method() ] ) ) {
			return true;
		}

		return false;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** SEPARATED TESTS THAT USE THE CONFIG FILE ================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Don't process with query string parameters, some parameters are allowed though.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_process_query_string() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$params = $this->get_query_params();

		if ( ! $params ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		// The page can be processed if at least one of these parameters is present.
		$allowed_params = [
			'lang'            => 1,
			's'               => 1,
			'permalink_name'  => 1,
			'lp-variation-id' => 1,
		];

		if ( array_intersect_key( $params, $allowed_params ) ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		// The page can be processed if at least one of these parameters is present.
		$allowed_params = $this->config->get_config( 'cache_query_strings' );

		if ( ! $allowed_params ) {
			// We have query strings but none is in the list set by the user.
			return self::memoize( __FUNCTION__, [], false );
		}

		$can = (bool) array_intersect_key( $params, array_flip( $allowed_params ) );

		return self::memoize( __FUNCTION__, [], $can );
	}

	/**
	 * Process SSL only if set in the plugin settings.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_process_ssl() {
		return ! $this->is_ssl() || $this->config->get_config( 'cache_ssl' );
	}

	/**
	 * Some URIs set in the plugin settings must not be processed.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_process_uri() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		// URIs not to cache.
		$uri_pattern = $this->config->get_config( 'cache_reject_uri' );

		if ( ! $uri_pattern ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		$can = ! preg_match( '#^(' . $uri_pattern . ')$#i', $this->get_request_uri_base() );

		return self::memoize( __FUNCTION__, [], $can );
	}

	/**
	 * Don't process if some cookies are present.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool|array
	 */
	public function has_rejected_cookie() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		if ( ! self::$cookies ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		$rejected_cookies = $this->config->get_rejected_cookies();

		if ( ! $rejected_cookies ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		$excluded_cookies = [];

		foreach ( array_keys( self::$cookies ) as $cookie_name ) {
			if ( preg_match( $rejected_cookies, $cookie_name ) ) {
				$excluded_cookies[] = $cookie_name;
			}
		}

		if ( ! empty( $excluded_cookies ) ) {
			return self::memoize( __FUNCTION__, [], $excluded_cookies );
		}

		return self::memoize( __FUNCTION__, [], false );
	}

	/**
	 * Don't process if some cookies are NOT present.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool|array
	 */
	public function has_mandatory_cookie() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$mandatory_cookies = $this->config->get_mandatory_cookies();

		if ( ! $mandatory_cookies ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		$missing_cookies = array_flip( explode( '|', $this->config->get_config( 'cache_mandatory_cookies' ) ) );

		if ( ! self::$cookies ) {
			return self::memoize( __FUNCTION__, [], $missing_cookies );
		}

		foreach ( array_keys( self::$cookies ) as $cookie_name ) {
			if ( preg_match( $mandatory_cookies, $cookie_name ) ) {
				unset( $missing_cookies[ $cookie_name ] );
			}
		}

		if ( empty( $missing_cookies ) ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		return self::memoize( __FUNCTION__, [], array_flip( $missing_cookies ) );
	}

	/**
	 * Don't process if the user agent is in the forbidden list.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_process_user_agent() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		if ( ! $this->config->get_server_input( 'HTTP_USER_AGENT' ) ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		$rejected_uas = $this->config->get_config( 'cache_reject_ua' );

		if ( ! $rejected_uas ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		$can = ! preg_match( '#' . $rejected_uas . '#', $this->config->get_server_input( 'HTTP_USER_AGENT' ) );

		return self::memoize( __FUNCTION__, [], $can );
	}

	/**
	 * Don't process if the user agent is in the forbidden list.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_process_mobile() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		if ( ! $this->config->get_server_input( 'HTTP_USER_AGENT' ) ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		if ( $this->config->get_config( 'cache_mobile' ) ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		$uas = '2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800';

		if ( preg_match( '#^.*(' . $uas . ').*#i', $this->config->get_server_input( 'HTTP_USER_AGENT' ) ) ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		$uas = 'w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-';

		if ( preg_match( '#^(' . $uas . ').*#i', $this->config->get_server_input( 'HTTP_USER_AGENT' ) ) ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		return self::memoize( __FUNCTION__, [], true );
	}

	/** ----------------------------------------------------------------------------------------- */
	/** SEPARATED TESTS AFTER PAGE RESPONSE ===================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Tell if the constant DONOTCACHEPAGE is set and not overridden.
	 * When defined, the page must not be cached.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function has_donotcachepage() {
		if ( ! defined( 'DONOTCACHEPAGE' ) || ! DONOTCACHEPAGE ) {
			return false;
		}

		/**
		 * At this point the constant DONOTCACHEPAGE is set to true.
		 * This filter allows to force the page caching.
		 * It prevents conflict with some plugins like Thrive Leads.
		 *
		 * @since 2.5
		 *
		 * @param bool $override_donotcachepage True will force the page to be cached.
		 */
		return ! apply_filters( 'rocket_override_donotcachepage', false );
	}

	/**
	 * Tell if we're in the WP’s 404 page.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_404() {
		return ! function_exists( 'is_404' ) || is_404();
	}

	/**
	 * Tell if we're in the WP’s search page.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_search() {
		if ( function_exists( 'is_search' ) && ! is_search() ) {
			return false;
		}

		/**
		 * At this point we’re in the WP’s search page.
		 * This filter allows to cache search results.
		 *
		 * @since 2.3.8
		 *
		 * @param bool $cache_search True will force caching search results.
		 */
		return ! apply_filters( 'rocket_cache_search', false );
	}

	/**
	 * Tell if the page content has a closing </html>.
	 *
	 * @since 3.9
	 *
	 * @param  string $buffer The buffer content.
	 * @return bool
	 */
	public function is_html( $buffer ) {
		return (bool) preg_match( '/<\s*\/\s*html\s*>/i', $buffer );
	}

	/** ----------------------------------------------------------------------------------------- */
	/** $_SERVER ================================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the IP address from which the user is viewing the current page.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 */
	public function get_ip() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$keys = [
			'HTTP_CF_CONNECTING_IP', // CF = CloudFlare.
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_X_REAL_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		];

		foreach ( $keys as $key ) {
			if ( ! $this->config->get_server_input( $key ) ) {
				continue;
			}

			$ip = explode( ',', $this->config->get_server_input( $key ) );
			$ip = end( $ip );

			if ( false !== filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return self::memoize( __FUNCTION__, [], $ip );
			}
		}

		return self::memoize( __FUNCTION__, [], '0.0.0.0' );
	}

	/**
	 * Tell if the request comes from a speed test tool.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_speed_tool() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$ips = [
			'208.70.247.157'  => '',   // GT Metrix - Vancouver 1.
			'172.255.48.130'  => '',   // GT Metrix - Vancouver 2.
			'172.255.48.131'  => '',   // GT Metrix - Vancouver 3.
			'172.255.48.132'  => '',   // GT Metrix - Vancouver 4.
			'172.255.48.133'  => '',   // GT Metrix - Vancouver 5.
			'172.255.48.134'  => '',   // GT Metrix - Vancouver 6.
			'172.255.48.135'  => '',   // GT Metrix - Vancouver 7.
			'172.255.48.136'  => '',   // GT Metrix - Vancouver 8.
			'172.255.48.137'  => '',   // GT Metrix - Vancouver 9.
			'172.255.48.138'  => '',   // GT Metrix - Vancouver 10.
			'172.255.48.139'  => '',   // GT Metrix - Vancouver 11.
			'172.255.48.140'  => '',   // GT Metrix - Vancouver 12.
			'172.255.48.141'  => '',   // GT Metrix - Vancouver 13.
			'172.255.48.142'  => '',   // GT Metrix - Vancouver 14.
			'172.255.48.143'  => '',   // GT Metrix - Vancouver 15.
			'172.255.48.144'  => '',   // GT Metrix - Vancouver 16.
			'172.255.48.145'  => '',   // GT Metrix - Vancouver 17.
			'172.255.48.146'  => '',   // GT Metrix - Vancouver 18.
			'172.255.48.147'  => '',   // GT Metrix - Vancouver 19.
			'52.229.122.240'  => '',   // GT Metrix - Quebec City.
			'104.214.72.101'  => '',   // GT Metrix - San Antonio, TX 1.
			'13.66.7.11'      => '',   // GT Metrix - San Antonio, TX 2.
			'13.85.24.83'     => '',   // GT Metrix - San Antonio, TX 3.
			'13.85.24.90'     => '',   // GT Metrix - San Antonio, TX 4.
			'13.85.82.26'     => '',   // GT Metrix - San Antonio, TX 5.
			'40.74.242.253'   => '',   // GT Metrix - San Antonio, TX 6.
			'40.74.243.13'    => '',   // GT Metrix - San Antonio, TX 7.
			'40.74.243.176'   => '',   // GT Metrix - San Antonio, TX 8.
			'104.214.48.247'  => '',   // GT Metrix - San Antonio, TX 9.
			'157.55.189.189'  => '',   // GT Metrix - San Antonio, TX 10.
			'104.214.110.135' => '',   // GT Metrix - San Antonio, TX 11.
			'70.37.83.240'    => '',   // GT Metrix - San Antonio, TX 12.
			'65.52.36.250'    => '',   // GT Metrix - San Antonio, TX 13.
			'13.78.216.56'    => '',   // GT Metrix - Cheyenne, WY.
			'52.162.212.163'  => '',   // GT Metrix - Chicago, IL.
			'23.96.34.105'    => '',   // GT Metrix - Danville, VA.
			'65.52.113.236'   => '',   // GT Metrix - San Francisco, CA.
			'172.255.61.34'   => '',   // GT Metrix - London 1.
			'172.255.61.35'   => '',   // GT Metrix - London 2.
			'172.255.61.36'   => '',   // GT Metrix - London 3.
			'172.255.61.37'   => '',   // GT Metrix - London 4.
			'172.255.61.38'   => '',   // GT Metrix - London 5.
			'172.255.61.39'   => '',   // GT Metrix - London 6.
			'172.255.61.40'   => '',   // GT Metrix - London 7.
			'52.237.235.185'  => '',   // GT Metrix - Sydney 1.
			'52.237.250.73'   => '',   // GT Metrix - Sydney 2.
			'52.237.236.145'  => '',   // GT Metrix - Sydney 3.
			'104.41.2.19'     => '',   // GT Metrix - São Paulo 1.
			'191.235.98.164'  => '',   // GT Metrix - São Paulo 2.
			'191.235.99.221'  => '',   // GT Metrix - São Paulo 3.
			'191.232.194.51'  => '',   // GT Metrix - São Paulo 4.
			'104.211.143.8'   => '',   // GT Metrix - Mumbai 1.
			'104.211.165.53'  => '',   // GT Metrix - Mumbai 2.
			'52.172.14.87'    => '',   // GT Metrix - Chennai.
			'40.83.89.214'    => '',   // GT Metrix - Hong Kong 1.
			'52.175.57.81'    => '',   // GT Metrix - Hong Kong 2.
			'20.188.63.151'   => '',   // GT Metrix - Paris.
			'20.52.36.49'     => '',   // GT Metrix - Frankfurt.
			'52.246.165.153'  => '',   // GT Metrix - Tokyo.
			'51.144.102.233'  => '',   // GT Metrix - Amsterdam.
			'13.76.97.224'    => '',   // GT Metrix - Singapore.
			'102.133.169.66'  => '',   // GT Metrix - Johannesburg.
			'52.231.199.170'  => '',   // GT Metrix - Busan.
			'13.53.162.7'     => '',   // GT Metrix - Stockholm.
			'40.123.218.94'   => '',   // GT Metrix - Dubai.
		];

		if ( isset( $ips[ $this->get_ip() ] ) ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		if ( ! $this->config->get_server_input( 'HTTP_USER_AGENT' ) ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		$user_agent = preg_match( '#PingdomPageSpeed|DareBoost|Google|PTST|Chrome-Lighthouse|WP Rocket#i', $this->config->get_server_input( 'HTTP_USER_AGENT' ) );

		return self::memoize( __FUNCTION__, [], (bool) $user_agent );
	}

	/**
	 * Determines if SSL is used.
	 * This is basically a copy of the WP function, where $_SERVER is not used directly.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return bool True if SSL, otherwise false.
	 */
	public function is_ssl() {
		if ( null !== $this->config->get_server_input( 'HTTPS' ) ) {
			if ( 'on' === strtolower( $this->config->get_server_input( 'HTTPS' ) ) ) {
				return true;
			}

			if ( '1' === (string) $this->config->get_server_input( 'HTTPS' ) ) {
				return true;
			}
		} elseif ( '443' === (string) $this->config->get_server_input( 'SERVER_PORT' ) ) {
			return true;
		}
		return false;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** REQUEST URI AND METHOD ================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the request URI.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_raw_request_uri() {
		if ( '' === $this->config->get_server_input( 'REQUEST_URI', '' ) ) {
			return '';
		}

		return '/' . ltrim( $this->config->get_server_input( 'REQUEST_URI' ), '/' );
	}

	/**
	 * Get the request URI without the query strings.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_request_uri_base() {
		$request_uri = $this->get_raw_request_uri();

		if ( ! $request_uri ) {
			return '';
		}

		$request_uri = explode( '?', $request_uri );

		return reset( $request_uri );
	}

	/**
	 * Get the request URI. The query string is sorted and some parameters are removed.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_clean_request_uri() {
		$request_uri = $this->get_request_uri_base();
		$request_uri = $this->remove_dot_segments( $request_uri );

		if ( ! $request_uri ) {
			return '';
		}

		$query_string = $this->get_query_string();

		if ( ! $query_string ) {
			return $request_uri;
		}

		return $request_uri . '?' . $query_string;
	}

	/**
	 * Get the request method.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_request_method() {
		if ( '' === $this->config->get_server_input( 'REQUEST_METHOD', '' ) ) {
			return '';
		}

		return strtoupper( $this->config->get_server_input( 'REQUEST_METHOD' ) );
	}

	/**
	 * Remove dot segments from a path
	 *
	 * @param string $input Path to process.
	 *
	 * @return string
	 */
	private function remove_dot_segments( string $input ) {
		$output = '';

		while ( strpos( $input, './' ) !== false || strpos( $input, '/.' ) !== false || '.' === $input || '..' === $input ) {
			/**
			 * A: If the input buffer begins with a prefix of "../" or "./",
			 * then remove that prefix from the input buffer; otherwise,
			 */
			if ( strpos( $input, '../' ) === 0 ) {
				$input = substr( $input, 3 );
			}
			elseif ( strpos( $input, './' ) === 0 ) {
				$input = substr( $input, 2 );
			}
			/**
			 * B: if the input buffer begins with a prefix of "/./" or "/.",
			 * where "." is a complete path segment, then replace that prefix
			 * with "/" in the input buffer; otherwise,
			 */
			elseif ( strpos( $input, '/./' ) === 0 ) {
				$input = substr( $input, 2 );
			}
			elseif ( '/.' === $input ) {
				$input = '/';
			}
			/**
			 * C: if the input buffer begins with a prefix of "/../" or "/..",
			 * where ".." is a complete path segment, then replace that prefix
			 * with "/" in the input buffer and remove the last segment and its
			 * preceding "/" (if any) from the output buffer; otherwise,
			 */
			elseif ( strpos( $input, '/../' ) === 0 ) {
				$input  = substr( $input, 3 );
				$output = substr_replace( $output, '', strrpos( $output, '/' ) );
			}
			elseif ( '/..' === $input ) {
				$input  = '/';
				$output = substr_replace( $output, '', strrpos( $output, '/' ) );
			}
			/**
			 * D: if the input buffer consists only of "." or "..", then remove
			 * that from the input buffer; otherwise,
			 */
			elseif ( '.' === $input || '..' === $input ) {
				$input = '';
			}
			/**
			 * E: move the first path segment in the input buffer to the end of
			 * the output buffer, including the initial "/" character (if any)
			 * and any subsequent characters up to, but not including, the next
			 * "/" character or the end of the input buffer
			 */
			elseif ( strpos( $input, '/', 1 ) !== false ) {
				$pos     = strpos( $input, '/', 1 );
				$output .= substr( $input, 0, $pos );
				$input   = substr_replace( $input, '', 0, $pos );
			}
			else {
				$output .= $input;
				$input   = '';
			}
		}
		return $output . $input;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** QUERY STRING ============================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the query string as an array. Parameters are sorted and some are removed.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public function get_query_params() {
		if ( ! self::$get ) {
			return [];
		}

		// Remove some parameters.
		$params = array_diff_key(
			self::$get,
			$this->config->get_config( 'cache_ignored_parameters' )
		);

		if ( $params ) {
			ksort( $params );
		}

		return $params;
	}

	/**
	 * Get the query string with sorted parameters, and some other removed.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_query_string() {
		return http_build_query( $this->get_query_params() );
	}

	/**
	 * Get the original query string
	 *
	 * @since  3.11.4
	 *
	 * @return string
	 */
	public function get_original_query_string() {
		return http_build_query( $this->get_get() );
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PROPERTY GETTERS ======================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the `cookies` property.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public function get_cookies() {
		return self::$cookies;
	}

	/**
	 * Get the `post` property.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public function get_post() {
		return self::$post;
	}

	/**
	 * Get the `get` property.
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public function get_get() {
		return self::$get;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** ERRORS ================================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Set an "error".
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @param string $message A message.
	 * @param array  $data    Related data.
	 */
	protected function set_error( $message, $data = [] ) {
		$this->last_error = [
			'message' => $message,
			'data'    => (array) $data,
		];
	}

	/**
	 * Get the last "error".
	 *
	 * @since  3.3
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public function get_last_error() {
		return array_merge(
			[
				'message' => '',
				'data'    => [],
			],
			(array) $this->last_error
		);
	}
}
