<?php

return [
	'shouldExcludeCSSFilesRucssDisabled' => [
		'config'   => [
			'minify_concatenate_css'    => true,
			'excluded-paths'   => [],
		],
		'expected' => [
			'/wp-content/et-cache/(.*).css',
		],
	],
	'minify_concatenate_css' => [
		'config'   => [
			'minify_concatenate_css'    => false,
			'excluded-paths'   => [],
		],
		'expected' => [],
	],
];
