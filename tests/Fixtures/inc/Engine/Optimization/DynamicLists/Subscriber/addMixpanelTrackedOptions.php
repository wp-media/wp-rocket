<?php

return [
	'shouldReturnUpdatedArrayWhenEmptyOriginal' => [
		'config' => [
			'original' => [],
			'list' => (object) [
				'mixpanel_tracked_settings' => [
					'auto_preload_fonts',
					'host_fonts',
				],
			],
		],
		'expected' => [
			'auto_preload_fonts',
			'host_fonts',
		],
	],
	'shouldReturnUpdatedArrayWhenNotEmptyOriginal' => [
		'config' => [
			'original' => [
				'some_existing_option',
			],
			'list' => (object) [
				'mixpanel_tracked_settings' => [
					'auto_preload_fonts',
					'host_fonts',
				],
			],
		],
		'expected' => [
			'some_existing_option',
			'auto_preload_fonts',
			'host_fonts',
		],
	],
	'shouldReturnOriginalWhenNoMixpanelTrackedSettingsInList' => [
		'config' => [
			'original' => [
				'existing_option',
			],
			'list' => (object) [
				'other_setting' => [
					'some_value',
				],
			],
		],
		'expected' => [
			'existing_option',
		],
	],
	'shouldReturnOriginalWhenListIsEmpty' => [
		'config' => [
			'original' => [
				'existing_option',
			],
			'list' => (object) [],
		],
		'expected' => [
			'existing_option',
		],
	],
];
