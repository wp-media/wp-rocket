<?php
/**
 * Tool allowing 3rd party WordPress plugins to handle partnership with Imagify.
 *
 * @package wp-media/wp-imagify-partner
 */

defined( 'ABSPATH' ) || die( 'Cheatin\' uh?' );

if ( ! class_exists( 'Imagify_Partner' ) ) :

	/**
	 * Class allowing to download, install, and activate Imagify plugin.
	 *
	 * @author Grégory Viguier
	 */
	class Imagify_Partner {

		/**
		 * Class version.
		 *
		 * @var string
		 */
		const VERSION = '1.0';

		/**
		 * Name of the option that stores the partner identifier.
		 *
		 * @var string
		 */
		const OPTION_NAME = 'imagifyp_id';

		/**
		 * Name of the transient that stores the error messages.
		 *
		 * @var string
		 */
		const ERROR_TRANSIENT_NAME = 'imagifyp_error';

		/**
		 * Name of the URL argument used on success.
		 *
		 * @var string
		 */
		const SUCCESS_ARG = 'imp-success';

		/**
		 * Name of the URL argument used to display an error notice.
		 *
		 * @var string
		 */
		const ERROR_ARG = 'imp-error';

		/**
		 * ID of the nonce used to install Imagify.
		 *
		 * @var string
		 */
		const NONCE_NAME = 'install_imagify_from_partner';

		/**
		 * Message used as fallback in get_message().
		 *
		 * @var string
		 */
		const FALLBACK_MESSAGE = 'Unknown message';

		/**
		 * Partner identifier.
		 *
		 * @var    string
		 * @access protected
		 */
		protected $partner;


		/** ----------------------------------------------------------------------------------------- */
		/** INSTANCE, INIT ========================================================================== */
		/** ----------------------------------------------------------------------------------------- */

		/**
		 * Class constructor: sanitize and set the partner identifier.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @param string $partner Partner identifier.
		 */
		public function __construct( $partner ) {
			$this->partner = self::sanitize_partner( $partner );
		}

		/**
		 * Class init.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 */
		public function init() {
			if ( ! $this->get_partner() ) {
				return;
			}

			if ( ! is_admin() ) {
				return;
			}

			if ( ! self::has_imagify_api_key() ) {
				add_action( 'wp_ajax_' . $this->get_post_action(),    array( $this, 'post_callback' ) );
				add_action( 'admin_post_' . $this->get_post_action(), array( $this, 'post_callback' ) );
			}

			if ( self::is_success() || self::is_error() ) {
				add_action( 'all_admin_notices',    array( __CLASS__, 'error_notice' ) );
				add_filter( 'removable_query_args', array( __CLASS__, 'add_query_args' ) );
			}
		}


		/** ----------------------------------------------------------------------------------------- */
		/** MAIN PUBLIC TOOLS ======================================================================= */
		/** ----------------------------------------------------------------------------------------- */

		/**
		 * Tell if Imagify's API key is set.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return bool
		 */
		public static function has_imagify_api_key() {
			static $has;

			if ( isset( $has ) ) {
				return $has;
			}

			if ( function_exists( 'get_imagify_option' ) ) {
				// Imagify is already installed and activated.
				$has = (bool) get_imagify_option( 'api_key' );
				return $has;
			}

			if ( defined( 'IMAGIFY_API_KEY' ) && IMAGIFY_API_KEY ) {
				// It's defined in wp-config.php.
				$has = true;
				return $has;
			}

			if ( ! is_multisite() ) {
				// Monosite: grab the value from the options table.
				$options = get_option( 'imagify_settings' );
				$has     = ! empty( $options['api_key'] );
				return $has;
			}

			$options = get_site_option( 'imagify_settings' );

			if ( ! empty( $options['api_key'] ) ) {
				// Multisite: Imagify was activated in the network.
				$has = true;
				return $has;
			}

			// Multisite: Imagify was activated for this site.
			$options = get_option( 'imagify_settings' );
			$has     = ! empty( $options['api_key'] );
			return $has;
		}

		/**
		 * Tell if Imagify is activated.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return bool
		 */
		public static function is_imagify_activated() {
			return defined( 'IMAGIFY_VERSION' );
		}

		/**
		 * Tell if Imagify is installed.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return bool
		 */
		public static function is_imagify_installed() {
			if ( self::is_imagify_activated() ) {
				return true;
			}

			return file_exists( self::get_imagify_path() );
		}

		/**
		 * Tell if Imagify has been successfully installed.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return bool
		 */
		public static function is_success() {
			return ! empty( $_GET[ self::SUCCESS_ARG ] ); // WPCS: CSRF ok.
		}

		/**
		 * Tell if Imagify install failed.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return bool
		 */
		public static function is_error() {
			return ! empty( $_GET[ self::ERROR_ARG ] ); // WPCS: CSRF ok.
		}

		/**
		 * Get the URL to install and activate Imagify.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return string The URL.
		 */
		public function get_post_install_url() {
			if ( ! $this->get_partner() || ! self::current_user_can() ) {
				return '';
			}

			$install_url = admin_url( 'admin-post.php' );
			$args        = array(
				'action'           => $this->get_post_action(),
				'_wpnonce'         => wp_create_nonce( self::NONCE_NAME ),
				// To make sure we have a referrer.
				'_wp_http_referer' => rawurlencode( self::get_current_url() ),
			);

			return add_query_arg( $args, $install_url );
		}

		/**
		 * Get the partner identifier.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return string Partner identifier.
		 */
		public function get_partner() {
			return $this->partner;
		}


		/** ----------------------------------------------------------------------------------------- */
		/** HOOKS =================================================================================== */
		/** ----------------------------------------------------------------------------------------- */

		/**
		 * Post callback to install and activate Imagify.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 */
		public function post_callback() {
			if ( ! check_ajax_referer( self::NONCE_NAME, '_wpnonce', false ) ) {
				$this->error_die();
			}

			if ( ! self::current_user_can() ) {
				$this->error_die( 'cant_install' );
			}

			/**
			 * Store the partner ID before doing anything.
			 * If something goes wrong during the plugin installation, the partner ID will still be saved.
			 */
			self::store_partner( $this->get_partner() );

			// Install Imagify.
			$result = $this->install_imagify();

			if ( is_wp_error( $result ) ) {
				// Install failed.
				if ( self::doing_ajax() ) {
					$this->send_json_error( $result );
				}
				// Redirect to the plugins search page.
				$this->error_redirect( $result );
			}

			// Activate Imagify.
			$result = $this->activate_imagify();

			if ( is_wp_error( $result ) ) {
				// Activation failed.
				if ( self::doing_ajax() ) {
					$this->send_json_error( $result );
				}
				// Redirect to the plugins search page.
				$this->error_redirect( $result );
			}

			if ( self::doing_ajax() ) {
				$this->send_json_success();
			}
			// Redirect to the partner's page.
			$this->success_redirect();
		}

		/**
		 * Maybe print an error notice on the plugins install page.
		 * We add the query argument we use to display an error message.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 */
		public static function error_notice() {
			if ( ! self::is_error() ) {
				// No URL argument.
				return;
			}

			$screen = get_current_screen();

			if ( ! $screen || 'plugin-install' !== $screen->id ) {
				// Not the good page.
				return;
			}

			$partner = self::get_stored_partner();

			if ( ! $partner ) {
				// No partner stored in the database.
				return;
			}

			$errors = get_transient( self::ERROR_TRANSIENT_NAME );

			if ( ! $errors ) {
				// No error messages.
				return;
			}

			if ( ! is_wp_error( $errors ) ) {
				// Invalid value.
				delete_transient( self::ERROR_TRANSIENT_NAME );
				return;
			}

			$errors = $errors->get_error_messages();

			if ( $errors ) {
				foreach ( $errors as $i => $error ) {
					if ( self::FALLBACK_MESSAGE === $error ) {
						unset( $errors[ $i ] );
					}
				}
			}

			if ( ! $errors ) {
				// Add a generic message.
				$instance = new self( $partner );
				$errors[] = $instance->get_message( 'process_failed' );
			}

			echo '<div class="error notice is-dismissible"><p>' . implode( '<br/>', $errors ) . '</p></div>';
		}

		/**
		 * Filter the list of query variables to remove from admin area URLs.
		 * We add the query arguments we use on success or error.
		 *
		 * @since  1.0
		 * @access public
		 * @see    wp_removable_query_args()
		 * @author Grégory Viguier
		 *
		 * @param  array $removable_query_args An array of query variables to remove from a URL.
		 * @return array
		 */
		public static function add_query_args( $removable_query_args ) {
			$removable_query_args[] = self::SUCCESS_ARG;
			$removable_query_args[] = self::ERROR_ARG;
			return $removable_query_args;
		}


		/** ----------------------------------------------------------------------------------------- */
		/** INFOS, INSTALL, ACTIVATE ================================================================ */
		/** ----------------------------------------------------------------------------------------- */

		/**
		 * Get Imagify infos from the repository.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 *
		 * @return object The plugin infos on success. A WP_Error object on failure.
		 */
		protected function get_imagify_infos() {
			static $infos;

			if ( isset( $infos ) ) {
				return $infos;
			}

			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			// Get Plugin Infos.
			$infos = plugins_api( 'plugin_information', array(
				'slug'   => 'imagify',
				'fields' => array(
					'short_description' => false,
					'sections'          => false,
					'rating'            => false,
					'ratings'           => false,
					'downloaded'        => false,
					'last_updated'      => false,
					'added'             => false,
					'tags'              => false,
					'homepage'          => false,
					'donate_link'       => false,
				),
			) );

			return $infos;
		}

		/**
		 * Get the URL to download Imagify.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 *
		 * @return string The URL. An empty string on error.
		 */
		protected function get_download_url() {
			$infos = $this->get_imagify_infos();
			return ! empty( $infos->download_link ) ? $infos->download_link : '';
		}

		/**
		 * Install Imagify.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 *
		 * @return object|null A WP_Object on failure, null on success.
		 */
		protected function install_imagify() {
			if ( self::is_imagify_installed() ) {
				// Imagify is already installed.
				return null;
			}

			$infos = $this->get_imagify_infos();

			if ( is_wp_error( $infos ) ) {
				return $infos;
			}

			ob_start();
			@set_time_limit( 0 );

			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$upgrader = new Plugin_Upgrader( new Automatic_Upgrader_Skin() );
			$result   = $upgrader->install( $this->get_download_url() );

			ob_end_clean();

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			clearstatcache();

			if ( ! self::is_imagify_installed() ) {
				return new WP_Error( 'process_failed', $this->get_message( 'process_failed' ) );
			}

			return null;
		}

		/**
		 * Activate Imagify.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 *
		 * @return object|null A WP_Object on failure, null on success.
		 */
		protected function activate_imagify() {
			return activate_plugin( self::get_imagify_path(), false, is_multisite() );
		}

		/**
		 * Get a message used by the class.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 *
		 * @param  string $message_id A message ID.
		 * @return string             A message.
		 */
		protected function get_message( $message_id ) {
			$messages = array(
				'success'        => __( 'Plugin installed successfully.' ),
				'cant_install'   => __( 'Sorry, you are not allowed to install plugins on this site.' ),
				'not_allowed'    => __( 'Sorry, you are not allowed to do that.' ),
				'process_failed' => __( 'Plugin install failed.' ),
				'go_back'        => __( 'Go back' ),
			);

			/**
			 * Filter messages used everywhere in the class.
			 * Default messages are already translated by WordPress.
			 *
			 * @since  1.0
			 * @author Grégory Viguier
			 *
			 * @param array $messages Messages.
			 */
			$messages = apply_filters( 'imagify_partner_messages_' . $this->get_partner(), $messages );

			return ! empty( $messages[ $message_id ] ) ? $messages[ $message_id ] : self::FALLBACK_MESSAGE;
		}


		/** ----------------------------------------------------------------------------------------- */
		/** HANDLE SUCCESS ========================================================================== */
		/** ----------------------------------------------------------------------------------------- */

		/**
		 * Send a JSON response back to an Ajax request, indicating success.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 */
		protected function send_json_success() {
			delete_transient( self::ERROR_TRANSIENT_NAME );

			wp_send_json_success( $this->get_message( 'success' ) );
		}

		/**
		 * Redirect the user after Imagify is successfully installed and activated.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 */
		protected function success_redirect() {
			delete_transient( self::ERROR_TRANSIENT_NAME );

			wp_safe_redirect( esc_url_raw( $this->get_success_redirection_url() ) );
			die();
		}

		/**
		 * Get the URL to redirect the user to after Imagify is successfully installed and activated: the referrer (partner's page URL).
		 * A "success" argument is added.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return string
		 */
		public function get_success_redirection_url() {
			$success_url = add_query_arg( array(
				self::SUCCESS_ARG => 1,
				self::ERROR_ARG   => false,
			), wp_get_referer() );

			/**
			 * Filter the URL to redirect the user to after Imagify is successfully installed and activated.
			 * Default is the partner's page URL.
			 *
			 * @since  1.0
			 * @author Grégory Viguier
			 *
			 * @param string $success_url The URL.
			 */
			return apply_filters( 'imagify_partner_success_url_' . $this->get_partner(), $success_url );
		}


		/** ----------------------------------------------------------------------------------------- */
		/** HANDLE ERROR ============================================================================ */
		/** ----------------------------------------------------------------------------------------- */

		/**
		 * Die on error.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 *
		 * @param string $message_id An error message ID.
		 */
		protected function error_die( $message_id = 'not_allowed' ) {
			$message = $this->get_message( $message_id );

			if ( self::doing_ajax() ) {
				$message = new WP_Error( $message_id, $message );
				$this->send_json_error( $message );
			}

			if ( wp_get_referer() ) {
				$message .= '</p><p>';
				$message .= sprintf( '<a href="%s">%s</a>',
					esc_url( remove_query_arg( 'updated', wp_get_referer() ) ),
					$this->get_message( 'go_back' )
				);
			}

			wp_die( $message, '', 403 );
		}

		/**
		 * Send a JSON response back to an Ajax request, indicating failure.
		 * This is a backward compatible version of wp_send_json_error(): WP_Error object handling was introduced in WP 4.1.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 *
		 * @param mixed $data Data to encode as JSON, then print and die.
		 */
		protected function send_json_error( $data ) {
			if ( is_wp_error( $data ) ) {
				$result = array();
				foreach ( $data->errors as $code => $messages ) {
					foreach ( $messages as $message ) {
						$result[] = array(
							'code'    => $code,
							'message' => $message,
						);
					}
				}
			} else {
				$result = $data;
			}

			wp_send_json_error( $result );
		}

		/**
		 * Store an error message in a transient then redirect the user.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 *
		 * @param object $error A WP_Error object.
		 */
		protected function error_redirect( $error ) {
			set_transient( self::ERROR_TRANSIENT_NAME, $error, 30 );

			wp_safe_redirect( esc_url_raw( $this->get_error_redirection_url() ) );
			die();
		}

		/**
		 * Get the URL to redirect the user to after Imagify installation failure: the plugins search page URL, searching for Imagify.
		 * An "error" argument is added, to display an error notice.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return string
		 */
		public function get_error_redirection_url() {
			$error_url = 'plugin-install.php?s=imagify&tab=search&type=term&' . self::ERROR_ARG . '=1';
			$error_url = is_multisite() ? network_admin_url( $error_url ) : admin_url( $error_url );

			/**
			 * Filter the URL to redirect the user to after Imagify installation failure.
			 * Default is the plugins search page URL.
			 *
			 * @since  1.0
			 * @author Grégory Viguier
			 *
			 * @param string $error_url The URL.
			 */
			return apply_filters( 'imagify_partner_error_url_' . $this->get_partner(), $error_url );
		}


		/** ----------------------------------------------------------------------------------------- */
		/** STORING THE PARTNER ID IN DATABASE ====================================================== */
		/** ----------------------------------------------------------------------------------------- */

		/**
		 * Get the partner identifier stored in the Database.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return string|bool The partner identifier, or false if none is stored.
		 */
		public static function get_stored_partner() {
			$partner = get_option( self::OPTION_NAME );

			if ( $partner && is_string( $partner ) ) {
				$partner = self::sanitize_partner( $partner );
			}

			return $partner ? $partner : false;
		}

		/**
		 * Delete the partner identifier stored in the Database.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 */
		public static function delete_stored_partner() {
			if ( false !== get_option( self::OPTION_NAME ) ) {
				delete_option( self::OPTION_NAME );
			}
		}

		/**
		 * Store the partner identifier in Database.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 *
		 * @param string $partner The partner identifier to store.
		 */
		protected static function store_partner( $partner ) {
			if ( false === get_option( self::OPTION_NAME ) ) {
				add_option( self::OPTION_NAME, $partner );
			} else {
				update_option( self::OPTION_NAME, $partner );
			}
		}

		/**
		 * Sanitize a partner ID.
		 *
		 * @since  1.0
		 * @access protected
		 * @author Grégory Viguier
		 *
		 * @param  string $partner Partner identifier.
		 * @return string
		 */
		protected static function sanitize_partner( $partner ) {
			return preg_replace( '@[^a-z0-9_-]@', '', strtolower( (string) $partner ) );
		}


		/** ----------------------------------------------------------------------------------------- */
		/** VARIOUS TOOLS =========================================================================== */
		/** ----------------------------------------------------------------------------------------- */

		/**
		 * Get the action.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return string Partner identifier.
		 */
		public function get_post_action() {
			return 'install_imagify_from_partner_' . $this->get_partner();
		}

		/**
		 * Determines whether the current request is a WordPress Ajax request.
		 * This is a clone of wp_doing_ajax(), intriduced in WP 4.7.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return bool True if it's a WordPress Ajax request, false otherwise.
		 */
		public static function doing_ajax() {
			/**
			 * Filters whether the current request is a WordPress Ajax request.
			 *
			 * @since 1.0
			 *
			 * @param bool $wp_doing_ajax Whether the current request is a WordPress Ajax request.
			 */
			return apply_filters( 'wp_doing_ajax', defined( 'DOING_AJAX' ) && DOING_AJAX );
		}

		/**
		 * Get Imagify's file path.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return string The file path.
		 */
		public static function get_imagify_path() {
			if ( defined( 'IMAGIFY_FILE' ) ) {
				return IMAGIFY_FILE;
			}

			return WP_PLUGIN_DIR . '/imagify/imagify.php';
		}

		/**
		 * Tell if the current user can install and activate Imagify.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return bool
		 */
		public static function current_user_can() {
			static $can;

			if ( ! isset( $can ) ) {
				$can = is_multisite() ? 'manage_network_plugins' : 'install_plugins';
				$can = current_user_can( $can );
			}

			return $can;
		}

		/**
		 * Get the current URL.
		 *
		 * @since  1.0
		 * @access public
		 * @author Grégory Viguier
		 *
		 * @return string
		 */
		public static function get_current_url() {
			$port = (int) $_SERVER['SERVER_PORT'];
			$port = 80 !== $port && 443 !== $port ? ( ':' . $port ) : '';
			$url  = ! empty( $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'] ) ? $GLOBALS['HTTP_SERVER_VARS']['REQUEST_URI'] : ( ! empty( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '' );

			return 'http' . ( is_ssl() ? 's' : '' ) . '://' . $_SERVER['HTTP_HOST'] . $port . $url;
		}
	}

endif;
