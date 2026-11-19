<?php

return [
	'shouldReturnDefaultWhenInternal' => [
		'config' => 'internal',
		'excluded' => [],
		'expected' => [],
	],
	'shouldReturnUpdatedWhenExternal' => [
		'config' => 'external',
		'excluded' => [],
		'expected' => [
			'{{uploads}}/elementor/css/(.*).css'
		],
	],
];
