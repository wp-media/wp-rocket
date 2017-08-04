<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

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
				'type'         => 'helper_panel_description',
				'name'         => 'optimization_options_panel_caption',
				'description'  => sprintf(
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
	'type'         => 'helper_detection',
	'name'         => 'minify_html_disabled',
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
		__( 'Minification (%1$s) is currently activated in <strong>Autoptimize</strong>. If you want to use %2$s’s minification, disable those options in Autoptimize.', 'rocket' ), $disabled, WP_ROCKET_PLUGIN_NAME );
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
	'readonly'	   => rocket_maybe_disable_minify_html(),
);
$rocket_minify_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'CSS',
	'name'         => 'minify_css',
	'label_screen' => __( 'Minify CSS files', 'rocket' ),
	'readonly'	   => rocket_maybe_disable_minify_css(),
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
	'type'         => 'helper_performance',
	'name'         => 'minify_perf_tip',
	'description'  => __( 'Reduces file size, can improve loading time.', 'rocket' ),
);
$rocket_minify_fields[] = array(
	'type'         => 'helper_description',
	'name'         => 'minify',
	'description'  => __( 'Removes spaces and comments from static files, enables browsers and search engines to faster process HTML, CSS, and JavaScript files.', 'rocket' ),
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
	'type'		   => 'checkbox',
	'label'		   => 'Google Fonts',
	'name'		   => 'minify_google_fonts',
	'label_screen' => __( 'Concatenate Google Fonts', 'rocket' ),
);
$rocket_concatenate_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'CSS',
	'name'         => 'minify_concatenate_css',
	'label_screen' => __( 'Concatenate CSS files', 'rocket' ),
	'readonly'	   => rocket_maybe_disable_minify_css(),
);
$rocket_concatenate_fields[] = array(
	'parent'	   => 'minify_concatenate_css',
	'type'         => 'checkbox',
	'label'        => __( 'Combine all CSS files into as few files as possible <em>(test thoroughly!)</em>', 'rocket' ),
	'name'         => 'minify_css_combine_all',
	'label_screen' => __( 'CSS Files concatenation', 'rocket' ),
);
$rocket_concatenate_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'JS',
	'name'         => 'minify_concatenate_js',
	'label_screen' => __( 'Concatenate JS files', 'rocket' ),
	'readonly'	   => rocket_maybe_disable_minify_js(),
);
$rocket_concatenate_fields[] = array(
	'parent'       => 'minify_concatenate_js',
	'type'         => 'checkbox',
	'label'        => __( 'Combine all JavaScript files into as few files as possible <em>(test thoroughly!)</em>', 'rocket' ),
	'name'         => 'minify_js_combine_all',
	'label_screen' => __( 'JS Files concatenation', 'rocket' ),
);
$rocket_concatenate_fields[] = array(
	'type'         => 'helper_performance',
	'name'         => 'minify_concatenate_perf_tip',
	'description'  => __( 'Reduces the number of HTTP requests, can improve loading time.', 'rocket' ),
);
$rocket_concatenate_fields[] = array(
	'type'         => 'helper_description',
	'name'         => 'rocket_minify_combine_all',
	'description'  => $rwl ? __( 'Files get concatenated into small groups in order to ensure theme/plugin compatibility and better performance. Forcing concatenation into 1 file is not recommended, because browsers are faster downloading up to 6 smaller files in parallel than 1-2 large files.', 'rocket' ) : __( 'Files get concatenated into small groups in order to <a href="http://docs.wp-rocket.me/article/17-reducing-the-number-of-minified-files" target="_blank">ensure theme/plugin compatibility and better performance</a>. Forcing concatenation into 1 file is not recommended, because browsers are faster downloading up to 6 smaller files in parallel than 1-2 large files.', 'rocket' ),
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
			'type'         => 'helper_help',
			'name'         => 'exclude_css',
			'description'  => __( 'Specify URLs of CSS files to be excluded from minification and concatenation (one per line)', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_css',
			'label_screen' => __( 'CSS files to be excluded from minification and concatenation:', 'rocket' ),
			'placeholder'  => '/wp-content/plugins/some-plugin/(.*).css',
		),
		array(
			'type'         => 'helper_description',
			'description'  =>
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
			'type'         => 'helper_help',
			'name'         => 'exclude_js',
			'description'  => __( 'Specify URLs of JS files to be excluded from minification and concatenation (one per line)', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_js',
			'label_screen' => __( 'JS files to be excludeded from minification and concatenation:', 'rocket' ),
			'placeholder'  => '/wp-content/themes/some-theme/(.*).js',
		),
		array(
			'type'         => 'helper_description',
			'description'  =>
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
			'type'         => 'helper_performance',
			'name'         => 'rocket_remove_query_strings_perf_tip',
			'description'  => sprintf(
				/* translators: %s = https://gtmetrix.com/remove-query-strings-from-static-resources.html */
				__( 'Can improve the performance grade on <a href="%s" target="_blank">GT Metrix</a>.', 'rocket' ),
			'https://gtmetrix.com/remove-query-strings-from-static-resources.html'
			),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'rocket_remove_query_strings_desc',
			'description'  => __( 'Removes the version query string from static files (e.g. style.css?ver=1.0) and encodes it into the file name instead (e.g. style-1-0.css).', 'rocket' ),
		),
	)
);

/**
 * Async CSS
 */
$rocket_render_blocking = array();

$rocket_render_blocking[] = array(
	'type'        => 'helper_warning',
	'name'		  => 'render_blocking_warning',
	'description' => sprintf(
		/* translators: %s = docs link, or nothing if white-label is enabled */
		__( 'Deactivate if you notice any visually broken items on your website.%s', 'rocket' ),
		$rwl ? '' : ' ' . __( '<a href="http://docs.wp-rocket.me/article/108-render-blocking-javascript-and-css-pagespeed" target="_blank">Why?</a>', 'rocket' )
	),
);

$rocket_render_blocking[] = array(
	'type'         => 'checkbox',
	'label'        => __( 'Load CSS files asynchronously', 'rocket' ),
	'name'         => 'async_css',
	'label_screen' => __( 'Load CSS files asynchronously', 'rocket' ),
	'readonly'	   => rocket_maybe_disable_async_css(),
);

if ( 0 !== absint( get_rocket_option( 'deferred_js' ) ) ) {
	$rocket_render_blocking[] = array(
			'type'        => 'helper_warning',
			'description' => __( 'If you activate the option below, your deprecated Defer JS option below will be deleted.', 'rocket' ),
	);
}

$rocket_render_blocking[] = array(
	'type'         => 'checkbox',
	'label'        => __( 'Load JS files deferred', 'rocket' ),
	'name'         => 'defer_all_js',
	'label_screen' => __( 'Load JS files deferred', 'rocket' ),
);
$rocket_render_blocking[] = array(
	'parent'	   => 'defer_all_js',
	'type'         => 'checkbox',
	'label'        => __( 'Safe mode (recommended)', 'rocket' ),
	'name'         => 'defer_all_js_safe',
	'label_screen' => __( 'Defer JS files safely', 'rocket' ),
);
$rocket_render_blocking[] = array(
	'parent'	   => 'defer_all_js',
	'type'         => 'helper_description',
	'name'         => 'defer_js_safe_description',
	'description'  => __( 'Safe mode for deferred JS ensures support for inline jQuery references from themes and plugins by loading jQuery at the top of the document as a render-blocking script. Deactivating may result in broken functionality, test thoroughly!', 'rocket' ),
);
$rocket_render_blocking[] = array(
	'type'         => 'helper_performance',
	'name'         => 'render_blocking_perf_tip',
	'description'  => __( 'Reduces the number of initial HTTP requests, can improve loading time and performance grade.', 'rocket' ),
);

add_settings_field(
	'rocket_render_blocking',
	 __( 'Render-blocking CSS/JS:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	$rocket_render_blocking
);

/**
 * Above the fold CSS
 */
add_settings_field(
	'rocket_critical_css',
	__( 'Critical path CSS:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'         => 'helper_help',
			'name'         => 'critical_css_description',
			'description'  => __( 'Specify CSS rules required for rendering above-the-fold content', 'rocket' ),
		),
		array(
			'type'         => 'textarea',
			'label_for'    => 'critical_css',
			'label_screen' => __( 'Critical path CSS rules for rendering above-the-fold content', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'critical_css_generator',
			'description'  => sprintf( __( 'Use the <a href="%s" target="_blank">Critical Path CSS Generator</a> to specify required CSS rules.', 'rocket' ), 'http://docs.wp-rocket.me/article/108-render-blocking-javascript-and-css-pagespeed#critical-path-css' ),
		),
		'class' => 'critical-css-row',
	)
);

/**
 * Deprecated options panel caption
 */
if ( ! $rwl && ( get_rocket_option( 'minify_js_in_footer') || get_rocket_option( 'deferred_js' ) ) ) {
	add_settings_field(
		'rocket_optimization_deprected_options',
		false,
		'rocket_field',
		'rocket_optimization',
		'rocket_display_optimization_options',
		array(
			array(
				'type'         => 'helper_panel_description',
				'description'  => sprintf(
					'<span class="dashicons dashicons-warning" aria-hidden="true"></span><strong>%1$s</strong>',
					/* translators: line-break recommended, but not mandatory  */
					__( 'The options below will be deprecated in WP Rocket 3.0, in favor of the new options for render-blocking CSS/JS above. If you use those new options, the deprecated one for deferred JS gets ignored already.', 'rocket' )
				),
			),
		)
	);
}

/**
 * Legacy: JS to footer
 */
if ( get_rocket_option( 'minify_js_in_footer' ) ) {
	add_settings_field(
		'minify_js_in_footer',
		__( 'Footer JS (deprecated):', 'rocket' ),
		'rocket_field',
		'rocket_optimization',
		'rocket_display_optimization_options',
		array(
			array(
				'type'                     => 'repeater',
				'label_screen'             => __( '<strong>JS</strong> files to be included in the footer during the minification process:', 'rocket' ),
				'name'                     => 'minify_js_in_footer',
				'placeholder'              => 'http://',
				'repeater_drag_n_drop'     => true,
				'repeater_label_add_field' => __( 'Add URL', 'rocket' ),
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'minify_js_in_footer',
				'description'  => __( 'Empty the field to remove it.', 'rocket' ),
				'class'	       => 'hide-if-js',
			),
			array(
				'type'         => 'helper_description',
				'name'         => 'minify_js_in_footer',
				'description'  => __( 'Specify complete URLs like:  <code>http://example.com/path/to/script.js</code>', 'rocket' ),
			),
		)
	);
}

/**
 * Legacy: Deferred JS
 */
if ( get_rocket_option( 'deferred_js' ) ) {
	$deferred_js_readonly = '';

	if ( get_rocket_option( 'defer_all_js', 0 ) ) {
		$deferred_js_readonly = 1;
	}

	add_settings_field(
		'rocket_deferred_js',
		__( 'Defer JS (deprecated):', 'rocket' ),
		'rocket_field',
		'rocket_optimization',
		'rocket_display_optimization_options',
		array(
			array(
				'type'         => 'helper_help',
				'name'         => 'deferred_js',
				'description'  =>
				/* translators: line-break recommended, but not mandatory  */
				__( 'Specify JS files to be loaded asynchronously as the page loads.<br>Do NOT add URLs of minified files generated by WP Rocket.', 'rocket' ),
				'readonly'	   => $deferred_js_readonly,
			),
			array(
					'type'         => 'rocket_defered_module',
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'deferred_js',
				'description'  => __( 'Empty the field to remove it.', 'rocket' ),
				'class'	       => 'hide-if-js',
			),
			array(
				'type'         => 'helper_description',
				'name'         => 'deferred_js',
				'description'  => __( 'Specify complete URLs like: <code>http://example.com/path/to/script.js</code>', 'rocket' ),
			),
		)
	);
}
