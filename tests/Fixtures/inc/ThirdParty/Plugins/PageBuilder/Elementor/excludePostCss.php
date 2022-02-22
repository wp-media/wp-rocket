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
			'/wp-content/uploads/elementor/css/(.*).css'
		],
	],
];
