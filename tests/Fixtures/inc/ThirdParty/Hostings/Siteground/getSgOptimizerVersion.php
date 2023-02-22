<?php
return [
	'shouldReturnNullOnNoVersion' => [
		'config' => [
			'version' => null,
			'optimizer' => [ 'Version' => '40.1' ]
		],
		'expected' => [
			'version' => '40.1',
			'cache_file' => 'WP_PLUGIN_DIR/sg-cachepress/sg-cachepress.php',
			'params' => [ 'Version' => 'Version' ],
		]
	],
];
