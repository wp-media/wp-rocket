<?php

return [
	'shouldReturnSameWhenCombineCSSDisabled' => [
		'config'   => [
			'minify_concatenate_css' => false,
			'excluded-paths' => [],
		],
		'expected' => [],
	],
	'shouldAddDiviCSSWhenCombineCSSEnabled' => [
		'config'   => [
			'minify_concatenate_css' => true,
			'excluded-paths' => [],
		],
		'expected' => [
			'/wp-content/et-cache/(.*).css',
		],
	],
];
