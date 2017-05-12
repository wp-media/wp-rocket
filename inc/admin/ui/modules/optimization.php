<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

add_settings_section( 'rocket_display_optimization_options', __( 'Files optimization', 'rocket' ), '__return_false', 'rocket_optimization' );

/**
 * Panel caption
 */
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
				/* translators: line break is recommended, but not mandatory  */
				__( 'Heads up! These options are not equally suitable for all WordPress setups.<br>In case you notice any visual issues on your site, just turn off the last option(s) you had activated here. <br>Read the documentation on <a href="http://docs.wp-rocket.me/article/19-resolving-issues-with-minification" target="_blank">troubleshooting file optimization</a>.', 'rocket' )
			),
		),
	)
);

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

	$rocket_maybe_disable_minify['description'] = sprintf( __( '<strong>Third-party feature detected:</strong> Minification (%s) is currently activated in <strong>Autoptimize</strong>. If you want to use WP Rocket’s minification, disable those options in Autoptimize.', 'rocket' ), $disabled );
}

/* Dynamic warning */
$rocket_minify_fields = array();

// get_rocket_option() might return a boolean or integer, so let’s be safe.
if (
	   0 !== absint( get_rocket_option( 'minify_html' ) )
	|| 0 !== absint( get_rocket_option( 'minify_css' ) )
	|| 0 !== absint( get_rocket_option( 'minify_js' ) )
) {
	$rocket_minify_fields[] = array(
			'type'        => 'helper_warning',
			'name'        => 'minify_warning',
			'description' => __( 'Deactivate in case you notice any visually broken items on your website. <a href="http://docs.wp-rocket.me/article/19-resolving-issues-with-minification" target="_blank">Why?</a>', 'rocket' ),
	);
}

/* Minify options */
$rocket_minify_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'HTML',
	'name'         => 'minify_html',
	'label_screen' => __( 'HTML Files minification', 'rocket' ),
	'readonly'	   => rocket_maybe_disable_minify_html(),
);
$rocket_minify_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'CSS',
	'name'         => 'minify_css',
	'label_screen' => __( 'CSS Files minification', 'rocket' ),
	'readonly'	   => rocket_maybe_disable_minify_css(),
);
$rocket_minify_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'JS',
	'name'         => 'minify_js',
	'label_screen' => __( 'JS Files minification', 'rocket' ),
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
	 __( 'Minification:', 'rocket' ),
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
			'description' => __( 'Deactivate in case you notice any visually broken items on your website. <a href="http://docs.wp-rocket.me/article/19-resolving-issues-with-minification" target="_blank">Why?</a>', 'rocket' ),
	);
}

/* Concatenation options */
$rocket_concatenate_fields[] = array(
	'type'		   => 'checkbox',
	'label'		   => 'Google Fonts',
	'name'		   => 'minify_google_fonts',
	'label_screen' => __( 'Google Fonts minification', 'rocket' ),
);
$rocket_concatenate_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'CSS',
	'name'         => 'minify_concatenate_css',
	'label_screen' => 'Concatenate CSS files',
	'readonly'	   => rocket_maybe_disable_minify_css(),
);
$rocket_concatenate_fields[] = array(
	'parent'	   => 'minify_concatenate_css',
	'type'         => 'checkbox',
	'label'        => 'Concatenate all CSS files into 1 file <em>(test thoroughly!)</em>',
	'name'         => 'minify_css_combine_all',
	'label_screen' => __( 'CSS Files concatenation', 'rocket' ),
);
$rocket_concatenate_fields[] = array(
	'type'         => 'checkbox',
	'label'        => 'JS',
	'name'         => 'minify_concatenate_js',
	'label_screen' => 'Concatenate JS files',
	'readonly'	   => rocket_maybe_disable_minify_js(),
);
$rocket_concatenate_fields[] = array(
	'parent'	   => 'minify_concatenate_js',
	'type'		   => 'checkbox',
	'label'		   => 'Concatenate all JavaScript files into 1 file <em>(test thoroughly!)</em>',
	'name'		   => 'minify_js_combine_all',
	'label_screen' => __( 'JS Files concatenation', 'rocket' ),
);
$rocket_concatenate_fields[] = array(
	'type'			=> 'helper_performance',
	'name'			=> 'minify_concatenate_perf_tip',
	'description'  => __( 'Reduces the number of HTTP requests, can improve loading time.', 'rocket' ),
);
$rocket_concatenate_fields[] = array(
	'type'			=> 'helper_description',
	'name'			=> 'rocket_minify_combine_all',
	'description'  => __( 'Files get concatenated into small groups in order to <a href="http://docs.wp-rocket.me/article/17-reducing-the-number-of-minified-files" target="_blank">ensure theme/plugin compatibility and better performance</a>. Forcing concatenation into 1 file is not recommended, because browsers are faster downloading up to 6 smaller files in parallel than 1-2 large files.', 'rocket' ),
);

add_settings_field(
	'rocket_concatenate',
	 __( 'Concatenation:', 'rocket' ),
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
	__( '<strong>CSS</strong> files to exclude:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_css',
			'label_screen' => __( '<strong>CSS</strong> files to exclude from minification:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'exclude_css',
			'description'  => __( 'Enter the URL of <strong>CSS</strong> files to reject (one per line).', 'rocket' ) . '<br/>' . __( 'You can use regular expressions (regex).', 'rocket' ),
		),
		'class' => 'exclude-css-row',
	)
);

/**
 * Exclusion (JS)
 */
add_settings_field(
	'rocket_exclude_js',
	__( '<strong>JS</strong> files to exclude:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_js',
			'label_screen' => __( '<strong>JS</strong> files to exclude from minification:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'exclude_js',
			'description'  => __( 'Enter the URL of <strong>JS</strong> files to reject (one per line).', 'rocket' ) . '<br/>' . __( 'You can use regular expressions (regex).', 'rocket' ),
		),
		'class' => 'exclude-js-row',
	)
);

/**
 * Legacy: JS to footer
 */
if ( get_rocket_option( 'minify_js_in_footer' ) ) {
	add_settings_field(
		'minify_js_in_footer',
		__( '<strong>JS</strong> files to be included in the footer during the minification process:', 'rocket' ),
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
				'type'         => 'helper_warning',
				'name'         => 'minify_js_in_footer',
				'description'  => __( 'You must specify the complete URL of the files.', 'rocket' ),
			),
			array(
				'type'         => 'helper_warning',
				'name'         => 'deferred_js',
				'description'  => __( 'This option will be deprecated in 3.0! It will also be ignored if you use the new defer js option introduced in 3.0.', 'rocket' ),
			),
		)
	);
}

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
			'type'			=> 'helper_performance',
			'name'			=> 'rocket_remove_query_strings_perf_tip',
			'description'   => sprintf(
				/* translators: %s = https://gtmetrix.com/remove-query-strings-from-static-resources.html */
				__( 'Can improve the performance grade on <a href="%s" target="_blank">GT Metrix</a>.', 'rocket' ),
			'https://gtmetrix.com/remove-query-strings-from-static-resources.html'
			),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'rocket_remove_query_strings_desc',
			'description'  => __( 'Removes the version query string from static files (e.g. style.css?ver=1.0) and encodes it into the file name instead (e.g. style-1.0.css).', 'rocket' ),
		),
	)
);

/**
 * Async CSS
 */
add_settings_field(
	'rocket_render_blocking',
	 __( 'Render blocking JavaScript & CSS above the fold:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Load CSS files asynchronously', 'rocket' ),
			'name'         => 'async_css',
			'label_screen' => __( 'Load CSS files asynchronously', 'rocket' ),
			'readonly'	   => rocket_maybe_disable_async_css(),
		),
		array(
			'type'         => 'checkbox',
			'label'        => __( 'Defer loading of JS files', 'rocket' ),
			'name'         => 'defer_all_js',
			'label_screen' => __( 'Defer loading of JS files', 'rocket' ),
		),
		array(
			'type'			=> 'helper_description',
			'name'			=> 'render_blocking_description',
			'description'  => __( '<strong>Note:</strong> By activating these options, you can improve your score on PageSpeed.', 'rocket' ),
		),
	)
);

/**
 * Above the fold CSS
 */
add_settings_field(
	'rocket_critical_css',
	__( 'Critical CSS for above the fold rendering:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'critical_css',
			'label_screen' => __( 'Critical CSS rules for above the fold rendering', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'critical_css_description',
			'description'  => __( 'Input the critical CSS rules required for rendering your above the fold content.', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'critical_css_generator',
			'description'  => sprintf( __( '<strong>Note:</strong> You can use %1$sthis tool%2$s to generate your critical CSS.', 'rocket' ), '<a href="https://www.sitelocity.com/critical-path-css-generator" target="_blank">', '</a>' ),
		),
		'class' => 'critical-css-row',
	)
);

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
		__( '<strong>JS</strong> files with deferred loading:', 'rocket' ),
		'rocket_field',
		'rocket_optimization',
		'rocket_display_optimization_options',
		array(
			array(
				'type'         => 'rocket_defered_module',
				),
			array(
				'type'         => 'helper_help',
				'name'         => 'deferred_js',
				'description'  => __( 'You can add JavaScript files that will be loaded asynchronously at the same time as the page loads.', 'rocket' ),
				'readonly'	   => $deferred_js_readonly,
				),
			array(
				'type'         => 'helper_help',
				'name'         => 'deferred_js',
				'description'  => __( 'Empty the field to remove it.', 'rocket' ),
				'class'	       => 'hide-if-js',
				),
			array(
				'type'         => 'helper_warning',
				'name'         => 'deferred_js',
				'description'  => __( 'You must specify the complete URL of the original files. Do NOT add URLs of minified files generated by WP Rocket.', 'rocket' ),
				),
			array(
				'type'         => 'helper_warning',
				'name'         => 'deferred_js',
				'description'  => __( 'This option will be deprecated in 3.0! It will also be ignored if you use the new defer js option introduced in 3.0.', 'rocket' ),
				),
		)
	);
}
