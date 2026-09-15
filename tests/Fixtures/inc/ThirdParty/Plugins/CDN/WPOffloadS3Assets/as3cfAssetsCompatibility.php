<?php

return [
	'shouldMakeCdnOptionReadonlyWhenAddonEnabled' => [
		'config'   => [
			'global_set'   => true,
			'plugin_setup' => true,
			'enable_addon' => 1,
		],
		'expected' => [ 'filter_added' => true ],
	],
	'shouldDoNothingWhenAddonDisabled'            => [
		'config'   => [
			'global_set'   => true,
			'plugin_setup' => true,
			'enable_addon' => 0,
		],
		'expected' => [ 'filter_added' => false ],
	],
	'shouldDoNothingWhenPluginNotSetup'           => [
		'config'   => [
			'global_set'   => true,
			'plugin_setup' => false,
			'enable_addon' => 1,
		],
		'expected' => [ 'filter_added' => false ],
	],
	'shouldDoNothingWhenGlobalMissing'            => [
		'config'   => [
			'global_set'   => false,
			'plugin_setup' => false,
			'enable_addon' => 0,
		],
		'expected' => [ 'filter_added' => false ],
	],
];
