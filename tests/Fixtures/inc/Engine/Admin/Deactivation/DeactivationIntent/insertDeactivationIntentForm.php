<?php

return [
	'shouldDoNothingWhenSnoozedForever' => [
		'config' => [
			'option' => 1,
			'transient' => false,
			'current_screen' => (object) [
				'id' => 'plugins',
			],
		],
		'expected' => false,
	],
	'shouldDoNothingWhenSnoozedByTransient' => [
		'config' => [
			'option' => false,
			'transient' => true,
			'current_screen' => (object) [
				'id' => 'plugins',
			],
		],
		'expected' => false,
	],
	'shouldDoNothingWhenNotOnPluginsPage' => [
		'config' => [
			'option' => false,
			'transient' => false,
			'current_screen' => (object) [
				'id' => 'settings_page_wp_rocket',
			],
		],
		'expected' => false,
	],
	'shouldDisplayFormOnPluginsPage' => [
		'config' => [
			'option' => false,
			'transient' => false,
			'current_screen' => (object) [
				'id' => 'plugins',
			],
		],
		'expected' => true,
	],
];
