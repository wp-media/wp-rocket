<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * This warning is displayed when the API KEY isn't already set or not valid
 *
 * @since 1.0
 */
function rocket_need_api_key() { ?>
	<div class="updated">
		<p><b><?php echo WP_ROCKET_PLUGIN_NAME; ?></b> : <?php echo sprintf ( __('Last step before enjoying the high performances of our plugin, please <a href="%s">Enter your API key</a> here.', 'rocket' ), admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ) ) ;?></p>
	</div>
<?php
}

/**
 * Add Rocket informations into USER_AGENT
 *
 * @since 1.1.0
 */
function rocket_user_agent( $user_agent ) {
	$consumer_key = '';
	if ( isset( $_POST[ WP_ROCKET_SLUG ]['consumer_key'] ) ) {
		$consumer_key = $_POST[ WP_ROCKET_SLUG ]['consumer_key'];
	} else if ( '' != (string) get_rocket_option( 'consumer_key' ) ) {
		$consumer_key = (string) get_rocket_option( 'consumer_key' );
	}

	$consumer_email = '';
	if ( isset( $_POST[ WP_ROCKET_SLUG ]['consumer_email'] ) ) {
		$consumer_email = $_POST[ WP_ROCKET_SLUG ]['consumer_email'];
	} else if ( '' != (string) get_rocket_option( 'consumer_email' ) ) {
		$consumer_email = (string) get_rocket_option( 'consumer_email' );
	}

	$bonus = ! rocket_is_white_label() ? '' : '*';
	$bonus .= ! get_rocket_option( 'do_beta' ) ? '' : '+';
	$new_ua = sprintf( '%s;WP-Rocket|%s%s|%s|%s|%s|;', $user_agent, WP_ROCKET_VERSION, $bonus, $consumer_key, $consumer_email, esc_url( home_url() ) );

    return $new_ua;
}

/**
 * Renew all boxes for everyone if $uid is missing
 *
 * @since 1.1.10
 * @modified 2.1 :
 *	- Better usage of delete_user_meta into delete_metadata
 *
 * @param (int|null)$uid : a User id, can be null, null = all users
 * @param (string|array)$keep_this : which box have to be kept
 * @return void
 */
function rocket_renew_all_boxes( $uid = null, $keep_this = array() ) {
	// Delete a user meta for 1 user or all at a time
	delete_metadata( 'user', $uid, 'rocket_boxes', null == $uid );

	// $keep_this works only for the current user
	if ( ! empty( $keep_this ) && null != $uid ) {
		if ( is_array( $keep_this ) ) {
			foreach ( $keep_this as $kt ) {
				rocket_dismiss_box( $kt );
			}
		} else {
			rocket_dismiss_box( $keep_this );
		}
	}
}

/**
 * Renew a dismissed error box admin side
 *
 * @since 1.1.10
 *
 * @return void
 */
function rocket_renew_box( $function, $uid = 0 ) {
	global $current_user;
	$uid    = $uid==0 ? $current_user->ID : $uid;
	$actual = get_user_meta( $uid, 'rocket_boxes', true );

	if ( $actual && false !== array_search( $function, $actual ) ) {
		unset( $actual[array_search( $function, $actual )] );
		update_user_meta( $uid, 'rocket_boxes', $actual );
	}
}

/**
 * Dismissed 1 box, wrapper of rocket_dismiss_boxes()
 *
 * @since 1.3.0
 *
 * @return void
 */
function rocket_dismiss_box( $function ) {
	rocket_dismiss_boxes(
		array(
			'box'      => $function,
			'_wpnonce' => wp_create_nonce( 'rocket_ignore_' . $function ),
			'action'   => 'rocket_ignore'
		)
	);
}

/**
 * Is this version White Labeled?
 *
 * @since 2.1
 */
function rocket_is_white_label() {
	$options = '';
	$names   = array( 
		'wl_plugin_name', 
		'wl_plugin_URI', 
		'wl_description', 
		'wl_author', 
		'wl_author_URI' 
	);
	
	foreach( $names as $value ) {
		$option   = get_rocket_option( $value ); 
		$options .= ! is_array( $option ) ? $option : reset( ( $option ) );
	}
	
	return 'a509cac94e0cd8238b250074fe802b90' != md5( $options );
}

/**
 * Reset white label options
 *
 * @since 2.1
 *
 * @return void
 */
function rocket_reset_white_label_values( $hack_post ) {
	// White Label default values - !!! DO NOT TRANSLATE !!!
	$options = get_option( WP_ROCKET_SLUG );
	$options['wl_plugin_name']	= 'WP Rocket';
	$options['wl_plugin_slug']	= 'wprocket';
	$options['wl_plugin_URI']	= 'http://www.wp-rocket.me';
	$options['wl_description']	= array( 'The best WordPress performance plugin.' );
	$options['wl_author']		= 'WP Rocket';
	$options['wl_author_URI']	= 'http://www.wp-rocket.me';
	
	if ( $hack_post ) {
		// hack $_POST to force refresh of files, sorry
		$_POST['page'] = 'wprocket';
	}
	
	update_option( WP_ROCKET_SLUG, $options );
}

/**
 * Create a unique id for some Rocket options and functions
 *
 * @since 2.1
 */
function create_rocket_uniqid() {
	return str_replace( '.', '', uniqid( '', true ) );
}

/**
 * Force our user agent header when we hit our urls
 *
 * @since 2.4
 */
add_filter( 'http_request_args', '__rocket_add_own_ua', 10, 3 );
function __rocket_add_own_ua( $r, $url ) {
	if ( strpos( $url, 'wp-rocket.me' ) !== false ) {
		$r['user-agent'] = rocket_user_agent( $r['user-agent'] );
	}
	return $r;
}

/**
 * Function used to print all hidden fields from rocket to avoid the loss of these.
 *
 * @since 2.1
 */
function rocket_hidden_fields( $fields ) {
	if ( ! is_array( $fields ) ) {
		return;
	}

	foreach ( $fields as $field ) {
		echo '<input type="hidden" name="wp_rocket_settings[' . $field . ']" value="' . esc_attr( get_rocket_option( $field ) ) . '" />';
	}
}

/**
 * Get name & version of all active plugins.
 *
 * @since 2.6
 */
function rocket_get_active_plugins() {
	$plugins 		= array();
	$active_plugins = array_intersect_key( get_plugins(), array_flip( array_filter( array_keys( get_plugins() ), 'is_plugin_active' ) ) );
	
	foreach ( $active_plugins as $plugin ) { 
		$plugins[] = $plugin['Name'] . ' ' . $plugin['Version'];
	}
	
	return $plugins;
}

/**
 * Sanitizes a string key like the sanitize_key() WordPress function without forcing lowercase.
 *
 * @since 2.7
 */
function rocket_sanitize_key( $key ) {
	$key = preg_replace( '/[^a-z0-9_\-]/i', '', $key );
	return $key;
}

/**
 * Used to sanitize values of the "Never send cache pages for these user agents" option.
 *
 * @since 2.6.4
 */
function rocket_sanitize_ua( $ua ) {
	$ua = preg_replace( '/[^a-z0-9._\(\)\*\-\/\s\x5c]/i', '', $ua );
	return $ua;
}

/**
 * Check if the whole website is on the SSL protocol
 *
 * @since 2.7
 */
function rocket_is_ssl_website() {
	return 'https' === parse_url( home_url(), PHP_URL_SCHEME );
 }

/**
 * Get the WP Rocket documentation URL
 *
 * @since 2.7
 */ 
function get_rocket_documentation_url() {
	$langs  = array( 
		'fr_FR' => 'fr.' ,
		'it_IT' => 'it.' ,
		'de_DE' => 'de.' ,
	);
	$lang   = get_locale();
	$prefix = isset( $langs[ $lang ] ) ? $langs[ $lang ] : '';
	$url    = "http://{$prefix}docs.wp-rocket.me/?utm_source=wp-rocket&utm_medium=wp-admin&utm_term=doc-support&utm_campaign=plugin";

	return $url;
}

/**
 * Get the Activation Link for a given plugin
 *
 * @param string $plugin the given plugin folder/file.php (e.i. "imagify/imagify.php")
 * @since 2.7.3
 * @author Geoffrey Crofte
 */
function rocket_get_plugin_activation_link( $plugin ) {
	$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );

	return $activation_url;
}

/**
 * Check if a given plugin is installed but not necessarily activated
 * Note: get_plugins( $folder ) from WP Core doesn't work
 * 
 * @param string $plugin a plugin folder/file.php (e.i. "imagify/imagify.php")
 * @since 2.7.3
 * @author Geoffrey Crofte
 */
function rocket_is_plugin_installed( $plugin ) {
	$installed_plugins = get_plugins();

	return isset( $installed_plugins[ $plugin ] );
}

/**
 * Performs the database optimization
 *
 * @since 2.8
 * @author Remy Perona
 */
 function do_rocket_database_optimization() {
     $options = array(
       'revisions',
       'auto_drafts',
       'trashed_posts',
       'spam_comments',
       'trashed_comments',
       'expired_transients',
       'all_transients',
       'optimize_tables'
     );

     foreach ( $options as $option ) {
         if ( get_rocket_option( 'database_' . $option, false ) ) {
             rocket_database_optimize( $option );
         }
     }
 }
 
/*
 * Optimizes the database depending on the option
 *
 * @since 2.8
 * @author Remy Perona
 *
 * @param string $type Type of optimization to perform
 */
function rocket_database_optimize( $type ) {
    global $wpdb;

    switch( $type ) {
        case 'revisions':
            $query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
            if ( $query ) {
                foreach ( $query as $id ) {
                    wp_delete_post_revision( intval( $id ) );
                }
            }
            break;
        case 'auto_drafts':
            $query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
            if ( $query ) {
                foreach ( $query as $id ) {
                    wp_delete_post( intval( $id ), true );
                }
            }
            break;
        case 'trashed_posts':
            $query = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_status = %s", 'trash' ) );
            if ( $query ) {
                foreach ( $query as $id ) {
                    wp_delete_post( $id, true );
                }
            }
            break;
        case 'spam_comments':
            $query = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE comment_approved = %s", 'spam' ) );
            if ( $query ) {
                foreach ( $query as $id ) {
                    wp_delete_comment( intval( $id ), true );
                }
            }
            break;
        case 'trashed_comments':
            $query = $wpdb->get_col( $wpdb->prepare( "SELECT comment_ID FROM $wpdb->comments WHERE (comment_approved = %s OR comment_approved = %s)", 'trash', 'post-trashed' ) );
            if ( $query ) {
                foreach ( $query as $id ) {
                    wp_delete_comment( intval( $id ), true );
                }
            }
            break;
        case 'expired_transients':
            $time = isset( $_SERVER['REQUEST_TIME'] ) ? (int) $_SERVER['REQUEST_TIME'] : time();
            $query = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s AND option_value < %s", '_transient_timeout%', $time ) );
            
            if ( $query ) {
                foreach ( $query as $transient ) {
                    $key = str_replace( '_transient_timeout_', '', $transient );
                    delete_transient( $key );
                }
            }
            break;
        case 'all_transients':
            $query = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s", '%_transient_%' ) );
            if ( $query ) {
                foreach ( $query as $transient ) {
                    if( strpos( $transient, '_site_transient_' ) !== false ) {
                        delete_site_transient( str_replace( '_site_transient_', '', $transient ) );
                    } else {
                        delete_transient( str_replace( '_transient_', '', $transient ) );
                    }
                }
            }
            break;
        case 'optimize_tables':
            $query = $wpdb->get_results( $wpdb->prepare( "SELECT table_name, data_free FROM information_schema.tables WHERE table_schema = %s and Engine <> 'InnoDB' and data_free > 0", DB_NAME ) );
            if ( $query ) {
                foreach( $query as $table ) {
                    $wpdb->query( 'OPTIMIZE TABLE ' . $table->table_name );
                }
            }
            break;
    }
}

/**
 * Run an async job to preload sitemaps in background
 *
 * @param $body (array) Contains the usual $_POST
 *
 * @since 2.8
 **/
function rocket_do_async_job( $body ) {
	$args = array(
		'timeout'   => 0.01,
		'blocking'  => false,
		'body'      => $body,
		'cookies'   => $_COOKIE,
		'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
	);

	wp_remote_post( admin_url( 'admin-ajax.php' ), $args );
}