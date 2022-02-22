<?php

return [
	'shouldReturnDefaultWhenInternal' => [
		'config' => 'internal',
		'excluded' => [],
		'expected' => [],
	],
	'shouldReturnDefaultWhenInternal' => [
		'config' => 'external',
		'excluded' => [],
		'expected' => [
			'/wp-content/uploads/elementor/css/(.*).css'
		],
	],
];
