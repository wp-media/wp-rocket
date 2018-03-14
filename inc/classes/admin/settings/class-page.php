<?php
namespace WP_Rocket\Admin\Settings;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Registers the admin page and WP Rocket settings
 *
 * @since 3.0
 * @author Remy Perona
 */
class Page {
	/**
	 * Plugin slug
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var string
	 */
	private $slug;

	/**
	 * Plugin page title
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Required capability to access the page
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var string
	 */
	private $capability;

	/**
	 * Settings instance
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var Settings
	 */
	private $settings;

	/**
	 * Render implementation
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var \WP_Rocket\Interfaces\Render_Interface
	 */
	private $render;

	/**
	 * Current WP locale without the region (e.g. en, fr)
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @var string
	 */
	private $locale;

	/**
	 * Constructor
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array                                  $args     Array of required arguments to add the admin page.
	 * @param Settings                               $settings Instance of Settings class.
	 * @param \WP_Rocket\Interfaces\Render_Interface $render   Implementation of Render interface.
	 */
	public function __construct( $args, Settings $settings, \WP_Rocket\Interfaces\Render_Interface $render ) {
		$this->slug       = $args['slug'];
		$this->title      = $args['title'];
		$this->capability = $args['capability'];
		$this->settings   = $settings;
		$this->render     = $render;

		$locale       = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$this->locale = current( array_slice( explode( '_', $locale ), 0, 1 ) );
	}

	/**
	 * Registers the class and hooks
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array                                  $args     Array of required arguments to add the admin page.
	 * @param Settings                               $settings Instance of Settings class.
	 * @param \WP_Rocket\Interfaces\Render_Interface $render   Implementation of Render interface.
	 * @return void
	 */
	public static function register( $args, Settings $settings, $render ) {
		$self = new self( $args, $settings, $render );

		add_action( 'admin_menu', [ $self, 'add_admin_page' ] );
		add_action( 'admin_init', [ $self, 'configure' ] );
		add_action( 'admin_print_footer_scripts-settings_page_wprocket', [ $self, 'insert_beacon' ] );
		add_action( 'wp_ajax_rocket_refresh_customer_data', [ $self, 'refresh_customer_data' ] );
		add_action( 'wp_ajax_rocket_toggle_varnish', [ $self, 'toggle_varnish' ] );
		add_action( 'wp_ajax_rocket_toggle_cloudflare', [ $self, 'toggle_cloudflare' ] );

		add_filter( 'option_page_capability_' . $self->slug, [ $self, 'required_capability' ] );
		add_filter( 'rocket_settings_menu_navigation', [ $self, 'add_menu_tools_page' ] );
	}

	/**
	 * Adds plugin page to the Settings menu
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function add_admin_page() {
		add_options_page(
			$this->title,
			$this->title,
			$this->capability,
			$this->slug,
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Registers the settings, page sections, fields sections and fields.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function configure() {
		register_setting( $this->slug, WP_ROCKET_SLUG, [ $this->settings, 'sanitize_callback' ] );

		if ( rocket_valid_key() ) {
			$this->dashboard_section();
			$this->cache_section();
			$this->assets_section();
			$this->media_section();
			$this->preload_section();
			$this->advanced_cache_section();
			$this->database_section();
			$this->cdn_section();
			$this->addons_section();
			$this->cloudflare_section();
		} else {
			$this->license_section();
		}

		$this->render->set_settings( $this->settings->get_settings() );

		$this->hidden_fields();

		$this->render->set_hidden_settings( $this->settings->get_hidden_settings() );
	}

	/**
	 * Renders the settings page
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function render_page() {
		echo $this->render->generate( 'page', [ 'slug' => $this->slug ] );
	}

	/**
	 * Sets the capability for the options page if custom.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $capability Custom capability to replace manage_options.
	 * @return string
	 */
	public function required_capability( $capability ) {
		/** This filter is documented in inc/admin-bar.php */
		return apply_filters( 'rocket_capacity', $capability );
	}

	/**
	 * Insert HelpScout Beacon script
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function insert_beacon() {
		/** This filter is documented in inc/admin-bar.php */
		if ( ! current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
			return;
		}

		switch ( $this->locale ) {
			case 'fr':
				$lang        = '-fr';
				$form_id     = '5d9279dc-1b2d-11e8-b466-0ec85169275a';
				$suggest     = wp_list_pluck( $this->get_beacon_suggest( 'faq', 'fr' ), 'id' );
				$translation = wp_json_encode( [
					'searchLabel'               => 'Comment pouvons-nous vous aider ?',
					'searchErrorLabel'          => 'Votre recherche a expiré. Veuillez vérifier votre connexion et réessayer.',
					'noResultsLabel'            => 'Aucun résultat trouvé pour',
					'contactLabel'              => 'Envoyer un message',
					'attachFileLabel'           => 'Joindre un fichier',
					'attachFileError'           => 'Le poids maximum de fichier est de 10Mo',
					'fileExtensionError'        => 'Le format du fichier attaché n\'est pas autorisé.',
					'nameLabel'                 => 'Votre nom',
					'nameError'                 => 'Veuillez entrer votre nom',
					'emailLabel'                => 'Adresse email',
					'emailError'                => 'Veuillez entrer une adresse email valide',
					'topicLabel'                => 'Sélectionnez un sujet',
					'topicError'                => 'Veuillez sélectionner un sujet dans la liste',
					'subjectLabel'              => 'Sujet',
					'subjectError'              => 'Veuillez entrer un sujet',
					'messageLabel'              => 'Comment pouvons-nous vous aider ?',
					'messageError'              => 'Veuillez entrer un message',
					'sendLabel'                 => 'Envoyer',
					'contactSuccessLabel'       => 'Message envoyé !',
					'contactSuccessDescription' => 'Merci de nous avoir contacté ! Un de nos rocketeers vous répondra rapidement.',
				] );
				break;
			default:
				$lang        = '';
				$form_id     = '6e4a6b6e-1b2d-11e8-b466-0ec85169275a';
				$suggest     = wp_list_pluck( $this->get_beacon_suggest( 'faq' ), 'id' );
				$translation = '{}';
				break;
		}

		$script = '<script>!function(e,o,n){window.HSCW=o,window.HS=n,n.beacon=n.beacon||{};var t=n.beacon;t.userConfig={},t.readyQueue=[],t.config=function(e){this.userConfig=e},t.ready=function(e){this.readyQueue.push(e)},o.config={docs:{enabled:!0,baseUrl:"https://wp-rocket' . $lang . '.helpscoutdocs.com/"},contact:{enabled:!0,formId:"' . $form_id . '"}};var r=e.getElementsByTagName("script")[0],c=e.createElement("script");c.type="text/javascript",c.async=!0,c.src="https://djtflbt20bdde.cloudfront.net/",r.parentNode.insertBefore(c,r)}(document,window.HSCW||{},window.HS||{});
			HS.beacon.ready( function() {
				HS.beacon.suggest(' . wp_json_encode( $suggest ) . ');
				HS.beacon.identify({
					email: "' . get_rocket_option( 'consumer_email' ) . '"
				});
			} );
			HS.beacon.config({
				showSubject: true,
				translation: ' . $translation . '
			});</script>';

		echo $script;
	}

	/**
	 * Gets customer data from WP Rocket website to display it in the dashboard
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return object
	 */
	private function get_customer_data() {
		$customer_key   = defined( 'WP_ROCKET_KEY' ) ? WP_ROCKET_KEY : get_rocket_option( 'consumer_key', '' );
		$customer_email = defined( 'WP_ROCKET_EMAIL' ) ? WP_ROCKET_EMAIL : get_rocket_option( 'consumer_email', '' );

		$response = wp_safe_remote_post(
			WP_ROCKET_WEB_MAIN . 'stat/1.0/wp-rocket/user.php',
			[
				'body' => 'user_id=' . $customer_email . '&consumer_key=' . $customer_key,
			]
		);

		if ( is_wp_error( $response ) ) {
			return (object) [
				'licence_account'    => __( 'Unavailable', 'rocket' ),
				'licence_expiration' => __( 'Unavailable', 'rocket' ),
			];
		}

		$customer_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 1 <= $customer_data->licence_account && $customer_data->licence_account < 3 ) {
			$customer_data->licence_account = 'Single';
		} elseif ( '-1' === $customer_data->licence_account ) {
			$customer_data->licence_account = 'Unlimited';
		} else {
			$customer_data->licence_account = 'Plus';
		}

		$customer_data->licence_expiration = date_i18n( get_option( 'date_format' ), $customer_data->licence_expiration );

		return $customer_data;
	}

	/**
	 * Returns customer data from transient or request and save it if not cached
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return object
	 */
	private function customer_data() {
		if ( false !== get_transient( 'wp_rocket_customer_data' ) ) {
			return get_transient( 'wp_rocket_customer_data' );
		}

		$customer_data = $this->get_customer_data();

		set_transient( 'wp_rocket_customer_data', $customer_data, DAY_IN_SECONDS );

		return $customer_data;
	}

	/**
	 * Gets customer data to refresh it on the dashboard with AJAX
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return string
	 */
	public function refresh_customer_data() {
		check_ajax_referer( 'rocket-ajax' );

		if ( ! current_user_can( apply_filters( 'rocket_capability', 'manage_options' ) ) ) {
			wp_die();
		}

		delete_transient( 'wp_rocket_customer_data' );

		return wp_send_json_success( $this->customer_data() );
	}

	/**
	 * Toggle varnish option value
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function toggle_varnish() {
		check_ajax_referer( 'rocket-ajax' );

		if ( ! current_user_can( apply_filters( 'rocket_capability', 'manage_options' ) ) ) {
			wp_die();
		}

		$value = (int) ! empty( $_POST['varnish_auto_purge'] );

		update_rocket_option( 'varnish_auto_purge', $value );

		wp_die();
	}

	/**
	 * Toggle varnish option value
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function toggle_cloudflare() {
		check_ajax_referer( 'rocket-ajax' );

		if ( ! current_user_can( apply_filters( 'rocket_capability', 'manage_options' ) ) ) {
			wp_die();
		}

		$value = (int) ! empty( $_POST['do_cloudflare'] );

		update_rocket_option( 'do_cloudflare', $value );

		wp_die();
	}

	/**
	 * Registers License section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function license_section() {
		$this->settings->add_page_section(
			'license',
			[
				'title' => __( 'License' ),
			]
		);

		$this->settings->add_settings_sections(
			[
				'license_section' => [
					'type' => 'nocontainer',
					'page' => 'license',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'consumer_key'   => [
					'type'              => 'text',
					'label'             => __( 'API key', 'rocket' ),
					'container_class'   => [
						'wpr-field--split',
						'wpr-isDisabled',
					],
					'section'           => 'license_section',
					'page'              => 'license',
					'sanitize_callback' => 'sanitize_text_field',
					'input_attr'        => [
						'disabled' => 1,
					],
				],
				'consumer_email' => [
					'type'              => 'text',
					'label'             => __( 'Email address', 'rocket' ),
					'container_class'   => [
						'wpr-field--split',
						'wpr-isDisabled',
					],
					'section'           => 'license_section',
					'page'              => 'license',
					'sanitize_callback' => 'sanitize_email',
					'input_attr'        => [
						'disabled' => 1,
					],
				],
			]
		);
	}

	/**
	 * Registers Dashboard section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function dashboard_section() {
		$this->settings->add_page_section(
			'dashboard',
			[
				'title'            => __( 'Dashboard', 'rocket' ),
				'menu_description' => __( 'Get help, account info', 'rocket' ),
				'faq'              => $this->get_beacon_suggest( 'faq', $this->locale ),
				'customer_data'    => $this->customer_data(),
			]
		);

		$this->settings->add_settings_sections(
			[
				'status' => [
					'title' => __( 'My Status', 'rocket' ),
					'page'  => 'dashboard',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'do_beta'           => [
					'type'              => 'sliding_checkbox',
					'label'             => __( 'Rocket Tester', 'rocket' ),
					'description'       => __( 'I am part of the WP Rocket Beta Testing Program.', 'rocket' ),
					'section'           => 'status',
					'page'              => 'dashboard',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'analytics_enabled' => [
					'type'              => 'sliding_checkbox',
					'label'             => __( 'Rocket Analytics', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'I agree to share anonymous data with the development team to help improve WP Rocket. %1$sWhat info will we collect?%2$s', 'rocket' ), '<button class="wpr-js-popin">', '</button>' ),
					'section'           => 'status',
					'page'              => 'dashboard',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
			]
		);
	}

	/**
	 * Registers Cache section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function cache_section() {
		$mobile_cache_beacon = $this->get_beacon_suggest( 'mobile_cache', $this->locale );
		$user_cache_beacon   = $this->get_beacon_suggest( 'user_cache', $this->locale );
		$nonce_beacon        = $this->get_beacon_suggest( 'nonce', $this->locale );

		$this->settings->add_page_section(
			'cache',
			[
				'title'            => __( 'Cache', 'rocket' ),
				'menu_description' => __( 'Basic cache options', 'rocket' ),
			]
		);

		$this->settings->add_settings_sections(
			[
				'mobile_cache_section' => [
					'title'       => __( 'Mobile Cache', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'Speed up your site for mobile visitors.', 'rocket' ),
					'help'        => $this->get_beacon_suggest( 'mobile_cache_section', $this->locale ),
					'page'        => 'cache',
				],
				'user_cache_section'   => [
					'title'       => __( 'User Cache', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description' => sprintf( __( '%1$sUser cache%2$s is great when you have user-specific or restricted content on your website.', 'rocket' ), '<a href="' . esc_url( $user_cache_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $user_cache_beacon['id'] ) . '">', '</a>' ),
					'help'        => $this->get_beacon_suggest( 'user_cache_section', $this->locale ),
					'page'        => 'cache',
				],
				'cache_lifespan'       => [
					'title'       => __( 'Cache Lifespan', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description' => sprintf( __( 'Cache lifespan is the period of time after which all cache files are removed.<br>Enable %1$spreloading%2$s for the cache to be rebuilt automatically after lifespan expiration.', 'rocket' ), '<a href="#preload">', '</a>' ),
					'help'        => $this->get_beacon_suggest( 'cache_lifespan', $this->locale ),
					'page'        => 'cache',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'user_cache'              => [
					'type'              => 'checkbox',
					'label'             => __( 'Enable caching for logged-in WordPress users', 'rocket' ),
					'section'           => 'user_cache_section',
					'page'              => 'cache',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'cache_mobile'            => [
					'type'              => 'checkbox',
					'label'             => __( 'Enable caching for mobile devices', 'rocket' ),
					'container_class'   => [
						rocket_is_mobile_plugin_active() ? 'wpr-isDisabled' : '',
						'wpr-isParent',
					],
					'section'           => 'mobile_cache_section',
					'page'              => 'cache',
					'default'           => 1,
					'sanitize_callback' => 'sanitize_checkbox',
					'input_attr'        => [
						'disabled' => rocket_is_mobile_plugin_active() ? 1 : 0,
					],
				],
				'do_caching_mobile_files' => [
					'type'              => 'checkbox',
					'label'             => __( 'Separate cache files for mobile devices', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( '%1$sMobile cache%2$s works safest with both options enabled. When in doubt, keep both.', 'rocket' ), '<a href="' . esc_url( $mobile_cache_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $mobile_cache_beacon['id'] ) . '">', '</a>' ),
					'container_class'   => [
						rocket_is_mobile_plugin_active() ? 'wpr-isDisabled' : '',
						'wpr-field--children',
					],
					'section'           => 'mobile_cache_section',
					'page'              => 'cache',
					'default'           => rocket_is_mobile_plugin_active() ? 1 : 0,
					'sanitize_callback' => 'sanitize_checkbox',
					'input_attr'        => [
						'disabled' => rocket_is_mobile_plugin_active() ? 1 : 0,
					],
				],
				'purge_cron_interval'     => [
					'type'              => 'cache_lifespan',
					'label'             => __( 'Specify time after which the global cache is cleared<br>(0 = unlimited )', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Reduce lifespan to 10 hours or less if you notice issues that seem to appear periodically. %1$sWhy?%2$s', 'rocket' ), '<a href="' . esc_url( $nonce_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $nonce_beacon['id'] ) . '">', '</a>' ),
					'section'           => 'cache_lifespan',
					'page'              => 'cache',
					'default'           => 10,
					'sanitize_callback' => 'sanitize_cache_lifespan',
					'choices'           => [
						'MINUTE_IN_SECONDS' => __( 'Minutes', 'rocket' ),
						'HOUR_IN_SECONDS'   => __( 'Hours', 'rocket' ),
						'DAY_IN_SECONDS'    => __( 'Days', 'rocket' ),
					],
				],
			]
		);
	}

	/**
	 * Registers CSS & Javascript section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function assets_section() {
		$this->settings->add_page_section(
			'file_optimization',
			[
				'title'            => __( 'File Optimization', 'rocket' ),
				'menu_description' => __( 'Optimize CSS & JS', 'rocket' ),
			]
		);

		$this->settings->add_settings_sections(
			[
				'basic' => [
					'title' => __( 'Basic Settings', 'rocket' ),
					'help'  => $this->get_beacon_suggest( 'basic_section', $this->locale ),
					'page'  => 'file_optimization',
				],
				'css'   => [
					'title' => __( 'CSS Files', 'rocket' ),
					'help'  => $this->get_beacon_suggest( 'css_section', $this->locale ),
					'page'  => 'file_optimization',
				],
				'js'    => [
					'title' => __( 'JavaScript Files', 'rocket' ),
					'help'  => $this->get_beacon_suggest( 'js_section', $this->locale ),
					'page'  => 'file_optimization',
				],
			]
		);

		$remove_qs_beacon = $this->get_beacon_suggest( 'remove_query_strings', $this->locale );
		$combine_beacon   = $this->get_beacon_suggest( 'combine', $this->locale );
		$defer_beacon     = $this->get_beacon_suggest( 'defer', $this->locale );

		$this->settings->add_settings_fields(
			[
				'minify_html'            => [
					'type'              => 'checkbox',
					'label'             => __( 'Minify HTML', 'rocket' ),
					'container_class'   => [
						rocket_maybe_disable_minify_html() ? 'wpr-isDisabled' : '',
					],
					'description'       => __( 'Minifying HTML removes whitespace and comments to reduce the size.', 'rocket' ),
					'section'           => 'basic',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
					'input_attr'        => [
						'disabled' => rocket_maybe_disable_minify_html() ? 1 : 0,
					],
				],
				'minify_google_fonts'    => [
					'type'              => 'checkbox',
					'label'             => __( 'Combine Google Fonts files', 'rocket' ),
					'description'       => __( 'Combining Google Fonts will reduce the number of HTTP requests.', 'rocket' ),
					'section'           => 'basic',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'remove_query_strings'   => [
					'type'              => 'checkbox',
					'label'             => __( 'Remove query strings from static resources', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Removes the version query string from static files (e.g. style.css?ver=1.0 and encodes it into the filename instead (e.g. style-1.0.css). Can improve your GTMetrix score. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $remove_qs_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $remove_qs_beacon['id'] ) . '">', '</a>' ),
					'section'           => 'basic',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'minify_css'             => [
					'type'              => 'checkbox',
					'label'             => __( 'Minify CSS files', 'rocket' ),
					'description'       => __( 'Minify CSS removes whitespace and comments to reduce the file size.', 'rocket' ),
					'container_class'   => [
						rocket_maybe_disable_minify_css() ? 'wpr-isDisabled' : '',
						'wpr-field--parent',
					],
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
					'input_attr'        => [
						'disabled' => rocket_maybe_disable_minify_css() ? 1 : 0,
					],
					'warning'           => [
						'title'        => __( 'This could break things!', 'rocket' ),
						'description'  => __( 'If you notice any errors on your website after having activated this setting, just deactivate it again, and your site will be back to normal.', 'rocket' ),
						'button_label' => __( 'Activate minify CSS', 'rocket' ),
					],
				],
				'minify_concatenate_css' => [
					'type'              => 'checkbox',
					'label'             => __( 'Combine CSS files <em>(Enable minify CSS files to select)</em>', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Combine CSS merges all your files into 1, reducing HTTP requests. Not recommended if your site uses HTTP/2. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $combine_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $combine_beacon['id'] ) . '">', '</a>' ),
					'container_class'   => [
						get_rocket_option( 'minify_css' ) ? '' : 'wpr-isDisabled',
						'wpr-field--parent',
					],
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
					'input_attr'        => [
						'disabled' => get_rocket_option( 'minify_css' ) ? 0 : 1,
					],
					'warning'           => [
						'title'        => __( 'This could break things!', 'rocket' ),
						'description'  => __( 'If you notice any errors on your website after having activated this setting, just deactivate it again, and your site will be back to normal.', 'rocket' ),
						'button_label' => __( 'Activate combine CSS', 'rocket' ),
					],
				],
				'async_css'              => [
					'type'              => 'checkbox',
					'label'             => __( 'Optimize CSS delivery', 'rocket' ),
					'container_class'   => [
						'wpr-isParent',
					],
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Optimize CSS delivery eliminates render-blocking CSS on your website for faster perceived load time. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $defer_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $defer_beacon['id'] ) . '">', '</a>' ),
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'critical_css'           => [
					'type'              => 'textarea',
					'label'             => __( 'Fallback critical CSS:', 'rocket' ),
					'container_class'   => [
						'wpr-field--children',
					],
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'helper'            => sprintf( __( 'Provides a fallback if auto-generated critical path CSS is incomplete. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $defer_beacon['url'] ) . '#fallback" data-beacon-article="' . esc_attr( $defer_beacon['id'] ) . '">', '</a>' ),
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'exclude_css'            => [
					'type'              => 'textarea',
					'label'             => __( 'Excluded CSS Files', 'rocket' ),
					'container_class'   => [
						'wpr-field--children',
					],
					'description'       => __( 'Specify URLs of CSS files to be excluded from minification and concatenation.', 'rocket' ),
					'helper'            => __( 'The domain part of the URL will be stripped automatically.<br>Use (.*).css wildcards to exclude all CSS files located at a specific path.', 'rocket' ),
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'minify_js'              => [
					'type'              => 'checkbox',
					'label'             => __( 'Minify JavaScript files', 'rocket' ),
					'description'       => __( 'Minify JavaScript removes whitespace and comments to reduce the file size.', 'rocket' ),
					'container_class'   => [
						rocket_maybe_disable_minify_js() ? 'wpr-isDisabled' : '',
						'wpr-field--parent',
					],
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => 0,
					'input_attr'        => [
						'disabled' => rocket_maybe_disable_minify_js() ? 1 : 0,
					],
					'sanitize_callback' => 'sanitize_checkbox',
					'warning'           => [
						'title'        => __( 'This could break things!', 'rocket' ),
						'description'  => __( 'If you notice any errors on your website after having activated this setting, just deactivate it again, and your site will be back to normal.', 'rocket' ),
						'button_label' => __( 'Activate minify JavaScript', 'rocket' ),
					],
				],
				'minify_concatenate_js'  => [
					'type'              => 'checkbox',
					'label'             => __( 'Combine JavaScript files <em>(Enable minify JS to select)</em>', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Combine Javascript files combines your site\'s JS info fewer files, reducing HTTP requests. Not recommended if your site uses HTTP/2. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $combine_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $combine_beacon['id'] ) . '">', '</a>' ),
					'container_class'   => [
						get_rocket_option( 'minify_js' ) ? '' : 'wpr-isDisabled',
						'wpr-field--parent',
					],
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
					'input_attr'        => [
						'disabled' => get_rocket_option( 'minify_js' ) ? 0 : 1,
					],
					'warning'           => [
						'title'        => __( 'This could break things!', 'rocket' ),
						'description'  => __( 'If you notice any errors on your website after having activated this setting, just deactivate it again, and your site will be back to normal.', 'rocket' ),
						'button_label' => __( 'Activate combine JavaScript', 'rocket' ),
					],
				],
				'defer_all_js'           => [
					'container_class'   => [
						'wpr-isParent',
					],
					'type'              => 'checkbox',
					'label'             => __( 'Load JavaScript deferred', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Load JavaScript deferred eliminates render-blocking JS on your site and can improve load time. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $defer_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $defer_beacon['id'] ) . '">', '</a>' ),
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'defer_all_js_safe'      => [
					'container_class'   => [
						'wpr-field--children',
					],
					'type'              => 'checkbox',
					'label'             => __( 'Safe Mode (recommended)', 'rocket' ),
					'description'       => __( 'Safe mode for deferred JS ensures support for inline jQuery references from themes and plugins by loading jQuery at the top of the document as a render-blocking script.<br><em>Deactivating may result in broken functionality, test thoroughly!</em>', 'rocket' ),
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => 1,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'exclude_js'             => [
					'type'              => 'textarea',
					'label'             => __( 'Excluded JavaScript Files', 'rocket' ),
					'container_class'   => [
						'wpr-field--children',
					],
					'description'       => __( 'Specify URLs of JavaScript files to be excluded from minification and concatenation.', 'rocket' ),
					'helper'            => __( 'The domain part of the URL will be stripped automatically.<br>Use (.*).js wildcards to exclude all JS files located at a specific path.', 'rocket' ),
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
			]
		);
	}

	/**
	 * Registers Media section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function media_section() {
		$this->settings->add_page_section(
			'media',
			[
				'title'            => __( 'Media', 'rocket' ),
				'menu_description' => __( 'LazyLoad, emojis, embeds', 'rocket' ),
			]
		);

		$this->settings->add_settings_sections(
			[
				'lazyload_section' => [
					'title'       => __( 'LazyLoad', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'It can improve actual and perceived loading time as images, iframes, and videos will be loaded only as they enter (or about to enter) the viewport and reduces the number of HTTP requests.', 'rocket' ),
					'help'        => $this->get_beacon_suggest( 'lazyload', $this->locale ),
					'page'        => 'media',
				],
				'emoji_section'    => [
					'title'       => __( 'Emoji 👻', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'Use default emoji of visitor\'s browser instead of loading emoji from WordPress.org', 'rocket' ),
					'page'        => 'media',
				],
				'embeds_section'   => [
					'title'       => __( 'Embeds', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'Prevents others from embedding content from your site, prevents you from embedding content from other (non-whitelisted) sites, and removes JavaScript requests related to WordPress embeds', 'rocket' ),
					'page'        => 'media',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'lazyload'         => [
					'type'              => 'checkbox',
					'label'             => __( 'Enable for images', 'rocket' ),
					'section'           => 'lazyload_section',
					'page'              => 'media',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'lazyload_iframes' => [
					'container_class'   => [
						'wpr-isParent',
					],
					'type'              => 'checkbox',
					'label'             => __( 'Enable for iframes and videos', 'rocket' ),
					'section'           => 'lazyload_section',
					'page'              => 'media',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'lazyload_youtube' => [
					'container_class'   => [
						'wpr-field--children',
					],
					'type'              => 'checkbox',
					'label'             => __( 'Replace YouTube iframe with preview image', 'rocket' ),
					'description'       => __( 'This can significantly improve your loading time if you have a lot of YouTube videos on a page.', 'rocket' ),
					'section'           => 'lazyload_section',
					'page'              => 'media',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'emoji'            => [
					'type'              => 'checkbox',
					'label'             => __( 'Disable Emoji', 'rocket' ),
					'description'       => __( 'Disable Emoji will reduce the number of external HTTP requests.', 'rocket' ),
					'section'           => 'emoji_section',
					'page'              => 'media',
					'default'           => 1,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'embeds'           => [
					'type'              => 'checkbox',
					'label'             => __( 'Disable WordPress embeds', 'rocket' ),
					'section'           => 'embeds_section',
					'page'              => 'media',
					'default'           => 1,
					'sanitize_callback' => 'sanitize_checkbox',
				],
			]
		);
	}

	/**
	 * Registers Preload section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function preload_section() {
		$this->settings->add_page_section(
			'preload',
			[
				'title'            => __( 'Preload', 'rocket' ),
				'menu_description' => __( 'Generate cache files', 'rocket' ),
			]
		);

		$bot_beacon = $this->get_beacon_suggest( 'bot', $this->locale );

		$this->settings->add_settings_sections(
			[
				'sitemap_preload_section' => [
					'title'       => __( 'Sitemap Preloading', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description' => sprintf( __( 'Sitemap preloading runs automatically when the cache lifespan expires. You can also launch it manually from the upper toolbar menu, or from Quick Actions on the %1$sWP Rocket Dashboard%2$s.', 'rocket' ), '<a href="#dashboard">', '</a>' ),
					'help'        => $this->get_beacon_suggest( 'sitemap_preload', $this->locale ),
					'page'        => 'preload',
				],
				'preload_bot_section'     => [
					'title'       => __( 'Preload Bot', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag, %3$s = opening <a> tag, %4$s = closing </a> tag.
					'description' => sprintf( __( '%1$sBot-based%2$s preloading should only be used on well-performing servers.<br>Once activated, it gets triggered automatically after you add or update content on your website.<br>You can also launch it manually from the upper toolbar menu, or from Quick Actions on the %3$sWP Rocket Dashboard%4$s.', 'rocket' ), '<a href="' . esc_url( $bot_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $bot_beacon['id'] ) . '">', '</a>', '<a href="#dashboard">', '</a>' ),
					'helper'      => __( 'Deactivate these options if you notice any overload on your server!', 'rocket' ),
					'help'        => $this->get_beacon_suggest( 'preload_bot', $this->locale ),
					'page'        => 'preload',
				],
				'dns_prefetch_section'    => [
					'title'       => __( 'Prefetch DNS Requests', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'DNS prefetching can make external files load faster, especially on mobile networks', 'rocket' ),
					'help'        => $this->get_beacon_suggest( 'dns_prefetch', $this->locale ),
					'page'        => 'preload',
				],
			]
		);

		// Add this separately to be able to filter it easily.
		$this->settings->add_settings_fields(
			apply_filters( 'rocket_sitemap_preload_options', [
				'sitemap_preload' => [
					'type'              => 'checkbox',
					'label'             => __( 'Activate sitemap-based cache preloading', 'rocket' ),
					'container_class'   => [
						'wpr-isParent',
					],
					'section'           => 'sitemap_preload_section',
					'page'              => 'preload',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
			] )
		);

		$this->settings->add_settings_fields(
			[
				'sitemaps'          => [
					'type'              => 'textarea',
					'label'             => __( 'Sitemaps for preloading', 'rocket' ),
					'container_class'   => [
						'wpr-field--children',
					],
					'description'       => __( 'Specify XML sitemap(s) to be used for preloading', 'rocket' ),
					'section'           => 'sitemap_preload_section',
					'page'              => 'preload',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'manual_preload'    => [
					'type'              => 'checkbox',
					'label'             => __( 'Manual', 'rocket' ),
					'section'           => 'preload_bot_section',
					'page'              => 'preload',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'automatic_preload' => [
					'type'              => 'checkbox',
					'label'             => __( 'Automatic', 'rocket' ),
					'section'           => 'preload_bot_section',
					'page'              => 'preload',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'dns_prefetch'      => [
					'type'              => 'textarea',
					'label'             => __( 'URLs to prefetch', 'rocket' ),
					'description'       => __( 'Specify external hosts to be prefetched', 'rocket' ),
					'section'           => 'dns_prefetch_section',
					'page'              => 'preload',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
			]
		);
	}

	/**
	 * Registers Advanced Cache section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function advanced_cache_section() {
		$this->settings->add_page_section(
			'advanced_cache',
			[
				'title'            => __( 'Advanced Rules', 'rocket' ),
				'menu_description' => __( 'Fine-tune cache rules', 'rocket' ),
			]
		);

		$ecommerce_beacon           = $this->get_beacon_suggest( 'ecommerce', $this->locale );
		$cache_query_strings_beacon = $this->get_beacon_suggest( 'cache_query_strings', $this->locale );

		$this->settings->add_settings_sections(
			[
				'cache_reject_uri_section'     => [
					'title'       => __( 'Never Cache URL(s)', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description' => sprintf( __( 'Sensitive pages like custom login/logout URLs should be excluded from cache.<br>Cart, checkout and "my account" pages set in WooCommerce (and some other %1$secommerce plugins%2$s) will be detected and never cached by default.', 'rocket' ), '<a href="' . esc_url( $ecommerce_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $ecommerce_beacon['id'] ) . '">', '</a>' ),
					'help'        => $this->get_beacon_suggest( 'never_cache', $this->locale ),
					'page'        => 'advanced_cache',
				],
				'cache_reject_cookies_section' => [
					'title' => __( 'Never Cache Cookies', 'rocket' ),
					'type'  => 'fields_container',
					'page'  => 'advanced_cache',
				],
				'cache_reject_ua_section'      => [
					'title' => __( 'Never Cache User Agent(s)', 'rocket' ),
					'type'  => 'fields_container',
					'page'  => 'advanced_cache',
				],
				'cache_purge_pages_section'    => [
					'title' => __( 'Always Purge URL(s)', 'rocket' ),
					'type'  => 'fields_container',
					'help'  => $this->get_beacon_suggest( 'always_purge', $this->locale ),
					'page'  => 'advanced_cache',
				],
				'cache_query_strings_section'  => [
					'title'       => __( 'Cache Query String(s)', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description' => sprintf( __( '%1$sCache for query strings%2$s enables you to force caching for specific GET parameters.', 'rocket' ), '<a href="' . esc_url( $cache_query_strings_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $cache_query_strings_beacon['id'] ) . '">', '</a>' ),
					'help'        => $this->get_beacon_suggest( 'query_strings', $this->locale ),
					'page'        => 'advanced_cache',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'cache_reject_uri'     => [
					'type'              => 'textarea',
					'description'       => __( 'Specify URLs of pages or posts that should never be cached', 'rocket' ),
					'helper'            => __( 'Use (.*) wildcards to address multiple URLs under a given path.', 'rocket' ),
					'section'           => 'cache_reject_uri_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_reject_cookies' => [
					'type'              => 'textarea',
					'description'       => __( 'Specify the IDs of cookies that, when set in the visitor\'s browser, should prevent a page from getting cached', 'rocket' ),
					'section'           => 'cache_reject_cookies_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_reject_ua'      => [
					'type'              => 'textarea',
					'description'       => __( 'Specify user agent strings that should never see cached pages', 'rocket' ),
					'helper'            => __( 'Use (.*) wildcards to detect parts of UA strings.', 'rocket' ),
					'section'           => 'cache_reject_ua_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_purge_pages'    => [
					'type'              => 'textarea',
					'description'       => __( 'Specify URLs you always want purged from cache whenever you update any post or page', 'rocket' ),
					'helper'            => __( 'Use (.*) wildcards to address multiple URLs under a given path', 'rocket' ),
					'section'           => 'cache_purge_pages_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_query_strings'  => [
					'type'              => 'textarea',
					'description'       => __( 'Specify query strings for caching', 'rocket' ),
					'section'           => 'cache_query_strings_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
			]
		);
	}

	/**
	 * Registers Database section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function database_section() {
		$total                 = array();
		$database_optimization = new \Rocket_Database_Optimization();

		foreach ( $database_optimization->options as $option ) {
			$total[ $option ] = $database_optimization->count_cleanup_items( $option );
		}

		$this->settings->add_page_section(
			'database',
			[
				'title'            => __( 'Database', 'rocket' ),
				'menu_description' => __( 'Optimize, reduce bloat', 'rocket' ),
			]
		);

		$this->settings->add_settings_sections(
			[
				'post_cleanup_section'       => [
					'title'       => __( 'Post Cleanup', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'Post revisions and drafts will be permanently deleted. Do not use this option if you need to retain revisions or drafts.', 'rocket' ),
					'help'        => $this->get_beacon_suggest( 'cleanup', $this->locale ),
					'page'        => 'database',
				],
				'comments_cleanup_section'   => [
					'title'       => __( 'Comments Cleanup', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'Spam and trashed comments will be permanently deleted.', 'rocket' ),
					'page'        => 'database',
				],
				'transients_cleanup_section' => [
					'title'       => __( 'Transients Cleanup', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'Transients are temporary options; they are safe to remove. They will be automatically regenerated as your plugins require them.', 'rocket' ),
					'page'        => 'database',
				],
				'database_cleanup_section'   => [
					'title'       => __( 'Database Cleanup', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'Reduces overhead of database tables', 'rocket' ),
					'page'        => 'database',
				],
				'schedule_cleanup_section'   => [
					'title' => __( 'Automatic cleanup', 'rocket' ),
					'type'  => 'fields_container',
					'page'  => 'database',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'database_revisions'          => [
					'type'              => 'checkbox',
					'label'             => __( 'Revisions', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s revision in your database.', '%s revisions in your database.', $total['revisions'], 'rocket' ), number_format_i18n( $total['revisions'] ) ),
					'section'           => 'post_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_auto_drafts'        => [
					'type'              => 'checkbox',
					'label'             => __( 'Auto Drafts', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s draft in your database.', '%s drafts in your database.', $total['auto_drafts'], 'rocket' ), number_format_i18n( $total['auto_drafts'] ) ),
					'section'           => 'post_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_trashed_posts'      => [
					'type'              => 'checkbox',
					'label'             => __( 'Trashed Posts', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s trashed post in your database.', '%s trashed posts in your database.', $total['trashed_posts'], 'rocket' ), $total['trashed_posts'] ),
					'section'           => 'post_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_spam_comments'      => [
					'type'              => 'checkbox',
					'label'             => __( 'Spam Comments', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s spam comment in your database.', '%s spam comments in your database.', $total['spam_comments'], 'rocket' ), number_format_i18n( $total['spam_comments'] ) ),
					'section'           => 'comments_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_trashed_comments'   => [
					'type'              => 'checkbox',
					'label'             => __( 'Trashed Comments', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s trashed comment in your database.', '%s trashed comments in your database.', $total['trashed_comments'], 'rocket' ), number_format_i18n( $total['trashed_comments'] ) ),
					'section'           => 'comments_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_expired_transients' => [
					'type'              => 'checkbox',
					'label'             => __( 'Expired transients', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s expired transient in your database.', '%s expired transients in your database.', $total['expired_transients'], 'rocket' ), number_format_i18n( $total['expired_transients'] ) ),
					'section'           => 'transients_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_all_transients'     => [
					'type'              => 'checkbox',
					'label'             => __( 'All transients', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s transient in your database.', '%s transients in your database.', $total['all_transients'], 'rocket' ), number_format_i18n( $total['all_transients'] ) ),
					'section'           => 'transients_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_optimize_tables'    => [
					'type'              => 'checkbox',
					'label'             => __( 'Optimize Tables', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s table to optimize in your database.', '%s tables to optimize in your database.', $total['optimize_tables'], 'rocket' ), number_format_i18n( $total['optimize_tables'] ) ),
					'section'           => 'database_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'schedule_automatic_cleanup'  => [
					'container_class'   => [
						'wpr-isParent',
					],
					'type'              => 'checkbox',
					'label'             => __( 'Schedule Automatic Cleanup', 'rocket' ),
					'description'       => '',
					'section'           => 'schedule_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'automatic_cleanup_frequency' => [
					'container_class'   => [
						'wpr-field--children',
					],
					'type'              => 'select',
					'label'             => __( 'Frequency', 'rocket' ),
					'description'       => '',
					'section'           => 'schedule_cleanup_section',
					'page'              => 'database',
					'default'           => 'daily',
					'sanitize_callback' => 'sanitize_text_field',
					'choices'           => [
						'daily'   => __( 'Daily', 'rocket' ),
						'weekly'  => __( 'Weekly', 'rocket' ),
						'monthly' => __( 'Monthly', 'rocket' ),
					],
				],
			]
		);
	}

	/**
	 * Registers CDN section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function cdn_section() {
		$this->settings->add_page_section(
			'page_cdn',
			[
				'title'            => __( 'CDN', 'rocket' ),
				'menu_description' => __( 'Integrate your CDN', 'rocket' ),
			]
		);

		$this->settings->add_settings_sections(
			[
				'cdn_section'         => [
					'title'       => __( 'CDN', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'All URLs of static files (CSS, JS, images) will be rewritten to the CNAME(s) you provide.', 'rocket' ),
					'help'        => $this->get_beacon_suggest( 'cdn', $this->locale ),
					'page'        => 'page_cdn',
				],
				'exclude_cdn_section' => [
					'title' => __( 'Exclude files from CDN', 'rocket ' ),
					'type'  => 'fields_container',
					'help'  => $this->get_beacon_suggest( 'exclude_cdn', $this->locale ),
					'page'  => 'page_cdn',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'cdn'              => [
					'type'              => 'checkbox',
					'label'             => __( 'Enable Content Delivery Network', 'rocket' ),
					'section'           => 'cdn_section',
					'page'              => 'page_cdn',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'cdn_cnames'       => [
					'type'        => 'cnames',
					'label'       => __( 'CDN CNAME(s)', 'rocket' ),
					'description' => __( 'Specify the CNAME(s) below', 'rocket' ),
					'default'     => [],
					'section'     => 'cdn_section',
					'page'        => 'page_cdn',
				],
				'cdn_reject_files' => [
					'type'              => 'textarea',
					'description'       => __( 'Specify URL(s) of files that should not get served via CDN', 'rocket' ),
					'helper'            => __( 'The domain part of the URL will be stripped automatically.<br>Use (.*).js wildcards to exclude all JS files located at a specific path.', 'rocket' ),
					'section'           => 'exclude_cdn_section',
					'page'              => 'page_cdn',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
			]
		);
	}

	/**
	 * Registers Add-ons section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function addons_section() {
		$this->settings->add_page_section(
			'addons',
			[
				'title'            => __( 'Add-ons', 'rocket' ),
				'menu_description' => __( 'Add more features', 'rocket' ),
			]
		);

		$this->settings->add_settings_sections(
			[
				'one_click' => [
					'title'       => __( 'One-click Rocket Add-ons', 'rocket' ),
					'description' => __( 'One-click add-ons are simple complementary features extending available options.', 'rocket' ),
					'type'        => 'addons_container',
					'page'        => 'addons',
				],
				'addons'    => [
					'title'       => __( 'Rocket Add-ons', 'rocket' ),
					'description' => __( 'Rocket Add-ons offer you an entire new tab in the left panel. Take a look inside to set your new options.', 'rocket' ),
					'type'        => 'addons_container',
					'page'        => 'addons',
				],
			]
		);

		/**
		 * Allow to display the "Varnish" tab in the settings page
		 *
		 * @since 2.7
		 *
		 * @param bool true will display the "Varnish" tab
		*/
		if ( apply_filters( 'rocket_display_varnish_options_tab', true ) ) {
			$varnish_beacon = $this->get_beacon_suggest( 'varnish', $this->locale );

			$this->settings->add_settings_fields(
				[
					'varnish_auto_purge' => [
						'type'              => 'one_click_addon',
						'label'             => __( 'Varnish', 'rocket' ),
						'logo'              => [
							'url'    => WP_ROCKET_ASSETS_IMG_URL . '/logo-varnish.svg',
							'width'  => 152,
							'height' => 135,
						],
						'title'             => __( 'If Varnish runs on your server, you must activate this add-on.', 'rocket' ),
						// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
						'description'       => sprintf( __( 'Varnish cache will be purged each time WP Rocket clears its cache to ensure content is always up-to-date.<br>%1$sLearn more%2$s', 'rocket' ), '<a href="' . esc_url( $varnish_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $varnish_beacon['id'] ) . '">', '</a>' ),
						'section'           => 'one_click',
						'page'              => 'addons',
						'default'           => 0,
						'sanitize_callback' => 'sanitize_textarea',
					],
				]
			);
		}

		$this->settings->add_settings_fields(
			[
				'do_cloudflare' => [
					'type'              => 'rocket_addon',
					'label'             => __( 'Cloudflare', 'rocket' ),
					'logo'              => [
						'url'    => WP_ROCKET_ASSETS_IMG_URL . '/logo-cloudflare2.svg',
						'width'  => 153,
						'height' => 51,
					],
					'title'             => __( 'Integrate your Cloudflare account with this add-on.', 'rocket' ),
					'description'       => __( 'Provide your account email, global API key, and domain to use options such as clearing the Cloudflare cache and enabling optimal settings with WP Rocket.', 'rocket' ),
					'section'           => 'addons',
					'page'              => 'addons',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_textarea',
				],
			]
		);
	}

	/**
	 * Registers Cloudflare section
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function cloudflare_section() {
		$this->settings->add_page_section(
			'cloudflare',
			[
				'title'            => __( 'Cloudflare', 'rocket' ),
				'menu_description' => '',
				'class'            => [
					'wpr-cloudflareToggle',
				],
			]
		);

		$this->settings->add_settings_sections(
			[
				'cloudflare_credentials' => [
					'type'  => 'fields_container',
					'title' => __( 'Cloudflare credentials', 'rocket' ),
					'help'  => $this->get_beacon_suggest( 'cloudflare_credentials', $this->locale ),
					'page'  => 'cloudflare',
				],
				'cloudflare_settings'    => [
					'type'  => 'fields_container',
					'title' => __( 'Cloudflare settings', 'rocket' ),
					'help'  => $this->get_beacon_suggest( 'cloudflare_settings', $this->locale ),
					'page'  => 'cloudflare',
				],
			]
		);

		if ( ! defined( 'WP_ROCKET_CF_API_KEY_HIDDEN' ) || ! WP_ROCKET_CF_API_KEY_HIDDEN ) {
			$this->settings->add_settings_fields(
				[
					'cloudflare_api_key' => [
						'label'       => _x( 'Global API key:', 'Cloudflare', 'rocket' ),
						'description' => sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( __( 'https://support.cloudflare.com/hc/en-us/articles/200167836-Where-do-I-find-my-Cloudflare-API-key-', 'rocket' ) ), _x( 'Find your API key', 'Cloudflare', 'rocket' ) ),
						'default'     => '',
						'section'     => 'cloudflare_credentials',
						'page'        => 'cloudflare',
					],
				]
			);
		}

		$this->settings->add_settings_fields(
			[
				'cloudflare_email'            => [
					'label'           => _x( 'Account email', 'Cloudflare', 'rocket' ),
					'container_class' => [
						'wpr-field--split',
					],
					'default'         => '',
					'section'         => 'cloudflare_credentials',
					'page'            => 'cloudflare',
				],
				'cloudflare_domain'           => [
					'label'           => _x( 'Domain', 'Cloudflare', 'rocket' ),
					'container_class' => [
						'wpr-field--split',
					],
					'default'         => '',
					'section'         => 'cloudflare_credentials',
					'page'            => 'cloudflare',
				],
				'cloudflare_devmode'          => [
					'type'              => 'sliding_checkbox',
					'label'             => __( 'Development mode', 'rocket' ),
					'description'       => __( 'Temporarily activate development mode on your website. This setting will automatically turn off after 3 hours. Learn more', 'rocket' ),
					'default'           => 0,
					'section'           => 'cloudflare_settings',
					'page'              => 'cloudflare',
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'cloudflare_auto_settings'    => [
					'type'              => 'sliding_checkbox',
					'label'             => __( 'Optimal settings', 'rocket' ),
					'description'       => __( 'Automatically enhances your Cloudflare configuration for speed, performance grade and compatibility.', 'rocket' ),
					'default'           => 0,
					'section'           => 'cloudflare_settings',
					'page'              => 'cloudflare',
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'cloudflare_protocol_rewrite' => [
					'type'              => 'sliding_checkbox',
					'label'             => __( 'Relative protocol', 'rocket' ),
					'description'       => __( 'Should only be used with Cloudflare\'s flexible SSL feature. URLs of static files (CSS, JS, images) will be rewritten to use // instead of http:// or https://.', 'rocket' ),
					'default'           => 0,
					'section'           => 'cloudflare_settings',
					'page'              => 'cloudflare',
					'sanitize_callback' => 'sanitize_checkbox',
				],
			]
		);
	}

	/**
	 * Sets hidden fields
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	private function hidden_fields() {
		$this->settings->add_hidden_settings_fields(
			[
				'consumer_key',
				'consumer_email',
				'secret_key',
				'license',
				'secret_cache_key',
				'minify_css_key',
				'minify_js_key',
				'version',
				'cloudflare_old_settings',
				'cloudflare_zone_id',
				'sitemap_preload_url_crawl',
			]
		);
	}

	/**
	 * Add Tools section to navigation
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param array $navigation Array of menu items.
	 * @return array
	 */
	public function add_menu_tools_page( $navigation ) {
		$navigation['tools'] = [
			'id'               => 'tools',
			'title'            => __( 'Tools', 'rocket' ),
			'menu_description' => __( 'Import, Export, Rollback', 'rocket' ),
		];

		return $navigation;
	}

	/**
	 * Returns the IDs for the HelpScout docs for the corresponding section and language.
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param string $doc_id   Section identifier.
	 * @param string $lang     Documentation language.
	 *
	 * @return string
	 */
	private function get_beacon_suggest( $doc_id, $lang = 'en' ) {
		$suggest = [
			'faq'                    => [
				'en' => [
					[
						'id'    => '5569b671e4b027e1978e3c51',
						'url'   => 'https://docs.wp-rocket.me/article/99-pages-are-not-cached-or-css-and-js-minification-are-not-working',
						'title' => 'Pages Are Not Cached or CSS and JS Minification Are Not Working',
					],
					[
						'id'    => '556778c8e4b01a224b426fad',
						'url'   => 'https://docs.wp-rocket.me/article/85-google-page-speed-grade-does-not-improve',
						'title' => 'Google PageSpeed Grade does not Improve',
					],
					[
						'id'    => '556ef48ce4b01a224b428691',
						'url'   => 'https://docs.wp-rocket.me/article/106-my-site-is-broken',
						'title' => 'My Site Is Broken',
					],
					[
						'id'    => '54205957e4b099def9b55df0',
						'url'   => 'https://docs.wp-rocket.me/article/19-resolving-issues-with-file-optimization',
						'title' => 'Resolving Issues with File Optimization',
					],
				],
				'fr' => [
					[
						'id'    => '5697d2dc9033603f7da31041',
						'url'   => 'https://fr.docs.wp-rocket.me/article/264-les-pages-ne-sont-pas-mises-en-cache-ou-la-minification-css-et-js-ne-fonctionne-pas',
						'title' => 'Les pages ne sont pas mises en cache, ou la minification CSS et JS ne fonctionne pas',
					],
					[
						'id'    => '569564dfc69791436155e0b0',
						'url'   => 'https://fr.docs.wp-rocket.me/article/218-la-note-google-page-speed-ne-sameliore-pas',
						'title' => "La note Google Page Speed ne s'améliore pas",
					],
					[
						'id'    => '5697d03bc69791436155ed69',
						'url'   => 'https://fr.docs.wp-rocket.me/article/263-site-casse',
						'title' => 'Mon site est cassé',
					],
					[
						'id'    => '56967d73c69791436155e637',
						'url'   => 'https://fr.docs.wp-rocket.me/article/241-problemes-minification',
						'title' => "Résoudre les problèmes avec l'optimisation des fichiers",
					],
				],
			],
			'user_cache_section'     => [
				'en' => '56b55ba49033600da1c0b687,587920b5c697915403a0e1f4,560c66b0c697917e72165a6d',
				'fr' => '56cb9ba990336008e9e9e3d9,5879230cc697915403a0e211,569410999033603f7da2fa94',
			],
			'user_cache'             => [
				'en' => [
					'id'  => '56b55ba49033600da1c0b687',
					'url' => 'http://docs.wp-rocket.me/article/313-user-cache',
				],
				'fr' => [
					'id'  => '56cb9ba990336008e9e9e3d9',
					'url' => 'https://fr.docs.wp-rocket.me/article/333-cache-utilisateurs-connectes',
				],
			],
			'mobile_cache_section'   => [
				'en' => '577a5f1f903360258a10e52a,5678aa76c697914361558e92,5745b9a6c697917290ddc715',
				'fr' => '589b17a02c7d3a784630b249,5a6b32830428632faf6233dc,58a480e5dd8c8e56bfa7b85c',
			],
			'mobile_cache'           => [
				'en' => [
					'id'  => '577a5f1f903360258a10e52a',
					'url' => 'https://docs.wp-rocket.me/article/708-mobile-caching',
				],
				'fr' => [
					'id'  => '589b17a02c7d3a784630b249',
					'url' => 'http://fr.docs.wp-rocket.me/article/934-mise-en-cache-pour-mobile',
				],
			],
			'cache_lifespan'         => [
				'en' => '555c7e9ee4b027e1978e17a5,5922fd0e0428634b4a33552c',
				'fr' => '568f7df49033603f7da2ec72,598080e1042863033a1b890e',
			],
			'nonce'                  => [
				'en' => [
					'id'  => '5922fd0e0428634b4a33552c',
					'url' => 'http://docs.wp-rocket.me/article/975-nonces-and-cache-lifespan',
				],
				'fr' => [
					'id'  => '598080e1042863033a1b890e',
					'url' => 'http://fr.docs.wp-rocket.me/article/1015-nonces-delai-nettoyage-cache',
				],
			],
			'basic_section'          => [
				'en' => '55231415e4b0221aadf25676,588286b32c7d3a4a60b95b6c,58869c492c7d3a7846303a3d',
				'fr' => '569568269033603f7da30334,58e3be72dd8c8e5c57311c6e,59b7f049042863033a1cc5d0',
			],
			'css_section'            => [
				'en' => '54205957e4b099def9b55df0,5419ec47e4b099def9b5565f,5578cfbbe4b027e1978e6bb1,5569b671e4b027e1978e3c51,5923772c2c7d3a074e8ab8b9',
				'fr' => '56967d73c69791436155e637,56967e80c69791436155e646,56957209c69791436155e0f6,5697d2dc9033603f7da31041593fec6d2c7d3a0747cddb93',
			],
			'js_section'             => [
				'en' => '54205957e4b099def9b55df0,5419ec47e4b099def9b5565f,5578cfbbe4b027e1978e6bb1,587904cf90336009736c678e,54b9509de4b07997ea3f27c7,59236dfb0428634b4a3358f9',
				'fr' => '56967d73c69791436155e637,56967e80c69791436155e646,56957209c69791436155e0f6,58a337c12c7d3a576d352cde,56967eebc69791436155e649,593fe9882c7d3a0747cddb77',
			],
			'remove_query_strings'   => [
				'en' => [
					'id'  => '55231415e4b0221aadf25676',
					'url' => 'https://docs.wp-rocket.me/article/56-remove-query-string-from-static-resources',
				],
				'fr' => [
					'id'  => '569568269033603f7da30334',
					'url' => 'https://fr.docs.wp-rocket.me/article/219-supprimer-les-chaines-de-requetes-sur-les-ressources-statiques',
				],
			],
			'combine'                => [
				'en' => [
					'id'  => '596eaf7d2c7d3a73488b3661',
					'url' => 'https://docs.wp-rocket.me/article/1009-configuration-for-http-2',
				],
				'fr' => [
					'id'  => '59a418ad042863033a1c572e',
					'url' => 'https://fr.docs.wp-rocket.me/article/1018-configuration-http-2',
				],
			],
			'defer'                  => [
				'en' => [
					'id'  => '5578cfbbe4b027e1978e6bb1',
					'url' => 'http://docs.wp-rocket.me/article/108-render-blocking-javascript-and-css-pagespeed',
				],
				'fr' => [
					'id'  => '56957209c69791436155e0f6',
					'url' => 'http://fr.docs.wp-rocket.me/article/230-javascript-et-css-bloquant-le-rendu-pagespeed',
				],
			],
			'lazyload'               => [
				'en' => '54b85754e4b0512429883a86,5418c792e4b0e7b8127bed99,569ec4a69033603f7da32c93,5419e246e4b099def9b5561e,5a299b332c7d3a1a640cb402',
				'fr' => '56967a859033603f7da30858,56967952c69791436155e60a,56cb9c9d90336008e9e9e3dc,569676ea9033603f7da3083d,5a3a66f52c7d3a1943676524',
			],
			'sitemap_preload'        => [
				'en' => '541780fde4b005ed2d11784c,5a71c8ab2c7d3a4a4198a9b3,55b282ede4b0b0593824f852',
				'fr' => '5693d582c69791436155d645',
			],
			'preload_bot'            => [
				'en' => '541780fde4b005ed2d11784c,55b282ede4b0b0593824f852,559113eae4b027e1978eba11',
				'fr' => '5693d582c69791436155d645,569433d1c69791436155d99c',
			],
			'bot'                    => [
				'en' => [
					'id'  => '541780fde4b005ed2d11784c',
					'url' => 'http://docs.wp-rocket.me/article/8-how-the-cache-is-preloaded',
				],
				'fr' => [
					'id'  => '5693d582c69791436155d645',
					'url' => 'http://fr.docs.wp-rocket.me/article/188-comment-est-pre-charge-le-cache',
				],
			],
			'dns_prefetch'           => [
				'en' => '541780fde4b005ed2d11784c',
				'fr' => '5693d582c69791436155d645',
			],
			'never_cache'            => [
				'en' => '5519ab03e4b061031402119f,559110d0e4b027e1978eba09,56b55ba49033600da1c0b687,553ac7bfe4b0eb143c62af44,587920b5c697915403a0e1f4,5569b671e4b027e1978e3c51',
				'fr' => '56941c0cc69791436155d8ab,56943395c69791436155d99a,56cb9ba990336008e9e9e3d9,56942fc3c69791436155d987,5879230cc697915403a0e211,5697d2dc9033603f7da31041',
			],
			'always_purge'           => [
				'en' => '555c7e9ee4b027e1978e17a,55151406e4b0610314020a3f,5632858890336002f86d903e,5792c0c1903360293603896b',
				'fr' => '568f7df49033603f7da2ec72,5694194d9033603f7da2fb00,56951208c69791436155de2a,57a4a0c3c697910783242008',
			],
			'query_strings'          => [
				'en' => '590a83610428634b4a32d52c',
				'fr' => '597a04fd042863033a1b6da4',
			],
			'ecommerce'              => [
				'en' => [
					'id'  => '555c619ce4b027e1978e1767',
					'url' => 'http://docs.wp-rocket.me/article/75-is-wp-rocket-compatible-with-e-commerce-plugins',
				],
				'fr' => [
					'id'  => '568f8291c69791436155caea',
					'url' => 'https://fr.docs.wp-rocket.me/article/176-compatibilite-extensions-e-commerce',
				],
			],
			'cache_query_strings'    => [
				'en' => [
					'id'  => '590a83610428634b4a32d52c',
					'url' => 'http://docs.wp-rocket.me/article/971-caching-query-strings',
				],
				'fr' => [
					'id'  => '597a04fd042863033a1b6da4',
					'url' => 'http://fr.docs.wp-rocket.me/article/1014-cache-query-strings',
				],
			],
			'cleanup'                => [
				'en' => '55dcaa28e4b01d7a6a9bd373,578cd762c6979160ca1441cd,5569d11ae4b01a224b427725',
				'fr' => '5697cebbc69791436155ed5e,58b6e7a0dd8c8e56bfa819f5,5697cd85c69791436155ed50',
			],
			'cdn'                    => [
				'en' => '54c7fa3de4b0512429885b5c,54205619e4b0e7b8127bf849,54a6d578e4b047ebb774a687,56b2b4459033603f7da37acf,566f749f9033603f7da28459,5434667fe4b0310ce5ee867a',
				'fr' => '5696830b9033603f7da308ac,5696837e9033603f7da308ae,569685749033603f7da308c0,57a4961190336059d4edc9d8,5697d5f8c69791436155ed8e,569684d29033603f7da308b9',
			],
			'exclude_cdn'            => [
				'en' => '5434667fe4b0310ce5ee867a',
				'fr' => '569684d29033603f7da308b9',
			],
			'cloudflare_credentials' => [
				'en' => '54205619e4b0e7b8127bf849',
				'fr' => '5696837e9033603f7da308ae',
			],
			'cloudflare_settings'    => [
				'en' => '54205619e4b0e7b8127bf849',
				'fr' => '5696837e9033603f7da308ae',
			],
			'varnish'                => [
				'en' => [
					'id'  => '56f48132c6979115a34095bd',
					'url' => 'http://docs.wp-rocket.me/article/493-using-varnish-with-wp-rocket',
				],
				'fr' => [
					'id'  => '56fd2f789033601d6683e574',
					'url' => 'http://fr.docs.wp-rocket.me/article/512-varnish-wp-rocket-2-7',
				],
			],
		];

		return isset( $suggest[ $doc_id ][ $lang ] ) ? $suggest[ $doc_id ][ $lang ] : $suggest[ $doc_id ]['en'];
	}
}
