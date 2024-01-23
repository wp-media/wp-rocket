<?php

return [
	'vfs_dir'   => 'wp-content/',
	'test_data' => [
		'shouldReturnExclusions' => [
			'config'   => [
				'exclusions' => [],
			],
			'expected' => [
				'/wp-content/themes/uncode/library/js/init.js',
				'/wp-content/themes/uncode/library/js/min/init.min.js',
				'/wp-content/themes/uncode/library/js/init.min.js',
				'/wp-content/themes/uncode/library/js/min/ai-uncode.min.js',
				'/wp-content/themes/uncode/library/js/ai-uncode.min.js',
				'/wp-content/themes/uncode/library/js/ai-uncode.js',
				'UNCODE\.(.*)\)\;'
			],
		],
	],
];
