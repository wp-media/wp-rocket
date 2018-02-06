<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

// Are we white-labeled?
$rwl = rocket_is_white_label();

add_settings_section( 'rocket_display_optimization_options', __( 'Files optimization', 'rocket' ), '__return_false', 'rocket_optimization' );

/**
 * Panel caption
 */
if ( ! $rwl ) {

	add_settings_field(
		'rocket_optimization_options_panel',
		false,
		'rocket_field',
		'rocket_optimization',
		'rocket_display_optimization_options',
		array(
			array(
				'type'        => 'helper_panel_description',
				'name'        => 'optimization_options_panel_caption',
				'description' => sprintf(
					'<span class="dashicons dashicons-admin-tools" aria-hidden="true"></span><strong>%1$s</strong>',
					/* translators: line-break recommended, but not mandatory; use URL of localised document if available in your language  */
					__( 'Heads up! These options are not equally suitable for all WordPress setups.<br>If you notice any visual issues on your site, just turn off the last option(s) you had activated here. <br>Read the documentation on <a href="http://docs.wp-rocket.me/article/19-resolving-issues-with-minification" target="_blank">troubleshooting file optimization</a>.', 'rocket' )
				),
			),
		)
	);
}

/**
 * Minification
 */
$rocket_maybe_disable_minify = array(
	'type' => 'helper_detection',
	'name' => 'minify_html_disabled',
);

/* Autoptimize? */
if ( rocket_maybe_disable_minify_html() || rocket_maybe_disable_minify_css() || rocket_maybe_disable_minify_js() ) {
	$disabled = '';

	if ( rocket_maybe_disable_minify_html() ) {
		$disabled .= 'HTML, ';
	}

	if ( rocket_maybe_disable_minify_css() ) {
		$disabled .= 'CSS, ';
	}

	if ( rocket_maybe_disable_minify_js() ) {
		$disabled .= 'JS, ';
	}

	$disabled = rtrim( $disabled, ', ' );

	$rocket_maybe_disable_minify['description'] = sprintf(
		/* translators: %1$s = file types (CSS, JS, HTML); %2$s = “WP Rocket” or white-label plugin name */
		__( 'Minification (%1$s) is currently activated in <strong>Autoptimize</strong>. If you want to use %2$s’s minification, disable those options in Autoptimize.', 'rocket' ), $disabled, WP_ROCKET_PLUGIN_NAME
		);
}

/* Dynamic warning */
$rocket_minify_fields = array();

$rocket_minify_fields[] = array(
	'type'        => 'helper_warning',
	'name'        => 'minify_warning',
	'description' => sprintf(
		/* translators: %s = docs link, or nothing if white-label is enabled */
		__( 'Deactivate if you notice any visually broken items on your website.%s', 'rocket' ),
		$rwl ? '' : ' ' . __( '<a href="http://docs.wp-rocket.me/article/19-resolving-issues-with-minification" target="_blank">Why?</a>', 'rocket' )
	),
);

/* Minify options */
$rocket_minify_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'HTML',
	'name'         => 'minify_html',
	'label_screen' => __( 'Minify HTML files', 'rocket' ),
	'readonly'     => rocket_maybe_disable_minify_html(),
);
$rocket_minify_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'CSS',
	'name'         => 'minify_css',
	'label_screen' => __( 'Minify CSS files', 'rocket' ),
	'readonly'     => rocket_maybe_disable_minify_css(),
);
$rocket_minify_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'JS',
	'name'         => 'minify_js',
	'label_screen' => __( 'Minify JS files', 'rocket' ),
	'readonly'     => rocket_maybe_disable_minify_js(),
);
$rocket_minify_fields[] = $rocket_maybe_disable_minify;
$rocket_minify_fields[] = array(
	'type'        => 'helper_performance',
	'name'        => 'minify_perf_tip',
	'description' => __( 'Reduces file size, can improve loading time.', 'rocket' ),
);
$rocket_minify_fields[] = array(
	'type'        => 'helper_description',
	'name'        => 'minify',
	'description' => __( 'Removes spaces and comments from static files, enables browsers and search engines to faster process HTML, CSS, and JavaScript files.', 'rocket' ),
);

add_settings_field(
	'rocket_minify',
	__( 'Minify files:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	$rocket_minify_fields
);

/**
 * Concatenation
 */

/* Dynamic warning */
$rocket_concatenate_fields = array();

// get_rocket_option() might return a boolean or integer, so let’s be safe.
if (
	0 !== absint( get_rocket_option( 'minify_google_fonts' ) )
	|| 0 !== absint( get_rocket_option( 'minify_concatenate_css' ) )
	|| 0 !== absint( get_rocket_option( 'minify_concatenate_js' ) )
) {
	$rocket_concatenate_fields[] = array(
		'type'        => 'helper_warning',
		'name'        => 'minify_concatenate_warning',
		'description' => sprintf(
			/* translators: %s = docs link, or nothing if white-label is enabled */
			__( 'Deactivate if you notice any visually broken items on your website.%s', 'rocket' ),
			$rwl ? '' : ' ' . __( '<a href="http://docs.wp-rocket.me/article/19-resolving-issues-with-minification" target="_blank">Why?</a>', 'rocket' )
		),
	);
}

/* Concatenation options */
$rocket_concatenate_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'Google Fonts',
	'name'         => 'minify_google_fonts',
	'label_screen' => __( 'Concatenate Google Fonts', 'rocket' ),
);
$rocket_concatenate_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'CSS',
	'name'         => 'minify_concatenate_css',
	'label_screen' => __( 'Concatenate CSS files', 'rocket' ),
	'readonly'     => rocket_maybe_disable_minify_css(),
);
$rocket_concatenate_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'JS',
	'name'         => 'minify_concatenate_js',
	'label_screen' => __( 'Concatenate JS files', 'rocket' ),
	'readonly'     => rocket_maybe_disable_minify_js(),
);
$rocket_concatenate_fields[] = array(
	'type'        => 'helper_performance',
	'name'        => 'minify_concatenate_perf_tip',
	'description' => __( 'Reduces the number of HTTP requests, can improve loading time.', 'rocket' ),
);

$rocket_concatenate_fields[] = array(
	'type'        => 'helper_warning',
	'name'        => 'minify_combine_http2_warning',
	'description' => sprintf(
		// Translators: %s = link to WP Rocket documentation.
		__( 'These settings are not recommended if your site uses HTTP/2.%s', 'rocket' ),
		$rwl ? '' : ' ' . __( '<a href="http://docs.wp-rocket.me/article/1009-configuration-for-http-2" target="_blank">More info</a>', 'rocket' )
	),
);

add_settings_field(
	'rocket_concatenate',
	__( 'Combine files:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	$rocket_concatenate_fields
);

/**
 * Exclusion (CSS)
 */
add_settings_field(
	'rocket_exclude_css',
	__( 'Exclude CSS:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'        => 'helper_help',
			'name'        => 'exclude_css',
			'description' => __( 'Specify URLs of CSS files to be excluded from minification and concatenation (one per line)', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_css',
			'label_screen' => __( 'CSS files to be excluded from minification and concatenation:', 'rocket' ),
			'placeholder'  => '/wp-content/plugins/some-plugin/(.*).css',
		),
		array(
			'type'        => 'helper_description',
			'description' =>
			/* translators: line-break recommended; %s = code sample  */
			sprintf( __( 'The domain part of the URL will be stripped automatically.<br>Use %s wildcards to exclude all CSS files located at a specific path.', 'rocket' ), '<code>(.*).css</code>' ),
		),
		'class' => 'exclude-css-row',
	)
);

/**
 * Exclusion (JS)
 */
add_settings_field(
	'rocket_exclude_js',
	__( 'Exclude JS:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'        => 'helper_help',
			'name'        => 'exclude_js',
			'description' => __( 'Specify URLs of JS files to be excluded from minification and concatenation (one per line)', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_js',
			'label_screen' => __( 'JS files to be excludeded from minification and concatenation:', 'rocket' ),
			'placeholder'  => '/wp-content/themes/some-theme/(.*).js',
		),
		array(
			'type'        => 'helper_description',
			'description' =>
			/* translators: line-break recommended; %s = code sample  */
			sprintf( __( 'The domain part of the URL will be stripped automatically.<br>Use %s wildcards to exclude all JS files located at a specific path.', 'rocket' ), '<code>(.*).js</code>' ),
		),
		'class' => 'exclude-js-row',
	)
);

/**
 * Remove query strings
 */
add_settings_field(
	'rocket_remove_query_string_static_resources',
	__( 'Remove query strings:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Remove query strings from static resources', 'rocket' ),
			'name'         => 'remove_query_strings',
			'label_screen' => __( 'Remove query strings from static resources', 'rocket' ),
		),
		array(
			'type'        => 'helper_performance',
			'name'        => 'rocket_remove_query_strings_perf_tip',
			'description' => sprintf(
				/* translators: %s = https://gtmetrix.com/remove-query-strings-from-static-resources.html */
				__( 'Can improve the performance grade on <a href="%s" target="_blank">GT Metrix</a>.', 'rocket' ),
			'https://gtmetrix.com/remove-query-strings-from-static-resources.html'
			),
		),
		array(
			'type'        => 'helper_description',
			'name'        => 'rocket_remove_query_strings_desc',
			'description' => __( 'Removes the version query string from static files (e.g. style.css?ver=1.0) and encodes it into the file name instead (e.g. style-1-0.css).', 'rocket' ),
		),
	)
);

/**
 * Async CSS
 */
$rocket_render_blocking = array();

$rocket_render_blocking[] = array(
	'type'        => 'helper_warning',
	'name'        => 'render_blocking_warning',
	'description' => sprintf(
		/* translators: %s = docs link, or nothing if white-label is enabled */
		__( 'Deactivate if you notice any visually broken items on your website.%s', 'rocket' ),
		$rwl ? '' : ' ' . __( '<a href="http://docs.wp-rocket.me/article/108-render-blocking-javascript-and-css-pagespeed" target="_blank">Why?</a>', 'rocket' )
	),
);

$rocket_render_blocking[] = array(
	'type'         => 'checkbox',
	'label'        => __( 'Load JS files deferred', 'rocket' ),
	'name'         => 'defer_all_js',
	'label_screen' => __( 'Load JS files deferred', 'rocket' ),
);
$rocket_render_blocking[] = array(
	'parent'       => 'defer_all_js',
	'type'         => 'checkbox',
	'label'        => __( 'Safe mode (recommended)', 'rocket' ),
	'name'         => 'defer_all_js_safe',
	'label_screen' => __( 'Defer JS files safely', 'rocket' ),
);
$rocket_render_blocking[] = array(
	'parent'      => 'defer_all_js',
	'type'        => 'helper_description',
	'name'        => 'defer_js_safe_description',
	'description' => __( 'Safe mode for deferred JS ensures support for inline jQuery references from themes and plugins by loading jQuery at the top of the document as a render-blocking script. Deactivating may result in broken functionality, test thoroughly!', 'rocket' ),
);

$rocket_render_blocking[] = array(
	'type'        => 'helper_performance',
	'name'        => 'render_blocking_perf_tip',
	'description' => __( 'Reduces the number of initial HTTP requests, can improve loading time and performance grade.', 'rocket' ),
);

$rocket_render_blocking[] = array(
	'type'         => 'checkbox',
	'label'        => __( 'Optimize CSS delivery', 'rocket' ),
	'name'         => 'async_css',
	'label_screen' => __( 'Optimize CSS delivery', 'rocket' ),
	'readonly'     => rocket_maybe_disable_async_css(),
);

$rocket_render_blocking[] = array(
	'type'        => 'helper_description',
	'name'        => 'async_css_description',
	'description' => sprintf(
		/* translators: %s = docs link, or nothing if white-label is enabled */
		__( 'Critical path CSS will be automatically generated.%s', 'rocket' ),
		$rwl ? '' : ' ' . __( '<a href="http://docs.wp-rocket.me/article/108-render-blocking-javascript-and-css-pagespeed#critical-path-css" target="_blank">More info</a>', 'rocket' )
	),
);

/**
 * Above the fold CSS fallback
 */
$rocket_render_blocking[] = array(
	'parent'      => 'async_css',
	'type'        => 'helper_help',
	'name'        => 'critical_css_fallback_title',
	'description' => __( 'Fallback critical path CSS:', 'rocket' ),
);

$rocket_render_blocking[] = array(
	'parent'       => 'async_css',
	'type'         => 'textarea',
	'label_for'    => 'critical_css',
	'label_screen' => __( 'Fallback critical path CSS:', 'rocket' ),
);

$rocket_render_blocking[] = array(
	'parent'      => 'async_css',
	'type'        => 'helper_description',
	'name'        => 'critical_css_fallback_description',
	'description' => sprintf(
		// translators: %s = docs link, or nothing if white-label is enabled.
		__( 'Provides a fallback if auto-generated critical path CSS is incomplete.%s', 'rocket' ),
		$rwl ? '' : ' ' . __( '<a href="http://docs.wp-rocket.me/article/108-render-blocking-javascript-and-css-pagespeed#fallback" target="_blank">More info</a>' ) ),
);

add_settings_field(
	'rocket_render_blocking',
	__( 'Render-blocking CSS/JS:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	$rocket_render_blocking
);
