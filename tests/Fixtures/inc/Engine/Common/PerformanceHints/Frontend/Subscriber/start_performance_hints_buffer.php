<?php
return [
	'shouldStartBufferWithWprImagedimensions' => [
		'config' => [
			'get' => [
				'wpr_imagedimensions' => '1',
			],
			'server' => [
				'HTTP_WPR_OPT_LIST' => true,
			],
		],
		'expected' => 1,
	],
	'shouldStartBufferWithWprLazyrendercontent' => [
		'config' => [
			'get' => [
				'wpr_lazyrendercontent' => '1',
			],
		],
		'expected' => 1,
	],
	'shouldNotStartBufferWithNoRelevantGETParams' => [
		'config' => [
			'get' => [
				'unrelated_param' => '1',
			],
		],
		'expected' => 0,
	],
	'shouldNotStartBufferWithEmptyGETParams' => [
		'config' => [],
		'expected' => 0,
	],
];
