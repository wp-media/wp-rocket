<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_tutorials', __( 'Tutorials', 'rocket' ), '__return_false', 'rocket_tutorials' );
add_settings_field(
	'tuto_preload_cache',
	__( 'Preload cache', 'rocket' ),
	'rocket_video',
	'rocket_tutorials',
	'rocket_display_tutorials',
	array(
		'description'	=> __( 'This video gives some explanations about our two crawler robots. They generate several cache files in a few seconds.', 'rocket' ),
		'url'			=> 'http://www.youtube.com/embed/9jDcg2f-9yM',
		'name'			=> 'tuto_preload_cache',
	)
);
add_settings_field(
	'tuto_css_javascript_minification',
	__( 'CSS and JavaScript minification', 'rocket' ),
	'rocket_video',
	'rocket_tutorials',
	'rocket_display_tutorials',
	array(
		'description'	=> __( 'This video gives some explanations about how to use the advanced processes of minification and concatenation of CSS and JavaScript files.', 'rocket' ),
		'url'			=> 'http://www.youtube.com/embed/iziXSvZgxLk',
		'name'			=> 'css_javascript_minification',
	)
);
add_settings_field(
	'tuto_preload_dns_queries',
	__( 'Preloading DNS queries', 'rocket' ),
	'rocket_video',
	'rocket_tutorials',
	'rocket_display_tutorials',
	array(
		'description'	=> __( 'This video helps to easily understand the advanced option of "Preloading DNS queries" and the use of the filter <code>rocket_dns_prefetch</code>.', 'rocket' ),
		'url'			=> 'http://www.youtube.com/embed/ElJCtUidLwc',
		'name'			=> 'tuto_preload_dns_queries',
	)
);
add_settings_field(
	'tuto_white_label',
	__( 'How to use the White Label functionality?', 'rocket' ),
	'rocket_video',
	'rocket_tutorials',
	'rocket_display_tutorials',
	array(
		'description'	=> __( 'This video helps to set up a White Label version of WP Rocket.', 'rocket' ),
		'url'			=> 'http://www.youtube.com/embed/3rDpaom6kSc',
		'name'			=> 'tuto_white_label',
	)
);
add_settings_field(
	'tuto_cdn',
	__( 'How to use the CDN functionality?', 'rocket' ),
	'rocket_video',
	'rocket_tutorials',
	'rocket_display_tutorials',
	array(
		'description'	=> __( 'This video helps to understand what is the CDN functionality included since WP Rocket 2.1.', 'rocket' ),
		'url'			=> 'http://www.youtube.com/embed/JIamaNM8yp4',
		'name'			=> 'tuto_cdn',
	)
);
add_settings_field(
	'tuto_cdn_api',
	__( 'How to use the CDN API?', 'rocket' ),
	'rocket_video',
	'rocket_tutorials',
	'rocket_display_tutorials',
	array(
		'description'	=> __( 'This video helps to set up the two functions <code>get_rocket_cdn_url()</code> and <code>rocket_cdn_url()</code>.', 'rocket' ),
		'url'			=> 'http://www.youtube.com/embed/qfcGBoVdYKI',
		'name'			=> 'tuto_cdn_api',
	)
);