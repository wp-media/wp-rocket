<?php
namespace WP_Rocket\Admin\Settings;

use WP_Rocket\Event_Management\Subscriber_Interface;

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Registers the admin page and WP Rocket settings
 *
 * @since 3.0
 * @author Remy Perona
 */
class Page implements Subscriber_Interface {
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
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'admin_menu'                                        => 'add_admin_page',
			'admin_init'                                        => 'configure',
			'admin_print_footer_scripts-settings_page_wprocket' => 'insert_beacon',
			'wp_ajax_rocket_refresh_customer_data'              => 'refresh_customer_data',
			'wp_ajax_rocket_toggle_option'                      => 'toggle_option',
			'option_page_capability_' . WP_ROCKET_PLUGIN_SLUG   => 'required_capability',
			'rocket_settings_menu_navigation'                   => 'add_menu_tools_page',
			'pre_get_rocket_option_cache_mobile'                => 'is_mobile_plugin_active',
			'pre_get_rocket_option_do_caching_mobile_files'     => 'is_mobile_plugin_active',
		];
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
				HS.beacon.identify(' . wp_json_encode( $this->beacon_identify_data() ) . ');
			} );
			HS.beacon.config({
				showSubject: true,
				translation: ' . $translation . '
			});</script>';

		echo $script;
	}

	/**
	 * Returns Data to pass to the Beacon identify() method
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return array
	 */
	private function beacon_identify_data() {
		global $wp_version;

		$options_to_send = [
			'cache_mobile'            => 'Mobile Cache',
			'do_caching_mobile_files' => 'Specific Cache for Mobile',
			'cache_logged_user'       => 'User Cache',
			'emoji'                   => 'Disable Emojis',
			'embeds'                  => 'Disable Embeds',
			'defer_all_js'            => 'Defer JS',
			'defer_all_js_safe'       => 'Defer JS Safe',
			'async_css'               => 'Optimize CSS Delivery',
			'lazyload'                => 'Lazyload Images',
			'lazyload_iframes'        => 'Lazyload Iframes',
			'lazyload_youtube'        => 'Lazyload Youtube',
			'minify_css'              => 'Minify CSS',
			'minify_concatenate_css'  => 'Combine CSS',
			'minify_js'               => 'Minify JS',
			'minify_concatenate_js'   => 'Combine JS',
			'minify_google_fonts'     => 'Combine Google Fonts',
			'minify_html'             => 'Minify HTML',
			'manual_preload'          => 'Manual Preload',
			'automatic_preload'       => 'Automatic Preload',
			'sitemap_preload'         => 'Sitemap Preload',
			'remove_query_strings'    => 'Remove Query Strings',
			'cdn'                     => 'CDN Enabled',
			'do_cloudflare'           => 'Cloudflare Enabled',
			'varnish_auto_purge'      => 'Varnish Purge Enabled',
		];

		$active_options = array_filter( (array) get_option( WP_ROCKET_SLUG ) );
		$active_options = array_intersect_key( $options_to_send, $active_options );

		$data = [
			'email'                    => get_rocket_option( 'consumer_email' ),
			'Website'                  => home_url(),
			'WordPress Version'        => $wp_version,
			'WP Rocket Version'        => WP_ROCKET_VERSION,
			'Plugins Enabled'          => implode( ' - ', rocket_get_active_plugins() ),
			'WP Rocket Active Options' => implode( ' - ', $active_options ),
		];

		return $data;
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
				'body' => 'user_id=' . rawurlencode( $customer_email ) . '&consumer_key=' . $customer_key,
			]
		);

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return (object) [
				'licence_account'    => __( 'Unavailable', 'rocket' ),
				'licence_expiration' => __( 'Unavailable', 'rocket' ),
				'class'              => 'wpr-isInvalid',
			];
		}

		$customer_data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( 1 <= $customer_data->licence_account && $customer_data->licence_account < 3 ) {
			$customer_data->licence_account = 'Single';
		} elseif ( '-1' === $customer_data->licence_account ) {
			$customer_data->licence_account = 'Infinite';
		} else {
			$customer_data->licence_account = 'Plus';
		}

		$customer_data->class              = time() < $customer_data->licence_expiration ? 'wpr-isValid' : 'wpr-isInvalid';
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
	 * Toggle sliding checkboxes option value
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @return void
	 */
	public function toggle_option() {
		check_ajax_referer( 'rocket-ajax' );

		if ( ! current_user_can( apply_filters( 'rocket_capability', 'manage_options' ) ) ) {
			wp_die();
		}

		$whitelist = [
			'do_beta'                     => 1,
			'analytics_enabled'           => 1,
			'debug_enabled'               => 1,
			'varnish_auto_purge'          => 1,
			'do_cloudflare'               => 1,
			'cloudflare_devmode'          => 1,
			'cloudflare_protocol_rewrite' => 1,
			'cloudflare_auto_settings'    => 1,
			'google_analytics_cache'      => 1,
		];

		if ( ! isset( $_POST['option']['name'] ) || ! isset( $whitelist[ $_POST['option']['name'] ] ) ) {
			wp_die();
		}

		$value = (int) ! empty( $_POST['option']['value'] );

		update_rocket_option( $_POST['option']['name'], $value );

		wp_die();
	}

	/**
	 * Forces the value for the mobile options if a mobile plugin is active
	 *
	 * @since 3.0
	 * @author Remy Perona
	 *
	 * @param mixed $value Option value.
	 *
	 * @return mixed
	 */
	public function is_mobile_plugin_active( $value ) {
		if ( rocket_is_mobile_plugin_active() ) {
			return 1;
		}

		return $value;
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
				'title' => __( 'License', 'rocket' ),
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
					'default'           => '',
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
					'default'           => '',
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
		$cache_life_beacon   = $this->get_beacon_suggest( 'cache_lifespan', $this->locale );
		$cache_ssl_beacon    = $this->get_beacon_suggest( 'cache_ssl', $this->locale );

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
					'help'        => [
						'url' => $mobile_cache_beacon['url'],
						'id'  => $this->get_beacon_suggest( 'mobile_cache_section', $this->locale ),
					],
					'helper'      => rocket_is_mobile_plugin_active() ? __( 'We detected you use a plugin that requires a separate cache for mobile, and automatically enabled this option for compatibility.', 'rocket' ) : '',
					'page'        => 'cache',
				],
				'user_cache_section'   => [
					'title'       => __( 'User Cache', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description' => sprintf( __( '%1$sUser cache%2$s is great when you have user-specific or restricted content on your website.', 'rocket' ), '<a href="' . esc_url( $user_cache_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $user_cache_beacon['id'] ) . '" target="_blank">', '</a>' ),
					'help'        => [
						'url' => $user_cache_beacon['url'],
						'id'  => $this->get_beacon_suggest( 'user_cache_section', $this->locale ),
					],
					'page'        => 'cache',
				],
				'cache_ssl_section'    => [
					'title'       => __( 'SSL Cache', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description' => sprintf( __( '%1$sSSL Cache%2$s works best when your entire website runs on HTTPS.', 'rocket' ), '<a href="' . esc_url( $cache_ssl_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $cache_ssl_beacon['id'] ) . '" target="_blank">', '</a>' ),
					'class'       => [
						rocket_is_ssl_website() ? 'wpr-isHidden' : '',
					],
					'help'        => [
						'url' => $cache_ssl_beacon['url'],
						'id'  => $cache_ssl_beacon['id'],
					],
					'page'        => 'cache',
				],
				'cache_lifespan'       => [
					'title'       => __( 'Cache Lifespan', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description' => sprintf( __( 'Cache lifespan is the period of time after which all cache files are removed.<br>Enable %1$spreloading%2$s for the cache to be rebuilt automatically after lifespan expiration.', 'rocket' ), '<a href="#preload">', '</a>' ),
					'help'        => [
						'url' => $cache_life_beacon['url'],
						'id'  => $this->get_beacon_suggest( 'cache_lifespan_section', $this->locale ),
					],
					'page'        => 'cache',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'cache_logged_user'       => [
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
					'description'       => sprintf( __( '%1$sMobile cache%2$s works safest with both options enabled. When in doubt, keep both.', 'rocket' ), '<a href="' . esc_url( $mobile_cache_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $mobile_cache_beacon['id'] ) . '" target="_blank">', '</a>' ),
					'container_class'   => [
						rocket_is_mobile_plugin_active() ? 'wpr-isDisabled' : '',
						'wpr-field--children',
					],
					'parent'            => 'cache_mobile',
					'section'           => 'mobile_cache_section',
					'page'              => 'cache',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
					'input_attr'        => [
						'disabled' => rocket_is_mobile_plugin_active() ? 1 : 0,
					],
				],
				'cache_ssl'               => [
					'type'              => 'checkbox',
					'label'             => __( 'Enable caching for pages with <code>https://</code>', 'rocket' ),
					'section'           => 'cache_ssl_section',
					'page'              => 'cache',
					'default'           => rocket_is_ssl_website() ? 1 : 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'purge_cron_interval'     => [
					'type'              => 'cache_lifespan',
					'label'             => __( 'Specify time after which the global cache is cleared<br>(0 = unlimited )', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Reduce lifespan to 10 hours or less if you notice issues that seem to appear periodically. %1$sWhy?%2$s', 'rocket' ), '<a href="' . esc_url( $nonce_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $nonce_beacon['id'] ) . '" target="_blank">', '</a>' ),
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
		$remove_qs_beacon  = $this->get_beacon_suggest( 'remove_query_strings', $this->locale );
		$combine_beacon    = $this->get_beacon_suggest( 'combine', $this->locale );
		$defer_beacon      = $this->get_beacon_suggest( 'defer', $this->locale );
		$files_beacon      = $this->get_beacon_suggest( 'file_optimization', $this->locale );
		$inline_js_beacon  = $this->get_beacon_suggest( 'exclude_inline_js', $this->locale );
		$exclude_js_beacon = $this->get_beacon_suggest( 'exclude_js', $this->locale );

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
					'title'  => __( 'Basic Settings', 'rocket' ),
					'help'   => [
						'url' => $remove_qs_beacon['url'],
						'id'  => $this->get_beacon_suggest( 'basic_section', $this->locale ),
					],
					'page'   => 'file_optimization',
					// translators: %1$s = type of minification (HTML, CSS or JS), %2$s = “WP Rocket”.
					'helper' => rocket_maybe_disable_minify_html() ? sprintf( __( '%1$s Minification is currently activated in <strong>Autoptimize</strong>. If you want to use %2$s’s minification, disable those options in Autoptimize.', 'rocket' ), 'HTML', WP_ROCKET_PLUGIN_NAME ) : '',
				],
				'css'   => [
					'title'  => __( 'CSS Files', 'rocket' ),
					'help'   => [
						'id'  => $this->get_beacon_suggest( 'css_section', $this->locale ),
						'url' => $files_beacon['url'],
					],
					'page'   => 'file_optimization',
					// translators: %1$s = type of minification (HTML, CSS or JS), %2$s = “WP Rocket”.
					'helper' => rocket_maybe_disable_minify_css() ? sprintf( __( '%1$s Minification is currently activated in <strong>Autoptimize</strong>. If you want to use %2$s’s minification, disable those options in Autoptimize.', 'rocket' ), 'CSS', WP_ROCKET_PLUGIN_NAME ) : '',
				],
				'js'    => [
					'title'  => __( 'JavaScript Files', 'rocket' ),
					'help'   => [
						'id'  => $this->get_beacon_suggest( 'js_section', $this->locale ),
						'url' => $files_beacon['url'],
					],
					'page'   => 'file_optimization',
					// translators: %1$s = type of minification (HTML, CSS or JS), %2$s = “WP Rocket”.
					'helper' => rocket_maybe_disable_minify_js() ? sprintf( __( '%1$s Minification is currently activated in <strong>Autoptimize</strong>. If you want to use %2$s’s minification, disable those options in Autoptimize.', 'rocket' ), 'JS', WP_ROCKET_PLUGIN_NAME ) : '',
				],
			]
		);

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
					'description'       => sprintf( __( 'Removes the version query string from static files (e.g. style.css?ver=1.0) and encodes it into the filename instead (e.g. style-1.0.css). Can improve your GTMetrix score. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $remove_qs_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $remove_qs_beacon['id'] ) . '" target="_blank">', '</a>' ),
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
					'label'             => __( 'Combine CSS files <em>(Enable Minify CSS files to select)</em>', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Combine CSS merges all your files into 1, reducing HTTP requests. Not recommended if your site uses HTTP/2. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $combine_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $combine_beacon['id'] ) . '" target="_blank">', '</a>' ),
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
				'exclude_css'            => [
					'type'              => 'textarea',
					'label'             => __( 'Excluded CSS Files', 'rocket' ),
					'description'       => __( 'Specify URLs of CSS files to be excluded from minification and concatenation (one per line).', 'rocket' ),
					'helper'            => __( 'The domain part of the URL will be stripped automatically.<br>Use (.*).css wildcards to exclude all CSS files located at a specific path.', 'rocket' ),
					'container_class'   => [
						'wpr-field--children',
					],
					'placeholder'       => '/wp-content/plugins/some-plugin/(.*).css',
					'parent'            => 'minify_css',
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'async_css'              => [
					'type'              => 'checkbox',
					'label'             => __( 'Optimize CSS delivery', 'rocket' ),
					'container_class'   => [
						'wpr-isParent',
					],
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Optimize CSS delivery eliminates render-blocking CSS on your website for faster perceived load time. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $defer_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $defer_beacon['id'] ) . '" target="_blank">', '</a>' ),
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'critical_css'           => [
					'type'              => 'textarea',
					'label'             => __( 'Fallback critical CSS', 'rocket' ),
					'container_class'   => [
						'wpr-field--children',
					],
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'helper'            => sprintf( __( 'Provides a fallback if auto-generated critical path CSS is incomplete. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $defer_beacon['url'] ) . '#fallback" data-beacon-article="' . esc_attr( $defer_beacon['id'] ) . '" target="_blank">', '</a>' ),
					'parent'            => 'async_css',
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
					'label'             => __( 'Combine JavaScript files <em>(Enable Minify JavaScript files to select)</em>', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Combine JavaScript files combines your site’s internal, 3rd party and inline JS reducing HTTP requests. Not recommended if your site uses HTTP/2. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $combine_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $combine_beacon['id'] ) . '" target="_blank">', '</a>' ),
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
				'exclude_inline_js'  => [
					'type'              => 'textarea',
					'label'             => __( 'Excluded Inline JavaScript', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Specify patterns of inline JavaScript to be excluded from concatenation (one per line). %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $inline_js_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $inline_js_beacon['id'] ) . '" rel="noopener noreferrer" target="_blank">', '</a>' ),
					'container_class'   => [
						get_rocket_option( 'minify_concatenate_js' ) ? '' : 'wpr-isDisabled',
						'wpr-field--children',
					],
					'placeholder'       => 'recaptcha',
					'parent'            => 'minify_concatenate_js',
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
					'input_attr'        => [
						'disabled' => get_rocket_option( 'minify_concatenate_js' ) ? 0 : 1,
					],
				],
				'exclude_js'             => [
					'type'              => 'textarea',
					'label'             => __( 'Excluded JavaScript Files', 'rocket' ),
					'description'       => __( 'Specify URLs of JavaScript files to be excluded from minification and concatenation (one per line).', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'helper'            => __( '<strong>Internal:</strong> The domain part of the URL will be stripped automatically. Use (.*).js wildcards to exclude all JS files located at a specific path.', 'rocket' ) . '<br>' .
					sprintf( __( '<strong>3rd Party:</strong> Use URL full path, including domain name, to exclude external JS. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $exclude_js_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $exclude_js_beacon['id'] ) . '" rel="noopener noreferrer" target="_blank">', '</a>' ),
					'container_class'   => [
						'wpr-field--children',
					],
					'placeholder'       => '/wp-content/themes/some-theme/(.*).js',
					'parent'            => 'minify_js',
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'defer_all_js'           => [
					'container_class'   => [
						'wpr-isParent',
					],
					'type'              => 'checkbox',
					'label'             => __( 'Load JavaScript deferred', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'Load JavaScript deferred eliminates render-blocking JS on your site and can improve load time. %1$sMore info%2$s', 'rocket' ), '<a href="' . esc_url( $defer_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $defer_beacon['id'] ) . '" target="_blank">', '</a>' ),
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
					'label'             => __( 'Safe Mode for jQuery (recommended)', 'rocket' ),
					'description'       => __( 'Safe mode for jQuery for deferred JS ensures support for inline jQuery references from themes and plugins by loading jQuery at the top of the document as a render-blocking script.<br><em>Deactivating may result in broken functionality, test thoroughly!</em>', 'rocket' ),
					'parent'            => 'defer_all_js',
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => 1,
					'sanitize_callback' => 'sanitize_checkbox',
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
		$lazyload_beacon = $this->get_beacon_suggest( 'lazyload', $this->locale );

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
					'help'        => [
						'id'  => $this->get_beacon_suggest( 'lazyload_section', $this->locale ),
						'url' => $lazyload_beacon['url'],
					],
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
					'parent'            => 'lazyload_iframes',
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
					'help'        => [
						'id'  => $this->get_beacon_suggest( 'sitemap_preload', $this->locale ),
						'url' => $bot_beacon['url'],
					],
					'page'        => 'preload',
				],
				'preload_bot_section'     => [
					'title'       => __( 'Preload Bot', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag, %3$s = opening <a> tag, %4$s = closing </a> tag.
					'description' => sprintf( __( '%1$sBot-based%2$s preloading should only be used on well-performing servers.<br>Once activated, it gets triggered automatically after you add or update content on your website.<br>You can also launch it manually from the upper toolbar menu, or from Quick Actions on the %3$sWP Rocket Dashboard%4$s.', 'rocket' ), '<a href="' . esc_url( $bot_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $bot_beacon['id'] ) . '" target="_blank">', '</a>', '<a href="#dashboard">', '</a>' ),
					'helper'      => __( 'Deactivate these options if you notice any overload on your server!', 'rocket' ),
					'help'        => [
						'id'  => $this->get_beacon_suggest( 'preload_bot', $this->locale ),
						'url' => $bot_beacon['url'],
					],
					'page'        => 'preload',
				],
				'dns_prefetch_section'    => [
					'title'       => __( 'Prefetch DNS Requests', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'DNS prefetching can make external files load faster, especially on mobile networks', 'rocket' ),
					'help'        => [
						'id'  => $this->get_beacon_suggest( 'dns_prefetch', $this->locale ),
						'url' => $bot_beacon['url'],
					],
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
					'placeholder'       => 'http://example.com/sitemap.xml',
					'parent'            => 'sitemap_preload',
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
					'description'       => __( 'Specify external hosts to be prefetched (no <code>http:</code>, one per line)', 'rocket' ),
					'placeholder'       => '//example.com',
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
		$never_cache_beacon         = $this->get_beacon_suggest( 'exclude_cache', $this->locale );
		$always_purge_beacon        = $this->get_beacon_suggest( 'always_purge', $this->locale );

		$ecommerce_plugin = '';
		$reject_uri_desc  = __( 'Sensitive pages like custom login/logout URLs should be excluded from cache.', 'rocket' );

		if ( function_exists( 'WC' ) && function_exists( 'wc_get_page_id' ) ) {
			$ecommerce_plugin = _x( 'WooCommerce', 'plugin name', 'rocket' );
		} elseif ( function_exists( 'EDD' ) ) {
			$ecommerce_plugin = _x( 'Easy Digital Downloads', 'plugin name', 'rocket' );
		} elseif ( function_exists( 'it_exchange_get_page_type' ) && function_exists( 'it_exchange_get_page_url' ) ) {
			$ecommerce_plugin = _x( 'iThemes Exchange', 'plugin name', 'rocket' );
		} elseif ( defined( 'JIGOSHOP_VERSION' ) && function_exists( 'jigoshop_get_page_id' ) ) {
			$ecommerce_plugin = _x( 'Jigoshop', 'plugin name', 'rocket' );
		} elseif ( defined( 'WPSHOP_VERSION' ) && class_exists( 'wpshop_tools' ) && method_exists( 'wpshop_tools', 'get_page_id' ) ) {
			$ecommerce_plugin = _x( 'WP-Shop', 'plugin name', 'rocket' );
		}

		if ( ! empty( $ecommerce_plugin ) ) {
			$reject_uri_desc .= sprintf(
					// translators: %1$s = opening <a> tag, %2$s = plugin name, %3$s closing </a> tag.
					__( '<br>Cart, checkout and "my account" pages set in <strong>%1$s%2$s%3$s</strong> will be detected and never cached by default.', 'rocket' ),
					'<a href="' . esc_url( $ecommerce_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $ecommerce_beacon['id'] ) . '" target="_blank">',
					$ecommerce_plugin,
					'</a>'
			);
		}

		$this->settings->add_settings_sections(
			[
				'cache_reject_uri_section'     => [
					'title'       => __( 'Never Cache URL(s)', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description' => $reject_uri_desc,
					'help'        => [
						'id'  => $this->get_beacon_suggest( 'never_cache', $this->locale ),
						'url' => $never_cache_beacon['url'],
					],
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
					'help'  => [
						'id'  => $this->get_beacon_suggest( 'always_purge_section', $this->locale ),
						'url' => $always_purge_beacon['url'],
					],
					'page'  => 'advanced_cache',
				],
				'cache_query_strings_section'  => [
					'title'       => __( 'Cache Query String(s)', 'rocket' ),
					'type'        => 'fields_container',
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description' => sprintf( __( '%1$sCache for query strings%2$s enables you to force caching for specific GET parameters.', 'rocket' ), '<a href="' . esc_url( $cache_query_strings_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $cache_query_strings_beacon['id'] ) . '" target="_blank">', '</a>' ),
					'help'        => [
						'id'  => $this->get_beacon_suggest( 'query_strings', $this->locale ),
						'url' => $cache_query_strings_beacon['url'],
					],
					'page'        => 'advanced_cache',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'cache_reject_uri'     => [
					'type'              => 'textarea',
					'description'       => __( 'Specify URLs of pages or posts that should never be cached (one per line)', 'rocket' ),
					'helper'            => __( 'The domain part of the URL will be stripped automatically.<br>Use (.*) wildcards to address multiple URLs under a given path.', 'rocket' ),
					'placeholder'       => '/members/(.*)',
					'section'           => 'cache_reject_uri_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_reject_cookies' => [
					'type'              => 'textarea',
					'description'       => __( 'Specify the IDs of cookies that, when set in the visitor\'s browser, should prevent a page from getting cached (one per line)', 'rocket' ),
					'section'           => 'cache_reject_cookies_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_reject_ua'      => [
					'type'              => 'textarea',
					'description'       => __( 'Specify user agent strings that should never see cached pages (one per line)', 'rocket' ),
					'helper'            => __( 'Use (.*) wildcards to detect parts of UA strings.', 'rocket' ),
					'placeholder'       => '(.*)Mobile(.*)Safari(.*)',
					'section'           => 'cache_reject_ua_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_purge_pages'    => [
					'type'              => 'textarea',
					'description'       => __( 'Specify URLs you always want purged from cache whenever you update any post or page (one per line)', 'rocket' ),
					'helper'            => __( 'The domain part of the URL will be stripped automatically.<br>Use (.*) wildcards to address multiple URLs under a given path.', 'rocket' ),
					'section'           => 'cache_purge_pages_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_query_strings'  => [
					'type'              => 'textarea',
					'description'       => __( 'Specify query strings for caching (one per line)', 'rocket' ),
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

		foreach ( array_keys( $database_optimization->get_options() ) as $key ) {
			$total[ $key ] = $database_optimization->count_cleanup_items( $key );
		}

		$this->settings->add_page_section(
			'database',
			[
				'title'            => __( 'Database', 'rocket' ),
				'menu_description' => __( 'Optimize, reduce bloat', 'rocket' ),
			]
		);

		$database_beacon = $this->get_beacon_suggest( 'slow_admin', $this->locale );

		$this->settings->add_settings_sections(
			[
				'post_cleanup_section'       => [
					'title'       => __( 'Post Cleanup', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'Post revisions and drafts will be permanently deleted. Do not use this option if you need to retain revisions or drafts.', 'rocket' ),
					'help'        => [
						'id'  => $this->get_beacon_suggest( 'cleanup', $this->locale ),
						'url' => $database_beacon['url'],
					],
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
					'description'       => sprintf( _n( '%s revision in your database.', '%s revisions in your database.', $total['database_revisions'], 'rocket' ), number_format_i18n( $total['database_revisions'] ) ),
					'section'           => 'post_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_auto_drafts'        => [
					'type'              => 'checkbox',
					'label'             => __( 'Auto Drafts', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s draft in your database.', '%s drafts in your database.', $total['database_auto_drafts'], 'rocket' ), number_format_i18n( $total['database_auto_drafts'] ) ),
					'section'           => 'post_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_trashed_posts'      => [
					'type'              => 'checkbox',
					'label'             => __( 'Trashed Posts', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s trashed post in your database.', '%s trashed posts in your database.', $total['database_trashed_posts'], 'rocket' ), $total['database_trashed_posts'] ),
					'section'           => 'post_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_spam_comments'      => [
					'type'              => 'checkbox',
					'label'             => __( 'Spam Comments', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s spam comment in your database.', '%s spam comments in your database.', $total['database_spam_comments'], 'rocket' ), number_format_i18n( $total['database_spam_comments'] ) ),
					'section'           => 'comments_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_trashed_comments'   => [
					'type'              => 'checkbox',
					'label'             => __( 'Trashed Comments', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s trashed comment in your database.', '%s trashed comments in your database.', $total['database_trashed_comments'], 'rocket' ), number_format_i18n( $total['database_trashed_comments'] ) ),
					'section'           => 'comments_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_expired_transients' => [
					'type'              => 'checkbox',
					'label'             => __( 'Expired transients', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s expired transient in your database.', '%s expired transients in your database.', $total['database_expired_transients'], 'rocket' ), number_format_i18n( $total['database_expired_transients'] ) ),
					'section'           => 'transients_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_all_transients'     => [
					'type'              => 'checkbox',
					'label'             => __( 'All transients', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s transient in your database.', '%s transients in your database.', $total['database_all_transients'], 'rocket' ), number_format_i18n( $total['database_all_transients'] ) ),
					'section'           => 'transients_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_optimize_tables'    => [
					'type'              => 'checkbox',
					'label'             => __( 'Optimize Tables', 'rocket' ),
					// translators: %s is the number of revisions found in the database. It's a formatted number, don't use %d.
					'description'       => sprintf( _n( '%s table to optimize in your database.', '%s tables to optimize in your database.', $total['database_optimize_tables'], 'rocket' ), number_format_i18n( $total['database_optimize_tables'] ) ),
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
					'parent'            => 'schedule_automatic_cleanup',
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

		$cdn_beacon         = $this->get_beacon_suggest( 'cdn', $this->locale );
		$cdn_exclude_beacon = $this->get_beacon_suggest( 'exclude_cdn', $this->locale );

		$this->settings->add_settings_sections(
			[
				'cdn_section'         => [
					'title'       => __( 'CDN', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'All URLs of static files (CSS, JS, images) will be rewritten to the CNAME(s) you provide.', 'rocket' ),
					'help'        => [
						'id'  => $this->get_beacon_suggest( 'cdn_section', $this->locale ),
						'url' => $cdn_beacon['url'],
					],
					'page'        => 'page_cdn',
				],
				'cnames_section'      => [
					'type' => 'nocontainer',
					'page' => 'page_cdn',
				],
				'exclude_cdn_section' => [
					'title' => __( 'Exclude files from CDN', 'rocket' ),
					'type'  => 'fields_container',
					'help'  => [
						'id'  => $cdn_exclude_beacon['id'],
						'url' => $cdn_exclude_beacon['url'],
					],
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
					'section'     => 'cnames_section',
					'page'        => 'page_cdn',
				],
				'cdn_reject_files' => [
					'type'              => 'textarea',
					'description'       => __( 'Specify URL(s) of files that should not get served via CDN (one per line).', 'rocket' ),
					'helper'            => __( 'The domain part of the URL will be stripped automatically.<br>Use (.*) wildcards to exclude all files of a given file type located at a specific path.', 'rocket' ),
					'placeholder'       => '/wp-content/plugins/some-plugins/(.*).css',
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
					'description' => __( 'One-Click Add-ons are features extending available options without configuration needed. Switch the option "on" to enable from this screen.', 'rocket' ),
					'type'        => 'addons_container',
					'page'        => 'addons',
				],
			]
		);

		$this->settings->add_settings_sections(
			[
				'addons' => [
					'title'       => __( 'Rocket Add-ons', 'rocket' ),
					'description' => __( 'Rocket Add-ons are complementary features extending available options.', 'rocket' ),
					'type'        => 'addons_container',
					'page'        => 'addons',
				],
			]
		);

		$ga_beacon = $this->get_beacon_suggest( 'google_tracking', $this->locale );

		$this->settings->add_settings_fields(
			[
				'google_analytics_cache' => [
					'type'              => 'one_click_addon',
					'label'             => __( 'Google Tracking', 'rocket' ),
					'logo'              => [
						'url'    => WP_ROCKET_ASSETS_IMG_URL . 'logo-google-analytics.svg',
						'width'  => 153,
						'height' => 111,
					],
					'title'             => __( 'Improve browser caching for Google Analytics', 'rocket' ),
					// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
					'description'       => sprintf( __( 'WP Rocket will host these Google scripts locally on your server to help satisfy the PageSpeed recommendation for <em>Leverage browser caching</em>.<br>%1$sLearn more%2$s', 'rocket' ), '<a href="' . esc_url( $ga_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $ga_beacon['id'] ) . '" target="_blank">', '</a>' ),
					'section'           => 'one_click',
					'page'              => 'addons',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
			]
		);

		/**
		 * Allow to display the "Varnish" tab in the settings page
		 *
		 * @since 2.7
		 *
		 * @param bool $display true will display the "Varnish" tab.
		*/
		if ( apply_filters( 'rocket_display_varnish_options_tab', true ) ) {
			$varnish_beacon = $this->get_beacon_suggest( 'varnish', $this->locale );

			$this->settings->add_settings_fields(
				/**
				 * Filters the Varnish field settings data
				 *
				 * @since 3.0
				 * @author Remy Perona
				 *
				 * @param array $settings Field settings data.
				 */
				apply_filters( 'rocket_varnish_field_settings', [
					'varnish_auto_purge' => [
						'type'              => 'one_click_addon',
						'label'             => __( 'Varnish', 'rocket' ),
						'logo'              => [
							'url'    => WP_ROCKET_ASSETS_IMG_URL . 'logo-varnish.svg',
							'width'  => 152,
							'height' => 135,
						],
						'title'             => __( 'If Varnish runs on your server, you must activate this add-on.', 'rocket' ),
						// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
						'description'       => sprintf( __( 'Varnish cache will be purged each time WP Rocket clears its cache to ensure content is always up-to-date.<br>%1$sLearn more%2$s', 'rocket' ), '<a href="' . esc_url( $varnish_beacon['url'] ) . '" data-beacon-article="' . esc_attr( $varnish_beacon['id'] ) . '" target="_blank">', '</a>' ),
						'section'           => 'one_click',
						'page'              => 'addons',
						'default'           => 0,
						'sanitize_callback' => 'sanitize_textarea',
					],
				] )
			);
		}

		$this->settings->add_settings_fields(
			[
				'do_cloudflare' => [
					'type'              => 'rocket_addon',
					'label'             => __( 'Cloudflare', 'rocket' ),
					'logo'              => [
						'url'    => WP_ROCKET_ASSETS_IMG_URL . 'logo-cloudflare2.svg',
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
					'help'  => [
						'id'  => $this->get_beacon_suggest( 'cloudflare_credentials', $this->locale ),
						'url' => '',
					],
					'page'  => 'cloudflare',
				],
				'cloudflare_settings'    => [
					'type'  => 'fields_container',
					'title' => __( 'Cloudflare settings', 'rocket' ),
					'help'  => [
						'id'  => $this->get_beacon_suggest( 'cloudflare_settings', $this->locale ),
						'url' => '',
					],
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
					'default'         => '',
					'container_class'   => [
						'wpr-field--split',
					],
					'section'         => 'cloudflare_credentials',
					'page'            => 'cloudflare',
				],
				'cloudflare_zone_id'          => [
					'label'           => _x( 'Zone ID', 'Cloudflare', 'rocket' ),
					'default'         => '',
					'container_class'   => [
						'wpr-field--split',
					],
					'section'         => 'cloudflare_credentials',
					'page'            => 'cloudflare',
				],
				'cloudflare_devmode'          => [
					'type'              => 'sliding_checkbox',
					'label'             => __( 'Development mode', 'rocket' ),
					// translators: %1$s = link opening tag, %2$s = link closing tag.
					'description'       => sprintf( __( 'Temporarily activate development mode on your website. This setting will automatically turn off after 3 hours. %1$sLearn more%2$s', 'rocket' ), '<a href="https://support.cloudflare.com/hc/en-us/articles/200168246" target="_blank">', '</a>' ),
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
						'url'   => 'https://docs.wp-rocket.me/article/99-pages-are-not-cached-or-css-and-js-minification-are-not-working/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Pages Are Not Cached or CSS and JS Minification Are Not Working',
					],
					[
						'id'    => '556778c8e4b01a224b426fad',
						'url'   => 'https://docs.wp-rocket.me/article/85-google-page-speed-grade-does-not-improve/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Google PageSpeed Grade does not Improve',
					],
					[
						'id'    => '556ef48ce4b01a224b428691',
						'url'   => 'https://docs.wp-rocket.me/article/106-my-site-is-broken/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'My Site Is Broken',
					],
					[
						'id'    => '54205957e4b099def9b55df0',
						'url'   => 'https://docs.wp-rocket.me/article/19-resolving-issues-with-file-optimization/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Resolving Issues with File Optimization',
					],
				],
				'fr' => [
					[
						'id'    => '5697d2dc9033603f7da31041',
						'url'   => 'https://fr.docs.wp-rocket.me/article/264-les-pages-ne-sont-pas-mises-en-cache-ou-la-minification-css-et-js-ne-fonctionne-pas/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Les pages ne sont pas mises en cache, ou la minification CSS et JS ne fonctionne pas',
					],
					[
						'id'    => '569564dfc69791436155e0b0',
						'url'   => 'https://fr.docs.wp-rocket.me/article/218-la-note-google-page-speed-ne-sameliore-pas/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => "La note Google Page Speed ne s'améliore pas",
					],
					[
						'id'    => '5697d03bc69791436155ed69',
						'url'   => 'https://fr.docs.wp-rocket.me/article/263-site-casse/?utm_source=wp_plugin&utm_medium=wp_rocket',
						'title' => 'Mon site est cassé',
					],
					[
						'id'    => '56967d73c69791436155e637',
						'url'   => 'https://fr.docs.wp-rocket.me/article/241-problemes-minification/?utm_source=wp_plugin&utm_medium=wp_rocket',
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
					'url' => 'https://docs.wp-rocket.me/article/313-user-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56cb9ba990336008e9e9e3d9',
					'url' => 'https://fr.docs.wp-rocket.me/article/333-cache-utilisateurs-connectes/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'mobile_cache_section'   => [
				'en' => '577a5f1f903360258a10e52a,5678aa76c697914361558e92,5745b9a6c697917290ddc715',
				'fr' => '589b17a02c7d3a784630b249,5a6b32830428632faf6233dc,58a480e5dd8c8e56bfa7b85c',
			],
			'mobile_cache'           => [
				'en' => [
					'id'  => '577a5f1f903360258a10e52a',
					'url' => 'https://docs.wp-rocket.me/article/708-mobile-caching/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '589b17a02c7d3a784630b249',
					'url' => 'https://fr.docs.wp-rocket.me/article/934-mise-en-cache-pour-mobile/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_ssl'              => [
				'en' => [
					'id'  => '56c24fd3903360436857f1ed',
					'url' => 'https://docs.wp-rocket.me/article/314-using-ssl-with-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56cb9d24c6979102ccfc801c',
					'url' => 'https://fr.docs.wp-rocket.me/article/335-utiliser-ssl-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_lifespan'         => [
				'en' => [
					'id'  => '555c7e9ee4b027e1978e17a5',
					'url' => 'https://docs.wp-rocket.me/article/78-how-often-is-the-cache-updated/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '568f7df49033603f7da2ec72',
					'url' => 'https://fr.docs.wp-rocket.me/article/171-intervalle-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_lifespan_section' => [
				'en' => '555c7e9ee4b027e1978e17a5,5922fd0e0428634b4a33552c',
				'fr' => '568f7df49033603f7da2ec72,598080e1042863033a1b890e',
			],
			'nonce'                  => [
				'en' => [
					'id'  => '5922fd0e0428634b4a33552c',
					'url' => 'https://docs.wp-rocket.me/article/975-nonces-and-cache-lifespan/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '598080e1042863033a1b890e',
					'url' => 'https://fr.docs.wp-rocket.me/article/1015-nonces-delai-nettoyage-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
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
					'url' => 'https://docs.wp-rocket.me/article/56-remove-query-string-from-static-resources/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '569568269033603f7da30334',
					'url' => 'https://fr.docs.wp-rocket.me/article/219-supprimer-les-chaines-de-requetes-sur-les-ressources-statiques/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'file_optimization'      => [
				'en' => [
					'id'  => '54205957e4b099def9b55df0',
					'url' => 'https://docs.wp-rocket.me/article/19-resolving-issues-with-file-optimization/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56967d73c69791436155e637',
					'url' => 'https://fr.docs.wp-rocket.me/article/241-problemes-minification/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'combine'                => [
				'en' => [
					'id'  => '596eaf7d2c7d3a73488b3661',
					'url' => 'https://docs.wp-rocket.me/article/1009-configuration-for-http-2/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '59a418ad042863033a1c572e',
					'url' => 'https://fr.docs.wp-rocket.me/article/1018-configuration-http-2/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_inline_js'      => [
				'en' => [
					'id'  => '5b4879100428630abc0c0713',
					'url' => 'https://docs.wp-rocket.me/article/1104-excluding-inline-js-from-combine/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_js'             => [
				'en' => [
					'id'  => '54b9509de4b07997ea3f27c7',
					'url' => 'https://docs.wp-rocket.me/article/39-excluding-external-js-from-concatenation/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'defer'                  => [
				'en' => [
					'id'  => '5578cfbbe4b027e1978e6bb1',
					'url' => 'https://docs.wp-rocket.me/article/108-render-blocking-javascript-and-css-pagespeed/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56957209c69791436155e0f6',
					'url' => 'https://fr.docs.wp-rocket.me/article/230-javascript-et-css-bloquant-le-rendu-pagespeed/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'lazyload'               => [
				'en' => [
					'id'  => '54b85754e4b0512429883a86',
					'url' => 'https://docs.wp-rocket.me/article/38-lazyload-plugin-compatibility/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56967a859033603f7da30858',
					'url' => 'https://fr.docs.wp-rocket.me/article/237-compatibilite-des-extensions-avec-le-lazyload/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'lazyload_section'       => [
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
					'url' => 'https://docs.wp-rocket.me/article/8-how-the-cache-is-preloaded/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5693d582c69791436155d645',
					'url' => 'https://fr.docs.wp-rocket.me/article/188-comment-est-pre-charge-le-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
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
			'always_purge_section'   => [
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
					'url' => 'https://docs.wp-rocket.me/article/75-is-wp-rocket-compatible-with-e-commerce-plugins/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '568f8291c69791436155caea',
					'url' => 'https://fr.docs.wp-rocket.me/article/176-compatibilite-extensions-e-commerce/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cache_query_strings'    => [
				'en' => [
					'id'  => '590a83610428634b4a32d52c',
					'url' => 'https://docs.wp-rocket.me/article/971-caching-query-strings/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '597a04fd042863033a1b6da4',
					'url' => 'https://fr.docs.wp-rocket.me/article/1014-cache-query-strings/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_cache'          => [
				'en' => [
					'id'  => '5519ab03e4b061031402119f',
					'url' => 'https://docs.wp-rocket.me/article/54-exclude-pages-from-the-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56941c0cc69791436155d8ab',
					'url' => 'https://fr.docs.wp-rocket.me/article/196-exclure-pages-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'always_purge'           => [
				'en' => [
					'id'  => '555c7e9ee4b027e1978e17a5',
					'url' => 'https://docs.wp-rocket.me/article/78-how-often-is-the-cache-updated/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '568f7df49033603f7da2ec72',
					'url' => 'https://fr.docs.wp-rocket.me/article/171-intervalle-cache/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cleanup'                => [
				'en' => '55dcaa28e4b01d7a6a9bd373,578cd762c6979160ca1441cd,5569d11ae4b01a224b427725',
				'fr' => '5697cebbc69791436155ed5e,58b6e7a0dd8c8e56bfa819f5,5697cd85c69791436155ed50',
			],
			'slow_admin'             => [
				'en' => [
					'id'  => '55dcaa28e4b01d7a6a9bd373',
					'url' => 'https://docs.wp-rocket.me/article/121-wp-admin-area-is-slow/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5697cebbc69791436155ed5e',
					'url' => 'https://fr.docs.wp-rocket.me/article/260-la-zone-d-administration-wp-est-lente/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'cdn_section'            => [
				'en' => '54c7fa3de4b0512429885b5c,54205619e4b0e7b8127bf849,54a6d578e4b047ebb774a687,56b2b4459033603f7da37acf,566f749f9033603f7da28459,5434667fe4b0310ce5ee867a',
				'fr' => '5696830b9033603f7da308ac,5696837e9033603f7da308ae,569685749033603f7da308c0,57a4961190336059d4edc9d8,5697d5f8c69791436155ed8e,569684d29033603f7da308b9',
			],
			'cdn'                    => [
				'en' => [
					'id'  => '54c7fa3de4b0512429885b5c',
					'url' => 'https://docs.wp-rocket.me/article/42-using-wp-rocket-with-a-cdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '5696830b9033603f7da308ac',
					'url' => 'https://fr.docs.wp-rocket.me/article/246-utiliser-wp-rocket-avec-un-cdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'exclude_cdn'            => [
				'en' => [
					'id'  => '5434667fe4b0310ce5ee867a',
					'url' => 'https://docs.wp-rocket.me/article/24-resolving-issues-with-cdn-and-fonts-icons/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '569684d29033603f7da308b9',
					'url' => 'https://fr.docs.wp-rocket.me/article/248-resoudre-des-problemes-avec-cdn-et-les-polices-icones/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
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
					'url' => 'https://docs.wp-rocket.me/article/493-using-varnish-with-wp-rocket/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
				'fr' => [
					'id'  => '56fd2f789033601d6683e574',
					'url' => 'https://fr.docs.wp-rocket.me/article/512-varnish-wp-rocket-2-7/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
			'google_tracking'        => [
				'en' => [
					'id'  => '5b4693220428630abc0bf97b',
					'url' => 'https://docs.wp-rocket.me/article/1103-google-tracking-add-on/?utm_source=wp_plugin&utm_medium=wp_rocket',
				],
			],
		];

		return isset( $suggest[ $doc_id ][ $lang ] ) ? $suggest[ $doc_id ][ $lang ] : $suggest[ $doc_id ]['en'];
	}
}
