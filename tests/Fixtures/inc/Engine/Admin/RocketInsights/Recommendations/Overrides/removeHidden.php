<?php

return [
	'test_data' => [
		'shouldReturnUnchangedWhenNoOverrides' => [
			'config'   => [
				'overrides'       => [],
				'removed'         => [],
				'recommendations' => [
					[ 'option_slug' => 'plugin_rocketcdn' ],
				],
			],
			'expected' => [
				[ 'option_slug' => 'plugin_rocketcdn' ],
			],
		],
		'shouldKeepWhenOverrideDoesNotRemove'  => [
			'config'   => [
				'overrides'       => [ 'plugin_rocketcdn' ],
				'removed'         => [],
				'recommendations' => [
					[ 'option_slug' => 'plugin_rocketcdn' ],
				],
			],
			'expected' => [
				[ 'option_slug' => 'plugin_rocketcdn' ],
			],
		],
		'shouldDropRemovedAndReindex'          => [
			'config'   => [
				'overrides'       => [ 'plugin_rocketcdn' ],
				'removed'         => [ 'plugin_rocketcdn' ],
				'recommendations' => [
					[ 'option_slug' => 'minify_css' ],
					[ 'option_slug' => 'plugin_rocketcdn' ],
					[ 'option_slug' => 'lazyload' ],
				],
			],
			'expected' => [
				[ 'option_slug' => 'minify_css' ],
				[ 'option_slug' => 'lazyload' ],
			],
		],
	],
];
