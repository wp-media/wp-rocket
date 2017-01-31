<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/*
 * Tell WP what to do when admin is loaded aka upgrader
 *
 * @since 1.0
 */
add_action( 'admin_init', 'rocket_upgrader' );
function rocket_upgrader() {
	// Grab some infos
	$actual_version = get_rocket_option( 'version' );
	// You can hook the upgrader to trigger any action when WP Rocket is upgraded
	// first install
	if ( ! $actual_version ){
		do_action( 'wp_rocket_first_install' );
	}
	// already installed but got updated
	elseif ( WP_ROCKET_VERSION != $actual_version ) {
		do_action( 'wp_rocket_upgrade', WP_ROCKET_VERSION, $actual_version );
	}

	// If any upgrade has been done, we flush and update version #
	if ( did_action( 'wp_rocket_first_install' ) || did_action( 'wp_rocket_upgrade' ) ) {
		flush_rocket_htaccess();

		rocket_renew_all_boxes( 0, array( 'rocket_warning_plugin_modification' ) );

		$options = get_option( WP_ROCKET_SLUG ); // do not use get_rocket_option() here
		$options['version'] = WP_ROCKET_VERSION;

		$keys = rocket_check_key( 'live' );
		if ( is_array( $keys ) ) {
			$options = array_merge( $keys, $options );
		}

		update_option( WP_ROCKET_SLUG, $options );

        // Empty OPCache to prevent issue where plugin is updated but still showing as old version in WP admin
        if ( function_exists( 'opcache_reset' ) ) {
            @opcache_reset();
        }
	} else {
		if ( empty( $_POST ) && rocket_valid_key() ) {
			rocket_check_key( 'transient_30' );
		}
	}
	/** This filter is documented in inc/admin-bar.php */
	if ( ! rocket_valid_key() && current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) &&
		( ! isset( $_GET['page'] ) || 'wprocket' != $_GET['page'] ) ) {
		add_action( 'admin_notices', 'rocket_need_api_key' );
	}
}

/**
 * Keeps this function up to date at each version
 *
 * @since 1.0
 */
add_action( 'wp_rocket_first_install', 'rocket_first_install' );
function rocket_first_install() {
	// Generate an random key for cache dir of user
	$secret_cache_key = create_rocket_uniqid();

	// Generate an random key for minify md5 filename
	$minify_css_key = create_rocket_uniqid();
	$minify_js_key = create_rocket_uniqid();

	// Create Option
	add_option( WP_ROCKET_SLUG,
        /*
         * Filters the default rocket options array
         *
         * @since 2.8
         *
         * @param array Array of default rocket options
         */
		apply_filters( 'rocket_first_install_options', array(
			'secret_cache_key'            => $secret_cache_key,
			'cache_mobile'                => 0,
			'do_caching_mobile_files'     => 0,
			'cache_feed'				  => 0,
			'cache_logged_user'           => 0,
			'common_cache_logged_users'   => 0,
			'cache_ssl'                   => ( rocket_is_ssl_website() ) ? 1 : 0,
			'emoji'					  	  => 0,
			'cache_reject_uri'            => array(),
			'cache_reject_cookies'        => array(),
			'cache_reject_ua'             => array(),
			'cache_query_strings'         => array(),
			'cache_purge_pages'           => array(),
			'purge_cron_interval'         => 24,
			'purge_cron_unit'             => 'HOUR_IN_SECONDS',
			'exclude_css'                 => array(),
			'exclude_js'                  => array(),
			'deferred_js_files'           => array(),
			'deferred_js_wait'            => array(),
			'lazyload'                    => 0,
			'lazyload_iframes'            => 0,
			'minify_css'                  => 0,
			'minify_css_key'              => $minify_css_key,
			'minify_css_combine_all'      => 0,
			'minify_js'                   => 0,
			'minify_js_key'               => $minify_js_key,
			'minify_js_in_footer'         => array(),
			'minify_js_combine_all'       => 0,
			'minify_google_fonts'         => 0,
			'minify_html'                 => 0,
			'minify_html_inline_css'      => 0,
			'minify_html_inline_js'       => 0,
			'manual_preload'              => 1,
			'automatic_preload'           => 1,
			'sitemap_preload'             => 0,
			'sitemap_preload_url_crawl'   => '500000',
			'sitemaps'                    => array(),
			'remove_query_strings'        => 0,
			'dns_prefetch'                => 0,
			'database_revisions'          => 0,
			'database_auto_drafts'        => 0,
			'database_trashed_posts'      => 0,
			'database_spam_comments'      => 0,
			'database_trashed_comments'   => 0,
			'database_expired_transients' => 0,
			'database_all_transients'     => 0,
			'database_optimize_tables'    => 0,
			'schedule_automatic_cleanup'  => 0,
			'automatic_cleanup_frequency' => '',
			'cdn'                         => 0,
			'cdn_cnames'                  => array(),
			'cdn_zone'                    => array(),
			'cdn_ssl'                     => 0,
			'cdn_reject_files'            => array(),
			'do_cloudflare'               => 0,
			'cloudflare_email'            => '',
			'cloudflare_api_key'          => '',
			'cloudflare_domain'           => '',
			'cloudflare_zone_id'          => '',
			'cloudflare_devmode'          => 0,
			'cloudflare_protocol_rewrite' => 0,
			'cloudflare_auto_settings'    => 0,
			'cloudflare_old_settings'     => '',
			'varnish_auto_purge'          => 0,
			'do_beta'                     => 0,
		)
	) );
	rocket_dismiss_box( 'rocket_warning_plugin_modification' );
	rocket_reset_white_label_values( false );
}

/**
 * What to do when Rocket is updated, depending on versions
 *
 * @since 1.0
 */
add_action( 'wp_rocket_upgrade', 'rocket_new_upgrade', 10, 2 );
function rocket_new_upgrade( $wp_rocket_version, $actual_version ) {
	if ( version_compare( $actual_version, '1.0.1', '<' ) ) {
		wp_clear_scheduled_hook( 'rocket_check_event' );
	}

	if ( version_compare( $actual_version, '1.2.0', '<' ) ) {
		// Delete old WP Rocket cache dir
		rocket_rrmdir( WP_ROCKET_PATH . 'cache' );

		// Create new WP Rocket cache dir
		if( ! is_dir( WP_ROCKET_CACHE_PATH ) ) {
			mkdir( WP_ROCKET_CACHE_PATH );
		}
	}

	if ( version_compare( $actual_version, '1.3.0', '<' ) ) {
		rocket_dismiss_box( 'rocket_warning_plugin_modification' );
	}

	if ( version_compare( $actual_version, '1.3.3', '<' ) ) {
		// Clean cache
		rocket_clean_domain();

		// Create cache files
		run_rocket_bot( 'cache-preload' );
	}

	if ( version_compare( $actual_version, '2.0', '<' ) ) {
		// Add secret cache key
		$options = get_option( WP_ROCKET_SLUG );
		$options['secret_cache_key'] = create_rocket_uniqid();
		update_option( WP_ROCKET_SLUG, $options );

		global $wp_filesystem;
	    if ( ! $wp_filesystem ) {
			require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php' );
			require_once( ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php' );
			$wp_filesystem = new WP_Filesystem_Direct( new StdClass() );
		}

		// Get chmod of old folder cache
		$chmod = is_dir( WP_CONTENT_DIR . '/wp-rocket-cache' ) ? substr( sprintf( '%o', fileperms( WP_CONTENT_DIR . '/wp-rocket-cache' ) ), -4 ) : CHMOD_WP_ROCKET_CACHE_DIRS;

		// Check and create cache folder in wp-content if not already exist
		if( ! $wp_filesystem->is_dir( WP_CONTENT_DIR . '/cache' ) ) {
			$wp_filesystem->mkdir( WP_CONTENT_DIR . '/cache' , octdec($chmod) );
		}

		$wp_filesystem->mkdir( WP_CONTENT_DIR . '/cache/wp-rocket' , octdec($chmod) );

		// Move old cache folder in new path
		@rename( WP_CONTENT_DIR . '/wp-rocket-cache', WP_CONTENT_DIR . '/cache/wp-rocket'  );

		// Add WP_CACHE constant in wp-config.php
		set_rocket_wp_cache_define( true );

		// Create advanced-cache.php file
		rocket_generate_advanced_cache_file();

		// Create config file
		rocket_generate_config_file();
	}

	if ( version_compare( $actual_version, '2.1', '<' ) ) {
		rocket_reset_white_label_values( false );

		// Create minify cache folder if not exist
	    if ( ! is_dir( WP_ROCKET_MINIFY_CACHE_PATH ) ) {
			rocket_mkdir_p( WP_ROCKET_MINIFY_CACHE_PATH );
	    }

		// Create config domain folder if not exist
	    if ( ! is_dir( WP_ROCKET_CONFIG_PATH ) ) {
			rocket_mkdir_p( WP_ROCKET_CONFIG_PATH );
	    }

	    // Create advanced-cache.php file
		rocket_generate_advanced_cache_file();

	    // Create config file
		rocket_generate_config_file();
	}

	if ( version_compare( $actual_version, '2.3.3', '<' ) ) {
		// Clean cache
		rocket_clean_domain();

		// Create cache files
		run_rocket_bot( 'cache-preload' );
	}

	if ( version_compare( $actual_version, '2.3.9', '<' ) ) {
		// Regenerate config file
		rocket_generate_config_file();
	}

	if ( version_compare( $actual_version, '2.4.1', '<' ) ) {
		// Regenerate advanced-cache.php file
		rocket_generate_advanced_cache_file();
		delete_transient( 'rocket_ask_for_update' );
	}

	if ( version_compare( $actual_version, '2.6', '<' ) ) {
		// Activate Inline CSS & JS minification if HTML minification is activated
		$options = get_option( WP_ROCKET_SLUG );

		if ( !empty( $options['minify_html'] ) ) {
			$options['minify_html_inline_css'] = 1;
			$options['minify_html_inline_js']  = 1;
		}
		
		update_option( WP_ROCKET_SLUG, $options );

		// Regenerate advanced-cache.php file
		rocket_generate_advanced_cache_file();
	}
	
	if ( version_compare( $actual_version, '2.7', '<' ) ) {
		// Regenerate advanced-cache.php file
		rocket_generate_advanced_cache_file();
		
		// Regenerate config file
		rocket_generate_config_file();
	}
	
	if ( version_compare( $actual_version, '2.7.1', '<' ) ) {
		// Regenerate advanced-cache.php file
		rocket_generate_advanced_cache_file();
	}

    if ( version_compare( $actual_version, '2.8', '<' ) ) {
		$options                              = get_option( WP_ROCKET_SLUG );
		$options['manual_preload']            = 1;
		$options['automatic_preload']         = 1;
		$options['sitemap_preload_url_crawl'] = '500000';
		
		update_option( WP_ROCKET_SLUG, $options );
	}

    // Deactivate CloudFlare completely if PHP Version is lower than 5.4
    if ( version_compare( $actual_version, '2.8.16', '<' ) && phpversion() < '5.4' ) {
        $options                                = get_option( WP_ROCKET_SLUG );
        $options['do_cloudflare']               = 0;
        $options['cloudflare_email']            = '';
		$options['cloudflare_api_key']          = '';
		$options['cloudflare_domain']           = '';
		$options['cloudflare_devmode']          = 0;
		$options['cloudflare_protocol_rewrite'] = 0;
		$options['cloudflare_auto_settings']    = 0;
		$options['cloudflare_old_settings']     = '';

        update_option( WP_ROCKET_SLUG, $options );
    }

    // Add a value to the new CF zone_id field if the CF domain is set
    if ( version_compare( $actual_version, '2.8.21', '<' ) && phpversion() >= '5.4' ) {
        $options = get_option( WP_ROCKET_SLUG );
        if ( 0 < $options['do_cloudflare'] && $options['cloudflare_domain'] !== '' ) {
            require( WP_ROCKET_ADMIN_PATH . 'compat/cf-upgrader-5.4.php' );
        }
    }

	// Disable minification options if they're active in Autoptimize.
	if ( version_compare( $actual_version, '2.9.5', '<' ) ) {
		if ( is_plugin_active( 'autoptimize/autoptimize.php' ) ) {
			if ( 'on' === get_option( 'autoptimize_html') ) {
				update_rocket_option( 'minify_html', 0 );
				update_rocket_option( 'minify_html_inline_css', 0 );
				update_rocket_option( 'minify_html_inline_js', 0 );
			}
			
			if ( 'on' === get_option( 'autoptimize_css') ) {
				update_rocket_option( 'minify_css', 0 );
			}
			
			if ( 'on' === get_option( 'autoptimize_js') ) {
				update_rocket_option( 'minify_js', 0 );
			}
		}
	}
}
