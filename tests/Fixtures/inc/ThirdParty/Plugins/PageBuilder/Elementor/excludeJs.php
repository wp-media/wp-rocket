<?php

return [
	'shouldReturnDefaultWhenCombineJSDisabled' => [
		'config' => [
			'combine_js' => false,
		],
		'expected' => [],
	],
	'shouldReturnUpdated' => [
		'config' => [
			'combine_js' => true,
		],
		'expected' => [
			'/wp-includes/js/dist/hooks(.min)?.js',
		],
	],
];
