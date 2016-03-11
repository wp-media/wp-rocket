<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_imp_options', __( 'Advanced options', 'rocket' ), '__return_false', 'rocket_advanced' );
add_settings_field(
	'rocket_dns_prefetch',
	__( 'Prefetch DNS requests:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'dns_prefetch',
			'label_screen' => __('Prefetch DNS requests:', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'dns_prefetch',
			'description'  => __( 'DNS prefetching is a way for browsers to anticipate the DNS resolution of external domains from your site.', 'rocket' ) . '<br/>' . __( 'This mechanism reduces the latency of some external files.', 'rocket' ),
			),
		array(
			'display'      => ! rocket_is_white_label(),
			'type'         => 'helper_help',
			'name'         => 'dns_prefetch',
			'description'  => sprintf( __( 'To learn more about this option and how to use it correctly, we advise you to watch the following video: <a href="%1$s" class="fancybox">%1$s</a>.', 'rocket' ), ( defined( 'WPLANG' ) && WPLANG == 'fr_FR' ) ? 'http://www.youtube.com/embed/ElJCtUidLwc' : 'http://www.youtube.com/embed/jKMU6HgMMrA' ),
			),
		array(
			'type'         => 'helper_help',
			'name'         => 'dns_prefetch',
			'description'  => __( '<strong>Note:</strong> Enter the domain names without their protocol, for example: <code>//ajax.googleapis.com</code> without <code>http:</code> (one per line).', 'rocket' ),
			),
	)
);
add_settings_field(
	'rocket_purge_pages',
	__( 'Empty the cache of the following pages when updating a post:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_purge_pages',
			'label_screen' => __( 'Empty the cache of the following pages when updating a post:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'purge_pages',
			'description'  => __( 'Enter the URL of additionnal pages to purge when updating a post (one per line).', 'rocket' ) . '<br/>' .
								  __( 'You can use regular expressions (regex).', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'purge_pages',
			'description'  => __( '<strong>Note:</strong> When you update a post or when a comment is posted, the homepage, categories and tags associated with this post are automatically removed from the cache and then recreated by our bot.', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_reject_uri',
	__( 'Never cache the following pages:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_reject_uri',
			'label_screen' => __( 'Never cache the following pages:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'reject_uri',
			'description'  => __( 'Enter the URL of pages to reject (one per line).', 'rocket' ) . '<br/>' . __( 'You can use regular expressions (regex).', 'rocket' )
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'cache_reject_ua',
			'description'  => __( '<strong>Note:</strong> The cart and checkout pages are auto-excluded from the cache for WooCommerce, Easy Digital Download, iThemes Exchange, Jigoshop & WP-Shop.', 'rocket' )
		),
	)
);
add_settings_field(
	'rocket_reject_cookies',
	__( 'Don\'t cache pages that use the following cookies:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_reject_cookies',
			'label_screen' => __( 'Don\'t cache pages that use the following cookies:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'reject_cookies',
			'description'  => __( 'List the names of the cookies (one per line).', 'rocket' )
			),
	)
);
add_settings_field(
	'rocket_query_strings',
	__( 'Cache pages that use the following query strings (GET parameters):', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_query_strings',
			'label_screen' => __( 'Cache pages that use the following query strings (GET parameters):', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'query_strings',
			'description'  => __( 'List of query strings which can be cached (one per line).', 'rocket' )
			),
	)
);
add_settings_field(
	'rocket_reject_ua',
	__( 'Never send cache pages for these user agents:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_reject_ua',
			'label_screen' => __( 'Never send cache pages for these user agents:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'cache_reject_ua',
			'description'  => __( 'Enter the user agents name to reject (one per line).', 'rocket' ) . '<br/>'  . __( 'You can use regular expressions (regex).', 'rocket' )
		)
	)
);
add_settings_field(
	'rocket_minify_combine_all',
	 __( 'Reducing the number of minified files in one file on:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => 'CSS',
			'name'         => 'minify_css_combine_all',
			'label_screen' => __( 'CSS Files minification', 'rocket' )
		),
		array(
			'type'		   => 'checkbox',
			'label'		   => 'JS',
			'name'		   => 'minify_js_combine_all',
			'label_screen' => __( 'JS Files minification', 'rocket' ),
		),
		array(
			'type'			=> 'helper_description',
			'name'			=> 'rocket_minify_combine_all',
			'description'  => __( '<strong>Note:</strong> We combine the minified files in little groups <strong>to ensure the best compatibility and better performance</strong>.', 'rocket' ) . '<br/>' . __( 'However <strong>you can force the minification to create only 1 file</strong> by activating this option.', 'rocket' ) . '<br/>' . __( 'But it\'s not recommended to do that because <strong>you won\'t take advantage of the parallelization of the download</strong>: it\'s faster to download 4 files in parallel rather than one big file.', 'rocket' )
		),
		array(
			'type'			=> 'helper_warning',
			'name'			=> 'rocket_minify_combine_all',
			'description'  => __( 'Depending to your server configuration, these options can break your website. If you have any issues, you must deactivate it!', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_exclude_css',
	__( '<b>CSS</b> files to exclude from minification:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_css',
			'label_screen' => __( '<b>CSS</b> files to exclude from minification:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'exclude_css',
			'description'  => __( 'Enter the URL of <b>CSS</b> files to reject (one per line).', 'rocket' ) . '<br/>' . __( 'You can use regular expressions (regex).', 'rocket' )
			),
	)
);
add_settings_field(
	'rocket_exclude_js',
	__( '<b>JS</b> files to exclude from minification:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_js',
			'label_screen' => __( '<b>JS</b> files to exclude from minification:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'exclude_js',
			'description'  => __( 'Enter the URL of <b>JS</b> files to reject (one per line).', 'rocket' ) . '<br/>' . __( 'You can use regular expressions (regex).', 'rocket' )
			),
	)
);
add_settings_field(
	'minify_js_in_footer',
	__( '<b>JS</b> files to be included in the footer during the minification process:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'                     => 'repeater',
			'label_screen'             => __( '<b>JS</b> files to be included in the footer during the minification process:', 'rocket' ),
			'name'                     => 'minify_js_in_footer',
			'placeholder'              => 'http://',
			'repeater_drag_n_drop'     => true,
			'repeater_label_add_field' => __( 'Add URL', 'rocket' )
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'minify_js_in_footer',
			'description'  => __( 'Empty the field to remove it.', 'rocket' ),
			'class'	       => 'hide-if-js'
		),
		array(
			'type'         => 'helper_warning',
			'name'         => 'minify_js_in_footer',
			'description'  => __( 'You must specify the complete URL of the files.', 'rocket' )
		)
	)
);
add_settings_field(
	'rocket_deferred_js',
	__( '<b>JS</b> files with deferred loading:', 'rocket' ),
	'rocket_field',
	'rocket_advanced',
	'rocket_display_imp_options',
	array(
		array(
			'type'         => 'rocket_defered_module',
			),
		array(
			'type'         => 'helper_help',
			'name'         => 'deferred_js',
			'description'  => __( 'You can add JavaScript files that will be loaded asynchronously at the same time as the page loads.', 'rocket' )
			),
		array(
			'type'         => 'helper_help',
			'name'         => 'deferred_js',
			'description'  => __( 'Empty the field to remove it.', 'rocket' ),
			'class'	       => 'hide-if-js'
			),
		array(
			'type'         => 'helper_warning',
			'name'         => 'deferred_js',
			'description'  => __( 'You must specify the complete URL of the original files. Do NOT add URLs of minified files generated by WP Rocket.', 'rocket' )
			),
	)
);