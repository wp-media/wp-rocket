<?php

return [
	'testShouldReturnNullWhenNotSettingsPage' => [
		'config' => [
			'current_screen' => (object) [
				'id' => 'dashboard',
			],
			'cap' => true,
			'transient' => [
				'pre' => '',
				'result' => '',
				'message' => '',
			],
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenNoCap' => [
		'config' => [
			'current_screen' => (object) [
				'id' => 'settings_page_wprocket',
			],
			'cap' => false,
			'transient' => [
				'pre' => '',
				[
					'result' => '',
					'message' => '',
				],
			],
		],
		'expected' => null,
	],
	'testShouldReturnNullWhenNoTransient' => [
		'config' => [
			'current_screen' => (object) [
				'id' => 'settings_page_wprocket',
			],
			'cap' => true,
			'transient' => false,
		],
		'expected' => null,
	],
	'testShouldReturnNoticeWhenTransient' => [
		'config' => [
			'current_screen' => (object) [
				'id' => 'settings_page_wprocket',
			],
			'cap' => true,
			'transient' => [
				'pre' => '',
				[
					'result' => 'success',
					'message' => 'test',
				],
			],
		],
		'expected' => 'html',
	],
];
