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
		add_filter( 'option_page_capability_' . $self->slug, [ $self, 'required_capability' ] );
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
		register_setting( $this->slug, $this->slug . '_settings', [ $this->settings, 'sanitize_callback' ] );

		$this->dashboard_section();
		$this->cache_section();
		$this->assets_section();
		$this->media_section();
		$this->preload_section();
		$this->advanced_cache_section();
		$this->database_section();
		$this->cdn_section();

		$this->render->set_settings( $this->settings->get_settings() );

		$this->hidden_fields();

		$this->render->set_hidden_settings( $this->settings->get_hidden_settings() );

		add_filter( 'rocket_settings_menu_navigation', [ $this, 'add_menu_tools_page' ] );
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
		return apply_filters( 'rocket_capacity', 'manage_options' );
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
			]
		);

		$this->settings->add_settings_sections(
			[
				'status' => [
					'title'       => __( 'My status', 'rocket' ),
					'type'        => 'fields_container',
					'help'        => '',
					'description' => '',
					'page'        => 'dashboard',
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
					'label'             => __( 'Rocket Sharer', 'rocket' ),
					'description'       => __( 'I agree to share anonymous data with the development team to help improve WP Rocket.', 'rocket' ),
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
					'help'        => '',
					'page'        => 'cache',
				],
				'user_cache_section'   => [
					'title'       => __( 'User Cache', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'User cache is great when you have user-specific or restricted content on your website.', 'rocket' ),
					'help'        => '1234',
					'page'        => 'cache',
				],
				'cache_lifespan'       => [
					'title'       => __( 'Cache Lifespan', 'rocket' ),
					'type'        => 'fields_container',
					'description' => __( 'Cache lifespan is the period of time after which all cache files are removed. Enable preloading for the cache to be rebuilt automatically after lifespan expiration.', 'rocket' ),
					'help'        => '',
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
					'class'             => rocket_is_mobile_plugin_active() ? 'disabled' : '',
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
					'parent'            => 'cache_mobile',
					'label'             => __( 'Separate cache files for mobile devices', 'rocket' ),
					'description'       => __( 'Mobile cache works safest with both options enabled. When in doubt, keep both.', 'rocket' ),
					'class'             => rocket_is_mobile_plugin_active() ? 'disabled' : '',
					'section'           => 'mobile_cache_section',
					'page'              => 'cache',
					'default'           => rocket_is_mobile_plugin_active() ? 1 : 0,
					'sanitize_callback' => 'sanitize_checkbox',
					'input_attr'        => [
						'disabled' => rocket_is_mobile_plugin_active() ? 1 : 0,
					],
				],
				'purge_cron_interval'     => [
					'type'              => 'number',
					'label'             => __( 'Clear cache after', 'rocket' ),
					'section'           => 'cache_lifespan',
					'page'              => 'cache',
					'default'           => 10,
					'sanitize_callback' => 'sanitize_text_field',
				],
				'purge_cron_unit'         => [
					'type'              => 'select',
					'label'             => __( 'Unit of time', 'rocket' ),
					'section'           => 'cache_lifespan',
					'page'              => 'cache',
					'default'           => 'HOUR_IN_SECONDS',
					'sanitize_callback' => 'sanitize_text_field',
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
				'title'            => __( 'File optimization', 'rocket' ),
				'menu_description' => __( 'Optimize CSS & JS', 'rocket' ),
			]
		);

		$this->settings->add_settings_sections(
			[
				'basic' => [
					'title'       => __( 'Basic Settings', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'file_optimization',
				],
				'css'   => [
					'title'       => __( 'CSS Files', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'file_optimization',
				],
				'js'    => [
					'title'       => __( 'JS Files', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'file_optimization',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'minify_html'            => [
					'type'              => 'checkbox',
					'label'             => __( 'Minify HTML', 'rocket' ),
					'class'             => rocket_maybe_disable_minify_html() ? 'disabled' : '',
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
					'label'             => __( 'Combine google fonts files', 'rocket' ),
					'section'           => 'basic',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'remove_query_strings'   => [
					'type'              => 'checkbox',
					'label'             => __( 'Remove query strings from static resources', 'rocket' ),
					'section'           => 'basic',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'minify_css'             => [
					'type'              => 'checkbox',
					'label'             => __( 'Minify CSS Files', 'rocket' ),
					'class'             => rocket_maybe_disable_minify_css() ? 'disabled' : '',
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
					'input_attr'        => [
						'disabled' => rocket_maybe_disable_minify_css() ? 1 : 0,
					],
					'warning'           => [
						'title'        => __( 'this could break things!', 'rocket' ),
						'description'  => '',
						'button_label' => __( 'Activate minify CSS', 'rocket' ),
					],
				],
				'minify_concatenate_css' => [
					'type'              => 'checkbox',
					'parent'            => 'minify_css',
					'label'             => __( 'Combine CSS Files', 'rocket' ),
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'async_css'              => [
					'type'              => 'checkbox',
					'label'             => __( 'Optimize CSS Delivery', 'rocket' ),
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'critical_css'           => [
					'type'              => 'textarea',
					'label'             => __( 'Critical CSS Fallback', 'rocket' ),
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'exclude_css'            => [
					'type'              => 'textarea',
					'label'             => __( 'Excluded CSS Files', 'rocket' ),
					'description'       => __( 'Specify URL', 'rocket' ),
					'section'           => 'css',
					'page'              => 'file_optimization',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'minify_js'              => [
					'type'              => 'checkbox',
					'label'             => __( 'Minify JS Files', 'rocket' ),
					'class'             => rocket_maybe_disable_minify_js() ? 'disabled' : '',
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => 0,
					'input_attr'        => [
						'disabled' => rocket_maybe_disable_minify_js() ? 1 : 0,
					],
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'minify_concatenate_js'  => [
					'type'              => 'checkbox',
					'label'             => __( 'Combine JS Files', 'rocket' ),
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'defer_all_js'           => [
					'type'              => 'checkbox',
					'label'             => __( 'Defer JS', 'rocket' ),
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'defer_all_js_safe'      => [
					'type'              => 'checkbox',
					'label'             => __( 'Safe Mode', 'rocket' ),
					'section'           => 'js',
					'page'              => 'file_optimization',
					'default'           => 1,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'exclude_js'             => [
					'type'              => 'textarea',
					'label'             => __( 'Excluded JS Files', 'rocket' ),
					'description'       => __( 'Specify URL', 'rocket' ),
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
	public function media_section() {
		$this->settings->add_page_section(
			'media',
			[
				'title'            => __( 'Media', 'rocket' ),
				'menu_description' => '',
			]
		);

		$this->settings->add_settings_sections(
			[
				'lazyload_section' => [
					'title'       => __( 'Lazyload', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'media',
				],
				'emoji_section'    => [
					'title'       => __( 'Disable emoji', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'media',
				],
				'embeds_section'   => [
					'title'       => __( 'Embeds', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'media',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'lazyload'         => [
					'type'              => 'checkbox',
					'label'             => __( 'Enable lazyload for images', 'rocket' ),
					'description'       => '',
					'section'           => 'lazyload_section',
					'page'              => 'media',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'lazyload_iframes' => [
					'type'              => 'checkbox',
					'label'             => __( 'Enable lazyload for iframes and videos', 'rocket' ),
					'description'       => '',
					'section'           => 'lazyload_section',
					'page'              => 'media',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'lazyload_youtube' => [
					'type'              => 'checkbox',
					'label'             => __( 'Replace Youtube iframe with preview image', 'rocket' ),
					'description'       => '',
					'section'           => 'lazyload_section',
					'page'              => 'media',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'emoji'            => [
					'type'              => 'checkbox',
					'label'             => __( 'Use default emoji', 'rocket' ),
					'description'       => '',
					'section'           => 'emoji_section',
					'page'              => 'media',
					'default'           => 1,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'embeds'           => [
					'type'              => 'checkbox',
					'label'             => __( 'Disable WordPress embeds', 'rocket' ),
					'description'       => '',
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
	public function preload_section() {
		$this->settings->add_page_section(
			'preload',
			[
				'title'            => __( 'Preload', 'rocket' ),
				'menu_description' => '',
			]
		);

		$this->settings->add_settings_sections(
			[
				'sitemap_preload_section' => [
					'title'       => __( 'Sitemap Preloading', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'preload',
				],
				'preload_bot_section'     => [
					'title'       => __( 'Preload Bot', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'preload',
				],
				'dns_prefetch_section'    => [
					'title'       => __( 'Prefetch DNS Requests', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
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
					'description'       => '',
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
					'description'       => '',
					'section'           => 'sitemap_preload_section',
					'page'              => 'preload',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'manual_preload'    => [
					'type'              => 'checkbox',
					'label'             => __( 'Manual', 'rocket' ),
					'description'       => '',
					'section'           => 'preload_bot_section',
					'page'              => 'preload',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'automatic_preload' => [
					'type'              => 'checkbox',
					'label'             => __( 'Automatic', 'rocket' ),
					'description'       => '',
					'section'           => 'preload_bot_section',
					'page'              => 'preload',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'dns_prefetch'      => [
					'type'              => 'textarea',
					'label'             => __( 'URLs to prefetch', 'rocket' ),
					'description'       => '',
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
	public function advanced_cache_section() {
		$this->settings->add_page_section(
			'advanced_cache',
			[
				'title'            => __( 'Advanced Rules', 'rocket' ),
				'menu_description' => '',
			]
		);

		$this->settings->add_settings_sections(
			[
				'cache_reject_uri_section'     => [
					'title'       => __( 'Never Cache URL(s)', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'advanced_cache',
				],
				'cache_reject_cookies_section' => [
					'title'       => __( 'Never Cache Cookies', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'advanced_cache',
				],
				'cache_reject_ua_section'      => [
					'title'       => __( 'Never Cache User Agent(s)', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'advanced_cache',
				],
				'cache_purge_pages_section'    => [
					'title'       => __( 'Always Purge URL(s)', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'advanced_cache',
				],
				'cache_query_strings_section'  => [
					'title'       => __( 'Cache Query String(s)', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'advanced_cache',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'cache_reject_uri'     => [
					'type'              => 'textarea',
					'label'             => '',
					'description'       => '',
					'section'           => 'cache_reject_uri_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_reject_cookies' => [
					'type'              => 'textarea',
					'label'             => '',
					'description'       => '',
					'section'           => 'cache_reject_cookies_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_reject_ua'      => [
					'type'              => 'textarea',
					'label'             => '',
					'description'       => '',
					'section'           => 'cache_reject_ua_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_purge_pages'    => [
					'type'              => 'textarea',
					'label'             => '',
					'description'       => '',
					'section'           => 'cache_purge_pages_section',
					'page'              => 'advanced_cache',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
				],
				'cache_query_strings'  => [
					'type'              => 'textarea',
					'label'             => '',
					'description'       => '',
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
	public function database_section() {
		$total                 = array();
		$database_optimization = new \Rocket_Database_Optimization();

		foreach ( $database_optimization->options as $option ) {
			$total[ $option ] = $database_optimization->count_cleanup_items( $option );
		}

		$this->settings->add_page_section(
			'database',
			[
				'title'            => __( 'Database', 'rocket' ),
				'menu_description' => '',
			]
		);

		$this->settings->add_settings_sections(
			[
				'post_cleanup_section'       => [
					'title'       => __( 'Post Cleanup', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'database',
				],
				'comments_cleanup_section'   => [
					'title'       => __( 'Comments Cleanup', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'database',
				],
				'transients_cleanup_section' => [
					'title'       => __( 'Transients Cleanup', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'database',
				],
				'database_cleanup_section'   => [
					'title'       => __( 'Database Cleanup', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'database',
				],
				'schedule_cleanup_section'   => [
					'title'       => __( 'Automatic cleanup', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'database',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'database_revisions'          => [
					'type'              => 'checkbox',
					'label'             => __( 'Revisions', 'rocket' ),
					// translators: %d is the number of revisions found in the database.
					'description'       => sprintf( _n( '%d revision in your database.', '%d revisions in your database.', $total['revisions'], 'rocket' ), $total['revisions'] ),
					'section'           => 'post_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_auto_drafts'        => [
					'type'              => 'checkbox',
					'label'             => __( 'Auto Drafts', 'rocket' ),
					// translators: %d is the number of drafts found in the database.
					'description'       => sprintf( _n( '%d draft in your database.', '%d drafts in your database.', $total['auto_drafts'], 'rocket' ), $total['auto_drafts'] ),
					'section'           => 'post_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_trashed_posts'      => [
					'type'              => 'checkbox',
					'label'             => __( 'Trashed Posts', 'rocket' ),
					// translators: %d is the number of trashed posts found in the database.
					'description'       => sprintf( _n( '%d trashed post in your database.', '%d trashed posts in your database.', $total['trashed_posts'], 'rocket' ), $total['trashed_posts'] ),
					'section'           => 'post_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_spam_comments'      => [
					'type'              => 'checkbox',
					'label'             => __( 'Spam Comments', 'rocket' ),
					// translators: %d is the number of spam comments found in the database.
					'description'       => sprintf( _n( '%d spam comment in your database.', '%d spam comments in your database.', $total['spam_comments'], 'rocket' ), $total['spam_comments'] ),
					'section'           => 'comments_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_trashed_comments'   => [
					'type'              => 'checkbox',
					'label'             => __( 'Trashed Comments', 'rocket' ),
					// translators: %d is the number of trashed comments found in the database.
					'description'       => sprintf( _n( '%d trashed comment in your database.', '%d trashed comments in your database.', $total['trashed_comments'], 'rocket' ), $total['trashed_comments'] ),
					'section'           => 'comments_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_expired_transients' => [
					'type'              => 'checkbox',
					'label'             => __( 'Expired transients', 'rocket' ),
					// translators: %d is the number of expired transients found in the database.
					'description'       => sprintf( _n( '%d expired transient in your database.', '%d expired transients in your database.', $total['expired_transients'], 'rocket' ), $total['expired_transients'] ),
					'section'           => 'transients_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_all_transients'     => [
					'type'              => 'checkbox',
					'label'             => __( 'All transients', 'rocket' ),
					// translators: %d is the number of transients found in the database.
					'description'       => sprintf( _n( '%d transient in your database.', '%d transients in your database.', $total['all_transients'], 'rocket' ), $total['all_transients'] ),
					'section'           => 'transients_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'database_optimize_tables'    => [
					'type'              => 'checkbox',
					'label'             => __( 'Optimize Tables', 'rocket' ),
					// translators: %d is the number of tables to optimize in the database.
					'description'       => sprintf( _n( '%d table to optimize in your database.', '%d tables to optimize in your database.', $total['optimize_tables'], 'rocket' ), $total['optimize_tables'] ),
					'section'           => 'database_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'schedule_automatic_cleanup'  => [
					'type'              => 'checkbox',
					'label'             => __( 'Schedule Automatic Cleanup', 'rocket' ),
					'description'       => '',
					'section'           => 'schedule_cleanup_section',
					'page'              => 'database',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'automatic_cleanup_frequency' => [
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
	public function cdn_section() {
		$this->settings->add_page_section(
			'cdn',
			[
				'title'            => __( 'CDN', 'rocket' ),
				'menu_description' => '',
			]
		);

		$this->settings->add_settings_sections(
			[
				'cdn_section'         => [
					'title'       => __( 'CDN', 'rocket' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'cdn',
				],
				'exclude_cdn_section' => [
					'title'       => __( 'Exclude files from CDN', 'rocket ' ),
					'type'        => 'fields_container',
					'description' => '',
					'help'        => '',
					'page'        => 'cdn',
				],
			]
		);

		$this->settings->add_settings_fields(
			[
				'cdn'              => [
					'type'              => 'checkbox',
					'label'             => __( 'Enable Content Delivery Network', 'rocket' ),
					'description'       => '',
					'section'           => 'cdn_section',
					'page'              => 'cdn',
					'default'           => 0,
					'sanitize_callback' => 'sanitize_checkbox',
				],
				'cdn_cnames'       => [
					'type'        => 'cnames',
					'label'       => __( 'CDN CNAME(s)', 'rocket' ),
					'description' => __( 'Specify the CNAME(s) below', 'rocket' ),
					'default'     => [],
					'section'     => 'cdn_section',
					'page'        => 'cdn',
				],
				'cdn_reject_files' => [
					'type'              => 'textarea',
					'label'             => '',
					'description'       => '',
					'section'           => 'exclude_cdn_section',
					'page'              => 'cdn',
					'default'           => [],
					'sanitize_callback' => 'sanitize_textarea',
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
}
