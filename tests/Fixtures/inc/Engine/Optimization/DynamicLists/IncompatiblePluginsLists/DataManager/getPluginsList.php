<?php
$json = '{"minify_js":[{"slug":"wp-js","file":"wp-js\/wp-js.php"}],"minify_css||minify_js":[{"slug":"wp-super-minify","file":"wp-super-minify\/wp-super-minify.php"}],"lazyload":[{"slug":"crazy-lazy","file":"crazy-lazy\/crazy-lazy.php"}],"control_heartbeat":[{"slug":"heartbeat-control","file":"heartbeat-control\/heartbeat-control.php"}],"":[{"slug":"wp-asset-clean-up","file":"wp-asset-clean-up\/wpacu.php"},{"slug":"wp-optimize","file":"wp-optimize\/wp-optimize.php"}]}';
$active_options = [
	'lazyload' => true,
	'minify_js' => true,
	'minify_css' => false,
	'control_heartbeat' => false,
];
return [
	'test_data' => [
		'shouldReturnIncompatiblePluginsLists' => [
			'config' => [
				'data_from_json' => json_decode($json),
				'active_options' => $active_options,
			],
			'expected' => [
				'wp-js' => 'wp-js/wp-js.php',
				'wp-super-minify' => 'wp-super-minify/wp-super-minify.php',
				'crazy-lazy' => 'crazy-lazy/crazy-lazy.php',
				'wp-asset-clean-up' => 'wp-asset-clean-up/wpacu.php',
				'wp-optimize' => 'wp-optimize/wp-optimize.php',
			],
		],
	],
];
