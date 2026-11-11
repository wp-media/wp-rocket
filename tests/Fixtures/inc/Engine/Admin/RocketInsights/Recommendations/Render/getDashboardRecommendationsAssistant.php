<?php

return [
	'assistant_available'     => [
		'config'   => [
			'available' => true,
		],
		'expected' => [
			'contains'     => [ 'wpr-dash-section-header__actions', 'data-wap-prompt', 'wpr-infoAction--help' ],
			'not_contains' => [],
		],
	],
	'assistant_not_available' => [
		'config'   => [
			'available' => false,
		],
		'expected' => [
			'contains'     => [ 'wpr-dash-section-header__title' ],
			'not_contains' => [ 'wpr-dash-section-header__actions', 'data-wap-prompt', 'wpr-infoAction--help' ],
		],
	],
];
