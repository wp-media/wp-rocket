<?php
return [
	'shouldStartBufferWithWprImagedimensions' => [
		'config' => [
			'wpr_imagedimensions' => '1',
		],
		'expected' => 1,
	],
	'shouldStartBufferWithWprLazyrendercontent' => [
		'config' => [
			'wpr_lazyrendercontent' => '1',
		],
		'expected' => 1,
	],
	'shouldNotStartBufferWithNoRelevantGETParams' => [
		'config' => [
			'unrelated_param' => '1',
		],
		'expected' => 0,
	],
	'shouldNotStartBufferWithEmptyGETParams' => [
		'config' => [],
		'expected' => 0,
	],
];
