<?php

return [
	'assistant_available'     => [
		'config'   => [
			'available' => true,
		],
		'expected' => [
			'contains'     => [ 'data-wap-prompt', 'wpr-infoAction--help' ],
			'not_contains' => [],
		],
	],
	'assistant_not_available' => [
		'config'   => [
			'available' => false,
		],
		'expected' => [
			'contains'     => [ 'Performance Summary' ],
			'not_contains' => [ 'data-wap-prompt', 'wpr-infoAction--help' ],
		],
	],
];
