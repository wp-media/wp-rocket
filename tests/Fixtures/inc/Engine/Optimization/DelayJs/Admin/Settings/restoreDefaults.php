<?php
return [
	'shouldRestoreDefaults' => [
		'input' => [
			'capabilities' => [ 'rocket_manage_options' ],
			'options' => [
				'delay_js_scripts' => []
			]
		],
		'restored' => true
	],
	'shouldNotRestoreDefaults' => [
		'input' => [
			'capabilities' => [],
			'options' => [
				'delay_js_scripts' => []
			]
		],
		'restored' => false
	]
];
