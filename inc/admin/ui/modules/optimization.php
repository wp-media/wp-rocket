<?php
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_optimization_options', __( 'Files optimization', 'rocket' ), '__return_false', 'rocket_optimization' );

add_settings_field(
	'rocket_remove_query_string_static_resources',
	 __( 'Static Resources:', 'rocket' ),
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
			'type'			=> 'helper_description',
			'name'			=> 'rocket_remove_query_strings_desc',
			'description'  => __( 'This will remove the version query string from static resources and encode it in the resources filename instead. e.g. style.css?ver=1.0 will become style-1.0.css', 'rocket' ),
		),
		array(
			'type'         => 'helper_description',
			'name'         => 'rocket_remove_query_strings_desc',
			'description'  => __( '<strong>Note:</strong> By activating this option, you will improve the <code>Remove query strings from static resources</code> grade on GT Metrix.', 'rocket' ),
		),
	)
);

$rocket_maybe_disable_minify = array(
	'type'         => 'helper_warning',
	'name'         => 'minify_html_disabled',
);

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

	$rocket_maybe_disable_minify['description'] = sprintf( __( 'These minification options are disabled because they are currently activated in Autoptimize. If you want to use WP Rocket minification, disable them there first: %s', 'rocket' ), $disabled );
}

add_settings_field(
	'rocket_minify',
	 __( 'Minification:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => 'HTML',
			'name'         => 'minify_html',
			'label_screen' => __( 'HTML Files minification', 'rocket' ),
			'readonly'	   => rocket_maybe_disable_minify_html(),
		),
		array(
			'type'         => 'checkbox',
			'label'        => 'CSS',
			'name'         => 'minify_css',
			'label_screen' => __( 'CSS Files minification', 'rocket' ),
			'readonly'	   => rocket_maybe_disable_minify_css(),
		),
		array(
			'type'		   => 'checkbox',
			'label'		   => 'JS',
			'name'		   => 'minify_js',
			'label_screen' => __( 'JS Files minification', 'rocket' ),
			'readonly'	   => rocket_maybe_disable_minify_js(),
		),
		$rocket_maybe_disable_minify,
		array(
			'type'			=> 'helper_description',
			'name'			=> 'minify',
			'description'  => __( 'Minification removes any spaces and comments present in the CSS and JavaScript files.', 'rocket' ) . '<br/>' . __( 'This mechanism reduces the weight of each file and allows a faster reading of browsers and search engines.', 'rocket' ),
		),
	)
);

add_settings_field(
	'rocket_concatenate',
	 __( 'Concatenation:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'		   => 'checkbox',
			'label'		   => 'Google Fonts',
			'name'		   => 'minify_google_fonts',
			'label_screen' => __( 'Google Fonts minification', 'rocket' ),
		),
		array(
			'type'         => 'checkbox',
			'label'        => 'CSS',
			'name'         => 'minify_concatenate_css',
			'label_screen' => 'Concatenate CSS files',
			'readonly'	   => rocket_maybe_disable_minify_css(),
		),
		array(
			'parent'	   => 'minify_concatenate_css',
			'type'         => 'checkbox',
			'label'        => 'Concatenate in one file',
			'name'         => 'minify_css_combine_all',
			'label_screen' => __( 'CSS Files concatenation', 'rocket' ),
		),
		array(
			'type'         => 'checkbox',
			'label'        => 'JS',
			'name'         => 'minify_concatenate_js',
			'label_screen' => 'Concatenate JS files',
			'readonly'	   => rocket_maybe_disable_minify_js(),
		),
		array(
			'parent'	   => 'minify_concatenate_js',
			'type'		   => 'checkbox',
			'label'		   => 'Concatenate in one file',
			'name'		   => 'minify_js_combine_all',
			'label_screen' => __( 'JS Files concatenation', 'rocket' ),
		),
		array(
			'type'			=> 'helper_description',
			'name'			=> 'minify',
			'description'  => __( 'Concatenation combines all CSS and JavaScript files.', 'rocket' ) . '<br/>' . __( 'This mechanism reduces the number of HTTP requests and improves the loading time.', 'rocket' ),
		),
		array(
			'display'		=> ! rocket_is_white_label(),
			'type'			=> 'helper_warning',
			'name'			=> 'minify_help2',
			'description'  => sprintf( __( 'In case of any errors we recommend you to turn off this option or watch the following video: <a href="%1$s" class="fancybox">%1$s</a>.', 'rocket' ), ( defined( 'WPLANG' ) && WPLANG === 'fr_FR' ) ? 'http://www.youtube.com/embed/5-Llh0ivyjs' : 'http://www.youtube.com/embed/kymoxCwW03c' ),
		),
		array(
			'type'			=> 'helper_description',
			'name'			=> 'rocket_minify_combine_all',
			'description'  => __( '<strong>Note:</strong> We combine the minified files in little groups <strong>to ensure the best compatibility and better performance</strong>.', 'rocket' ) . '<br/>' . __( 'However <strong>you can force the minification to create only 1 file</strong> by activating the sub-option.', 'rocket' ) . '<br/>' . __( 'But it\'s not recommended to do that because <strong>you won\'t take advantage of the parallelization of the download</strong>: it\'s faster to download 4 files in parallel rather than one big file.', 'rocket' ),
		),
	)
);
add_settings_field(
	'rocket_exclude_css',
	__( '<b>CSS</b> files to exclude:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_css',
			'label_screen' => __( '<b>CSS</b> files to exclude from minification:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'exclude_css',
			'description'  => __( 'Enter the URL of <b>CSS</b> files to reject (one per line).', 'rocket' ) . '<br/>' . __( 'You can use regular expressions (regex).', 'rocket' ),
			),
	)
);
add_settings_field(
	'rocket_exclude_js',
	__( '<b>JS</b> files to exclude:', 'rocket' ),
	'rocket_field',
	'rocket_optimization',
	'rocket_display_optimization_options',
	array(
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_js',
			'label_screen' => __( '<b>JS</b> files to exclude from minification:', 'rocket' ),
		),
		array(
			'type'         => 'helper_help',
			'name'         => 'exclude_js',
			'description'  => __( 'Enter the URL of <b>JS</b> files to reject (one per line).', 'rocket' ) . '<br/>' . __( 'You can use regular expressions (regex).', 'rocket' ),
			),
	)
);

if ( get_rocket_option( 'minify_js_in_footer' ) ) {
	add_settings_field(
		'minify_js_in_footer',
		__( '<b>JS</b> files to be included in the footer during the minification process:', 'rocket' ),
		'rocket_field',
		'rocket_optimization',
		'rocket_display_optimization_options',
		array(
			array(
				'type'                     => 'repeater',
				'label_screen'             => __( '<b>JS</b> files to be included in the footer during the minification process:', 'rocket' ),
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

if ( get_rocket_option( 'deferred_js' ) ) {
	$deferred_js_readonly = '';

	if ( get_rocket_option( 'defer_all_js', 0 ) ) {
		$deferred_js_readonly = 1;
	}

	add_settings_field(
		'rocket_deferred_js',
		__( '<b>JS</b> files with deferred loading:', 'rocket' ),
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
