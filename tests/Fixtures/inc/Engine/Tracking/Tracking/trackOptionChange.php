<?php

return [
	'shouldReturnEmptyArrayWhenNoMixpanelTrackedSettings' => [
		'config' => [
			'list_data' => (object) [
				'other_setting' => [
					'some_value',
				],
			],
			'original_options' => [],
		],
		'expected' => [],
	],
	'shouldReturnEmptyArrayWhenListIsEmpty' => [
		'config' => [
			'list_data' => (object) [],
			'original_options' => [],
		],
		'expected' => [],
	],
	'shouldReturnDynamicOptionsWhenAvailable' => [
		'config' => [
			'list_data' => (object) [
				'mixpanel_tracked_settings' => [
					'auto_preload_fonts',
					'host_fonts',
				],
			],
			'original_options' => [],
		],
		'expected' => [
			'auto_preload_fonts',
			'host_fonts',
		],
	],
	'shouldReturnSingleOptionWhenOnlyOneInList' => [
		'config' => [
			'list_data' => (object) [
				'mixpanel_tracked_settings' => [
					'host_fonts',
				],
			],
			'original_options' => [],
		],
		'expected' => [
			'host_fonts',
		],
	],
	'shouldMergeDynamicOptionsWithExistingOptions' => [
		'config' => [
			'list_data' => (object) [
				'mixpanel_tracked_settings' => [
					'host_fonts',
					'some_new_option',
				],
			],
			'original_options' => [ 'auto_preload_fonts' ],
		],
		'expected' => [
			'auto_preload_fonts',
			'host_fonts',
			'some_new_option',
		],
	],
	'shouldPreserveExistingOptionsWhenNoMixpanelSettings' => [
		'config' => [
			'list_data' => (object) [
				'other_setting' => [
					'some_value',
				],
			],
			'original_options' => [ 'existing_option', 'another_option' ],
		],
		'expected' => [
			'existing_option',
			'another_option',
		],
	],
];
