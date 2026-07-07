<?php

return [
	'settings'  => [
		'lazyload' => 0,
	],
	'test_data' => [
		'testShouldNotBeAdminContext' => [
			'config'   => [
				'action' => 'none',
			],
			'expected' => [
				'is_admin' => false,
			],
		],

		'testShouldHaveRocketAfterSaveOptionsHookedOutsideAdmin' => [
			'config'   => [
				'action' => 'none',
			],
			'expected' => [
				'hooked' => true,
			],
		],

		'testShouldFireOptionsChangedWhenUpdateRocketOptionAlone' => [
			'config'   => [
				'action'       => 'update_rocket_option',
				'option_name'  => 'lazyload',
				'option_value' => 1,
			],
			'expected' => [
				'options_changed_fired' => true,
			],
		],

		'testShouldFireOptionsChangedWhenSetOptionExecutes' => [
			'config'   => [
				'action'       => 'set_option_execute',
				'option_name'  => 'lazyload',
				'option_value' => 1,
			],
			'expected' => [
				'success'               => true,
				'options_changed_fired' => true,
			],
		],
	],
];
