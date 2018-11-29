<?php
namespace WP_Rocket\Buffer;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Handle the tests for page cache and optimizations.
 *
 * @since  3.3
 * @author Grégory Viguier
 */
class Tests {
	use \WP_Rocket\Traits\Memoize;

	/**
	 * Path to the directory containing the config files.
	 *
	 * @var    string
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	private static $config_dir_path;

	/**
	 * Values of $_COOKIE to use for some tests.
	 *
	 * @var    array
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	private static $cookies;

	/**
	 * Values of $_SERVER to use for some tests.
	 *
	 * @var    array
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	private static $server;

	/**
	 * Values of $_POST to use for some tests.
	 *
	 * @var    array
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	private static $post;

	/**
	 * Values of $_GET to use for some tests.
	 *
	 * @var    array
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	private static $get;

	/**
	 * Array of complementary tests to perform.
	 *
	 * @var    array Tests are listed as array keys.
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	private $tests = [];

	/**
	 * Information about the last "error".
	 * Here an "error" is a test failure.
	 *
	 * @var    array {
	 *     @type string $message A message.
	 *     @type array  $data    Related data.
	 * }
	 * @since  3.3
	 * @access private
	 * @author Grégory Viguier
	 */
	private $last_error = [];

	/**
	 * Constructor.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param array $args {
	 *     An array of arguments.
	 *
	 *     @type string $config_dir_path Path to the directory containing the config files.
	 *     @type array  $cookies         Values of $_COOKIE to use for the tests. Default is $_COOKIE.
	 *     @type array  $server          Values of $_SERVER to use for the tests. Default is $_SERVER.
	 *     @type array  $post            Values of $_POST to use for the tests. Default is $_POST
	 *     @type array  $get             Values of $_GET to use for the tests. Default is $_GET.
	 *     @type array  $tests           List of complementary tests to perform. Optional.
	 * }
	 */
	public function __construct( array $args ) {
		if ( ! empty( $args['tests'] ) ) {
			$this->set_tests( (array) $args['tests'] );
		}

		if ( isset( self::$config_dir_path ) ) {
			// Make sure to keep the same values all along.
			return;
		}

		// Provide fallback values.
		if ( ! isset( $args['cookies'] ) && ! empty( $_COOKIE ) && is_array( $_COOKIE ) ) {
			$args['cookies'] = $_COOKIE;
		}
		if ( ! isset( $args['server'] ) && ! empty( $_SERVER ) && is_array( $_SERVER ) ) {
			$args['server'] = $_SERVER;
		}
		if ( ! isset( $args['post'] ) && ! empty( $_POST ) && is_array( $_POST ) ) { // WPCS: CSRF ok.
			$args['post'] = $_POST; // WPCS: CSRF ok.
		}
		if ( ! isset( $args['get'] ) && ! empty( $_GET ) && is_array( $_GET ) ) { // WPCS: CSRF ok.
			$args['get'] = $_GET; // WPCS: CSRF ok.
		}

		// Set properties.
		self::$config_dir_path = rtrim( $args['config_dir_path'], '/' ) . '/';
		self::$cookies         = ! empty( $args['cookies'] ) && is_array( $args['cookies'] ) ? $args['cookies'] : [];
		self::$server          = ! empty( $args['server'] ) && is_array( $args['server'] ) ? $args['server'] : [];
		self::$post            = ! empty( $args['post'] ) && is_array( $args['post'] ) ? $args['post'] : [];
		self::$get             = ! empty( $args['get'] ) && is_array( $args['get'] ) ? $args['get'] : [];

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
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_init_process() {
		$this->last_error = [];

		// Don't process robots.txt && .htaccess files (it has happened sometimes with weird server configuration).
		if ( $this->is_rejected_file() ) {
			$this->set_error( 'File not processed.' );
			return false;
		}

		// Don't process disallowed file extensions (like php, xml, xsl).
		if ( $this->is_rejected_extension() ) {
			$this->set_error( 'File extension not processed.' );
			return false;
		}

		// Don't cache if in admin or ajax.
		if ( $this->is_admin() ) {
			$this->set_error( 'Admin and ajax not processed.' );
			return false;
		}

		// Don't process the customizer preview.
		if ( $this->is_customizer_preview() ) {
			$this->set_error( 'Customizer preview not processed.' );
			return false;
		}

		// Don't process without GET method.
		if ( ! $this->is_get_method() ) {
			$this->set_error(
				'Request method not processed.',
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

		// Exit if no config file exists.
		if ( ! $this->has_config_file() ) {
			$this->set_error( 'No config file found.' );
			return false;
		}

		// Don’t process with query strings parameters, but the processed content is served if the visitor comes from an RSS feed, a Facebook action or Google Adsense tracking.
		if ( $this->has_test( 'query_string' ) && ! $this->can_process_query_string() ) {
			$this->set_error( 'Query strings not processed.' );
			return false;
		}

		// Don't process SSL.
		if ( $this->has_test( 'ssl' ) && ! $this->can_process_ssl() ) {
			$this->set_error( 'SSL not processed.' );
			return false;
		}

		// Don't process these pages.
		if ( $this->has_test( 'uri' ) && ! $this->can_process_uri() ) {
			$this->set_error( 'Rejected URI not processed.' );
			return false;
		}

		// Don't process page with these cookies.
		if ( $this->has_test( 'rejected_cookie' ) && $this->has_rejected_cookie() ) {
			$this->set_error(
				'Cookie not processed.',
				[
					'cookies' => self::$cookies,
				]
			);
			return false;
		}

		// Don't process page when these cookies don't exist.
		if ( $this->has_test( 'mandatory_cookie' ) && ! $this->is_speed_tool() && ! $this->has_mandatory_cookie() ) {
			$this->set_error(
				'Missing cookie: page not processed.',
				[
					'cookies' => self::$cookies,
				]
			);
			return false;
		}

		// Don't process page with these user agents.
		if ( $this->has_test( 'user_agent' ) && ! $this->can_process_user_agent() ) {
			$this->set_error(
				'User agent not processed.',
				[
					'user_agent' => $this->get_server_input( 'HTTP_USER_AGENT' ),
				]
			);
			return false;
		}

		// Don't process if mobile detection is activated.
		if ( $this->has_test( 'mobile' ) && ! $this->can_process_mobile() ) {
			$this->set_error(
				'Mobile user agent not processed.',
				[
					'user_agent' => $this->get_server_input( 'HTTP_USER_AGENT' ),
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
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $test_name Identifier of the test.
	 *                           Possible values are: 'query_string', 'ssl', 'uri', 'rejected_cookie', 'mandatory_cookie', 'user_agent', 'mobile'.
	 * @return bool
	 */
	public function has_test( $test_name = false ) {
		if ( ! $test_name ) {
			return ! empty( $this->tests );
		}

		return isset( $this->tests[ $test_name ] );
	}

	/**
	 * Set the list of tests to perform.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param array $tests An array of test names.
	 */
	public function set_tests( array $tests ) {
		$this->tests = array_flip( $tests );
	}

	/**
	 * Tell if the buffer should be processed.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $buffer The buffer content.
	 * @return bool
	 */
	public function can_process_buffer( $buffer ) {
		$this->last_error = [];

		if ( ! function_exists( 'rocket_mkdir_p' ) ) {
			// Uh?
			$this->set_error( 'WP Rocket not found.' );
			return false;
		}

		if ( strlen( $buffer ) <= 255 ) {
			// Buffer length must be > 255 (IE does not read pages under 255 c).
			$this->set_error( 'Content under 255 caracters.' );
			return false;
		}

		if ( http_response_code() !== 200 ) {
			// Only cache 200.
			$this->set_error( 'Not a 200 HTTP response.' );
			return false;
		}

		if ( $this->has_test( 'donotcachepage' ) && $this->has_donotcachepage() ) {
			// Don't process templates that use the DONOTCACHEPAGE constant.
			$this->set_error( 'DONOTCACHEPAGE defined.' );
			return false;
		}

		if ( $this->has_test( 'wp_404' ) && $this->is_404() ) {
			// Don't process WP 404 page.
			$this->set_error( 'WP 404 page not processed.' );
			return false;
		}

		if ( $this->has_test( 'search' ) && $this->is_search() ) {
			// Don't process search results.
			$this->set_error( 'Search page not processed.' );
			return false;
		}

		$this->last_error = [];

		return true;
	}

	/** ----------------------------------------------------------------------------------------- */
	/** SEPARATED TESTS ========================================================================= */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Tell if the current URI corresponds to a file that must not be processed.
	 *
	 * @since  3.3
	 * @access public
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
	 * @access public
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

		$is = $extension && isset( $extensions[ $extension ] );

		return self::memoize( __FUNCTION__, [], $is );
	}

	/**
	 * Tell if we're in the admin area (or ajax) or not.
	 * Test against ajax added in 2e3c0fa74246aa13b36835f132dfd55b90d4bf9e for whatever reason.
	 *
	 * @since  3.3
	 * @access public
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
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_customizer_preview() {
		return isset( self::$post['wp_customize'] );
	}

	/**
	 * Tell if we're in a GET request.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_get_method() {
		return 'GET' === $this->get_request_method();
	}

	/** ----------------------------------------------------------------------------------------- */
	/** SEPARATED TESTS THAT USE THE CONFIG FILE ================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Don't process with query string parameters, some parameters are allowed though.
	 *
	 * @since  3.3
	 * @access public
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
		$allowed_params = $this->get_config( 'cache_query_strings' );

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
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_process_ssl() {
		return ! $this->is_ssl() || $this->get_config( 'cache_ssl' );
	}

	/**
	 * Some URIs set in the plugin settings must not be processed.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_process_uri() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		// URIs not to cache.
		$uri_pattern = $this->get_config( 'cache_reject_uri' );

		if ( ! $uri_pattern ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		$can = ! preg_match( '#^' . $uri_pattern . '$#i', $this->get_clean_request_uri() );

		return self::memoize( __FUNCTION__, [], $can );
	}

	/**
	 * Don't process if some cookies are present.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function has_rejected_cookie() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		if ( ! self::$cookies ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		$rejected_cookies = $this->get_rejected_cookies();

		if ( ! $rejected_cookies ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		foreach ( array_keys( self::$cookies ) as $cookie_name ) {
			if ( preg_match( $rejected_cookies, $cookie_name ) ) {
				return self::memoize( __FUNCTION__, [], true );
			}
		}

		return self::memoize( __FUNCTION__, [], false );
	}

	/**
	 * Don't process if some cookies are NOT present.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function has_mandatory_cookie() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$mandatory_cookies = $this->get_mandatory_cookies();

		if ( ! $mandatory_cookies ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		if ( ! self::$cookies ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		foreach ( array_keys( self::$cookies ) as $cookie_name ) {
			if ( preg_match( $mandatory_cookies, $cookie_name ) ) {
				return self::memoize( __FUNCTION__, [], true );
			}
		}

		return self::memoize( __FUNCTION__, [], false );
	}

	/**
	 * Don't process if the user agent is in the forbidden list.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_process_user_agent() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		if ( ! $this->get_server_input( 'HTTP_USER_AGENT' ) ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		$rejected_uas = $this->get_config( 'cache_reject_ua' );

		if ( ! $rejected_uas ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		$can = ! preg_match( '#' . $rejected_uas . '#', $this->get_server_input( 'HTTP_USER_AGENT' ) );

		return self::memoize( __FUNCTION__, [], $can );
	}

	/**
	 * Don't process if the user agent is in the forbidden list.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function can_process_mobile() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		if ( ! $this->get_server_input( 'HTTP_USER_AGENT' ) ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		if ( $this->get_config( 'cache_mobile' ) ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		$uas = '2.0\ MMP|240x320|400X240|AvantGo|BlackBerry|Blazer|Cellphone|Danger|DoCoMo|Elaine/3.0|EudoraWeb|Googlebot-Mobile|hiptop|IEMobile|KYOCERA/WX310K|LG/U990|MIDP-2.|MMEF20|MOT-V|NetFront|Newt|Nintendo\ Wii|Nitro|Nokia|Opera\ Mini|Palm|PlayStation\ Portable|portalmmm|Proxinet|ProxiNet|SHARP-TQ-GX10|SHG-i900|Small|SonyEricsson|Symbian\ OS|SymbianOS|TS21i-10|UP.Browser|UP.Link|webOS|Windows\ CE|WinWAP|YahooSeeker/M1A1-R2D2|iPhone|iPod|Android|BlackBerry9530|LG-TU915\ Obigo|LGE\ VX|webOS|Nokia5800';

		if ( preg_match( '#^.*(' . $uas . ').*#i', $this->get_server_input( 'HTTP_USER_AGENT' ) ) ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		$uas = 'w3c\ |w3c-|acs-|alav|alca|amoi|audi|avan|benq|bird|blac|blaz|brew|cell|cldc|cmd-|dang|doco|eric|hipt|htc_|inno|ipaq|ipod|jigs|kddi|keji|leno|lg-c|lg-d|lg-g|lge-|lg/u|maui|maxo|midp|mits|mmef|mobi|mot-|moto|mwbp|nec-|newt|noki|palm|pana|pant|phil|play|port|prox|qwap|sage|sams|sany|sch-|sec-|send|seri|sgh-|shar|sie-|siem|smal|smar|sony|sph-|symb|t-mo|teli|tim-|tosh|tsm-|upg1|upsi|vk-v|voda|wap-|wapa|wapi|wapp|wapr|webc|winw|winw|xda\ |xda-';

		if ( preg_match( '#^(' . $uas . ').*#i', $this->get_server_input( 'HTTP_USER_AGENT' ) ) ) {
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
	 * @access public
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
	 * @access public
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
	 * @access public
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

	/** ----------------------------------------------------------------------------------------- */
	/** CONFIGURATION =========================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get a specific config/option value.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $config_name Name of a specific config/option.
	 * @return mixed
	 */
	public function get_config( $config_name ) {
		$config = $this->get_configs();
		return isset( $config[ $config_name ] ) ? $config[ $config_name ] : null;
	}

	/**
	 * Get the whole current configuration.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array|bool An array containing the configuration. False on failure.
	 */
	public function get_configs() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$config_file_path = $this->get_config_file_path();

		if ( ! $config_file_path ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		include $config_file_path;

		$config = [
			'cookie_hash'               => '',
			'logged_in_cookie'          => '',
			'common_cache_logged_users' => 0,
			'cache_mobile_files_tablet' => 'desktop',
			'cache_ssl'                 => 0,
			'cache_mobile'              => 0,
			'do_caching_mobile_files'   => 0,
			'secret_cache_key'          => '',
			'cache_reject_uri'          => '',
			'cache_query_strings'       => [],
			'cache_reject_cookies'      => '',
			'cache_reject_ua'           => '',
			'cache_mandatory_cookies'   => '',
			'cache_dynamic_cookies'     => [],
			'url_no_dots'               => 0,
		];

		foreach ( $config as $entry_name => $entry_value ) {
			$var_name = 'rocket_' . $entry_name;

			if ( isset( $$var_name ) ) {
				$config[ $entry_name ] = $$var_name;
			}
		}

		return self::memoize( __FUNCTION__, [], $config );
	}

	/**
	 * Get the path to an existing config file.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string|bool The path to the file. False if no file is found.
	 */
	protected function get_config_file_path() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$config_dir_real_path = realpath( self::$config_dir_path ) . DIRECTORY_SEPARATOR;

		$host = $this->get_host();

		if ( realpath( self::$config_dir_path . $host . '.php' ) && 0 === stripos( realpath( self::$config_dir_path . $host . '.php' ), $config_dir_real_path ) ) {
			$config_file_path = self::$config_dir_path . $host . '.php';
			return self::memoize( __FUNCTION__, [], $config_file_path );
		}

		$path = str_replace( '\\', '/', strtok( $this->get_server_input( 'REQUEST_URI', '' ), '?' ) );
		$path = preg_replace( '|(?<=.)/+|', '/', $path );
		$path = explode( '%2F', preg_replace( '/^(?:%2F)*(.*?)(?:%2F)*$/', '$1', rawurlencode( $path ) ) );

		foreach ( $path as $p ) {
			if ( realpath( self::$config_dir_path . $host . '.' . $p . '.php' ) && 0 === stripos( realpath( self::$config_dir_path . $host . '.' . $p . '.php' ), $config_dir_real_path ) ) {
				$config_file_path = self::$config_dir_path . $host . '.' . $p . '.php';
				return self::memoize( __FUNCTION__, [], $config_file_path );
			}

			if ( realpath( self::$config_dir_path . $host . '.' . $dir . $p . '.php' ) && 0 === stripos( realpath( self::$config_dir_path . $host . '.' . $dir . $p . '.php' ), $config_dir_real_path ) ) {
				$config_file_path = self::$config_dir_path . $host . '.' . $dir . $p . '.php';
				return self::memoize( __FUNCTION__, [], $config_file_path );
			}

			$dir .= $p . '.';
		}

		return self::memoize( __FUNCTION__, [], false );
	}

	/**
	 * Tell if a config file has been found.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	protected function has_config_file() {
		return (bool) $this->get_config_file_path();
	}

	/** ----------------------------------------------------------------------------------------- */
	/** SPECIFIC CONFIG GETTERS ================================================================= */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get rejected cookies as a regex pattern.
	 * `#` is used as pattern delimiter.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	protected function get_rejected_cookies() {
		$rejected_cookies = $this->get_config( 'cache_reject_cookies' );

		if ( '' === $rejected_cookies ) {
			return $rejected_cookies;
		}

		return '#' . $rejected_cookies . '#';
	}

	/**
	 * Get mandatory cookies as a regex pattern.
	 * `#` is used as pattern delimiter.
	 *
	 * @since  3.3
	 * @access protected
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	protected function get_mandatory_cookies() {
		$mandatory_cookies = $this->get_config( 'cache_mandatory_cookies' );

		if ( '' === $mandatory_cookies ) {
			return $mandatory_cookies;
		}

		return '#' . $mandatory_cookies . '#';
	}

	/** ----------------------------------------------------------------------------------------- */
	/** $_SERVER ================================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get a $_SERVER entry.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @param  string $entry_name Name of the entry.
	 * @param  mixed  $default    Value to return if the entry is not set.
	 * @return mixed
	 */
	public function get_server_input( $entry_name, $default = null ) {
		if ( ! isset( self::$server[ $entry_name ] ) ) {
			return $default;
		}

		return self::$server[ $entry_name ];
	}

	/**
	 * Get the IP address from which the user is viewing the current page.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 */
	public function get_ip() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$keys = array(
			'HTTP_CF_CONNECTING_IP', // CF = CloudFlare.
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_X_REAL_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $keys as $key ) {
			if ( ! $this->get_server_input( $key ) ) {
				continue;
			}

			$ip = explode( ',', $this->get_server_input( $key ) );
			$ip = end( $ip );

			if ( false !== filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return self::memoize( __FUNCTION__, [], $ip );
			}
		}

		return self::memoize( __FUNCTION__, [], '0.0.0.0' );
	}

	/**
	 * Get the host, to use for config and cache file path.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_host() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$host = $this->get_server_input( 'HTTP_HOST', (string) time() );
		$host = preg_replace( '/:\d+$/', '', $host );
		$host = trim( strtolower( $host ), '.' );

		return self::memoize( __FUNCTION__, [], rawurlencode( $host ) );
	}

	/**
	 * Tell if the request comes from a speed test tool.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool
	 */
	public function is_speed_tool() {
		if ( self::is_memoized( __FUNCTION__ ) ) {
			return self::get_memoized( __FUNCTION__ );
		}

		$ips = [
			'208.70.247.157' => '', // GT Metrix - Vancouver 1.
			'204.187.14.70'  => '', // GT Metrix - Vancouver 2.
			'204.187.14.71'  => '', // GT Metrix - Vancouver 3.
			'204.187.14.72'  => '', // GT Metrix - Vancouver 4.
			'204.187.14.73'  => '', // GT Metrix - Vancouver 5.
			'204.187.14.74'  => '', // GT Metrix - Vancouver 6.
			'204.187.14.75'  => '', // GT Metrix - Vancouver 7.
			'204.187.14.76'  => '', // GT Metrix - Vancouver 8.
			'204.187.14.77'  => '', // GT Metrix - Vancouver 9.
			'204.187.14.78'  => '', // GT Metrix - Vancouver 10.
			'199.10.31.194'  => '', // GT Metrix - Vancouver 11.
			'13.85.80.124'   => '', // GT Metrix - Dallas 1.
			'13.84.146.132'  => '', // GT Metrix - Dallas 2.
			'13.84.146.226'  => '', // GT Metrix - Dallas 3.
			'40.74.254.217'  => '', // GT Metrix - Dallas 4.
			'13.84.43.227'   => '', // GT Metrix - Dallas 5.
			'172.255.61.34'  => '', // GT Metrix - London 1.
			'172.255.61.35'  => '', // GT Metrix - London 2.
			'172.255.61.36'  => '', // GT Metrix - London 3.
			'172.255.61.37'  => '', // GT Metrix - London 4.
			'172.255.61.38'  => '', // GT Metrix - London 5.
			'172.255.61.39'  => '', // GT Metrix - London 6.
			'172.255.61.40'  => '', // GT Metrix - London 7.
			'13.70.66.20'    => '', // GT Metrix - Sydney.
			'191.235.85.154' => '', // GT Metrix - São Paulo 1.
			'191.235.86.0'   => '', // GT Metrix - São Paulo 2.
			'52.66.75.147'   => '', // GT Metrix - Mumbai.
			'52.175.28.116'  => '', // GT Metrix - Hong Kong.
		];

		if ( isset( $ips[ $this->get_ip() ] ) ) {
			return self::memoize( __FUNCTION__, [], true );
		}

		if ( ! $this->get_server_input( 'HTTP_USER_AGENT' ) ) {
			return self::memoize( __FUNCTION__, [], false );
		}

		$is = preg_match( '#PingdomPageSpeed|DareBoost|Google|PTST|WP Rocket#i', $this->get_server_input( 'HTTP_USER_AGENT' ) );

		return self::memoize( __FUNCTION__, [], $is );
	}

	/**
	 * Determines if SSL is used.
	 * This is basically a copy of the WP function, where $_SERVER is not used directly.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return bool True if SSL, otherwise false.
	 */
	public function is_ssl() {
		if ( null !== $this->get_server_input( 'HTTPS' ) ) {
			if ( 'on' === strtolower( $this->get_server_input( 'HTTPS' ) ) ) {
				return true;
			}

			if ( '1' === (string) $this->get_server_input( 'HTTPS' ) ) {
				return true;
			}
		} elseif ( '443' === (string) $this->get_server_input( 'SERVER_PORT' ) ) {
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
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_raw_request_uri() {
		if ( '' === $this->get_server_input( 'REQUEST_URI', '' ) ) {
			return '';
		}

		return '/' . ltrim( $this->get_server_input( 'REQUEST_URI' ), '/' );
	}

	/**
	 * Get the request URI without the query strings.
	 *
	 * @since  3.3
	 * @access public
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
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_clean_request_uri() {
		$request_uri = $this->get_request_uri_base();

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
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_request_method() {
		if ( '' === $this->get_server_input( 'REQUEST_METHOD', '' ) ) {
			return '';
		}

		return strtoupper( $this->get_server_input( 'REQUEST_METHOD' ) );
	}

	/** ----------------------------------------------------------------------------------------- */
	/** QUERY STRING ============================================================================ */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the query string as an array. Parameters are sorted and some are removed.
	 *
	 * @since  3.3
	 * @access public
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
			[
				'utm_source'      => 1,
				'utm_medium'      => 1,
				'utm_campaign'    => 1,
				'utm_expid'       => 1,
				'fb_action_ids'   => 1,
				'fb_action_types' => 1,
				'fb_source'       => 1,
				'fbclid'          => 1,
				'gclid'           => 1,
				'age-verified'    => 1,
				'ao_noptimize'    => 1,
				'usqp'            => 1,
			]
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
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return string
	 */
	public function get_query_string() {
		return http_build_query( $this->get_query_params() );
	}

	/** ----------------------------------------------------------------------------------------- */
	/** PROPERTY GETTERS ======================================================================== */
	/** ----------------------------------------------------------------------------------------- */

	/**
	 * Get the `cookies` property.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public function get_cookies() {
		return self::$cookies;
	}

	/**
	 * Get the `server` property.
	 *
	 * @since  3.3
	 * @access public
	 * @author Grégory Viguier
	 *
	 * @return array
	 */
	public function get_server() {
		return self::$server;
	}

	/**
	 * Get the `post` property.
	 *
	 * @since  3.3
	 * @access public
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
	 * @access public
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
	 * @access protected
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
	 * @access public
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
