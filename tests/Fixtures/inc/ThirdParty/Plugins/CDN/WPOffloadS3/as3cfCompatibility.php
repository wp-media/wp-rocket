<?php

return [
	'shouldDisableCdnImagesWhenServingFromS3' => [
		'config'   => [
			'global_set'    => true,
			'plugin_setup'  => true,
			'serve_from_s3' => 1,
		],
		'expected' => [ 'filter_added' => true ],
	],
	'shouldDoNothingWhenNotServingFromS3'     => [
		'config'   => [
			'global_set'    => true,
			'plugin_setup'  => true,
			'serve_from_s3' => 0,
		],
		'expected' => [ 'filter_added' => false ],
	],
	'shouldDoNothingWhenPluginNotSetup'       => [
		'config'   => [
			'global_set'    => true,
			'plugin_setup'  => false,
			'serve_from_s3' => 1,
		],
		'expected' => [ 'filter_added' => false ],
	],
	'shouldDoNothingWhenGlobalMissing'        => [
		'config'   => [
			'global_set'    => false,
			'plugin_setup'  => false,
			'serve_from_s3' => 0,
		],
		'expected' => [ 'filter_added' => false ],
	],
];
