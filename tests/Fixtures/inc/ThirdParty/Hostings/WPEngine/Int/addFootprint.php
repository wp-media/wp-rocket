<?php

return [
	'testWPEngineAddFootprint' => [
		'white_label_footprint' => false,
		'html'                  => '<html><head><title>Sample Page</title>' .
						                '</head><body></body></html>',
		'expected'              => '<html><head><title>Sample Page</title>' .
										'</head><body>' .
										'<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"width_threshold":1920,"height_threshold":1080}</script><script src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>' .
										"</body></html>\n" . '<!-- This website is like a Rocket, isn\'t it? Performance optimized by WP Rocket. Learn more: https://wp-rocket.me' . ' -->',
	],
	'testWPEngineAddFootprintWithWhitelabel' => [
		'white_label_footprint' => true,
		'html'                  => '<html><head><title>Sample Page</title>' .
										'</head><body></body></html>',
		'expected'              => '<html><head><title>Sample Page</title>' .
										'</head><body>' .
										'<script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"width_threshold":1920,"height_threshold":1080}</script><script src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script>' .
										"</body></html>\n" . '<!-- Optimized for great performance' . ' -->',
	],
	'testWPEngineAddFootprintNoHtmlShouldBailOut' => [
		'white_label_footprint' => false,
		'html'                  => '<html><head><title>Sample Page</title>' .
										'</head><body></body>',
		'expected'              => '<html><head><title>Sample Page</title>' .
										'</head><body><script>var rocket_lcp_data = {"ajax_url":"http:\/\/example.org\/wp-admin\/admin-ajax.php","nonce":"'.wp_create_nonce( 'rocket_lcp' ).'","url":"http:\/\/example.org","is_mobile":false,"width_threshold":1920,"height_threshold":1080}</script><script src=\'http://example.org/wp-content/plugins/wp-rocket/assets/js/lcp-beacon.min.js\' async></script></body>',
	],
];
